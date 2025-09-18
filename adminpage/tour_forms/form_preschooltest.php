<?php
session_start();

// Simple login check
if (!isset($_SESSION['user'])) {
    header('Location: ../adminmain.php');
    exit();
}

$active = "Account";
include("../../homepage/db.php");
include("../../homepage/functions.php");

// Tour-specific information (will be replaced by actual values)
$tour_name = 'preschooltest';
$tour_title = 'Preschool Test';
$destination = 'sfsf';
$duration = '2';

// NEW: Fetch tour year from tours table
$tour_year = null;
$tour_year_query = "SELECT year FROM tours WHERE tourname = '$tour_name' LIMIT 1";
$tour_year_result = mysqli_query($con, $tour_year_query);
if ($tour_year_result && mysqli_num_rows($tour_year_result) > 0) {
    $tour_year_data = mysqli_fetch_assoc($tour_year_result);
    $tour_year = $tour_year_data['year'];
} else {
    // Fallback to current year if tour not found
    $tour_year = date('Y');
}

// Check if editing existing record or searching by NIC
$editing = false;
$existing_data = null;
$nic_search = '';

// Handle NIC search
if (isset($_POST['search_nic']) && !empty($_POST['search_nic'])) {
    $nic_search = mysqli_real_escape_string($con, $_POST['search_nic']);
    $query = "SELECT * FROM visa_applications WHERE nic_number = '$nic_search' AND tourname = '$tour_name'";
    $result = mysqli_query($con, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $existing_data = mysqli_fetch_assoc($result);
        $editing = true;
    }
} elseif (isset($_GET['nic']) && !empty($_GET['nic'])) {
    $nic = mysqli_real_escape_string($con, $_GET['nic']);
    $query = "SELECT * FROM visa_applications WHERE nic_number = '$nic' AND tourname = '$tour_name'";
    $result = mysqli_query($con, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $existing_data = mysqli_fetch_assoc($result);
        $editing = true;
        $nic_search = $nic;
    }
}

// Function to check if field should be conditionally 


// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['search_nic'])) {
    $response = array('success' => false, 'message' => '');
    
    try {
        // Get NIC number (primary key)
        $nic_number = mysqli_real_escape_string($con, $_POST['nic_number']);
        
        if (empty($nic_number)) {
            throw new Exception('NIC Number is ');
        }
        
        // Create uploads directory if it doesn't exist
        $upload_dir = '../uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Function to handle file uploads with surname + othername folder
        function handleFileUpload($file_input_name, $surname, $othername, $file_prefix, $tour_name) {
            global $upload_dir;

            if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == 0) {
                // Set folder: upload/visa/tourname/surname_othername/
                $folder_name = trim($surname . '_' . $othername);
                $folder_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $folder_name);
                $user_folder = $upload_dir . 'visa/' . $tour_name . '/' . $folder_name . '/';

                if (!file_exists($user_folder)) {
                    mkdir($user_folder, 0777, true);
                }

                $file_tmp = $_FILES[$file_input_name]['tmp_name'];
                $file_extension = pathinfo($_FILES[$file_input_name]['name'], PATHINFO_EXTENSION);
                $new_filename = $file_prefix . '.' . $file_extension;
                $upload_path = $user_folder . $new_filename;

                if (move_uploaded_file($file_tmp, $upload_path)) {
                    return 'visa/' . $tour_name . '/' . $folder_name . '/' . $new_filename;
                } else {
                    throw new Exception('Failed to upload ' . $file_prefix);
                }
            }
            return null;
        }
        
        // Function to handle multiple file uploads
        function handleMultipleFileUploads($file_input_name, $surname, $othername, $file_prefix, $tour_name) {
            global $upload_dir;
            $uploaded_files = array();

            if (isset($_FILES[$file_input_name]) && is_array($_FILES[$file_input_name]['name'])) {
                // Set folder: upload/visa/tourname/surname_othername/
                $folder_name = trim($surname . '_' . $othername);
                $folder_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $folder_name);
                $user_folder = $upload_dir . 'visa/' . $tour_name . '/' . $folder_name . '/';

                if (!file_exists($user_folder)) {
                    mkdir($user_folder, 0777, true);
                }

                for ($i = 0; $i < count($_FILES[$file_input_name]['name']); $i++) {
                    if ($_FILES[$file_input_name]['error'][$i] == 0) {
                        $file_tmp = $_FILES[$file_input_name]['tmp_name'][$i];
                        $file_extension = pathinfo($_FILES[$file_input_name]['name'][$i], PATHINFO_EXTENSION);
                        $new_filename = $file_prefix . '_' . ($i + 1) . '.' . $file_extension;
                        $upload_path = $user_folder . $new_filename;

                        if (move_uploaded_file($file_tmp, $upload_path)) {
                            $uploaded_files[] = 'visa/' . $tour_name . '/' . $folder_name . '/' . $new_filename;
                        }
                    }
                }
            }
            return implode(',', $uploaded_files);
        }

        // Get surname and othername for folder creation
        $surname = mysqli_real_escape_string($con, $_POST['surname'] ?? '');
        $othername = mysqli_real_escape_string($con, $_POST['othername'] ?? '');
        
        // Check if record exists for this specific tour
        $check_query = "SELECT * FROM visa_applications WHERE nic_number = '$nic_number' AND tourname = '$tour_name'";
        $check_result = mysqli_query($con, $check_query);
        $record_exists = mysqli_num_rows($check_result) > 0;
        
        // Get existing data if record exists
        $existing_record = null;
        if ($record_exists) {
            $existing_record = mysqli_fetch_assoc($check_result);
        }
        
        // Handle file uploads - UPDATED: Most are now multiple file uploads
        $passport_copy = handleFileUpload('passportCopy', $surname, $othername, 'passport_copy', $tour_name);
        $photo_id = handleFileUpload('photoId', $surname, $othername, 'photo_id', $tour_name);
        $visa_request_letter = handleMultipleFileUploads('visaRequestLetter', $surname, $othername, 'visa_request_letter', $tour_name);
        $bank_statements = handleMultipleFileUploads('bankStatements', $surname, $othername, 'bank_statement', $tour_name);
        $employment_letter = handleMultipleFileUploads('employmentLetter', $surname, $othername, 'employment_letter', $tour_name);
        $epf_confirmation = handleMultipleFileUploads('epfConfirmation', $surname, $othername, 'epf_confirmation', $tour_name);
        $pay_slips = handleMultipleFileUploads('paySlips', $surname, $othername, 'pay_slip', $tour_name);
        $business_registration = handleMultipleFileUploads('businessRegistration', $surname, $othername, 'business_registration', $tour_name);
        $form_pvt_ltd = handleMultipleFileUploads('formPvtLtd', $surname, $othername, 'form_pvt_ltd', $tour_name);
        $company_statements = handleMultipleFileUploads('companyStatements', $surname, $othername, 'company_statement', $tour_name);
        $service_letters = handleMultipleFileUploads('serviceLetters', $surname, $othername, 'service_letter', $tour_name);
        $student_letter = handleMultipleFileUploads('studentLetter', $surname, $othername, 'student_letter', $tour_name);
        $dependent_confirmation = handleMultipleFileUploads('dependentConfirmation', $surname, $othername, 'dependent_confirmation', $tour_name);
        $dependent_income = handleMultipleFileUploads('dependentIncome', $surname, $othername, 'dependent_income', $tour_name);
        $other_documents = handleMultipleFileUploads('otherDocuments', $surname, $othername, 'other_document', $tour_name);
        
        // Combine date fields
        $date_of_birth = '';
        if (!empty($_POST['birthYear']) && !empty($_POST['birthMonth']) && !empty($_POST['birthDay'])) {
            $date_of_birth = $_POST['birthYear'] . '-' . str_pad($_POST['birthMonth'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($_POST['birthDay'], 2, '0', STR_PAD_LEFT);
        }
        
        $issue_date = '';
        if (!empty($_POST['issueYear']) && !empty($_POST['issueMonth']) && !empty($_POST['issueDay'])) {
            $issue_date = $_POST['issueYear'] . '-' . str_pad($_POST['issueMonth'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($_POST['issueDay'], 2, '0', STR_PAD_LEFT);
        }
        
        $expiry_date = '';
        if (!empty($_POST['expiryYear']) && !empty($_POST['expiryMonth']) && !empty($_POST['expiryDay'])) {
            $expiry_date = $_POST['expiryYear'] . '-' . str_pad($_POST['expiryMonth'], 2, '0', STR_PAD_LEFT) . '-' . str_pad($_POST['expiryDay'], 2, '0', STR_PAD_LEFT);
        }
        
        // Combine address lines
        $permanent_address = '';
        $address_lines = array();
        if (!empty($_POST['address_line1'])) $address_lines[] = $_POST['address_line1'];
        if (!empty($_POST['address_line2'])) $address_lines[] = $_POST['address_line2'];
        if (!empty($_POST['address_line3'])) $address_lines[] = $_POST['address_line3'];
        $permanent_address = implode(', ', $address_lines);
        
        if ($record_exists) {
            // Update existing record - preserve existing data and only update new/changed fields
            $update_parts = array();
            
            // Personal details - only update if new data provided and preserve existing
            if (isset($_POST['nameforthecertificates']) && !empty($_POST['nameforthecertificates'])) {
                $update_parts[] = "name_for_certificates = '" . mysqli_real_escape_string($con, $_POST['nameforthecertificates']) . "'";
            }
            if (isset($_POST['nameforthetourid']) && !empty($_POST['nameforthetourid'])) {
                $update_parts[] = "name_for_tour_id = '" . mysqli_real_escape_string($con, $_POST['nameforthetourid']) . "'";
            }
            if (!empty($permanent_address)) {
                $update_parts[] = "permanent_address = '" . mysqli_real_escape_string($con, $permanent_address) . "'";
            }
            if (isset($_POST['city']) && !empty($_POST['city'])) {
                $update_parts[] = "city = '" . mysqli_real_escape_string($con, $_POST['city']) . "'";
            }
            if (isset($_POST['postalCode']) && !empty($_POST['postalCode'])) {
                $update_parts[] = "postal_code = '" . mysqli_real_escape_string($con, $_POST['postalCode']) . "'";
            }
            if (isset($_POST['province']) && !empty($_POST['province'])) {
                $update_parts[] = "province = '" . mysqli_real_escape_string($con, $_POST['province']) . "'";
            }
            
            // Passport details - preserve existing data
            if (isset($_POST['surname']) && !empty($_POST['surname'])) {
                $update_parts[] = "surname = '" . mysqli_real_escape_string($con, $_POST['surname']) . "'";
            }
            if (isset($_POST['othername']) && !empty($_POST['othername'])) {
                $update_parts[] = "other_names = '" . mysqli_real_escape_string($con, $_POST['othername']) . "'";
            }
            if (!empty($date_of_birth)) {
                $update_parts[] = "date_of_birth = '" . mysqli_real_escape_string($con, $date_of_birth) . "'";
            }
            if (isset($_POST['gender']) && !empty($_POST['gender'])) {
                $update_parts[] = "gender = '" . mysqli_real_escape_string($con, $_POST['gender']) . "'";
            }
            if (isset($_POST['passportNumber']) && !empty($_POST['passportNumber'])) {
                $update_parts[] = "passport_number = '" . mysqli_real_escape_string($con, $_POST['passportNumber']) . "'";
            }
            if (!empty($issue_date)) {
                $update_parts[] = "issue_date = '" . mysqli_real_escape_string($con, $issue_date) . "'";
            }
            if (!empty($expiry_date)) {
                $update_parts[] = "expiry_date = '" . mysqli_real_escape_string($con, $expiry_date) . "'";
            }
            // UPDATED: Always allow updating employment status
            if (isset($_POST['employmentStatus']) && !empty($_POST['employmentStatus'])) {
                $update_parts[] = "employment_status = '" . mysqli_real_escape_string($con, $_POST['employmentStatus']) . "'";
            }
            if (isset($_POST['dependentStatus']) && !empty($_POST['dependentStatus'])) {
                $update_parts[] = "dependent_status = '" . mysqli_real_escape_string($con, $_POST['dependentStatus']) . "'";
            }
            
            // File updates - UPDATED: Always allow updating files
            if ($passport_copy) $update_parts[] = "passport_copy = '$passport_copy'";
            if ($photo_id) $update_parts[] = "photo_id = '$photo_id'";
            if ($visa_request_letter) $update_parts[] = "visa_request_letter = '$visa_request_letter'";
            if ($bank_statements) $update_parts[] = "bank_statements = '$bank_statements'";
            if ($employment_letter) $update_parts[] = "employment_letter = '$employment_letter'";
            if ($epf_confirmation) $update_parts[] = "epf_confirmation = '$epf_confirmation'";
            if ($pay_slips) $update_parts[] = "pay_slips = '$pay_slips'";
            if ($business_registration) $update_parts[] = "business_registration = '$business_registration'";
            if ($form_pvt_ltd) $update_parts[] = "form_pvt_ltd = '$form_pvt_ltd'";
            if ($company_statements) $update_parts[] = "company_statements = '$company_statements'";
            if ($service_letters) $update_parts[] = "service_letters = '$service_letters'";
            if ($student_letter) $update_parts[] = "student_letter = '$student_letter'";
            if ($dependent_confirmation) $update_parts[] = "dependent_confirmation = '$dependent_confirmation'";
            if ($dependent_income) $update_parts[] = "dependent_income = '$dependent_income'";
            if ($other_documents) $update_parts[] = "other_documents = '$other_documents'";
            
            // CHANGED: Always set year from tour data
            $update_parts[] = "year = '" . mysqli_real_escape_string($con, $tour_year) . "'";
            $update_parts[] = "updated_at = '" . date('Y-m-d H:i:s') . "'";
            
            if (!empty($update_parts)) {
                $update_query = "UPDATE visa_applications SET " . implode(', ', $update_parts) . " WHERE nic_number = '$nic_number' AND tourname = '$tour_name'";
                
                if (mysqli_query($con, $update_query)) {
                    $response['success'] = true;
                    $response['message'] = 'Data updated successfully';
                } else {
                    throw new Exception('Database update failed: ' . mysqli_error($con));
                }
            } else {
                $response['success'] = true;
                $response['message'] = 'No changes to update';
            }
        } else {
            // Insert new record
            $fields = array(
                'nic_number' => $nic_number,
                'tourname' => $tour_name,
                'year' => mysqli_real_escape_string($con, $tour_year), // CHANGED: Auto-set year from tour
                'name_for_certificates' => mysqli_real_escape_string($con, $_POST['nameforthecertificates'] ?? ''),
                'name_for_tour_id' => mysqli_real_escape_string($con, $_POST['nameforthetourid'] ?? ''),
                'permanent_address' => mysqli_real_escape_string($con, $permanent_address),
                'city' => mysqli_real_escape_string($con, $_POST['city'] ?? ''),
                'postal_code' => mysqli_real_escape_string($con, $_POST['postalCode'] ?? ''),
                'province' => mysqli_real_escape_string($con, $_POST['province'] ?? ''),
                'surname' => mysqli_real_escape_string($con, $_POST['surname'] ?? ''),
                'other_names' => mysqli_real_escape_string($con, $_POST['othername'] ?? ''),
                'date_of_birth' => mysqli_real_escape_string($con, $date_of_birth),
                'gender' => mysqli_real_escape_string($con, $_POST['gender'] ?? ''),
                'passport_number' => mysqli_real_escape_string($con, $_POST['passportNumber'] ?? ''),
                'issue_date' => mysqli_real_escape_string($con, $issue_date),
                'expiry_date' => mysqli_real_escape_string($con, $expiry_date),
                'employment_status' => mysqli_real_escape_string($con, $_POST['employmentStatus'] ?? ''),
                'dependent_status' => mysqli_real_escape_string($con, $_POST['dependentStatus'] ?? ''),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );
            
            // Add file fields only if files were uploaded
            if ($passport_copy) $fields['passport_copy'] = $passport_copy;
            if ($photo_id) $fields['photo_id'] = $photo_id;
            if ($visa_request_letter) $fields['visa_request_letter'] = $visa_request_letter;
            if ($bank_statements) $fields['bank_statements'] = $bank_statements;
            if ($employment_letter) $fields['employment_letter'] = $employment_letter;
            if ($epf_confirmation) $fields['epf_confirmation'] = $epf_confirmation;
            if ($pay_slips) $fields['pay_slips'] = $pay_slips;
            if ($business_registration) $fields['business_registration'] = $business_registration;
            if ($form_pvt_ltd) $fields['form_pvt_ltd'] = $form_pvt_ltd;
            if ($company_statements) $fields['company_statements'] = $company_statements;
            if ($service_letters) $fields['service_letters'] = $service_letters;
            if ($student_letter) $fields['student_letter'] = $student_letter;
            if ($dependent_confirmation) $fields['dependent_confirmation'] = $dependent_confirmation;
            if ($dependent_income) $fields['dependent_income'] = $dependent_income;
            if ($other_documents) $fields['other_documents'] = $other_documents;
            
            $columns = implode(', ', array_keys($fields));
            $values = "'" . implode("', '", array_values($fields)) . "'";
            $insert_query = "INSERT INTO visa_applications ($columns) VALUES ($values)";
            
            if (mysqli_query($con, $insert_query)) {
                $response['success'] = true;
                $response['message'] = 'Data saved successfully';
            } else {
                throw new Exception('Database insert failed: ' . mysqli_error($con));
            }
        }
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    // Return JSON response for AJAX requests
    if (isset($_POST['ajax']) && $_POST['ajax'] == 1) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Redirect or show message for regular form submissions
    if ($response['success']) {
        $success_message = $response['message'];
        // Re-fetch updated data
        $query = "SELECT * FROM visa_applications WHERE nic_number = '$nic_number' AND tourname = '$tour_name'";
        $result = mysqli_query($con, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $existing_data = mysqli_fetch_assoc($result);
            $editing = true;
        }
    } else {
        $error_message = $response['message'];
    }
}

// Function to check if field should be locked (has existing data) - UPDATED: Don't lock files and employment
function shouldLockField($existing_data, $field_name) {
    // Don't lock file upload fields and employment selections
    $unlocked_fields = ['passport_copy', 'photo_id', 'visa_request_letter', 'bank_statements', 
                       'employment_letter', 'epf_confirmation', 'pay_slips', 'business_registration', 
                       'form_pvt_ltd', 'company_statements', 'service_letters', 'student_letter',
                       'dependent_confirmation', 'dependent_income', 'other_documents', 
                       'employment_status', 'dependent_status'];
    
    if (in_array($field_name, $unlocked_fields)) {
        return false;
    }
    
    return $existing_data && isset($existing_data[$field_name]) && 
           !empty($existing_data[$field_name]) && 
           $existing_data[$field_name] !== null;
}

// Parse existing dates for display
$birth_year = $birth_month = $birth_day = '';
$issue_year = $issue_month = $issue_day = '';
$expiry_year = $expiry_month = $expiry_day = '';
$address_line1 = $address_line2 = $address_line3 = '';

if ($existing_data) {
    if (!empty($existing_data['date_of_birth'])) {
        $birth_parts = explode('-', $existing_data['date_of_birth']);
        if (count($birth_parts) == 3) {
            $birth_year = $birth_parts[0];
            $birth_month = intval($birth_parts[1]);
            $birth_day = intval($birth_parts[2]);
        }
    }
    
    if (!empty($existing_data['issue_date'])) {
        $issue_parts = explode('-', $existing_data['issue_date']);
        if (count($issue_parts) == 3) {
            $issue_year = $issue_parts[0];
            $issue_month = intval($issue_parts[1]);
            $issue_day = intval($issue_parts[2]);
        }
    }
    
    if (!empty($existing_data['expiry_date'])) {
        $expiry_parts = explode('-', $existing_data['expiry_date']);
        if (count($expiry_parts) == 3) {
            $expiry_year = $expiry_parts[0];
            $expiry_month = intval($expiry_parts[1]);
            $expiry_day = intval($expiry_parts[2]);
        }
    }
    
    if (!empty($existing_data['permanent_address'])) {
        $address_parts = explode(', ', $existing_data['permanent_address']);
        $address_line1 = $address_parts[0] ?? '';
        $address_line2 = $address_parts[1] ?? '';
        $address_line3 = $address_parts[2] ?? '';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tour_title); ?> - Application Form</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- ADDED: Icon support -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="apple-touch-icon" sizes="180x180" href="icon/apple-touch-icon.png">
    <link rel="icon" type="icon/favicon-32x32.png" sizes="32x32" href="../assets/icons/favicon-32x32.png">
    <link rel="icon" type="icon/favicon-16x16.png" sizes="16x16" href="../assets/icons/favicon-16x16.png">
    <link rel="manifest" href="icon/site.webmanifest">
    
    <style>
        /* Enhanced CSS with field validation and animations */
        :root {
            --primary-color: #1a2b49;
            --secondary-color: #ff7e00;
            --text-color: #333333;
            --light-gray: #f5f5f5;
            --medium-gray: #e0e0e0;
            --dark-gray: rgb(65, 62, 62);
            --white: #ffffff;
            --shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
            --success-color: #27ae60;
            --error-color: #e74c3c;
        }

        body {
            font-family: 'Poppins', 'Muli', sans-serif;
            background-color: var(--light-gray);
            color: var(--text-color);
            margin: 0;
            padding: 20px 0;
            line-height: 1.6;
        }

        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            background-color: var(--white);
            border-radius: 12px;
            box-shadow: var(--shadow);
        }

        /* Tour Information Header */
        .tour-info {
            /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
            color: black;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
        }

        .tour-info h2 {
            margin-bottom: 10px;
            font-size: 32px;
            font-weight: 700;
        }

        .tour-info p {
            font-size: 18px;
            margin-bottom: 15px;
        }

        .tour-details {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .tour-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
            font-weight: 600;
        }

        .page-title {
            text-align: center;
            margin-bottom: 30px;
            color: var(--primary-color);
            font-size: 28px;
            font-weight: 600;
            border-bottom: 3px solid var(--secondary-color);
            padding-bottom: 15px;
        }

        .page-title small {
            font-size: 16px;
            color: var(--dark-gray);
            display: block;
            margin-top: 10px;
            font-weight: 400;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid;
            font-weight: 500;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
            border-color: #bee5eb;
        }

        /* Form Sections */
        .form-section {
            padding: 30px;
            background-color: #f9fbfe;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid var(--medium-gray);
        }

        .section-title {
            color: var(--primary-color);
            margin-bottom: 25px;
            font-size: 22px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::before {
            content: '';
            width: 4px;
            height: 30px;
            background-color: var(--secondary-color);
            border-radius: 2px;
        }

        /* NIC Search Section inside Personal Details */
        .nic-search-section {
            background-color: #e8f4fd;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 2px solid var(--secondary-color);
        }

        .nic-search-form {
            display: flex;
            gap: 15px;
            align-items: end;
        }

        .nic-search-form .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        .btn-search {
            background-color: var(--secondary-color);
            color: var(--white);
            padding: 12px 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: var(--transition);
            height: fit-content;
        }

        .btn-search:hover {
            background-color: #ff9f40;
            transform: translateY(-2px);
        }

        /* Form Elements */
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -15px;
            gap: 0;
        }

        .form-col {
            flex: 1;
            padding: 0 15px;
            min-width: 280px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-color);
            font-size: 15px;
            position: relative;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--medium-gray);
            border-radius: 8px;
            font-size: 16px;
            transition: var(--transition);
            background-color: var(--white);
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 3px rgba(255, 126, 0, 0.1);
        }

        /* Field validation states */
        .form-group input.valid,
        .form-group select.valid,
        .form-group textarea.valid {
            border-color: var(--success-color);
            background-color: rgba(39, 174, 96, 0.05);
        }

        .form-group input.locked,
        .form-group select.locked,
        .form-group textarea.locked {
            background-color: var(--light-gray);
            color: var(--dark-gray);
            cursor: not-allowed;
            border-color: var(--success-color);
        }

        /* ADDED: Error states for validation */
        .form-group input.error,
        .form-group select.error,
        .form-group textarea.error {
            border-color: var(--error-color);
            background-color: rgba(231, 76, 60, 0.05);
        }

        .form-group label.error {
            border-color: var(--error-color);
            background-color: rgba(231, 76, 60, 0.05);
        }

        .file-upload.error {
            border-color: var(--error-color);
            background-color: rgba(231, 76, 60, 0.05);
        }

        /* ADDED:  field indicator */
        .form-group label[]::after {
            content: " *";
            color: var(--error-color);
            font-weight: bold;
        }

        /* Date container for separated date fields */
        .date-container {
            display: flex;
            gap: 10px;
        }

        .date-container select {
            flex: 1;
        }

        /* Address lines */
        .address-lines {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* File Upload Styling */
        .file-upload {
            border: 2px dashed var(--secondary-color);
            padding: 30px;
            text-align: center;
            border-radius: 12px;
            background-color: rgba(255, 126, 0, 0.05);
            transition: var(--transition);
            position: relative;
            cursor: pointer;
        }

        .file-upload:hover {
            background-color: rgba(255, 126, 0, 0.1);
            border-color: #ff9f40;
        }

        .file-upload.uploading {
            border-color: var(--secondary-color);
            background-color: rgba(255, 126, 0, 0.1);
        }

        .file-upload.uploaded {
            border-color: var(--success-color);
            background-color: rgba(39, 174, 96, 0.05);
        }

        .upload-icon {
            display: block;
            margin: 0 auto 15px;
            font-size: 48px;
            color: var(--secondary-color);
        }

        .file-upload h4 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 18px;
            font-weight: 600;
        }

        .file-upload p {
            color: var(--dark-gray);
            margin-bottom: 8px;
        }

        .file-name-display {
            margin-top: 15px;
            font-weight: 600;
            color: var(--success-color);
            min-height: 20px;
            word-break: break-word;
        }

        /* Upload animation */
        .upload-progress {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            display: none;
        }

        .spinner {
            border: 4px solid var(--light-gray);
            border-top: 4px solid var(--secondary-color);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Radio and Checkbox Groups */
        .radio-group {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .radio-group label {
            display: flex;
            align-items: center;
            cursor: pointer;
            font-weight: 500;
            padding: 10px 15px;
            border: 2px solid var(--medium-gray);
            border-radius: 8px;
            transition: var(--transition);
            background-color: var(--white);
        }

        .radio-group label:hover {
            border-color: var(--secondary-color);
            background-color: rgba(255, 126, 0, 0.05);
        }

        .radio-group input[type="radio"] {
            margin-right: 8px;
            width: auto;
        }

        /* Buttons */
        .btn {
            padding: 15px 30px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 140px;
            margin: 20px auto;
            display: block;
        }

        .btn-submit {
            background-color: var(--success-color);
            color: var(--white);
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
        }

        .btn-submit:hover {
            background-color: #2ecc71;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(39, 174, 96, 0.4);
        }

        /* Employment Sections */
        .employment-section {
            margin-top: 20px;
            padding: 20px;
            border: 1px solid var(--medium-gray);
            border-radius: 8px;
            background-color: var(--white);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 20px;
            }

            .tour-info h2 {
                font-size: 24px;
            }

            .tour-details {
                flex-direction: column;
                gap: 10px;
            }

            .page-title {
                font-size: 24px;
            }

            .form-section {
                padding: 20px;
            }

            .section-title {
                font-size: 20px;
            }

            .form-row {
                flex-direction: column;
                margin: 0;
            }

            .form-col {
                padding: 0;
                min-width: auto;
                margin-bottom: 15px;
            }

            .file-upload {
                padding: 20px;
            }

            .upload-icon {
                font-size: 32px;
            }

            .date-container {
                flex-direction: column;
            }

            .nic-search-form {
                flex-direction: column;
            }
        }

        /* Success animation */
        @keyframes successPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .success-animation {
            animation: successPulse 0.5s ease;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="page-title">
        📄 Visa Document Submission
        <small>Tour: <?php echo htmlspecialchars($tour_title); ?></small>
        <?php if ($editing): ?>
            <small>✏️ Editing Application for NIC: <?php echo htmlspecialchars($existing_data['nic_number']); ?></small>
        <?php endif; ?>
    </h1>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            ✅ <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger">
            ❌ <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <!-- NIC Search Section inside Personal Details -->
    <div class="nic-search-section">
        <h4 style="margin-top: 0; color: var(--primary-color);">🔍 Search Existing Application</h4>
        <form method="post" class="nic-search-form">
            <div class="form-group">
                <label for="search_nic">Enter NIC Number to Search:</label>
                <input type="text" id="search_nic" name="search_nic" 
                       value="<?php echo htmlspecialchars($nic_search); ?>" 
                       placeholder="Enter NIC number to search existing data">
            </div>
            <button type="submit" class="btn-search">🔍 Search</button>
        </form>
        
        <?php if (isset($_POST['search_nic']) && !empty($_POST['search_nic']) && !$existing_data): ?>
            <div class="alert alert-info" style="margin-top: 15px;">
                ℹ️ No existing record found for NIC: <?php echo htmlspecialchars($_POST['search_nic']); ?> in <?php echo htmlspecialchars($tour_name); ?>. You can create a new application.
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Single Page Form -->
    <form id="visaApplicationForm" method="post" enctype="multipart/form-data">
        <input type="hidden" name="ajax" value="1">
        
        <!-- Personal Details Section with NIC Search -->
        <div class="form-section">
            <div class="section-title">
                <h3>👤 Personal Details</h3>
            </div>
            
            <!-- NIC Number Field -->
            <div class="form-group">
                <label for="nic_number" >🆔 NIC Number</label>
                <input type="text" id="nic_number" name="nic_number"  
                       value="<?php echo $existing_data ? htmlspecialchars($existing_data['nic_number']) : htmlspecialchars($nic_search); ?>"
                       <?php echo shouldLockField($existing_data, 'nic_number') ? 'readonly class="locked valid"' : ''; ?>
                       placeholder="Enter your NIC number">
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="nameforthecertificates" >📜 Name for the Certificates</label>
                        <input type="text" id="nameforthecertificates" name="nameforthecertificates" 
                               value="<?php echo $existing_data ? htmlspecialchars($existing_data['name_for_certificates']) : ''; ?>"
                               <?php echo shouldLockField($existing_data, 'name_for_certificates') ? 'readonly class="locked valid"' : ''; ?>
                               placeholder="Name for the certificates">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="nameforthetourid" >🎫 Name for the Tour ID<br><small>(One Short Name, eg: Nuwan)</small></label>
                        <input type="text" id="nameforthetourid" name="nameforthetourid" 
                               value="<?php echo $existing_data ? htmlspecialchars($existing_data['name_for_tour_id']) : ''; ?>"
                               <?php echo shouldLockField($existing_data, 'name_for_tour_id') ? 'readonly class="locked valid"' : ''; ?>
                               placeholder="Short name for tour ID">
                    </div>
                </div>
            </div>

            <!-- Multi-line Address -->
            <div class="form-group">
                <label >🏠 Permanent Address</label>
                <div class="address-lines">
                    <input type="text" id="address_line1" name="address_line1" 
                           value="<?php echo htmlspecialchars($address_line1); ?>"
                           <?php echo shouldLockField($existing_data, 'permanent_address') ? 'readonly class="locked valid"' : ''; ?>
                           placeholder="Address Line 1">
                    <input type="text" id="address_line2" name="address_line2"
                           value="<?php echo htmlspecialchars($address_line2); ?>"
                           <?php echo shouldLockField($existing_data, 'permanent_address') ? 'readonly class="locked valid"' : ''; ?>
                           placeholder="Address Line 2 (Optional)">
                    <input type="text" id="address_line3" name="address_line3"
                           value="<?php echo htmlspecialchars($address_line3); ?>"
                           <?php echo shouldLockField($existing_data, 'permanent_address') ? 'readonly class="locked valid"' : ''; ?>
                           placeholder="Address Line 3 (Optional)">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="city" >🏙️ City</label>
                        <input type="text" id="city" name="city" 
                               value="<?php echo $existing_data ? htmlspecialchars($existing_data['city']) : ''; ?>"
                               <?php echo shouldLockField($existing_data, 'city') ? 'readonly class="locked valid"' : ''; ?>
                               placeholder="Enter city">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="postalCode" >📮 Postal Code</label>
                        <input type="text" id="postalCode" name="postalCode" 
                               value="<?php echo $existing_data ? htmlspecialchars($existing_data['postal_code']) : ''; ?>"
                               <?php echo shouldLockField($existing_data, 'postal_code') ? 'readonly class="locked valid"' : ''; ?>
                               placeholder="Enter postal code">
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="province" >🗺️ Province</label>
                <input type="text" id="province" name="province" 
                       value="<?php echo $existing_data ? htmlspecialchars($existing_data['province']) : ''; ?>"
                       <?php echo shouldLockField($existing_data, 'province') ? 'readonly class="locked valid"' : ''; ?>
                       placeholder="Enter province">
            </div>
        </div>

        <!-- Passport Details Section -->
        <div class="form-section">
            <div class="section-title">
                <h3>📘 Passport Details</h3>
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label for="surname" >👤 Surname (As given in the Passport)</label>
                        <input type="text" id="surname" name="surname" 
                               value="<?php echo $existing_data ? htmlspecialchars($existing_data['surname']) : ''; ?>"
                               <?php echo shouldLockField($existing_data, 'surname') ? 'readonly class="locked valid"' : ''; ?>
                               placeholder="Surname from passport">
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label for="othername" >👥 Other Names (As given in the Passport)</label>
                        <input type="text" id="othername" name="othername" 
                               value="<?php echo $existing_data ? htmlspecialchars($existing_data['other_names']) : ''; ?>"
                               <?php echo shouldLockField($existing_data, 'other_names') ? 'readonly class="locked valid"' : ''; ?>
                               placeholder="Other names from passport">
                    </div>
                </div>
            </div>

            <!-- Separated Date of Birth -->
            <div class="form-group">
                <label >📅 Date of Birth (As given in the Passport)</label>
                <div class="date-container">
                    <select id="birthYear" name="birthYear"  <?php echo shouldLockField($existing_data, 'date_of_birth') ? 'disabled class="locked valid"' : ''; ?>>
                        <option value="">Year</option>
                        <?php 
                        for($year = 1950; $year <= 2010; $year++) {
                            $selected = ($birth_year == $year) ? 'selected' : '';
                            echo "<option value='$year' $selected>$year</option>";
                        }
                        ?>
                    </select>
                    
                    <select id="birthMonth" name="birthMonth"  <?php echo shouldLockField($existing_data, 'date_of_birth') ? 'disabled class="locked valid"' : ''; ?>>
                        <option value="">Month</option>
                        <?php 
                        $months = [
                            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                        ];
                        foreach($months as $num => $name) {
                            $selected = ($birth_month == $num) ? 'selected' : '';
                            echo "<option value='$num' $selected>$name</option>";
                        }
                        ?>
                    </select>
                    
                    <select id="birthDay" name="birthDay"  <?php echo shouldLockField($existing_data, 'date_of_birth') ? 'disabled class="locked valid"' : ''; ?>>
                        <option value="">Day</option>
                        <?php 
                        for($day = 1; $day <= 31; $day++) {
                            $selected = ($birth_day == $day) ? 'selected' : '';
                            echo "<option value='$day' $selected>$day</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <!-- Gender without default selection -->
            <div class="form-group">
                <label for="gender" >⚧ Gender (As given in the Passport)</label>
                <select id="gender" name="gender"  <?php echo shouldLockField($existing_data, 'gender') ? 'disabled class="locked valid"' : ''; ?>>
                    <option value="">Select Gender</option>
                    <option value="male" <?php echo ($existing_data && $existing_data['gender'] == 'male') ? 'selected' : ''; ?>>👨 Male</option>
                    <option value="female" <?php echo ($existing_data && $existing_data['gender'] == 'female') ? 'selected' : ''; ?>>👩 Female</option>
                </select>
            </div>

            <div class="form-group">
                <label for="passportNumber" >📘 Passport Number (As given in the Passport)</label>
                <input type="text" id="passportNumber" name="passportNumber" 
                       value="<?php echo $existing_data ? htmlspecialchars($existing_data['passport_number']) : ''; ?>"
                       <?php echo shouldLockField($existing_data, 'passport_number') ? 'readonly class="locked valid"' : ''; ?>
                       placeholder="Passport number">
            </div>
            
            <div class="form-row">
                <div class="form-col">
                    <div class="form-group">
                        <label >📅 Issue Date</label>
                        <div class="date-container">
                            <select id="issueYear" name="issueYear"  <?php echo shouldLockField($existing_data, 'issue_date') ? 'disabled class="locked valid"' : ''; ?>>
                                <option value="">Year</option>
                                <?php 
                                for($year = 2015; $year <= 2025; $year++) {
                                    $selected = ($issue_year == $year) ? 'selected' : '';
                                    echo "<option value='$year' $selected>$year</option>";
                                }
                                ?>
                            </select>
                            
                            <select id="issueMonth" name="issueMonth"  <?php echo shouldLockField($existing_data, 'issue_date') ? 'disabled class="locked valid"' : ''; ?>>
                                <option value="">Month</option>
                                <?php 
                                foreach($months as $num => $name) {
                                    $selected = ($issue_month == $num) ? 'selected' : '';
                                    echo "<option value='$num' $selected>$name</option>";
                                }
                                ?>
                            </select>
                            
                            <select id="issueDay" name="issueDay"  <?php echo shouldLockField($existing_data, 'issue_date') ? 'disabled class="locked valid"' : ''; ?>>
                                <option value="">Day</option>
                                <?php 
                                for($day = 1; $day <= 31; $day++) {
                                    $selected = ($issue_day == $day) ? 'selected' : '';
                                    echo "<option value='$day' $selected>$day</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-col">
                    <div class="form-group">
                        <label >⏰ Expiry Date</label>
                        <div class="date-container">
                            <select id="expiryYear" name="expiryYear"  <?php echo shouldLockField($existing_data, 'expiry_date') ? 'disabled class="locked valid"' : ''; ?>>
                                <option value="">Year</option>
                                <?php 
                                for($year = 2026; $year <= 2035; $year++) {
                                    $selected = ($expiry_year == $year) ? 'selected' : '';
                                    echo "<option value='$year' $selected>$year</option>";
                                }
                                ?>
                            </select>
                            
                            <select id="expiryMonth" name="expiryMonth"  <?php echo shouldLockField($existing_data, 'expiry_date') ? 'disabled class="locked valid"' : ''; ?>>
                                <option value="">Month</option>
                                <?php 
                                foreach($months as $num => $name) {
                                    $selected = ($expiry_month == $num) ? 'selected' : '';
                                    echo "<option value='$num' $selected>$name</option>";
                                }
                                ?>
                            </select>
                            
                            <select id="expiryDay" name="expiryDay"  <?php echo shouldLockField($existing_data, 'expiry_date') ? 'disabled class="locked valid"' : ''; ?>>
                                <option value="">Day</option>
                                <?php 
                                for($day = 1; $day <= 31; $day++) {
                                    $selected = ($expiry_day == $day) ? 'selected' : '';
                                    echo "<option value='$day' $selected>$day</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Visa Documents Section -->
        <div class="form-section">
            <div class="section-title">
                <h3>📋 Visa Documents</h3>
            </div>
            
            <!-- Passport Copy (SINGLE FILE) -->
            <div class="form-group">
                <label for="passportCopy" >📄 Passport Copy (Both Sides)</label>
                <div class="file-upload <?php echo ($existing_data && $existing_data['passport_copy']) ? 'uploaded' : ''; ?>">
                    <div class="upload-progress">
                        <div class="spinner"></div>
                        <p>Uploading...</p>
                    </div>
                    <span class="upload-icon">📄</span>
                    <h4>Upload Passport Copy</h4>
                    <p>Drag and drop your file here or click to browse</p>
                    <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: PDF, JPG, PNG (Max size: 5MB)</p>
                    <input type="file" id="passportCopy" name="passportCopy" accept=".pdf,.jpg,.jpeg,.png" >
                    <div class="file-name-display" id="passportFileDisplay">
                        <?php if ($existing_data && $existing_data['passport_copy']): ?>
                            ✅ Current file: <?php echo htmlspecialchars($existing_data['passport_copy']); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Passport Size Photo (SINGLE FILE) -->
            <div class="form-group">
                <label for="photoId" >🖼️ Passport Size Photo</label>
                <div class="file-upload <?php echo ($existing_data && $existing_data['photo_id']) ? 'uploaded' : ''; ?>">
                    <div class="upload-progress">
                        <div class="spinner"></div>
                        <p>Uploading...</p>
                    </div>
                    <span class="upload-icon">🖼️</span>
                    <h4>Upload Passport Size Photo</h4>
                    <p>Drag and drop your file here or click to browse</p>
                    <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PNG (Max size: 2MB)</p>
                    <input type="file" id="photoId" name="photoId" accept=".jpg,.jpeg,.png" >
                    <div class="file-name-display" id="photoFileDisplay">
                        <?php if ($existing_data && $existing_data['photo_id']): ?>
                            ✅ Current file: <?php echo htmlspecialchars($existing_data['photo_id']); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Visa Request Letter (MULTIPLE FILES) -->
            <div class="form-group">
                <label for="visaRequestLetter" >📝 Visa Request Letter (Signed)</label>
                <div class="file-upload <?php echo ($existing_data && $existing_data['visa_request_letter']) ? 'uploaded' : ''; ?>">
                    <div class="upload-progress">
                        <div class="spinner"></div>
                        <p>Uploading...</p>
                    </div>
                    <span class="upload-icon">📝</span>
                    <h4>Upload Visa Request Letter</h4>
                    <p>Drag and drop your files here or click to browse</p>
                    <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF (Max size: 2MB each)</p>
                    <input type="file" id="visaRequestLetter" name="visaRequestLetter[]" multiple accept=".jpg,.jpeg,.pdf" >
                    <div class="file-name-display" id="visaRequestLetterDisplay">
                        <?php if ($existing_data && $existing_data['visa_request_letter']): ?>
                            ✅ Current files: <?php echo htmlspecialchars($existing_data['visa_request_letter']); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Bank Statements (MULTIPLE FILES) -->
            <div class="form-group">
                <label for="bankStatements" >🏦 Bank Statements</label>
                <div class="file-upload <?php echo ($existing_data && $existing_data['bank_statements']) ? 'uploaded' : ''; ?>">
                    <div class="upload-progress">
                        <div class="spinner"></div>
                        <p>Uploading...</p>
                    </div>
                    <span class="upload-icon">🏦</span>
                    <h4>Upload Bank Statements</h4>
                    <p>Drag and drop your files here or click to browse</p>
                    <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PNG, PDF (Max size: 2MB each)</p>
                    <input type="file" id="bankStatements" name="bankStatements[]" multiple accept=".jpg,.jpeg,.pdf,.png" >
                    <div class="file-name-display" id="bankStatementsDisplay">
                        <?php if ($existing_data && $existing_data['bank_statements']): ?>
                            ✅ Current files: <?php echo htmlspecialchars($existing_data['bank_statements']); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Employment Status Selection -->
            <div class="form-group">
                <label style="border-bottom: 2px solid var(--medium-gray); padding-bottom: 10px; margin-bottom: 20px;" >
                    💼 If you are
                </label>
                
                <div class="status-selector">
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="employmentStatus" value="employee" onclick="showEmploymentSection('employee')" 
                                   <?php echo ($existing_data && $existing_data['employment_status'] == 'employee') ? 'checked' : ''; ?>>
                            <span>👨‍💼 Employee</span>
                        </label>
                        <label>
                            <input type="radio" name="employmentStatus" value="business" onclick="showEmploymentSection('business')" 
                                   <?php echo ($existing_data && $existing_data['employment_status'] == 'business') ? 'checked' : ''; ?>>
                            <span>🏢 Business Owner</span>
                        </label>
                        <label>
                            <input type="radio" name="employmentStatus" value="freelancer" onclick="showEmploymentSection('freelancer')" 
                                   <?php echo ($existing_data && $existing_data['employment_status'] == 'freelancer') ? 'checked' : ''; ?>>
                            <span>💻 Freelancer</span>
                        </label>
                        <label>
                            <input type="radio" name="employmentStatus" value="student" onclick="showEmploymentSection('student_dependent')" 
                                   <?php echo ($existing_data && $existing_data['employment_status'] == 'student') ? 'checked' : ''; ?>>
                            <span>🎓 Student/Dependent</span>
                        </label>
                    </div>
                </div>
                
                <!-- Employee Section -->
                <div id="employeeSection" class="employment-section" style="display: <?php echo ($existing_data && $existing_data['employment_status'] == 'employee') ? 'block' : 'none'; ?>;">
                    <div class="file-upload" style="margin-bottom: 20px;">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">📝</span>
                        <h4>Upload Employment Confirmation Letter with Leave Confirmation <span style="color: var(--error-color);">*</span></h4>
                        <p>Drag and drop your files here or click to browse</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF (Max size: 2MB each)</p>
                        <input type="file" id="employmentLetter" name="employmentLetter[]" multiple accept=".jpg,.jpeg,.pdf">
                        <div class="file-name-display" id="employmentLetterDisplay">
                            <?php if ($existing_data && $existing_data['employment_letter']): ?>
                                ✅ Current files: <?php echo htmlspecialchars($existing_data['employment_letter']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="file-upload" style="margin-bottom: 20px;">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">📊</span>
                        <h4>Upload EPF Confirmation Letter</h4>
                        <p>Drag and drop your files here or click to browse</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF (Max size: 2MB each)</p>
                        <input type="file" id="epfConfirmation" name="epfConfirmation[]" multiple accept=".jpg,.jpeg,.pdf">
                        <div class="file-name-display" id="epfConfirmationDisplay">
                            <?php if ($existing_data && $existing_data['epf_confirmation']): ?>
                                ✅ Current files: <?php echo htmlspecialchars($existing_data['epf_confirmation']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="file-upload" style="margin-bottom: 20px;">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">💰</span>
                        <h4>Upload Pay Slips of Last 3 Months <span style="color: var(--error-color);">*</span></h4>
                        <p>Drag and drop your files here or click to browse</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF (Max size: 2MB each)</p>
                        <input type="file" id="paySlips" name="paySlips[]" multiple accept=".jpg,.jpeg,.pdf">
                        <div class="file-name-display" id="paySlipsDisplay">
                            <?php if ($existing_data && $existing_data['pay_slips']): ?>
                                ✅ Current files: <?php echo htmlspecialchars($existing_data['pay_slips']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="file-upload">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">📋</span>
                        <h4>Upload Other Documents</h4>
                        <p>Birth or Marriage Certificate if Applicable, Any other letters</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF (Max size: 2MB each)</p>
                        <input type="file" id="otherDocumentsEmployee" name="otherDocuments[]" multiple accept=".jpg,.jpeg,.pdf">
                        <div class="file-name-display" id="otherDocumentsEmployeeDisplay">
                            <?php if ($existing_data && $existing_data['other_documents']): ?>
                                ✅ Current files: <?php echo htmlspecialchars($existing_data['other_documents']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Business Owner Section -->
                <div id="businessSection" class="employment-section" style="display: <?php echo ($existing_data && $existing_data['employment_status'] == 'business') ? 'block' : 'none'; ?>;">
                    <div class="file-upload" style="margin-bottom: 20px;">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">🏢</span>
                        <h4>Upload Original Business Registration and English Translation <span style="color: var(--error-color);">*</span></h4>
                        <p>Drag and drop your files here or click to browse</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF (Max size: 2MB each)</p>
                        <input type="file" id="businessRegistration" name="businessRegistration[]" multiple accept=".jpg,.jpeg,.pdf">
                        <div class="file-name-display" id="businessRegistrationDisplay">
                            <?php if ($existing_data && $existing_data['business_registration']): ?>
                                ✅ Current files: <?php echo htmlspecialchars($existing_data['business_registration']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="file-upload" style="margin-bottom: 20px;">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">📑</span>
                        <h4>Upload Form 1 for PVT LTD</h4>
                        <p>Drag and drop your files here or click to browse</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF (Max size: 2MB each)</p>
                        <input type="file" id="formPvtLtd" name="formPvtLtd[]" multiple accept=".jpg,.jpeg,.pdf">
                        <div class="file-name-display" id="formPvtLtdDisplay">
                            <?php if ($existing_data && $existing_data['form_pvt_ltd']): ?>
                                ✅ Current files: <?php echo htmlspecialchars($existing_data['form_pvt_ltd']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="file-upload" style="margin-bottom: 20px;">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">📈</span>
                        <h4>Upload Company Account Statements of Last 3 Months</h4>
                        <p>Drag and drop your files here or click to browse</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF (Max size: 2MB each)</p>
                        <input type="file" id="companyStatements" name="companyStatements[]" multiple accept=".jpg,.jpeg,.pdf">
                        <div class="file-name-display" id="companyStatementsDisplay">
                            <?php if ($existing_data && $existing_data['company_statements']): ?>
                                ✅ Current files: <?php echo htmlspecialchars($existing_data['company_statements']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="file-upload">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">📋</span>
                        <h4>Upload Other Documents</h4>
                        <p>Birth or Marriage Certificate if Applicable, Any other letters</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF (Max size: 2MB each)</p>
                        <input type="file" id="otherDocumentsBusiness" name="otherDocuments[]" multiple accept=".jpg,.jpeg,.pdf">
                        <div class="file-name-display" id="otherDocumentsBusinessDisplay">
                            <?php if ($existing_data && $existing_data['other_documents']): ?>
                                ✅ Current files: <?php echo htmlspecialchars($existing_data['other_documents']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Freelancer Section -->
                <div id="freelancerSection" class="employment-section" style="display: <?php echo ($existing_data && $existing_data['employment_status'] == 'freelancer') ? 'block' : 'none'; ?>;">
                    <div class="file-upload" style="margin-bottom: 20px;">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">📝</span>
                        <h4>Upload Service Letters from Clients <span style="color: var(--error-color);">*</span></h4>
                        <p>Drag and drop your files here or click to browse</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF (Max size: 2MB each)</p>
                        <input type="file" id="serviceLetters" name="serviceLetters[]" multiple accept=".jpg,.jpeg,.pdf">
                        <div class="file-name-display" id="serviceLettersDisplay">
                            <?php if ($existing_data && $existing_data['service_letters']): ?>
                                ✅ Current files: <?php echo htmlspecialchars($existing_data['service_letters']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="file-upload">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">📋</span>
                        <h4>Upload Other Documents</h4>
                        <p>Birth or Marriage Certificate if Applicable, Any other letters</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF (Max size: 2MB each)</p>
                        <input type="file" id="otherDocumentsFreelancer" name="otherDocuments[]" multiple accept=".jpg,.jpeg,.pdf">
                        <div class="file-name-display" id="otherDocumentsFreelancerDisplay">
                            <?php if ($existing_data && $existing_data['other_documents']): ?>
                                ✅ Current files: <?php echo htmlspecialchars($existing_data['other_documents']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Student/Dependent Section -->
                <div id="studentDependentSection" class="employment-section" style="display: <?php echo ($existing_data && $existing_data['employment_status'] == 'student') ? 'block' : 'none'; ?>;">
                    <div class="form-group">
                        <label style="border-bottom: 2px solid var(--medium-gray); padding-bottom: 10px; margin-bottom: 20px;" >
                            👨‍👩‍👧‍👦 If you are
                        </label>
                        
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="dependentStatus" value="student" onclick="showDependentSection('student')"
                                       <?php echo ($existing_data && $existing_data['dependent_status'] == 'student') ? 'checked' : ''; ?>>
                                <span>🎓 Student</span>
                            </label>
                            <label>
                                <input type="radio" name="dependentStatus" value="dependent" onclick="showDependentSection('dependent')"
                                       <?php echo ($existing_data && $existing_data['dependent_status'] == 'dependent') ? 'checked' : ''; ?>>
                                <span>👨‍👩‍👧‍👦 Dependent</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Student Sub-section -->
                    <div id="studentSubSection" style="display: <?php echo ($existing_data && $existing_data['dependent_status'] == 'student') ? 'block' : 'none'; ?>;">
                        <div class="file-upload" style="margin-bottom: 20px;">
                            <div class="upload-progress">
                                <div class="spinner"></div>
                                <p>Uploading...</p>
                            </div>
                            <span class="upload-icon">🎓</span>
                            <h4>Upload Student Confirmation Letter <span style="color: var(--error-color);">*</span></h4>
                            <p>Drag and drop your files here or click to browse</p>
                            <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF (Max size: 2MB each)</p>
                            <input type="file" id="studentLetter" name="studentLetter[]" multiple accept=".jpg,.jpeg,.pdf">
                            <div class="file-name-display" id="studentLetterDisplay">
                                <?php if ($existing_data && $existing_data['student_letter']): ?>
                                    ✅ Current files: <?php echo htmlspecialchars($existing_data['student_letter']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="file-upload" style="margin-bottom: 20px;">
                            <div class="upload-progress">
                                <div class="spinner"></div>
                                <p>Uploading...</p>
                            </div>
                            <span class="upload-icon">👨‍👩‍👧‍👦</span>
                            <h4>Upload Dependent Confirmation Letter</h4>
                            <p>Drag and drop your files here or click to browse</p>
                            <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF (Max size: 2MB each)</p>
                            <input type="file" id="dependentConfirmationStudent" name="dependentConfirmation[]" multiple accept=".jpg,.jpeg,.pdf">
                            <div class="file-name-display" id="dependentConfirmationStudentDisplay">
                                <?php if ($existing_data && $existing_data['dependent_confirmation']): ?>
                                    ✅ Current files: <?php echo htmlspecialchars($existing_data['dependent_confirmation']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="file-upload" style="margin-bottom: 20px;">
                            <div class="upload-progress">
                                <div class="spinner"></div>
                                <p>Uploading...</p>
                            </div>
                            <span class="upload-icon">💰</span>
                            <h4>Upload Dependent Income Documents</h4>
                            <p>Drag and drop your files here or click to browse</p>
                            <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF (Max size: 2MB each)</p>
                            <input type="file" id="dependentIncomeStudent" name="dependentIncome[]" multiple accept=".jpg,.jpeg,.pdf">
                            <div class="file-name-display" id="dependentIncomeStudentDisplay">
                                <?php if ($existing_data && $existing_data['dependent_income']): ?>
                                    ✅ Current files: <?php echo htmlspecialchars($existing_data['dependent_income']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="file-upload">
                            <div class="upload-progress">
                                <div class="spinner"></div>
                                <p>Uploading...</p>
                            </div>
                            <span class="upload-icon">📋</span>
                            <h4>Upload Other Documents</h4>
                            <p>Birth or Marriage Certificate if Applicable, Any other letters</p>
                            <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF (Max size: 2MB each)</p>
                            <input type="file" id="otherDocumentsStudent" name="otherDocuments[]" multiple accept=".jpg,.jpeg,.pdf">
                            <div class="file-name-display" id="otherDocumentsStudentDisplay">
                                <?php if ($existing_data && $existing_data['other_documents']): ?>
                                    ✅ Current files: <?php echo htmlspecialchars($existing_data['other_documents']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Dependent Sub-section -->
                    <div id="dependentSubSection" style="display: <?php echo ($existing_data && $existing_data['dependent_status'] == 'dependent') ? 'block' : 'none'; ?>;">
                        <div class="file-upload" style="margin-bottom: 20px;">
                            <div class="upload-progress">
                                <div class="spinner"></div>
                                <p>Uploading...</p>
                            </div>
                            <span class="upload-icon">👨‍👩‍👧‍👦</span>
                            <h4>Upload Dependent Confirmation Letter <span style="color: var(--error-color);">*</span></h4>
                            <p>Drag and drop your files here or click to browse</p>
                            <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF (Max size: 2MB each)</p>
                            <input type="file" id="dependentConfirmationDependent" name="dependentConfirmation[]" multiple accept=".jpg,.jpeg,.pdf">
                            <div class="file-name-display" id="dependentConfirmationDependentDisplay">
                                <?php if ($existing_data && $existing_data['dependent_confirmation']): ?>
                                    ✅ Current files: <?php echo htmlspecialchars($existing_data['dependent_confirmation']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="file-upload" style="margin-bottom: 20px;">
                            <div class="upload-progress">
                                <div class="spinner"></div>
                                <p>Uploading...</p>
                            </div>
                            <span class="upload-icon">💰</span>
                            <h4>Upload Dependent Income Documents</h4>
                            <p>Drag and drop your files here or click to browse</p>
                            <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF (Max size: 2MB each)</p>
                            <input type="file" id="dependentIncomeDependent" name="dependentIncome[]" multiple accept=".jpg,.jpeg,.pdf">
                            <div class="file-name-display" id="dependentIncomeDependentDisplay">
                                <?php if ($existing_data && $existing_data['dependent_income']): ?>
                                    ✅ Current files: <?php echo htmlspecialchars($existing_data['dependent_income']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="file-upload">
                            <div class="upload-progress">
                                <div class="spinner"></div>
                                <p>Uploading...</p>
                            </div>
                            <span class="upload-icon">📋</span>
                            <h4>Upload Other Documents</h4>
                            <p>Birth or Marriage Certificate if Applicable, Any other letters</p>
                            <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF (Max size: 2MB each)</p>
                            <input type="file" id="otherDocumentsDependent" name="otherDocuments[]" multiple accept=".jpg,.jpeg,.pdf">
                            <div class="file-name-display" id="otherDocumentsDependentDisplay">
                                <?php if ($existing_data && $existing_data['other_documents']): ?>
                                    ✅ Current files: <?php echo htmlspecialchars($existing_data['other_documents']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Submit Button -->
        <button type="submit" class="btn btn-submit" id="submitBtn">
            <?php echo $editing ? '💾 Update Application' : '📤 Submit Application'; ?>
        </button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('visaApplicationForm');
    const submitBtn = document.getElementById('submitBtn');
    const isEditing = <?php echo $editing ? 'true' : 'false'; ?>;
    
    // Enhanced field validation with conditional requirements
    function validateField(field) {
        const value = field.value.trim();
        
        
        if (is && value === '') {
            field.classList.add('error');
            field.classList.remove('valid');
            return false;
        } else if (value !== '') {
            field.classList.add('valid');
            field.classList.remove('error');
            return true;
        } else {
            field.classList.remove('valid', 'error');
            return !is;
        }
    }

    // Check if field is conditionally  based on employment status
    function isFieldConditionally(field) {
        const employmentStatus = form.querySelector('input[name="employmentStatus"]:checked');
        const dependentStatus = form.querySelector('input[name="dependentStatus"]:checked');
        const fieldName = field.name || field.id;
        
        if (!employmentStatus) return false;
        
        const status = employmentStatus.value;
        const depStatus = dependentStatus ? dependentStatus.value : null;
        
        // Check if field is in visible section and  for that employment type
        switch(status) {
            case 'employee':
                if (document.getElementById('employeeSection').style.display === 'block') {
                    return ['employmentLetter', 'paySlips'].includes(fieldName);
                }
                break;
            case 'business':
                if (document.getElementById('businessSection').style.display === 'block') {
                    return ['businessRegistration'].includes(fieldName);
                }
                break;
            case 'freelancer':
                if (document.getElementById('freelancerSection').style.display === 'block') {
                    return ['serviceLetters'].includes(fieldName);
                }
                break;
            case 'student':
                if (document.getElementById('studentDependentSection').style.display === 'block') {
                    if (depStatus === 'student') {
                        return ['dependentStatus', 'studentLetter'].includes(fieldName);
                    } else if (depStatus === 'dependent') {
                        return ['dependentStatus', 'dependentConfirmationDependent'].includes(fieldName);
                    }
                    return fieldName === 'dependentStatus';
                }
                break;
        }
        return false;
    }

    // Validate radio groups
    function validateRadioGroup(groupName) {
        const radios = document.querySelectorAll(`input[name="${groupName}"]`);
        const isChecked = Array.from(radios).some(radio => radio.checked);
        
        radios.forEach(radio => {
            const label = radio.closest('label');
            if (isChecked) {
                label.classList.add('valid');
                label.classList.remove('error');
            } else {
                label.classList.add('error');
                label.classList.remove('valid');
            }
        });
        
        return isChecked;
    }

    // Validate file inputs with conditional requirements
    function validateFileInput(input) {
        const hasFiles = input.files && input.files.length > 0;
        const hasExistingFile = input.closest('.file-upload').classList.contains('uploaded');
        
        if (is && !hasFiles && !hasExistingFile) {
            input.classList.add('error');
            input.closest('.file-upload').classList.add('error');
            input.classList.remove('valid');
            return false;
        } else if (hasFiles || hasExistingFile) {
            input.classList.add('valid');
            input.closest('.file-upload').classList.remove('error');
            input.classList.remove('error');
            return true;
        } else {
            input.classList.remove('valid', 'error');
            input.closest('.file-upload').classList.remove('error');
            return !is;
        }
    }
    
    // Add event listeners to all form fields for real-time validation
    const formFields = form.querySelectorAll('input, select, textarea');
    formFields.forEach(field => {
        if (!field.classList.contains('locked')) {
            field.addEventListener('input', function() {
                validateField(this);
            });
            field.addEventListener('change', function() {
                validateField(this);
            });
        }
    });
    
    // File upload handling with animation
    function setupFileUpload(inputId, displayId) {
        const input = document.getElementById(inputId);
        const display = document.getElementById(displayId);
        if (!input || !display) return;
        
        const uploadContainer = input.closest('.file-upload');
        const progressElement = uploadContainer.querySelector('.upload-progress');
        
        input.addEventListener('change', function() {
            const files = this.files;
            if (files.length > 0) {
                // Show upload animation
                progressElement.style.display = 'block';
                uploadContainer.classList.add('uploading');
                
                // Simulate upload delay
                setTimeout(() => {
                    progressElement.style.display = 'none';
                    uploadContainer.classList.remove('uploading');
                    uploadContainer.classList.add('uploaded');
                    
                    // Display file names
                    let fileNames = [];
                    for (let i = 0; i < files.length; i++) {
                        fileNames.push(files[i].name);
                    }
                    display.innerHTML = '✅ ' + fileNames.join(', ');
                    
                    // Add success animation
                    uploadContainer.classList.add('success-animation');
                    setTimeout(() => {
                        uploadContainer.classList.remove('success-animation');
                    }, 500);
                    
                    // Mark as valid
                    validateFileInput(input);
                }, 1500);
            }
        });
    }
    
    // Setup file uploads
    const fileInputs = [
        'passportCopy', 'photoId', 'visaRequestLetter', 'bankStatements',
        'employmentLetter', 'epfConfirmation', 'paySlips', 'businessRegistration',
        'formPvtLtd', 'companyStatements', 'serviceLetters', 'studentLetter',
        'dependentConfirmationStudent', 'dependentIncomeStudent', 'otherDocumentsStudent',
        'dependentConfirmationDependent', 'dependentIncomeDependent', 'otherDocumentsDependent',
        'otherDocumentsEmployee', 'otherDocumentsBusiness', 'otherDocumentsFreelancer'
    ];
    
    fileInputs.forEach(inputId => {
        setupFileUpload(inputId, inputId + 'Display');
    });
    
    // Employment status section management
    window.showEmploymentSection = function(status) {
        // Hide all sections
        const sections = ['employeeSection', 'businessSection', 'freelancerSection', 'studentDependentSection'];
        sections.forEach(sectionId => {
            const section = document.getElementById(sectionId);
            if (section) section.style.display = 'none';
        });
        
        // Show selected section
        if (status === 'employee') {
            document.getElementById('employeeSection').style.display = 'block';
        } else if (status === 'business') {
            document.getElementById('businessSection').style.display = 'block';
        } else if (status === 'freelancer') {
            document.getElementById('freelancerSection').style.display = 'block';
        } else if (status === 'student_dependent') {
            document.getElementById('studentDependentSection').style.display = 'block';
        }
        
        // Revalidate all fields after section change
        setTimeout(() => {
            formFields.forEach(field => {
                if (!field.classList.contains('locked')) {
                    validateField(field);
                }
            });
            // Validate file inputs in visible sections
            fileInputs.forEach(inputId => {
                const input = document.getElementById(inputId);
                if (input && input.closest('.employment-section') && 
                    input.closest('.employment-section').style.display === 'block') {
                    validateFileInput(input);
                }
            });
        }, 100);
    };
    
    // Dependent status section management
    window.showDependentSection = function(status) {
        const studentSub = document.getElementById('studentSubSection');
        const dependentSub = document.getElementById('dependentSubSection');
        
        if (studentSub) studentSub.style.display = 'none';
        if (dependentSub) dependentSub.style.display = 'none';
        
        if (status === 'student' && studentSub) {
            studentSub.style.display = 'block';
        } else if (status === 'dependent' && dependentSub) {
            dependentSub.style.display = 'block';
        }
        
        // Revalidate fields after dependent section change
        setTimeout(() => {
            formFields.forEach(field => {
                if (!field.classList.contains('locked')) {
                    validateField(field);
                }
            });
        }, 100);
    };
    
    // Form submission handling with enhanced validation
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate all fields
        let isValid = true;
        const errors = [];
        
        // Validate text inputs and selects
        const textFields = form.querySelectorAll('input[type="text"], input[type="email"], select');
        textFields.forEach(field => {
            if (!field.classList.contains('locked') && !validateField(field)) {
                isValid = false;
                const labelText = field.closest('.form-group').querySelector('label').textContent;
                errors.push(`${labelText.replace('*', '').trim()} is `);
            }
        });
        
        // Validate  radio groups
        if (!validateRadioGroup('employmentStatus')) {
            isValid = false;
            errors.push('Employment status selection is ');
        }
        
        // Validate dependent status if student is selected
        const employmentStatus = form.querySelector('input[name="employmentStatus"]:checked');
        if (employmentStatus && employmentStatus.value === 'student') {
            if (!validateRadioGroup('dependentStatus')) {
                isValid = false;
                errors.push('Student/Dependent status selection is ');
            }
        }
        
        // Validate file inputs (only in visible sections)
        const fileInputsToValidate = form.querySelectorAll('input[type="file"]');
        fileInputsToValidate.forEach(input => {
            const section = input.closest('.employment-section');
            const isInVisibleSection = !section || section.style.display === 'block';
            
            if (isInVisibleSection && !validateFileInput(input)) {
                isValid = false;
                const labelText = input.closest('.file-upload').querySelector('h4').textContent;
                errors.push(`${labelText.replace('*', '').trim()} is `);
            }
        });
        
        if (!isValid) {
            // Show validation errors
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger';
            alertDiv.innerHTML = '❌ Please fill in all  fields:<br>' + errors.slice(0, 5).join('<br>');
            form.insertBefore(alertDiv, form.firstChild);
            
            // Scroll to top
            window.scrollTo(0, 0);
            
            // Auto-hide error message after 7 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 7000);
            
            return;
        }
        
        // If validation passes, proceed with submission
        // Disable submit button to prevent double submission
        submitBtn.disabled = true;
        submitBtn.innerHTML = '⏳ Processing...';
        
        // Create FormData object
        const formData = new FormData(form);
        
        // Submit via AJAX
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-success';
                alertDiv.innerHTML = '✅ ' + data.message;
                form.insertBefore(alertDiv, form.firstChild);
                
                // Scroll to top
                window.scrollTo(0, 0);
                
                // Auto-hide success message after 5 seconds
                setTimeout(() => {
                    alertDiv.remove();
                }, 5000);
                
                // Refresh page to show updated locked fields
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
                
            } else {
                // Show error message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger';
                alertDiv.innerHTML = '❌ ' + data.message;
                form.insertBefore(alertDiv, form.firstChild);
                
                // Scroll to top
                window.scrollTo(0, 0);
                
                // Auto-hide error message after 5 seconds
                setTimeout(() => {
                    alertDiv.remove();
                }, 5000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-danger';
            alertDiv.innerHTML = '❌ An error occurred while submitting the form.';
            form.insertBefore(alertDiv, form.firstChild);
            
            // Scroll to top
            window.scrollTo(0, 0);
            
            // Auto-hide error message after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        })
        .finally(() => {
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.innerHTML = isEditing ? '💾 Update Application' : '📤 Submit Application';
        });
    });
    
    // Initialize form validation on page load
    formFields.forEach(field => {
        if (field.value.trim() !== '' && !field.classList.contains('locked')) {
            field.classList.add('valid');
        }
    });
    
    // Show appropriate sections based on existing data
    <?php if ($existing_data && $existing_data['employment_status']): ?>
        showEmploymentSection('<?php echo $existing_data['employment_status']; ?>');
    <?php endif; ?>
    
    <?php if ($existing_data && $existing_data['dependent_status']): ?>
        showDependentSection('<?php echo $existing_data['dependent_status']; ?>');
    <?php endif; ?>
});
</script>

</body>
</html>
