<head>
  <link rel="stylesheet" href="css/footer.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
</head>
<!-- Footer Section Begin -->
<footer class="footer-section">
    <div class="container">
        <!-- Newsletter Banner -->
        <div class="footer-newsletter">
            <?php
            // Multilingual WhatsApp section
            $whatsapp_texts = [
                'en' => [
                    'title' => 'Connect With Us',
                    'subtitle' => 'Send a message for tour details and special offers.',
                    'placeholder' => 'Enter Your Message',
                    'button' => 'Send'
                ],
                'si' => [
                    'title' => 'අප සමඟ සම්බන්ධ වන්න',
                    'subtitle' => 'සංචාර විස්තර සහ විශේෂ පිරිනැමීම් සඳහා පණිවිඩයක් යවන්න.',
                    'placeholder' => 'ඔබේ පණිවිඩය ඇතුලත් කරන්න',
                    'button' => 'යවන්න'
                ]
            ];

            // Use the current language or default to English
            $lang = isset($_SESSION['site_language']) ? $_SESSION['site_language'] : 'en';
            ?>
            <!-- Removed commented WhatsApp section -->
        </div>

        <!-- Main Footer Content -->
        <div class="footer-content">
            <div class="footer-logo-section">
                <div class="footer-logo">
                    <a href="index.php">
                        <img src="img/logo.png" alt="EduTourism Logo">
                    </a>
                </div>
                <?php
                // Multilingual contact information
                $contact_texts = [
                    'en' => [
                        'phone' => '077 7138134 / 071 8081831',
                        'email' => 'info@edutourism.lk',
                        'location' => '2nd floor, Udeshi City, Kiribathgoda'
                    ],
                    'si' => [
                        'phone' => '+94 7123456789',
                        'email' => 'info@edutourism.lk',
                        'location' => '2 වන මහල, උදේෂි සිටි, කිරිබත්ගොඩ'
                    ]
                ];
                ?>
                <ul class="contact-info">
                    <li><i class="fa fa-phone"></i> <a href="tel:<?php echo str_replace([' ', '/'], ['', ','], $contact_texts[$lang]['phone']); ?>" class="contact-link"><?php echo $contact_texts[$lang]['phone']; ?></a></li>
                    <li><i class="fa fa-envelope"></i> <a href="mailto:<?php echo $contact_texts[$lang]['email']; ?>" class="contact-link"><?php echo $contact_texts[$lang]['email']; ?></a></li>
                    <li><i class="fa fa-map-marker"></i> <a href="https://maps.google.com/?q=<?php echo urlencode($contact_texts[$lang]['location']); ?>" target="_blank" class="contact-link"><?php echo $contact_texts[$lang]['location']; ?></a></li>
                </ul>
            </div>

            <div class="footer-links-section">
                <?php
                // Multilingual Information section
                $info_texts = [
                    'en' => [
                        'title' => 'Information',
                        'links' => [
                            'About Us' => 'aboutus.php',
                            'Contact' => 'contact.php',
                            'Guidelines' => 'guidelines/guidelinehub.php',
                            'FAQ' => 'faqs.php',
                            'Terms & Conditions' => '#'
                        ]
                    ],
                    'si' => [
                        'title' => 'තොරතුරු',
                        'links' => [
                            'අප ගැන' => 'index.php',
                            'සම්බන්ධවන්න' => 'contact.php',
                            'මාර්ගෝපදේශ' => 'guidelines/guidelinehub.php',
                            'නිති අසන පැණ' => 'faq.php',
                            'නියමයන් සහ කොන්දේසි' => 'terms.php'
                        ]
                    ]
                ];

                // Multilingual Tours section
                $tours_texts = [
                    'en' => [
                        'title' => 'Highlights',
                        'links' => [
                            'Last HRM Tour 2nd day' => '#',
                            'Counselling November Putra Jaya 2024' => '#',
                            'SLPCA February 2024' => '#',
                            'Malaysia Culture Explore' => '#',
                            'Fun Moments' => '#'
                        ]
                    ],
                    'si' => [
                        'title' => 'විශේෂ සංචාර අවස්ථා',
                        'links' => [
                            'අවසාන මානව සම්පත් කළමනාකරණ චාරිකාවේ 2 වන දිනය' => '#',
                            'උපදේශන නොවැම්බර් පුත්‍රජය 2024' => '#',
                            'SLPCA පෙබරවාරි 2024' => '#',
                            'මැලේසියානු සංස්කෘතිය ගවේෂණය' => '#',
                            'විනෝදජනක මොහොතවල්' => '#'
                        ]
                    ]
                ];

                // Multilingual Downloads section
                $download_texts = [
                    'en' => [
                        'title' => 'Your Downloads',
                        'login_prompt' => 'Please login to access downloads',
                        'brochure' => 'e-Visa',
                        'guide' => 'Air ticket',
                        'map' => 'Letters'
                    ],
                    'si' => [
                        'title' => 'ඔබේ බාගැනීම්',
                        'login_prompt' => 'බාගැනීම් සඳහා කරුණාකර පිවිසෙන්න',
                        'brochure' => 'e-වීසා',
                        'guide' => 'ගුවන් ටිකට්පත්',
                        'map' => 'ලිපි'
                    ]
                ];
                ?>

                <!-- Quick Links -->
                <div class="footer-widget">
                    <h5><?php echo $info_texts[$lang]['title']; ?></h5>
                    <ul>
                        <?php foreach ($info_texts[$lang]['links'] as $text => $link): ?>
                            <li><a href="<?php echo $link; ?>"><?php echo $text; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Popular Tours -->
                <div class="footer-widget">
                    <h5><?php echo $tours_texts[$lang]['title']; ?></h5>
                    <ul>
                        <?php foreach ($tours_texts[$lang]['links'] as $text => $link): ?>
                            <li><a href="<?php echo $link; ?>"><?php echo $text; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Downloads -->
                <div class="footer-widget" style="display: <?php if (isset($active) && ($active == 'Register' || $active == 'Login')) { echo 'none'; } ?>;">
                    <h5><?php echo $download_texts[$lang]['title']; ?></h5>
                    <ul>
                        <?php 
                        if (!isset($_SESSION['customer_email']) || $_SESSION['customer_email'] == 'unset') {
                            echo "<li><a href='login.php'>" . $download_texts[$lang]['login_prompt'] . "</a></li>";
                        } else {
                            // Only show these links if user is logged in
                            echo "<li><a href='downloads.php'>" . $download_texts[$lang]['brochure'] . "</a></li>";
                            echo "<li><a href='downloads.php'>" . $download_texts[$lang]['guide'] . "</a></li>";
                            echo "<li><a href='downloads.php'>" . $download_texts[$lang]['map'] . "</a></li>";
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Social Media & Copyright -->
        <div class="footer-bottom">
            <?php
            // Multilingual social media labels (for accessibility)
            $social_texts = [
                'en' => [
                    'facebook' => 'Facebook',
                    'instagram' => 'Instagram', 
                    'linkedin' => 'LinkedIn',
                    'youtube' => 'YouTube',
                    'copyright' => '© 2025 EduTourism. All rights reserved.',
                    'language' => 'Language:'
                ],
                'si' => [
                    'facebook' => 'Facebook',
                    'instagram' => 'Instagram', 
                    'linkedin' => 'LinkedIn',
                    'youtube' => 'YouTube',
                    'copyright' => '© 2025 EduTourism. සියලුම හිමිකම් ඇවිරිණි.',
                    'language' => 'භාෂාව:'
                ]
            ];
            ?>

            <div class="copyright">
                <p><?php echo $social_texts[$lang]['copyright']; ?></p>
            </div>

            <div class="footer-social">
                <a href="https://www.facebook.com/edutourism.lk/" target="_blank" aria-label="<?php echo $social_texts[$lang]['facebook']; ?>"><i class="fa fa-facebook"></i></a>
                <a href="https://www.instagram.com/edutourism.lk/" target="_blank" aria-label="<?php echo $social_texts[$lang]['instagram']; ?>"><i class="fa fa-instagram"></i></a>
                <a href="https://www.linkedin.com/in/edutourism/" target="_blank" aria-label="<?php echo $social_texts[$lang]['linkedin']; ?>"><i class="fa fa-linkedin"></i></a>
                <a href="https://www.youtube.com/@edutourismLK" target="_blank" aria-label="<?php echo $social_texts[$lang]['youtube']; ?>"><i class="fa fa-youtube"></i></a>
            </div>

            <div class="language-switcher">
                <span><?php echo $social_texts[$lang]['language']; ?></span>
                <select id="footer-language-select" onchange="changeLanguage(this.value)">
                    <option value="en" <?php echo $lang == 'en' ? 'selected' : ''; ?>>English</option>
                    <option value="si" <?php echo $lang == 'si' ? 'selected' : ''; ?>>සිංහල</option>
                </select>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts with performance improvements -->
<script src="js/jquery-3.3.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.4.4/umd/popper.min.js" integrity="sha512-eUQ9hGdLjBjY3F41CScH3UX+4JDSI9zXeroz7hJ+RteoCaY+GP/LDoM8AO+Pt+DRFw3nXqsjh9Zsts8hnYv8/A==" crossorigin="anonymous"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.zoom.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.4.0/bootbox.min.js" integrity="sha512-8vfyGnaOX2EeMypNMptU+MwwK206Jk1I/tMQV4NkhOz+W8glENoMhGyU6n/6VgQUhQcJH8NqQgHhMtZjJJBv3A==" crossorigin="anonymous"></script>
<script src="js/jquery.slicknav.js"></script>
<script src="js/owl.carousel.min.js"></script>
<script src="js/main.js"></script>

<script>
// Unified language change function that works with both header and footer selectors
function changeLanguage(language) {
    window.location.href = window.location.pathname + (window.location.search ? 
        window.location.search.replace(/([?&])lang=[^&]*(&|$)/, '$1lang=' + language + '$2') : 
        (window.location.search ? window.location.search + '&lang=' + language : '?lang=' + language));
}

// Set correct height for iOS devices with notches
document.addEventListener('DOMContentLoaded', function() {
    // Apply variable height adjustments
    document.documentElement.style.setProperty('--safe-area-inset-top', env(safe-area-inset-top));
    document.documentElement.style.setProperty('--safe-area-inset-bottom', env(safe-area-inset-bottom));
});
</script>