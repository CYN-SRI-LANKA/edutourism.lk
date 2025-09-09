<head>
  <link rel="stylesheet" href="css/footer.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
</head>
<!-- Footer Section Begin -->
<footer class="footer-section">
    <div class="container">
        <!-- Footer Logo Section -->
        <div class="footer-logo-section">
            <div class="footer-logo">
                <a href="index.php">
                    <img src="img/logo.png" alt="EduTourism Logo">
                </a>
            </div>
        </div>

        <!-- Main Footer Content -->
        <div class="footer-content">
            <?php
            // Get current language
            $lang = isset($_SESSION['site_language']) ? $_SESSION['site_language'] : 'en';

            // Multilingual content arrays
            $footer_texts = [
                'en' => [
                    'information' => 'Information',
                    'quick_links' => 'Quick Links',
                    'additional' => 'Additional',
                    'phone' => '077 7138134 / 071 8081831',
                    'email' => 'info@edutourism.lk',
                    'address' => '2nd floor, Udeshi City, Kiribathgoda',
                    'terms' => 'Terms & Conditions',
                    'refunds' => 'Policy Refunds',
                    'home' => 'Home',
                    'past_tours' => 'Past Tours',
                    'downloads' => 'Downloads',
                    'about' => 'About Us',
                    'contact' => 'Contact Us',
                    'faqs' => 'FAQs',
                    'guidelines' => 'Guidelines',
                    'statistics' => 'Statistics View',
                    'copyright' => '© 2025 EduTourism. All rights reserved.',
                    'language' => 'Language:'
                ],
                'si' => [
                    'information' => 'තොරතුරු',
                    'quick_links' => 'ඉක්මන් සබැඳි',
                    'additional' => 'අමතර',
                    'phone' => '077 7138134 / 071 8081831',
                    'email' => 'info@edutourism.lk',
                    'address' => '2 වන මහල, උදේෂි සිටි, කිරිබත්ගොඩ',
                    'terms' => 'නියමයන් සහ කොන්දේසි',
                    'refunds' => 'ප්‍රතිපණ ප්‍රතිපත්තිය',
                    'home' => 'මුල් පිටුව',
                    'past_tours' => 'පසුගිය චාරිකා',
                    'downloads' => 'බාගැනීම්',
                    'about' => 'අප ගැන',
                    'contact' => 'අප වෙත',
                    'faqs' => 'නිති ප්‍රශ්න',
                    'guidelines' => 'මාර්ගෝපදේශ',
                    'statistics' => 'සංඛ්‍යාලේඛන දසුන',
                    'copyright' => '© 2025 EduTourism. සියලුම හිමිකම් ඇවිරිණි.',
                    'language' => 'භාෂාව:'
                ]
            ];

            $texts = $footer_texts[$lang];
            ?>

            <!-- Information Section -->
            <div class="footer-widget info-widget">
                <h5><?php echo $texts['information']; ?></h5>
                <ul>
                    <li>
                        
                        <a href="tel:<?php echo str_replace([' ', '/'], ['', ','], $texts['phone']); ?>" class="contact-link">
                            <?php echo $texts['phone']; ?>
                        </a>
                    </li>
                    <li>
                        
                        <a href="mailto:<?php echo $texts['email']; ?>" class="contact-link">
                            <?php echo $texts['email']; ?>
                        </a>
                    </li>
                    <li>
                        
                        <a href="https://maps.google.com/?q=<?php echo urlencode($texts['address']); ?>" target="_blank" class="contact-link">
                            <?php echo $texts['address']; ?>
                        </a>
                    </li>
                    <li><a href="terms.php"><?php echo $texts['terms']; ?></a></li>
                    <li><a href="refunds.php"><?php echo $texts['refunds']; ?></a></li>
                </ul>
            </div>

            <!-- Quick Links Section -->
            <div class="footer-widget">
                <h5><?php echo $texts['quick_links']; ?></h5>
                <ul>
                    <li><a href="index.php"><?php echo $texts['home']; ?></a></li>
                    <li><a href="past-tours.php"><?php echo $texts['past_tours']; ?></a></li>
                    <?php if (isset($_SESSION['customer_email']) && $_SESSION['customer_email'] != 'unset'): ?>
                        <li><a href="downloads.php"><?php echo $texts['downloads']; ?></a></li>
                    <?php endif; ?>
                    <li><a href="aboutus.php"><?php echo $texts['about']; ?></a></li>
                    <li><a href="contact.php"><?php echo $texts['contact']; ?></a></li>
                    <li><a href="faqs.php"><?php echo $texts['faqs']; ?></a></li>
                </ul>
            </div>

            <!-- Additional Section -->
            <div class="footer-widget">
                <h5><?php echo $texts['additional']; ?></h5>
                <ul>
                    <li><a href="guidelines/guidelinehub.php"><?php echo $texts['guidelines']; ?></a></li>
                    <li><a href="statistics.php"><?php echo $texts['statistics']; ?></a></li>
                </ul>
            </div>
        </div>

        <!-- Social Media & Copyright -->
        <div class="footer-bottom">
            <div class="copyright">
                <p><?php echo $texts['copyright']; ?></p>
            </div>

            <div class="footer-social">
                <a href="https://www.facebook.com/edutourism.lk/" target="_blank" aria-label="Facebook">
                    <i class="fa fa-facebook"></i>
                </a>
                <a href="https://www.instagram.com/edutourism.lk/" target="_blank" aria-label="Instagram">
                    <i class="fa fa-instagram"></i>
                </a>
                <a href="https://www.linkedin.com/in/edutourism/" target="_blank" aria-label="LinkedIn">
                    <i class="fa fa-linkedin"></i>
                </a>
                <a href="https://www.youtube.com/@edutourismLK" target="_blank" aria-label="YouTube">
                    <i class="fa fa-youtube"></i>
                </a>
                <a href="https://www.tiktok.com/@edutourism.lk" target="_blank" aria-label="TikTok" class="tiktok-icon">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                        <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-.88-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z"/>
                    </svg>
                </a>
            </div>

            <div class="language-switcher">
                <span><?php echo $texts['language']; ?></span>
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
    // Apply variable height adjustments for iOS devices
    if (CSS.supports('padding: env(safe-area-inset-top)')) {
        document.documentElement.style.setProperty('--safe-area-inset-top', 'env(safe-area-inset-top)');
        document.documentElement.style.setProperty('--safe-area-inset-bottom', 'env(safe-area-inset-bottom)');
    }
});
</script>