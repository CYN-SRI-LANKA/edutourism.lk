<?php
// fix_passwords.php - Convert plaintext passwords to proper hashes
include('../homepage/db.php');

$users = [
    'gayanraj' => ['password' => 'admin123', 'role' => 'super'],
    'tharusha' => ['password' => 'admin123', 'role' => 'admin'], 
    'tharushika' => ['password' => 'admin123', 'role' => 'staff']
];

echo "<h2>🔐 Password Hashing Results</h2>";

foreach ($users as $username => $data) {
    $password = $data['password'];
    $role = $data['role'];
    
    // Generate proper bcrypt hash
    $hash = password_hash($password, PASSWORD_DEFAULT);
    
    echo "<strong>Processing: $username</strong><br>";
    echo "Original: $password<br>";
    echo "New hash length: " . strlen($hash) . " chars<br>";
    echo "Hash preview: " . substr($hash, 0, 20) . "...<br>";
    
    // Update database
    $stmt = $con->prepare("UPDATE employees SET password = ?, role = ?, active = 1 WHERE username = ?");
    if ($stmt) {
        $stmt->bind_param("sss", $hash, $role, $username);
        
        if ($stmt->execute()) {
            echo "✅ <span style='color:green'>SUCCESS: $username updated</span><br><br>";
        } else {
            echo "❌ <span style='color:red'>FAILED: " . $stmt->error . "</span><br><br>";
        }
        $stmt->close();
    } else {
        echo "❌ <span style='color:red'>PREPARE FAILED: " . $con->error . "</span><br><br>";
    }
}

echo "<h3 style='color:green'>✅ Password Hashing Complete!</h3>";
echo "<p><strong style='color:red'>IMPORTANT: Delete this file now for security!</strong></p>";
echo "<p><a href='adminmain.php'>← Go to Admin Login</a></p>";
echo "<p><a href='debug_login.php'>🔍 Test Again with Debug</a></p>";
?>
