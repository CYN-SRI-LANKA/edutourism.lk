<?php
// export_excel.php - Excel Export for Visa Management System (Specific Fields Only)
session_start();

// Check if user is logged in and has permission
if (!isset($_SESSION['user'])) {
    header('Location: adminmain.php');
    exit();
}

// Role checking function (copied from main file)
function roles(): array { 
    if (!isset($_SESSION['user']['role']) || empty($_SESSION['user']['role'])) {
        return [];
    }
    
    $role = $_SESSION['user']['role'];
    switch($role) {
        case 'super': return ['SUPER_ADMIN'];
        case 'admin': return ['TMS_ADMIN'];
        case 'staff': return ['VMS_ADMIN'];
        default: return [];
    }
}

function has_role(string ...$required): bool {
    $mine = roles();
    foreach ($required as $r) {
        if (in_array($r, $mine, true)) return true;
    }
    return false;
}

function can_vms(): bool { 
    return has_role('SUPER_ADMIN', 'VMS_ADMIN'); 
}

// Check VMS access permission
if (!can_vms()) {
    die('Access Denied');
}

include('../homepage/db.php');

// Get parameters
$selected_year = $_GET['year'] ?? '';
$selected_tour = $_GET['tour'] ?? '';
$search = trim($_GET['search'] ?? '');
$filter_missing = $_GET['filter_missing'] ?? '';

if (empty($selected_year) || empty($selected_tour)) {
    die('Year and Tour parameters are required');
}

// Build the same query as in the main file
$where = [];
$where[] = "YEAR(created_at) = '" . mysqli_real_escape_string($con, $selected_year) . "'";
$tour_escaped = mysqli_real_escape_string($con, $selected_tour);
$where[] = "tourname = '$tour_escaped'";

if ($search) {
    $search_db = mysqli_real_escape_string($con, $search);
    $like = "%$search_db%";
    $where[] = "(nic_number LIKE '$like' OR name_for_certificates LIKE '$like' OR surname LIKE '$like' OR passport_number LIKE '$like')";
}

// File attachment columns
$file_columns = [
    'passport_copy','photo_id','visa_request_letter','bank_statements',
    'employment_letter','epf_confirmation','pay_slips','business_registration',
    'form_pvt_ltd','company_statements','service_letters','student_letter',
    'dependent_confirmation','dependent_income','other_documents'
];

if ($filter_missing === "1") {
    $miss_checks = [];
    foreach ($file_columns as $col) {
        $miss_checks[] = "($col IS NULL OR $col = '')";
    }
    $where[] = '(' . implode(" OR ", $miss_checks) . ')';
}

$search_sql = " WHERE ".implode(" AND ", $where);
$order = "ORDER BY created_at DESC";
$sql = "SELECT * FROM visa_applications $search_sql $order";
$result = mysqli_query($con, $sql);

// Generate filename
$filename = "Visa_Applications_" . $selected_year . "_" . preg_replace('/[^A-Za-z0-9_-]/', '_', $selected_tour) . "_" . date('Y-m-d_H-i-s');

// Set headers for Excel download
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename.xls\"");
header("Pragma: no-cache");
header("Expires: 0");

// Define only the fields you want to export in the exact order
$export_cols = [
    'nic_number' => 'NIC Number',
    'name_for_certificates' => 'Name for the Certificates',
    'name_for_tour_id' => 'Name for the Tour ID',
    'permanent_address' => 'Permanent Address',
    'city' => 'City',
    'postal_code' => 'Postal Code',
    'province' => 'Province',
    'surname' => 'Surname',
    'other_names' => 'Other Names',
    'date_of_birth' => 'Date of Birth',
    'gender' => 'Gender',
    'passport_number' => 'Passport Number',
    'issue_date' => 'Issue Date',
    'expiry_date' => 'Expiry Date',
    'employment_status' => 'Employment Status',
    'dependent_status' => 'Student or Dependent Status',
];

// Start Excel table
echo '<table border="1">';

// Export info header
echo '<tr>';
echo '<td colspan="' . count($export_cols) . '" style="background-color: #e9f2fc; font-weight: bold; text-align: center; padding: 10px;">';
echo 'Visa Management System Export - Year: ' . htmlspecialchars($selected_year) . ' | Tour: ' . htmlspecialchars($selected_tour);
if ($search) echo ' | Search: ' . htmlspecialchars($search);
if ($filter_missing) echo ' | Filter: Missing Files Only';
echo ' | Generated: ' . date('Y-m-d H:i:s');
echo '</td>';
echo '</tr>';

// Empty row for spacing
echo '<tr><td colspan="' . count($export_cols) . '"></td></tr>';

// Column headers
echo '<tr style="background-color: #e9f2fc; font-weight: bold;">';
foreach ($export_cols as $col_key => $label) {
    echo '<th>' . htmlspecialchars($label) . '</th>';
}
echo '</tr>';

// Data rows
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<tr>';
        
        foreach ($export_cols as $col_key => $label) {
            // For file fields, show file availability status
            if (in_array($col_key, $file_columns)) {
                if (empty($row[$col_key])) {
                    echo '<td style="color: #ca3a2b; font-weight: bold;">Not Uploaded</td>';
                } else {
                    // Show file names (comma-separated if multiple)
                    $files = array_map('trim', explode(',', $row[$col_key]));
                    $file_list = [];
                    foreach ($files as $f) {
                        if ($f) {
                            $file_list[] = htmlspecialchars($f);
                        }
                    }
                    echo '<td>Uploaded (' . implode(', ', $file_list) . ')</td>';
                }
            } else {
                // Regular fields
                $value = htmlspecialchars($row[$col_key] ?? '');
                if (empty($value)) {
                    $value = '-';
                }
                
                // Format dates for better readability
                if (in_array($col_key, ['date_of_birth', 'issue_date', 'expiry_date', 'submission_date', 'review_date', 'completion_date', 'payment_date', 'created_at', 'updated_at'])) {
                    if ($row[$col_key] && $row[$col_key] != '0000-00-00' && $row[$col_key] != '0000-00-00 00:00:00') {
                        $date = new DateTime($row[$col_key]);
                        $value = $date->format('Y-m-d');
                        if (in_array($col_key, ['created_at', 'updated_at'])) {
                            $value = $date->format('Y-m-d H:i:s');
                        }
                    } else {
                        $value = '-';
                    }
                }
                
                // Format currency for processing fee
                if ($col_key == 'processing_fee' && !empty($row[$col_key])) {
                    $value = 'LKR ' . number_format((float)$row[$col_key], 2);
                }
                
                echo '<td>' . $value . '</td>';
            }
        }
        
        echo '</tr>';
    }
} else {
    echo '<tr>';
    echo '<td colspan="' . count($export_cols) . '" style="text-align: center; padding: 20px; color: #666;">';
    echo 'No records found for the selected criteria';
    echo '</td>';
    echo '</tr>';
}

echo '</table>';

// Add summary at the bottom
$total_records = mysqli_num_rows($result);
echo '<br><br>';
echo '<table border="1">';
echo '<tr style="background-color: #f0f0f0;"><td><strong>Export Summary</strong></td></tr>';
echo '<tr><td>Total Records Exported: ' . $total_records . '</td></tr>';
echo '<tr><td>Total Fields Exported: ' . count($export_cols) . '</td></tr>';
echo '<tr><td>Export Date: ' . date('Y-m-d H:i:s') . '</td></tr>';
echo '<tr><td>Exported by: ' . htmlspecialchars($_SESSION['user']['username']) . '</td></tr>';
echo '</table>';

// Close database connection
mysqli_close($con);
?>
