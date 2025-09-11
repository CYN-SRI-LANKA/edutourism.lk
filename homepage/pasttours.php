<?php
$active = "pasttours";
include("functions.php");
include("header.php");
include("db.php");

// Fetch past tours from database
$past_tours_sql = "SELECT * FROM tours WHERE tour_status = 'past' AND status = 'active' ORDER BY tour_date DESC";
$past_tours_result = mysqli_query($con, $past_tours_sql);
?>

<head>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/tourcard.css">
    <style>
        .no-tours-message {
            text-align: center;
            padding: 50px 20px;
            width: 100%;
        }
        .no-tours-message p {
            font-size: 18px;
            color: #666;
            margin: 0;
        }
        .tour-date-display {
            font-weight: 500;
        }
    </style>
</head>

<!-- Tours Section for past tours with language support -->
<section class="tour-showcase">
    <div class="tour-container">
        <div class="tour-section-heading">
            <?php
            // Language-based tour section translations
            $tour_texts = [
                'en' => [
                    'heading' => 'Past Tours',
                    'subheading' => 'Take a look back at our unforgettable journeys!',
                    'view_all' => 'View All Past Tours',
                    'view_details' => 'View Tour Details',
                    'days' => 'Days',
                    'combined' => 'Combined',
                    'no_tours' => 'No past tours available at the moment.',
                    'contact_us' => 'Contact us for more information.'
                ],
                'si' => [
                    'heading' => 'පසුගිය සංචාර',
                    'subheading' => 'අමතක නොවන සංචාරයන්වල මතකයන් නැවත බැලිය හැක!',
                    'view_all' => 'පසුගිය සංචාර සියල්ල බලන්න',
                    'view_details' => 'සංචාර විස්තර බලන්න',
                    'days' => 'දින',
                    'combined' => 'ඒකාබද්ධ',
                    'no_tours' => 'දැනට පසුගිය සංචාර නොමැත.',
                    'contact_us' => 'වැඩි විස්තර සඳහා අපව සම්බන්ධ කරගන්න.'
                ]
            ];

            // Ensure language is set, default to English
            $lang = isset($_SESSION['site_language']) ? $_SESSION['site_language'] : 'en';
            ?>
            <h2><?php echo $tour_texts[$lang]['heading']; ?></h2>
            <p><?php echo $tour_texts[$lang]['subheading']; ?></p>
        </div>

        <div class="tour-cards-wrapper">
            <?php if (mysqli_num_rows($past_tours_result) > 0): ?>
                <?php while ($tour = mysqli_fetch_assoc($past_tours_result)): ?>
                    <div class="tour-card" data-destination="<?php echo $tour['tourname']; ?>" data-category="<?php echo $tour['category']; ?>">
                        <a href = "https://cynsrilanka.org/gallery">
                        <div class="tour-card-inner">
                            <div class="tour-card-image">
                                <?php if ($tour['tour_type'] == 'combined'): ?>
                                    <div class="tour-discount-badge"><?php echo $tour_texts[$lang]['combined']; ?></div>
                                <?php elseif (!empty($tour['category']) && $tour['category'] != 'regular'): ?>
                                    <div class="tour-discount-badge"><?php echo ucfirst($tour['category']); ?></div>
                                <?php endif; ?>
                                
                                <?php if (!empty($tour['image_path']) && file_exists($tour['image_path'])): ?>
                                    <img src="<?php echo $tour['image_path']; ?>" alt="<?php echo $lang == 'si' ? htmlspecialchars($tour['title_si']) : htmlspecialchars($tour['title_en']); ?>">
                                <?php else: ?>
                                    <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <span class="text-muted">No Image</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="tour-card-content">
                                <div class="tour-card-header">
                                    <h3 class="tour-title">
                                        <?php echo $lang == 'si' ? htmlspecialchars($tour['title_si']) : htmlspecialchars($tour['title_en']); ?>
                                    </h3>
                                </div>
                                
                                <div class="tour-details">
                                    <div class="tour-meta">
                                        <span class="tour-date-display">
                                            <i class="fa fa-calendar"></i> 
                                            <?php 
                                            // Use date_range if available, otherwise format tour_date
                                            if (!empty($tour['date_range'])) {
                                                echo htmlspecialchars($tour['date_range']);
                                            } elseif (!empty($tour['tour_date'])) {
                                                $formatted_date = date('F j, Y', strtotime($tour['tour_date']));
                                                echo $formatted_date;
                                            } else {
                                                echo 'Date TBA';
                                            }
                                            ?>
                                        </span>
                                        <span><i class="fa fa-map-marker"></i> <?php echo htmlspecialchars($tour['destination']); ?></span>
                                        <?php if ($tour['duration'] > 0): ?>
                                            <span><i class="fa fa-clock-o"></i> <?php echo $tour['duration']; ?> <?php echo $tour_texts[$lang]['days']; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <p class="tour-description">
                                        <?php 
                                        $description = $lang == 'si' ? $tour['description_si'] : $tour['description_en'];
                                        // Limit description length for card display
                                        if (strlen($description) > 150) {
                                            echo htmlspecialchars(substr($description, 0, 150)) . '...';
                                        } else {
                                            echo htmlspecialchars($description);
                                        }
                                        ?>
                                    </p>
                                </div>
                                
                                <div class="tour-card-footer">
                                    <div class="tour-price">
                                        <?php if (!empty($tour['price'])): ?>
                                            <span><?php echo htmlspecialchars($tour['price']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="tour-action">
                                        <span><?php echo $tour_texts[$lang]['view_details']; ?></span>
                                        <i class="fa fa-arrow-right"></i>
                                    </div>
                                </div>
                            </div>
                        </div></a>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-tours-message">
                    <div class="text-center">
                        <i class="fa fa-calendar-times-o fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted"><?php echo $tour_texts[$lang]['no_tours']; ?><br></h4>
                        <a href="https://cynsrilanka.org/gallery"><h5>See the gallery</h5></a>
                        <p class="text-muted"><?php echo $tour_texts[$lang]['contact_us']; ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include("footer.php"); ?>