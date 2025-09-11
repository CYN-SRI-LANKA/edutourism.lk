<?php
session_start();

// Simple login check
if (!isset($_SESSION['user'])) {
    header('Location: adminmain.php');
    exit();
}

include("../homepage/functions.php");
include('../homepage/db.php');

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
        $year = mysqli_real_escape_string($con, $_POST['year']);
        $status = mysqli_real_escape_string($con, $_POST['status']);
        $tour_type = mysqli_real_escape_string($con, $_POST['tour_type']);
        $tourname = mysqli_real_escape_string($con, $_POST['tourname']);
        $tour_status = mysqli_real_escape_string($con, $_POST['tour_status']);
        $tour_date = mysqli_real_escape_string($con, $_POST['tour_date']);
        $date_range = mysqli_real_escape_string($con, $_POST['date_range']);
        
        // Handle image upload
        $image_path = '';
        if (isset($_FILES['tour_image']) && $_FILES['tour_image']['error'] == 0) {
            $upload_dir = '../homepage/img/tours/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['tour_image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['tour_image']['tmp_name'], $upload_path)) {
                $image_path = 'img/tours/' . $new_filename;
            }
        }
        
        $sql = "INSERT INTO tours (title_en, title_si, description_en, description_si, destination, duration, category, year, image_path, status, tour_type, tourname, tour_status, tour_date, date_range, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "sssssisssssssss", $title_en, $title_si, $description_en, $description_si, $destination, $duration, $category, $year, $image_path, $status, $tour_type, $tourname, $tour_status, $tour_date, $date_range);
        
        if (mysqli_stmt_execute($stmt)) {
            $tour_id = mysqli_insert_id($con);
            // Only create form for upcoming tours
            if ($tour_status == 'upcoming') {
                createTourForm($tour_id, $tourname, $title_en, $destination, $duration);
                $success_message = "Tour added successfully! Form created for: " . $tourname;
            } else {
                $success_message = "Past tour added successfully!";
            }
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
        $year = mysqli_real_escape_string($con, $_POST['year']);
        $status = mysqli_real_escape_string($con, $_POST['status']);
        $tour_type = mysqli_real_escape_string($con, $_POST['tour_type']);
        $tourname = mysqli_real_escape_string($con, $_POST['tourname']);
        $tour_status = mysqli_real_escape_string($con, $_POST['tour_status']);
        $tour_date = mysqli_real_escape_string($con, $_POST['tour_date']);
        $date_range = mysqli_real_escape_string($con, $_POST['date_range']);
        
        // Handle image upload
        $image_update = '';
        if (isset($_FILES['tour_image']) && $_FILES['tour_image']['error'] == 0) {
            $upload_dir = '../homepage/img/tours/';
            $file_extension = pathinfo($_FILES['tour_image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['tour_image']['tmp_name'], $upload_path)) {
                $image_update = ", image_path = 'img/tours/" . $new_filename . "'";
            }
        }
        
        $sql = "UPDATE tours SET title_en = ?, title_si = ?, description_en = ?, description_si = ?, destination = ?, duration = ?, category = ?, year = ?, status = ?, tour_type = ?, tourname = ?, tour_status = ?, tour_date = ?, date_range = ? $image_update WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "sssssissssssssi", $title_en, $title_si, $description_en, $description_si, $destination, $duration, $category, $year, $status, $tour_type, $tourname, $tour_status, $tour_date, $date_range, $tour_id);
        
        if (mysqli_stmt_execute($stmt)) {
            // Update tour form if tourname changed and it's upcoming
            if ($tour_status == 'upcoming') {
                updateTourForm($tour_id, $tourname, $title_en, $destination, $duration);
            }
            $success_message = "Tour updated successfully!";
        } else {
            $error_message = "Error updating tour: " . mysqli_error($con);
        }
        mysqli_stmt_close($stmt);
    }
    
    if (isset($_POST['delete_tour'])) {
        // Delete tour
        $tour_id = (int)$_POST['tour_id'];
        
        // Get tour data for cleanup
        $sql = "SELECT image_path, tourname FROM tours WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $tour_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $tour = mysqli_fetch_assoc($result);
        
        if ($tour) {
            // Delete image file
            if (!empty($tour['image_path']) && file_exists('../homepage/' . $tour['image_path'])) {
                unlink('../homepage/' . $tour['image_path']);
            }
            
            // Delete tour form file
            $form_file = 'tour_forms/form_' . $tour['tourname'] . '.php';
            if (file_exists($form_file)) {
                unlink($form_file);
            }
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

// Function to create tour-specific form
function createTourForm($tour_id, $tourname, $title_en, $destination, $duration) {
    $form_dir = 'tour_forms/';
    if (!file_exists($form_dir)) {
        mkdir($form_dir, 0777, true);
    }
    
    $form_file = $form_dir . 'form_' . $tourname . '.php';
    
    // Read the template form (your existing visa form)
    if (file_exists('visa_form_template.php')) {
        $template = file_get_contents('visa_form_template.php');
        
        // Replace placeholders with tour-specific information
        $form_content = str_replace(
            [
                '{{TOUR_NAME}}',
                '{{TOUR_TITLE}}',
                '{{DESTINATION}}',
                '{{DURATION}}',
                '{{TOUR_ID}}'
            ],
            [
                $tourname,
                $title_en,
                $destination,
                $duration,
                $tour_id
            ],
            $template
        );
        
        // Write the new form file
        file_put_contents($form_file, $form_content);
    }
}

// Function to update tour form
function updateTourForm($tour_id, $tourname, $title_en, $destination, $duration) {
    // Delete old form if exists
    $form_files = glob('tour_forms/form_*.php');
    foreach ($form_files as $file) {
        $content = file_get_contents($file);
        if (strpos($content, "tour_id = $tour_id") !== false) {
            unlink($file);
            break;
        }
    }
    
    // Create new form
    createTourForm($tour_id, $tourname, $title_en, $destination, $duration);
}

// Fetch all tours with filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$tours_sql = "SELECT * FROM tours";
if ($filter == 'upcoming') {
    $tours_sql .= " WHERE tour_status = 'upcoming'";
} elseif ($filter == 'past') {
    $tours_sql .= " WHERE tour_status = 'past'";
}
$tours_sql .= " ORDER BY created_at DESC";
$tours_result = mysqli_query($con, $tours_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Tour Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-header {
            /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
            color: black;
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
        .form-link {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            border: 1px solid #dee2e6;
        }
        .copy-btn {
            font-size: 12px;
        }
        .filter-buttons {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1>Tour Management System</h1>
                </div>
                <div class="col-md-6 text-end">
                    <a href="adminmain.php" class="btn"><i class="fas fa-arrow-left"></i> Main Admin</a>
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
            <div class="col-md-6">
                <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#addTourModal">
                    <i class="fas fa-plus"></i> Add New Tour
                </button>
            </div>
            <div class="col-md-6">
                <div class="filter-buttons text-end">
                    <div class="btn-group" role="group">
                        <a href="?filter=all" class="btn <?php echo $filter == 'all' ? 'btn-primary' : 'btn-outline-primary'; ?>">All Tours</a>
                        <a href="?filter=upcoming" class="btn <?php echo $filter == 'upcoming' ? 'btn-info' : 'btn-outline-info'; ?>">Upcoming</a>
                        <a href="?filter=past" class="btn <?php echo $filter == 'past' ? 'btn-secondary' : 'btn-outline-secondary'; ?>">Past Tours</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list"></i> All Tours (<?php echo ucfirst($filter); ?>)</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Image</th>
                                <th>Title (EN)</th>
                                <th>Tour Name</th>
                                <th>Destination</th>
                                <th>Duration</th>
                                <th>Tour Status</th>
                                <th>Tour Date</th>
                                <th>Category</th>
                                <th>Year</th>
                                <th>Status</th>
                                <th>Form Link</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($tour = mysqli_fetch_assoc($tours_result)): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($tour['image_path']) && file_exists('../homepage/' . $tour['image_path'])): ?>
                                            <img src="../homepage/<?php echo $tour['image_path']; ?>" alt="Tour Image" class="tour-image-preview rounded">
                                        <?php else: ?>
                                            <div class="bg-light p-2 rounded text-center" style="width: 100px; height: 60px; line-height: 40px;">
                                                No Image
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($tour['title_en']); ?></td>
                                    <td><code><?php echo htmlspecialchars($tour['tourname']); ?></code></td>
                                    <td><?php echo htmlspecialchars($tour['destination']); ?></td>
                                    <td><?php echo $tour['duration']; ?> days</td>
                                    <td>
                                        <span class="badge <?php echo $tour['tour_status'] == 'past' ? 'bg-secondary' : 'bg-info'; ?>">
                                            <?php echo ucfirst($tour['tour_status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        if (!empty($tour['tour_date'])) {
                                            echo date('M j, Y', strtotime($tour['tour_date']));
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td><span class="badge bg-primary"><?php echo htmlspecialchars($tour['category']); ?></span></td>
                                    <td><?php echo htmlspecialchars($tour['year']); ?></td>
                                    <td>
                                        <span class="<?php echo $tour['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                            <i class="fas fa-circle"></i> <?php echo ucfirst($tour['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($tour['tour_status'] == 'upcoming'): ?>
                                            <?php 
                                            $form_link = "tour_forms/form_" . $tour['tourname'] . ".php";
                                            $full_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/" . $form_link;
                                            ?>
                                            <div class="form-link">
                                                <small class="text-muted">Form URL:</small><br>
                                                <input type="text" class="form-control form-control-sm mb-1" 
                                                       value="<?php echo $full_url; ?>" 
                                                       id="url_<?php echo $tour['id']; ?>" readonly>
                                                <button class="btn btn-outline-primary btn-sm copy-btn" 
                                                        onclick="copyToClipboard('url_<?php echo $tour['id']; ?>')">
                                                    <i class="fas fa-copy"></i> Copy
                                                </button>
                                                <a href="<?php echo $form_link; ?>" target="_blank" class="btn btn-outline-success btn-sm">
                                                    <i class="fas fa-external-link-alt"></i> Open
                                                </a>
                                                <button class="btn btn-outline-info btn-sm" 
                                                        onclick="shareWhatsApp('<?php echo urlencode($full_url); ?>', '<?php echo urlencode($tour['title_en']); ?>')">
                                                    <i class="fab fa-whatsapp"></i> Share
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">No form for past tours</span>
                                        <?php endif; ?>
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Tour Name (URL-friendly) *</label>
                                    <input type="text" name="tourname" class="form-control" required 
                                           placeholder="e.g., malaysia2025, singapore_trip, etc."
                                           pattern="[a-z0-9_]+" title="Only lowercase letters, numbers, and underscores allowed">
                                    <small class="form-text text-muted">Used for form URL. Use lowercase, no spaces.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Tour Status *</label>
                                    <select name="tour_status" class="form-control" required>
                                        <option value="upcoming">Upcoming Tour</option>
                                        <option value="past">Past Tour</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Tour Date *</label>
                                    <input type="date" name="tour_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Date Range Display</label>
                                    <input type="text" name="date_range" class="form-control" 
                                           placeholder="e.g., 19th to 25th November">
                                    <small class="form-text text-muted">How dates will appear on the website</small>
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
                                        <option value="upcoming">Upcoming</option>
                                        <option value="popular">Popular</option>
                                        <option value="premium">Premium</option>
                                        <option value="counsellor">Counsellor</option>
                                        <option value="beautician">Beautician</option>
                                        <option value="management">Management</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">year</label>
                                    <input type="text" name="year" class="form-control" placeholder="year">
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Tour Name (URL-friendly) *</label>
                                    <input type="text" name="tourname" id="edit_tourname" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Tour Status *</label>
                                    <select name="tour_status" id="edit_tour_status" class="form-control" required>
                                        <option value="upcoming">Upcoming Tour</option>
                                        <option value="past">Past Tour</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Tour Date *</label>
                                    <input type="date" name="tour_date" id="edit_tour_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Date Range Display</label>
                                    <input type="text" name="date_range" id="edit_date_range" class="form-control">
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
                                        <option value="upcoming">Upcoming</option>
                                        <option value="popular">Popular</option>
                                        <option value="premium">Premium</option>
                                        <option value="counsellor">Counsellor</option>
                                        <option value="beautician">Beautician</option>
                                        <option value="management">Management</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="form-label">year</label>
                                    <input type="text" name="year" id="edit_year" class="form-control">
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
                        <p class="text-danger"><small>This action will also delete the associated form file and cannot be undone.</small></p>
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
            document.getElementById('edit_tourname').value = tour.tourname;
            document.getElementById('edit_tour_status').value = tour.tour_status;
            document.getElementById('edit_tour_date').value = tour.tour_date;
            document.getElementById('edit_date_range').value = tour.date_range || '';
            document.getElementById('edit_description_en').value = tour.description_en;
            document.getElementById('edit_description_si').value = tour.description_si;
            document.getElementById('edit_destination').value = tour.destination;
            document.getElementById('edit_duration').value = tour.duration;
            document.getElementById('edit_category').value = tour.category;
            document.getElementById('edit_year').value = tour.year;
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

        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            element.select();
            element.setSelectionRange(0, 99999);
            document.execCommand('copy');
            
            // Show feedback
            const btn = event.target.closest('button');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            btn.classList.add('btn-success');
            btn.classList.remove('btn-outline-primary');
            
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            }, 2000);
        }

        function shareWhatsApp(url, title) {
            const message = `Check out this tour: ${title}\n\nApply here: ${url}`;
            const whatsappUrl = `https://wa.me/?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }
    </script>
</body>
</html>