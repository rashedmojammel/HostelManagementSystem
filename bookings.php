<?php
// =============================================
// Student - My Bookings
// =============================================

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is student
requireRole('student');

$student_id = $_SESSION['user_id'];

// Get all bookings for this student
$sql = "SELECT b.*, r.room_number, r.room_type, r.price, r.floor_number 
        FROM bookings b 
        JOIN rooms r ON b.room_id = r.room_id 
        WHERE b.student_id = ? 
        ORDER BY b.created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$bookings = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - <?php echo SITE_NAME; ?></title>
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
                    <li><a href="bookings.php" class="active">📅 My Bookings</a></li>
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
                <!-- Page Header -->
                <div class="dashboard-header">
                    <h1>📅 My Bookings</h1>
                    <p>View all your room bookings</p>
                </div>

                <!-- Display messages -->
                <?php echo displayMessages(); ?>

                <!-- Bookings Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>All My Bookings (<?php echo mysqli_num_rows($bookings); ?>)</h2>
                        <a href="rooms.php" class="btn btn-primary btn-small">+ Book New Room</a>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Room Details</th>
                                <th>Booking Date</th>
                                <th>Check-in Date</th>
                                <th>Check-out Date</th>
                                <th>Monthly Rent</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($bookings) > 0): ?>
                                <?php while ($booking = mysqli_fetch_assoc($bookings)): ?>
                                    <tr>
                                        <td><strong>#<?php echo $booking['booking_id']; ?></strong></td>
                                        <td>
                                            <strong>Room <?php echo htmlspecialchars($booking['room_number']); ?></strong><br>
                                            <small>
                                                <?php echo ucfirst($booking['room_type']); ?> | 
                                                Floor <?php echo $booking['floor_number']; ?>
                                            </small>
                                        </td>
                                        <td><?php echo formatDate($booking['booking_date']); ?></td>
                                        <td><?php echo formatDate($booking['check_in_date']); ?></td>
                                        <td>
                                            <?php echo $booking['check_out_date'] ? formatDate($booking['check_out_date']) : 'N/A'; ?>
                                        </td>
                                        <td><strong>৳<?php echo number_format($booking['price'], 2); ?></strong></td>
                                        <td>
                                            <?php
                                            $badge_class = '';
                                            $status_text = '';
                                            switch ($booking['status']) {
                                                case 'approved': 
                                                    $badge_class = 'badge-success'; 
                                                    $status_text = 'Approved';
                                                    break;
                                                case 'pending': 
                                                    $badge_class = 'badge-warning'; 
                                                    $status_text = 'Pending';
                                                    break;
                                                case 'rejected': 
                                                    $badge_class = 'badge-danger'; 
                                                    $status_text = 'Rejected';
                                                    break;
                                                case 'completed': 
                                                    $badge_class = 'badge-info'; 
                                                    $status_text = 'Completed';
                                                    break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo $status_text; ?></span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 3rem;">
                                        <h3>📭 No Bookings Yet</h3>
                                        <p>You haven't made any room bookings.</p>
                                        <a href="rooms.php" class="btn btn-primary">Browse Available Rooms</a>
                                    </td>
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
