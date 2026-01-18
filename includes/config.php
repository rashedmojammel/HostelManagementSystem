<?php
// =============================================
// Database Configuration
// =============================================

// Database connection parameters
define('DB_HOST', 'localhost');      // Database host
define('DB_USER', 'root');           // Database username
define('DB_PASS', '');               // Database password
define('DB_NAME', 'hostel_management'); // Database name

// Create database connection
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8");

// =============================================
// Session Configuration
// =============================================

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// =============================================
// Site Configuration
// =============================================

define('SITE_NAME', 'Hostel Management System');
define('SITE_URL', 'http://localhost/HostelManagementSystem');

// =============================================
// Timezone Configuration
// =============================================

date_default_timezone_set('Asia/Dhaka');

?>
