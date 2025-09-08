<?php
// admin_visa_panel.php

include('../homepage/db.php');

// --- Get filter/search inputs (with XSS/SQL prevention) ---
$search = trim($_GET['search'] ?? '');
$filter_missing = $_GET['filter_missing'] ?? '';
$search_sql = '';
$where = [];

if ($search) {
    $search_db = mysqli_real_escape_string($con, $search);
    $like = "%$search_db%";
    // Add or extend to more fields as needed for searching:
    $where[] = "(nic_number LIKE '$like' OR name_for_certificates LIKE '$like' OR surname LIKE '$like' OR passport_number LIKE '$like')";
}

// File attachment columns (include all here!)
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
if ($where) $search_sql = " WHERE ".implode(" AND ", $where);

// --- Get Total count (all/filtered) ---
$total_q = mysqli_query($con, "SELECT COUNT(*) as cnt FROM visa_applications");
$total_row = mysqli_fetch_assoc($total_q);
$total_count = (int)$total_row['cnt'];
$filter_q = mysqli_query($con, "SELECT COUNT(*) as cnt FROM visa_applications $search_sql");
$filter_row = mysqli_fetch_assoc($filter_q);
$shown_count = (int)$filter_row['cnt'];

// --- Query Table Data ---
$order = "ORDER BY created_at DESC";
$sql = "SELECT * FROM visa_applications $search_sql $order";
$result = mysqli_query($con, $sql);

// --- Table Column Headings for Display (adjust order/labels as needed) ---
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

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Panel - VMS</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body { font-family: "Segoe UI", Arial, Helvetica, sans-serif; background: #f7fafb; margin: 0; }
        .container { width: 98%; max-width: 1800px; margin: 30px auto 40px auto; background: #fff; border-radius: 12px; padding: 32px 18px; box-shadow: 0 6px 24px 3px #0002; }
        h1 { color: #1a3861; font-size: 2rem; margin-bottom:18px; }
        .meta-bar { display:flex; flex-wrap:wrap; gap: 24px; align-items:center; margin-bottom:18px;}
        .meta-stat { font-size:1.02rem; color: #295; font-weight:bold;}
        .search-bar { display: flex; flex-wrap:wrap; gap:16px; align-items:center; margin-bottom:20px;}
        .search-in { font-size:1.12rem; padding:6px 9px; border-radius:7px; border:1px solid #d0d6e4;}
        .filter-row label { margin-right: 22px; font-size:.98rem;}
        .table-wrap {border-radius: 10px;box-shadow: 0 2px 18px 1px #0001;}
        table { border-collapse:collapse; width:100%; min-width:1300px; background:#fff; }
        th,td { padding: 12px 9px; border-bottom:1px solid #e9ecf2; font-size:.99rem;}
        th { background: #e9f2fc; text-align:left; font-weight: 700; }
        tr:nth-child(even) { background: #f7fafb; }
        tr.missing { background: #fff2e9 !important; }
        td.missing { color: #ca3a2b; font-weight: bold; }
        .file-link { color:#406aad; text-decoration:underline; }
        .stat-dot {display:inline-block;width:12px;height:12px;border-radius:50%;margin-right:3px;vertical-align:middle;}
        .stat-dot.all {background:#99d2d0;}
        .stat-dot.shown {background:#7cb667;}
        .stat-dot.missing {background:#f6b085;}
        @media (max-width:1020px){
            .container{padding:10px 4px;}
            table{min-width:950px;}
        }
        @media (max-width:700px){
            th,td{font-size:85%;}
            .search-in{width:100%;}
        }
    </style>
</head>
<body>
<div class="container">
    <h1>🗂️ Visa Applications Admin Panel</h1>
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
        <input class="search-in" type="text" name="search" placeholder="Search by NIC, Name, Passport No..." value="<?php echo htmlspecialchars($search); ?>">
        <label class="filter-row">
            <input type="checkbox" name="filter_missing" value="1" <?php if($filter_missing=="1") echo "checked";?>>
            Show only records with <span style="color:#d55; font-weight:600;">missing files</span>
        </label>
        <button type="submit" class="search-in" style="background:#2b8e62;color:#fff;border:none;">Apply</button>
        <a href="admin_visa_panel.php" class="search-in" style="background:#f6b085;color:#fff;text-decoration:none;border:none;">Reset</a>
        <a href="file_explorer.php" target="_blank" class="search-in" style="
        background: #5ced1eff;
        color:#fff;
        text-decoration:none;
        border:none;">Download</a>
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
            while ($row = mysqli_fetch_assoc($result)):
                $missing = false;
                foreach ($file_columns as $col) {
                    if (empty($row[$col])) {
                        $missing = true; break;
                    }
                }
                echo '<tr class="'.($missing? 'missing':'').'">';
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
                                    $url = "uploads/" . $safe; // Change as per your uploads path
                                    $out[] = "<a class='file-link' href='$url' target='_blank'>$safe</a>";
                                }
                            }
                            echo "<td>".implode(', ',$out)."</td>";
                        }
                    } else {
                        echo '<td>' . htmlspecialchars($row[$col_key]??'') . '</td>';
                    }
                }
                echo '</tr>';
            endwhile;
            ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
