<?php
// employee.php
include 'db.php';
session_start();

$message = '';

// Auto-login if session or cookie exists
if (isset($_SESSION['employee_id'])) {
    $employee_id = $_SESSION['employee_id'];
} elseif (isset($_COOKIE['employee_login'])) {
    // Cookie stores user id securely? For demo, assume just ID (NOT recommended for production)
    $employee_id = intval($_COOKIE['employee_login']);
    $_SESSION['employee_id'] = $employee_id;
} else {
    $employee_id = 0;
}

// Handle logout (optional)
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

    // Lookup user
    $stmt = $conn->prepare("SELECT id, password, name FROM employees WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();
        // Verify password
        if (password_verify($password, $user['password'])) {
            $employee_id = $user['id'];
            $_SESSION['employee_id'] = $employee_id;
            // Set cookie for 30 days
            setcookie('employee_login', $employee_id, time() + (30 * 24 * 60 * 60), "/");
            header('Location: employee.php'); // redirect to avoid form resubmission
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
    // Handle Check In
    if (isset($_POST['check_in'])) {
        $date_today = date('Y-m-d');
        $time_now = date('H:i:s');
        // Check if already checked in today
        $stmt = $conn->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date = ?");
        $stmt->bind_param("is", $employee_id, $date_today);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Already has attendance record today, check if already checked in
            $att = $result->fetch_assoc();
            if ($att['check_in'] !== null) {
                $message = "Already checked in today at " . $att['check_in'];
            } else {
                // Update check_in time
                $stmt = $conn->prepare("UPDATE attendance SET check_in = ? WHERE id = ?");
                $stmt->bind_param("si", $time_now, $att['id']);
                if ($stmt->execute()) {
                    $message = "Checked in successfully at $time_now";
                }
            }
        } else {
            // Insert new record with check_in time
            $stmt = $conn->prepare("INSERT INTO attendance (employee_id, date, check_in) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $employee_id, $date_today, $time_now);
            if ($stmt->execute()) {
                $message = "Checked in successfully at $time_now";
            }
        }
    }

    // Handle Check Out
    if (isset($_POST['check_out'])) {
        $date_today = date('Y-m-d');
        $time_now = date('H:i:s');

        // Check if check in exists today
        $stmt = $conn->prepare("SELECT * FROM attendance WHERE employee_id = ? AND date = ?");
        $stmt->bind_param("is", $employee_id, $date_today);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $att = $result->fetch_assoc();
            if ($att['check_in'] === null) {
                $message = "You have not checked in today.";
            } elseif ($att['check_out'] !== null) {
                $message = "Already checked out today at " . $att['check_out'];
            } else {
                // Update check_out time
                $stmt = $conn->prepare("UPDATE attendance SET check_out = ? WHERE id = ?");
                $stmt->bind_param("si", $time_now, $att['id']);
                if ($stmt->execute()) {
                    $message = "Checked out successfully at $time_now";
                }
            }
        } else {
            $message = "You have not checked in today.";
        }
    }

    // Fetch employee info
    $stmt = $conn->prepare("SELECT name FROM employees WHERE id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $employee = $res->fetch_assoc();

} // end if logged in

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Employee Attendance</title>
<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1 { color: #333; }
    form { margin: 10px 0; }
    input[type=email], input[type=password] {
        padding: 8px; width: 250px; margin: 5px 0;
        border: 1px solid #ccc; border-radius: 4px;
        font-size: 14px;
    }
    button {
        padding: 10px 20px;
        background-color: #3498db;
        border: none;
        color: white;
        border-radius: 4px;
        cursor: pointer;
        font-size: 16px;
        margin-top: 10px;
    }
    button:hover {
        background-color: #2980b9;
    }
    .msg {
        margin: 15px 0;
        padding: 10px;
        background-color: #dff0d8;
        border: 1px solid #3c763d;
        color: #3c763d;
        border-radius: 4px;
        max-width: 350px;
    }
    .error {
        background-color: #f2dede;
        border-color: #ebccd1;
        color: #a94442;
    }
    form.inline {
        display: inline-block;
        margin-right: 10px;
    }
    .logout {
        margin-top: 20px;
    }
</style>
</head>
<body>

<?php if ($employee_id > 0): ?>

    <h1>Welcome, <?php echo htmlspecialchars($employee['name']); ?></h1>

    <?php if ($message): ?>
        <p class="msg"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <form method="post" class="inline">
        <button type="submit" name="check_in">Check In</button>
    </form>

    <form method="post" class="inline">
        <button type="submit" name="check_out">Check Out</button>
    </form>

    <div class="logout">
        <a href="employee.php?logout=1" style="color:#e74c3c;">Logout</a>
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

</body>
</html>
