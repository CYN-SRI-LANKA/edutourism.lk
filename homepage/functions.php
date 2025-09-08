<?php
// Database connection with error handling
function getDbconnection() {
    static $db = null;
    if ($db === null) {
        $db = mysqli_connect('localhost', 'root', '', 'edutourism_lk');
        if (!$db) {
            die("connection failed: " . mysqli_connect_error());
        }
        mysqli_set_charset($db, 'utf8mb4');
    }
    return $db;
}

// Get real IP with validation
function getRealIpUser() {
    $ip_sources = [
        'HTTP_X_REAL_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR',
        'REMOTE_ADDR'
    ];

    foreach ($ip_sources as $source) {
        if (!empty($_SERVER[$source])) {
            $ip = filter_var($_SERVER[$source], FILTER_VALIDATE_IP);
            if ($ip) return $ip;
        }
    }
    
    return '0.0.0.0';
}

// Add to cart with prepared statements
function addCart() {
    $db = getDbconnection();
    
    if (!isset($_GET['add_cart']) || !isset($_SESSION['customer_email'])) {
        return false;
    }

    $c_id = mysqli_real_escape_string($db, $_SESSION['customer_email']);
    $p_id = (int)$_GET['add_cart'];
    $qty = isset($_POST['product_qty']) ? (int)$_POST['product_qty'] : 1;
    $size = isset($_POST['size']) ? mysqli_real_escape_string($db, $_POST['size']) : '';
    $ip_add = getRealIpUser();

    // Check if product already exists in cart
    $stmt = $db->prepare("SELECT * FROM cart WHERE c_id = ? AND products_id = ?");
    $stmt->bind_param("si", $c_id, $p_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Product already added.');</script>";
        echo "<script>window.open('product.php?pro_id=" . $p_id . "','_self');</script>";
        return false;
    }

    // Insert new product into cart
    $stmt = $db->prepare("INSERT INTO cart (products_id, ip_add, qty, size, date, c_id) VALUES (?, ?, ?, ?, NOW(), ?)");
    $stmt->bind_param("isiss", $p_id, $ip_add, $qty, $size, $c_id);
    
    if ($stmt->execute()) {
        echo "<script>alert('Product added to Cart. Keep Shopping.');</script>";
        echo "<script>window.open('product.php?product_id=" . $p_id . "','_self');</script>";
        return true;
    }
    
    return false;
}

// Get Women Products with prepared statement
function getWProduct() {
    $db = getDbconnection();
    $output = '';
    
    $stmt = $db->prepare("SELECT products_id, product_title, product_price, product_img1 
                         FROM products WHERE cat_id = 2 ORDER BY RAND() LIMIT 7");
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $output .= generateProductHTML($row);
    }
    
    echo $output;
}

// Get Men Products with prepared statement
function getMProduct() {
    $db = getDbconnection();
    $output = '';
    
    $stmt = $db->prepare("SELECT products_id, product_title, product_price, product_img1 
                         FROM products WHERE cat_id = 1 ORDER BY RAND() LIMIT 7");
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $output .= generateProductHTML($row);
    }
    
    echo $output;
}

// Helper function to generate product HTML
function generateProductHTML($product) {
    return "
    <div class='product-item'>
        <div class='pi-pic' style='max-height:300px'>
            <img src='img/products/" . htmlspecialchars($product['product_img1']) . "' 
                 alt='" . htmlspecialchars($product['product_title']) . "'>
            <ul>
                <li class='quick-view'>
                    <a href='product.php?product_id=" . (int)$product['products_id'] . "' 
                       style='background:#fe4231;color:white'>View Details</a>
                </li>
            </ul>
        </div>
        <div class='pi-text'>
            <a href='product.php?product_id=" . (int)$product['products_id'] . "'>
                <h5>" . htmlspecialchars($product['product_title']) . "</h5>
            </a>
            <div class='product-price'>
                PKR " . htmlspecialchars($product['product_price']) . "
            </div>
        </div>
    </div>";
}

// Get cart items count
function items() {
    if (!isset($_SESSION['customer_email'])) return 0;
    
    $db = getDbconnection();
    $c_id = mysqli_real_escape_string($db, $_SESSION['customer_email']);
    
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM cart WHERE c_id = ?");
    $stmt->bind_param("s", $c_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo $row['count'];
}

// Calculate total price
function total_price() {
    if (!isset($_SESSION['customer_email'])) return 0;
    
    $db = getDbconnection();
    $c_id = mysqli_real_escape_string($db, $_SESSION['customer_email']);
    
    $stmt = $db->prepare("
        SELECT SUM(p.product_price * c.qty) as total 
        FROM cart c 
        JOIN products p ON c.products_id = p.products_id 
        WHERE c.c_id = ?
    ");
    $stmt->bind_param("s", $c_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo "PKR " . number_format($row['total'] ?? 0, 2);
}

// Security helper functions
function isLoggedIn() {
    return isset($_SESSION['customer_email']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin']);
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function formatDate($date) {
    return date('M d, Y H:i', strtotime($date));
}




// Function to generate a unique application ID
function generateApplicationID() {
    // Format: VD-YYMM-XXXX where XXXX is a random number
    $prefix = "VD";
    $dateCode = date("ym");
    $randomNum = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    return $prefix . "-" . $dateCode . "-" . $randomNum;
}

// Function to sanitize input data
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to validate date format
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Function to check if a file exists
function fileExists($filePath) {
    return file_exists($filePath) && is_file($filePath);
}

// Function to create directory if it doesn't exist
function createDirectory($dirPath) {
    if (!file_exists($dirPath)) {
        return mkdir($dirPath, 0777, true);
    }
    return true;
}

// Function to move uploaded file with error handling
function moveUploadedFileWithCheck($tmpName, $destination) {
    if (move_uploaded_file($tmpName, $destination)) {
        return true;
    }
    return false;
}


/**
 * Common functions used across the application
 */

/**
 * Generate a unique application ID
 * @return string A unique application ID
 */


/**
 * Log activity
 * @param string $applicationID Application ID
 * @param string $activity Activity description
 * @return bool Success or failure
 */
function logActivity($applicationID, $activity) {
    $con = connectDB();
    
    if (!$con) {
        return false;
    }
    
    try {
        $stmt = $con->prepare("INSERT INTO activity_log (application_id, activity, log_time) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $applicationID, $activity);
        $result = $stmt->execute();
        $stmt->close();
        $con->close();
        
        return $result;
    } catch (Exception $e) {
        error_log("Error logging activity: " . $e->getMessage());
        $con->close();
        return false;
    }
}

/**
 * Check if an application exists
 * @param string $applicationID Application ID to check
 * @return bool True if exists, false otherwise
 */
function applicationExists($applicationID) {
    $con = connectDB();
    
    if (!$con) {
        return false;
    }
    
    try {
        $stmt = $con->prepare("SELECT COUNT(*) as count FROM visadetailsdocuments WHERE ApplicationID = ?");
        $stmt->bind_param("s", $applicationID);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $exists = ($row['count'] > 0);
        
        $stmt->close();
        $con->close();
        
        return $exists;
    } catch (Exception $e) {
        error_log("Error checking application: " . $e->getMessage());
        $con->close();
        return false;
    }
}

/**
 * Get application status
 * @param string $applicationID Application ID to check
 * @return string Status of the application or empty string if not found
 */
function getApplicationStatus($applicationID) {
    $con = connectDB();
    
    if (!$con) {
        return "";
    }
    
    try {
        $stmt = $con->prepare("SELECT status FROM application_status WHERE application_id = ?");
        $stmt->bind_param("s", $applicationID);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $status = $row['status'];
        } else {
            $status = "";
        }
        
        $stmt->close();
        $con->close();
        
        return $status;
    } catch (Exception $e) {
        error_log("Error getting application status: " . $e->getMessage());
        $con->close();
        return "";
    }
}

/**
 * Update application status
 * @param string $applicationID Application ID
 * @param string $status New status
 * @return bool Success or failure
 */
function updateApplicationStatus($applicationID, $status) {
    $con = connectDB();
    
    if (!$con) {
        return false;
    }
    
    try {
        $stmt = $con->prepare("INSERT INTO application_status (application_id, status, update_time) 
                               VALUES (?, ?, NOW()) 
                               ON DUPLICATE KEY UPDATE 
                               status = VALUES(status), 
                               update_time = NOW()");
        $stmt->bind_param("ss", $applicationID, $status);
        $result = $stmt->execute();
        
        $stmt->close();
        $con->close();
        
        // Log the status change
        logActivity($applicationID, "Status changed to: " . $status);
        
        return $result;
    } catch (Exception $e) {
        error_log("Error updating application status: " . $e->getMessage());
        $con->close();
        return false;
    }
}
function connectDB() {
    // Database configuration
    
    
    // Create connection
    $con = mysqli_connect("localhost","root", "", "edutourism_lk");
    
    // Check connection
    if ($con->connect_error) {
        error_log("Connection failed: " . $con->connect_error);
        return false;
    }
    
    return $con;
}
?>