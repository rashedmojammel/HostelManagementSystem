<?php
// =============================================
// Student - Profile Management
// =============================================

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is student
requireRole('student');

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
            header('Location: profile.php');
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
            header('Location: profile.php');
            exit();
        } else {
            setErrorMessage('Failed to change password');
        }
    }
}

// Handle account deletion
if (isset($_POST['delete_account'])) {
    $confirm_password = $_POST['confirm_delete_password'];
    
    if (empty($confirm_password)) {
        setErrorMessage('Please enter your password to confirm deletion');
    } elseif (!password_verify($confirm_password, $user['password'])) {
        setErrorMessage('Incorrect password');
    } else {
        if (deleteUserAccount($_SESSION['user_id'])) {
            session_destroy();
            header('Location: ../index.php');
            exit();
        } else {
            setErrorMessage('Failed to delete account');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h2>🏠 <?php echo SITE_NAME; ?> - Student</h2>
            </div>
            <div class="nav-links">
                <span style="color: white;">Welcome, <?php echo $_SESSION['full_name']; ?></span>
                <a href="dashboard.php">Dashboard</a>
                <a href="profile.php">Profile</a>
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
                    <li><a href="rooms.php">🏠 Available Rooms</a></li>
                    <li><a href="bookings.php">📅 My Bookings</a></li>
                    <li><a href="payments.php">💳 Payments</a></li>
                    <li><a href="complaints.php">📝 Complaints</a></li>
                    <li><a href="meals.php">🍽️ Meal Schedule</a></li>
                    <li><a href="events.php">📢 Events</a></li>
                    <li><a href="room_transfer.php">🔄 Room Transfer</a></li>
                    <li><a href="profile.php" class="active">👤 Profile</a></li>
                </ul>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Page Header -->
                <div class="dashboard-header">
                    <h1>👤 My Profile</h1>
                    <p>Manage your account settings</p>
                </div>

                <!-- Display messages -->
                <?php echo displayMessages(); ?>

                <!-- Profile Information -->
                <div class="card">
                    <div class="card-header">
                        <h3>📝 Profile Information</h3>
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

                                <!-- Status (Read-only) -->
                                <div class="form-group">
                                    <label for="status">Account Status</label>
                                    <input type="text" id="status" 
                                           value="<?php echo ucfirst($user['status']); ?>" 
                                           style="color: <?php echo $user['status'] == 'active' ? '#27ae60' : '#e74c3c'; ?>; font-weight: bold;"
                                           disabled>
                                </div>

                                <!-- Member Since -->
                                <div class="form-group">
                                    <label for="created_at">Member Since</label>
                                    <input type="text" id="created_at" value="<?php echo formatDate($user['created_at']); ?>" disabled>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" name="update_profile" class="btn btn-primary">💾 Update Profile</button>
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
                                <button type="submit" name="change_password" class="btn btn-warning">🔐 Change Password</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Delete Account -->
                <div class="card" style="border: 2px solid #e74c3c;">
                    <div class="card-header" style="background-color: #e74c3c; color: white;">
                        <h3 style="margin: 0;">⚠️ Danger Zone</h3>
                    </div>
                    <div class="card-body">
                        <h4>Delete Account</h4>
                        <p style="color: #7f8c8d; margin-bottom: 1rem;">
                            Once you delete your account, there is no going back. Please be certain.
                        </p>
                        
                        <form method="POST" action="" onsubmit="return confirm('Are you absolutely sure you want to delete your account? This action cannot be undone!');">
                            <div class="form-group">
                                <label for="confirm_delete_password">Enter your password to confirm *</label>
                                <input type="password" id="confirm_delete_password" name="confirm_delete_password" required
                                       placeholder="Enter your password">
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" name="delete_account" class="btn btn-danger">
                                    🗑️ Delete My Account
                                </button>
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
