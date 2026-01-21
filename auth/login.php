<?php


require_once '../includes/config.php';
require_once '../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $role = $_SESSION['role'];
    header("Location: ../$role/dashboard.php");
    exit();
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    // Validate inputs
    if (empty($email) || empty($password)) {
        setErrorMessage('Please fill in all fields');
    } else {
        // Check user credentials
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                // Check if account is active
                if ($user['status'] === 'blocked') {
                    setErrorMessage('Your account has been blocked. Please contact admin.');
                } else {
                    // Set session variables
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    
                    // Redirect based on role
                    header("Location: ../{$user['role']}/dashboard.php");
                    exit();
                }
            } else {
                setErrorMessage('Invalid email or password');
            }
        } else {
            setErrorMessage('Invalid email or password');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
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
                <a href="register.php">Register</a>
            </div>
        </div>
    </nav>

    <!-- Login Form -->
    <div class="form-container">
        <h2>🔐 Login to Your Account</h2>
        
        <!-- Display messages -->
        <?php echo displayMessages(); ?>
        
        <form method="POST" action="" onsubmit="return validateLoginForm(event)">
            <!-- Email -->
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <!-- Password -->
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <!-- Submit Button -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            </div>
        </form>
        
        <!-- Links -->
        <div class="form-link">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
            <p><a href="reset_password.php">Forgot Password?</a></p>
        </div>
        
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
