<?php
$active = "pasttours"; // Changed from "Home" to "home" to match the header's key
include("functions.php");
include("header.php");
include("db.php")
?>
<head>
  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/tourcard.css">
</head>
<!-- Tours Section for index.php with language support -->
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
                    'combined' => 'Combined'
                ],
                'si' => [
                    'heading' => 'පසුගිය සංචාර',
                    'subheading' => 'අමතක නොවන සංචාරයන්වල මතකයන් නැවත බැලිය හැක!',
                    'view_all' => 'පසුගිය සංචාර සියල්ල බලන්න',
                    'view_details' => 'සංචාර විස්තර බලන්න',
                    'days' => 'දින',
                    'combined' => 'ඒකාබද්ධ'
                ]
            ];


            // Tour descriptions in both languages
$tour_descriptions = [
    'management' => [
        'en' => 'International Study Tour for Management Professional and Skills Development',
        'si' => 'කළමනාකරණ වෘත්තීය සහ කුසලතා සංවර්ධනය සඳහා ජාත්‍යන්තර අධ්‍යයන සංචාරය'
    ],
    'counsellor' => [
        'en' => 'International Study Tour for Counseling Professional and Skills Development',
        'si' => 'උපදේශන වෘත්තීය සහ කුසලතා සංවර්ධනය සඳහා ජාත්‍යන්තර අධ්‍යයන සංචාරය'
    ],
    'beautician' => [
        'en' => 'International Study Tour for Professional Beauticians - Malaysia',
        'si' => 'වෘත්තීය රූපලාවණ්‍ය විශේෂඥයින් සඳහා ජාත්‍යන්තර අධ්‍යයන සංචාරය - මැලේසියාව'
    ],
    'counsellor_nov' => [
        'en' => 'International Study Tour for Psychology and Counselling Students - Malaysia',
        'si' => 'මැලේසියාවේ මානසික විද්‍යාව සහ උපදේශන ශිෂ්‍යයන් සඳහා ජාත්‍යන්තර අධ්‍යයන සංචාරය'
    ],
    'counsellor_july' => [
        'en' => 'International Professional Study Tour for Professional Psychological Counsellors - Malaysia',
        'si' => 'මැලේසියාවේ වෘත්තීය මානසික උපදේශකයින් සඳහා ජාත්‍යන්තර වෘත්තීය අධ්‍යයන සංචාරය'
    ],
    'counsellor_feb' => [
        'en' => 'International Study Tour for Human Resource Management Students – Malaysia',
        'si' => 'ජාත්‍යන්තර මානව සම්පත් කළමනාකරණ අධ්‍යයන සංචාරය – මැලේසියාව'
    ]
];

// Tour titles in both languages
$tour_titles = [
    'beautician' => [
        'en' => 'Beautician Study Tour - 2024',
        'si' => 'රූපලාවණ්‍යාලාභය අධ්‍යයන සංචාරය - 2024'
    ],
    'counsellor_nov' => [
        'en' => 'Counsellors Study Tour - 2024',
        'si' => 'උපදේශක අධ්‍යයන සංචාරය - 2024'
    ],
    'counsellor_july' => [
        'en' => 'Counsellors Study Tour - 2024',
        'si' => 'උපදේශක අධ්‍යයන සංචාරය - 2024'
    ],
    'counsellor_feb' => [
        'en' => 'Human Resource Management Study Tour - 2025',
        'si' => 'උපදේශක අධ්‍යයන සංචාරය - 2025'
    ]
];

// Tour dates
$tour_dates = [
    'beautician' => [
        'en' => '19th to 25th November',
        'si' => 'නොවැම්බර් 19 සිට 25 දක්වා'
    ],
    'counsellor_nov' => [
        'en' => '05th to 10th November',
        'si' => 'නොවැම්බර් 05 සිට 10 දක්වා'
    ],
    'counsellor_july' => [
        'en' => '17th to 23rd July',
        'si' => 'ජූලි 17 සිට 23 දක්වා'
    ],
    'counsellor_feb' => [
        'en' => '19th to 25th February',
        'si' => 'පෙබරවාරි 19 සිට 25 දක්වා'
    ]
];

// Ensure language is set, default to English
$lang = isset($_SESSION['site_language']) ? $_SESSION['site_language'] : 'en';
?>
<h2><?php echo $tour_texts[$lang]['heading']; ?></h2>
<p><?php echo $tour_texts[$lang]['subheading']; ?></p>
</div>

<div class="tour-cards-wrapper">
    <!-- Tour Card 5 - Counsellor July 2024 -->
    <div class="tour-card" data-destination="counsellor-july" data-category="counsellor">
        <div class="tour-card-inner">
            <div class="tour-card-image">
                <div class="tour-discount-badge">SLPPC Association</div>
                <img src="img/pasttours/MalJulPsy24.png" alt="<?php echo $tour_titles['counsellor_july'][$lang]; ?>">
            </div>
            <div class="tour-card-content">
                <div class="tour-card-header">
                    <h3 class="tour-title"><?php echo $tour_titles['counsellor_july'][$lang]; ?></h3>
                </div>
                <div class="tour-details">
                    <div class="tour-meta">
                        <span><i class="fa fa-calendar"></i> <?php echo $tour_dates['counsellor_july'][$lang]; ?></span>
                        <span><i class="fa fa-map-marker"></i> Malaysia</span>
                    </div>
                    <p class="tour-description"><?php echo $tour_descriptions['counsellor_july'][$lang]; ?></p>
                </div>
                <div class="tour-card-footer">
                    <div class="tour-price">
                        
                    </div>
                    <div class="tour-action">
                        <span><?php echo $tour_texts[$lang]['view_details']; ?></span>
                        <i class="fa fa-arrow-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    

    <!-- Tour Card 4 - Counsellor November 2024 -->
    <div class="tour-card" data-destination="counsellor-nov" data-category="counsellor">
        <div class="tour-card-inner">
            <div class="tour-card-image">
                <div class="tour-discount-badge">Combined</div>
                <img src="img/pasttours/MalNovPsy24.png" alt="<?php echo $tour_titles['counsellor_nov'][$lang]; ?>">
            </div>
            <div class="tour-card-content">
                <div class="tour-card-header">
                    <h3 class="tour-title"><?php echo $tour_titles['counsellor_nov'][$lang]; ?></h3>
                </div>
                <div class="tour-details">
                    <div class="tour-meta">
                        <span><i class="fa fa-calendar"></i> <?php echo $tour_dates['counsellor_nov'][$lang]; ?></span>
                        <span><i class="fa fa-map-marker"></i> Malaysia</span>
                    </div>
                    <p class="tour-description"><?php echo $tour_descriptions['counsellor_nov'][$lang]; ?></p>
                </div>
                <div class="tour-card-footer">
                    <div class="tour-price">
                        
                    </div>
                    <div class="tour-action">
                        <span><?php echo $tour_texts[$lang]['view_details']; ?></span>
                        <i class="fa fa-arrow-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tour Card 3 - Beautician November 2024 -->
    <div class="tour-card" data-destination="beautician" data-category="beautician">
        <div class="tour-card-inner">
            <div class="tour-card-image">
                <img src="img/pasttours/MalNovSal24.png" alt="<?php echo $tour_titles['beautician'][$lang]; ?>">
            </div>
            <div class="tour-card-content">
                <div class="tour-card-header">
                    <h3 class="tour-title"><?php echo $tour_titles['beautician'][$lang]; ?></h3>
                </div>
                <div class="tour-details">
                    <div class="tour-meta">
                        <span><i class="fa fa-calendar"></i> <?php echo $tour_dates['beautician'][$lang]; ?></span>
                        <span><i class="fa fa-map-marker"></i> Malaysia</span>
                    </div>
                    <p class="tour-description"><?php echo $tour_descriptions['beautician'][$lang]; ?></p>
                </div>
                <div class="tour-card-footer">
                    <div class="tour-price">
                        
                    </div>
                    <div class="tour-action">
                        <span><?php echo $tour_texts[$lang]['view_details']; ?></span>
                        <i class="fa fa-arrow-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tour Card 6 - Counsellor February 2025 (LPEC Students) -->
    <div class="tour-card" data-destination="counsellor-feb" data-category="counsellor">
        <div class="tour-card-inner">
            <div class="tour-card-image">
                <div class="tour-discount-badge">LPEC Students</div>
                <img src="img/pasttours/MalFebHRM.png" alt="<?php echo $tour_titles['counsellor_feb'][$lang]; ?>">
            </div>
            <div class="tour-card-content">
                <div class="tour-card-header">
                    <h3 class="tour-title"><?php echo $tour_titles['counsellor_feb'][$lang]; ?></h3>
                </div>
                <div class="tour-details">
                    <div class="tour-meta">
                        <span><i class="fa fa-calendar"></i> <?php echo $tour_dates['counsellor_feb'][$lang]; ?></span>
                        <span><i class="fa fa-map-marker"></i> Malaysia</span>
                    </div>
                    <p class="tour-description"><?php echo $tour_descriptions['counsellor_feb'][$lang]; ?></p>
                </div>
                <div class="tour-card-footer">
                    <div class="tour-price">
                        
                    </div>
                    <div class="tour-action">
                        <span><?php echo $tour_texts[$lang]['view_details']; ?></span>
                        <i class="fa fa-arrow-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
        
        <!-- <div class="tour-view-all">
            <a href="#" class="tour-view-all-btn"><?php echo $tour_texts[$lang]['view_all']; ?> <i class="fa fa-long-arrow-right"></i></a>
        </div> -->
    </div>
</section>
<?php
include("footer.php");
?>
