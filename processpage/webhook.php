<?php
require_once 'vendor/autoload.php';
use Twilio\TwiML\MessagingResponse;
use Twilio\Rest\Client;

// Enable error logging
error_log("Webhook received: " . print_r($_POST, true));

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'edutourism_lk';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

// Twilio credentials
$sid = "AC0f08b2620ce36b50798c5f7dacaf0ced";
$token = "055358bc43c6ee280cd496585c6cba15";
$twilio = new Client($sid, $token);

// Base cloudflare URL
$baseUrl = "https://edutourism.lk";


//Function to check for keywords
function isMessageConfirmed($message) {
    $confirmationKeywords = ['confirm', 'confirmed', 'yes', 'approve', 'approved'];
    $message = strtolower($message);
    
    // Check if any of the keywords exist in the message
    foreach ($confirmationKeywords as $keyword) {
        if (strpos($message, $keyword) !== false) {
            return true;
        }
    }
    return false;
}

// Function to generate invitation PDF
function generateInvitationPDF($conn, $registrationData) {
    global $baseUrl;
    
    // Extract data from registration
    $name = $registrationData['full_name'];
    $email = $registrationData['customer_email']; // This could be used as NIC if that's how your schema works
    $date = date('Y-m-d'); // Current date, or you could use a date from registration data
    
    // Format the date
    $formatted_date = date('d-m-Y', strtotime($date));
    
    // Template URL
    $templateUrl = $baseUrl . "/Edutourism.lk/invitationTemplate/Malaysia%20Study%20Tour%20Invitation%20Letter%20.html";
    
    // Read the template file from URL
    $htmlContent = file_get_contents($templateUrl);
    
    if ($htmlContent === false) {
        error_log("Error: Unable to access template at {$templateUrl}");
        return false;
    }
    
    // Replace placeholders with actual data
    $htmlContent = str_replace('$date', $formatted_date, $htmlContent);
    $htmlContent = str_replace('$name', htmlspecialchars($name), $htmlContent);
    $htmlContent = str_replace('$email', htmlspecialchars($email), $htmlContent);
    
    // Save the customized HTML to a new file
    $timestamp = date('d-m-Y_H-i-s');
    $invitationLetter = 'invitation_' . $timestamp . '.html';
    file_put_contents($invitationLetter, $htmlContent);
    
    // Generate the full URL to the saved HTML file
    $relativePath = "/Edutourism.lk/invitationTemplate/" . $invitationLetter;
    $serverUrl = $baseUrl . $relativePath;
    
    // Generate PDF using PDFShift
    $pdfFilename = 'invitation_' . $timestamp . '.pdf';
    
    // Initialize cURL
    $curl = curl_init();
    
    // Configure the PDFShift API request with parameters for foreground-only rendering
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.pdfshift.io/v3/convert/pdf",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(array(
            "source" => $serverUrl,
            "landscape" => false,
            "format" => "A4",
            "margin" => "0",  // No margin
            "css" => "body { background: none !important; border: none !important; } 
                     * { background: none !important; border: none !important; box-shadow: none !important; }",
            "use_print" => true  // Use print media type which often has simpler styling
        )),
        CURLOPT_HTTPHEADER => array('Content-Type:application/json'),
        CURLOPT_USERPWD => 'api:sk_3e668973b9800ab9ebdfafeada837c0e7bbf7677'
    ));
    
    // Execute cURL request
    $response = curl_exec($curl);
    $error = curl_error($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    
    // Close cURL
    curl_close($curl);
    
    // Check if the request was successful
    if ($httpCode == 200 && !$error) {
        // Save the PDF file
        file_put_contents($pdfFilename, $response);
        
        // Update database with the PDF path
        $updateSql = "UPDATE tour_registrations SET invitation_pdf_path = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("si", $pdfFilename, $registrationData['id']);
        $updateStmt->execute();
        $updateStmt->close();
        
        return $pdfFilename;
    } else {
        error_log("PDFShift Error - HTTP Code: $httpCode, Error: $error, Response: $response");
        return false;
    }
}

// Get WhatsApp message details
$from = $_POST['From'] ?? '';
$body = $_POST['Body'] ?? '';
$messageSid = $_POST['MessageSid'] ?? '';

// Check if message is confirmed
$isConfirmed = isMessageConfirmed($body);

// Save to database with confirmation status
$sql = "INSERT INTO message_responses (
            phone_number, 
            message_content, 
            message_sid, 
            is_confirmed,
            created_at
        ) VALUES (?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $from, $body, $messageSid, $isConfirmed);

try {
    $stmt->execute();
    error_log("Message saved successfully");
    
    // Initialize response text
    $responseText = "Message received: " . $body;
    
    // If message is confirmed, update status to "approved" and generate invitation
    if ($isConfirmed) {
        // Get the pending registration
        $sqlsel = "SELECT id, full_name, whatsapp_number, payment_slip_path, customer_email, tour_type
                FROM tour_registrations 
                WHERE status = 'pending' 
                ORDER BY id DESC 
                LIMIT 1";
        $resultInvi = $conn->query($sqlsel);

        error_log("Payment confirmed for message: " . $messageSid);
        
        if ($resultInvi && $resultInvi->num_rows > 0) {
            $row = $resultInvi->fetch_assoc();
            $registrationId = $row['id'];
            
            // Format WhatsApp number
            $whatsapp_number = $row['whatsapp_number'];
            if (substr($whatsapp_number, 0, 1) === '0') {
                // Remove the leading 0 and add +94
                $whatsapp_number = '+94' . substr($whatsapp_number, 1);
            }
            
            // Generate invitation PDF
            $pdfFilename = generateInvitationPDF($conn, $row);
            
            if ($pdfFilename) {
                // Update status to "approved" in tour_registrations table
                $updateSql = "UPDATE tour_registrations SET status = 'approved' WHERE id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("i", $registrationId);
                $updateStmt->execute();
                $updateStmt->close();
                
                error_log("Status updated to 'approved' for ID: " . $registrationId);
                
                // Get full server path to PDF
                $pdfPath = realpath($pdfFilename);
                
                // Send PDF via WhatsApp
                try {
                    // First send confirmation message
                    $confirmationMessage = $twilio->messages->create(
                        "whatsapp:" . $whatsapp_number,
                        array(
                            "from" => "whatsapp:+94777937438",
                            "body" => "Thank you! Your payment has been confirmed and your registration for the Malaysia Study Tour is now approved. Your invitation letter is attached."
                        )
                    );
                    
                    // Then send the PDF as a separate message
                    if (file_exists($pdfPath)) {
                        $pdfMessage = $twilio->messages->create(
                            "whatsapp:" . $whatsapp_number,
                            array(
                                "from" => "whatsapp:+94777937438",
                                "mediaUrl" => [$baseUrl . "/Edutourism.lk/invitationTemplate/" . $pdfFilename],
                                "body" => "Malaysia Study Tour Invitation Letter for " . $row['full_name']
                            )
                        );
                        error_log("PDF sent successfully to: " . $whatsapp_number);
                    } else {
                        error_log("PDF file does not exist: " . $pdfPath);
                    }
                    
                    $responseText = "Thank you! Your payment has been confirmed and your registration is now approved. An invitation letter has been sent to your WhatsApp.";
                    
                } catch (Exception $e) {
                    error_log("Error sending WhatsApp message: " . $e->getMessage());
                    $responseText = "Thank you! Your payment has been confirmed and your registration is now approved. However, there was an issue sending your invitation letter.";
                }
            } else {
                error_log("Failed to generate invitation PDF");
                $responseText = "Thank you! Your payment has been confirmed and your registration is now approved. Your invitation letter will be sent shortly.";
            }
        } else {
            error_log("No pending registration found");
        }
    }
} catch (Exception $e) {
    error_log("Error: " . $e->getMessage());
}

// Send response
$response = new MessagingResponse();
$response->message($responseText);

// Set content type header before outputting response
header("content-type: text/xml");
echo $response;

// Clean up
$stmt->close();
$conn->close();