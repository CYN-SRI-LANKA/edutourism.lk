<?php
session_start();

// Direct Database Connection - Replace with your actual database details
$host = 'localhost';
$dbname = 'edutouri_edutourism_lk';    // Change this to your actual database name
$username = 'root';               // Change this to your database username
$password = '';                   // Change this to your database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// Create upload directories if they don't exist
if (!file_exists('uploads/reviews')) {
    mkdir('uploads/reviews', 0755, true);
}
if (!file_exists('uploads/reviews/thumbnails')) {
    mkdir('uploads/reviews/thumbnails', 0755, true);
}

// Rest of your code continues here...


// <?php
// session_start();
// include('../homepage/db.php');

$action = $_GET['action'] ?? 'list';
$message = '';
$error = '';

// Create upload directories if they don't exist
if (!file_exists('uploads/reviews')) {
    mkdir('uploads/reviews', 0755, true);
}
if (!file_exists('uploads/reviews/thumbnails')) {
    mkdir('uploads/reviews/thumbnails', 0755, true);
}

// Handle form submissions
if ($_POST) {
    if (isset($_POST['add_review'])) {
        $name = trim($_POST['name']);
        $position = trim($_POST['position']);
        $organization = trim($_POST['organization']);
        $content_en = trim($_POST['content_en']);
        $content_si = trim($_POST['content_si']);
        $rating = (int)$_POST['rating'];
        $status = $_POST['status'];
        
        // Handle file upload
        $profile_image = '';
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
            $upload_dir = 'uploads/reviews/';
            $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
            $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            
            if (in_array($file_extension, $allowed_types)) {
                $file_name = 'review_' . time() . '_' . uniqid() . '.' . $file_extension;
                $upload_path = $upload_dir . $file_name;
                
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                    $profile_image = $file_name;
                    
                    // Create thumbnail
                    createThumbnail($upload_path, 'uploads/reviews/thumbnails/' . $file_name, 150, 150);
                } else {
                    $error = 'Failed to upload image.';
                }
            } else {
                $error = 'Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.';
            }
        }
        
        if (!$error) {
            $stmt = $pdo->prepare("INSERT INTO reviews (name, position, organization, content_en, content_si, profile_image, rating, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            if ($stmt->execute([$name, $position, $organization, $content_en, $content_si, $profile_image, $rating, $status])) {
                $message = 'Review added successfully!';
            } else {
                $error = 'Failed to add review to database.';
            }
        }
    }
    
    if (isset($_POST['update_status'])) {
        $review_id = (int)$_POST['review_id'];
        $new_status = $_POST['new_status'];
        
        $stmt = $pdo->prepare("UPDATE reviews SET status = ? WHERE id = ?");
        if ($stmt->execute([$new_status, $review_id])) {
            $message = 'Review status updated successfully!';
        } else {
            $error = 'Failed to update review status.';
        }
    }
    
    if (isset($_POST['delete_review'])) {
        $review_id = (int)$_POST['review_id'];
        
        // Get current image to delete
        $stmt = $pdo->prepare("SELECT profile_image FROM reviews WHERE id = ?");
        $stmt->execute([$review_id]);
        $review = $stmt->fetch();
        
        if ($review && $review['profile_image']) {
            if (file_exists('uploads/reviews/' . $review['profile_image'])) {
                unlink('uploads/reviews/' . $review['profile_image']);
            }
            if (file_exists('uploads/reviews/thumbnails/' . $review['profile_image'])) {
                unlink('uploads/reviews/thumbnails/' . $review['profile_image']);
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
        if ($stmt->execute([$review_id])) {
            $message = 'Review deleted successfully!';
        } else {
            $error = 'Failed to delete review.';
        }
    }
}

// Get all reviews for listing
$reviews = [];
$stmt = $pdo->prepare("SELECT * FROM reviews ORDER BY created_at DESC");
$stmt->execute();
$reviews = $stmt->fetchAll();

// Image resize function
function createThumbnail($source, $destination, $width, $height) {
    $info = getimagesize($source);
    $mime = $info['mime'];
    
    switch ($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($source);
            break;
        default:
            return false;
    }
    
    $thumb = imagecreatetruecolor($width, $height);
    
    // Preserve transparency for PNG and GIF
    if ($mime == 'image/png' || $mime == 'image/gif') {
        imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
        imagealphablending($thumb, false);
        imagesavealpha($thumb, true);
    }
    
    imagecopyresampled($thumb, $image, 0, 0, 0, 0, $width, $height, imagesx($image), imagesy($image));
    
    // Create directory if it doesn't exist
    $thumb_dir = dirname($destination);
    if (!file_exists($thumb_dir)) {
        mkdir($thumb_dir, 0755, true);
    }
    
    switch ($mime) {
        case 'image/jpeg':
            imagejpeg($thumb, $destination, 90);
            break;
        case 'image/png':
            imagepng($thumb, $destination);
            break;
        case 'image/gif':
            imagegif($thumb, $destination);
            break;
    }
    
    imagedestroy($image);
    imagedestroy($thumb);
    return true;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - RMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .review-card {
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        .review-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .profile-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
        }
        .status-badge {
            font-size: 0.8rem;
        }
        .btn-action {
            margin: 2px;
            font-size: 0.8rem;
        }
        .default-avatar {
            width: 80px;
            height: 80px;
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #6c757d;
        }
    </style>
</head>
<body class="bg-light">

<div class="admin-header">
    <div class="container">
        <h1>Review Management System</h1>
        <p class="mb-0">Manage customer testimonials and reviews</p>
    </div>
</div>

<div class="container">
    <!-- Alert Messages -->
    <?php if ($message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Action Buttons -->
    <div class="row mb-4">
        <div class="col-md-6">
            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#addReviewModal">
                <i class="fas fa-plus me-2"></i>Add New Review
            </button>
        </div>
        <div class="col-md-6 text-end">
            <div class="btn-group">
                <button class="btn btn-outline-secondary" onclick="filterReviews('all')">All Reviews (<?php echo count($reviews); ?>)</button>
                <button class="btn btn-outline-success" onclick="filterReviews('approved')">Approved</button>
                <button class="btn btn-outline-warning" onclick="filterReviews('pending')">Pending</button>
                <button class="btn btn-outline-danger" onclick="filterReviews('rejected')">Rejected</button>
            </div>
        </div>
    </div>

    <!-- Reviews List -->
    <div class="row">
        <?php if (empty($reviews)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <h5><i class="fas fa-info-circle me-2"></i>No Reviews Found</h5>
                    <p>Click "Add New Review" to create your first review.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
            <div class="col-md-6 col-lg-4 review-item" data-status="<?php echo $review['status']; ?>">
                <div class="card review-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-start mb-3">
                            <div class="me-3">
                                <?php if ($review['profile_image']): ?>
                                    <img src="uploads/reviews/thumbnails/<?php echo htmlspecialchars($review['profile_image']); ?>" 
                                         class="profile-img" alt="Profile">
                                <?php else: ?>
                                    <div class="default-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?php echo htmlspecialchars($review['name']); ?></h6>
                                <small class="text-muted"><?php echo htmlspecialchars($review['position']); ?></small><br>
                                <small class="text-muted"><?php echo htmlspecialchars($review['organization']); ?></small>
                            </div>
                            <span class="badge bg-<?php 
                                echo $review['status'] == 'approved' ? 'success' : 
                                    ($review['status'] == 'pending' ? 'warning' : 'danger'); 
                            ?> status-badge">
                                <?php echo ucfirst($review['status']); ?>
                            </span>
                        </div>
                        
                        <div class="review-content mb-3">
                            <strong>English:</strong>
                            <p class="small"><?php echo substr(htmlspecialchars($review['content_en']), 0, 150) . (strlen($review['content_en']) > 150 ? '...' : ''); ?></p>
                            <?php if ($review['content_si']): ?>
                            <strong>Sinhala:</strong>
                            <p class="small"><?php echo substr(htmlspecialchars($review['content_si']), 0, 100) . (strlen($review['content_si']) > 100 ? '...' : ''); ?></p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $review['rating'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <small class="text-muted"><?php echo date('M j, Y', strtotime($review['created_at'])); ?></small>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-light">
                        <div class="btn-group-vertical w-100">
                            <div class="btn-group mb-1">
                                <?php if ($review['status'] != 'approved'): ?>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                    <input type="hidden" name="new_status" value="approved">
                                    <button type="submit" name="update_status" class="btn btn-sm btn-success btn-action">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>
                                <?php endif; ?>
                                
                                <?php if ($review['status'] != 'rejected'): ?>
                                <form method="post" class="d-inline">
                                    <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                    <input type="hidden" name="new_status" value="rejected">
                                    <button type="submit" name="update_status" class="btn btn-sm btn-warning btn-action">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                            
                            <div class="btn-group">
                                <button class="btn btn-sm btn-info btn-action" onclick="editReview(<?php echo $review['id']; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                
                                <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this review?')">
                                    <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                    <button type="submit" name="delete_review" class="btn btn-sm btn-danger btn-action">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Add Review Modal -->
<div class="modal fade" id="addReviewModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Add New Review</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name *</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="position" class="form-label">Position</label>
                                <input type="text" class="form-control" id="position" name="position">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="organization" class="form-label">Organization</label>
                                <input type="text" class="form-control" id="organization" name="organization">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="rating" class="form-label">Rating</label>
                                <select class="form-control" id="rating" name="rating">
                                    <option value="5">5 Stars</option>
                                    <option value="4">4 Stars</option>
                                    <option value="3">3 Stars</option>
                                    <option value="2">2 Stars</option>
                                    <option value="1">1 Star</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="approved">Approved</option>
                                    <option value="pending">Pending</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="profile_image" class="form-label">Profile Image</label>
                        <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                        <div class="form-text">Upload JPG, PNG, or GIF files only. Maximum file size: 2MB</div>
                        <div id="imagePreview" class="mt-2"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content_en" class="form-label">Review Content (English) *</label>
                        <textarea class="form-control" id="content_en" name="content_en" rows="4" required></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="content_si" class="form-label">Review Content (Sinhala)</label>
                        <textarea class="form-control" id="content_si" name="content_si" rows="4"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_review" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Review
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function filterReviews(status) {
    const reviewItems = document.querySelectorAll('.review-item');
    
    reviewItems.forEach(item => {
        if (status === 'all' || item.dataset.status === status) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
}

function editReview(reviewId) {
    alert('Edit functionality can be implemented. Review ID: ' + reviewId);
}

// Preview uploaded image
document.getElementById('profile_image').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('imagePreview');
    
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `
                <div class="border rounded p-2">
                    <img src="${e.target.result}" style="max-width: 200px; max-height: 200px;" class="img-thumbnail">
                    <p class="small text-muted mt-1">Preview: ${file.name}</p>
                </div>
            `;
        };
        reader.readAsDataURL(file);
    } else {
        preview.innerHTML = '';
    }
});

// Auto-dismiss alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(alert => {
        if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    });
}, 5000);
</script>

</body>
</html>
