<?php
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $date = $_POST['date'] ?? '';
    $address = $_POST['address'] ?? '';
    $purpose = $_POST['purpose'] ?? '';
    
    // Format the date
    $formatted_date = date('d-m-Y', strtotime($date));
    
    // Base cloudflare URL
    $baseUrl = "http://localhost";
    
    // Template URL
    $templateUrl = $baseUrl . "/Edutourism.lk/invitationTemplate/malinvtemp.html";
    
    // Read the template file from URL
    $htmlContent = file_get_contents($templateUrl);
    
    if ($htmlContent !== false) {
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
            $pdfCreated = true;
        } else {
            $pdfCreated = false;
            $errorMessage = "Error converting to PDF (HTTP Code: $httpCode): " . ($error ? $error : $response);
            
            // Log more details for debugging
            error_log("PDFShift Error - HTTP Code: $httpCode, Error: $error, Response: $response");
        }
        
        // Show success message
        echo "
        <!DOCTYPE html>
        <html>
        <head>
            <title>Invitation Generated</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 40px;
                    line-height: 1.6;
                }
                .container {
                    max-width: 800px;
                    margin: 0 auto;
                    padding: 20px;
                    border: 1px solid #ddd;
                    border-radius: 5px;
                }
                .success {
                    color: #4CAF50;
                    font-size: 24px;
                    margin-bottom: 20px;
                }
                .error {
                    color: #f44336;
                    font-size: 24px;
                    margin-bottom: 20px;
                }
                .button {
                    display: inline-block;
                    padding: 10px 15px;
                    background-color: #4CAF50;
                    color: white;
                    text-decoration: none;
                    border-radius: 4px;
                    margin-right: 10px;
                    cursor: pointer;
                }
                pre {
                    background-color: #f5f5f5;
                    padding: 10px;
                    border-radius: 5px;
                    overflow: auto;
                    max-height: 200px;
                }
            </style>
        </head>
        <body>
            <div class='container'>";
        
        if (isset($pdfCreated) && $pdfCreated) {
            echo "
                <div class='success'>Invitation letter has been generated successfully!</div>
                <p>Your invitation letter has been generated and saved as:</p>
                <p><strong>HTML: {$invitationLetter}</strong></p>
                <p><strong>PDF: {$pdfFilename}</strong></p>
                <a href='{$invitationLetter}' class='button' target='_blank'>View HTML</a>
                <a href='{$pdfFilename}' class='button' target='_blank'>Download PDF</a>
                <a href='index.php' class='button'>Create Another</a>";
        } else {
            echo "
                <div class='error'>Error creating PDF</div>
                <p>" . (isset($errorMessage) ? $errorMessage : "An unknown error occurred") . "</p>
                <p>HTML file: <a href='{$invitationLetter}' target='_blank'>{$invitationLetter}</a></p>
                <p>URL sent to PDFShift: <code>{$serverUrl}</code></p>
                <p>Debug Information:</p>
                <pre>" . (isset($response) ? htmlspecialchars(json_encode(json_decode($response), JSON_PRETTY_PRINT)) : "No response data") . "</pre>
                <a href='index.php' class='button'>Try Again</a>";
        }
        
        echo "
            </div>
        </body>
        </html>
        ";
    } else {
        echo "Error: Unable to access template at {$templateUrl}";
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Generate Invitation Letter</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"],
        input[type="email"],
        input[type="date"],
        textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        textarea {
            height: 100px;
        }
        button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Generate Invitation Letter</h1>
    
    <form method="post" action="">
        <div class="form-group">
            <label for="name">Recipient Name:</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-group">
            <label for="email">Recipient Nic:</label>
            <input type="text" id="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label for="date">Date:</label>
            <input type="date" id="date" name="date" required>
        </div>
        
        <div class="form-group">
            <label for="address">Recipient Country:</label>
            <input type="text" id="address" name="address" required placeholder="e.g. Sri Lanka">
        </div>
        
        <div class="form-group">
            <label for="purpose">Purpose of Invitation (Optional):</label>
            <textarea id="purpose" name="purpose"></textarea>
        </div>
        
        <button type="submit">Generate Invitation</button>
    </form>
</body>
</html>