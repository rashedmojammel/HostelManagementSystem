<?php
// =============================================
// Admin - Settings (Profile & Password)
// =============================================

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
requireRole('admin');

// Get current user data
$user = getUserById($_SESSION['user_id']);

// Handle profile update
if (isset($_POST['update_profile'])) {
    $full_name = sanitizeInput($_POST['full_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    
    if (empty($full_name) || empty($email) || empty($phone)) {
        setErrorMessage('Please fill in all fields');
    } elseif (!validateEmail($email)) {
        setErrorMessage('Invalid email format');
    } elseif (!validatePhone($phone)) {
        setErrorMessage('Invalid phone number format');
    } else {
        $data = [
            'full_name' => $full_name,
            'email' => $email,
            'phone' => $phone
        ];
        
        if (updateUserProfile($_SESSION['user_id'], $data)) {
            $_SESSION['full_name'] = $full_name;
            $_SESSION['email'] = $email;
            setSuccessMessage('Profile updated successfully');
            header('Location: settings.php');
            exit();
        } else {
            setErrorMessage('Failed to update profile');
        }
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        setErrorMessage('Please fill in all password fields');
    } elseif (!password_verify($current_password, $user['password'])) {
        setErrorMessage('Current password is incorrect');
    } elseif (strlen($new_password) < 6) {
        setErrorMessage('New password must be at least 6 characters');
    } elseif ($new_password !== $confirm_password) {
        setErrorMessage('New passwords do not match');
    } else {
        if (changePassword($_SESSION['user_id'], $new_password)) {
            setSuccessMessage('Password changed successfully');
            header('Location: settings.php');
            exit();
        } else {
            setErrorMessage('Failed to change password');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h2>🏠 <?php echo SITE_NAME; ?> - Admin</h2>
            </div>
            <div class="nav-links">
                <span style="color: white;">Welcome, <?php echo $_SESSION['full_name']; ?></span>
                <a href="dashboard.php">Dashboard</a>
                <a href="settings.php">Settings</a>
                <a href="../auth/logout.php" class="btn btn-danger btn-small">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Dashboard Layout -->
    <div class="container">
        <div class="dashboard-layout">
            <!-- Sidebar -->
            <aside class="sidebar">
                <ul class="sidebar-menu">
                    <li><a href="dashboard.php">📊 Dashboard</a></li>
                    <li><a href="students.php">👨‍🎓 Students</a></li>
                    <li><a href="rooms.php">🏠 Rooms</a></li>
                    <li><a href="bookings.php">📅 Bookings</a></li>
                    <li><a href="payments.php">💳 Payments</a></li>
                    <li><a href="complaints.php">📝 Complaints</a></li>
                    <li><a href="events.php">📢 Events</a></li>
                    <li><a href="settings.php" class="active">⚙️ Settings</a></li>
                </ul>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Page Header -->
                <div class="dashboard-header">
                    <h1>⚙️ Settings</h1>
                    <p>Manage your profile and account settings</p>
                </div>

                <!-- Display messages -->
                <?php echo displayMessages(); ?>

                <!-- Update Profile -->
                <div class="card">
                    <div class="card-header">
                        <h3>👤 Update Profile</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                                <!-- Full Name -->
                                <div class="form-group">
                                    <label for="full_name">Full Name *</label>
                                    <input type="text" id="full_name" name="full_name" required
                                           value="<?php echo htmlspecialchars($user['full_name']); ?>">
                                </div>
                                
                                <!-- Email -->
                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input type="email" id="email" name="email" required
                                           value="<?php echo htmlspecialchars($user['email']); ?>">
                                </div>
                                
                                <!-- Phone -->
                                <div class="form-group">
                                    <label for="phone">Phone *</label>
                                    <input type="text" id="phone" name="phone" required
                                           value="<?php echo htmlspecialchars($user['phone']); ?>">
                                </div>
                                
                                <!-- Role (Read-only) -->
                                <div class="form-group">
                                    <label for="role">Role</label>
                                    <input type="text" id="role" value="<?php echo ucfirst($user['role']); ?>" disabled>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="card">
                    <div class="card-header">
                        <h3>🔒 Change Password</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <!-- Current Password -->
                            <div class="form-group">
                                <label for="current_password">Current Password *</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
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
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" name="change_password" class="btn btn-warning">Change Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
