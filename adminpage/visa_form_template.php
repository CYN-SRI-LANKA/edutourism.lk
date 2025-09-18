<?php

$active = "Account";
include("../../homepage/db.php");
include("../../homepage/functions.php");
include("google_drive_api.php"); // Include Google Drive API

$drive = new GoogleDriveFormAPI();

// Tour-specific information (will be replaced by actual values)
$tourname = "{{TOUR_NAME}}";
$tourtitle = "{{TOUR_TITLE}}";
$destination = "{{DESTINATION}}";
$duration = "{{DURATION}}";

$tour_year = null;
$tour_year_query = "SELECT year FROM tours WHERE tourname = '$tourname' LIMIT 1";
$tour_year_result = mysqli_query($con, $tour_year_query);
if ($tour_year_result && mysqli_num_rows($tour_year_result) > 0) {
    $tour_year_data = mysqli_fetch_assoc($tour_year_result);
    $tour_year = $tour_year_data['year'];
}

$tour_year = date('Y');

// Check if editing existing record or searching by NIC
$editing = false;
$existingdata = null;
$nicsearch = "";

// Handle NIC search
if (isset($_POST['searchnic']) && !empty($_POST['searchnic'])) {
    $nicsearch = mysqli_real_escape_string($con, $_POST['searchnic']);
    $query = "SELECT * FROM visa_applications WHERE nic_number = '$nicsearch' AND tourname = '$tourname'";
    $result = mysqli_query($con, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $existingdata = mysqli_fetch_assoc($result);
        $editing = true;
    }
} elseif (isset($_GET['nic']) && !empty($_GET['nic'])) {
    $nic = mysqli_real_escape_string($con, $_GET['nic']);
    $query = "SELECT * FROM visa_applications WHERE nic_number = '$nic' AND tourname = '$tourname'";
    $result = mysqli_query($con, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $existingdata = mysqli_fetch_assoc($result);
        $editing = true;
        $nicsearch = $nic;
    }
}

// Initialize Google Drive API

// Function to check if field should be conditionally required
function isFieldConditionallyRequired($fieldname, $employmentstatus, $dependentstatus = null) {
    // Always required fields
    $alwaysrequired = ['nic_number', 'name_for_certificates', 'name_for_tour_id', 'addressline1', 
                       'city', 'postal_code', 'province', 'surname', 'othername', 'birthYear', 
                       'birthMonth', 'birthDay', 'gender', 'passport_number', 'issueYear', 
                       'issueMonth', 'issueDay', 'expiryYear', 'expiryMonth', 'expiryDay', 
                       'passport_copy', 'photo_id', 'visa_request_letter', 'bank_statements', 'employment_status'];
    
    if (in_array($fieldname, $alwaysrequired)) {
        return true;
    }
    
    // Conditional requirements based on employment status
    switch($employmentstatus) {
        case 'employee':
            return in_array($fieldname, ['employment_letter', 'pay_slips']);
        case 'business':
            return in_array($fieldname, ['business_registration']);
        case 'freelancer':
            return in_array($fieldname, ['service_letters']);
        case 'student':
            $studentrequired = ['dependent_status'];
            if ($dependentstatus == 'student') {
                $studentrequired[] = 'student_letter';
            } elseif ($dependentstatus == 'dependent') {
                $studentrequired[] = 'dependent_confirmation_dependent';
            }
            return in_array($fieldname, $studentrequired);
    }
    return false;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['searchnic'])) {
    $response = array('success' => false, 'message' => '');
    
    try {
        // Get NIC number (primary key)
        $nic_number = mysqli_real_escape_string($con, $_POST['nic_number']);
        if (empty($nic_number)) {
            throw new Exception("NIC Number is required");
        }

        // Function to handle single file upload to Google Drive
        function handleGoogleDriveUpload($fileInputName, $surname, $otherName, $filePrefix, $tourName) {
    global $drive;
    
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] == 0) {
        // Setup folder structure
        $folderStructure = $drive->setupFolderStructure($tourName, $surname, $otherName);
        if (!$folderStructure) {
            throw new Exception("Failed to setup Google Drive folders: " . $drive->getLastError());
        }
        
        $fileTmp = $_FILES[$fileInputName]['tmp_name'];
        $fileExtension = pathinfo($_FILES[$fileInputName]['name'], PATHINFO_EXTENSION);
        $newFileName = $filePrefix . '_' . time() . '.' . $fileExtension;
        
        // Upload to Google Drive
        $uploadResult = $drive->uploadFile($fileTmp, $newFileName, $folderStructure['user_folder_id']);
        
        if ($uploadResult) {
            return array(
                'file_id' => $uploadResult['file_id'],
                'download_link' => $uploadResult['download_link'],
                'view_link' => $uploadResult['view_link'],
                'filename' => $newFileName
            );
        } else {
            throw new Exception("Failed to upload " . $filePrefix . " to Google Drive: " . $drive->getLastError());
        }
    }
    return null;
}

function handleMultipleGoogleDriveUploads($fileInputName, $surname, $otherName, $filePrefix, $tourName) {
    global $drive;
    
    $uploadedFiles = array();
    
    if (isset($_FILES[$fileInputName]) && is_array($_FILES[$fileInputName]['name'])) {
        // Setup folder structure
        $folderStructure = $drive->setupFolderStructure($tourName, $surname, $otherName);
        if (!$folderStructure) {
            throw new Exception("Failed to setup Google Drive folders: " . $drive->getLastError());
        }
        
        for ($i = 0; $i < count($_FILES[$fileInputName]['name']); $i++) {
            if ($_FILES[$fileInputName]['error'][$i] == 0) {
                $fileTmp = $_FILES[$fileInputName]['tmp_name'][$i];
                $fileExtension = pathinfo($_FILES[$fileInputName]['name'][$i], PATHINFO_EXTENSION);
                $newFileName = $filePrefix . '_' . ($i + 1) . '_' . time() . '.' . $fileExtension;
                
                // Upload to Google Drive
                $uploadResult = $drive->uploadFile($fileTmp, $newFileName, $folderStructure['user_folder_id']);
                
                if ($uploadResult) {
                    $uploadedFiles[] = array(
                        'file_id' => $uploadResult['file_id'],
                        'download_link' => $uploadResult['download_link'],
                        'view_link' => $uploadResult['view_link'],
                        'filename' => $newFileName
                    );
                }
            }
        }
    }
    
    return !empty($uploadedFiles) ? json_encode($uploadedFiles) : null;
}

    // Handle form submissions - this is where Google Drive upload happens
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['search_nic'])) {
    $response = array('success' => false, 'message' => '');
    
    try {
        // Get NIC number (primary key)
        $nic_number = mysqli_real_escape_string($con, $_POST['nic_number']);
        if (empty($nic_number)) {
            throw new Exception("NIC Number is required");
        }
        
        // Get surname and other_name for folder creation
        $surname = mysqli_real_escape_string($con, $_POST['surname'] ?? '');
        $other_name = mysqli_real_escape_string($con, $_POST['othername'] ?? '');
        
        if (empty($surname) || empty($other_name)) {
            throw new Exception("Surname and Other Names are required for Google Drive upload");
        }
        
        // Check if record exists for this specific tour
        $check_query = "SELECT * FROM visa_applications WHERE nic_number = '$nic_number' AND tourname = '$tourname'";
        $check_result = mysqli_query($con, $check_query);
        $record_exists = mysqli_num_rows($check_result) > 0;
        
        // Handle file uploads to Google Drive ONLY when form is submitted
        $passport_copy = handleGoogleDriveUpload('passport_copy', $surname, $other_name, 'passport_copy', $tourname);
        $photo_id = handleGoogleDriveUpload('photo_id', $surname, $other_name, 'photo_id', $tourname);
        $visa_request_letter = handleMultipleGoogleDriveUploads('visa_request_letter', $surname, $other_name, 'visa_request_letter', $tourname);
        $bank_statements = handleMultipleGoogleDriveUploads('bank_statements', $surname, $other_name, 'bank_statement', $tourname);
        $employment_letter = handleMultipleGoogleDriveUploads('employment_letter', $surname, $other_name, 'employment_letter', $tourname);
        $epf_confirmation = handleMultipleGoogleDriveUploads('epf_confirmation', $surname, $other_name, 'epf_confirmation', $tourname);
        $pay_slips = handleMultipleGoogleDriveUploads('pay_slips', $surname, $other_name, 'pay_slip', $tourname);
        $business_registration = handleMultipleGoogleDriveUploads('business_registration', $surname, $other_name, 'business_registration', $tourname);
        $form_pvt_ltd = handleMultipleGoogleDriveUploads('form_pvt_ltd', $surname, $other_name, 'form_pvt_ltd', $tourname);
        $company_statements = handleMultipleGoogleDriveUploads('company_statements', $surname, $other_name, 'company_statement', $tourname);
        $service_letters = handleMultipleGoogleDriveUploads('service_letters', $surname, $other_name, 'service_letter', $tourname);
        $student_letter = handleMultipleGoogleDriveUploads('student_letter', $surname, $other_name, 'student_letter', $tourname);
        $dependent_confirmation = handleMultipleGoogleDriveUploads('dependent_confirmation', $surname, $other_name, 'dependent_confirmation', $tourname);
        $dependent_income = handleMultipleGoogleDriveUploads('dependent_income', $surname, $other_name, 'dependent_income', $tourname);
        $other_documents = handleMultipleGoogleDriveUploads('other_documents', $surname, $other_name, 'other_document', $tourname);
        
        // Rest of your existing database processing code remains the same...
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
        if (!empty($_POST['addressline1'])) $address_lines[] = $_POST['addressline1'];
        if (!empty($_POST['addressline2'])) $address_lines[] = $_POST['addressline2'];
        if (!empty($_POST['addressline3'])) $address_lines[] = $_POST['addressline3'];
        $permanent_address = implode(', ', $address_lines);
        
        // Your existing database update/insert logic here...
        // Just make sure to store the Google Drive file data as JSON
        
        if ($record_exists) {
            // Update existing record
            $update_parts = array();
            
            // Add all your existing field updates...
            // File updates - store Google Drive data as JSON
            if ($passport_copy) $update_parts[] = "passport_copy = '" . mysqli_real_escape_string($con, json_encode($passport_copy)) . "'";
            if ($photo_id) $update_parts[] = "photo_id = '" . mysqli_real_escape_string($con, json_encode($photo_id)) . "'";
            if ($visa_request_letter) $update_parts[] = "visa_request_letter = '" . mysqli_real_escape_string($con, $visa_request_letter) . "'";
            if ($bank_statements) $update_parts[] = "bank_statements = '" . mysqli_real_escape_string($con, $bank_statements) . "'";
            if ($employment_letter) $update_parts[] = "employment_letter = '" . mysqli_real_escape_string($con, $employment_letter) . "'";
            if ($epf_confirmation) $update_parts[] = "epf_confirmation = '" . mysqli_real_escape_string($con, $epf_confirmation) . "'";
            if ($pay_slips) $update_parts[] = "pay_slips = '" . mysqli_real_escape_string($con, $pay_slips) . "'";
            if ($business_registration) $update_parts[] = "business_registration = '" . mysqli_real_escape_string($con, $business_registration) . "'";
            if ($form_pvt_ltd) $update_parts[] = "form_pvt_ltd = '" . mysqli_real_escape_string($con, $form_pvt_ltd) . "'";
            if ($company_statements) $update_parts[] = "company_statements = '" . mysqli_real_escape_string($con, $company_statements) . "'";
            if ($service_letters) $update_parts[] = "service_letters = '" . mysqli_real_escape_string($con, $service_letters) . "'";
            if ($student_letter) $update_parts[] = "student_letter = '" . mysqli_real_escape_string($con, $student_letter) . "'";
            if ($dependent_confirmation) $update_parts[] = "dependent_confirmation = '" . mysqli_real_escape_string($con, $dependent_confirmation) . "'";
            if ($dependent_income) $update_parts[] = "dependent_income = '" . mysqli_real_escape_string($con, $dependent_income) . "'";
            if ($other_documents) $update_parts[] = "other_documents = '" . mysqli_real_escape_string($con, $other_documents) . "'";
            
            // Add your other field updates here...
            $update_parts[] = "updated_at = '" . date('Y-m-d H:i:s') . "'";
            
            if (!empty($update_parts)) {
                $update_query = "UPDATE visa_applications SET " . implode(', ', $update_parts) . " WHERE nic_number = '$nic_number' AND tourname = '$tourname'";
                
                if (mysqli_query($con, $update_query)) {
                    $response['success'] = true;
                    $response['message'] = "Application updated successfully with Google Drive uploads!";
                } else {
                    throw new Exception("Database update failed: " . mysqli_error($con));
                }
            } else {
                $response['success'] = true;
                $response['message'] = "No changes to update";
            }
        } else {
            // Insert new record with Google Drive data
            $fields = array(
                'nic_number' => $nic_number,
                'tourname' => $tourname,
                'year' => mysqli_real_escape_string($con, $tour_year),
                'name_for_certificates' => mysqli_real_escape_string($con, $_POST['name_for_certificates'] ?? ''),
                'name_for_tour_id' => mysqli_real_escape_string($con, $_POST['name_for_tour_id'] ?? ''),
                'permanent_address' => mysqli_real_escape_string($con, $permanent_address),
                'city' => mysqli_real_escape_string($con, $_POST['city'] ?? ''),
                'postal_code' => mysqli_real_escape_string($con, $_POST['postal_code'] ?? ''),
                'province' => mysqli_real_escape_string($con, $_POST['province'] ?? ''),
                'surname' => mysqli_real_escape_string($con, $_POST['surname'] ?? ''),
                'other_names' => mysqli_real_escape_string($con, $_POST['othername'] ?? ''),
                'date_of_birth' => mysqli_real_escape_string($con, $date_of_birth),
                'gender' => mysqli_real_escape_string($con, $_POST['gender'] ?? ''),
                'passport_number' => mysqli_real_escape_string($con, $_POST['passport_number'] ?? ''),
                'issue_date' => mysqli_real_escape_string($con, $issue_date),
                'expiry_date' => mysqli_real_escape_string($con, $expiry_date),
                'employment_status' => mysqli_real_escape_string($con, $_POST['employment_status'] ?? ''),
                'dependent_status' => mysqli_real_escape_string($con, $_POST['dependent_status'] ?? ''),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );
            
            // Add Google Drive file data as JSON
            if ($passport_copy) $fields['passport_copy'] = json_encode($passport_copy);
            if ($photo_id) $fields['photo_id'] = json_encode($photo_id);
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
                $response['message'] = "Application submitted successfully with Google Drive uploads!";
            } else {
                throw new Exception("Database insert failed: " . mysqli_error($con));
            }
        }
        
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }
    
    // Return JSON response for AJAX requests
    if (isset($_POST['ajax']) && $_POST['ajax'] == '1') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    // Redirect or show message for regular form submissions
    if ($response['success']) {
        $success_message = $response['message'];
        // Re-fetch updated data
        $query = "SELECT * FROM visa_applications WHERE nic_number = '$nic_number' AND tourname = '$tourname'";
        $result = mysqli_query($con, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $existing_data = mysqli_fetch_assoc($result);
            $editing = true;
        }
    } else {
        $error_message = $response['message'];
    }
}


       

        // Get surname and othername for folder creation
        $surname = mysqli_real_escape_string($con, $_POST['surname'] ?? '');
        $othername = mysqli_real_escape_string($con, $_POST['othername'] ?? '');

        // Check if record exists for this specific tour
        $checkquery = "SELECT * FROM visa_applications WHERE nic_number = '$nic_number' AND tourname = '$tourname'";
        $checkresult = mysqli_query($con, $checkquery);
        $recordexists = mysqli_num_rows($checkresult) > 0;

        // Get existing data if record exists
        $existingrecord = null;
        if ($recordexists) {
            $existingrecord = mysqli_fetch_assoc($checkresult);
        }

        // Handle file uploads to Google Drive
        $passport_copy = handleGoogleDriveUpload('passport_copy', $surname, $othername, 'passportcopy', $tourname);
        $photo_id = handleGoogleDriveUpload('photo_id', $surname, $othername, 'photoid', $tourname);
        $visa_request_letter = handleMultipleGoogleDriveUploads('visa_request_letter', $surname, $othername, 'visarequestletter', $tourname);
        $bank_statements = handleMultipleGoogleDriveUploads('bank_statements', $surname, $othername, 'bankstatement', $tourname);
        $employment_letter = handleMultipleGoogleDriveUploads('employment_letter', $surname, $othername, 'employmentletter', $tourname);
        $epf_confirmation = handleMultipleGoogleDriveUploads('epf_confirmation', $surname, $othername, 'epfconfirmation', $tourname);
        $pay_slips = handleMultipleGoogleDriveUploads('pay_slips', $surname, $othername, 'payslip', $tourname);
        $business_registration = handleMultipleGoogleDriveUploads('business_registration', $surname, $othername, 'businessregistration', $tourname);
        $form_pvt_ltd = handleMultipleGoogleDriveUploads('form_pvt_ltd', $surname, $othername, 'formpvtltd', $tourname);
        $company_statements = handleMultipleGoogleDriveUploads('company_statements', $surname, $othername, 'companystatement', $tourname);
        $service_letters = handleMultipleGoogleDriveUploads('service_letters', $surname, $othername, 'serviceletter', $tourname);
        $student_letter = handleMultipleGoogleDriveUploads('student_letter', $surname, $othername, 'studentletter', $tourname);
        $dependent_confirmation = handleMultipleGoogleDriveUploads('dependent_confirmation', $surname, $othername, 'dependentconfirmation', $tourname);
        $dependent_income = handleMultipleGoogleDriveUploads('dependent_income', $surname, $othername, 'dependentincome', $tourname);
        $other_documents = handleMultipleGoogleDriveUploads('other_documents', $surname, $othername, 'otherdocument', $tourname);

        // Combine date fields
        $date_of_birth = "";
        if (!empty($_POST['birthYear']) && !empty($_POST['birthMonth']) && !empty($_POST['birthDay'])) {
            $date_of_birth = $_POST['birthYear'] . "-" . str_pad($_POST['birthMonth'], 2, "0", STR_PAD_LEFT) . "-" . str_pad($_POST['birthDay'], 2, "0", STR_PAD_LEFT);
        }

        $issue_date = "";
        if (!empty($_POST['issueYear']) && !empty($_POST['issueMonth']) && !empty($_POST['issueDay'])) {
            $issue_date = $_POST['issueYear'] . "-" . str_pad($_POST['issueMonth'], 2, "0", STR_PAD_LEFT) . "-" . str_pad($_POST['issueDay'], 2, "0", STR_PAD_LEFT);
        }

        $expiry_date = "";
        if (!empty($_POST['expiryYear']) && !empty($_POST['expiryMonth']) && !empty($_POST['expiryDay'])) {
            $expiry_date = $_POST['expiryYear'] . "-" . str_pad($_POST['expiryMonth'], 2, "0", STR_PAD_LEFT) . "-" . str_pad($_POST['expiryDay'], 2, "0", STR_PAD_LEFT);
        }

        // Combine address lines
        $permanent_address = "";
        $addresslines = array();
        if (!empty($_POST['addressline1'])) $addresslines[] = $_POST['addressline1'];
        if (!empty($_POST['addressline2'])) $addresslines[] = $_POST['addressline2'];
        if (!empty($_POST['addressline3'])) $addresslines[] = $_POST['addressline3'];
        $permanent_address = implode(", ", $addresslines);

        if ($recordexists) {
            // Update existing record
            $updateparts = array();
            
            // Personal details - only update if new data provided
            if (isset($_POST['name_for_certificates']) && !empty($_POST['name_for_certificates'])) {
                $updateparts[] = "name_for_certificates = '" . mysqli_real_escape_string($con, $_POST['name_for_certificates']) . "'";
            }
            if (isset($_POST['name_for_tour_id']) && !empty($_POST['name_for_tour_id'])) {
                $updateparts[] = "name_for_tour_id = '" . mysqli_real_escape_string($con, $_POST['name_for_tour_id']) . "'";
            }
            if (!empty($permanent_address)) {
                $updateparts[] = "permanent_address = '" . mysqli_real_escape_string($con, $permanent_address) . "'";
            }
            if (isset($_POST['city']) && !empty($_POST['city'])) {
                $updateparts[] = "city = '" . mysqli_real_escape_string($con, $_POST['city']) . "'";
            }
            if (isset($_POST['postal_code']) && !empty($_POST['postal_code'])) {
                $updateparts[] = "postal_code = '" . mysqli_real_escape_string($con, $_POST['postal_code']) . "'";
            }
            if (isset($_POST['province']) && !empty($_POST['province'])) {
                $updateparts[] = "province = '" . mysqli_real_escape_string($con, $_POST['province']) . "'";
            }

            // Passport details
            if (isset($_POST['surname']) && !empty($_POST['surname'])) {
                $updateparts[] = "surname = '" . mysqli_real_escape_string($con, $_POST['surname']) . "'";
            }
            if (isset($_POST['othername']) && !empty($_POST['othername'])) {
                $updateparts[] = "other_names = '" . mysqli_real_escape_string($con, $_POST['othername']) . "'";
            }
            if (!empty($date_of_birth)) {
                $updateparts[] = "date_of_birth = '" . mysqli_real_escape_string($con, $date_of_birth) . "'";
            }
            if (isset($_POST['gender']) && !empty($_POST['gender'])) {
                $updateparts[] = "gender = '" . mysqli_real_escape_string($con, $_POST['gender']) . "'";
            }
            if (isset($_POST['passport_number']) && !empty($_POST['passport_number'])) {
                $updateparts[] = "passport_number = '" . mysqli_real_escape_string($con, $_POST['passport_number']) . "'";
            }
            if (!empty($issue_date)) {
                $updateparts[] = "issue_date = '" . mysqli_real_escape_string($con, $issue_date) . "'";
            }
            if (!empty($expiry_date)) {
                $updateparts[] = "expiry_date = '" . mysqli_real_escape_string($con, $expiry_date) . "'";
            }

            // Employment status
            if (isset($_POST['employment_status']) && !empty($_POST['employment_status'])) {
                $updateparts[] = "employment_status = '" . mysqli_real_escape_string($con, $_POST['employment_status']) . "'";
            }
            if (isset($_POST['dependent_status']) && !empty($_POST['dependent_status'])) {
                $updateparts[] = "dependent_status = '" . mysqli_real_escape_string($con, $_POST['dependent_status']) . "'";
            }

            // File updates - store Google Drive data as JSON
            if ($passport_copy) {
                $updateparts[] = "passport_copy = '" . mysqli_real_escape_string($con, json_encode($passport_copy)) . "'";
            }
            if ($photo_id) {
                $updateparts[] = "photo_id = '" . mysqli_real_escape_string($con, json_encode($photo_id)) . "'";
            }
            if ($visa_request_letter) {
                $updateparts[] = "visa_request_letter = '" . mysqli_real_escape_string($con, $visa_request_letter) . "'";
            }
            if ($bank_statements) {
                $updateparts[] = "bank_statements = '" . mysqli_real_escape_string($con, $bank_statements) . "'";
            }
            if ($employment_letter) {
                $updateparts[] = "employment_letter = '" . mysqli_real_escape_string($con, $employment_letter) . "'";
            }
            if ($epf_confirmation) {
                $updateparts[] = "epf_confirmation = '" . mysqli_real_escape_string($con, $epf_confirmation) . "'";
            }
            if ($pay_slips) {
                $updateparts[] = "pay_slips = '" . mysqli_real_escape_string($con, $pay_slips) . "'";
            }
            if ($business_registration) {
                $updateparts[] = "business_registration = '" . mysqli_real_escape_string($con, $business_registration) . "'";
            }
            if ($form_pvt_ltd) {
                $updateparts[] = "form_pvt_ltd = '" . mysqli_real_escape_string($con, $form_pvt_ltd) . "'";
            }
            if ($company_statements) {
                $updateparts[] = "company_statements = '" . mysqli_real_escape_string($con, $company_statements) . "'";
            }
            if ($service_letters) {
                $updateparts[] = "service_letters = '" . mysqli_real_escape_string($con, $service_letters) . "'";
            }
            if ($student_letter) {
                $updateparts[] = "student_letter = '" . mysqli_real_escape_string($con, $student_letter) . "'";
            }
            if ($dependent_confirmation) {
                $updateparts[] = "dependent_confirmation = '" . mysqli_real_escape_string($con, $dependent_confirmation) . "'";
            }
            if ($dependent_income) {
                $updateparts[] = "dependent_income = '" . mysqli_real_escape_string($con, $dependent_income) . "'";
            }
            if ($other_documents) {
                $updateparts[] = "other_documents = '" . mysqli_real_escape_string($con, $other_documents) . "'";
            }

            // Always set year from tour data
            $updateparts[] = "year = '" . mysqli_real_escape_string($con, $touryear) . "'";
            $updateparts[] = "updated_at = '" . date('Y-m-d H:i:s') . "'";

            if (!empty($updateparts)) {
                $updatequery = "UPDATE visa_applications SET " . implode(", ", $updateparts) . " WHERE nic_number = '$nic_number' AND tourname = '$tourname'";
                if (mysqli_query($con, $updatequery)) {
                    $response['success'] = true;
                    $response['message'] = "Data updated successfully!";
                } else {
                    throw new Exception("Database update failed: " . mysqli_error($con));
                }
            } else {
                $response['success'] = true;
                $response['message'] = "No changes to update";
            }
        } else {
            // Insert new record
            $fields = array(
                'nic_number' => $nic_number,
                'tourname' => $tourname,
                'year' => mysqli_real_escape_string($con, $touryear),
                'name_for_certificates' => mysqli_real_escape_string($con, $_POST['name_for_certificates'] ?? ''),
                'name_for_tour_id' => mysqli_real_escape_string($con, $_POST['name_for_tour_id'] ?? ''),
                'permanent_address' => mysqli_real_escape_string($con, $permanent_address),
                'city' => mysqli_real_escape_string($con, $_POST['city'] ?? ''),
                'postal_code' => mysqli_real_escape_string($con, $_POST['postal_code'] ?? ''),
                'province' => mysqli_real_escape_string($con, $_POST['province'] ?? ''),
                'surname' => mysqli_real_escape_string($con, $_POST['surname'] ?? ''),
                'other_names' => mysqli_real_escape_string($con, $_POST['othername'] ?? ''),
                'date_of_birth' => mysqli_real_escape_string($con, $date_of_birth),
                'gender' => mysqli_real_escape_string($con, $_POST['gender'] ?? ''),
                'passport_number' => mysqli_real_escape_string($con, $_POST['passport_number'] ?? ''),
                'issue_date' => mysqli_real_escape_string($con, $issue_date),
                'expiry_date' => mysqli_real_escape_string($con, $expiry_date),
                'employment_status' => mysqli_real_escape_string($con, $_POST['employment_status'] ?? ''),
                'dependent_status' => mysqli_real_escape_string($con, $_POST['dependent_status'] ?? ''),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            // Add Google Drive file data as JSON
            if ($passport_copy) $fields['passport_copy'] = json_encode($passport_copy);
            if ($photo_id) $fields['photo_id'] = json_encode($photo_id);
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

            $columns = implode(", ", array_keys($fields));
            $values = "'" . implode("', '", array_values($fields)) . "'";

            $insertquery = "INSERT INTO visa_applications ($columns) VALUES ($values)";
            if (mysqli_query($con, $insertquery)) {
                $response['success'] = true;
                $response['message'] = "Data saved successfully!";
            } else {
                throw new Exception("Database insert failed: " . mysqli_error($con));
            }
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    }

    // Return JSON response for AJAX requests
    if (isset($_POST['ajax']) && $_POST['ajax'] == 1) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
    }

    // Redirect or show message for regular form submissions
    if ($response['success']) {
        $successmessage = $response['message'];
        // Re-fetch updated data
        $query = "SELECT * FROM visa_applications WHERE nic_number = '$nic_number' AND tourname = '$tourname'";
        $result = mysqli_query($con, $query);
        if ($result && mysqli_num_rows($result) > 0) {
            $existingdata = mysqli_fetch_assoc($result);
            $editing = true;
        }
    } else {
        $errormessage = $response['message'];
    }
}

// Function to check if field should be locked (has existing data)
function shouldLockField($existingdata, $fieldname) {
    // Don't lock file upload fields and employment selections
    $unlockedfields = ['passport_copy', 'photo_id', 'visa_request_letter', 'bank_statements', 
                       'employment_letter', 'epf_confirmation', 'pay_slips', 'business_registration', 
                       'form_pvt_ltd', 'company_statements', 'service_letters', 'student_letter', 
                       'dependent_confirmation', 'dependent_income', 'other_documents', 
                       'employment_status', 'dependent_status'];
    
    if (in_array($fieldname, $unlockedfields)) {
        return false;
    }
    
    return $existingdata && isset($existingdata[$fieldname]) && !empty($existingdata[$fieldname]) && $existingdata[$fieldname] != 'null';
}

// Parse existing dates for display
$birthyear = $birthmonth = $birthday = $issueyear = $issuemonth = $issueday = $expiryyear = $expirymonth = $expiryday = "";
$addressline1 = $addressline2 = $addressline3 = "";

if ($existingdata) {
    if (!empty($existingdata['date_of_birth'])) {
        $birthparts = explode("-", $existingdata['date_of_birth']);
        if (count($birthparts) == 3) {
            $birthyear = $birthparts[0];
            $birthmonth = intval($birthparts[1]);
            $birthday = intval($birthparts[2]);
        }
    }
    
    if (!empty($existingdata['issue_date'])) {
        $issueparts = explode("-", $existingdata['issue_date']);
        if (count($issueparts) == 3) {
            $issueyear = $issueparts[0];
            $issuemonth = intval($issueparts[1]);
            $issueday = intval($issueparts[2]);
        }
    }
    
    if (!empty($existingdata['expiry_date'])) {
        $expiryparts = explode("-", $existingdata['expiry_date']);
        if (count($expiryparts) == 3) {
            $expiryyear = $expiryparts[0];
            $expirymonth = intval($expiryparts[1]);
            $expiryday = intval($expiryparts[2]);
        }
    }
    
    if (!empty($existingdata['permanent_address'])) {
        $addressparts = explode(", ", $existingdata['permanent_address']);
        $addressline1 = $addressparts[0] ?? '';
        $addressline2 = $addressparts[1] ?? '';
        $addressline3 = $addressparts[2] ?? '';
    }
}

// Function to display Google Drive file information
function displayGoogleDriveFiles($data) {
    if (empty($data)) return '';
    
    $files = json_decode($data, true);
    if (!$files) return $data; // Fallback to original data if not JSON
    
    if (isset($files['filename'])) {
        // Single file
        return 'File: ' . $files['filename'] . ' (Google Drive)';
    } else if (is_array($files)) {
        // Multiple files
        $filenames = array();
        foreach ($files as $file) {
            if (isset($file['filename'])) {
                $filenames[] = $file['filename'];
            }
        }
        return 'Files: ' . implode(', ', $filenames) . ' (Google Drive)';
    }
    
    return $data;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tourtitle); ?> - Application Form</title>
    <link rel="stylesheet" href="../css/header.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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

        /* ADDED: Required field indicator */
        .form-group label[required]::after {
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

        /* Loading Popup Overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 9999;
    backdrop-filter: blur(5px);
}

.loading-popup {
    background-color: var(--white);
    border-radius: 15px;
    padding: 40px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    max-width: 400px;
    width: 90%;
}

/* Advanced Loading Animation */
.loading-spinner {
    width: 60px;
    height: 60px;
    margin: 0 auto 20px;
    position: relative;
}

.spinner-ring {
    box-sizing: border-box;
    display: block;
    position: absolute;
    width: 60px;
    height: 60px;
    margin: 0;
    border: 6px solid transparent;
    border-top-color: var(--secondary-color);
    border-radius: 50%;
    animation: spin-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
}

.spinner-ring:nth-child(1) { animation-delay: -0.45s; }
.spinner-ring:nth-child(2) { animation-delay: -0.3s; border-top-color: var(--primary-color); }
.spinner-ring:nth-child(3) { animation-delay: -0.15s; border-top-color: #27ae60; }

@keyframes spin-ring {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Progress Bar */
.progress-container {
    width: 100%;
    height: 8px;
    background-color: var(--light-gray);
    border-radius: 4px;
    margin: 20px 0;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--secondary-color), var(--primary-color));
    border-radius: 4px;
    width: 0%;
    transition: width 0.3s ease;
    animation: progress-pulse 2s ease-in-out infinite;
}

@keyframes progress-pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}

/* Loading Text Animation */
.loading-text {
    color: var(--primary-color);
    font-size: 18px;
    font-weight: 600;
    margin: 15px 0;
}

.loading-subtext {
    color: var(--dark-gray);
    font-size: 14px;
    margin: 10px 0;
}

/* Dots Animation */
.loading-dots::after {
    content: '';
    animation: dots 1.5s steps(4, end) infinite;
}

@keyframes dots {
    0%, 20% { content: '.'; }
    40% { content: '..'; }
    60% { content: '...'; }
    80%, 100% { content: ''; }
}

/* Success Animation */
.success-checkmark {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: block;
    stroke-width: 3;
    stroke: var(--success-color);
    stroke-miterlimit: 10;
    margin: 0 auto 20px;
    box-shadow: inset 0px 0px 0px var(--success-color);
    animation: fill 0.4s ease-in-out 0.4s forwards, scale 0.3s ease-in-out 0.9s both;
}

.success-checkmark-circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    stroke-width: 3;
    stroke-miterlimit: 10;
    stroke: var(--success-color);
    fill: none;
    animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.success-checkmark-check {
    transform-origin: 50% 50%;
    stroke-dasharray: 48;
    stroke-dashoffset: 48;
    animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
}

@keyframes stroke {
    100% { stroke-dashoffset: 0; }
}

@keyframes scale {
    0%, 100% { transform: none; }
    50% { transform: scale3d(1.1, 1.1, 1); }
}

@keyframes fill {
    100% { box-shadow: inset 0px 0px 0px 30px var(--success-color); }
}

    </style>
</head>
<body>
    <!-- Loading Popup Overlay -->
<div id="loadingOverlay" class="loading-overlay">
    <div class="loading-popup">
        <div id="loadingContent">
            <div class="loading-spinner">
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
                <div class="spinner-ring"></div>
            </div>
            <div class="loading-text">Processing Your Application<span class="loading-dots"></span></div>
            <div class="loading-subtext">Please wait while we upload your documents and save your information</div>
            <div class="progress-container">
                <div id="progressBar" class="progress-bar"></div>
            </div>
        </div>
        
        <div id="successContent" style="display: none;">
            <svg class="success-checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                <circle class="success-checkmark-circle" cx="26" cy="26" r="25" fill="none"/>
                <path class="success-checkmark-check" fill="none" d="m14.1 27.2l7.1 7.2 16.7-16.8"/>
            </svg>
            <div class="loading-text" style="color: var(--success-color);">Application Submitted Successfully!</div>
            <div class="loading-subtext">Redirecting...</div>
        </div>
    </div>
</div>


    <div class="container">
        <h1 class="page-title">
            Visa Document Submission
            <small>Tour: <?php echo htmlspecialchars($tourtitle); ?></small>
            <?php if ($editing): ?>
                <small>Editing Application for NIC: <?php echo htmlspecialchars($existingdata['nic_number']); ?></small>
            <?php endif; ?>
        </h1>

        <?php if (isset($successmessage)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($successmessage); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($errormessage)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($errormessage); ?>
            </div>
        <?php endif; ?>

        <!-- NIC Search Section inside Personal Details -->
        <div class="nic-search-section">
            <h4 style="margin-top: 0; color: var(--primary-color);">Search Existing Application</h4>
            <form method="post" class="nic-search-form">
                <div class="form-group">
                    <label for="searchnic">Enter NIC Number to Search:</label>
                    <input type="text" id="searchnic" name="searchnic" value="<?php echo htmlspecialchars($nicsearch); ?>" placeholder="Enter NIC number to search existing data">
                </div>
                <button type="submit" class="btn-search">Search</button>
            </form>
            
            <?php if (isset($_POST['searchnic']) && !empty($_POST['searchnic']) && !$existingdata): ?>
                <div class="alert alert-info" style="margin-top: 15px;">
                    No existing record found for NIC "<?php echo htmlspecialchars($_POST['searchnic']); ?>" in <?php echo htmlspecialchars($tourname); ?>. You can create a new application.
                </div>
            <?php endif; ?>
        </div>

        <!-- Single Page Form -->
        <form id="visaApplicationForm" method="post" enctype="multipart/form-data">
            <input type="hidden" name="ajax" value="1">

            <!-- Personal Details Section with NIC Search -->
            <div class="form-section">
                <div class="section-title">
                    <h3>Personal Details</h3>
                </div>

                <!-- NIC Number Field -->
                <div class="form-group">
                    <label for="nic_number" required>NIC Number:</label>
                    <input type="text" id="nic_number" name="nic_number" required 
                           value="<?php echo $existingdata ? htmlspecialchars($existingdata['nic_number']) : htmlspecialchars($nicsearch); ?>" 
                           <?php echo shouldLockField($existingdata, 'nic_number') ? 'readonly class="locked valid"' : ''; ?>
                           placeholder="Enter your NIC number">
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="name_for_certificates" required>Name for the Certificates:</label>
                            <input type="text" id="name_for_certificates" name="name_for_certificates" required
                                   value="<?php echo $existingdata ? htmlspecialchars($existingdata['name_for_certificates']) : ''; ?>" 
                                   <?php echo shouldLockField($existingdata, 'name_for_certificates') ? 'readonly class="locked valid"' : ''; ?>
                                   placeholder="Name for the certificates">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="name_for_tour_id" required>Name for the Tour ID<br><small>One Short Name, eg: Nuwan</small>:</label>
                            <input type="text" id="name_for_tour_id" name="name_for_tour_id" required
                                   value="<?php echo $existingdata ? htmlspecialchars($existingdata['name_for_tour_id']) : ''; ?>" 
                                   <?php echo shouldLockField($existingdata, 'name_for_tour_id') ? 'readonly class="locked valid"' : ''; ?>
                                   placeholder="Short name for tour ID">
                        </div>
                    </div>
                </div>

                <!-- Multi-line Address -->
                <div class="form-group">
                    <label required>Permanent Address:</label>
                    <div class="address-lines">
                        <input type="text" id="addressline1" name="addressline1" required
                               value="<?php echo htmlspecialchars($addressline1); ?>" 
                               <?php echo shouldLockField($existingdata, 'permanent_address') ? 'readonly class="locked valid"' : ''; ?>
                               placeholder="Address Line 1">
                        <input type="text" id="addressline2" name="addressline2" 
                               value="<?php echo htmlspecialchars($addressline2); ?>" 
                               <?php echo shouldLockField($existingdata, 'permanent_address') ? 'readonly class="locked valid"' : ''; ?>
                               placeholder="Address Line 2 (Optional)">
                        <input type="text" id="addressline3" name="addressline3" 
                               value="<?php echo htmlspecialchars($addressline3); ?>" 
                               <?php echo shouldLockField($existingdata, 'permanent_address') ? 'readonly class="locked valid"' : ''; ?>
                               placeholder="Address Line 3 (Optional)">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="city" required>City:</label>
                            <input type="text" id="city" name="city" required
                                   value="<?php echo $existingdata ? htmlspecialchars($existingdata['city']) : ''; ?>" 
                                   <?php echo shouldLockField($existingdata, 'city') ? 'readonly class="locked valid"' : ''; ?>
                                   placeholder="Enter city">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="postal_code" required>Postal Code:</label>
                            <input type="text" id="postal_code" name="postal_code" required
                                   value="<?php echo $existingdata ? htmlspecialchars($existingdata['postal_code']) : ''; ?>" 
                                   <?php echo shouldLockField($existingdata, 'postal_code') ? 'readonly class="locked valid"' : ''; ?>
                                   placeholder="Enter postal code">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="province" required>Province:</label>
                    <input type="text" id="province" name="province" required
                           value="<?php echo $existingdata ? htmlspecialchars($existingdata['province']) : ''; ?>" 
                           <?php echo shouldLockField($existingdata, 'province') ? 'readonly class="locked valid"' : ''; ?>
                           placeholder="Enter province">
                </div>
            </div>

            <!-- Passport Details Section -->
            <div class="form-section">
                <div class="section-title">
                    <h3>Passport Details</h3>
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label for="surname" required>Surname (As given in the Passport):</label>
                            <input type="text" id="surname" name="surname" required
                                   value="<?php echo $existingdata ? htmlspecialchars($existingdata['surname']) : ''; ?>" 
                                   <?php echo shouldLockField($existingdata, 'surname') ? 'readonly class="locked valid"' : ''; ?>
                                   placeholder="Surname from passport">
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label for="othername" required>Other Names (As given in the Passport):</label>
                            <input type="text" id="othername" name="othername" required
                                   value="<?php echo $existingdata ? htmlspecialchars($existingdata['other_names']) : ''; ?>" 
                                   <?php echo shouldLockField($existingdata, 'other_names') ? 'readonly class="locked valid"' : ''; ?>
                                   placeholder="Other names from passport">
                        </div>
                    </div>
                </div>

                <!-- Separated Date of Birth -->
                <div class="form-group">
                    <label required>Date of Birth (As given in the Passport):</label>
                    <div class="date-container">
                        <select id="birthYear" name="birthYear" required <?php echo shouldLockField($existingdata, 'date_of_birth') ? 'disabled class="locked valid"' : ''; ?>>
                            <option value="">Year</option>
                            <?php for($year = 1950; $year <= 2010; $year++): ?>
                                <option value="<?php echo $year; ?>" <?php echo ($birthyear == $year) ? 'selected' : ''; ?>><?php echo $year; ?></option>
                            <?php endfor; ?>
                        </select>
                        <select id="birthMonth" name="birthMonth" required <?php echo shouldLockField($existingdata, 'date_of_birth') ? 'disabled class="locked valid"' : ''; ?>>
                            <option value="">Month</option>
                            <?php 
                            $months = [1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 
                                       7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'];
                            foreach($months as $num => $name): 
                            ?>
                                <option value="<?php echo $num; ?>" <?php echo ($birthmonth == $num) ? 'selected' : ''; ?>><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="birthDay" name="birthDay" required <?php echo shouldLockField($existingdata, 'date_of_birth') ? 'disabled class="locked valid"' : ''; ?>>
                            <option value="">Day</option>
                            <?php for($day = 1; $day <= 31; $day++): ?>
                                <option value="<?php echo $day; ?>" <?php echo ($birthday == $day) ? 'selected' : ''; ?>><?php echo $day; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>

                <!-- Gender without default selection -->
                <div class="form-group">
                    <label for="gender" required>Gender (As given in the Passport):</label>
                    <select id="gender" name="gender" required <?php echo shouldLockField($existingdata, 'gender') ? 'disabled class="locked valid"' : ''; ?>>
                        <option value="">Select Gender</option>
                        <option value="male" <?php echo ($existingdata && $existingdata['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo ($existingdata && $existingdata['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="passport_number" required>Passport Number (As given in the Passport):</label>
                    <input type="text" id="passport_number" name="passport_number" required
                           value="<?php echo $existingdata ? htmlspecialchars($existingdata['passport_number']) : ''; ?>" 
                           <?php echo shouldLockField($existingdata, 'passport_number') ? 'readonly class="locked valid"' : ''; ?>
                           placeholder="Passport number">
                </div>

                <div class="form-row">
                    <div class="form-col">
                        <div class="form-group">
                            <label required>Issue Date:</label>
                            <div class="date-container">
                                <select id="issueYear" name="issueYear" required <?php echo shouldLockField($existingdata, 'issue_date') ? 'disabled class="locked valid"' : ''; ?>>
                                    <option value="">Year</option>
                                    <?php for($year = 2015; $year <= 2025; $year++): ?>
                                        <option value="<?php echo $year; ?>" <?php echo ($issueyear == $year) ? 'selected' : ''; ?>><?php echo $year; ?></option>
                                    <?php endfor; ?>
                                </select>
                                <select id="issueMonth" name="issueMonth" required <?php echo shouldLockField($existingdata, 'issue_date') ? 'disabled class="locked valid"' : ''; ?>>
                                    <option value="">Month</option>
                                    <?php foreach($months as $num => $name): ?>
                                        <option value="<?php echo $num; ?>" <?php echo ($issuemonth == $num) ? 'selected' : ''; ?>><?php echo $name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select id="issueDay" name="issueDay" required <?php echo shouldLockField($existingdata, 'issue_date') ? 'disabled class="locked valid"' : ''; ?>>
                                    <option value="">Day</option>
                                    <?php for($day = 1; $day <= 31; $day++): ?>
                                        <option value="<?php echo $day; ?>" <?php echo ($issueday == $day) ? 'selected' : ''; ?>><?php echo $day; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-col">
                        <div class="form-group">
                            <label required>Expiry Date:</label>
                            <div class="date-container">
                                <select id="expiryYear" name="expiryYear" required <?php echo shouldLockField($existingdata, 'expiry_date') ? 'disabled class="locked valid"' : ''; ?>>
                                    <option value="">Year</option>
                                    <?php for($year = 2026; $year <= 2035; $year++): ?>
                                        <option value="<?php echo $year; ?>" <?php echo ($expiryyear == $year) ? 'selected' : ''; ?>><?php echo $year; ?></option>
                                    <?php endfor; ?>
                                </select>
                                <select id="expiryMonth" name="expiryMonth" required <?php echo shouldLockField($existingdata, 'expiry_date') ? 'disabled class="locked valid"' : ''; ?>>
                                    <option value="">Month</option>
                                    <?php foreach($months as $num => $name): ?>
                                        <option value="<?php echo $num; ?>" <?php echo ($expirymonth == $num) ? 'selected' : ''; ?>><?php echo $name; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <select id="expiryDay" name="expiryDay" required <?php echo shouldLockField($existingdata, 'expiry_date') ? 'disabled class="locked valid"' : ''; ?>>
                                    <option value="">Day</option>
                                    <?php for($day = 1; $day <= 31; $day++): ?>
                                        <option value="<?php echo $day; ?>" <?php echo ($expiryday == $day) ? 'selected' : ''; ?>><?php echo $day; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visa Documents Section -->
            <div class="form-section">
                <div class="section-title">
                    <h3>Visa Documents</h3>
                </div>

                <!-- Passport Copy (SINGLE FILE) -->
                <div class="form-group">
                    <label for="passport_copy" required>Passport Copy (Both Sides):</label>
                    <div class="file-upload <?php echo ($existingdata && $existingdata['passport_copy']) ? 'uploaded' : ''; ?>">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">📄</span>
                        <h4>Upload Passport Copy</h4>
                        <p>Drag and drop your file here or click to browse</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: PDF, JPG, PNG | Max size: 5MB</p>
                        <input type="file" id="passport_copy" name="passport_copy" accept=".pdf,.jpg,.jpeg,.png" required>
                        <div class="file-name-display" id="passportFileDisplay">
                            <?php if ($existingdata && $existingdata['passport_copy']): ?>
                                Current file: <?php echo displayGoogleDriveFiles($existingdata['passport_copy']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Passport Size Photo (SINGLE FILE) -->
                <div class="form-group">
                    <label for="photo_id" required>Passport Size Photo:</label>
                    <div class="file-upload <?php echo ($existingdata && $existingdata['photo_id']) ? 'uploaded' : ''; ?>">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">📸</span>
                        <h4>Upload Passport Size Photo</h4>
                        <p>Drag and drop your file here or click to browse</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PNG | Max size: 2MB</p>
                        <input type="file" id="photo_id" name="photo_id" accept=".jpg,.jpeg,.png" required>
                        <div class="file-name-display" id="photoFileDisplay">
                            <?php if ($existingdata && $existingdata['photo_id']): ?>
                                Current file: <?php echo displayGoogleDriveFiles($existingdata['photo_id']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Visa Request Letter (MULTIPLE FILES) -->
                <div class="form-group">
                    <label for="visa_request_letter" required>Visa Request Letter (Signed):</label>
                    <div class="file-upload <?php echo ($existingdata && $existingdata['visa_request_letter']) ? 'uploaded' : ''; ?>">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">📋</span>
                        <h4>Upload Visa Request Letter</h4>
                        <p>Drag and drop your files here or click to browse</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF | Max size: 2MB each</p>
                        <input type="file" id="visa_request_letter" name="visa_request_letter[]" multiple accept=".jpg,.jpeg,.pdf" required>
                        <div class="file-name-display" id="visaRequestLetterDisplay">
                            <?php if ($existingdata && $existingdata['visa_request_letter']): ?>
                                Current files: <?php echo displayGoogleDriveFiles($existingdata['visa_request_letter']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Bank Statements (MULTIPLE FILES) -->
                <div class="form-group">
                    <label for="bank_statements" required>Bank Statements:</label>
                    <div class="file-upload <?php echo ($existingdata && $existingdata['bank_statements']) ? 'uploaded' : ''; ?>">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">🏦</span>
                        <h4>Upload Bank Statements</h4>
                        <p>Drag and drop your files here or click to browse</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PNG, PDF | Max size: 2MB each</p>
                        <input type="file" id="bank_statements" name="bank_statements[]" multiple accept=".jpg,.jpeg,.pdf,.png" required>
                        <div class="file-name-display" id="bankStatementsDisplay">
                            <?php if ($existingdata && $existingdata['bank_statements']): ?>
                                Current files: <?php echo displayGoogleDriveFiles($existingdata['bank_statements']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Employment Status Selection -->
                <div class="form-group">
                    <label style="border-bottom: 2px solid var(--medium-gray); padding-bottom: 10px; margin-bottom: 20px;" required>If you are:</label>
                    <div class="status-selector">
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="employment_status" value="employee" onclick="showEmploymentSection('employee')" required 
                                       <?php echo ($existingdata && $existingdata['employment_status'] == 'employee') ? 'checked' : ''; ?>>
                                <span>Employee</span>
                            </label>
                            <label>
                                <input type="radio" name="employment_status" value="business" onclick="showEmploymentSection('business')" required 
                                       <?php echo ($existingdata && $existingdata['employment_status'] == 'business') ? 'checked' : ''; ?>>
                                <span>Business Owner</span>
                            </label>
                            <label>
                                <input type="radio" name="employment_status" value="freelancer" onclick="showEmploymentSection('freelancer')" required 
                                       <?php echo ($existingdata && $existingdata['employment_status'] == 'freelancer') ? 'checked' : ''; ?>>
                                <span>Freelancer</span>
                            </label>
                            <label>
                                <input type="radio" name="employment_status" value="student" onclick="showEmploymentSection('studentdependent')" required 
                                       <?php echo ($existingdata && $existingdata['employment_status'] == 'student') ? 'checked' : ''; ?>>
                                <span>Student/Dependent</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Employee Section -->
                <div id="employeeSection" class="employment-section" style="display: <?php echo ($existingdata && $existingdata['employment_status'] == 'employee') ? 'block' : 'none'; ?>;">
                    <div class="file-upload" style="margin-bottom: 20px;">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">📋</span>
                        <h4>Upload Employment Confirmation Letter with Leave Confirmation <span style="color: var(--error-color);">*</span></h4>
                        <p>Drag and drop your files here or click to browse</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF | Max size: 2MB each</p>
                        <input type="file" id="employment_letter" name="employment_letter[]" multiple accept=".jpg,.jpeg,.pdf">
                        <div class="file-name-display" id="employmentLetterDisplay">
                            <?php if ($existingdata && $existingdata['employment_letter']): ?>
                                Current files: <?php echo displayGoogleDriveFiles($existingdata['employment_letter']); ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="file-upload" style="margin-bottom: 20px;">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">📋</span>
                        <h4>Upload EPF Confirmation Letter</h4>
                        <p>Drag and drop your files here or click to browse</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF | Max size: 2MB each</p>
                        <input type="file" id="epf_confirmation" name="epf_confirmation[]" multiple accept=".jpg,.jpeg,.pdf">
                        <div class="file-name-display" id="epfConfirmationDisplay">
                            <?php if ($existingdata && $existingdata['epf_confirmation']): ?>
                                Current files: <?php echo displayGoogleDriveFiles($existingdata['epf_confirmation']); ?>
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
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF | Max size: 2MB each</p>
                        <input type="file" id="pay_slips" name="pay_slips[]" multiple accept=".jpg,.jpeg,.pdf">
                        <div class="file-name-display" id="paySlipsDisplay">
                            <?php if ($existingdata && $existingdata['pay_slips']): ?>
                                Current files: <?php echo displayGoogleDriveFiles($existingdata['pay_slips']); ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="file-upload">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">📄</span>
                        <h4>Upload Other Documents</h4>
                        <p>Birth or Marriage Certificate if Applicable, Any other letters</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF | Max size: 2MB each</p>
                        <input type="file" id="otherDocumentsEmployee" name="other_documents[]" multiple accept=".jpg,.jpeg,.pdf">
                        <div class="file-name-display" id="otherDocumentsEmployeeDisplay">
                            <?php if ($existingdata && $existingdata['other_documents']): ?>
                                Current files: <?php echo displayGoogleDriveFiles($existingdata['other_documents']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Business Owner Section -->
                <div id="businessSection" class="employment-section" style="display: <?php echo ($existingdata && $existingdata['employment_status'] == 'business') ? 'block' : 'none'; ?>;">
                    <div class="file-upload" style="margin-bottom: 20px;">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">📋</span>
                        <h4>Upload Original Business Registration and English Translation <span style="color: var(--error-color);">*</span></h4>
                        <p>Drag and drop your files here or click to browse</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF | Max size: 2MB each</p>
                        <input type="file" id="business_registration" name="business_registration[]" multiple accept=".jpg,.jpeg,.pdf">
                        <div class="file-name-display" id="businessRegistrationDisplay">
                            <?php if ($existingdata && $existingdata['business_registration']): ?>
                                Current files: <?php echo displayGoogleDriveFiles($existingdata['business_registration']); ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="file-upload" style="margin-bottom: 20px;">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">📋</span>
                        <h4>Upload Form 1 for PVT LTD</h4>
                        <p>Drag and drop your files here or click to browse</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF | Max size: 2MB each</p>
                        <input type="file" id="form_pvt_ltd" name="form_pvt_ltd[]" multiple accept=".jpg,.jpeg,.pdf">
                        <div class="file-name-display" id="formPvtLtdDisplay">
                            <?php if ($existingdata && $existingdata['form_pvt_ltd']): ?>
                                Current files: <?php echo displayGoogleDriveFiles($existingdata['form_pvt_ltd']); ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="file-upload" style="margin-bottom: 20px;">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">🏦</span>
                        <h4>Upload Company Account Statements of Last 3 Months</h4>
                        <p>Drag and drop your files here or click to browse</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF | Max size: 2MB each</p>
                        <input type="file" id="company_statements" name="company_statements[]" multiple accept=".jpg,.jpeg,.pdf">
                        <div class="file-name-display" id="companyStatementsDisplay">
                            <?php if ($existingdata && $existingdata['company_statements']): ?>
                                Current files: <?php echo displayGoogleDriveFiles($existingdata['company_statements']); ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="file-upload">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">📄</span>
                        <h4>Upload Other Documents</h4>
                        <p>Birth or Marriage Certificate if Applicable, Any other letters</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF | Max size: 2MB each</p>
                        <input type="file" id="otherDocumentsBusiness" name="other_documents[]" multiple accept=".jpg,.jpeg,.pdf">
                        <div class="file-name-display" id="otherDocumentsBusinessDisplay">
                            <?php if ($existingdata && $existingdata['other_documents']): ?>
                                Current files: <?php echo displayGoogleDriveFiles($existingdata['other_documents']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Freelancer Section -->
                <div id="freelancerSection" class="employment-section" style="display: <?php echo ($existingdata && $existingdata['employment_status'] == 'freelancer') ? 'block' : 'none'; ?>;">
                    <div class="file-upload" style="margin-bottom: 20px;">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">📋</span>
                        <h4>Upload Service Letters from Clients <span style="color: var(--error-color);">*</span></h4>
                        <p>Drag and drop your files here or click to browse</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF | Max size: 2MB each</p>
                        <input type="file" id="service_letters" name="service_letters[]" multiple accept=".jpg,.jpeg,.pdf">
                        <div class="file-name-display" id="serviceLettersDisplay">
                            <?php if ($existingdata && $existingdata['service_letters']): ?>
                                Current files: <?php echo displayGoogleDriveFiles($existingdata['service_letters']); ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="file-upload">
                        <div class="upload-progress">
                            <div class="spinner"></div>
                            <p>Uploading...</p>
                        </div>
                        <span class="upload-icon">📄</span>
                        <h4>Upload Other Documents</h4>
                        <p>Birth or Marriage Certificate if Applicable, Any other letters</p>
                        <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF | Max size: 2MB each</p>
                        <input type="file" id="otherDocumentsFreelancer" name="other_documents[]" multiple accept=".jpg,.jpeg,.pdf">
                        <div class="file-name-display" id="otherDocumentsFreelancerDisplay">
                            <?php if ($existingdata && $existingdata['other_documents']): ?>
                                Current files: <?php echo displayGoogleDriveFiles($existingdata['other_documents']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Student/Dependent Section -->
                <div id="studentDependentSection" class="employment-section" style="display: <?php echo ($existingdata && $existingdata['employment_status'] == 'student') ? 'block' : 'none'; ?>;">
                    <div class="form-group">
                        <label style="border-bottom: 2px solid var(--medium-gray); padding-bottom: 10px; margin-bottom: 20px;" required>If you are:</label>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="dependent_status" value="student" onclick="showDependentSection('student')" 
                                       <?php echo ($existingdata && $existingdata['dependent_status'] == 'student') ? 'checked' : ''; ?>>
                                <span>Student</span>
                            </label>
                            <label>
                                <input type="radio" name="dependent_status" value="dependent" onclick="showDependentSection('dependent')" 
                                       <?php echo ($existingdata && $existingdata['dependent_status'] == 'dependent') ? 'checked' : ''; ?>>
                                <span>Dependent</span>
                            </label>
                        </div>
                    </div>

                    <!-- Student Sub-section -->
                    <div id="studentSubSection" style="display: <?php echo ($existingdata && $existingdata['dependent_status'] == 'student') ? 'block' : 'none'; ?>;">
                        <div class="file-upload" style="margin-bottom: 20px;">
                            <div class="upload-progress">
                                <div class="spinner"></div>
                                <p>Uploading...</p>
                            </div>
                            <span class="upload-icon">🎓</span>
                            <h4>Upload Student Confirmation Letter <span style="color: var(--error-color);">*</span></h4>
                            <p>Drag and drop your files here or click to browse</p>
                            <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF | Max size: 2MB each</p>
                            <input type="file" id="student_letter" name="student_letter[]" multiple accept=".jpg,.jpeg,.pdf">
                            <div class="file-name-display" id="studentLetterDisplay">
                                <?php if ($existingdata && $existingdata['student_letter']): ?>
                                    Current files: <?php echo displayGoogleDriveFiles($existingdata['student_letter']); ?>
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
                            <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF | Max size: 2MB each</p>
                            <input type="file" id="dependentConfirmationStudent" name="dependent_confirmation[]" multiple accept=".jpg,.jpeg,.pdf">
                            <div class="file-name-display" id="dependentConfirmationStudentDisplay">
                                <?php if ($existingdata && $existingdata['dependent_confirmation']): ?>
                                    Current files: <?php echo displayGoogleDriveFiles($existingdata['dependent_confirmation']); ?>
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
                            <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF | Max size: 2MB each</p>
                            <input type="file" id="dependentIncomeStudent" name="dependent_income[]" multiple accept=".jpg,.jpeg,.pdf">
                            <div class="file-name-display" id="dependentIncomeStudentDisplay">
                                <?php if ($existingdata && $existingdata['dependent_income']): ?>
                                    Current files: <?php echo displayGoogleDriveFiles($existingdata['dependent_income']); ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="file-upload">
                            <div class="upload-progress">
                                <div class="spinner"></div>
                                <p>Uploading...</p>
                            </div>
                            <span class="upload-icon">📄</span>
                            <h4>Upload Other Documents</h4>
                            <p>Birth or Marriage Certificate if Applicable, Any other letters</p>
                            <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF | Max size: 2MB each</p>
                            <input type="file" id="otherDocumentsStudent" name="other_documents[]" multiple accept=".jpg,.jpeg,.pdf">
                            <div class="file-name-display" id="otherDocumentsStudentDisplay">
                                <?php if ($existingdata && $existingdata['other_documents']): ?>
                                    Current files: <?php echo displayGoogleDriveFiles($existingdata['other_documents']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Dependent Sub-section -->
                    <div id="dependentSubSection" style="display: <?php echo ($existingdata && $existingdata['dependent_status'] == 'dependent') ? 'block' : 'none'; ?>;">
                        <div class="file-upload" style="margin-bottom: 20px;">
                            <div class="upload-progress">
                                <div class="spinner"></div>
                                <p>Uploading...</p>
                            </div>
                            <span class="upload-icon">👨‍👩‍👧‍👦</span>
                            <h4>Upload Dependent Confirmation Letter <span style="color: var(--error-color);">*</span></h4>
                            <p>Drag and drop your files here or click to browse</p>
                            <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF | Max size: 2MB each</p>
                            <input type="file" id="dependentConfirmationDependent" name="dependent_confirmation[]" multiple accept=".jpg,.jpeg,.pdf">
                            <div class="file-name-display" id="dependentConfirmationDependentDisplay">
                                <?php if ($existingdata && $existingdata['dependent_confirmation']): ?>
                                    Current files: <?php echo displayGoogleDriveFiles($existingdata['dependent_confirmation']); ?>
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
                            <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF | Max size: 2MB each</p>
                            <input type="file" id="dependentIncomeDependent" name="dependent_income[]" multiple accept=".jpg,.jpeg,.pdf">
                            <div class="file-name-display" id="dependentIncomeDependentDisplay">
                                <?php if ($existingdata && $existingdata['dependent_income']): ?>
                                    Current files: <?php echo displayGoogleDriveFiles($existingdata['dependent_income']); ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="file-upload">
                            <div class="upload-progress">
                                <div class="spinner"></div>
                                <p>Uploading...</p>
                            </div>
                            <span class="upload-icon">📄</span>
                            <h4>Upload Other Documents</h4>
                            <p>Birth or Marriage Certificate if Applicable, Any other letters</p>
                            <p style="font-size: 14px; color: var(--dark-gray);">Accepted formats: JPG, PDF | Max size: 2MB each</p>
                            <input type="file" id="otherDocumentsDependent" name="other_documents[]" multiple accept=".jpg,.jpeg,.pdf">
                            <div class="file-name-display" id="otherDocumentsDependentDisplay">
                                <?php if ($existingdata && $existingdata['other_documents']): ?>
                                    Current files: <?php echo displayGoogleDriveFiles($existingdata['other_documents']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-submit" id="submitBtn">
                <?php echo $editing ? 'Update Application' : 'Submit Application'; ?>
            </button>
        </form>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('visaApplicationForm');
    const submitBtn = document.getElementById('submitBtn');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const progressBar = document.getElementById('progressBar');
    const loadingContent = document.getElementById('loadingContent');
    const successContent = document.getElementById('successContent');
    const isEditing = <?php echo $editing ? 'true' : 'false'; ?>;

    // Show loading popup with animation
    function showLoadingPopup() {
        loadingOverlay.style.display = 'flex';
        loadingContent.style.display = 'block';
        successContent.style.display = 'none';
        progressBar.style.width = '0%';
        
        // Animate progress bar
        let progress = 0;
        const progressInterval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 85) {
                progress = 85; // Don't complete until actual completion
            }
            progressBar.style.width = progress + '%';
        }, 200);
        
        return progressInterval;
    }

    // Complete loading animation
    function completeLoading() {
        progressBar.style.width = '100%';
        
        setTimeout(() => {
            loadingContent.style.display = 'none';
            successContent.style.display = 'block';
        }, 500);
    }

    // Hide loading popup
    function hideLoadingPopup() {
        setTimeout(() => {
            loadingOverlay.style.display = 'none';
            loadingContent.style.display = 'block';
            successContent.style.display = 'none';
            progressBar.style.width = '0%';
        }, 2500);
    }

    // Show error popup
    function showErrorMessage(message) {
        loadingOverlay.style.display = 'none';
        
        const alertDiv = document.createElement('div');
        alertDiv.className = 'alert alert-danger';
        alertDiv.innerHTML = message;
        form.insertBefore(alertDiv, form.firstChild);
        
        // Scroll to top
        window.scrollTo(0, 0);
        
        // Auto-hide error message after 7 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 7000);
    }

    // Enhanced field validation
    function validateField(field) {
        const value = field.value.trim();
        const isRequired = field.hasAttribute('required') || isFieldConditionallyRequired(field);
        
        if (isRequired && !value) {
            field.classList.add('error');
            field.classList.remove('valid');
            return false;
        } else if (value !== '') {
            field.classList.add('valid');
            field.classList.remove('error');
            return true;
        } else {
            field.classList.remove('valid', 'error');
            return !isRequired;
        }
    }

    // Check if field is conditionally required based on employment status
    function isFieldConditionallyRequired(field) {
        const employmentStatus = form.querySelector('input[name="employmentstatus"]:checked');
        const dependentStatus = form.querySelector('input[name="dependentstatus"]:checked');
        const fieldName = field.name || field.id;
        
        if (!employmentStatus) return false;
        
        const status = employmentStatus.value;
        const depStatus = dependentStatus ? dependentStatus.value : null;
        
        // Always required fields
        const alwaysRequired = [
            'nicnumber', 'nameforcertificates', 'namefortourid', 'addressline1', 
            'city', 'postalcode', 'province', 'surname', 'othername', 'birthYear', 
            'birthMonth', 'birthDay', 'gender', 'passportnumber', 'issueYear', 
            'issueMonth', 'issueDay', 'expiryYear', 'expiryMonth', 'expiryDay', 
            'passportcopy', 'photoid', 'visarequestletter', 'bankstatements', 'employmentstatus'
        ];
        
        if (alwaysRequired.includes(fieldName)) return true;
        
        // Check if field is in visible section and required for that employment type
        switch (status) {
            case 'employee':
                if (document.getElementById('employeeSection').style.display === 'block') {
                    return ['employmentletter', 'payslips'].includes(fieldName);
                }
                break;
            case 'business':
                if (document.getElementById('businessSection').style.display === 'block') {
                    return ['businessregistration'].includes(fieldName);
                }
                break;
            case 'freelancer':
                if (document.getElementById('freelancerSection').style.display === 'block') {
                    return ['serviceletters'].includes(fieldName);
                }
                break;
            case 'student':
                if (document.getElementById('studentDependentSection').style.display === 'block') {
                    if (depStatus === 'student') {
                        return ['dependentstatus', 'studentletter'].includes(fieldName);
                    } else if (depStatus === 'dependent') {
                        return ['dependentstatus', 'dependentconfirmation'].includes(fieldName);
                    }
                }
                break;
        }
        return fieldName === 'dependentstatus';
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
        const isRequired = input.hasAttribute('required') || isFieldConditionallyRequired(input);
        const hasFiles = input.files && input.files.length > 0;
        const hasExistingFile = input.closest('.file-upload').classList.contains('uploaded');
        
        if (isRequired && !hasFiles && !hasExistingFile) {
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
            return !isRequired;
        }
    }

    // Form submission handling with loading animation
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
                errors.push(labelText.replace('*', '').trim() + ' is required');
            }
        });

        // Validate required radio groups
        // if (!validateRadioGroup('employmentstatus')) {
        //     isValid = false;
        //     errors.push('Employment status selection is required');
        // }

        // Validate dependent status if student is selected
        const employmentStatus = form.querySelector('input[name="employmentstatus"]:checked');
        if (employmentStatus && employmentStatus.value === 'student') {
            if (!validateRadioGroup('dependentstatus')) {
                isValid = false;
                errors.push('Student/Dependent status selection is required');
            }
        }

        // Validate file inputs only in visible sections
        const fileInputsToValidate = form.querySelectorAll('input[type="file"]');
        fileInputsToValidate.forEach(input => {
            const section = input.closest('.employment-section');
            const isInVisibleSection = !section || section.style.display === 'block';
            
            if (isInVisibleSection && !validateFileInput(input)) {
                isValid = false;
                const labelText = input.closest('.file-upload').querySelector('h4').textContent;
                errors.push(labelText.replace('*', '').trim() + ' is required');
            }
        });

        if (!isValid) {
            // Show validation errors
            const errorMessage = `Please fill in all required fields:<br>• ${errors.slice(0, 5).join('<br>• ')}`;
            showErrorMessage(errorMessage);
            return;
        }

        // Start loading animation
        const progressInterval = showLoadingPopup();

        // Create FormData object
        const formData = new FormData(form);
        formData.append('ajax', '1'); // Add AJAX flag

        // Submit via AJAX with loading animation
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            clearInterval(progressInterval);
            completeLoading();
            
            if (data.success) {
                // Hide popup after showing success
                hideLoadingPopup();
                
                // Refresh page after delay to show updated data
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            } else {
                // Hide loading and show error
                setTimeout(() => {
                    showErrorMessage(data.message);
                }, 1000);
            }
        })
        .catch(error => {
            clearInterval(progressInterval);
            console.error('Error:', error);
            setTimeout(() => {
                showErrorMessage('An error occurred while submitting the form. Please try again.');
            }, 1000);
        });
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
                    display.innerHTML = fileNames.join(', ');

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
        'passportcopy', 'photoid', 'visarequestletter', 'bankstatements',
        'employmentletter', 'epfconfirmation', 'payslips', 'businessregistration',
        'formpvtltd', 'companystatements', 'serviceletters', 'studentletter',
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
        } else if (status === 'studentdependent') {
            document.getElementById('studentDependentSection').style.display = 'block';
        }

        // Revalidate all fields after section change
        setTimeout(() => {
            const formFields = form.querySelectorAll('input, select, textarea');
            formFields.forEach(field => {
                if (!field.classList.contains('locked')) {
                    validateField(field);
                }
            });

            // Validate file inputs in visible sections
            fileInputs.forEach(inputId => {
                const input = document.getElementById(inputId);
                if (input && (!input.closest('.employment-section') || input.closest('.employment-section').style.display === 'block')) {
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
            const formFields = form.querySelectorAll('input, select, textarea');
            formFields.forEach(field => {
                if (!field.classList.contains('locked')) {
                    validateField(field);
                }
            });
        }, 100);
    };

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

    // Show appropriate sections based on existing data
    <?php if ($existingdata && $existingdata['employmentstatus']): ?>
    showEmploymentSection('<?php echo $existingdata['employmentstatus']; ?>');
    <?php endif; ?>

    <?php if ($existingdata && $existingdata['dependentstatus']): ?>
    showDependentSection('<?php echo $existingdata['dependentstatus']; ?>');
    <?php endif; ?>
});
</script>

</body>
</html>
