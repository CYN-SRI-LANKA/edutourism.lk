<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coming Soon - EduTourism v3.1</title>
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Arial', sans-serif;
        }

        .bgimg {
            color: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100%;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .topleft {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }

        .middle {
            text-align: center;
            color: white;
            z-index: 100;
        }

        .middle h1 {
            font-size: 4rem;
            margin: 0;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
            animation: pulse 2s infinite;
        }

        .middle hr {
            width: 200px;
            border: 2px solid white;
            margin: 20px auto;
        }

        .middle p {
            font-size: 1.5rem;
            margin: 10px 0;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }

        .bottomleft {
            position: absolute;
            bottom: 20px;
            left: 20px;
            color: white;
            font-size: 1rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* Redirect message styling */
        .redirect-message {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.95);
            color: #333;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            z-index: 9999;
            min-width: 320px;
            border-left: 5px solid #5cb85c;
            backdrop-filter: blur(10px);
        }

        .countdown-number {
            color: #d9534f;
            font-size: 24px;
            font-weight: bold;
            text-shadow: none;
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            margin-top: 10px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .btn-success {
            background-color: #28a745;
        }

        .btn-success:hover {
            background-color: #1e7e34;
        }

        .close-btn {
            position: absolute;
            top: 5px;
            right: 10px;
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            color: #999;
        }

        .close-btn:hover {
            color: #333;
        }

        /* Progress bar */
        .progress-container {
            width: 100%;
            height: 4px;
            background: #eee;
            border-radius: 2px;
            margin: 10px 0;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #28a745, #20c997);
            border-radius: 2px;
            transition: width 1s linear;
        }

        /* Mobile responsive */
        @media (max-width: 768px) {
            .middle h1 {
                font-size: 2.5rem;
            }
            
            .middle p {
                font-size: 1.2rem;
            }
            
            .topleft img {
                width: 150px !important;
            }
            
            .redirect-message {
                right: 10px;
                left: 10px;
                min-width: auto;
                top: 10px;
            }
        }

        /* Loading animation */
        .loading-dots {
            display: inline-block;
        }

        .loading-dots:after {
            content: '';
            animation: loading 2s infinite;
        }

        @keyframes loading {
            0% { content: ''; }
            25% { content: '.'; }
            50% { content: '..'; }
            75% { content: '...'; }
            100% { content: ''; }
        }
    </style>
</head>
<body style="background-color:black;">
    <div class="bgimg">
        <div class="topleft">
            <img src="../homepage/img/logo.png" style="width: 200px; height: auto;" alt="EduTourism Logo">
        </div>
        <div class="middle">
            <h1>COMING SOON</h1>
            <hr>
            <p>In EduTourism v3.1</p>
        </div>
        <div class="bottomleft">
            <p>Under operation<span class="loading-dots"></span></p>
        </div>
    </div>

    <script>
        // Auto-redirect with countdown display
        let countdown = 8;
        let redirectTimer;
        let progressTimer;

        function showRedirectMessage() {
            // Create the redirect message
            let messageDiv = document.getElementById('redirect-message');
            if (!messageDiv) {
                messageDiv = document.createElement('div');
                messageDiv.id = 'redirect-message';
                messageDiv.className = 'redirect-message';
                
                messageDiv.innerHTML = `
                    <button class="close-btn" onclick="cancelRedirect()" title="Cancel redirect">&times;</button>
                    <div style="margin-right: 20px;">
                        <strong style="color: #28a745;">Edutourism Team</strong><br>
                        <p style="margin: 10px 0; font-size: 14px;">Redirecting to Admin Panel in <span class="countdown-number">${countdown}</span> seconds...</p>
                        <div class="progress-container">
                            <div class="progress-bar" id="progress-bar" style="width: 100%;"></div>
                        </div>
                        <a href="adminmain.php" class="btn btn-success" style="margin-right: 10px;">
                             Go Now
                        </a>
                        <button onclick="cancelRedirect()" class="btn" style="background-color: #6c757d;">
                             Cancel
                        </button>
                    </div>
                `;
                
                document.body.appendChild(messageDiv);
            } else {
                // Update countdown
                messageDiv.querySelector('.countdown-number').textContent = countdown;
                // Update progress bar
                const progressPercentage = ((8 - countdown) / 8) * 100;
                messageDiv.querySelector('#progress-bar').style.width = (100 - progressPercentage) + '%';
            }
        }

        function startRedirectCountdown() {
            showRedirectMessage();
            
            redirectTimer = setInterval(function() {
                countdown--;
                showRedirectMessage();
                
                if (countdown <= 0) {
                    clearInterval(redirectTimer);
                    // Add loading effect before redirect
                    document.querySelector('.redirect-message').innerHTML = `
                        <div style="text-align: center; padding: 10px;">
                            <strong style="color: #007bff;">🔄 Redirecting...</strong>
                            <div class="progress-container">
                                <div class="progress-bar" style="width: 100%; animation: pulse 1s infinite;"></div>
                            </div>
                        </div>
                    `;
                    setTimeout(() => {
                        window.location.href = 'adminmain.php';
                    }, 1000);
                }
            }, 1000);
        }

        function cancelRedirect() {
            clearInterval(redirectTimer);
            const messageDiv = document.getElementById('redirect-message');
            if (messageDiv) {
                messageDiv.style.animation = 'fadeOut 0.3s ease-out';
                setTimeout(() => {
                    messageDiv.remove();
                }, 300);
            }
        }

        // Add fade out animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeOut {
                from { opacity: 1; transform: translateX(0); }
                to { opacity: 0; transform: translateX(100%); }
            }
        `;
        document.head.appendChild(style);

        // Start countdown when page loads
        window.onload = function() {
            // Add a small delay before showing the redirect message
            setTimeout(() => {
                startRedirectCountdown();
            }, 2000);
        };

        // Add keyboard shortcut to go directly
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                window.location.href = 'adminmain.php';
            } else if (event.key === 'Escape') {
                cancelRedirect();
            }
        });


    </script>
</body>
</html>
