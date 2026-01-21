<?php


require_once '../includes/config.php';
require_once '../includes/functions.php';

if (isLoggedIn()) {
    $role = $_SESSION['role'];
    header("Location: ../$role/dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = 'student'; // Default role for registration
    
    if (empty($full_name) || empty($email) || empty($phone) || empty($password)) {
        setErrorMessage('Please fill in all fields');
    } elseif (!validateEmail($email)) {
        setErrorMessage('Invalid email format');
    } elseif (!validatePhone($phone)) {
        setErrorMessage('Invalid phone number. Use format: 01XXXXXXXXX');
    } elseif (strlen($password) < 6) {
        setErrorMessage('Password must be at least 6 characters');
    } elseif ($password !== $confirm_password) {
        setErrorMessage('Passwords do not match');
    } else {

    $sql = "SELECT user_id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if (mysqli_num_rows($result) > 0) {
            setErrorMessage('Email already registered');
        } else {
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO users (full_name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssss", $full_name, $email, $phone, $hashed_password, $role);
            
            if (mysqli_stmt_execute($stmt)) {
                setSuccessMessage('Registration successful! Please login.');
                header('Location: login.php');
                exit();
            } else {
                setErrorMessage('Registration failed. Please try again.');
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    
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

    <!-- Registration Form -->
    <div class="form-container">
        <h2>📝 Create New Account</h2>
        
        <!-- Display messages -->
        <?php echo displayMessages(); ?>
        
        <form method="POST" action="" onsubmit="return validateRegistrationForm(event)">
            <!-- Full Name -->
            <div class="form-group">
                <label for="full_name">Full Name *</label>
                <input type="text" id="full_name" name="full_name"placeholder="Full name"  required 
                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
            </div>
            
            <!-- Email -->
            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" placeholder="Email" required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>
            
            <!-- Phone -->
            <div class="form-group">
                <label for="phone">Phone Number *</label>
                <input type="text" id="phone" name="phone" placeholder="01XXXXXXXXX" required
                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
            </div>
            
            <!-- Password -->
            <div class="form-group">
                <label for="password">Password *</label>
                <input type="password" id="password" name="password" placeholder="Password"  required>
                <small style="color: #7f8c8d;">Minimum 6 characters</small>
            </div>
            
            <!-- Confirm Password -->
            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm password"  required>
            </div>
            
            <!-- Submit Button -->
            <div class="form-group">
                <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
            </div>
        </form>
        
        <!-- Links -->
        <div class="form-link">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
