<?php
// ajax_get_tours.php - Returns tours filtered by year
session_start();
include('../homepage/db.php');

// Check if user is logged in (basic security)
if (!isset($_SESSION['user'])) {
    http_response_code(403);
    exit('Unauthorized');
}

$year = $_GET['year'] ?? '';
$tours = [];

if ($year && is_numeric($year)) {
    $year_escaped = mysqli_real_escape_string($con, $year);
    
    // Get tours for the selected year, ordered by latest first
    $query = "SELECT DISTINCT tourname, MAX(created_at) as latest_date 
              FROM visa_applications 
              WHERE YEAR(created_at) = '$year_escaped' 
              GROUP BY tourname 
              ORDER BY latest_date DESC, tourname ASC";
    
    $result = mysqli_query($con, $query);
    
    if ($result) {
        while($row = mysqli_fetch_assoc($result)) {
            $tours[] = $row['tourname'];
        }
    }
}

header('Content-Type: application/json');
echo json_encode($tours);
?>
