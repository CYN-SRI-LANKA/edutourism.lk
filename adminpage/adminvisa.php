<?php
// adminvisa.php - Visa Management System with Role-Based Access Control and Dynamic Dropdowns
session_start();

// ===================================
// ROLE-BASED ACCESS CONTROL
// ===================================
// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: adminmain.php');
    exit();
}

// Role mapping and permission functions
function roles(): array { 
    if (!isset($_SESSION['user']['role']) || empty($_SESSION['user']['role'])) {
        return [];
    }
    
    // Map database roles to admin panel roles
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
    die('
    <html>
    <head>
        <title>Access Denied</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; padding: 50px; background: #f8f9fa; }
            .error-box { background: white; padding: 40px; border-radius: 12px; max-width: 500px; margin: 0 auto; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
            .error-icon { font-size: 4rem; color: #dc3545; margin-bottom: 20px; }
            h1 { color: #dc3545; margin-bottom: 10px; }
            p { color: #666; margin-bottom: 20px; }
            .btn { background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; }
            .user-info { background: #e9ecef; padding: 10px; border-radius: 6px; margin-top: 20px; font-size: 0.9em; }
        </style>
    </head>
    <body>
        <div class="error-box">
            <div class="error-icon">🚫</div>
            <h1>Access Denied</h1>
            <p><strong>You do not have permission to access the Visa Management System.</strong></p>
            <p>This system requires <strong>SUPER_ADMIN</strong> or <strong>VMS_ADMIN</strong> privileges.</p>
            <a href="adminmain.php" class="btn">← Back to Dashboard</a>
            <div class="user-info">
                Logged in as: <strong>' . htmlspecialchars($_SESSION['user']['username']) . '</strong><br>
                Current role: <strong>' . htmlspecialchars($_SESSION['user']['role']) . '</strong><br>
                Required roles: <strong>super</strong> or <strong>staff</strong>
            </div>
        </div>
    </body>
    </html>');
}

include('../homepage/db.php');

// Get current year for default selection
$current_year = date('Y');

// Get all available years for dropdown
$years_query = mysqli_query($con, "SELECT DISTINCT YEAR(created_at) as year FROM visa_applications ORDER BY year DESC");

// Check if form submitted for year/tour selection
$show_table = false;
$selected_year = '';
$selected_tour = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['year']) && isset($_POST['tour'])) {
    $selected_year = $_POST['year'];
    $selected_tour = $_POST['tour'];
    $show_table = true;
} elseif (isset($_GET['year']) && isset($_GET['tour'])) {
    // For maintaining selection when applying filters
    $selected_year = $_GET['year'];
    $selected_tour = $_GET['tour'];
    $show_table = true;
}

// Initialize table data variables
$total_count = 0;
$shown_count = 0;
$result = null;

if ($show_table) {
    // Get filter/search inputs (with XSS/SQL prevention)
    $search = trim($_GET['search'] ?? '');
    $filter_missing = $_GET['filter_missing'] ?? '';
    $filter_required = $_GET['filter_required'] ?? '';

    $search_sql = '';
    $where = [];

    // Base filter for selected year and tour
    $where[] = "YEAR(created_at) = '" . mysqli_real_escape_string($con, $selected_year) . "'";
    if($selected_tour) {
        $tour_escaped = mysqli_real_escape_string($con, $selected_tour);
        $where[] = "tourname = '$tour_escaped'";
    }

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

    // Required fields (add/modify as per your requirements)
    $required_fields = [
        'nic_number', 'name_for_certificates', 'surname', 'date_of_birth', 
        'gender', 'passport_number', 'employment_status'
    ];

    if ($filter_missing === "1") {
        $miss_checks = [];
        foreach ($file_columns as $col) {
            $miss_checks[] = "($col IS NULL OR $col = '')";
        }
        $where[] = '(' . implode(" OR ", $miss_checks) . ')';
    }

    if ($filter_required === "1") {
        $req_checks = [];
        foreach ($required_fields as $col) {
            $req_checks[] = "($col IS NULL OR $col = '')";
        }
        $where[] = '(' . implode(" OR ", $req_checks) . ')';
    }

    if ($where) $search_sql = " WHERE ".implode(" AND ", $where);

    // Get Total count (all/filtered)
    $base_where = "WHERE YEAR(created_at) = '" . mysqli_real_escape_string($con, $selected_year) . "'";
    if($selected_tour) {
        $base_where .= " AND tourname = '$tour_escaped'";
    }

    $total_q = mysqli_query($con, "SELECT COUNT(*) as cnt FROM visa_applications $base_where");
    $total_row = mysqli_fetch_assoc($total_q);
    $total_count = (int)$total_row['cnt'];

    $filter_q = mysqli_query($con, "SELECT COUNT(*) as cnt FROM visa_applications $search_sql");
    $filter_row = mysqli_fetch_assoc($filter_q);
    $shown_count = (int)$filter_row['cnt'];

    // Query Table Data
    $order = "ORDER BY created_at DESC";
    $sql = "SELECT * FROM visa_applications $search_sql $order";
    $result = mysqli_query($con, $sql);

    // Table Column Headings for Display
    $display_cols = [
        'nic_number' => 'NIC',
        'name_for_certificates' => 'Name (Cert)',
        'name_for_tour_id' => 'Name (TourID)',
        'surname' => 'Surname',
        'other_names' => 'Other Names',
        'date_of_birth' => 'DOB',
        'gender' => 'Gender',
        'passport_number' => 'Passport No',
        'employment_status' => 'Emp. Status',
        'dependent_status' => 'Dep. Status',
        'passport_copy' => 'Passport Copy',
        'photo_id' => 'Photo',
        'visa_request_letter' => 'Visa Req. Letter',
        'bank_statements' => 'Bank Stmts',
        'employment_letter' => 'Emp. Letter',
        'epf_confirmation' => 'EPF Conf.',
        'pay_slips' => 'Pay Slips',
        'business_registration' => 'Biz Reg.',
        'form_pvt_ltd' => 'PVT LTD Form 1',
        'company_statements' => 'Company Stmts',
        'service_letters' => 'Service Letters/Contracts',
        'student_letter' => 'Student Letter',
        'dependent_confirmation' => 'Dep. Confirmation',
        'dependent_income' => 'Dep. Income',
        'other_documents' => 'Other Docs',
        'application_status' => 'App Status',
        'created_at' => 'Created At',
        'updated_at' => 'Updated At',
    ];
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Panel - VMS</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    
    <!-- jQuery for AJAX functionality -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        body { font-family: "Segoe UI", Arial, Helvetica, sans-serif; background: #f7fafb; margin: 0; }
        .container { width: 98%; max-width: 1800px; margin: 30px auto 40px auto; background: #fff; border-radius: 12px; padding: 32px 18px; box-shadow: 0 6px 24px 3px #0002; }
        
        /* User Info Bar */
        .user-info-bar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        .user-info-bar .user-details {
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }
        .user-pill {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 600;
        }
        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.2s;
        }
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        /* Landing Page Styles */
        .landing-section { text-align: center; padding: 40px 20px; }
        .landing-section h1 { color: #1a3861; font-size: 2.5rem; margin-bottom: 30px; }
        .form-group { margin-bottom: 25px; text-align: left; max-width: 400px; margin-left: auto; margin-right: auto; }
        .form-group label { display: block; margin-bottom: 8px; color: #333; font-weight: 600; font-size: 16px; }
        .form-group select { width: 100%; padding: 12px; border: 1px solid #d0d6e4; border-radius: 6px; font-size: 16px; background: white; }
        .btn { padding: 12px 30px; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; text-decoration: none; display: inline-block; text-align: center; margin: 5px; }
        .btn-primary { background: #2b8e62; color: white; }
        .btn-primary:hover { background: #248853; }
        .btn-primary:disabled { background: #cccccc; cursor: not-allowed; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #545b62; }
        
        /* Dynamic Dropdown Styles */
        .loading-tours {
            color: #666;
            font-style: italic;
        }
        .tour-dropdown-disabled {
            opacity: 0.6;
            pointer-events: none;
        }
        
        /* Table Section Styles */
        .table-section { display: none; }
        .table-section.active { display: block; }
        .header-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
        .header-section h1 { color: #1a3861; font-size: 2rem; margin: 0; }
        .selection-display { background: #e9f2fc; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #2b8e62; }
        .selection-display h3 { margin: 0 0 8px 0; color: #1a3861; }
        .selection-info { display: flex; gap: 30px; flex-wrap: wrap; align-items: center; }
        .selection-item { color: #333; font-weight: 600; }
        .meta-bar { display:flex; flex-wrap:wrap; gap: 24px; align-items:center; margin-bottom:18px;}
        .meta-stat { font-size:1.02rem; color: #295; font-weight:bold;}
        .search-bar { display: flex; flex-wrap:wrap; gap:16px; align-items:center; margin-bottom:20px;}
        .search-in { font-size:1.12rem; padding:6px 9px; border-radius:7px; border:1px solid #d0d6e4;}
        .filter-row label { margin-right: 22px; font-size:.98rem;}
        .table-wrap {border-radius: 10px;box-shadow: 0 2px 18px 1px #0001; overflow-x: auto;}
        table { border-collapse:collapse; width:100%; min-width:1300px; background:#fff; }
        th,td { padding: 12px 9px; border-bottom:1px solid #e9ecf2; font-size:.99rem;}
        th { background: #e9f2fc; text-align:left; font-weight: 700; }
        tr:nth-child(even) { background: #f7fafb; }
        tr.missing { background: #fff2e9 !important; }
        tr.required-missing { background: #ffe6e6 !important; }
        td.missing { color: #ca3a2b; font-weight: bold; }
        td.required-missing { color: #d55; font-weight: bold; }
        .file-link { color:#406aad; text-decoration:underline; }
        .stat-dot {display:inline-block;width:12px;height:12px;border-radius:50%;margin-right:3px;vertical-align:middle;}
        .stat-dot.all {background:#99d2d0;}
        .stat-dot.shown {background:#7cb667;}
        .stat-dot.missing {background:#f6b085;}
        .stat-dot.required {background:#ff9999;}
        
        @media (max-width:1020px){
            .container{padding:10px 4px;}
            table{min-width:950px;}
        }
        @media (max-width:700px){
            th,td{font-size:85%;}
            .search-in{width:100%;}
            .header-section { flex-direction: column; align-items: flex-start; }
            .selection-info { flex-direction: column; gap: 10px; }
            .user-info-bar { flex-direction: column; gap: 10px; text-align: center; }
        }
    </style>
</head>
<body>
<div class="container">
    

    <?php if (!$show_table): ?>
    <!-- Landing Section with Dynamic Dropdown -->
    <div class="landing-section">
        <div>
            <a href="adminmain.php" class="btn btn-secondary">← Back to Main</a>
        </div>
        <h1>Visa Management System</h1>
        <p style="color: #666; font-size: 18px; margin-bottom: 40px;">Select year and tour to view visa applications</p>
        
        <form method="post" id="vmsForm">
            <div class="form-group">
                <label for="year">Select Year:</label>
                <select id="year" name="year" required>
                    <option value="">Choose Year</option>
                    <option value="<?php echo $current_year; ?>" selected><?php echo $current_year; ?> (Current)</option>
                    <?php 
                    mysqli_data_seek($years_query, 0);
                    while($year_row = mysqli_fetch_assoc($years_query)): 
                        if($year_row['year'] != $current_year): ?>
                            <option value="<?php echo $year_row['year']; ?>"><?php echo $year_row['year']; ?></option>
                        <?php endif; 
                    endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="tour">Select Tour:</label>
                <select id="tour" name="tour" required class="tour-dropdown-disabled">
                    <option value="">First select a year</option>
                </select>
            </div>
            
            <div style="margin-top: 30px;">
                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>🔍 View Applications</button>
            </div>
        </form>
    </div>
    
    <script>
$(document).ready(function() {
    // Load tours for current year on page load
    if ($('#year').val()) {
        loadTours($('#year').val());
    }
    
    // Handle year change
    $('#year').change(function() {
        var selectedYear = $(this).val();
        $('#submitBtn').prop('disabled', true);
        
        if (selectedYear) {
            loadTours(selectedYear);
        } else {
            resetTourDropdown();
        }
    });
    
    // Handle tour selection
    $('#tour').change(function() {
        $('#submitBtn').prop('disabled', $(this).val() === '');
    });
    
    function loadTours(year) {
        var $tourSelect = $('#tour');
        
        // Show loading state
        $tourSelect.removeClass('tour-dropdown-disabled')
                  .html('<option value="">Loading tours...</option>')
                  .addClass('loading-tours');
        
        $.ajax({
            url: 'ajax_get_tours.php',
            type: 'GET',
            data: { year: year },
            dataType: 'json',
            success: function(tours) {
                $tourSelect.removeClass('loading-tours').empty();
                
                if (tours.length > 0) {
                    $tourSelect.append('<option value="">Select Tour</option>');
                    
                    $.each(tours, function(index, tour) {
                        var tourname = typeof tour === 'string' ? tour : tour.tourname;
                        var displayText = tourname;
                        
                        // If tour object has additional data, enhance display
                        if (typeof tour === 'object') {
                            var statusBadge = '';
                            if (tour.tour_status === 'upcoming') {
                                statusBadge = ' 🔥';
                            } else if (tour.tour_status === 'past') {
                                statusBadge = ' 📅';
                            }
                            
                            // Show tour title if available
                            if (tour.title_en && tour.title_en !== tourname) {
                                displayText = tourname + ' (' + tour.title_en + ')' + statusBadge;
                            } else {
                                displayText = tourname + statusBadge;
                            }
                        }
                        
                        var option = $('<option></option>')
                                      .val(tourname)
                                      .text(displayText);
                        
                        // Select the first (latest) tour by default
                        if (index === 0) {
                            option.prop('selected', true);
                            $('#submitBtn').prop('disabled', false);
                            displayText += ' (Latest)';
                            option.text(displayText);
                        }
                        
                        $tourSelect.append(option);
                    });
                    
                } else {
                    $tourSelect.append('<option value="">No tours available for ' + year + '</option>');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                $tourSelect.removeClass('loading-tours')
                          .html('<option value="">Error loading tours</option>');
            }
        });
    }
    
    function resetTourDropdown() {
        $('#tour').addClass('tour-dropdown-disabled')
                 .html('<option value="">First select a year</option>');
    }
});
</script>

    
    <?php else: ?>
    <!-- Table Section -->
    <div class="table-section active">
        <div>
            <a href="adminvisa.php" class="btn btn-secondary">← Back</a>
        </div>
        <div class="header-section">
            <h1>Visa Management System</h1>
        </div>
        
        <div class="selection-display">
            <h3>Current Selection</h3>
            <div class="selection-info">
                <div class="selection-item">📅 Year: <span style="color: #2b8e62;"><?php echo htmlspecialchars($selected_year); ?></span></div>
                <div class="selection-item">🎯 Tour: <span style="color: #2b8e62;"><?php echo htmlspecialchars($selected_tour); ?></span></div>
            </div>
        </div>
        
        <div class="meta-bar">
            <span class="meta-stat">
                <span class="stat-dot all"></span>
                Total Records: <strong><?php echo $total_count; ?></strong>
            </span>
            <span class="meta-stat">
                <span class="stat-dot shown"></span>
                Showing: <strong><?php echo $shown_count; ?></strong>
            </span>
        </div>
        
        <form method="get" class="search-bar">
            <input type="hidden" name="year" value="<?php echo htmlspecialchars($selected_year); ?>">
            <input type="hidden" name="tour" value="<?php echo htmlspecialchars($selected_tour); ?>">
            <input class="search-in" type="text" name="search" placeholder="Search by NIC, Name, Passport No..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
            <label class="filter-row">
                <input type="checkbox" name="filter_missing" value="1" <?php if(($filter_missing ?? '') == "1") echo "checked";?>>
                Show only records with <span style="color:#d55; font-weight:600;">missing files</span>
                <span class="stat-dot missing"></span>
            </label>
            <label class="filter-row">
                <input type="checkbox" name="filter_required" value="1" <?php if(($filter_required ?? '') == "1") echo "checked";?>>
                Show only records with <span style="color:#d55; font-weight:600;">missing required fields</span>
                <span class="stat-dot required"></span>
            </label>
            <button type="submit" class="search-in" style="background:#2b8e62;color:#fff;border:none;">Apply Filters</button>
            <a href="adminvisa.php?year=<?php echo urlencode($selected_year); ?>&tour=<?php echo urlencode($selected_tour); ?>" class="search-in" style="background:#f6b085;color:#fff;text-decoration:none;border:none;">Reset Filters</a>
            <a href="file_explorer.php" target="_blank" class="search-in" style="background: #5ced1eff;color:#fff;text-decoration:none;border:none;">📁 Download</a>
        </form>
        
        <div class="table-wrap">
            <table>
                <thead>
                <tr>
                    <?php
                    foreach ($display_cols as $k=>$label) {
                        echo "<th>$label</th>";
                    }
                    ?>
                </tr>
                </thead>
                <tbody>
                <?php
                if ($result && mysqli_num_rows($result) > 0):
                while ($row = mysqli_fetch_assoc($result)):
                    $missing_files = false;
                    $missing_required = false;
                    
                    // Check for missing files
                    foreach ($file_columns as $col) {
                        if (empty($row[$col])) {
                            $missing_files = true; 
                            break;
                        }
                    }
                    
                    // Check for missing required fields
                    foreach ($required_fields as $col) {
                        if (empty($row[$col])) {
                            $missing_required = true; 
                            break;
                        }
                    }
                    
                    $row_class = '';
                    if($missing_required) $row_class = 'required-missing';
                    elseif($missing_files) $row_class = 'missing';
                    
                    echo '<tr class="'.$row_class.'">';
                    
                    foreach ($display_cols as $col_key=>$label) {
                        // For file fields, show "Available", file name or "Missing"
                        if (in_array($col_key, $file_columns)) {
                            if (empty($row[$col_key])) {
                                echo '<td class="missing">Missing</td>';
                            } else {
                                // File(s) can be comma-separated (for multi-upload fields)
                                $files = array_map('trim', explode(',', $row[$col_key]));
                                $out = [];
                                foreach ($files as $f) {
                                    if ($f) {
                                        $safe = htmlspecialchars($f);
                                        // (Optional) Link to download file if you store in a known folder path:
                                        $url = "../uploads/" . $safe; // Change as per your uploads path
                                        $out[] = "<a class='file-link' href='$url' target='_blank'>$safe</a>";
                                    }
                                }
                                echo "<td>".implode(', ',$out)."</td>";
                            }
                        } else {
                            // For required fields, highlight if missing
                            $value = htmlspecialchars($row[$col_key]??'');
                            $cell_class = '';
                            if(in_array($col_key, $required_fields) && empty($row[$col_key])) {
                                $cell_class = 'required-missing';
                                $value = $value ?: 'REQUIRED';
                            }
                            echo '<td class="'.$cell_class.'">' . $value . '</td>';
                        }
                    }
                    echo '</tr>';
                endwhile;
                else:
                ?>
                <tr>
                    <td colspan="<?php echo count($display_cols); ?>" style="text-align: center; padding: 40px; color: #666;">
                        🔍 No records found for the selected criteria.
                    </td>
                </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
