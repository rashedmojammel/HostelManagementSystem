<?php
// =============================================
// Fix All User Passwords
// =============================================

require_once 'includes/config.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Passwords</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; background: #f4f7f9; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #f5c6cb; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #3498db; color: white; }
        .btn { display: inline-block; background: #3498db; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #ffc107; }
    </style>
</head>
<body>
<div class='container'>
<h1>🔧 Fix User Passwords</h1>";

// Check if users exist
$check_sql = "SELECT COUNT(*) as count FROM users";
$result = mysqli_query($conn, $check_sql);
$row = mysqli_fetch_assoc($result);

if ($row['count'] == 0) {
    echo "<div class='error'>❌ No users found in database. Please run database.sql first!</div>";
    echo "</div></body></html>";
    exit();
}

echo "<p>Updating passwords for all default users...</p>";

// Array of users to update
$users = [
    ['email' => 'admin@campusnest.com', 'password' => 'admin123', 'role' => 'admin', 'name' => 'Admin User', 'phone' => '01700000000'],
    ['email' => 'manager@campusnest.com', 'password' => 'manager123', 'role' => 'manager', 'name' => 'Manager User', 'phone' => '01700000001'],
    ['email' => 'student@hostel.com', 'password' => 'student123', 'role' => 'student', 'name' => 'Student User', 'phone' => '01700000002']
];

$updated = 0;
$errors = 0;

foreach ($users as $user) {
    // Check if user exists
    $check = "SELECT user_id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $check);
    mysqli_stmt_bind_param($stmt, "s", $user['email']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $hashed_password = password_hash($user['password'], PASSWORD_DEFAULT);
    
    if (mysqli_num_rows($result) > 0) {
        // Update existing user
        $sql = "UPDATE users SET password = ?, full_name = ?, phone = ?, role = ?, status = 'active' WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssss", $hashed_password, $user['name'], $user['phone'], $user['role'], $user['email']);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<div class='success'>✅ Updated: {$user['email']} ({$user['role']})</div>";
            $updated++;
        } else {
            echo "<div class='error'>❌ Failed to update: {$user['email']}</div>";
            $errors++;
        }
    } else {
        // Insert new user
        $sql = "INSERT INTO users (full_name, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, 'active')";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssss", $user['name'], $user['email'], $user['phone'], $hashed_password, $user['role']);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<div class='success'>✅ Created: {$user['email']} ({$user['role']})</div>";
            $updated++;
        } else {
            echo "<div class='error'>❌ Failed to create: {$user['email']}</div>";
            $errors++;
        }
    }
}

echo "<hr>";
echo "<h2 style='color: #27ae60;'>✅ Password Update Complete!</h2>";
echo "<p><strong>{$updated}</strong> user(s) updated successfully.</p>";

if ($errors > 0) {
    echo "<p style='color: #e74c3c;'><strong>{$errors}</strong> error(s) occurred.</p>";
}

// Display all users
echo "<h3>📋 Current Users in Database:</h3>";
$all_users = mysqli_query($conn, "SELECT user_id, full_name, email, role, status, created_at FROM users ORDER BY role, email");

echo "<table>";
echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th></tr>";

while ($u = mysqli_fetch_assoc($all_users)) {
    $status_color = $u['status'] == 'active' ? '#27ae60' : '#e74c3c';
    echo "<tr>";
    echo "<td>{$u['user_id']}</td>";
    echo "<td>{$u['full_name']}</td>";
    echo "<td><strong>{$u['email']}</strong></td>";
    echo "<td><span style='background: #3498db; color: white; padding: 5px 10px; border-radius: 3px;'>{$u['role']}</span></td>";
    echo "<td style='color: {$status_color}; font-weight: bold;'>{$u['status']}</td>";
    echo "<td>" . date('M d, Y', strtotime($u['created_at'])) . "</td>";
    echo "</tr>";
}

echo "</table>";

// Test passwords
echo "<h3>🔐 Testing Password Verification:</h3>";

foreach ($users as $user) {
    $sql = "SELECT password FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $user['email']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($user['password'], $row['password'])) {
            echo "<div class='success'>✅ Password verification SUCCESS for: {$user['email']}</div>";
        } else {
            echo "<div class='error'>❌ Password verification FAILED for: {$user['email']}</div>";
        }
    }
}

echo "<hr>";
echo "<div style='background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
echo "<h3>🎉 Ready to Login!</h3>";
echo "<table style='background: white; border: 1px solid #ddd;'>";
echo "<tr style='background: #3498db; color: white;'><th>Role</th><th>Email</th><th>Password</th></tr>";
echo "<tr><td><strong>Admin</strong></td><td>admin@campusnest.com</td><td>admin123</td></tr>";
echo "<tr><td><strong>Manager</strong></td><td>manager@campusnest.com</td><td>manager123</td></tr>";
echo "<tr><td><strong>Student</strong></td><td>student@hostel.com</td><td>student123</td></tr>";
echo "</table>";
echo "</div>";

echo "<div style='text-align: center;'>";
echo "<a href='auth/login.php' class='btn'>🚀 Go to Login Page</a>";
echo "</div>";

echo "<div class='warning'>";
echo "<strong>⚠️ SECURITY WARNING:</strong> Delete this file (fix_passwords.php) immediately after use!";
echo "</div>";

echo "</div></body></html>";
?>
