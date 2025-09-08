<?php
// download.php - Handle file downloads with proper headers
if (isset($_GET['file'])) {
    $filename = basename($_GET['file']);
    $filepath = __DIR__ . '/downloads/' . $filename;
    
    // Security check - only allow PDF files
    if (file_exists($filepath) && pathinfo($filename, PATHINFO_EXTENSION) === 'pdf') {
        // Set headers for PDF download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
        header('Expires: 0');
        
        // For mobile compatibility
        header('Accept-Ranges: bytes');
        
        // Clear any previous output
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Output the file
        readfile($filepath);
        exit;
    } else {
        // File not found or invalid
        http_response_code(404);
        echo "<!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>File Not Found</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; text-align: center; }
                .error { color: #f44336; font-size: 24px; margin-bottom: 20px; }
                .button { 
                    display: inline-block; 
                    padding: 10px 15px; 
                    background-color: #4CAF50; 
                    color: white; 
                    text-decoration: none; 
                    border-radius: 4px; 
                }
            </style>
        </head>
        <body>
            <div class='error'>❌ File not found</div>
            <p>The requested file could not be found or is not accessible.</p>
            <a href='index.php' class='button'>🏠 Go Back</a>
        </body>
        </html>";
    }
} else {
    // No file parameter
    http_response_code(400);
    echo "<!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Bad Request</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; text-align: center; }
            .error { color: #f44336; font-size: 24px; margin-bottom: 20px; }
            .button { 
                display: inline-block; 
                padding: 10px 15px; 
                background-color: #4CAF50; 
                color: white; 
                text-decoration: none; 
                border-radius: 4px; 
            }
        </style>
    </head>
    <body>
        <div class='error'>❌ Bad Request</div>
        <p>No file specified for download.</p>
        <a href='index.php' class='button'>🏠 Go Back</a>
    </body>
    </html>";
}
?>
