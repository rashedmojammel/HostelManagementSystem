<?php

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is student
requireRole('student');

// Get upcoming events
$sql = "SELECT e.*, u.full_name as creator_name 
        FROM events e 
        JOIN users u ON e.created_by = u.user_id 
        WHERE e.event_date >= CURDATE() 
        ORDER BY e.event_date ASC, e.event_time ASC";
$upcoming_events = mysqli_query($conn, $sql);

// Get past events
$sql = "SELECT e.*, u.full_name as creator_name 
        FROM events e 
        JOIN users u ON e.created_by = u.user_id 
        WHERE e.event_date < CURDATE() 
        ORDER BY e.event_date DESC, e.event_time DESC 
        LIMIT 10";
$past_events = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - <?php echo SITE_NAME; ?></title>
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
                    <li><a href="meals.php">🍽️ Meal Schedule</a></li>
                    <li><a href="events.php" class="active">📢 Events</a></li>
                    <li><a href="profile.php">👤 Profile</a></li>
                </ul>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Page Header -->
                <div class="dashboard-header">
                    <h1>📢 Hostel Events</h1>
                    <p>Stay updated with upcoming hostel events and activities</p>
                </div>

                <!-- Display messages -->
                <?php echo displayMessages(); ?>

                <!-- Upcoming Events -->
                <div class="card">
                    <div class="card-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                        <h3 style="margin: 0;">🎉 Upcoming Events</h3>
                    </div>
                    <div class="card-body">
                        <?php if (mysqli_num_rows($upcoming_events) > 0): ?>
                            <div style="display: grid; gap: 1.5rem;">
                                <?php while ($event = mysqli_fetch_assoc($upcoming_events)): ?>
                                    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; border-left: 4px solid #3498db;">
                                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                            <h3 style="margin: 0; color: #2c3e50;">
                                                <?php echo htmlspecialchars($event['title']); ?>
                                            </h3>
                                            <span class="badge badge-success">Upcoming</span>
                                        </div>
                                        
                                        <div style="display: flex; flex-wrap: wrap; gap: 1rem; margin: 1rem 0; color: #7f8c8d; font-size: 0.95rem;">
                                            <span>📅 <?php echo formatDate($event['event_date']); ?></span>
                                            <span>🕐 <?php echo date('h:i A', strtotime($event['event_time'])); ?></span>
                                            <span>📍 <?php echo htmlspecialchars($event['location']); ?></span>
                                        </div>
                                        
                                        <p style="margin: 0; color: #555; line-height: 1.6;">
                                            <?php echo htmlspecialchars($event['description']); ?>
                                        </p>
                                        
                                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #ddd; font-size: 0.9rem; color: #7f8c8d;">
                                            <span>Organized by: <?php echo htmlspecialchars($event['creator_name']); ?></span>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; padding: 3rem;">
                                <h3 style="color: #7f8c8d;">📭 No Upcoming Events</h3>
                                <p>There are no upcoming events scheduled at the moment.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Past Events -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>📚 Past Events</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Event Title</th>
                                <th>Date & Time</th>
                                <th>Location</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($past_events) > 0): ?>
                                <?php while ($event = mysqli_fetch_assoc($past_events)): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($event['title']); ?></strong></td>
                                        <td>
                                            <?php echo formatDate($event['event_date']); ?><br>
                                            <small><?php echo date('h:i A', strtotime($event['event_time'])); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($event['location']); ?></td>
                                        <td style="max-width: 400px;"><?php echo htmlspecialchars($event['description']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 2rem;">
                                        <p style="color: #7f8c8d;">No past events</p>
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
