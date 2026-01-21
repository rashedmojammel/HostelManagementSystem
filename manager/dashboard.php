<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is manager
requireRole('manager');

// Get dashboard statistics
$manager_id = $_SESSION['user_id'];
$total_students = getCount('users', "role = 'student' AND status = 'active'");
$total_rooms = getCount('rooms');
$pending_bookings = getCount('bookings', "status = 'pending'");
$assigned_complaints = getCount('complaints', "manager_id = $manager_id AND status != 'closed'");
$available_rooms = getCount('rooms', "status = 'available'");

// Get recent assigned complaints
$sql = "SELECT c.*, u.full_name 
        FROM complaints c 
        JOIN users u ON c.student_id = u.user_id 
        WHERE c.manager_id = ? 
        ORDER BY c.created_at DESC LIMIT 5";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $manager_id);
mysqli_stmt_execute($stmt);
$recent_complaints = mysqli_stmt_get_result($stmt);

// Get recent bookings
$sql = "SELECT b.*, u.full_name, r.room_number, r.room_type 
        FROM bookings b 
        JOIN users u ON b.student_id = u.user_id 
        JOIN rooms r ON b.room_id = r.room_id 
        ORDER BY b.created_at DESC LIMIT 5";
$recent_bookings = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <!-- Navigation -->
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
                    <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
                    <li><a href="students.php">👨‍🎓 Students</a></li>
                    <li><a href="rooms.php">🏠 Rooms</a></li>
                    <li><a href="bookings.php">📅 Bookings</a></li>
                    <li><a href="complaints.php">📝 Complaints</a></li>
                    <li><a href="meals.php">🍽️ Meals</a></li>
                    <li><a href="profile.php">👤 Profile</a></li>
                </ul>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Welcome Header -->
                <div class="dashboard-header">
                    <h1>Manager Dashboard</h1>
                    <p>Welcome back, <?php echo $_SESSION['full_name']; ?>! Manage daily hostel operations.</p>
                </div>

                <!-- Display messages -->
                <?php echo displayMessages(); ?>

                <!-- Statistics Cards -->
                <div class="dashboard-stats">
                    <!-- Total Students -->
                    <div class="stat-card" style="border-left: 4px solid #3498db;">
                        <div class="stat-icon">👨‍🎓</div>
                        <div class="stat-info">
                            <h3><?php echo $total_students; ?></h3>
                            <p>Active Students</p>
                        </div>
                    </div>

                    <!-- Total Rooms -->
                    <div class="stat-card" style="border-left: 4px solid #27ae60;">
                        <div class="stat-icon">🏠</div>
                        <div class="stat-info">
                            <h3><?php echo $total_rooms; ?></h3>
                            <p>Total Rooms</p>
                        </div>
                    </div>

                    <!-- Available Rooms -->
                    <div class="stat-card" style="border-left: 4px solid #2ecc71;">
                        <div class="stat-icon">✅</div>
                        <div class="stat-info">
                            <h3><?php echo $available_rooms; ?></h3>
                            <p>Available Rooms</p>
                        </div>
                    </div>

                    <!-- Pending Bookings -->
                    <div class="stat-card" style="border-left: 4px solid #f39c12;">
                        <div class="stat-icon">⏳</div>
                        <div class="stat-info">
                            <h3><?php echo $pending_bookings; ?></h3>
                            <p>Pending Bookings</p>
                        </div>
                    </div>

                    <!-- Assigned Complaints -->
                    <div class="stat-card" style="border-left: 4px solid #e74c3c;">
                        <div class="stat-icon">📝</div>
                        <div class="stat-info">
                            <h3><?php echo $assigned_complaints; ?></h3>
                            <p>My Complaints</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>Recent Bookings</h2>
                        <a href="bookings.php" class="btn btn-primary btn-small">View All</a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Room</th>
                                <th>Check-in Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($recent_bookings) > 0): ?>
                                <?php while ($booking = mysqli_fetch_assoc($recent_bookings)): ?>
                                    <tr>
                                        <td>#<?php echo $booking['booking_id']; ?></td>
                                        <td><?php echo htmlspecialchars($booking['full_name']); ?></td>
                                        <td><?php echo $booking['room_number'] . ' (' . ucfirst($booking['room_type']) . ')'; ?></td>
                                        <td><?php echo formatDate($booking['check_in_date']); ?></td>
                                        <td>
                                            <?php
                                            $badge_class = '';
                                            switch ($booking['status']) {
                                                case 'approved': $badge_class = 'badge-success'; break;
                                                case 'pending': $badge_class = 'badge-warning'; break;
                                                case 'rejected': $badge_class = 'badge-danger'; break;
                                                default: $badge_class = 'badge-info';
                                            }
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($booking['status']); ?></span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center;">No bookings found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- My Assigned Complaints -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>My Assigned Complaints</h2>
                        <a href="complaints.php" class="btn btn-primary btn-small">View All</a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Type</th>
                                <th>Subject</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($recent_complaints) > 0): ?>
                                <?php while ($complaint = mysqli_fetch_assoc($recent_complaints)): ?>
                                    <tr>
                                        <td>#<?php echo $complaint['complaint_id']; ?></td>
                                        <td><?php echo htmlspecialchars($complaint['full_name']); ?></td>
                                        <td><?php echo ucfirst($complaint['complaint_type']); ?></td>
                                        <td><?php echo htmlspecialchars($complaint['subject']); ?></td>
                                        <td>
                                            <?php
                                            $badge_class = '';
                                            switch ($complaint['status']) {
                                                case 'resolved': $badge_class = 'badge-success'; break;
                                                case 'pending': $badge_class = 'badge-warning'; break;
                                                case 'assigned': $badge_class = 'badge-info'; break;
                                                case 'in_progress': $badge_class = 'badge-info'; break;
                                                default: $badge_class = 'badge-secondary';
                                            }
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?></span>
                                        </td>
                                        <td><?php echo formatDateTime($complaint['created_at']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">No complaints assigned</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
