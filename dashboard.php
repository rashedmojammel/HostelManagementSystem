<?php
// =============================================
// Student Dashboard
// =============================================

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is student
requireRole('student');

$student_id = $_SESSION['user_id'];

// Get dashboard statistics
$my_bookings = getCount('bookings', "student_id = $student_id");
$active_bookings = getCount('bookings', "student_id = $student_id AND status = 'approved'");
$my_complaints = getCount('complaints', "student_id = $student_id");
$pending_complaints = getCount('complaints', "student_id = $student_id AND status = 'pending'");
$my_payments = getCount('payments', "student_id = $student_id AND status = 'paid'");

// Get recent bookings
$sql = "SELECT b.*, r.room_number, r.room_type, r.price 
        FROM bookings b 
        JOIN rooms r ON b.room_id = r.room_id 
        WHERE b.student_id = ? 
        ORDER BY b.created_at DESC LIMIT 5";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$recent_bookings = mysqli_stmt_get_result($stmt);

// Get recent complaints
$sql = "SELECT * FROM complaints WHERE student_id = ? ORDER BY created_at DESC LIMIT 5";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$recent_complaints = mysqli_stmt_get_result($stmt);

// Get upcoming events
$sql = "SELECT * FROM events WHERE event_date >= CURDATE() ORDER BY event_date ASC LIMIT 3";
$upcoming_events = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - <?php echo SITE_NAME; ?></title>
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
                    <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
                    <li><a href="rooms.php">🏠 Available Rooms</a></li>
                    <li><a href="bookings.php">📅 My Bookings</a></li>
                    <li><a href="payments.php">💳 Payments</a></li>
                    <li><a href="complaints.php">📝 Complaints</a></li>
                    <li><a href="meals.php">🍽️ Meal Schedule</a></li>
                    <li><a href="events.php">📢 Events</a></li>
                    <li><a href="room_transfer.php">🔄 Room Transfer</a></li>
                    <li><a href="profile.php">👤 Profile</a></li>
                </ul>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Welcome Header -->
                <div class="dashboard-header">
                    <h1>Student Dashboard</h1>
                    <p>Welcome back, <?php echo $_SESSION['full_name']; ?>! Manage your hostel activities.</p>
                </div>

                <!-- Display messages -->
                <?php echo displayMessages(); ?>

                <!-- Statistics Cards -->
                <div class="dashboard-stats">
                    <!-- Total Bookings -->
                    <div class="stat-card" style="border-left: 4px solid #3498db;">
                        <div class="stat-icon">📅</div>
                        <div class="stat-info">
                            <h3><?php echo $my_bookings; ?></h3>
                            <p>Total Bookings</p>
                        </div>
                    </div>

                    <!-- Active Bookings -->
                    <div class="stat-card" style="border-left: 4px solid #27ae60;">
                        <div class="stat-icon">✅</div>
                        <div class="stat-info">
                            <h3><?php echo $active_bookings; ?></h3>
                            <p>Active Bookings</p>
                        </div>
                    </div>

                    <!-- Total Complaints -->
                    <div class="stat-card" style="border-left: 4px solid #f39c12;">
                        <div class="stat-icon">📝</div>
                        <div class="stat-info">
                            <h3><?php echo $my_complaints; ?></h3>
                            <p>My Complaints</p>
                        </div>
                    </div>

                    <!-- Pending Complaints -->
                    <div class="stat-card" style="border-left: 4px solid #e74c3c;">
                        <div class="stat-icon">⏳</div>
                        <div class="stat-info">
                            <h3><?php echo $pending_complaints; ?></h3>
                            <p>Pending Complaints</p>
                        </div>
                    </div>

                    <!-- Payments Made -->
                    <div class="stat-card" style="border-left: 4px solid #9b59b6;">
                        <div class="stat-icon">💳</div>
                        <div class="stat-info">
                            <h3><?php echo $my_payments; ?></h3>
                            <p>Payments Made</p>
                        </div>
                    </div>
                </div>

                <!-- Recent Bookings -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>My Recent Bookings</h2>
                        <a href="bookings.php" class="btn btn-primary btn-small">View All</a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Room</th>
                                <th>Check-in Date</th>
                                <th>Price</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($recent_bookings) > 0): ?>
                                <?php while ($booking = mysqli_fetch_assoc($recent_bookings)): ?>
                                    <tr>
                                        <td>#<?php echo $booking['booking_id']; ?></td>
                                        <td>
                                            <strong><?php echo $booking['room_number']; ?></strong><br>
                                            <small><?php echo ucfirst($booking['room_type']); ?></small>
                                        </td>
                                        <td><?php echo formatDate($booking['check_in_date']); ?></td>
                                        <td>৳<?php echo number_format($booking['price'], 2); ?></td>
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
                                    <td colspan="5" style="text-align: center;">
                                        No bookings yet. <a href="rooms.php">Book a room now</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Recent Complaints -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>My Recent Complaints</h2>
                        <a href="complaints.php" class="btn btn-primary btn-small">View All</a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
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
                                        <td><?php echo ucfirst($complaint['complaint_type']); ?></td>
                                        <td><?php echo htmlspecialchars($complaint['subject']); ?></td>
                                        <td>
                                            <?php
                                            $badge_class = '';
                                            switch ($complaint['status']) {
                                                case 'resolved': $badge_class = 'badge-success'; break;
                                                case 'pending': $badge_class = 'badge-warning'; break;
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
                                    <td colspan="5" style="text-align: center;">
                                        No complaints yet. <a href="complaints.php">Submit a complaint</a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Upcoming Events -->
                <div class="card">
                    <div class="card-header">
                        <h3>📢 Upcoming Events</h3>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($upcoming_events) > 0): ?>
                            <?php while ($event = mysqli_fetch_assoc($upcoming_events)): ?>
                                <div style="border-left: 3px solid #3498db; padding-left: 15px; margin-bottom: 15px;">
                                    <h4 style="margin-bottom: 5px;"><?php echo htmlspecialchars($event['title']); ?></h4>
                                    <p style="color: #7f8c8d; font-size: 0.9rem; margin-bottom: 5px;">
                                        📅 <?php echo formatDate($event['event_date']); ?> | 
                                        🕐 <?php echo date('h:i A', strtotime($event['event_time'])); ?> | 
                                        📍 <?php echo htmlspecialchars($event['location']); ?>
                                    </p>
                                    <p style="font-size: 0.95rem;"><?php echo htmlspecialchars($event['description']); ?></p>
                                </div>
                            <?php endwhile; ?>
                            <a href="events.php" class="btn btn-primary btn-small">View All Events</a>
                        <?php else: ?>
                            <p style="text-align: center; color: #7f8c8d;">No upcoming events</p>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
