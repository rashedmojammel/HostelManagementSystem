<?php


require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
requireRole('admin');

// Get all payments with booking and student details
$sql = "SELECT p.*, b.booking_id, b.check_in_date, u.full_name, u.email, r.room_number, r.room_type 
        FROM payments p 
        JOIN bookings b ON p.booking_id = b.booking_id 
        JOIN users u ON p.student_id = u.user_id 
        JOIN rooms r ON b.room_id = r.room_id 
        ORDER BY p.payment_date DESC";
$payments = mysqli_query($conn, $sql);

// Calculate total revenue
$sql_revenue = "SELECT SUM(amount) as total_revenue FROM payments WHERE status = 'paid'";
$result_revenue = mysqli_query($conn, $sql_revenue);
$revenue_data = mysqli_fetch_assoc($result_revenue);
$total_revenue = $revenue_data['total_revenue'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments Management - <?php echo SITE_NAME; ?></title>
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
                    <li><a href="payments.php" class="active">💳 Payments</a></li>
                    <li><a href="complaints.php">📝 Complaints</a></li>
                    <li><a href="events.php">📢 Events</a></li>
                    <li><a href="settings.php">⚙️ Settings</a></li>
                </ul>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Page Header -->
                <div class="dashboard-header">
                    <h1>💳 Payments Management</h1>
                    <p>View all payment transactions and revenue</p>
                </div>

                <!-- Display messages -->
                <?php echo displayMessages(); ?>

                <!-- Revenue Card -->
                <div class="stat-card" style="border-left: 4px solid #27ae60; margin-bottom: 2rem;">
                    <div class="stat-icon">💰</div>
                    <div class="stat-info">
                        <h3>৳<?php echo number_format($total_revenue, 2); ?></h3>
                        <p>Total Revenue (Paid)</p>
                    </div>
                </div>

                <!-- Payments Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>All Payments (<?php echo mysqli_num_rows($payments); ?>)</h2>
                        <input type="text" id="searchInput" placeholder="🔍 Search payments..." 
                               style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <table id="paymentsTable">
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Booking ID</th>
                                <th>Student</th>
                                <th>Room</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Transaction ID</th>
                                <th>Status</th>
                                <th>Payment Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($payments) > 0): ?>
                                <?php while ($payment = mysqli_fetch_assoc($payments)): ?>
                                    <tr>
                                        <td>#<?php echo $payment['payment_id']; ?></td>
                                        <td>#<?php echo $payment['booking_id']; ?></td>
                                        <td>
                                            <?php echo htmlspecialchars($payment['full_name']); ?><br>
                                            <small><?php echo htmlspecialchars($payment['email']); ?></small>
                                        </td>
                                        <td>
                                            <strong><?php echo $payment['room_number']; ?></strong><br>
                                            <small><?php echo ucfirst($payment['room_type']); ?></small>
                                        </td>
                                        <td><strong>৳<?php echo number_format($payment['amount'], 2); ?></strong></td>
                                        <td><?php echo htmlspecialchars($payment['payment_method'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($payment['transaction_id'] ?? 'N/A'); ?></td>
                                        <td>
                                            <?php
                                            $badge_class = '';
                                            switch ($payment['status']) {
                                                case 'paid': $badge_class = 'badge-success'; break;
                                                case 'pending': $badge_class = 'badge-warning'; break;
                                                case 'failed': $badge_class = 'badge-danger'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($payment['status']); ?></span>
                                        </td>
                                        <td><?php echo formatDateTime($payment['payment_date']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center;">No payments found</td>
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
        filterTable('searchInput', 'paymentsTable');
    </script>
</body>
</html>
