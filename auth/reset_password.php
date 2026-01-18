<?php
// =============================================
// Reset Password Page
// =============================================

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Process password reset
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate inputs
    if (empty($email) || empty($new_password) || empty($confirm_password)) {
        setErrorMessage('Please fill in all fields');
    } elseif ($new_password !== $confirm_password) {
        setErrorMessage('Passwords do not match');
    } elseif (strlen($new_password) < 6) {
        setErrorMessage('Password must be at least 6 characters');
    } else {
        // Check if email exists
        $sql = "SELECT user_id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET password = ? WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user['user_id']);
            
            if (mysqli_stmt_execute($stmt)) {
                setSuccessMessage('Password reset successful! Please login with your new password.');
                header('Location: login.php');
                exit();
            } else {
                setErrorMessage('Password reset failed. Please try again.');
            }
        } else {
            setErrorMessage('Email not found');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h2>🏠 <?php echo SITE_NAME; ?></h2>
            </div>
            <div class="nav-links">
                <a href="../index.php">Home</a>
                <a href="login.php">Login</a>
            </div>
        </div>
    </nav>

    <!-- Reset Password Form -->
    <div class="form-container">
        <h2>🔑 Reset Password</h2>
        
        <!-- Display messages -->
        <?php echo displayMessages(); ?>
        
        <form method="POST" action="">
            <!-- Email -->
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <!-- New Password -->
            <div class="form-group">
                <label for="new_password">New Password *</label>
                <input type="password" id="new_password" name="new_password" required>
                <small style="color: #7f8c8d;">Minimum 6 characters</small>
            </div>
            
            <!-- Confirm Password -->
            <div class="form-group">
                <label for="confirm_password">Confirm New Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <!-- Submit Button -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Reset Password</button>
            </div>
        </form>
        
        <!-- Links -->
        <div class="form-link">
            <p><a href="login.php">Back to Login</a></p>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
