<?php
session_start();
include("../homepage/functions.php");
include('../homepage/db.php');

// Check if admin is logged in (you may need to adjust this based on your auth system)
// if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
//     header("Location: admin_login.php");
//     exit();
// }

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_tour'])) {
        // Add new tour
        $title_en = mysqli_real_escape_string($con, $_POST['title_en']);
        $title_si = mysqli_real_escape_string($con, $_POST['title_si']);
        $description_en = mysqli_real_escape_string($con, $_POST['description_en']);
        $description_si = mysqli_real_escape_string($con, $_POST['description_si']);
        $destination = mysqli_real_escape_string($con, $_POST['destination']);
        $duration = (int)$_POST['duration'];
        $category = mysqli_real_escape_string($con, $_POST['category']);
        $price = mysqli_real_escape_string($con, $_POST['price']);
        $status = mysqli_real_escape_string($con, $_POST['status']);
        $tour_type = mysqli_real_escape_string($con, $_POST['tour_type']);
        
        // Handle image upload
        $image_path = '';
        if (isset($_FILES['tour_image']) && $_FILES['tour_image']['error'] == 0) {
            $upload_dir = 'img/tours/';
            $file_extension = pathinfo($_FILES['tour_image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['tour_image']['tmp_name'], $upload_path)) {
                $image_path = $upload_path;
            }
        }
        
        $sql = "INSERT INTO tours (title_en, title_si, description_en, description_si, destination, duration, category, price, image_path, status, tour_type, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssissss", $title_en, $title_si, $description_en, $description_si, $destination, $duration, $category, $price, $image_path, $status, $tour_type);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Tour added successfully!";
        } else {
            $error_message = "Error adding tour: " . mysqli_error($con);
        }
        mysqli_stmt_close($stmt);
    }
    
    if (isset($_POST['update_tour'])) {
        // Update existing tour
        $tour_id = (int)$_POST['tour_id'];
        $title_en = mysqli_real_escape_string($con, $_POST['title_en']);
        $title_si = mysqli_real_escape_string($con, $_POST['title_si']);
        $description_en = mysqli_real_escape_string($con, $_POST['description_en']);
        $description_si = mysqli_real_escape_string($con, $_POST['description_si']);
        $destination = mysqli_real_escape_string($con, $_POST['destination']);
        $duration = (int)$_POST['duration'];
        $category = mysqli_real_escape_string($con, $_POST['category']);
        $price = mysqli_real_escape_string($con, $_POST['price']);
        $status = mysqli_real_escape_string($con, $_POST['status']);
        $tour_type = mysqli_real_escape_string($con, $_POST['tour_type']);
        
        // Handle image upload
        $image_update = '';
        if (isset($_FILES['tour_image']) && $_FILES['tour_image']['error'] == 0) {
            $upload_dir = '../homepage/img/tours/';
            $file_extension = pathinfo($_FILES['tour_image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['tour_image']['tmp_name'], $upload_path)) {
                $image_update = ", image_path = '$upload_path'";
            }
        }
        
        $sql = "UPDATE tours SET title_en = ?, title_si = ?, description_en = ?, description_si = ?, destination = ?, duration = ?, category = ?, price = ?, status = ?, tour_type = ? $image_update WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssisssi", $title_en, $title_si, $description_en, $description_si, $destination, $duration, $category, $price, $status, $tour_type, $tour_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Tour updated successfully!";
        } else {
            $error_message = "Error updating tour: " . mysqli_error($con);
        }
        mysqli_stmt_close($stmt);
    }
    
    if (isset($_POST['delete_tour'])) {
        // Delete tour
        $tour_id = (int)$_POST['tour_id'];
        
        // Get image path to delete file
        $sql = "SELECT image_path FROM tours WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $tour_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $tour = mysqli_fetch_assoc($result);
        
        if ($tour && !empty($tour['image_path']) && file_exists($tour['image_path'])) {
            unlink($tour['image_path']);
        }
        mysqli_stmt_close($stmt);
        
        $sql = "DELETE FROM tours WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $tour_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Tour deleted successfully!";
        } else {
            $error_message = "Error deleting tour: " . mysqli_error($con);
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch all tours
$tours_sql = "SELECT * FROM tours ORDER BY created_at DESC";
$tours_result = mysqli_query($con, $tours_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Tour Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .tour-image-preview {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
        }
        .action-buttons {
            white-space: nowrap;
        }
        .table-responsive {
            font-size: 14px;
        }
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .status-active {
            color: #28a745;
        }
        .status-inactive {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1><i class="fas fa-map-marked-alt"></i> Tour Management Admin Panel</h1>
                </div>
                <div class="col-md-6 text-end">
                    <a href="index.php" class="btn btn-light"><i class="fas fa-home"></i> Back to Website</a>
                    <a href="admin_logout.php" class="btn btn-outline-light"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-12">
                <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#addTourModal">
                    <i class="fas fa-plus"></i> Add New Tour
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list"></i> All Tours</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Image</th>
                                <th>Title (EN)</th>
                                <th>Title (SI)</th>
                                <th>Destination</th>
                                <th>Duration</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($tour = mysqli_fetch_assoc($tours_result)): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($tour['image_path']) && file_exists($tour['image_path'])): ?>
                                            <img src="<?php echo $tour['image_path']; ?>" alt="Tour Image" class="tour-image-preview rounded">
                                        <?php else: ?>
                                            <div class="bg-light p-2 rounded text-center" style="width: 100px; height: 60px; line-height: 40px;">
                                                No Image
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($tour['title_en']); ?></td>
                                    <td><?php echo htmlspecialchars($tour['title_si']); ?></td>
                                    <td><?php echo htmlspecialchars($tour['destination']); ?></td>
                                    <td><?php echo $tour['duration']; ?> days</td>
                                    <td><span class="badge bg-primary"><?php echo htmlspecialchars($tour['category']); ?></span></td>
                                    <td><?php echo htmlspecialchars($tour['price']); ?></td>
                                    <td>
                                        <span class="<?php echo $tour['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                            <i class="fas fa-circle"></i> <?php echo ucfirst($tour['status']); ?>
                                        </span>
                                    </td>
                                    <td class="action-buttons">
                                        <button class="btn btn-sm btn-primary" onclick="editTour(<?php echo htmlspecialchars(json_encode($tour)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteTour(<?php echo $tour['id']; ?>, '<?php echo htmlspecialchars($tour['title_en']); ?>')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Tour Modal -->
    <div class="modal fade" id="addTourModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-plus"></i> Add New Tour</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Title (English) *</label>
                                    <input type="text" name="title_en" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Title (Sinhala) *</label>
                                    <input type="text" name="title_si" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Description (English) *</label>
                                    <textarea name="description_en" class="form-control" rows="3" required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Description (Sinhala) *</label>
                                    <textarea name="description_si" class="form-control" rows="3" required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Destination *</label>
                                    <input type="text" name="destination" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Duration (Days) *</label>
                                    <input type="number" name="duration" class="form-control" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Category *</label>
                                    <select name="category" class="form-control" required>
                                        <option value="">Select Category</option>
                                        <option value="management">Upcoming</option>
                                        
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Price</label>
                                    <input type="text" name="price" class="form-control" placeholder="e.g., $1500 or Contact for Price">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Tour Type *</label>
                                    <select name="tour_type" class="form-control" required>
                                        <option value="regular">Regular</option>
                                        <option value="combined">Combined</option>
                                        <option value="premium">Premium</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Status *</label>
                                    <select name="status" class="form-control" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tour Image</label>
                            <input type="file" name="tour_image" class="form-control" accept="image/*">
                            <small class="form-text text-muted">Upload an image for the tour (JPG, PNG, GIF)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_tour" class="btn btn-success">Add Tour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Tour Modal -->
    <div class="modal fade" id="editTourModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="tour_id" id="edit_tour_id">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit"></i> Edit Tour</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Title (English) *</label>
                                    <input type="text" name="title_en" id="edit_title_en" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Title (Sinhala) *</label>
                                    <input type="text" name="title_si" id="edit_title_si" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Description (English) *</label>
                                    <textarea name="description_en" id="edit_description_en" class="form-control" rows="3" required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label">Description (Sinhala) *</label>
                                    <textarea name="description_si" id="edit_description_si" class="form-control" rows="3" required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Destination *</label>
                                    <input type="text" name="destination" id="edit_destination" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Duration (Days) *</label>
                                    <input type="number" name="duration" id="edit_duration" class="form-control" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Category *</label>
                                    <select name="category" id="edit_category" class="form-control" required>
                                        <option value="">Select Category</option>
                                        <option value="management">Upcoming</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Price</label>
                                    <input type="text" name="price" id="edit_price" class="form-control" placeholder="e.g., $1500 or Contact for Price">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Tour Type *</label>
                                    <select name="tour_type" id="edit_tour_type" class="form-control" required>
                                        <option value="regular">Regular</option>
                                        <option value="combined">Combined</option>
                                        <option value="premium">Premium</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">Status *</label>
                                    <select name="status" id="edit_status" class="form-control" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tour Image</label>
                            <input type="file" name="tour_image" class="form-control" accept="image/*">
                            <small class="form-text text-muted">Upload a new image to replace the current one</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_tour" class="btn btn-primary">Update Tour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Tour Modal -->
    <div class="modal fade" id="deleteTourModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="tour_id" id="delete_tour_id">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger"><i class="fas fa-exclamation-triangle"></i> Delete Tour</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete the tour "<strong id="delete_tour_title"></strong>"?</p>
                        <p class="text-danger"><small>This action cannot be undone.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_tour" class="btn btn-danger">Delete Tour</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editTour(tour) {
            document.getElementById('edit_tour_id').value = tour.id;
            document.getElementById('edit_title_en').value = tour.title_en;
            document.getElementById('edit_title_si').value = tour.title_si;
            document.getElementById('edit_description_en').value = tour.description_en;
            document.getElementById('edit_description_si').value = tour.description_si;
            document.getElementById('edit_destination').value = tour.destination;
            document.getElementById('edit_duration').value = tour.duration;
            document.getElementById('edit_category').value = tour.category;
            document.getElementById('edit_price').value = tour.price;
            document.getElementById('edit_tour_type').value = tour.tour_type;
            document.getElementById('edit_status').value = tour.status;
            
            var editModal = new bootstrap.Modal(document.getElementById('editTourModal'));
            editModal.show();
        }

        function deleteTour(id, title) {
            document.getElementById('delete_tour_id').value = id;
            document.getElementById('delete_tour_title').textContent = title;
            
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteTourModal'));
            deleteModal.show();
        }
    </script>
</body>
</html>