
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edutourism.lk guidelines</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }
        body {
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            color: #333;
        }
        .header {
            background-color: #3498db;
            color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .guideline-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .guideline-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .guideline-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .guideline-card h3 {
            color: #3498db;
            margin-top: 0;
        }
        .guideline-card p {
            color: #666;
        }
        .guideline-card .icon {
            font-size: 48px;
            margin-bottom: 15px;
            color: #3498db;
        }
        .nav {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }
        .nav-right {
            display: flex;
            align-items: center;
        }
        .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .payment-section {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
            border-left: 4px solid #3498db;
        }
        .payment-steps {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
            text-align: center;
        }
        .step {
            flex: 1;
            padding: 15px;
            position: relative;
        }
        .step:not(:last-child):after {
            content: "→";
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            font-size: 24px;
            color: #ccc;
        }
        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            background-color: #3498db;
            color: white;
            border-radius: 50%;
            line-height: 30px;
            margin-bottom: 10px;
        }
        footer {
            text-align: center;
            padding: 20px;
            background-color: #333;
            color: white;
            margin-top: 40px;
        }
    </style>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="header">
        <h1>Edutourism.lk guidelines</h1>
        <p>All the guidelines you need in one place</p>
    </div>
    
    <div class="container">
        <div class="nav">
            <div>
                <h2></h2>
            </div>
            <div class="nav-right">
                <!--  if ($logged_in): ?>
                    <a href="my_account.php" class="btn"><i class="fas fa-user"></i> My Account</a>
                 else: ?>
                    <a href="login.php" class="btn"><i class="fas fa-sign-in-alt"></i> Login</a>
                 endif; ?> -->
            </div>
        </div>
        
        <div class="guideline-grid">
            <a href="letters_guidelines.php" class="guideline-card">
                <div class="icon"><i class="fas fa-envelope"></i></div>
                <h3>Letters Guidelines</h3>
                <p>Learn how to format and submit required letters for your application.</p>
            </a>
            
            <a href="pass_size_photo.php" class="guideline-card">
                <div class="icon"><i class="fas fa-id-card"></i></div>
                <h3>Passport Size Photo</h3>
                <p>Requirements and specifications for passport-sized photographs.</p>
            </a>
            
            <a href="visa_req_guidelines.php" class="guideline-card">
                <div class="icon"><i class="fas fa-passport"></i></div>
                <h3>Visa Requirements</h3>
                <p>Complete list of documents needed for visa application.</p>
            </a>
            
            <a href="bank_statement_guidelines.php" class="guideline-card">
                <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <h3>Bank Statement Guidelines</h3>
                <p>Format and requirements for bank statements submission.</p>
            </a>
        </div>
        
        <!--  if ($logged_in): ?> -->
        <div class="payment-section">
            <h2>Payment and Application Process</h2>
            <p>Follow these steps to complete your application:</p>
            
            <div class="payment-steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h4>Login</h4>
                    <p>Access your account</p>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <h4>First Payment</h4>
                    <p>Complete initial payment</p>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <h4>Submit Documents</h4>
                    <p>Upload required documents</p>
                </div>
                
                <div class="step">
                    <div class="step-number">4</div>
                    <h4>Second Payment</h4>
                    <p>Complete final payment</p>
                </div>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="my_account.php" class="btn"><i class="fas fa-arrow-right"></i> Go to My Account</a>
            </div>
        </div>
        <!-- php endif; ?> -->
    </div>
    
    <footer>
        &copy; <?php echo date('Y'); ?> Edutourism - All Rights Reserved
    </footer>
</body>
</html>