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
    <link href='https://fonts.googleapis.com/css?family=Sofia' rel='stylesheet'>

    <!-- Tab Icon -->
    <link rel="apple-touch-icon" sizes="180x180" href="icon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="icon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="icon/favicon-16x16.png">

    <!-- Css Styles -->
    <link rel='stylesheet' href='css/bootstrap.min.css' type='text/css'>
    <link rel='stylesheet' href='css/font-awesome.min.css' type='text/css'>
    <link rel='stylesheet' href='css/themify-icons.css' type='text/css'>
    <link rel='stylesheet' href='css/elegant-icons.css' type='text/css'>
    <link rel='stylesheet' href='css/owl.carousel.min.css' type='text/css'>
    <link rel='stylesheet' href='css/slicknav.min.css' type='text/css'>
    <link rel='stylesheet' href='css/header.css' type='text/css'>
    
    <!-- Ensure mobile menu styling for safari and older browsers -->
    <style>
        /* Fix for buttons on mobile */
        @media (max-width: 575px) {
            .user-actions .action-btn span {
                display: inline-block !important; /* Override the display: none in the CSS */
            }
            
            .action-btn {
                width: auto !important; /* Override the fixed width */
                height: auto !important; /* Override the fixed height */
                padding: 6px 10px !important; /* Restore padding */
            }
            
            .action-btn i {
                margin-right: 5px !important; /* Restore margin */
            }
        }
    </style>
</head>

<body>
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
            <div class="container">
                <nav class="nav-menu mobile-menu">
                    <ul>
                        <?php 
                        // Language-based navigation texts
                        $nav_texts = [
                            'en' => [
                                'home' => 'Home',
                                'tours' => 'Tours',
                                'pasttours' => 'Past Tours',
                                'downloads' => 'Downloads',
                                'aboutus' => 'About Us',
                                'contact' => 'Contact Us',
                                'faqs' => 'FAQs'
                            ],
                            'si' => [
                                'home' => 'මුල් පිටුව',
                                'tours' => 'සංචාර',
                                'pasttours' => 'පසු ගමන්',
                                'downloads' => 'බාගත කිරීම්',
                                'aboutus' => 'අප ගැන',
                                'contact' => 'සම්බන්ධ වීමට',
                                'faqs' => 'ප්‍රශ්න'
                            ]
                        ];
                        
                        // Tour categories from index.php
                        
                        
                        // Navigation menu items with dropdown options
                        $menu_items = [
                            'home' => ['url' => 'index.php', 'has_dropdown' => false],
                            'pasttours' => ['url' => 'pasttours.php', 'has_dropdown' => false],
                            'downloads' => ['url' => 'downloads.php', 'has_dropdown' => false],
                            'aboutus' => ['url' => 'aboutus.php', 'has_dropdown' => false],
                            'contact' => ['url' => 'contact.php', 'has_dropdown' => false],
                            'faqs' => ['url' => 'faqs.php', 'has_dropdown' => false]
                        ];

                        // Generate menu items
                        foreach ($menu_items as $key => $item) {
                            // Check for active page - handling both formats of the active variable
                            $active_class = '';
                            if (isset($active)) {
                                // Convert both to lowercase for case-insensitive comparison
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

    <!-- Mobile Menu JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const mobileMenu = document.querySelector('.mobile-menu');
            
            mobileMenuBtn.addEventListener('click', function() {
                this.classList.toggle('active');
                mobileMenu.classList.toggle('active');
            });

            // Handle dropdowns on mobile
            const dropdownItems = document.querySelectorAll('.has-dropdown');
            
            dropdownItems.forEach(item => {
                const link = item.querySelector('a');
                
                // For mobile devices, first click shows dropdown, second navigates
                link.addEventListener('click', function(e) {
                    if (window.innerWidth <= 767) {
                        if (!item.classList.contains('dropdown-open')) {
                            e.preventDefault();
                            // Close all other dropdowns
                            dropdownItems.forEach(otherItem => {
                                if (otherItem !== item) {
                                    otherItem.classList.remove('dropdown-open');
                                }
                            });
                            
                            // Toggle this dropdown
                            item.classList.add('dropdown-open');
                        }
                    }
                });
            });
        });
    </script>

    <?php
    // // Cart deletion functionality
    if (isset($_GET['delcart'])) {
        $p_id = $_GET['delcart'];
        $query = "Delete from cart where products_id='$p_id'";
        $run_query = mysqli_query($con, $query);
        echo "<script>window.open('index.php','_self')</script>";
    }
    ?>