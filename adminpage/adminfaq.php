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
    if (isset($_POST['add_faq'])) {
        // Add new FAQ
        $question_en = mysqli_real_escape_string($con, $_POST['question_en']);
        $question_si = mysqli_real_escape_string($con, $_POST['question_si']);
        $answer_en = mysqli_real_escape_string($con, $_POST['answer_en']);
        $answer_si = mysqli_real_escape_string($con, $_POST['answer_si']);
        $status = mysqli_real_escape_string($con, $_POST['status']);
        $display_order = (int)$_POST['display_order'];
        
        $sql = "INSERT INTO faqs (question_en, question_si, answer_en, answer_si, status, display_order) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "sssssi", $question_en, $question_si, $answer_en, $answer_si, $status, $display_order);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "FAQ added successfully!";
        } else {
            $error_message = "Error adding FAQ: " . mysqli_error($con);
        }
        mysqli_stmt_close($stmt);
    }
    
    if (isset($_POST['update_faq'])) {
        // Update existing FAQ
        $faq_id = (int)$_POST['faq_id'];
        $question_en = mysqli_real_escape_string($con, $_POST['question_en']);
        $question_si = mysqli_real_escape_string($con, $_POST['question_si']);
        $answer_en = mysqli_real_escape_string($con, $_POST['answer_en']);
        $answer_si = mysqli_real_escape_string($con, $_POST['answer_si']);
        $status = mysqli_real_escape_string($con, $_POST['status']);
        $display_order = (int)$_POST['display_order'];
        
        $sql = "UPDATE faqs SET question_en = ?, question_si = ?, answer_en = ?, answer_si = ?, status = ?, display_order = ? WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "sssssii", $question_en, $question_si, $answer_en, $answer_si, $status, $display_order, $faq_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "FAQ updated successfully!";
        } else {
            $error_message = "Error updating FAQ: " . mysqli_error($con);
        }
        mysqli_stmt_close($stmt);
    }
    
    if (isset($_POST['delete_faq'])) {
        // Delete FAQ
        $faq_id = (int)$_POST['faq_id'];
        
        $sql = "DELETE FROM faqs WHERE id = ?";
        $stmt = mysqli_prepare($con, $sql);
        mysqli_stmt_bind_param($stmt, "i", $faq_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "FAQ deleted successfully!";
        } else {
            $error_message = "Error deleting FAQ: " . mysqli_error($con);
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch all FAQs
$faqs_sql = "SELECT * FROM faqs ORDER BY display_order ASC, created_at DESC";
$faqs_result = mysqli_query($con, $faqs_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - FAQM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-header {
            /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
            color: black;
            padding: 20px 0;
            margin-bottom: 30px;
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
        .order-badge {
            background: #6c757d;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.8em;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1>FAQ Management System</h1>
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
            <div class="col-md-12">
                <button class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#addFaqModal">
                    <i class="fas fa-plus"></i> Add New FAQ
                </button>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-list"></i> All FAQs</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Order</th>
                                <th>Question (EN)</th>
                                <th>Question (SI)</th>
                                <th>Answer Preview</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($faq = mysqli_fetch_assoc($faqs_result)): ?>
                                <tr>
                                    <td><span class="order-badge"><?php echo $faq['display_order']; ?></span></td>
                                    <td><?php echo htmlspecialchars(substr($faq['question_en'], 0, 50)) . '...'; ?></td>
                                    <td><?php echo htmlspecialchars(substr($faq['question_si'], 0, 50)) . '...'; ?></td>
                                    <td><?php echo htmlspecialchars(substr($faq['answer_en'], 0, 80)) . '...'; ?></td>
                                    <td>
                                        <span class="<?php echo $faq['status'] == 'active' ? 'status-active' : 'status-inactive'; ?>">
                                            <i class="fas fa-circle"></i> <?php echo ucfirst($faq['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($faq['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="editFaq(<?php echo htmlspecialchars(json_encode($faq)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteFaq(<?php echo $faq['id']; ?>, '<?php echo htmlspecialchars($faq['question_en']); ?>')">
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

    <!-- Add FAQ Modal -->
    <div class="modal fade" id="addFaqModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-plus"></i> Add New FAQ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Question (English) *</label>
                            <textarea name="question_en" class="form-control" rows="2" required placeholder="Enter question in English"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Question (Sinhala) *</label>
                            <textarea name="question_si" class="form-control" rows="2" required placeholder="Enter question in Sinhala"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Answer (English) *</label>
                            <textarea name="answer_en" class="form-control" rows="4" required placeholder="Enter answer in English"></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Answer (Sinhala) *</label>
                            <textarea name="answer_si" class="form-control" rows="4" required placeholder="Enter answer in Sinhala"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Display Order</label>
                                    <input type="number" name="display_order" class="form-control" value="0" min="0">
                                    <small class="form-text text-muted">Lower numbers appear first</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Status *</label>
                                    <select name="status" class="form-control" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="add_faq" class="btn btn-success">Add FAQ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit FAQ Modal -->
    <div class="modal fade" id="editFaqModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="faq_id" id="edit_faq_id">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-edit"></i> Edit FAQ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label class="form-label">Question (English) *</label>
                            <textarea name="question_en" id="edit_question_en" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Question (Sinhala) *</label>
                            <textarea name="question_si" id="edit_question_si" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Answer (English) *</label>
                            <textarea name="answer_en" id="edit_answer_en" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Answer (Sinhala) *</label>
                            <textarea name="answer_si" id="edit_answer_si" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Display Order</label>
                                    <input type="number" name="display_order" id="edit_display_order" class="form-control" min="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">Status *</label>
                                    <select name="status" id="edit_status" class="form-control" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_faq" class="btn btn-primary">Update FAQ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete FAQ Modal -->
    <div class="modal fade" id="deleteFaqModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="faq_id" id="delete_faq_id">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger"><i class="fas fa-exclamation-triangle"></i> Delete FAQ</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this FAQ?</p>
                        <p><strong>"<span id="delete_faq_question"></span>"</strong></p>
                        <p class="text-danger"><small>This action cannot be undone.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="delete_faq" class="btn btn-danger">Delete FAQ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editFaq(faq) {
            document.getElementById('edit_faq_id').value = faq.id;
            document.getElementById('edit_question_en').value = faq.question_en;
            document.getElementById('edit_question_si').value = faq.question_si;
            document.getElementById('edit_answer_en').value = faq.answer_en;
            document.getElementById('edit_answer_si').value = faq.answer_si;
            document.getElementById('edit_display_order').value = faq.display_order;
            document.getElementById('edit_status').value = faq.status;
            
            var editModal = new bootstrap.Modal(document.getElementById('editFaqModal'));
            editModal.show();
        }

        function deleteFaq(id, question) {
            document.getElementById('delete_faq_id').value = id;
            document.getElementById('delete_faq_question').textContent = question;
            
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteFaqModal'));
            deleteModal.show();
        }
    </script>
</body>
</html>
