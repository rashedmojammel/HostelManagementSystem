<?php
// =============================================
// Student - My Payments
// =============================================

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is student
requireRole('student');

$student_id = $_SESSION['user_id'];

// Handle payment submission
if (isset($_POST['make_payment'])) {
    $payment_id = intval($_POST['payment_id']);
    $payment_method = sanitizeInput($_POST['payment_method']);
    $transaction_id = sanitizeInput($_POST['transaction_id']);
    
    // Update payment status
    $sql = "UPDATE payments SET status = 'paid', payment_method = ?, transaction_id = ?, payment_date = NOW() 
            WHERE payment_id = ? AND student_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssii", $payment_method, $transaction_id, $payment_id, $student_id);
    
    if (mysqli_stmt_execute($stmt)) {
        setSuccessMessage('Payment submitted successfully!');
    } else {
        setErrorMessage('Failed to submit payment');
    }
    
    header('Location: payments.php');
    exit();
}

// Get all payments for this student
$sql = "SELECT p.*, b.booking_id, b.check_in_date, r.room_number, r.room_type 
        FROM payments p 
        JOIN bookings b ON p.booking_id = b.booking_id 
        JOIN rooms r ON b.room_id = r.room_id 
        WHERE p.student_id = ? 
        ORDER BY p.payment_date DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$payments = mysqli_stmt_get_result($stmt);

// Calculate totals
$total_paid = 0;
$total_pending = 0;
mysqli_data_seek($payments, 0);
while ($p = mysqli_fetch_assoc($payments)) {
    if ($p['status'] == 'paid') {
        $total_paid += $p['amount'];
    } else {
        $total_pending += $p['amount'];
    }
}
mysqli_data_seek($payments, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Payments - <?php echo SITE_NAME; ?></title>
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
                    <li><a href="payments.php" class="active">💳 Payments</a></li>
                    <li><a href="complaints.php">📝 Complaints</a></li>
                    <li><a href="meals.php">🍽️ Meal Schedule</a></li>
                    <li><a href="events.php">📢 Events</a></li>
                    <li><a href="profile.php">👤 Profile</a></li>
                </ul>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Page Header -->
                <div class="dashboard-header">
                    <h1>💳 My Payments</h1>
                    <p>View and manage your payment history</p>
                </div>

                <!-- Display messages -->
                <?php echo displayMessages(); ?>

                <!-- Payment Summary -->
                <div class="dashboard-stats">
                    <div class="stat-card" style="border-left: 4px solid #27ae60;">
                        <div class="stat-icon">✅</div>
                        <div class="stat-info">
                            <h3>৳<?php echo number_format($total_paid, 2); ?></h3>
                            <p>Total Paid</p>
                        </div>
                    </div>

                    <div class="stat-card" style="border-left: 4px solid #f39c12;">
                        <div class="stat-icon">⏳</div>
                        <div class="stat-info">
                            <h3>৳<?php echo number_format($total_pending, 2); ?></h3>
                            <p>Pending Payments</p>
                        </div>
                    </div>
                </div>

                <!-- Payments Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>Payment History (<?php echo mysqli_num_rows($payments); ?>)</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Payment ID</th>
                                <th>Booking ID</th>
                                <th>Room</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Transaction ID</th>
                                <th>Status</th>
                                <th>Payment Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($payments) > 0): ?>
                                <?php while ($payment = mysqli_fetch_assoc($payments)): ?>
                                    <tr>
                                        <td><strong>#<?php echo $payment['payment_id']; ?></strong></td>
                                        <td>#<?php echo $payment['booking_id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($payment['room_number']); ?></strong><br>
                                            <small><?php echo ucfirst($payment['room_type']); ?></small>
                                        </td>
                                        <td><strong>৳<?php echo number_format($payment['amount'], 2); ?></strong></td>
                                        <td><?php echo $payment['payment_method'] ? htmlspecialchars($payment['payment_method']) : 'N/A'; ?></td>
                                        <td><?php echo $payment['transaction_id'] ? htmlspecialchars($payment['transaction_id']) : 'N/A'; ?></td>
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
                                        <td>
                                            <?php if ($payment['status'] === 'pending'): ?>
                                                <button onclick="openPaymentModal(<?php echo $payment['payment_id']; ?>, <?php echo $payment['amount']; ?>)" 
                                                        class="btn btn-success btn-small">
                                                    Pay Now
                                                </button>
                                            <?php else: ?>
                                                <span style="color: #95a5a6;">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 3rem;">
                                        <h3>💳 No Payments Yet</h3>
                                        <p>You don't have any payment records.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>

    <!-- Payment Modal -->
    <div id="paymentModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background: white; padding: 2rem; border-radius: 10px; max-width: 500px; width: 90%;">
            <h2>💳 Make Payment</h2>
            <form method="POST" action="">
                <input type="hidden" name="payment_id" id="modal_payment_id">
                
                <div class="form-group">
                    <label>Amount to Pay</label>
                    <input type="text" id="modal_amount" readonly style="font-size: 1.3rem; font-weight: bold; color: #27ae60;">
                </div>

                <div class="form-group">
                    <label for="payment_method">Payment Method *</label>
                    <select id="payment_method" name="payment_method" required>
                        <option value="">Select Method</option>
                        <option value="bKash">bKash</option>
                        <option value="Nagad">Nagad</option>
                        <option value="Rocket">Rocket</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Cash">Cash</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="transaction_id">Transaction ID / Reference Number *</label>
                    <input type="text" id="transaction_id" name="transaction_id" required
                           placeholder="Enter transaction ID or reference number">
                </div>

                <div class="form-actions">
                    <button type="submit" name="make_payment" class="btn btn-success">Submit Payment</button>
                    <button type="button" onclick="closePaymentModal()" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
    <script>
        function openPaymentModal(paymentId, amount) {
            document.getElementById('modal_payment_id').value = paymentId;
            document.getElementById('modal_amount').value = '৳' + amount.toFixed(2);
            document.getElementById('paymentModal').style.display = 'flex';
        }

        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
        }
    </script>
</body>
</html>
