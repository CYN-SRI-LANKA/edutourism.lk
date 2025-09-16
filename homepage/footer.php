<?php
// If your app sets $_SESSION['site_language'] elsewhere, you can remove this check.
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

// Get current language (fallback to 'en')
$lang = isset($_SESSION['site_language']) ? $_SESSION['site_language'] : 'en';

// Multilingual content arrays
$footer_texts = [
    'en' => [
        'information' => 'Information',
        'quick_links' => 'Quick Links',
        'additional' => 'Additional',
        'phone' => '077 7138134',
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
        // 'statistics' => 'Statistics View',
        'copyright' => '© 2025 EduTourism. All rights reserved.',
        'language' => 'Language:'
    ],
    'si' => [
        'information' => 'තොරතුරු',
        'quick_links' => 'ඉක්මන් සබැඳි',
        'additional' => 'අමතර',
        'phone' => '077 7138134',
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
        // 'statistics' => 'සංඛ්‍යාලේඛන දසුන',
        'copyright' => '© 2025 EduTourism. සියලුම හිමිකම් ඇවිරිණි.',
        'language' => 'භාෂාව:'
    ]
];

$texts = $footer_texts[$lang];
?>
<head>
  <link rel="stylesheet" href="css/footer.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
</head>

<!-- Footer Section Begin -->
<footer class="footer-section" role="contentinfo">
    <div class="container">
        <!-- Footer Logo Section -->
        <div class="footer-logo-section" style="padding: 20px 0;">
    <div class="footer-logo">
        <a href="index.php" aria-label="EduTourism Home">
            <img src="img/logo.png" alt="EduTourism Logo" style="max-width:270px; height:auto; display:block;">
        </a>
    </div>
</div>


        <!-- Main Footer Content -->
        <div class="footer-content" aria-label="Footer navigation and contact">
            <!-- Information Section -->
            <div class="footer-widget info-widget">
                <h5><?php echo $texts['information']; ?></h5>
                <ul>
                    <li>
                        <a href="tel:<?php echo str_replace([' ', '/'], ['', ','], $texts['phone']); ?>" class="contact-link" aria-label="Call EduTourism">
                            <?php echo $texts['phone']; ?>
                        </a>
                    </li>
                    <li>
                        <a href="mailto:<?php echo $texts['email']; ?>" class="contact-link" aria-label="Email EduTourism">
                            <?php echo $texts['email']; ?>
                        </a>
                    </li>
                    <li>
                        <a href="https://maps.app.goo.gl/5LBALSyHDfszwLou5" target="_blank" rel="noopener" class="contact-link" aria-label="View address on Google Maps">
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
                    <li><a href="pasttours.php"><?php echo $texts['past_tours']; ?></a></li>
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
                    <!-- <li><a href="statistics.php"><?php echo $texts['statistics']; ?></a></li> -->
                </ul>
            </div>
        </div>

        <!-- Social Media & Copyright -->
        <div class="footer-bottom">
            <div class="copyright">
                <p><?php echo $texts['copyright']; ?></p>
            </div>

            <div class="footer-social" aria-label="Social media links">
                <!-- Facebook -->
                <a href="https://www.facebook.com/edutourism.lk/" target="_blank" rel="noopener" aria-label="Facebook" class="social-icon">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" role="img" aria-label="Facebook">
                        <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                    </svg>
                </a>
                
                <!-- Instagram -->
                <a href="https://www.instagram.com/edutourism.lk/" target="_blank" rel="noopener" aria-label="Instagram" class="social-icon">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" role="img" aria-label="Instagram">
                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                    </svg>
                </a>
                
                <!-- LinkedIn -->
                <a href="https://www.linkedin.com/in/edutourism/" target="_blank" rel="noopener" aria-label="LinkedIn" class="social-icon">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" role="img" aria-label="LinkedIn">
                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                    </svg>
                </a>
                
                <!-- YouTube -->
                <a href="https://www.youtube.com/@edutourismLK" target="_blank" rel="noopener" aria-label="YouTube" class="social-icon">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" role="img" aria-label="YouTube">
                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                    </svg>
                </a>
                
                <!-- TikTok -->
                <a href="https://www.tiktok.com/@edutourism.lk" target="_blank" rel="noopener" aria-label="TikTok" class="social-icon">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor" role="img" aria-label="TikTok">
                        <path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.31-4.64 2.93 2.93 0 01.88.13V9.4a6.84 6.84 0 00-.88-.05A6.33 6.33 0 005 20.1a6.34 6.34 0 0010.86-4.43v-7a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1-.1z"/>
                    </svg>
                </a>
            </div>


            <div class="language-switcher" aria-label="Language selector">
                <span><?php echo $texts['language']; ?></span>
                <select id="footer-language-select" onchange="changeLanguage(this.value)" aria-label="Choose language">
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
// Unified language change function using URL API (safe & reliable)
function changeLanguage(language) {
    try {
        const url = new URL(window.location.href);
        url.searchParams.set('lang', language);
        window.location.href = url.toString();
    } catch (e) {
        // Fallback for very old browsers
        var query = window.location.search;
        var hasLang = /([?&])lang=/.test(query);
        if (hasLang) {
            window.location.search = query.replace(/([?&])lang=[^&]*/,'$1lang=' + language);
        } else {
            window.location.search = (query ? query + '&' : '?') + 'lang=' + language;
        }
    }
}

// Set CSS safe-area variables for iOS notches
document.addEventListener('DOMContentLoaded', function() {
    if (CSS.supports('padding: env(safe-area-inset-top)')) {
        document.documentElement.style.setProperty('--safe-area-inset-top', 'env(safe-area-inset-top)');
        document.documentElement.style.setProperty('--safe-area-inset-bottom', 'env(safe-area-inset-bottom)');
    }
});
</script>
