<?php
// =============================================
// Debug Login Issues
// =============================================

require_once 'includes/config.php';

echo "<!DOCTYPE html><html><head><title>Login Debug</title>";
echo "<style>body{font-family:Arial;margin:40px;background:#f4f7f9;} .box{background:white;padding:20px;margin:10px 0;border-radius:5px;border-left:4px solid #3498db;} .success{border-color:#27ae60;background:#d4edda;} .error{border-color:#e74c3c;background:#f8d7da;} table{width:100%;border-collapse:collapse;margin:10px 0;} th,td{padding:10px;border:1px solid #ddd;text-align:left;} th{background:#3498db;color:white;} pre{background:#f8f9fa;padding:10px;border-radius:3px;overflow:auto;}</style>";
echo "</head><body>";

echo "<h1>🔍 Login Debug Tool</h1>";

// Test 1: Database Connection
echo "<div class='box'>";
echo "<h3>Test 1: Database Connection</h3>";
if ($conn) {
    echo "✅ Database connected successfully<br>";
    echo "Database: <strong>hostel_management</strong>";
} else {
    echo "❌ Database connection failed: " . mysqli_connect_error();
}
echo "</div>";

// Test 2: Check Users Table
echo "<div class='box'>";
echo "<h3>Test 2: Users in Database</h3>";
$result = mysqli_query($conn, "SELECT user_id, full_name, email, role, status FROM users");
if ($result) {
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr>";
    $user_count = 0;
    while ($user = mysqli_fetch_assoc($result)) {
        echo "<tr>";
        echo "<td>{$user['user_id']}</td>";
        echo "<td>{$user['full_name']}</td>";
        echo "<td><strong>{$user['email']}</strong></td>";
        echo "<td>{$user['role']}</td>";
        echo "<td>{$user['status']}</td>";
        echo "</tr>";
        $user_count++;
    }
    echo "</table>";
    echo "Total users: <strong>{$user_count}</strong>";
} else {
    echo "❌ Error: " . mysqli_error($conn);
}
echo "</div>";

// Test 3: Test Each Login
$test_logins = [
    ['email' => 'admin@campusnest.com', 'password' => 'admin123', 'expected_role' => 'admin'],
    ['email' => 'manager@campusnest.com', 'password' => 'manager123', 'expected_role' => 'manager'],
    ['email' => 'student@hostel.com', 'password' => 'student123', 'expected_role' => 'student']
];

foreach ($test_logins as $test) {
    echo "<div class='box'>";
    echo "<h3>Test: {$test['email']} / {$test['password']}</h3>";
    
    // Check if user exists
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $test['email']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($user = mysqli_fetch_assoc($result)) {
        echo "✅ User found in database<br>";
        echo "User ID: {$user['user_id']}<br>";
        echo "Name: {$user['full_name']}<br>";
        echo "Email: {$user['email']}<br>";
        echo "Role: {$user['role']}<br>";
        echo "Status: {$user['status']}<br>";
        echo "Password Hash (first 30 chars): " . substr($user['password'], 0, 30) . "...<br><br>";
        
        // Test password verification
        if (password_verify($test['password'], $user['password'])) {
            echo "<div class='success'><strong>✅ PASSWORD VERIFICATION: SUCCESS!</strong><br>";
            echo "This login should work: {$test['email']} / {$test['password']}</div>";
        } else {
            echo "<div class='error'><strong>❌ PASSWORD VERIFICATION: FAILED!</strong><br>";
            echo "The password '{$test['password']}' does NOT match the hash in database.<br>";
            echo "This account needs password reset.</div>";
            
            // Show how to fix
            echo "<br><strong>Fix Command:</strong><br>";
            echo "<pre>UPDATE users SET password = '" . password_hash($test['password'], PASSWORD_DEFAULT) . "' WHERE email = '{$test['email']}';</pre>";
        }
        
    } else {
        echo "<div class='error'>❌ User NOT found with email: {$test['email']}</div>";
        echo "<br><strong>Create User:</strong><br>";
        $hash = password_hash($test['password'], PASSWORD_DEFAULT);
        echo "<pre>INSERT INTO users (full_name, email, phone, password, role) VALUES ('Test User', '{$test['email']}', '01700000000', '{$hash}', '{$test['expected_role']}');</pre>";
    }
    
    echo "</div>";
}

// Test 4: Simulate Actual Login Process
echo "<div class='box'>";
echo "<h3>Test 4: Simulate Login for admin@campusnest.com</h3>";

$test_email = 'admin@campusnest.com';
$test_password = 'admin123';

echo "Attempting login with:<br>";
echo "Email: <strong>{$test_email}</strong><br>";
echo "Password: <strong>{$test_password}</strong><br><br>";

$sql = "SELECT * FROM users WHERE email = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $test_email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($user = mysqli_fetch_assoc($result)) {
    echo "✅ Step 1: User found<br>";
    
    if (password_verify($test_password, $user['password'])) {
        echo "✅ Step 2: Password verified<br>";
        
        if ($user['status'] === 'blocked') {
            echo "❌ Step 3: Account is BLOCKED<br>";
        } else {
            echo "✅ Step 3: Account is active<br>";
            echo "<div class='success'><strong>🎉 LOGIN SHOULD SUCCEED!</strong><br>";
            echo "Would redirect to: {$user['role']}/dashboard.php</div>";
        }
    } else {
        echo "❌ Step 2: Password verification FAILED<br>";
        echo "<div class='error'>This is why login fails!</div>";
    }
} else {
    echo "❌ Step 1: User not found with email: {$test_email}<br>";
}

echo "</div>";

// Quick Fix Button
echo "<div class='box' style='background:#fff3cd;border-color:#ffc107;'>";
echo "<h3>⚡ Quick Fix</h3>";
echo "<p>Click button below to automatically fix all passwords:</p>";
echo "<form method='POST' action=''>";
echo "<button type='submit' name='auto_fix' style='background:#27ae60;color:white;padding:15px 30px;border:none;border-radius:5px;font-size:16px;cursor:pointer;'>🔧 Auto-Fix All Passwords Now</button>";
echo "</form>";
echo "</div>";

// Handle Auto Fix
if (isset($_POST['auto_fix'])) {
    echo "<div class='box success'>";
    echo "<h3>🔧 Auto-Fixing Passwords...</h3>";
    
    $fixes = [
        ['email' => 'admin@campusnest.com', 'password' => 'admin123', 'role' => 'admin', 'name' => 'Admin User'],
        ['email' => 'manager@campusnest.com', 'password' => 'manager123', 'role' => 'manager', 'name' => 'Manager User'],
        ['email' => 'student@hostel.com', 'password' => 'student123', 'role' => 'student', 'name' => 'Student User']
    ];
    
    foreach ($fixes as $fix) {
        $hash = password_hash($fix['password'], PASSWORD_DEFAULT);
        
        // Check if exists
        $check = mysqli_query($conn, "SELECT user_id FROM users WHERE email = '{$fix['email']}'");
        
        if (mysqli_num_rows($check) > 0) {
            // Update
            $sql = "UPDATE users SET password = '{$hash}', role = '{$fix['role']}', status = 'active' WHERE email = '{$fix['email']}'";
            mysqli_query($conn, $sql);
            echo "✅ Updated: {$fix['email']}<br>";
        } else {
            // Insert
            $sql = "INSERT INTO users (full_name, email, phone, password, role) VALUES ('{$fix['name']}', '{$fix['email']}', '01700000000', '{$hash}', '{$fix['role']}')";
            mysqli_query($conn, $sql);
            echo "✅ Created: {$fix['email']}<br>";
        }
    }
    
    echo "<br><strong>✅ All passwords fixed!</strong><br>";
    echo "<a href='debug_login_issue.php' style='color:#3498db;'>Refresh to verify</a> | ";
    echo "<a href='auth/login.php' style='color:#27ae60;font-weight:bold;'>Go to Login Page</a>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='text-align:center;'><a href='auth/login.php' style='background:#3498db;color:white;padding:15px 30px;text-decoration:none;border-radius:5px;'>Go to Login Page</a></p>";
echo "<p style='color:#e74c3c;text-align:center;'><strong>⚠️ Delete this file after fixing!</strong></p>";

echo "</body></html>";
?>
