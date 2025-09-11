<?php
$active = "faqs";
include('db.php');
include("functions.php");
include("language.php"); // Include language handling
include("header.php");

// Initialize and get current language
// $lang = initializeLanguage();

// Fetch active FAQs from database
$faqs_sql = "SELECT * FROM faqs WHERE status = 'active' ORDER BY display_order ASC, created_at DESC";
$faqs_result = mysqli_query($con, $faqs_sql);
?>

<link rel="stylesheet" href="css/faqs.css">

<!-- FAQs Section Begin -->
<div class="faq-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="faq-container">
                    <?php
                    // Multilingual page title
                    $page_texts = [
                        'en' => [
                            'title' => 'Frequently Asked Questions',
                            'no_faqs' => 'No FAQs available at the moment.',
                            'contact_info' => 'Please contact us for any questions.'
                        ],
                        'si' => [
                            'title' => 'නිතර අසන ප්‍රශ්න',
                            'no_faqs' => 'දැනට FAQ නොමැත.',
                            'contact_info' => 'ඕනෑම ප්‍රශ්නයක් සඳහා අප අමතන්න.'
                        ]
                    ];
                    ?>

                    <h1 class="faq-title"><?php echo $page_texts[$lang]['title']; ?></h1>

                    <div class="faq-list">
                        <?php if (mysqli_num_rows($faqs_result) > 0): ?>
                            <?php while ($faq = mysqli_fetch_assoc($faqs_result)): ?>
                                <div class='faq-item'>
                                    <div class='faq-header'>
                                        <span class='faq-question'>
                                            <?php echo htmlspecialchars($faq['question_' . $lang]); ?>
                                        </span>
                                        <span class='faq-icon'>+</span>
                                    </div>
                                    <div class='faq-answer'>
                                        <?php echo htmlspecialchars($faq['answer_' . $lang]); ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <!-- No FAQs available message -->
                            <div class="no-faqs-message text-center py-5">
                                <i class="fa fa-question-circle-o fa-3x text-muted mb-3"></i>
                                <h3 class="text-muted"><?php echo $page_texts[$lang]['no_faqs']; ?></h3>
                                <p class="text-muted"><?php echo $page_texts[$lang]['contact_info']; ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- FAQs Section End -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get all FAQ headers
    const faqHeaders = document.querySelectorAll('.faq-header');
    
    // Add click event listener to each FAQ header
    faqHeaders.forEach(header => {
        header.addEventListener('click', function() {
            // Get parent FAQ item
            const faqItem = this.parentElement;
            
            // Get the answer element
            const answer = this.nextElementSibling;
            
            // Get the icon element
            const icon = this.querySelector('.faq-icon');
            
            // Toggle active class on FAQ item
            faqItem.classList.toggle('active');
            
            // Toggle answer visibility with smooth animation
            if (answer.style.display === 'block') {
                // Close the FAQ
                answer.style.display = 'none';
                icon.textContent = '+';
                icon.classList.remove('rotate');
            } else {
                // Open the FAQ
                answer.style.display = 'block';
                icon.textContent = '-';
                icon.classList.add('rotate');
            }
        });
    });
    
    // Optional: Add keyboard accessibility
    faqHeaders.forEach(header => {
        header.setAttribute('tabindex', '0');
        
        header.addEventListener('keydown', function(e) {
            // Activate on Enter or Space key
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
});
</script>

<style>
/* Additional styles for no FAQs message */
.no-faqs-message {
    padding: 40px 20px;
    background: #f8f9fa;
    border-radius: 10px;
    margin: 20px 0;
}

/* Ensure FAQ styling remains consistent */
.faq-item {
    margin-bottom: 15px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
}

.faq-header {
    padding: 15px 20px;
    background: #f8f9fa;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: background-color 0.3s ease;
}

.faq-header:hover {
    background: #e9ecef;
}

.faq-question {
    font-weight: 600;
    color: #333;
}

.faq-icon {
    font-size: 18px;
    font-weight: bold;
    color: #667eea;
    transition: transform 0.3s ease;
}

.faq-icon.rotate {
    transform: rotate(180deg);
}

.faq-answer {
    padding: 15px 20px;
    background: white;
    display: none;
    border-top: 1px solid #e0e0e0;
    color: #666;
    line-height: 1.6;
}

.faq-item.active .faq-header {
    background: #667eea;
    color: white;
}

.faq-item.active .faq-question {
    color: white;
}

.faq-item.active .faq-icon {
    color: white;
}
</style>

<?php include('footer.php'); ?>
