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
?>

<head>
    <link rel="stylesheet" href="css/aboutus.css">
</head>

<!-- Testimonial Section Begin -->
<div class="testimonial-section">
    <div class="container">
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
                <?php foreach ($reviews as $review): ?>
                    <div class="testimonial-box">
                        <div class="testimonial-content">
                            <p><?php echo nl2br(htmlspecialchars($lang == 'si' && $review['content_si'] ? $review['content_si'] : $review['content_en'])); ?></p>
                        </div>
                        <div class="testimonial-author">
                            <div class="author-image">
                                <?php if ($review['profile_image']): ?>
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
                                <?php if ($review['position']): ?>
                                    <p><?php echo htmlspecialchars($review['position']); ?></p>
                                <?php endif; ?>
                                <?php if ($review['organization']): ?>
                                    <span class="organization"><?php echo htmlspecialchars($review['organization']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- No reviews available message -->
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
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- Testimonial Section End -->

<?php include("footer.php"); ?>
