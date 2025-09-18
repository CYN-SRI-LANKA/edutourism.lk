<?php
$active = "testimonials";
include("functions.php");
include("header.php");
include("db.php");

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Get approved reviews from database
$stmt = $pdo->prepare("SELECT * FROM reviews WHERE status = 'approved' ORDER BY created_at DESC");
$stmt->execute();
$reviews = $stmt->fetchAll();

// YouTube embed function
function getYouTubeEmbedURL($url) {
    if (empty($url)) return '';
    
    $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/';
    
    if (preg_match($pattern, $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }
    
    return '';
}
?>

<head>
    <link rel="stylesheet" href="css/aboutus.css">
    <style>
        .video-testimonial {
            margin-top: 20px;
            margin-bottom: 15px;
        }
        .video-testimonial iframe {
            width: 100%;
            height: 200px;
            border-radius: 8px;
            border: none;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        /* Card Container Styling */
        .testimonial-card-wrapper {
            margin-bottom: 30px;
            padding: 0 15px;
        }
        
        .testimonial-box {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: auto;
            min-height: 300px;
            position: relative;
        }
        
        .testimonial-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .testimonial-content {
            margin-bottom: 20px;
        }
        
        .testimonial-content p {
            font-size: 14px;
            line-height: 1.6;
            color: #555;
            font-style: italic;
            position: relative;
            padding: 0 10px;
            margin-bottom: 0;
        }
        
        
        .testimonial-author {
            display: flex;
            align-items: center;
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }
        
        .author-image {
            margin-right: 12px;
            flex-shrink: 0;
        }
        
        .author-image img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #f0f0f0;
        }
        
        .default-avatar {
            width: 50px;
            height: 50px;
            background: #f8f9fa;
            border: 2px solid #f0f0f0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #6c757d;
        }
        
        .author-info {
            flex: 1;
            min-width: 0;
        }
        
        .author-info h4 {
            margin: 0 0 3px 0;
            font-size: 16px;
            font-weight: 600;
            color: #333;
        }
        
        .author-info p {
            margin: 0;
            font-size: 12px;
            color: #666;
            font-style: normal;
        }
        
        .organization {
            font-size: 11px;
            color: #888;
            background: #f8f9fa;
            padding: 2px 6px;
            border-radius: 10px;
            display: inline-block;
            margin-top: 3px;
        }
        
        .testimonial-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 50px;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: #333;
        }
        
        .section-title p {
            font-size: 1.1rem;
            color: #666;
        }
        
        .no-reviews-message {
            padding: 60px 20px;
            text-align: center;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 1200px) {
            .testimonial-container {
                max-width: 100%;
            }
        }
        
        @media (max-width: 992px) {
            .testimonial-box {
                min-height: 250px;
                padding: 25px;
            }
            .video-testimonial iframe {
                height: 180px;
            }
        }
        
        @media (max-width: 768px) {
            .testimonial-box {
                min-height: auto;
                padding: 20px;
                margin-bottom: 20px;
            }
            .video-testimonial iframe {
                height: 200px;
            }
            .section-title h2 {
                font-size: 2rem;
            }
            .testimonial-content p {
                font-size: 15px;
                padding: 0 15px;
            }
            .testimonial-card-wrapper {
                margin-bottom: 20px;
                padding: 0 10px;
            }
        }
        
        @media (max-width: 576px) {
            .video-testimonial iframe {
                height: 180px;
            }
            .testimonial-author {
                flex-direction: column;
                text-align: center;
            }
            .author-image {
                margin-right: 0;
                margin-bottom: 10px;
            }
            .testimonial-box {
                padding: 15px;
            }
        }
    </style>
</head>

<!-- Testimonial Section Begin -->
<div class="testimonial-section">
    <div class="container-fluid">
        <?php
        $testimonial_texts = [
            'en' => [
                'title' => 'What Our Participants Say',
                'subtitle' => 'Authentic experiences from our program participants'
            ],
            'si' => [
                'title' => 'සහභාගීවුවන්ගේ අදහස්',
                'subtitle' => 'අපගේ වැඩසටහන් සහභාගිවන්නන්ගේ සැබෑ අත්දැකීම්'
            ]
        ];
        ?>
        <div class="section-title">
            <h2><?php echo $testimonial_texts[$lang]['title']; ?></h2>
            <p><?php echo $testimonial_texts[$lang]['subtitle']; ?></p>
        </div>

        <div class="testimonial-container">
            <?php if (count($reviews) > 0): ?>
                <div class="row">
                    <?php foreach ($reviews as $review): ?>
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-12">
                            <div class="testimonial-card-wrapper">
                                <div class="testimonial-box">
                                    <div class="testimonial-content">
                                        <p><?php echo nl2br(htmlspecialchars($lang == 'si' && !empty($review['content_si']) ? $review['content_si'] : $review['content_en'])); ?></p>
                                    </div>
                                    
                                    <!-- Add YouTube video embed if available -->
                                    <?php if (!empty($review['youtube_link'] ?? '')): ?>
                                        <?php $embedURL = getYouTubeEmbedURL($review['youtube_link']); ?>
                                        <?php if ($embedURL): ?>
                                            <div class="video-testimonial">
                                                <iframe src="<?php echo $embedURL; ?>" 
                                                        frameborder="0" 
                                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                                        allowfullscreen>
                                                </iframe>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    
                                    <div class="testimonial-author">
                                        <div class="author-image">
                                            <?php if (!empty($review['profile_image'])): ?>
                                                <img src="../adminpage/uploads/reviews/<?php echo htmlspecialchars($review['profile_image']); ?>" 
                                                     alt="<?php echo htmlspecialchars($review['name']); ?>">
                                            <?php else: ?>
                                                <div class="default-avatar">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="author-info">
                                            <h4><?php echo htmlspecialchars($review['name']); ?></h4>
                                            <?php if (!empty($review['position'])): ?>
                                                <p><?php echo htmlspecialchars($review['position']); ?></p>
                                            <?php endif; ?>
                                            <?php if (!empty($review['organization'])): ?>
                                                <span class="organization"><?php echo htmlspecialchars($review['organization']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <!-- No reviews available message -->
                <div class="row">
                    <div class="col-12 text-center py-5">
                        <div class="no-reviews-message">
                            <i class="fa fa-comments-o fa-3x text-muted mb-3"></i>
                            <h3 class="text-muted">
                                <?php echo ($lang == 'si') ? 'දැනට සාක්ෂි නොමැත' : 'No testimonials available at the moment'; ?>
                            </h3>
                            <p class="text-muted">
                                <?php echo ($lang == 'si') ? 'කරුණාකර පසුව නැවත පරීක්ෂා කරන්න.' : 'Please check back later for new testimonials.'; ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- Testimonial Section End -->

<?php include("footer.php"); ?>
