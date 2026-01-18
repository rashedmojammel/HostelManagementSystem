<?php
// =============================================
// Admin - Students Management
// =============================================

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
requireRole('admin');

// Handle student status update (Block/Activate)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $student_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    if ($action === 'block') {
        $sql = "UPDATE users SET status = 'blocked' WHERE user_id = ? AND role = 'student'";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $student_id);
        if (mysqli_stmt_execute($stmt)) {
            setSuccessMessage('Student account blocked successfully');
        } else {
            setErrorMessage('Failed to block student account');
        }
    } elseif ($action === 'activate') {
        $sql = "UPDATE users SET status = 'active' WHERE user_id = ? AND role = 'student'";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $student_id);
        if (mysqli_stmt_execute($stmt)) {
            setSuccessMessage('Student account activated successfully');
        } else {
            setErrorMessage('Failed to activate student account');
        }
    } elseif ($action === 'delete') {
        $sql = "DELETE FROM users WHERE user_id = ? AND role = 'student'";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $student_id);
        if (mysqli_stmt_execute($stmt)) {
            setSuccessMessage('Student deleted successfully');
        } else {
            setErrorMessage('Failed to delete student');
        }
    }
    
    header('Location: students.php');
    exit();
}

// Get all students
$sql = "SELECT * FROM users WHERE role = 'student' ORDER BY created_at DESC";
$students = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students Management - <?php echo SITE_NAME; ?></title>
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
                    <li><a href="students.php" class="active">👨‍🎓 Students</a></li>
                    <li><a href="rooms.php">🏠 Rooms</a></li>
                    <li><a href="bookings.php">📅 Bookings</a></li>
                    <li><a href="payments.php">💳 Payments</a></li>
                    <li><a href="complaints.php">📝 Complaints</a></li>
                    <li><a href="events.php">📢 Events</a></li>
                    <li><a href="settings.php">⚙️ Settings</a></li>
                </ul>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Page Header -->
                <div class="dashboard-header">
                    <h1>👨‍🎓 Students Management</h1>
                    <p>View and manage all student accounts</p>
                </div>

                <!-- Display messages -->
                <?php echo displayMessages(); ?>

                <!-- Students Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>All Students (<?php echo mysqli_num_rows($students); ?>)</h2>
                        <div>
                            <input type="text" id="searchInput" placeholder="🔍 Search students..." 
                                   style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                        </div>
                    </div>
                    <table id="studentsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Registered</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($students) > 0): ?>
                                <?php while ($student = mysqli_fetch_assoc($students)): ?>
                                    <tr>
                                        <td>#<?php echo $student['user_id']; ?></td>
                                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                        <td>
                                            <?php if ($student['status'] === 'active'): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Blocked</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo formatDateTime($student['created_at']); ?></td>
                                        <td class="actions">
                                            <?php if ($student['status'] === 'active'): ?>
                                                <a href="?action=block&id=<?php echo $student['user_id']; ?>" 
                                                   class="btn btn-warning btn-small"
                                                   onclick="return confirm('Block this student?')">Block</a>
                                            <?php else: ?>
                                                <a href="?action=activate&id=<?php echo $student['user_id']; ?>" 
                                                   class="btn btn-success btn-small"
                                                   onclick="return confirm('Activate this student?')">Activate</a>
                                            <?php endif; ?>
                                            <a href="?action=delete&id=<?php echo $student['user_id']; ?>" 
                                               class="btn btn-danger btn-small"
                                               onclick="return confirm('Delete this student permanently?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">No students found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    <script>
        // Initialize table search
        filterTable('searchInput', 'studentsTable');
    </script>
</body>
</html>
