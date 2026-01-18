<?php
// =============================================
// Common Functions for Hostel Management System
// =============================================

// Include configuration
require_once 'config.php';

// =============================================
// Authentication Functions
// =============================================

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Check if user has specific role
 * @param string $role - Role to check (admin, manager, student)
 * @return bool
 */
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/auth/login.php');
        exit();
    }
}

/**
 * Redirect if user doesn't have required role
 * @param string $role - Required role
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: ' . SITE_URL . '/index.php');
        exit();
    }
}

/**
 * Check if user account is active
 * @param int $user_id
 * @return bool
 */
function isAccountActive($user_id) {
    global $conn;
    $sql = "SELECT status FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    return $user && $user['status'] === 'active';
}

// =============================================
// User Functions
// =============================================

/**
 * Get user information by ID
 * @param int $user_id
 * @return array|null
 */
function getUserById($user_id) {
    global $conn;
    $sql = "SELECT * FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result);
}

/**
 * Update user profile
 * @param int $user_id
 * @param array $data
 * @return bool
 */
function updateUserProfile($user_id, $data) {
    global $conn;
    $sql = "UPDATE users SET full_name = ?, email = ?, phone = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $data['full_name'], $data['email'], $data['phone'], $user_id);
    return mysqli_stmt_execute($stmt);
}

/**
 * Change user password
 * @param int $user_id
 * @param string $new_password
 * @return bool
 */
function changePassword($user_id, $new_password) {
    global $conn;
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET password = ? WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
    return mysqli_stmt_execute($stmt);
}

/**
 * Delete user account
 * @param int $user_id
 * @return bool
 */
function deleteUserAccount($user_id) {
    global $conn;
    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    return mysqli_stmt_execute($stmt);
}

// =============================================
// Dashboard Statistics Functions
// =============================================

/**
 * Get total count from table
 * @param string $table - Table name
 * @param string $condition - Optional WHERE condition
 * @return int
 */
function getCount($table, $condition = '') {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM $table";
    if ($condition) {
        $sql .= " WHERE $condition";
    }
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'];
}

// =============================================
// Sanitization Functions
// =============================================

/**
 * Sanitize input data
 * @param string $data
 * @return string
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Validate email format
 * @param string $email
 * @return bool
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validate phone number (Bangladesh format)
 * @param string $phone
 * @return bool
 */
function validatePhone($phone) {
    return preg_match('/^01[3-9]\d{8}$/', $phone);
}

// =============================================
// Alert/Message Functions
// =============================================

/**
 * Set success message in session
 * @param string $message
 */
function setSuccessMessage($message) {
    $_SESSION['success_message'] = $message;
}

/**
 * Set error message in session
 * @param string $message
 */
function setErrorMessage($message) {
    $_SESSION['error_message'] = $message;
}

/**
 * Display and clear session messages
 * @return string HTML
 */
function displayMessages() {
    $html = '';
    if (isset($_SESSION['success_message'])) {
        $html .= '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
        unset($_SESSION['success_message']);
    }
    if (isset($_SESSION['error_message'])) {
        $html .= '<div class="alert alert-error">' . $_SESSION['error_message'] . '</div>';
        unset($_SESSION['error_message']);
    }
    return $html;
}

// =============================================
// Date/Time Functions
// =============================================

/**
 * Format date for display
 * @param string $date
 * @return string
 */
function formatDate($date) {
    return date('d M, Y', strtotime($date));
}

/**
 * Format datetime for display
 * @param string $datetime
 * @return string
 */
function formatDateTime($datetime) {
    return date('d M, Y h:i A', strtotime($datetime));
}

?>
