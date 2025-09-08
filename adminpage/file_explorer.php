<?php
// ---- CONFIG ----
$baseDir = "uploads/visa";

// ---- Handle Any Download Request ----
if (isset($_GET['download_folder'])) {
    $folder = trim($_GET['download_folder']);
    $folder = basename($folder); // more secure
    $dir = "$baseDir/$folder";
    if (!$folder || !is_dir($dir)) die("Folder not found.");

    $zipName = $folder . ".zip";
    $zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zipName;

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE)
        die("Failed to create zip");

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    foreach ($files as $file) {
        $filePath = $file->getRealPath();
        $localPath = substr($filePath, strlen($dir) + 1);
        $zip->addFile($filePath, $localPath);
    }
    $zip->close();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipName . '"');
    header('Content-Length: ' . filesize($zipPath));
    readfile($zipPath);
    unlink($zipPath);
    exit;
}

if (isset($_GET['download_all'])) {
    $zipName = "All_Applicants.zip";
    $zipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $zipName;

    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE)
        die("Failed to create zip");

    $folders = array_filter(glob($baseDir . '/*'), 'is_dir');
    foreach ($folders as $folder) {
        $folderName = basename($folder);
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folder, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($files as $file) {
            $filePath = $file->getRealPath();
            $localPath = $folderName . '/' . substr($filePath, strlen($folder) + 1);
            $zip->addFile($filePath, $localPath);
        }
    }
    $zip->close();

    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zipName . '"');
    header('Content-Length: ' . filesize($zipPath));
    readfile($zipPath);
    unlink($zipPath);
    exit;
}

// ---- List all folders ----
$folders = [];
if (is_dir($baseDir)) {
    $items = scandir($baseDir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        if (is_dir("$baseDir/$item")) {
            $folders[] = $item;
        }
    }
}

// ---- Show files in folder if requested ----
$show_files = false;
$active_folder = '';
$files_list = [];
if (isset($_GET['folder'])) {
    $active_folder = trim($_GET['folder']);
    $active_folder = basename($active_folder);
    $dir = "$baseDir/$active_folder";
    if (is_dir($dir)) {
        $show_files = true;
        $files_in_dir = array_diff(scandir($dir), ['.', '..']);
        foreach ($files_in_dir as $file) {
            if (is_file("$dir/$file")) {
                $files_list[] = $file;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Applicant Folders Explorer & Download</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body { font-family: Arial, sans-serif; background: #f7fafb; padding:27px;}
        .container { background: #fff; border-radius:10px; padding:32px 22px; box-shadow:0 2px 14px 2px #0001; max-width:580px; margin:38px auto;}
        h2 { color: #1a3861; margin-bottom:18px;}
        ul { padding-left:24px;}
        li { margin-bottom:13px; }
        a, .btn { color: #2b8e62; text-decoration: underline; cursor:pointer;}
        .btn { color:#fff; background:#2b8e62; border:none; padding:7px 13px; border-radius:8px; text-decoration:none; margin-left:9px; font-size:.97em;}
        .btn-all { background:#5584bc; }
        .file-preview { display:block;margin-top:4px;max-width:171px;max-height:110px;border:1px solid #dde;}
        .nofiles { color:#b8513e; }
        .back { display:inline-block;margin-bottom:20px;color:#566; text-decoration:none;}
        .folderlist { margin-bottom:35px; }
    </style>
</head>
<body>
<div class="container">
    <h2>Applicant Folders Explorer</h2>

    <!-- Download All -->
    <div style="margin-bottom:26px;">
        <a href="?download_all=1" class="btn btn-all">Download All Folders as ZIP</a>
    </div>

    <?php if ($show_files): ?>
        <!-- Files in Selected Folder -->
        <a class='back' href="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">&laquo; Back to folders</a>
        <h3>
            Files for <span style='color:#296;'><?php echo htmlspecialchars($active_folder); ?></span>
            <a href="?download_folder=<?php echo urlencode($active_folder); ?>" class="btn">Download Folder ZIP</a>
        </h3>
        <?php if ($files_list): ?>
            <ul>
                <?php foreach ($files_list as $file): 
                    $safe_fn = htmlspecialchars($file);
                    $file_url = "$baseDir/$active_folder/$safe_fn";
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $is_img = in_array($ext, ['jpg','jpeg','png','gif','webp','bmp']);
                ?>
                <li>
                    <a href="<?php echo $file_url; ?>" target="_blank"><?php echo $safe_fn; ?></a>
                    <?php if ($is_img): ?>
                        <br><img src="<?php echo $file_url; ?>" alt="<?php echo $safe_fn; ?>" class="file-preview">
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="nofiles">No files found in this folder.</p>
        <?php endif; ?>
    <?php else: ?>
        <!-- List All Folders -->
        <div class="folderlist">
        <?php if ($folders): ?>
            <ul>
            <?php foreach ($folders as $folder): ?>
                <li>
                    <a href="?folder=<?php echo urlencode($folder); ?>">
                        <?php echo htmlspecialchars($folder); ?>
                    </a>
                    <a href="?download_folder=<?php echo urlencode($folder); ?>" class="btn">Download ZIP</a>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="nofiles">No applicant folders found.</p>
        <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
