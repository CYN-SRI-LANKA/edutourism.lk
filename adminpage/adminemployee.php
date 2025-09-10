<?php
// admin.php - Enhanced Employee Management System
include('../homepage/db.php');
session_start();

// ===================================
// SUPER ADMIN AUTHENTICATION CHECK
// ===================================
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'super') {
    die('<h1 style="color:red;">❌ Access Denied</h1><p>Only SUPER users can access this panel.</p><p><a href="adminmain.php">← Go to Login</a></p>');
}

$message = '';
$message_type = 'info'; // success, error, info

// Handle Add Employee
if (isset($_POST['add_employee'])) {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];
    $password = $_POST['password'];

    // Validation
    if ($name === '' || $username === '' || $email === '' || $role === '' || $password === '') {
        $message = "All fields are required.";
        $message_type = 'error';
    } else {
        // Check if username already exists
        $check_stmt = $con->prepare("SELECT id FROM employees WHERE username = ?");
        $check_stmt->bind_param("s", $username);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $message = "Username already exists. Please choose a different one.";
            $message_type = 'error';
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $con->prepare("INSERT INTO employees (name, username, email, role, password, active) VALUES (?, ?, ?, ?, ?, 1)");
            $stmt->bind_param("sssss", $name, $username, $email, $role, $hashed_password);
            
            if ($stmt->execute()) {
                $message = "Employee '$name' added successfully with username '$username'.";
                $message_type = 'success';
            } else {
                $message = "Error adding employee: " . $con->error;
                $message_type = 'error';
            }
        }
    }
}

// Handle Remove Employee
if (isset($_POST['remove_employee'])) {
    $emp_id = intval($_POST['emp_id']);
    
    // Get employee name for message
    $name_stmt = $con->prepare("SELECT name FROM employees WHERE id = ?");
    $name_stmt->bind_param("i", $emp_id);
    $name_stmt->execute();
    $name_result = $name_stmt->get_result();
    $emp_name = $name_result->fetch_assoc()['name'] ?? 'Unknown';
    
    // Delete attendance records first (foreign key constraint)
    $con->query("DELETE FROM attendance WHERE employee_id=$emp_id");
    
    $stmt = $con->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->bind_param("i", $emp_id);
    
    if ($stmt->execute()) {
        $message = "Employee '$emp_name' removed successfully.";
        $message_type = 'success';
    } else {
        $message = "Error removing employee: " . $con->error;
        $message_type = 'error';
    }
}

// Handle Role Change
if (isset($_POST['change_role'])) {
    $emp_id = intval($_POST['emp_id']);
    $role = $_POST['role'];

    $stmt = $con->prepare("UPDATE employees SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $emp_id);
    
    if ($stmt->execute()) {
        $message = "Role updated to '$role' successfully.";
        $message_type = 'success';
    } else {
        $message = "Error updating role: " . $con->error;
        $message_type = 'error';
    }
}

// Handle Password Change
if (isset($_POST['change_password'])) {
    $emp_id = intval($_POST['emp_id']);
    $new_password = $_POST['new_password'];
    
    if (empty($new_password)) {
        $message = "Password cannot be empty.";
        $message_type = 'error';
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        $stmt = $con->prepare("UPDATE employees SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed_password, $emp_id);
        
        if ($stmt->execute()) {
            $message = "Password updated successfully.";
            $message_type = 'success';
        } else {
            $message = "Error updating password: " . $con->error;
            $message_type = 'error';
        }
    }
}

// Handle Toggle Active Status
if (isset($_POST['toggle_status'])) {
    $emp_id = intval($_POST['emp_id']);
    $current_status = intval($_POST['current_status']);
    $new_status = $current_status ? 0 : 1;
    
    $stmt = $con->prepare("UPDATE employees SET active = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_status, $emp_id);
    
    if ($stmt->execute()) {
        $status_text = $new_status ? 'activated' : 'deactivated';
        $message = "Employee account $status_text successfully.";
        $message_type = 'success';
    } else {
        $message = "Error updating status: " . $con->error;
        $message_type = 'error';
    }
}

// Fetch all employees
$employees_result = $con->query("SELECT * FROM employees ORDER BY id DESC");

// Attendance Filtering
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

function colorCode($hours) {
    if ($hours === null) return 'gray';
    if ($hours < 8) return 'red';
    if ($hours == 8) return 'yellow';
    if ($hours > 8) return 'green';
    return 'gray';
}

$all_emps = $con->query("SELECT * FROM employees ORDER BY name ASC");
$days_in_month = cal_days_in_month(CAL_GREGORIAN, intval(substr($month, 5, 2)), intval(substr($month, 0, 4)));

$month_dates = [];
for ($d = 1; $d <= $days_in_month; $d++) {
    $month_dates[] = sprintf("%s-%02d", $month, $d);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Panel - EMS</title>
<style>
/* Enhanced CSS Styles */
* { margin: 0; padding: 0; box-sizing: border-box; }
body { 
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
    background: #f8fafc; 
    color: #2d3748; 
    line-height: 1.6; 
}

.header {
    /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
    color: black;
    padding: 2rem 0;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

.header h1 { font-size: 2.5rem; font-weight: 700; margin-bottom: 0.5rem; }
.header p { font-size: 1.1rem; opacity: 0.9; }

.container { max-width: 1200px; margin: 0 auto; padding: 0 1rem; }

/* Message Styling */
.message {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 2rem;
    font-weight: 500;
    border-left: 4px solid;
}
.message.success { background: #f0fff4; color: #22543d; border-color: #38a169; }
.message.error { background: #fed7d7; color: #742a2a; border-color: #e53e3e; }
.message.info { background: #ebf8ff; color: #2a4365; border-color: #3182ce; }

/* Section Styling */
.section {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    margin-bottom: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
    border: 1px solid #e2e8f0;
}

.section h2 { 
    color: #2d3748; 
    margin-bottom: 1.5rem; 
    font-size: 1.5rem; 
    border-bottom: 2px solid #e2e8f0; 
    padding-bottom: 0.5rem; 
}

/* Form Styling */
.form-inline {
    display: flex;
    gap: 1rem;
    align-items: end;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    min-width: 150px;
    flex: 1;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #4a5568;
}

input[type="text"], input[type="email"], input[type="password"], select {
    padding: 0.75rem;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.2s;
}

input:focus, select:focus {
    outline: none;
    border-color: #3182ce;
    box-shadow: 0 0 0 3px rgba(49, 130, 206, 0.1);
}

/* Button Styling */
.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-primary { background: #3182ce; color: white; }
.btn-primary:hover { background: #2c5282; transform: translateY(-1px); }

.btn-success { background: #38a169; color: white; }
.btn-success:hover { background: #2f855a; }

.btn-warning { background: #d69e2e; color: white; }
.btn-warning:hover { background: #b7791f; }

.btn-danger { background: #e53e3e; color: white; }
.btn-danger:hover { background: #c53030; }

.btn-small { padding: 0.5rem 1rem; font-size: 0.875rem; }

/* Table Styling */
.table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

.table th, .table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid #e2e8f0;
}

.table th {
    background: #f7fafc;
    font-weight: 600;
    color: #4a5568;
    text-transform: uppercase;
    font-size: 0.875rem;
    letter-spacing: 0.05em;
}

.table tr:hover { background: #f7fafc; }

/* Status Badge */
.status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 600;
}
.status-active { background: #c6f6d5; color: #22543d; }
.status-inactive { background: #fed7d7; color: #742a2a; }

/* Password Modal */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
}

.modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 2rem;
    border-radius: 12px;
    width: 90%;
    max-width: 400px;
}

/* Responsive */
@media (max-width: 768px) {
    .form-inline { flex-direction: column; }
    .form-group { min-width: 100%; }
    .header h1 { font-size: 2rem; }
    .section { padding: 1rem; }
}

/* Attendance Table */
#attendance-table-container {
    max-width: 100%;
    overflow-x: auto;
    margin-top: 1rem;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.05);
}

#attendance-table th, #attendance-table td {
    min-width: 40px;
    text-align: center;
    padding: 0.5rem;
}

.work-hours {
    font-weight: 600;
    border-radius: 4px;
    padding: 0.25rem;
}
.work-hours.red { background: #fed7d7; color: #742a2a; }
.work-hours.yellow { background: #fefcbf; color: #744210; }
.work-hours.green { background: #c6f6d5; color: #22543d; }
.work-hours.gray { background: #f7fafc; color: #718096; }
</style>
</head>
<body>

<div class="header">
    <div class="container">
        <h1>Employee Management System</h1>
        <!-- <p>Employee Management System</p> -->
        <!-- <p>Logged in as: <strong><?= htmlspecialchars($_SESSION['user']['username']) ?></strong> | Role: <strong><?= htmlspecialchars($_SESSION['user']['role']) ?></strong></p> -->
         <a href="adminmain.php" class="btn" style=' background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px;'>← Back to Dashboard</a>
    </div>
</div>

<div class="container">

<?php if ($message): ?>
    <div class="message <?= $message_type ?>">
        <?php 
        if ($message_type === 'success') echo '✅ ';
        elseif ($message_type === 'error') echo '❌ ';
        else echo 'ℹ️ ';
        echo htmlspecialchars($message); 
        ?>
    </div>
<?php endif; ?>

<!-- Add Employee Form -->
<div class="section">
    <h2>➕ Add New Employee</h2>
    <form method="post" class="form-inline">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" placeholder="John Doe" required>
        </div>
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" placeholder="johndoe" required>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" placeholder="john@example.com" required>
        </div>
        <div class="form-group">
            <label>Role</label>
            <select name="role" required>
                <option value="">Select Role</option>
                <option value="staff">Staff</option>
                <option value="admin">Admin</option>
                <option value="super">Super</option>
            </select>
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" placeholder="Secure password" required>
        </div>
        <button type="submit" name="add_employee" class="btn btn-success">Add Employee</button>
    </form>
</div>

<!-- Employees List -->
<div class="section">
    <h2>👥 Employee Management</h2>
    <div style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $employees_result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><strong><?= htmlspecialchars($row['username']) ?></strong></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td>
                        <form method="post" style="display: inline-block;">
                            <input type="hidden" name="emp_id" value="<?= $row['id'] ?>">
                            <select name="role" onchange="this.form.submit()" style="padding: 0.25rem;">
                                <option value="staff" <?= $row['role'] == 'staff' ? 'selected' : '' ?>>Staff</option>
                                <option value="admin" <?= $row['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="super" <?= $row['role'] == 'super' ? 'selected' : '' ?>>Super</option>
                            </select>
                            <input type="hidden" name="change_role" value="1">
                        </form>
                    </td>
                    <td>
                        <form method="post" style="display: inline-block;">
                            <input type="hidden" name="emp_id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="current_status" value="<?= $row['active'] ?>">
                            <button type="submit" name="toggle_status" 
                                    class="status-badge <?= $row['active'] ? 'status-active' : 'status-inactive' ?>" 
                                    style="border: none; cursor: pointer;">
                                <?= $row['active'] ? 'Active' : 'Inactive' ?>
                            </button>
                        </form>
                    </td>
                    <td>
                        <!-- Password Change Button -->
                        <button onclick="openPasswordModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['name']) ?>')" 
                                class="btn btn-warning btn-small">🔑 Password</button>
                        
                        <!-- Remove Button -->
                        <form method="post" style="display: inline-block;" 
                              onsubmit="return confirm('Are you sure you want to remove <?= htmlspecialchars($row['name']) ?>?');">
                            <input type="hidden" name="emp_id" value="<?= $row['id'] ?>">
                            <button type="submit" name="remove_employee" class="btn btn-danger btn-small">🗑️ Remove</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Attendance Overview -->
<div class="section">
    <h2>📅 Attendance Overview</h2>
    <form method="get" class="form-inline">
        <div class="form-group">
            <label>Select Month</label>
            <input type="month" name="month" value="<?= htmlspecialchars($month) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">View Attendance</button>
    </form>

    <div id="attendance-table-container">
        <table id="attendance-table" class="table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <?php foreach ($month_dates as $date): ?>
                        <th><?= date('j', strtotime($date)) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
            <?php while ($emp = $all_emps->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($emp['name']) ?></strong></td>
                    <?php foreach ($month_dates as $date): ?>
                        <?php
                        $stmt = $con->prepare("SELECT work_hours FROM attendance WHERE employee_id = ? AND date = ?");
                        $stmt->bind_param("is", $emp['id'], $date);
                        $stmt->execute();
                        $res = $stmt->get_result();
                        $row = $res->fetch_assoc();
                        $hours = $row ? $row['work_hours'] : null;
                        $color = colorCode($hours);
                        $display_hours = $hours !== null ? $hours : '-';
                        ?>
                        <td class="work-hours <?= $color ?>" title="<?= htmlspecialchars($date) ?>"><?= $display_hours ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</div>

<!-- Password Change Modal -->
<div id="passwordModal" class="modal">
    <div class="modal-content">
        <h3>🔑 Change Password</h3>
        <p>Employee: <strong id="empName"></strong></p>
        <form method="post">
            <input type="hidden" id="empId" name="emp_id" value="">
            <div style="margin: 1rem 0;">
                <label>New Password:</label>
                <input type="password" name="new_password" required 
                       placeholder="Enter new secure password" 
                       style="width: 100%; margin-top: 0.5rem;">
            </div>
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <button type="button" onclick="closePasswordModal()" class="btn btn-warning">Cancel</button>
                <button type="submit" name="change_password" class="btn btn-success">Update Password</button>
            </div>
        </form>
    </div>
</div>

<script>
function openPasswordModal(empId, empName) {
    document.getElementById('empId').value = empId;
    document.getElementById('empName').textContent = empName;
    document.getElementById('passwordModal').style.display = 'block';
}

function closePasswordModal() {
    document.getElementById('passwordModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('passwordModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>

</body>
</html>
