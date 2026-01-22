<?php
<<<<<<< HEAD
// =============================================
// Manager-Bookings Management
// =============================================
=======
>>>>>>> 6b93f3740aca234ffe1e3c00ada0407a3935908e

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is manager
requireRole('manager');

// Handle booking confirmation
if (isset($_GET['confirm']) && isset($_GET['id'])) {
    $booking_id = intval($_GET['id']);
    
    $sql = "UPDATE bookings SET status = 'approved' WHERE booking_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $booking_id);
    
    if (mysqli_stmt_execute($stmt)) {
        setSuccessMessage('Booking confirmed successfully');
    } else {
        setErrorMessage('Failed to confirm booking');
    }
    
    header('Location: bookings.php');
    exit();
}

// Get all bookings part
$sql = "SELECT b.*, u.full_name, u.email, u.phone, r.room_number, r.room_type, r.price 
        FROM bookings b 
        JOIN users u ON b.student_id = u.user_id 
        JOIN rooms r ON b.room_id = r.room_id 
        ORDER BY b.created_at DESC";
$bookings = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <!-- Navigation Bar -->
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
                    <li><a href="bookings.php" class="active">📅 Bookings</a></li>
                    <li><a href="complaints.php">📝 Complaints</a></li>
                    <li><a href="meals.php">🍽️ Meals</a></li>
                    <li><a href="profile.php">👤 Profile</a></li>
                </ul>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Page Header -->
                <div class="dashboard-header">
                    <h1>📅 Bookings Management</h1>
                    <p>View and confirm room bookings</p>
                </div>

                <!-- Display messages -->
                <?php echo displayMessages(); ?>

                <!-- Bookings Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>All Bookings (<?php echo mysqli_num_rows($bookings); ?>)</h2>
                        <input type="text" id="searchInput" placeholder="🔍 Search bookings..." 
                               style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <table id="bookingsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Student</th>
                                <th>Contact</th>
                                <th>Room</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($bookings) > 0): ?>
                                <?php while ($booking = mysqli_fetch_assoc($bookings)): ?>
                                    <tr>
                                        <td>#<?php echo $booking['booking_id']; ?></td>
                                        <td><?php echo htmlspecialchars($booking['full_name']); ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($booking['email']); ?><br>
                                            <small><?php echo htmlspecialchars($booking['phone']); ?></small>
                                        </td>
                                        <td>
                                            <strong><?php echo $booking['room_number']; ?></strong><br>
                                            <small><?php echo ucfirst($booking['room_type']); ?></small>
                                        </td>
                                        <td><?php echo formatDate($booking['check_in_date']); ?></td>
                                        <td><?php echo $booking['check_out_date'] ? formatDate($booking['check_out_date']) : 'N/A'; ?></td>
                                        <td>
                                            <?php
                                            $badge_class = '';
                                            switch ($booking['status']) {
                                                case 'approved': $badge_class = 'badge-success'; break;
                                                case 'pending': $badge_class = 'badge-warning'; break;
                                                case 'rejected': $badge_class = 'badge-danger'; break;
                                                case 'completed': $badge_class = 'badge-info'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($booking['status']); ?></span>
                                        </td>
                                        <td>
                                            <?php if ($booking['status'] === 'pending' || $booking['status'] === 'approved'): ?>
                                                <a href="?confirm=1&id=<?php echo $booking['booking_id']; ?>" 
                                                   class="btn btn-success btn-small"
                                                   onclick="return confirm('Confirm this booking?')">Confirm</a>
                                            <?php else: ?>
                                                <span style="color: #95a5a6;">No action</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No bookings found</td>
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
        filterTable('searchInput', 'bookingsTable');
    </script>
</body>
</html>
