<?php
$active = "home"; 
include("functions.php");
include("language.php");
include("header.php");
include("db.php");
// Fetch active tours from database
$tours_query = "SELECT * FROM tours WHERE status = 'active' AND tour_status = 'upcoming' ORDER BY created_at DESC";
$tours_result = mysqli_query($con, $tours_query);
?>
<head>
  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/tourcard.css">
</head>
<!-- Hero Section Begin -->
<!-- Tours Section for index.php with language support -->
<section class="tour-showcase">
    <div class="tour-container">
        <div class="tour-section-heading">
            <?php
            // Language-based tour section translations
            $tour_texts = [
                'en' => [
                    'heading' => 'Up Coming Tours',
                    'subheading' => 'Seats are limited – don\'t miss this opportunity!',
                    'view_all' => 'View All Tours',
                    'view_details' => 'View Details',
                    'days' => 'Days',
                    'combined' => 'Combined',
                    'premium' => 'Premium',
                    'regular' => 'Regular'
                ],
                'si' => [
                    'heading' => 'ඉදිරි සංචාර',
                    'subheading' => 'ඉඩ සීමිතයි - මෙම අවස්ථාව මඟ නොහරින්න!',
                    'view_all' => 'සියලුම සංචාර බලන්න',
                    'view_details' => 'විස්තර බලන්න',
                    'days' => 'දින',
                    'combined' => 'ඒකාබද්ධ',
                    'premium' => 'ප්‍රිමියම්',
                    'regular' => 'සාමාන්‍ය'
                ]
            ];

            // Ensure language is set, default to English
            $lang = isset($_SESSION['site_language']) ? $_SESSION['site_language'] : 'en';
            ?>
            <h2><?php echo $tour_texts[$lang]['heading']; ?></h2>
            <p><?php echo $tour_texts[$lang]['subheading']; ?></p>
        </div>
        
        <div class="tour-cards-wrapper">
            <?php if (mysqli_num_rows($tours_result) > 0): ?>
                <?php while ($tour = mysqli_fetch_assoc($tours_result)): ?>
                    <!-- Dynamic Tour Card -->
                    <div class="tour-card" data-destination="<?php echo htmlspecialchars($tour['category']); ?>" data-category="<?php echo htmlspecialchars($tour['category']); ?>">
                        <a href="<?php echo ($tour['category'] == 'management') ? 'visa_documents.php' : 'tour-details.php?tour=' . $tour['id']; ?>" class="tour-card-link">
                            <div class="tour-card-inner">
                                <div class="tour-card-image">
                                    <?php if ($tour['tour_type'] == 'combined'): ?>
                                        <div class="tour-discount-badge"><?php echo $tour_texts[$lang]['combined']; ?></div>
                                    <?php elseif ($tour['tour_type'] == 'premium'): ?>
                                        <div class="tour-discount-badge premium"><?php echo $tour_texts[$lang]['premium']; ?></div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($tour['image_path']) && file_exists($tour['image_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($tour['image_path']); ?>" alt="<?php echo htmlspecialchars($tour['title_' . $lang]); ?>">
                                    <?php else: ?>
                                        <img src="img/tours/default-tour.png" alt="<?php echo htmlspecialchars($tour['title_' . $lang]); ?>">
                                    <?php endif; ?>
                                </div>
                                <div class="tour-card-content">
                                    <div class="tour-card-header">
                                        <h3 class="tour-title"><?php echo htmlspecialchars($tour['title_' . $lang]); ?></h3>
                                    </div>
                                    <div class="tour-details">
                                        <div class="tour-meta">
                                            <span><i class="fa fa-clock-o"></i> <?php echo $tour['duration']; ?> <?php echo $tour_texts[$lang]['days']; ?></span>
                                            <span><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($tour['destination']); ?></span>
                                        </div>
                                        <p class="tour-description"><?php echo htmlspecialchars($tour['description_' . $lang]); ?></p>
                                    </div>
                                    <div class="tour-card-footer">
                                        <div class="tour-price">
                                            <?php if (!empty($tour['price'])): ?>
                                                <span class="price"><?php echo htmlspecialchars($tour['price']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="tour-action">
                                            <span><?php echo $tour_texts[$lang]['view_details']; ?></span>
                                            <i class="fa fa-arrow-right"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <!-- No tours available message -->
                <div class="col-12 text-center py-5">
                    <div class="no-tours-message">
                        <i class="fa fa-map-o fa-3x text-muted mb-3"></i>
                        <h3 class="text-muted">
                            <?php echo ($lang == 'si') ? 'දැනට සංචාර නොමැත' : 'No tours available at the moment'; ?>
                        </h3>
                        <p class="text-muted">
                            <?php echo ($lang == 'si') ? 'කරුණාකර පසුව නැවත පරීක්ෂා කරන්න.' : 'Please check back later for new tours.'; ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    
    </div>
</section>

<?php
// Reset the result pointer for any additional usage
mysqli_data_seek($tours_result, 0);
include("footer.php");
?>

<script>
$(document).ready(function() {
    // Initialize Owl Carousel with responsive settings
    $('.hero-items').owlCarousel({
        loop: true,
        margin: 0,
        nav: true,
        dots: true,
        autoplay: true,
        autoplayTimeout: 5000,
        autoplayHoverPause: true,
        smartSpeed: 1200,
        responsive: {
            0: {
                items: 1
            },
            576: {
                items: 1
            },
            992: {
                items: 1
            }
        }
    });
    
    // Apply background images from data-setbg attribute
    $('.set-bg').each(function() {
        var bg = $(this).data('setbg');
        $(this).css('background', 'url(' + bg + ')');
    });

    // Add fade-in animation to tour cards
    $('.tour-card').each(function(index) {
        $(this).css('animation-delay', (index * 0.2) + 's');
        $(this).addClass('animate-fade-in');
    });
});
</script>

<style>
/* Additional styles for dynamic tours */
.tour-discount-badge.premium {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.no-tours-message {
    padding: 40px 20px;
    border-radius: 10px;
    margin: 20px;
}

.animate-fade-in {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.6s ease forwards;
}

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.tour-card:hover {
    transform: translateY(-5px);
    transition: all 0.3s ease;
}

.tour-card-image img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px 8px 0 0;
}

.price {
    font-weight: bold;
    color: #e74c3c;
    font-size: 1.1em;
}
</style>