<?php
// Check if language is submitted via GET or POST
if (isset($_GET['lang'])) {
    $lang = $_GET['lang'];
    $_SESSION['site_language'] = $lang;
} elseif (isset($_POST['lang'])) {
    $lang = $_POST['lang'];
    $_SESSION['site_language'] = $lang;
}

// Validate the language
$allowed_languages = ['en', 'si'];
if (!isset($_SESSION['site_language']) || !in_array($_SESSION['site_language'], $allowed_languages)) {
    $_SESSION['site_language'] = 'en'; // Reset to default if invalid
}

// Get current language from session
$lang = $_SESSION['site_language'];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="description" content="EduTourism - Educational Tourism in Sri Lanka">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edutourism.lk</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Muli:300,400,500,600,700,800,900&display=swap" rel="stylesheet">

    <!-- Tab Icon -->
    <link rel="apple-touch-icon" sizes="180x180" href="icon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="icon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="icon/favicon-16x16.png">

    <!-- CSS Styles -->
    <link rel='stylesheet' href='css/bootstrap.min.css' type='text/css'>
    <link rel='stylesheet' href='css/font-awesome.min.css' type='text/css'>
    <link rel='stylesheet' href='css/themify-icons.css' type='text/css'>
    <link rel='stylesheet' href='css/elegant-icons.css' type='text/css'>
    <link rel='stylesheet' href='css/owl.carousel.min.css' type='text/css'>
    <link rel='stylesheet' href='css/slicknav.min.css' type='text/css'>
    <link rel='stylesheet' href='css/header.css' type='text/css'>
</head>

<body>
    <!-- Mobile Overlay -->
    <div class="mobile-overlay"></div>

    <!-- Header Section Begin -->
    <header class="header-section">
        <!-- Main Header -->
        <div class="header-wrapper">
            <div class="container">
                <div class="header-inner">
                    <!-- Logo -->
                    <div class="logo">
                        <a href="index.php">
                            <img src="img/logo.png" alt="EduTourism Logo">
                        </a>
                    </div>

                    <!-- Header Actions -->
                    <div class="header-actions">
                        <?php
                        // Language-based button texts
                        $button_texts = [
                            'en' => [
                                'login' => 'Login',
                                'logout' => 'Log Out',
                                'signup' => 'Sign Up',
                                'myaccount' => 'My Account',
                                'search' => 'Search tours...'
                            ],
                            'si' => [
                                'login' => 'පිවිසුම',
                                'logout' => 'පිටවීම',
                                'signup' => 'ලියාපදිංචි වන්න',
                                'myaccount' => 'මගේ ගිණුම',
                                'search' => 'සංචාර සොයන්න...'
                            ]
                        ];
                        ?>

                        <!-- Language Switcher -->
                        <div class="lang-switcher">
                            <form method="get">
                                <select name="lang" id="language-select" onchange="this.form.submit()">
                                    <option value="en" <?php echo $lang == 'en' ? 'selected' : ''; ?>>English</option>
                                    <option value="si" <?php echo $lang == 'si' ? 'selected' : ''; ?>>සිංහල</option>
                                </select>
                                <!-- Preserve any existing GET parameters -->
                                <?php
                                foreach ($_GET as $key => $value) {
                                    if ($key != 'lang') {
                                        echo '<input type="hidden" name="' . htmlspecialchars($key) . '" value="' . htmlspecialchars($value) . '">';
                                    }
                                }
                                ?>
                            </form>
                        </div>

                        <!-- Mobile Menu Button -->
                        <div class="mobile-menu-btn">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <div class="nav-menu-container">
            <!-- Mobile Menu Close Button -->
            <!-- <div class="mobile-menu-close">×</div> -->
            
            <div class="container">
                <nav class="nav-menu">
                    <ul>
                        <?php 
                        // Language-based navigation texts
                        $nav_texts = [
                            'en' => [
                                'home' => 'Home',
                                'tours' => 'Tours',
                                'pasttours' => 'Past Tours',
                                'testimonials' => 'Testimonials',
                                'aboutus' => 'About Us',
                                'contact' => 'Contact Us',
                                'faqs' => 'FAQs'
                            ],
                            'si' => [
                                'home' => 'මුල් පිටුව',
                                'tours' => 'සංචාර',
                                'pasttours' => 'පසු ගමන්',
                                'testimonials' => 'අදහස්',
                                'aboutus' => 'අප ගැන',
                                'contact' => 'සම්බන්ධ වීමට',
                                'faqs' => 'ප්‍රශ්න'
                            ]
                        ];
                        
                        // Navigation menu items
                        $menu_items = [
                            'home' => ['url' => 'index.php', 'has_dropdown' => false],
                            'pasttours' => ['url' => 'pasttours.php', 'has_dropdown' => false],
                            'testimonials' => ['url' => 'testimonials.php', 'has_dropdown' => false],
                            'aboutus' => ['url' => 'aboutus.php', 'has_dropdown' => false],
                            'contact' => ['url' => 'contact.php', 'has_dropdown' => false],
                            'faqs' => ['url' => 'faqs.php', 'has_dropdown' => false]
                        ];

                        // Generate menu items
                        foreach ($menu_items as $key => $item) {
                            // Check for active page
                            $active_class = '';
                            if (isset($active)) {
                                if (strtolower($active) == strtolower($key)) {
                                    $active_class = 'active';
                                }
                            }
                            
                            echo '<li class="' . $active_class . ($item['has_dropdown'] ? ' has-dropdown' : '') . '">';
                            echo '<a href="' . $item['url'] . '">' . $nav_texts[$lang][$key] . '</a>';
                            echo '</li>';
                        }
                        ?>
                    </ul>
                </nav>
            </div>
        </div>
    </header>
    <!-- Header End -->

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const mobileMenuContainer = document.querySelector('.nav-menu-container');
            const mobileOverlay = document.querySelector('.mobile-overlay');
            const mobileCloseBtn = document.querySelector('.mobile-menu-close');
            const body = document.body;
            
            // Function to open mobile menu
            function openMobileMenu() {
                if (mobileMenuBtn && mobileMenuContainer && mobileOverlay) {
                    mobileMenuBtn.classList.add('active');
                    mobileMenuContainer.classList.add('active');
                    mobileOverlay.classList.add('active');
                    body.classList.add('menu-open');
                }
            }
            
            // Function to close mobile menu
            function closeMobileMenu() {
                if (mobileMenuBtn && mobileMenuContainer && mobileOverlay) {
                    mobileMenuBtn.classList.remove('active');
                    mobileMenuContainer.classList.remove('active');
                    mobileOverlay.classList.remove('active');
                    body.classList.remove('menu-open');
                }
            }
            
            // Open/close menu when burger is clicked
            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    if (this.classList.contains('active')) {
                        closeMobileMenu();
                    } else {
                        openMobileMenu();
                    }
                });
            }
            
            
            // Close menu when overlay is clicked
            if (mobileOverlay) {
                mobileOverlay.addEventListener('click', function() {
                    closeMobileMenu();
                });
            }
            
            // Close menu when navigation link is clicked (mobile only)
            const navLinks = document.querySelectorAll('.nav-menu a');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 767) {
                        closeMobileMenu();
                    }
                });
            });
            
            // Close menu on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeMobileMenu();
                }
            });
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth > 767) {
                    closeMobileMenu();
                }
            });

            // Handle dropdowns if you add them later
            const dropdownItems = document.querySelectorAll('.has-dropdown');
            dropdownItems.forEach(item => {
                const link = item.querySelector('a');
                if (link) {
                    link.addEventListener('click', function(e) {
                        if (window.innerWidth <= 767) {
                            if (!item.classList.contains('dropdown-open')) {
                                e.preventDefault();
                                dropdownItems.forEach(otherItem => {
                                    if (otherItem !== item) {
                                        otherItem.classList.remove('dropdown-open');
                                    }
                                });
                                item.classList.add('dropdown-open');
                            }
                        }
                    });
                }
            });
        });
    </script>

    <?php
    // Cart deletion functionality
    if (isset($_GET['delcart'])) {
        $p_id = $_GET['delcart'];
        $query = "DELETE FROM cart WHERE products_id='$p_id'";
        $run_query = mysqli_query($con, $query);
        echo "<script>window.open('index.php','_self')</script>";
    }
    ?>
</body>
</html>
