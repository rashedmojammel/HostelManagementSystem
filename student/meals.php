<?php

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is student
requireRole('student');

// Get today's date
$today = date('Y-m-d');

// Get this week's meals
$week_start = date('Y-m-d', strtotime('monday this week'));
$week_end = date('Y-m-d', strtotime('sunday this week'));

$sql = "SELECT * FROM meals 
        WHERE meal_date BETWEEN ? AND ? 
        ORDER BY meal_date DESC, 
        FIELD(meal_type, 'breakfast', 'lunch', 'dinner')";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ss", $week_start, $week_end);
mysqli_stmt_execute($stmt);
$weekly_meals = mysqli_stmt_get_result($stmt);

// Get today's meals
$sql = "SELECT * FROM meals WHERE meal_date = ? ORDER BY FIELD(meal_type, 'breakfast', 'lunch', 'dinner')";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $today);
mysqli_stmt_execute($stmt);
$today_meals = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meal Schedule - <?php echo SITE_NAME; ?></title>
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
                    <li><a href="payments.php">💳 Payments</a></li>
                    <li><a href="complaints.php">📝 Complaints</a></li>
                    <li><a href="meals.php" class="active">🍽️ Meal Schedule</a></li>
                    <li><a href="events.php">📢 Events</a></li>
                    <li><a href="profile.php">👤 Profile</a></li>
                </ul>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Page Header -->
                <div class="dashboard-header">
                    <h1>🍽️ Meal Schedule</h1>
                    <p>View daily meal menu</p>
                </div>

                <!-- Display messages -->
                <?php echo displayMessages(); ?>

                <!-- Today's Meals -->
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <h3 style="margin: 0;">📅 Today's Menu - <?php echo formatDate($today); ?></h3>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($today_meals) > 0): ?>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                                <?php while ($meal = mysqli_fetch_assoc($today_meals)): ?>
                                    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; border-left: 4px solid #3498db;">
                                        <h4 style="margin-bottom: 0.5rem; color: #2c3e50;">
                                            <?php 
                                            $icons = [
                                                'breakfast' => '🌅',
                                                'lunch' => '☀️',
                                                'dinner' => '🌙'
                                            ];
                                            echo $icons[$meal['meal_type']] . ' ' . ucfirst($meal['meal_type']); 
                                            ?>
                                        </h4>
                                        <p style="margin: 0; font-size: 1.1rem; color: #555;">
                                            <?php echo htmlspecialchars($meal['menu_items']); ?>
                                        </p>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 2rem;">
                                <h3 style="color: #7f8c8d;">🍽️ No menu available for today</h3>
                                <p>The meal schedule for today hasn't been uploaded yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- This Week's Meals -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>📆 This Week's Schedule</h2>
                        <span style="color: #7f8c8d;">
                            <?php echo date('M d', strtotime($week_start)); ?> - <?php echo date('M d, Y', strtotime($week_end)); ?>
                        </span>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Meal Type</th>
                                <th>Menu Items</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($weekly_meals) > 0): ?>
                                <?php while ($meal = mysqli_fetch_assoc($weekly_meals)): ?>
                                    <tr>
                                        <td><?php echo formatDate($meal['meal_date']); ?></td>
                                        <td><?php echo date('l', strtotime($meal['meal_date'])); ?></td>
                                        <td>
                                            <?php
                                            $badge_colors = [
                                                'breakfast' => 'badge-warning',
                                                'lunch' => 'badge-success',
                                                'dinner' => 'badge-info'
                                            ];
                                            $icons = [
                                                'breakfast' => '🌅',
                                                'lunch' => '☀️',
                                                'dinner' => '🌙'
                                            ];
                                            ?>
                                            <span class="badge <?php echo $badge_colors[$meal['meal_type']]; ?>">
                                                <?php echo $icons[$meal['meal_type']] . ' ' . ucfirst($meal['meal_type']); ?>
                                            </span>
                                        </td>
                                        <td style="font-size: 1.05rem;"><?php echo htmlspecialchars($meal['menu_items']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 3rem;">
                                        <h3>📭 No Meals Scheduled</h3>
                                        <p>Meal schedule for this week hasn't been uploaded yet.</p>
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
