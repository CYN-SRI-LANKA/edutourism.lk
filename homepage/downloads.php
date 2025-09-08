<?php
$active = "downloads";
include('db.php');
include("functions.php");
include("header.php");

?>

<!-- Enhanced Download Center Styles -->
<style>
    /* Importing root variables from index styles */
    :root {
        --primary-color: #1a2b49;  /* Deep blue */
        --secondary-color: #ff7e00; /* Orange */
        --text-color: #333333;
        --light-gray: #f5f5f5;
        --medium-gray: #e0e0e0;
        --dark-gray: #888888;
        --white: #ffffff;
        --shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        --transition: all 0.3s ease;
        --border-radius: 8px;
        --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }
    
    /* General Styles */
    .downloads-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    /* Breadcrumb Styles */
    .breadcrumb-section {
        background-color: var(--light-gray);
        padding: 15px 0;
        margin-bottom: 30px;
    }
    
    .breadcrumb-text {
        display: flex;
        align-items: center;
        font-size: 16px;
    }
    
    .breadcrumb-text a {
        color: var(--primary-color);
        text-decoration: none;
        transition: color 0.3s;
    }
    
    .breadcrumb-text a:hover {
        color: var(--secondary-color);
    }
    
    .breadcrumb-text span {
        margin-left: 10px;
        position: relative;
        padding-left: 15px;
        color: var(--text-color);
    }
    
    .breadcrumb-text span:before {
        content: '/';
        position: absolute;
        left: 5px;
        color: #999;
    }
    
    /* Section Styles */
    .downloads-section {
        padding: 60px 0;
    }
    
    .section-title {
        text-align: center;
        margin-bottom: 40px;
    }
    
    .section-title h2 {
        font-size: 32px;
        font-weight: 700;
        color: var(--primary-color);
        position: relative;
        padding-bottom: 15px;
        margin-bottom: 15px;
    }
    
    .section-title h2:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 80px;
        height: 3px;
        background: var(--secondary-color);
    }
    
    .section-title p {
        color: var(--dark-gray);
        font-size: 18px;
        max-width: 700px;
        margin: 0 auto;
    }
    
    /* Card Styles */
    .download-card {
        background-color: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        overflow: hidden;
        margin-bottom: 30px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .download-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }
    
    .card-header {
        padding: 20px;
        background: linear-gradient(135deg, var(--primary-color), #2a3b59);
        color: white;
    }
    
    .card-header h3 {
        margin: 0;
        font-size: 22px;
        font-weight: 600;
        display: flex;
        align-items: center;
    }
    
    .card-header h3 i {
        margin-right: 10px;
    }
    
    .card-body {
        padding: 30px;
    }
    
    .card-description {
        color: var(--dark-gray);
        margin-bottom: 25px;
        font-size: 16px;
        line-height: 1.6;
    }
    
    /* Button Styles */
    .btn-container {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        justify-content: center;
    }
    
    .download-btn {
        padding: 12px 24px;
        border-radius: 4px;
        font-weight: 600;
        text-align: center;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: none;
        cursor: pointer;
        min-width: 180px;
        text-transform: uppercase;
    }
    
    .btn-primary {
        background-color: var(--primary-color);
        color: white;
    }
    
    .btn-success {
        background-color: #4CAF50;
        color: white;
    }
    
    .btn-warning {
        background-color: var(--secondary-color);
        color: white;
    }
    
    .download-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }
    
    .download-btn:active {
        transform: translateY(0);
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
    }
    
    .download-btn i {
        margin-right: 10px;
        font-size: 18px;
    }
    
    /* Modal Styles */
    .modal-content {
        border-radius: 10px;
        overflow: hidden;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    
    .modal-header {
        background: linear-gradient(135deg, var(--primary-color), #2a3b59);
        color: white;
        padding: 20px;
        border-bottom: none;
    }
    
    .modal-title {
        font-weight: 600;
    }
    
    .modal-body {
        padding: 30px;
    }
    
    .btn-close {
        color: white;
        opacity: 1;
        filter: brightness(0) invert(1);
    }
    
    /* Success Animation */
    .success-animation {
        display: inline-block;
        margin: 0 auto;
    }
    
    .checkmark {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: block;
        stroke-width: 2;
        stroke: #4CAF50;
        stroke-miterlimit: 10;
        box-shadow: inset 0px 0px 0px #4CAF50;
        animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
    }
    
    .checkmark__circle {
        stroke-dasharray: 166;
        stroke-dashoffset: 166;
        stroke-width: 2;
        stroke-miterlimit: 10;
        stroke: #4CAF50;
        fill: none;
        animation: stroke .6s cubic-bezier(0.650, 0.000, 0.450, 1.000) forwards;
    }
    
    .checkmark__check {
        transform-origin: 50% 50%;
        stroke-dasharray: 48;
        stroke-dashoffset: 48;
        animation: stroke .3s cubic-bezier(0.650, 0.000, 0.450, 1.000) .8s forwards;
    }
    
    @keyframes stroke {
        100% {
            stroke-dashoffset: 0;
        }
    }
    
    @keyframes scale {
        0%, 100% {
            transform: none;
        }
        50% {
            transform: scale3d(1.1, 1.1, 1);
        }
    }
    
    @keyframes fill {
        100% {
            box-shadow: inset 0px 0px 0px 30px #4CAF50;
        }
    }
    
    /* Categories section */
    .categories-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 40px;
    }
    
    .category-card {
        flex: 1 1 300px;
        background-color: white;
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }
    
    .category-icon {
        font-size: 36px;
        margin-bottom: 15px;
        color: var(--primary-color);
    }
    
    /* Media Queries for Responsiveness */
    @media (max-width: 992px) {
        .section-title h2 {
            font-size: 28px;
        }
        
        .section-title p {
            font-size: 16px;
        }
        
        .card-header h3 {
            font-size: 20px;
        }
    }
    
    @media (max-width: 768px) {
        .section-title h2 {
            font-size: 24px;
        }
        
        .btn-container {
            flex-direction: column;
        }
        
        .download-btn {
            width: 100%;
        }
    }
    
    @media (max-width: 576px) {
        .section-title h2 {
            font-size: 22px;
        }
        
        .breadcrumb-text {
            font-size: 14px;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .card-description {
            font-size: 15px;
        }
    }
</style>

<!-- Breadcrumb Section -->
<div class="breadcrumb-section">
    <div class="downloads-container">
        <div class="breadcrumb-text">
            <a href="index.php"><i class="fa fa-home"></i> Home</a>
            <span>Downloads</span>
        </div>
    </div>
</div>

<!-- Downloads Section -->
<section class="downloads-section">
    <div class="downloads-container">
        <div class="section-title">
            <h2>Document Center</h2>
            <p>Generate and download various letters and documents you need for your applications and requests</p>
        </div>
        
        <!-- Request Letters Card -->
        <div class="download-card">
            <div class="card-header">
                <h3><i class="fas fa-file-alt"></i> Request Letter Generator</h3>
            </div>
            <div class="card-body">
                <p class="card-description">
                    Select the type of letter you need to generate. Our system will create a professional template that you can download or share directly.
                </p>
                <div class="btn-container">
                    <button id="passport-letter-btn" class="download-btn btn-primary">
                        <i class="fas fa-passport"></i>
                        Passport Request
                    </button>
                    <button id="leave-letter-btn" class="download-btn btn-success">
                        <i class="fas fa-calendar-alt"></i>
                        Leave Request
                    </button>
                    <a href="invitationTemplate/invitation_generator.php"><button id="invitation-letter-btn" class="download-btn btn-warning">
                        <i class="fas fa-pen-fancy"></i>
                        Invitation Letter
                    </button></a> 
                </div>
            </div>
        </div>
        
        <!-- Visa Letters Card -->
        <div class="download-card">
            <div class="card-header">
                <h3><i class="fas fa-globe"></i> Visa Documentation</h3>
            </div>
            <div class="card-body">
                <p class="card-description">
                    Download pre-formatted visa-related documents for your international travel needs. All templates follow standard formats required by most embassies.
                </p>
                <div class="btn-container">
                    <button id="visa-request-btn" class="download-btn btn-primary">
                        <i class="fas fa-plane-departure"></i>
                        Visa Request
                    </button>
                    <button id="dependent-visa-btn" class="download-btn btn-success">
                        <i class="fas fa-users"></i>
                        Dependent Letter
                    </button>
                </div>
            </div>
        </div>
        <!-- Visa Letters Card -->
        <div class="download-card">
            <div class="card-header">
                <h3><i class="fas fa-globe"></i>Download Your</h3>
            </div>
            <div class="card-body">
                <p class="card-description">
                    Download pre-formatted visa-related documents for your international travel needs. All templates follow standard formats required by most embassies.
                </p>
                <div class="btn-container">
                    <button id="visa-request-btn" class="download-btn btn-primary">
                        <i class="fas fa-plane-departure"></i>
                        Airticket
                    </button>
                    <button id="dependent-visa-btn" class="download-btn btn-success">
                        <i class="fas fa-users"></i>
                        E - Visa
                    </button>
                </div>
            </div>
        </div>
        
        
</section>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Letter Generated Successfully!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="success-animation">
                        <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                            <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                            <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                        </svg>
                    </div>
                    <h4 class="mt-4">Your letter has been generated successfully!</h4>
                    <p class="text-muted">What would you like to do with your letter?</p>
                </div>
                <div class="btn-container">
                    <button id="download-btn" class="download-btn btn-primary">
                        <i class="fas fa-download"></i> Download
                    </button>
                    <button id="whatsapp-btn" class="download-btn btn-success">
                        <i class="fab fa-whatsapp"></i> Send to WhatsApp
                    </button>
                    <button id="email-btn" class="download-btn btn-warning">
                        <i class="fas fa-envelope"></i> Send via Email
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Reference to buttons
    const passportLetterBtn = document.getElementById('passport-letter-btn');
    const leaveLetterBtn = document.getElementById('leave-letter-btn');
    const invitationLetterBtn = document.getElementById('invitation-letter-btn');
    const visaRequestBtn = document.getElementById('visa-request-btn');
    const dependentVisaBtn = document.getElementById('dependent-visa-btn');
    
    const downloadBtn = document.getElementById('download-btn');
    const whatsappBtn = document.getElementById('whatsapp-btn');
    const emailBtn = document.getElementById('email-btn');
    
    // Reference to modal
    const successModal = new bootstrap.Modal(document.getElementById('successModal'));
    
    let generatedLetterContent = '';
    let letterType = '';
    
    // Add click event to all letter buttons
    const letterButtons = [
        passportLetterBtn, 
        leaveLetterBtn, 
        invitationLetterBtn,
        visaRequestBtn,
        dependentVisaBtn
    ];
    
    letterButtons.forEach(button => {
        if (button) {
            button.addEventListener('click', function() {
                let buttonId = this.id;
                
                // Determine letter type based on button clicked
                if (buttonId === 'passport-letter-btn') {
                    letterType = 'passport';
                    generatedLetterContent = generatePassportLetter();
                } else if (buttonId === 'leave-letter-btn') {
                    letterType = 'leave';
                    generatedLetterContent = generateLeaveLetter();
                } else if (buttonId === 'invitation-letter-btn') {
                    letterType = 'invitation';
                    generatedLetterContent = generateInvitationLetter();
                } else if (buttonId === 'visa-request-btn') {
                    letterType = 'visa';
                    generatedLetterContent = generateVisaLetter();
                } else if (buttonId === 'dependent-visa-btn') {
                    letterType = 'dependent';
                    generatedLetterContent = generateDependentLetter();
                }
                
                // Show success modal
                setTimeout(() => {
                    successModal.show();
                }, 300);
            });
        }
    });
    
    // Download button click event
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function() {
            downloadLetter(generatedLetterContent, letterType);
        });
    }
    
    // WhatsApp button click event
    if (whatsappBtn) {
        whatsappBtn.addEventListener('click', function() {
            sendToWhatsApp(generatedLetterContent);
        });
    }
    
    // Email button click event
    if (emailBtn) {
        emailBtn.addEventListener('click', function() {
            sendViaEmail(generatedLetterContent, letterType);
        });
    }
    
    
    // Function to download letter
    function downloadLetter(content, type) {
        const element = document.createElement('a');
        const file = new Blob([content], {type: 'text/plain'});
        element.href = URL.createObjectURL(file);
        element.download = `${type}_letter.txt`;
        document.body.appendChild(element);
        element.click();
        document.body.removeChild(element);
    }
    
    // Function to send letter to WhatsApp
    function sendToWhatsApp(content) {
        // Encode the content for WhatsApp
        const encodedContent = encodeURIComponent(content);
        const whatsappURL = `https://wa.me/?text=${encodedContent}`;
        window.open(whatsappURL, '_blank');
    }
    
    // Function to send letter via email
    function sendViaEmail(content, type) {
        const subject = encodeURIComponent(`${type.charAt(0).toUpperCase() + type.slice(1)} Letter`);
        const body = encodeURIComponent(content);
        const mailtoLink = `mailto:?subject=${subject}&body=${body}`;
        window.location.href = mailtoLink;
    }
});
</script>

<?php include('footer.php'); ?>