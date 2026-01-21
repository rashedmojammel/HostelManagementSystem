<?php
// =============================================
// Student - View Available Rooms
// =============================================

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is student
requireRole('student');

// Get all available rooms
$sql = "SELECT * FROM rooms WHERE status = 'available' ORDER BY room_number ASC";
$rooms = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Rooms - <?php echo SITE_NAME; ?></title>
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
                    <li><a href="rooms.php" class="active">🏠 Available Rooms</a></li>
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
                <!-- Page Header -->
                <div class="dashboard-header">
                    <h1>🏠 Available Rooms</h1>
                    <p>Browse and book available hostel rooms</p>
                </div>

                <!-- Display messages -->
                <?php echo displayMessages(); ?>

                <!-- Rooms Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem;">
                    <?php if (mysqli_num_rows($rooms) > 0): ?>
                        <?php while ($room = mysqli_fetch_assoc($rooms)): ?>
                            <div class="card">
                                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem;">
                                    <h3 style="margin: 0; font-size: 1.8rem;">Room <?php echo htmlspecialchars($room['room_number']); ?></h3>
                                    <p style="margin: 0; opacity: 0.9;"><?php echo ucfirst($room['room_type']); ?> Room</p>
                                </div>
                                <div class="card-body">
                                    <div style="margin-bottom: 1rem;">
                                        <p style="margin-bottom: 0.5rem;">
                                            <strong>👥 Capacity:</strong> <?php echo $room['capacity']; ?> Person(s)
                                        </p>
                                        <p style="margin-bottom: 0.5rem;">
                                            <strong>🏢 Floor:</strong> Floor <?php echo $room['floor_number']; ?>
                                        </p>
                                        <p style="margin-bottom: 0.5rem;">
                                            <strong>💰 Price:</strong> 
                                            <span style="font-size: 1.5rem; color: #27ae60; font-weight: bold;">
                                                ৳<?php echo number_format($room['price'], 2); ?>
                                            </span>
                                            <span style="font-size: 0.9rem; color: #7f8c8d;">/month</span>
                                        </p>
                                        <p style="margin-bottom: 0.5rem;">
                                            <strong>📝 Description:</strong><br>
                                            <?php echo htmlspecialchars($room['description']); ?>
                                        </p>
                                    </div>
                                    <a href="book_room.php?room_id=<?php echo $room['room_id']; ?>" 
                                       class="btn btn-success" style="width: 100%;">
                                        📅 Book This Room
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="card" style="grid-column: 1/-1;">
                            <div class="card-body" style="text-align: center; padding: 3rem;">
                                <h3>😔 No Available Rooms</h3>
                                <p>All rooms are currently occupied. Please check back later.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
