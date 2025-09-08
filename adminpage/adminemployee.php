<?php
// admin.php
include('../homepage/db.php');
session_start();

// ===================================
// Simple Admin Authentication Setup
// (Replace with your real auth system)
// if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
//     // For demo, force admin login as 'Admin' (remove in production)
//     // $_SESSION['user_role'] = 'Admin';
//     // or redirect to login page
//     die("Access denied. Only Admin allowed.");
// }
// ===================================

$message = '';

// Handle Add Employee
if (isset($_POST['add_employee'])) {
    $name = trim($_POST['name']);
    $role = $_POST['role'];
    $email = trim($_POST['email']);

    // Basic validation
    if ($name === '' || $email === '' || $role === '') {
        $message = "Please fill all fields.";
    } else {
        $stmt = $con->prepare("INSERT INTO employees (name, role, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $role, $email);
        if ($stmt->execute()) {
            $message = "Employee added successfully.";
        } else {
            $message = "Error adding employee: " . $con->error;
        }
    }
}

// Handle Remove Employee
if (isset($_POST['remove_employee'])) {
    $emp_id = intval($_POST['emp_id']);
    // Delete attendance records for this employee (foreign key constraint might handle this)
    $con->query("DELETE FROM attendance WHERE employee_id=$emp_id");
    $stmt = $con->prepare("DELETE FROM employees WHERE id = ?");
    $stmt->bind_param("i", $emp_id);
    if ($stmt->execute()) {
        $message = "Employee removed successfully.";
    } else {
        $message = "Error removing employee: " . $con->error;
    }
}

// Handle Role Change
if (isset($_POST['change_role'])) {
    $emp_id = intval($_POST['emp_id']);
    $role = $_POST['role'];

    $stmt = $con->prepare("UPDATE employees SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $role, $emp_id);
    if ($stmt->execute()) {
        $message = "Role updated successfully.";
    } else {
        $message = "Error updating role: " . $con->error;
    }
}

// Fetch all employees
$employees_result = $con->query("SELECT * FROM employees ORDER BY id DESC");

// Attendance Filtering - by month (default is current month)
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Helper function: color code based on hours
function colorCode($hours) {
    if ($hours === null) return 'gray'; // no data
    if ($hours < 8) return 'red';
    if ($hours == 8) return 'yellow';
    if ($hours > 8) return 'green';
    return 'gray';
}

// Fetch all employees for attendance table
$all_emps = $con->query("SELECT * FROM employees ORDER BY name ASC");

// Get days in selected month for header
$days_in_month = cal_days_in_month(CAL_GREGORIAN, intval(substr($month, 5, 2)), intval(substr($month, 0, 4)));

// Prepare dates array of month days
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
<link rel="stylesheet" href="css/adminemployee.css" />
<style>
/* Added some inline styles for table scroll */
#attendance-table-container {
    max-width: 100%;
    overflow-x: auto;
    margin-top: 20px;
}
</style>
</head>
<body>

<h1>Admin Panel - Employee Management & Attendance</h1>

<?php if ($message): ?>
    <p class="msg"><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<!-- Add Employee Form -->
<section>
    <h2>Add Employee</h2>
    <form method="post" class="form-inline">
        <input type="text" name="name" placeholder="Employee Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <select name="role" required>
            <option value="">Select Role</option>
            <option value="Staff">Staff</option>
            <option value="Manager">Manager</option>
            <option value="Admin">Admin</option>
        </select>
        <button type="submit" name="add_employee">Add Employee</button>
    </form>
</section>

<!-- Employees List with Edit Role and Remove -->
<section>
    <h2>Employee List</h2>
    <table class="emp-list">
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Change Role</th><th>Remove</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $employees_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['role']); ?></td>
                <td>
                    <form method="post" class="form-inline" style="margin:0;">
                        <input type="hidden" name="emp_id" value="<?php echo $row['id']; ?>">
                        <select name="role" required>
                            <option value="Staff" <?php if($row['role']=='Staff') echo "selected"; ?>>Staff</option>
                            <option value="Manager" <?php if($row['role']=='Manager') echo "selected"; ?>>Manager</option>
                            <option value="Admin" <?php if($row['role']=='Admin') echo "selected"; ?>>Admin</option>
                        </select>
                        <button type="submit" name="change_role">Update</button>
                    </form>
                </td>
                <td>
                    <form method="post" onsubmit="return confirm('Remove employee?');" style="margin:0;">
                        <input type="hidden" name="emp_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" name="remove_employee" class="remove-btn">Remove</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</section>

<!-- Attendance View -->
<section>
    <h2>Attendance Overview</h2>
    <form method="get" class="form-inline">
        <label for="month">Select Month: </label>
        <input type="month" id="month" name="month" value="<?php echo htmlspecialchars($month); ?>" required>
        <button type="submit">View</button>
    </form>

    <div id="attendance-table-container">
        <table id="attendance-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <?php
                    foreach ($month_dates as $date) {
                        echo '<th>' . date('j', strtotime($date)) . '</th>';
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
            <?php
            while ($emp = $all_emps->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($emp['name']) . '</td>';

                foreach ($month_dates as $date) {
                    // Fetch work_hours for employee on this date
                    $stmt = $con->prepare("SELECT work_hours FROM attendance WHERE employee_id = ? AND date = ?");
                    $stmt->bind_param("is", $emp['id'], $date);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $row = $res->fetch_assoc();
                    $hours = $row ? $row['work_hours'] : null;
                    $color = colorCode($hours);

                    $display_hours = $hours !== null ? $hours : '-';

                    echo '<td class="work-hours ' . $color . '" title="' . htmlspecialchars($date) . '">' . $display_hours . '</td>';
                }

                echo '</tr>';
            }
            ?>
            </tbody>
        </table>
    </div>
</section>

</body>
</html>