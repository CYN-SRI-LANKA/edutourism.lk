<?php
// employee.php
include 'db.php';
session_start();

$message = '';

// Auto-login if session or cookie exists
if (isset($_SESSION['employee_id'])) {
    $employee_id = $_SESSION['employee_id'];
} elseif (isset($_COOKIE['employee_login'])) {
    $employee_id = intval($_COOKIE['employee_login']);
    $_SESSION['employee_id'] = $employee_id;
} else {
    $employee_id = 0;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    setcookie('employee_login', '', time() - 3600, "/");
    header('Location: employee.php');
    exit;
}

// Handle login form submission
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $con->prepare("SELECT id, password, name FROM employees WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $employee_id = $user['id'];
            $_SESSION['employee_id'] = $employee_id;
            setcookie('employee_login', $employee_id, time() + (30 * 24 * 60 * 60), "/");
            header('Location: employee.php');
            exit;
        } else {
            $message = "Invalid password.";
        }
    } else {
        $message = "User not found.";
    }
}

// After login (or if already logged in) show attendance page
if ($employee_id > 0) {
    
    // Get today's attendance record
    $date_today = date('Y-m-d');
    $today_attendance = null;
    $current_break = null;
    
    $stmt = $con->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date = ?");
    $stmt->bind_param("is", $employee_id, $date_today);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $today_attendance = $result->fetch_assoc();
        
        // Check if currently on break
        $stmt = $con->prepare("SELECT * FROM breaks WHERE attendance_id = ? AND break_end IS NULL ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("i", $today_attendance['id']);
        $stmt->execute();
        $break_result = $stmt->get_result();
        if ($break_result->num_rows > 0) {
            $current_break = $break_result->fetch_assoc();
        }
    }

    // Handle Check In
    if (isset($_POST['check_in'])) {
        $time_now = date('H:i:s');
        
        if ($today_attendance) {
            if ($today_attendance['check_in'] !== null) {
                $message = "Already checked in today at " . $today_attendance['check_in'];
            } else {
                $stmt = $con->prepare("UPDATE attendance SET check_in = ? WHERE id = ?");
                $stmt->bind_param("si", $time_now, $today_attendance['id']);
                if ($stmt->execute()) {
                    $message = "Checked in successfully at $time_now";
                    header('Location: employee.php');
                    exit;
                }
            }
        } else {
            $stmt = $con->prepare("INSERT INTO attendance (employee_id, date, check_in) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $employee_id, $date_today, $time_now);
            if ($stmt->execute()) {
                $message = "Checked in successfully at $time_now";
                header('Location: employee.php');
                exit;
            }
        }
    }

    // Handle Break Start
    if (isset($_POST['break_start'])) {
        if ($today_attendance && $today_attendance['check_in'] && !$current_break) {
            $time_now = date('H:i:s');
            $stmt = $con->prepare("INSERT INTO breaks (attendance_id, break_start) VALUES (?, ?)");
            $stmt->bind_param("is", $today_attendance['id'], $time_now);
            if ($stmt->execute()) {
                $message = "Break started at $time_now";
                header('Location: employee.php');
                exit;
            }
        } else {
            $message = "Cannot start break. Either not checked in or already on break.";
        }
    }

    // Handle Break End
    if (isset($_POST['break_end'])) {
        if ($current_break) {
            $time_now = date('H:i:s');
            $stmt = $con->prepare("UPDATE breaks SET break_end = ? WHERE id = ?");
            $stmt->bind_param("si", $time_now, $current_break['id']);
            if ($stmt->execute()) {
                $break_duration = strtotime($time_now) - strtotime($current_break['break_start']);
                $break_mins = floor($break_duration / 60);
                $message = "Break ended at $time_now (Duration: {$break_mins} minutes)";
                header('Location: employee.php');
                exit;
            }
        } else {
            $message = "You are not currently on break.";
        }
    }

    // Handle Check Out with working hours calculation
    if (isset($_POST['check_out'])) {
        $time_now = date('H:i:s');

        if ($today_attendance) {
            if ($today_attendance['check_in'] === null) {
                $message = "You have not checked in today.";
            } elseif ($today_attendance['check_out'] !== null) {
                $message = "Already checked out today at " . $today_attendance['check_out'];
            } elseif ($current_break) {
                $message = "Please end your break before checking out.";
            } else {
                // Calculate total break time
                $break_duration_seconds = 0;
                $stmt = $con->prepare("SELECT break_start, break_end FROM breaks WHERE attendance_id = ? AND break_end IS NOT NULL");
                $stmt->bind_param("i", $today_attendance['id']);
                $stmt->execute();
                $break_result = $stmt->get_result();
                
                while ($break_row = $break_result->fetch_assoc()) {
                    $break_start_seconds = strtotime($break_row['break_start']);
                    $break_end_seconds = strtotime($break_row['break_end']);
                    if ($break_end_seconds > $break_start_seconds) {
                        $break_duration_seconds += ($break_end_seconds - $break_start_seconds);
                    }
                }

                // Calculate working hours
                $total_seconds = strtotime($time_now) - strtotime($today_attendance['check_in']);
                $working_seconds = $total_seconds - $break_duration_seconds;
                
                // Convert to H:i:s format
                $working_hours = gmdate('H:i:s', $working_seconds);
                
                // Update check_out and working_hours
                $stmt = $con->prepare("UPDATE attendance SET check_out = ?, working_hours = ? WHERE id = ?");
                $stmt->bind_param("ssi", $time_now, $working_hours, $today_attendance['id']);
                if ($stmt->execute()) {
                    $hours = floor($working_seconds / 3600);
                    $minutes = floor(($working_seconds % 3600) / 60);
                    $message = "Checked out successfully at $time_now. Total working time: {$hours}h {$minutes}m";
                    header('Location: employee.php');
                    exit;
                }
            }
        } else {
            $message = "You have not checked in today.";
        }
    }

    // Fetch employee info
    $stmt = $con->prepare("SELECT name FROM employees WHERE id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $employee = $res->fetch_assoc();

    // Get today's break summary
    $break_summary = '';
    if ($today_attendance) {
        $stmt = $con->prepare("SELECT break_start, break_end FROM breaks WHERE attendance_id = ? ORDER BY break_start");
        $stmt->bind_param("i", $today_attendance['id']);
        $stmt->execute();
        $break_result = $stmt->get_result();
        
        $total_break_time = 0;
        $break_count = 0;
        while ($break_row = $break_result->fetch_assoc()) {
            $break_count++;
            if ($break_row['break_end']) {
                $break_duration = strtotime($break_row['break_end']) - strtotime($break_row['break_start']);
                $total_break_time += $break_duration;
            }
        }
        
        if ($total_break_time > 0) {
            $break_hours = floor($total_break_time / 3600);
            $break_minutes = floor(($total_break_time % 3600) / 60);
            $break_summary = "Today's breaks: {$break_count} break(s), Total: {$break_hours}h {$break_minutes}m";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Employee Attendance</title>
<style>
    body { font-family: Arial, sans-serif; margin: 20px; background-color: #f5f5f5; }
    .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    h1 { color: #333; text-align: center; margin-bottom: 30px; }
    form { margin: 10px 0; }
    input[type=email], input[type=password] {
        padding: 12px; width: 100%; margin: 8px 0;
        border: 1px solid #ddd; border-radius: 6px;
        font-size: 16px; box-sizing: border-box;
    }
    button {
        padding: 12px 24px;
        background-color: #3498db;
        border: none;
        color: white;
        border-radius: 6px;
        cursor: pointer;
        font-size: 16px;
        margin: 5px;
        transition: background-color 0.3s;
    }
    button:hover { background-color: #2980b9; }
    button.break { background-color: #f39c12; }
    button.break:hover { background-color: #d68910; }
    button.checkout { background-color: #e74c3c; }
    button.checkout:hover { background-color: #c0392b; }
    button.break-end { background-color: #27ae60; }
    button.break-end:hover { background-color: #229954; }
    .msg {
        margin: 20px 0;
        padding: 15px;
        background-color: #dff0d8;
        border: 1px solid #3c763d;
        color: #3c763d;
        border-radius: 6px;
    }
    .error {
        background-color: #f2dede;
        border-color: #ebccd1;
        color: #a94442;
    }
    .info {
        background-color: #d9edf7;
        border-color: #bce8f1;
        color: #31708f;
    }
    .button-row {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin: 20px 0;
        justify-content: center;
    }
    .status-info {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 6px;
        margin: 20px 0;
        border-left: 4px solid #007bff;
    }
    .logout {
        text-align: center;
        margin-top: 30px;
    }
    .logout a {
        color: #e74c3c;
        text-decoration: none;
        font-weight: bold;
    }
    .break-status {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        color: #856404;
        padding: 10px;
        border-radius: 6px;
        margin: 10px 0;
        text-align: center;
        font-weight: bold;
    }
</style>
</head>
<body>

<div class="container">
<?php if ($employee_id > 0): ?>

    <h1>Welcome, <?php echo htmlspecialchars($employee['name']); ?></h1>

    <?php if ($message): ?>
        <p class="msg"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if ($today_attendance): ?>
        <div class="status-info">
            <h3>Today's Status</h3>
            <p><strong>Check In:</strong> <?php echo $today_attendance['check_in'] ? $today_attendance['check_in'] : 'Not checked in'; ?></p>
            <p><strong>Check Out:</strong> <?php echo $today_attendance['check_out'] ? $today_attendance['check_out'] : 'Not checked out'; ?></p>
            <?php if ($today_attendance['working_hours']): ?>
                <p><strong>Working Hours:</strong> <?php echo $today_attendance['working_hours']; ?></p>
            <?php endif; ?>
            <?php if ($break_summary): ?>
                <p><strong><?php echo $break_summary; ?></strong></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($current_break): ?>
        <div class="break-status">
            🕐 Currently on break since <?php echo $current_break['break_start']; ?>
        </div>
    <?php endif; ?>

    <div class="button-row">
        <?php if (!$today_attendance || $today_attendance['check_in'] === null): ?>
            <form method="post">
                <button type="submit" name="check_in">🕘 Check In</button>
            </form>
        <?php endif; ?>

        <?php if ($today_attendance && $today_attendance['check_in'] && $today_attendance['check_out'] === null): ?>
            
            <?php if (!$current_break): ?>
                <form method="post">
                    <button type="submit" name="break_start" class="break">☕ Start Break</button>
                </form>
            <?php else: ?>
                <form method="post">
                    <button type="submit" name="break_end" class="break-end">✅ End Break</button>
                </form>
            <?php endif; ?>

            <?php if (!$current_break): ?>
                <form method="post">
                    <button type="submit" name="check_out" class="checkout">🕕 Check Out</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="logout">
        <a href="employee.php?logout=1">Logout</a>
    </div>

<?php else: ?>

    <h1>Employee Login</h1>

    <?php if ($message): ?>
        <p class="msg error"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="post">
        <input type="email" name="email" placeholder="Email" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit" name="login">Login</button>
    </form>

<?php endif; ?>
</div>

<script>
// Auto refresh page every 60 seconds to show updated times
<?php if ($employee_id > 0): ?>
setTimeout(function(){
    window.location.reload();
}, 60000);
<?php endif; ?>
</script>

</body>
</html>
