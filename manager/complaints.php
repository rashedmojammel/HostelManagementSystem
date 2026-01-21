<?php
// =============================================
// Manager - Complaints Management
// =============================================

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is manager
requireRole('manager');

$manager_id = $_SESSION['user_id'];

// Handle complaint status update
if (isset($_POST['update_status'])) {
    $complaint_id = intval($_POST['complaint_id']);
    $status = sanitizeInput($_POST['status']);
    
    $sql = "UPDATE complaints SET status = ? WHERE complaint_id = ? AND manager_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sii", $status, $complaint_id, $manager_id);
    
    if (mysqli_stmt_execute($stmt)) {
        setSuccessMessage('Complaint status updated successfully');
    } else {
        setErrorMessage('Failed to update complaint status');
    }
    
    header('Location: complaints.php');
    exit();
}

// Get complaints assigned to this manager
$sql = "SELECT c.*, u.full_name as student_name, u.email, u.phone 
        FROM complaints c 
        JOIN users u ON c.student_id = u.user_id 
        WHERE c.manager_id = ? 
        ORDER BY c.created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $manager_id);
mysqli_stmt_execute($stmt);
$complaints = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complaints - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <!-- Navigation bar -->
    <nav class="navbar">
        <div class="container">
            <div class="nav-brand">
                <h2>🏠 <?php echo SITE_NAME; ?> - Manager</h2>
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
                    <li><a href="students.php">👨‍🎓 Students</a></li>
                    <li><a href="rooms.php">🏠 Rooms</a></li>
                    <li><a href="bookings.php">📅 Bookings</a></li>
                    <li><a href="complaints.php" class="active">📝 Complaints</a></li>
                    <li><a href="meals.php">🍽️ Meals</a></li>
                    <li><a href="profile.php">👤 Profile</a></li>
                </ul>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Page Header -->
                <div class="dashboard-header">
                    <h1>📝 My Assigned Complaints</h1>
                    <p>View and update complaint status</p>
                </div>

                <!-- Display messages -->
                <?php echo displayMessages(); ?>

                <!-- Complaints Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>Assigned Complaints (<?php echo mysqli_num_rows($complaints); ?>)</h2>
                        <input type="text" id="searchInput" placeholder="🔍 Search complaints..." 
                               style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <table id="complaintsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Contact</th>
                                <th>Type</th>
                                <th>Subject</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($complaints) > 0): ?>
                                <?php while ($complaint = mysqli_fetch_assoc($complaints)): ?>
                                    <tr>
                                        <td>#<?php echo $complaint['complaint_id']; ?></td>
                                        <td><?php echo htmlspecialchars($complaint['student_name']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($complaint['email']); ?><br>
                                            <small><?php echo htmlspecialchars($complaint['phone']); ?></small>
                                        </td>
                                        <td><?php echo ucfirst($complaint['complaint_type']); ?></td>
                                        <td><?php echo htmlspecialchars($complaint['subject']); ?></td>
                                        <td style="max-width: 250px;"><?php echo htmlspecialchars($complaint['description']); ?></td>
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
                                        <td>
                                            <?php if ($complaint['status'] !== 'closed'): ?>
                                                <!-- Update Status Form -->
                                                <form method="POST" action="" style="display: inline-block;">
                                                    <input type="hidden" name="complaint_id" value="<?php echo $complaint['complaint_id']; ?>">
                                                    <select name="status" required style="padding: 0.3rem; font-size: 0.85rem; border: 1px solid #ddd; border-radius: 3px;">
                                                        <option value="assigned" <?php echo $complaint['status'] == 'assigned' ? 'selected' : ''; ?>>Assigned</option>
                                                        <option value="in_progress" <?php echo $complaint['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                                        <option value="resolved" <?php echo $complaint['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                                                    </select>
                                                    <button type="submit" name="update_status" class="btn btn-primary btn-small">Update</button>
                                                </form>
                                            <?php else: ?>
                                                <span style="color: #95a5a6;">Closed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center;">No complaints assigned to you</td>
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
        filterTable('searchInput', 'complaintsTable');
    </script>
</body>
</html>
