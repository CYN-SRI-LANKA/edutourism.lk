<?php
require_once __DIR__ . '/vendor/autoload.php';
use Twilio\Rest\Client;

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'edutourism_lk';
$adminwhatsapp_number = '+94777937486';
$staffwhatsapp_number = '+94777937486';

session_start();
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create message log table if it doesn't exist
$createLogTable = "CREATE TABLE IF NOT EXISTS message_logs (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    message_sid VARCHAR(255),
    recipient VARCHAR(50),
    message_type VARCHAR(50),
    status VARCHAR(50),
    error_message TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($createLogTable);

// Function to send WhatsApp template messages
function sendWhatsAppTemplate($to, $templateSid, $templateVariables, $accountSid, $authToken, $from, $conn) {
    try {
        $client = new Client($accountSid, $authToken);
        
        // Format WhatsApp number if needed
        if (substr($to, 0, 1) === '0') {
            $to = '+94' . substr($to, 1);
        } elseif (substr($to, 0, 3) === '+94') {
            // Already formatted correctly
        } else {
            // Assume it's a local number without the leading 0
            $to = '+94' . $to;
        }
        
        // Add "whatsapp:" prefix for Twilio if not already present
        if (strpos($to, 'whatsapp:') !== 0) {
            $to = 'whatsapp:' . $to;
        }
        
        if (strpos($from, 'whatsapp:') !== 0) {
            $from = 'whatsapp:' . $from;
        }
        
        // Check rate limits for this recipient
        $rateLimit = $conn->query("SELECT COUNT(*) as count FROM message_logs 
                                  WHERE recipient = '" . $conn->real_escape_string($to) . "' 
                                  AND sent_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $count = $rateLimit->fetch_assoc()['count'];
        
        // Set reasonable limit - adjust based on your needs
        if ($count > 10) {
            error_log("Rate limit exceeded for $to - not sending message");
            return [
                'success' => false,
                'message' => 'Rate limit exceeded',
                'error' => 'Too many messages sent to this recipient in 24 hours'
            ];
        }
        
        // Log the attempt with details
        error_log("Sending template message to: $to from: $from");
        error_log("Template SID: $templateSid");
        error_log("Template variables: " . json_encode($templateVariables));
        
        // Send the template message
        $message = $client->messages->create(
            $to,
            [
                'from' => $from,
                'contentSid' => $templateSid,
                'contentVariables' => json_encode($templateVariables)
            ]
        );
        
        // Fix: Get the message SID as a string directly
        $messageSid = $message->sid;
        
        // Log successful send to database
        $stmt = $conn->prepare("INSERT INTO message_logs (message_sid, recipient, message_type, status) 
                               VALUES (?, ?, 'template', 'sent')");
        $stmt->bind_param("ss", $messageSid, $to);
        $stmt->execute();
        $stmt->close();
        
        error_log("Template message sent: " . $messageSid);
        return [
            'success' => true,
            'message' => 'WhatsApp message sent successfully',
            'messageSid' => $messageSid,
            'dateCreated' => $message->dateCreated->format('Y-m-d H:i:s')
        ];
    } catch (Exception $e) {
        // Log error to database
        $errorMsg = $e->getMessage();
        $stmt = $conn->prepare("INSERT INTO message_logs (recipient, message_type, status, error_message) 
                               VALUES (?, 'template', 'failed', ?)");
        $stmt->bind_param("ss", $to, $errorMsg);
        $stmt->execute();
        $stmt->close();
        
        error_log("Error sending template message to $to: " . $errorMsg);
        return [
            'success' => false,
            'message' => 'Error sending WhatsApp message: ' . $errorMsg,
            'error' => $errorMsg
        ];
    }
}

$customer_email = isset($_SESSION['customer_email']) ? $_SESSION['customer_email'] : '';

// Log current session data for debugging
error_log("Customer email from session: " . $customer_email);

if (empty($customer_email)) {
    error_log("No customer email found in session");
    echo "Error: No customer email found in session. Please try again or contact support.";
    exit;
}

// Fetch registration data from database
$sql = "SELECT full_name, id, whatsapp_number, payment_slip_path, customer_email, tour_type
        FROM tour_registrations 
        WHERE customer_email = '" . $conn->real_escape_string($customer_email) . "'
        ORDER BY id DESC 
        LIMIT 1";
$result = $conn->query($sql);

// Twilio credentials
$sid = "ACfsfafafaf6bb"; // Your Twilio SID
$token = "2758a338dadadadadadc0"; // Your Twilio token
$twilio_whatsapp_number = "+94777937438"; // Your Twilio WhatsApp number

// Check if we got any results
if ($result && $result->num_rows > 0) {
    error_log("Found " . $result->num_rows . " registration(s) for email: " . $customer_email);
    
    // Process each registration
    while($row = $result->fetch_assoc()) {
        $whatsapp_number = $row['whatsapp_number'];
        
        // Format WhatsApp number
        if (substr($whatsapp_number, 0, 1) === '0') {
            // Remove the leading 0 and add +94
            $whatsapp_number = '+94' . substr($whatsapp_number, 1);
        }
        
        // Log registration details
        error_log("Processing registration ID: " . $row['id'] . " for " . $row['full_name']);
        error_log("Customer WhatsApp: " . $whatsapp_number);
        
        try {
            // Initialize success tracking
            $allSuccess = true;
            
            // Templates for different message types - remove any extra spaces
            $adminTemplateSid = 'HXded29xxxxx994xx79e70'; // Admin template SID
            $staffTemplateSid = 'HX42791841946xasfb5c197966'; // Staff template SID - removed extra spaces
            $customerTemplateSid = 'HX2asae189c5c2'; // Customer template SID
            
            // If you have a generic fallback template, define it here
            $fallbackTemplateSid = 'HX42fwfwf0e60fb5cw66'; // Use your approved fallback template
            
            // Admin message - Ensuring variable numbers match your approved template
            $adminTemplateVariables = [
                '5' => $row['id'],
                '6' => $row['full_name'],
                '7' => $row['customer_email'],
                '8' => $row['tour_type']
            ];

            $adminResult = sendWhatsAppTemplate(
                $adminwhatsapp_number,
                $adminTemplateSid,
                $adminTemplateVariables,
                $sid,
                $token,
                $twilio_whatsapp_number,
                $conn
            );

            // If admin message fails, log but continue with other messages
            if (!$adminResult['success']) {
                $allSuccess = false;
                error_log("Admin message failed: " . $adminResult['error']);
            }

            // Rate limiting - pause between messages
            //sleep(2);

            // STAFF MESSAGE - sent after delay
            $staffTemplateVariables = [
                   '1' => $row['id'],
                   '2' => $row['full_name'],
                   '3' => $row['customer_email'],
                   '4' => $row['tour_type']
            ];

            $staffResult = sendWhatsAppTemplate(
                $staffwhatsapp_number,
                $staffTemplateSid,
                $staffTemplateVariables,
                $sid,
                $token,
                $twilio_whatsapp_number,
                $conn
            );

            // If staff message fails, log but continue
            if (!$staffResult['success']) {
                $allSuccess = false;
                error_log("Staff message failed: " . $staffResult['error']);
            }

            // Rate limiting - pause between messages
            //sleep(2);

            // CUSTOMER MESSAGE - sent after delay
            $customerTemplateVariables = [
                '10' => $row['full_name']
            ];

            $customerResult = sendWhatsAppTemplate(
                $whatsapp_number,
                $customerTemplateSid,
                $customerTemplateVariables,
                $sid,
                $token,
                $twilio_whatsapp_number,
                $conn
            );

            // If customer message fails, log but continue
            if (!$customerResult['success']) {
                $allSuccess = false;
                error_log("Customer message failed: " . $customerResult['error']);
            }
            
            // Instead of trying to update non-existent columns, just log the result
            error_log("Notification status for registration " . $row['id'] . ": " . ($allSuccess ? 'All messages sent' : 'Some messages failed'));
            
            //if ($allSuccess) {
            //    echo "<script>alert('Registration confirmed! Notifications sent successfully.');</script>";
            //} else {
            // //   echo "<script>alert('Registration confirmed, but some notifications could not be sent.');</script>";
            //}
            
            // Redirect to account page
            echo "<script>window.location.href = 'account.php';</script>";
            
        } catch (Exception $e) {
            error_log("Error in message processing: " . $e->getMessage());
            echo "Error processing messages: " . $e->getMessage() . "<br>";
            // Continue execution instead of stopping at the first error
        }
    }
} else {
    error_log("No registration found for email: " . $customer_email);
    echo "<script>alert('No registration found. Please try again or contact support.');</script>";
    echo "<script>window.location.href = 'account.php';</script>";
}

$conn->close();
?>