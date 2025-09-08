<?php
$active = "faqs";
include('db.php');
include("functions.php");
include("header.php");
?>
<link rel="stylesheet" href="css/faqs.css">
<!-- Breadcrumb Section Begin -->
<div class="breacrumb-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="breadcrumb-text">
                    <?php
                    // Language-based breadcrumb translations
                    $breadcrumb_texts = [
                        'en' => [
                            'home' => 'Home',
                            'faqs' => 'FAQs'
                        ],
                        'si' => [
                            'home' => 'මුල් පිටුව',
                            'faqs' => 'නිතර අසන පැන'
                        ]
                    ];
                    // Ensure language is set, default to English
                    $lang = isset($_SESSION['site_language']) ? $_SESSION['site_language'] : 'en';
                    ?>
                    <a href="index.php"><i class="fa fa-home"></i> <?php echo $breadcrumb_texts[$lang]['home']; ?></a>
                    <span><?php echo $breadcrumb_texts[$lang]['faqs']; ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Breadcrumb Section End -->

<!-- FAQs Section Begin -->
<div class="faq-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="faq-container">
                    <?php
                    // Multilingual page title and FAQ content
                    $page_texts = [
                        'en' => [
                            'title' => 'Frequently Asked Questions',
                            'faqs' => [
                                "What is included in the tour package?" => "The tour package includes accommodation, transportation, meals, and guided tours.",
                                "How do I book a tour?" => "You can book a tour through our website or by contacting our customer service team.",
                                "What is the cancellation policy?" => "Cancellations made 14 days before the tour date are eligible for a full refund. After that, partial refunds apply.",
                                "Are there any age restrictions?" => "Our tours are open to all ages, but some activities may have age or physical fitness requirements.",
                                "What should I pack for the tour?" => "We recommend packing comfortable clothing, walking shoes, and any personal items you may need. Specific packing lists will be provided for each tour.",
                                "Are meals included in the tour price?" => "Yes, meals are included as mentioned in the tour itinerary.",
                                "How do I get travel insurance for the tour?" => "Travel insurance is not included, but we recommend purchasing it through your local provider.",
                                "Can I customize my tour?" => "Yes, we offer customizable tour packages. Contact us for more details."
                            ]
                        ],
                        'si' => [
                            'title' => 'නිතර අසන පැන',
                            'faqs' => [
                                "සංචාර පැකේජයට කුමක් ඇතුළත් වේ?" => "සංචාර පැකේජයට නවාතැන, ප්‍රවාහන, ආහාර සහ මඟ පෙන්වන සංචාර ඇතුළත් වේ.",
                                "සංචාරයක් කෙසේ වේද?" => "ඔබට අපේ වෙබ් අඩවිය හරහා හෝ අපේ ක්‍රියාශීලි සේවා කණ්ඩායම අමතා සංචාරයක් තේරිය හැක.",
                                "අවලංගු කිරීමේ ප්‍රතිපත්ති/ය කුමක්ද?" => "සංචාර දිනට දින 14කට පෙර අවලංගු කිරීම් සඳහා සම්පුර්ණ නැවත්වීම් සුදුසුකම් ලැබේ. එයින් පසු අර්ධ නැවත්වීම් අදාළ වේ.",
                                "වයස් සීමා තිබේද?" => "අපේ සංචාර සෑම වයසකටම විවෘත වන අතර, සමහර සඳහා කොන්දේසි විය හැක.",
                                "සංචාරය සඳහා මා කුමක් ඇඳුම් දැමිය යුතුද?" => "පහසු ඇඳුම්, පා වැසුම් සහ ඔබට අවශ්‍ය පුද්ගලික අයිතම් ඇඳීමට අපි නිර්දේශ කරමු. සෑම සංචාරයකටම නිශ්චිත පැකේ ලැයිස්තු සපයනු ලැබේ.",
                                "ආහාර සංචාර මිලට ඇතුළත් වේද?" => "ඔව්, සංචාර නිකුතුවේ සඳහන් පරිදි ආහාර ඇතුළත් වේ.",
                                "සංචාර ගමන් රක්ෂණය කෙසේ සපයා ගත යුතුද?" => "ගමන් රක්ෂණය ඇතුළත් නොවේ, නමුත් ඔබේ දේශීය සපයන්නා මඟින් එය මිලදී ගැනීම නිර්දේශ කරමු.",
                                "මගේ සංචාරය අභිමත කළ හැක්කේ කෙසේද?" => "ඔව්, අපි අභිමත සංචාර පැකේජ සපයමු. වැඩි විස්තර සඳහා අප අමතන්න."
                            ]
                        ]
                    ];

                    // Use the current language or default to English
                    $lang = isset($_SESSION['site_language']) ? $_SESSION['site_language'] : 'en';
                    ?>

                    <h1 class="faq-title"><?php echo $page_texts[$lang]['title']; ?></h1>

                    <div class="faq-list">
                        <?php
                        // Display FAQs based on selected language
                        foreach ($page_texts[$lang]['faqs'] as $question => $answer) {
                            echo "
                            <div class='faq-item'>
                                <div class='faq-header'>
                                    <span class='faq-question'>{$question}</span>
                                    <span class='faq-icon'>+</span>
                                </div>
                                <div class='faq-answer'>{$answer}</div>
                            </div>
                            ";
                        }
                        ?>
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

<?php
include('footer.php');
?>