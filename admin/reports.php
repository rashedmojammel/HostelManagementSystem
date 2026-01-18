<?php
// =============================================
// Admin - Analytics & Reports Dashboard
// =============================================

require_once '../includes/config.php';
require_once '../includes/functions.php';
requireRole('admin');

// Get date range (default: last 30 days)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Revenue Analytics
$revenue_sql = "SELECT 
    DATE(payment_date) as date,
    SUM(amount) as daily_revenue,
    COUNT(*) as transaction_count
    FROM payments 
    WHERE status = 'paid' 
    AND payment_date BETWEEN ? AND ?
    GROUP BY DATE(payment_date)
    ORDER BY date ASC";
$stmt = mysqli_prepare($conn, $revenue_sql);
mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt);
$revenue_data = mysqli_stmt_get_result($stmt);

// Occupancy Rate
$occupancy_sql = "SELECT 
    (SELECT COUNT(*) FROM rooms WHERE status = 'occupied') as occupied,
    (SELECT COUNT(*) FROM rooms) as total";
$occupancy = mysqli_fetch_assoc(mysqli_query($conn, $occupancy_sql));
$occupancy_rate = ($occupancy['total'] > 0) ? 
    round(($occupancy['occupied'] / $occupancy['total']) * 100, 2) : 0;

// Booking Status Distribution
$booking_stats = mysqli_query($conn, "
    SELECT status, COUNT(*) as count 
    FROM bookings 
    WHERE created_at BETWEEN '$start_date' AND '$end_date'
    GROUP BY status
");

// Complaint Resolution Rate
$complaint_stats = mysqli_query($conn, "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved
    FROM complaints
    WHERE created_at BETWEEN '$start_date' AND '$end_date'
");
$complaints = mysqli_fetch_assoc($complaint_stats);
$resolution_rate = ($complaints['total'] > 0) ? 
    round(($complaints['resolved'] / $complaints['total']) * 100, 2) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics & Reports - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <!-- Navigation (copy from dashboard.php) -->
    
    <div class="container">
        <div class="dashboard-layout">
            <!-- Sidebar (copy from dashboard.php) -->
            
            <main class="main-content">
                <div class="dashboard-header">
                    <h1>📊 Analytics & Reports</h1>
                    <p>Comprehensive insights and performance metrics</p>
                </div>

                <!-- Date Range Filter -->
                <div class="card">
                    <form method="GET" action="">
                        <div style="display: flex; gap: 1rem; align-items: end;">
                            <div class="form-group" style="margin: 0;">
                                <label>Start Date</label>
                                <input type="date" name="start_date" value="<?php echo $start_date; ?>">
                            </div>
                            <div class="form-group" style="margin: 0;">
                                <label>End Date</label>
                                <input type="date" name="end_date" value="<?php echo $end_date; ?>">
                            </div>
                            <button type="submit" class="btn btn-primary">Generate Report</button>
                            <a href="export_report.php?start=<?php echo $start_date; ?>&end=<?php echo $end_date; ?>" 
                               class="btn btn-success">📥 Export CSV</a>
                        </div>
                    </form>
                </div>

                <!-- KPI Cards -->
                <div class="dashboard-stats">
                    <div class="stat-card" style="border-left-color: var(--primary-color);">
                        <div class="stat-icon">🏠</div>
                        <div class="stat-info">
                            <h3><?php echo $occupancy_rate; ?>%</h3>
                            <p>Occupancy Rate</p>
                        </div>
                    </div>
                    <div class="stat-card" style="border-left-color: var(--success-color);">
                        <div class="stat-icon">✅</div>
                        <div class="stat-info">
                            <h3><?php echo $resolution_rate; ?>%</h3>
                            <p>Complaint Resolution</p>
                        </div>
                    </div>
                </div>

                <!-- Revenue Chart -->
                <div class="card">
                    <div class="card-header">
                        <h3>💰 Revenue Trend</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="revenueChart" height="80"></canvas>
                    </div>
                </div>

                <!-- Booking Status Pie Chart -->
                <div class="card">
                    <div class="card-header">
                        <h3>📊 Booking Status Distribution</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="bookingChart" height="80"></canvas>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        // Revenue Chart
        const revenueData = {
            labels: [<?php 
                mysqli_data_seek($revenue_data, 0);
                while($r = mysqli_fetch_assoc($revenue_data)) {
                    echo "'" . date('M d', strtotime($r['date'])) . "',";
                }
            ?>],
            datasets: [{
                label: 'Daily Revenue',
                data: [<?php 
                    mysqli_data_seek($revenue_data, 0);
                    while($r = mysqli_fetch_assoc($revenue_data)) {
                        echo $r['daily_revenue'] . ",";
                    }
                ?>],
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
                tension: 0.4
            }]
        };
        new Chart(document.getElementById('revenueChart'), {
            type: 'line',
            data: revenueData,
            options: { responsive: true, maintainAspectRatio: true }
        });

        // Booking Status Chart
        const bookingData = {
            labels: [<?php 
                while($b = mysqli_fetch_assoc($booking_stats)) {
                    echo "'" . ucfirst($b['status']) . "',";
                }
            ?>],
            datasets: [{
                data: [<?php 
                    mysqli_data_seek($booking_stats, 0);
                    while($b = mysqli_fetch_assoc($booking_stats)) {
                        echo $b['count'] . ",";
                    }
                ?>],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444', '#3b82f6']
            }]
        };
        new Chart(document.getElementById('bookingChart'), {
            type: 'doughnut',
            data: bookingData
        });
    </script>
</body>
</html>
