// File: includes/security.php (NEW FILE)
<?php
// =============================================
// Enhanced Security Functions
// =============================================

// CSRF Token Protection
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Session Security
function secureSession() {
    // Regenerate session ID periodically
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
    
    // Session timeout (30 minutes)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        session_unset();
        session_destroy();
        header('Location: /auth/login.php?timeout=1');
        exit();
    }
    $_SESSION['last_activity'] = time();
}

// IP and User Agent validation
function validateSession() {
    if (!isset($_SESSION['user_ip'])) {
        $_SESSION['user_ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    }
    
    if ($_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR'] || 
        $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        session_destroy();
        header('Location: /auth/login.php?security=1');
        exit();
    }
}
?>
