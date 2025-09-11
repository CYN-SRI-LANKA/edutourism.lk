<?php
$active = "home"; 
include("functions.php");
include("language.php");
include("header.php");
include("db.php");

// Get tour ID from URL parameter
$tour_id = isset($_GET['tour']) ? (int)$_GET['tour'] : 0;

// Fetch tour details from database
$tour_query = "SELECT * FROM tours WHERE id = $tour_id LIMIT 1";
$tour_result = mysqli_query($con, $tour_query);

// Check if tour exists
if (mysqli_num_rows($tour_result) == 0) {
    // Redirect to index if tour not found
    header('Location: index.php');
    exit();
}

$tour = mysqli_fetch_assoc($tour_result);

// Language support
$lang = isset($_SESSION['site_language']) ? $_SESSION['site_language'] : 'en';

$detail_texts = [
    'en' => [
        'back_to_tours' => 'Back to Tours',
        'tour_details' => 'Tour Details',
        'destination' => 'Destination',
        'duration' => 'Duration',
        'date_range' => 'Date Range',
        'days' => 'Days',
        'tour_type' => 'Tour Type',
        'status' => 'Status',
        'description' => 'Description',
        'combined' => 'Combined Package',
        'premium' => 'Premium Experience',
        'regular' => 'Regular Tour',
        'upcoming' => 'Upcoming',
        'active' => 'Active',
        'contact_us' => 'Contact Us for More Information',
        'overview' => 'Tour Overview'
    ],
    'si' => [
        'back_to_tours' => 'සංචාර වෙත ආපසු',
        'tour_details' => 'සංචාර විස්තර',
        'destination' => 'ගමනාන්තය',
        'duration' => 'කාලසීමාව',
        'date_range' => 'Date Range',
        'days' => 'දින',
        'tour_type' => 'සංචාර වර්ගය',
        'status' => 'තත්ත්වය',
        'description' => 'විස්තර',
        'combined' => 'ඒකාබද්ධ පැකේජය',
        'premium' => 'ප්‍රිමියම් අත්දැකීම',
        'regular' => 'සාමාන්‍ය සංචාරය',
        'upcoming' => 'ඉදිරියට',
        'active' => 'ක්‍රියාත්මක',
        'contact_us' => 'වැඩි විස්තර සඳහා අප අමතන්න',
        'overview' => 'සංචාර දළ විශ්ලේෂණය'
    ]
];
?>

<head>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/tourcard.css">
    <style>
        /* Tour Details Specific Styles */
        .tour-details-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .breadcrumb {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .breadcrumb a {
            color: #007bff;
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .tour-hero {
            position: relative;
            margin-bottom: 40px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .tour-hero-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        .tour-hero-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            color: white;
            padding: 40px 30px 30px;
        }

        .tour-hero h1 {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .tour-type-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .tour-type-badge.combined {
            background: linear-gradient(135deg, #ff7e00, #ff9f40);
        }

        .tour-type-badge.premium {
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .tour-type-badge.regular {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .tour-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .tour-main-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .tour-sidebar {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            height: fit-content;
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #ff7e00;
        }

        .tour-description {
            font-size: 16px;
            line-height: 1.8;
            color: #666;
            margin-bottom: 30px;
        }

        .tour-info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .tour-info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #333;
        }

        .info-value {
            color: #666;
            font-weight: 500;
        }

        .contact-section {
            /* background: linear-gradient(135deg, #667eea, #764ba2); */
            color: black;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            margin-top: 30px;
        }

        .contact-section h3 {
            margin-bottom: 15px;
        }

        .contact-section p {
            margin: 0;
            opacity: 0.9;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .tour-content {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .tour-hero h1 {
                font-size: 2rem;
            }

            .tour-hero-overlay {
                padding: 30px 20px 20px;
            }

            .tour-main-content,
            .tour-sidebar {
                padding: 20px;
            }

            .tour-details-container {
                padding: 10px;
            }
        }

        /* Animation */
        .tour-details-container {
            animation: fadeInUp 0.6s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<div class="tour-details-container">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="index.php">
            <i class="fa fa-arrow-left"></i> <?php echo $detail_texts[$lang]['back_to_tours']; ?>
        </a>
    </div>

    <!-- Tour Hero Section -->
    <div class="tour-hero">
        <?php if (!empty($tour['image_path']) && file_exists($tour['image_path'])): ?>
            <img src="<?php echo htmlspecialchars($tour['image_path']); ?>" alt="<?php echo htmlspecialchars($tour['title_' . $lang]); ?>" class="tour-hero-image">
        <?php else: ?>
            <img src="img/tours/default-tour.png" alt="<?php echo htmlspecialchars($tour['title_' . $lang]); ?>" class="tour-hero-image">
        <?php endif; ?>
        
        <div class="tour-hero-overlay">
            <?php if ($tour['tour_type'] != 'regular'): ?>
                <div class="tour-type-badge <?php echo $tour['tour_type']; ?>">
                    <?php echo $detail_texts[$lang][$tour['tour_type']]; ?>
                </div>
            <?php endif; ?>
            <h1><?php echo htmlspecialchars($tour['title_' . $lang]); ?></h1>
            <div class="">
                <span><i class="fa fa-calendar"></i> <?php echo htmlspecialchars($tour['date_range']); ?></span>&nbsp&nbsp&nbsp&nbsp
                <span><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($tour['destination']); ?></span>
                <span style="margin-left: 20px;"><i class="fa fa-clock-o"></i> <?php echo $tour['duration']; ?> <?php echo $detail_texts[$lang]['days']; ?></span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="tour-content">
        <!-- Left Column - Main Content -->
        <div class="tour-main-content">
            <h2 class="section-title"><?php echo $detail_texts[$lang]['overview']; ?></h2>
            <div class="tour-description">
                <p><?php echo nl2br(htmlspecialchars($tour['description_' . $lang])); ?></p>
            </div>
        </div>

        <!-- Right Column - Sidebar -->
        <div class="tour-sidebar">
            <h3 class="section-title"><?php echo $detail_texts[$lang]['tour_details']; ?></h3>
            
            <div class="tour-info-item">
                <span class="info-label">
                    <i class="fa fa-map-marker"></i> <?php echo $detail_texts[$lang]['destination']; ?>
                </span>
                <span class="info-value"><?php echo htmlspecialchars($tour['destination']); ?></span>
            </div>

            <div class="tour-info-item">
                <span class="info-label">
                    <i class="fa fa-clock-o"></i> <?php echo $detail_texts[$lang]['duration']; ?>
                </span>
                <span class="info-value"><?php echo $tour['duration']; ?> <?php echo $detail_texts[$lang]['days']; ?></span>
            </div>

            <div class="tour-info-item">
                <span class="info-label">
                    <i class="fa fa-calendar"></i> <?php echo $detail_texts[$lang]['date_range']; ?>
                </span>
                <span class="info-value"><?php echo $tour['date_range']; ?> </span>
            </div>

            <div class="tour-info-item">
                <span class="info-label">
                    <i class="fa fa-tag"></i> <?php echo $detail_texts[$lang]['tour_type']; ?>
                </span>
                <span class="info-value"><?php echo $detail_texts[$lang][$tour['tour_type']]; ?></span>
            </div>

            <div class="tour-info-item">
                <span class="info-label">
                    <i class="fa fa-info-circle"></i> <?php echo $detail_texts[$lang]['status']; ?>
                </span>
                <span class="info-value"><?php echo $detail_texts[$lang][$tour['tour_status']]; ?></span>
            </div>

            <!-- Contact Section -->
            <div class="contact-section">
                <h3><?php echo $detail_texts[$lang]['contact_us']; ?></h3>
                <p><i class="fa fa-phone"></i> Call us for booking and inquiries</p>
            </div>
        </div>
    </div>
</div>

<?php include("footer.php"); ?>

<script>
$(document).ready(function() {
    // Smooth scroll for back button
    $('.breadcrumb a').click(function(e) {
        e.preventDefault();
        window.history.back();
    });

    // Add parallax effect to hero image
    $(window).scroll(function() {
        var scrolled = $(window).scrollTop();
        var parallax = $('.tour-hero-image');
        var speed = 0.5;
        
        if (parallax.length) {
            var yPos = -(scrolled * speed);
            parallax.css('transform', 'translate3d(0, ' + yPos + 'px, 0)');
        }
    });
});
</script>
