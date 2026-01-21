<?php


require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
requireRole('admin');

// Handle complaint assignment to manager
if (isset($_POST['assign_complaint'])) {
    $complaint_id = intval($_POST['complaint_id']);
    $manager_id = intval($_POST['manager_id']);
    
    $sql = "UPDATE complaints SET manager_id = ?, status = 'assigned' WHERE complaint_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $manager_id, $complaint_id);
    
    if (mysqli_stmt_execute($stmt)) {
        setSuccessMessage('Complaint assigned to manager successfully');
    } else {
        setErrorMessage('Failed to assign complaint');
    }
    
    header('Location: complaints.php');
    exit();
}

// Handle complaint status update
if (isset($_GET['action']) && isset($_GET['id'])) {
    $complaint_id = intval($_GET['id']);
    $action = $_GET['action'];
    
    $status = '';
    switch ($action) {
        case 'resolve':
            $status = 'resolved';
            break;
        case 'close':
            $status = 'closed';
            break;
    }
    
    if ($status) {
        $sql = "UPDATE complaints SET status = ? WHERE complaint_id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "si", $status, $complaint_id);
        
        if (mysqli_stmt_execute($stmt)) {
            setSuccessMessage('Complaint status updated successfully');
        } else {
            setErrorMessage('Failed to update complaint status');
        }
        
        header('Location: complaints.php');
        exit();
    }
}

// Get all complaints
$sql = "SELECT c.*, u.full_name as student_name, m.full_name as manager_name 
        FROM complaints c 
        JOIN users u ON c.student_id = u.user_id 
        LEFT JOIN users m ON c.manager_id = m.user_id 
        ORDER BY c.created_at DESC";
$complaints = mysqli_query($conn, $sql);

// Get all managers for assignment
$sql_managers = "SELECT user_id, full_name FROM users WHERE role = 'manager' AND status = 'active'";
$managers = mysqli_query($conn, $sql_managers);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaints Management - <?php echo SITE_NAME; ?></title>
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
                    <li><a href="complaints.php" class="active">📝 Complaints</a></li>
                    <li><a href="events.php">📢 Events</a></li>
                    <li><a href="settings.php">⚙️ Settings</a></li>
                </ul>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Page Header -->
                <div class="dashboard-header">
                    <h1>📝 Complaints Management</h1>
                    <p>View and manage student complaints</p>
                </div>

                <!-- Display messages -->
                <?php echo displayMessages(); ?>

                <!-- Complaints Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>All Complaints (<?php echo mysqli_num_rows($complaints); ?>)</h2>
                        <input type="text" id="searchInput" placeholder="🔍 Search complaints..." 
                               style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <table id="complaintsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Type</th>
                                <th>Subject</th>
                                <th>Description</th>
                                <th>Assigned To</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($complaints) > 0): ?>
                                <?php 
                                mysqli_data_seek($complaints, 0); // Reset pointer
                                while ($complaint = mysqli_fetch_assoc($complaints)): 
                                ?>
                                    <tr>
                                        <td>#<?php echo $complaint['complaint_id']; ?></td>
                                        <td><?php echo htmlspecialchars($complaint['student_name']); ?></td>
                                        <td><?php echo ucfirst($complaint['complaint_type']); ?></td>
                                        <td><?php echo htmlspecialchars($complaint['subject']); ?></td>
                                        <td style="max-width: 200px;"><?php echo htmlspecialchars(substr($complaint['description'], 0, 100)) . '...'; ?></td>
                                        <td><?php echo $complaint['manager_name'] ? htmlspecialchars($complaint['manager_name']) : 'Not Assigned'; ?></td>
                                        <td>
                                            <?php
                                            $badge_class = '';
                                            switch ($complaint['status']) {
                                                case 'resolved': $badge_class = 'badge-success'; break;
                                                case 'pending': $badge_class = 'badge-warning'; break;
                                                case 'assigned': $badge_class = 'badge-info'; break;
                                                case 'in_progress': $badge_class = 'badge-info'; break;
                                                case 'closed': $badge_class = 'badge-secondary'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?></span>
                                        </td>
                                        <td><?php echo formatDateTime($complaint['created_at']); ?></td>
                                        <td class="actions">
                                            <?php if ($complaint['status'] === 'pending'): ?>
                                                <!-- Assign to Manager Form -->
                                                <form method="POST" action="" style="display: inline-block;">
                                                    <input type="hidden" name="complaint_id" value="<?php echo $complaint['complaint_id']; ?>">
                                                    <select name="manager_id" required style="padding: 0.3rem; font-size: 0.85rem; border: 1px solid #ddd; border-radius: 3px;">
                                                        <option value="">Select Manager</option>
                                                        <?php 
                                                        mysqli_data_seek($managers, 0); // Reset pointer
                                                        while ($manager = mysqli_fetch_assoc($managers)): 
                                                        ?>
                                                            <option value="<?php echo $manager['user_id']; ?>"><?php echo htmlspecialchars($manager['full_name']); ?></option>
                                                        <?php endwhile; ?>
                                                    </select>
                                                    <button type="submit" name="assign_complaint" class="btn btn-primary btn-small">Assign</button>
                                                </form>
                                            <?php elseif ($complaint['status'] === 'assigned' || $complaint['status'] === 'in_progress'): ?>
                                                <a href="?action=resolve&id=<?php echo $complaint['complaint_id']; ?>" 
                                                   class="btn btn-success btn-small"
                                                   onclick="return confirm('Mark as resolved?')">Resolve</a>
                                            <?php elseif ($complaint['status'] === 'resolved'): ?>
                                                <a href="?action=close&id=<?php echo $complaint['complaint_id']; ?>" 
                                                   class="btn btn-secondary btn-small"
                                                   onclick="return confirm('Close this complaint?')">Close</a>
                                            <?php else: ?>
                                                <span style="color: #95a5a6;">Closed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center;">No complaints found</td>
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
        filterTable('searchInput', 'complaintsTable');
    </script>
</body>
</html>
