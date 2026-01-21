<?php

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is manager
requireRole('manager');

// Handle room status update
if (isset($_POST['update_status'])) {
    $room_id = intval($_POST['room_id']);
    $status = sanitizeInput($_POST['status']);
    
    $sql = "UPDATE rooms SET status = ? WHERE room_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $status, $room_id);
    
    if (mysqli_stmt_execute($stmt)) {
        setSuccessMessage('Room status updated successfully');
    } else {
        setErrorMessage('Failed to update room status');
    }
    
    header('Location: rooms.php');
    exit();
}

// Get all rooms here
$sql = "SELECT * FROM rooms ORDER BY room_number ASC";
$rooms = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <!-- Navigation bar -->
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
                    <li><a href="rooms.php" class="active">🏠 Rooms</a></li>
                    <li><a href="bookings.php">📅 Bookings</a></li>
                    <li><a href="complaints.php">📝 Complaints</a></li>
                    <li><a href="meals.php">🍽️ Meals</a></li>
                    <li><a href="profile.php">👤 Profile</a></li>
                </ul>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Page Header -->
                <div class="dashboard-header">
                    <h1>🏠 Rooms Management</h1>
                    <p>View and update room status</p>
                </div>

                <!-- Display messages -->
                <?php echo displayMessages(); ?>

                <!-- Rooms Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>All Rooms (<?php echo mysqli_num_rows($rooms); ?>)</h2>
                        <input type="text" id="searchInput" placeholder="🔍 Search rooms..." 
                               style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <table id="roomsTable">
                        <thead>
                            <tr>
                                <th>Room No.</th>
                                <th>Type</th>
                                <th>Capacity</th>
                                <th>Price</th>
                                <th>Floor</th>
                                <th>Status</th>
                                <th>Description</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($rooms) > 0): ?>
                                <?php while ($room = mysqli_fetch_assoc($rooms)): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($room['room_number']); ?></strong></td>
                                        <td><?php echo ucfirst($room['room_type']); ?></td>
                                        <td><?php echo $room['capacity']; ?> Person(s)</td>
                                        <td>৳<?php echo number_format($room['price'], 2); ?></td>
                                        <td>Floor <?php echo $room['floor_number']; ?></td>
                                        <td>
                                            <?php
                                            $badge_class = '';
                                            switch ($room['status']) {
                                                case 'available': $badge_class = 'badge-success'; break;
                                                case 'occupied': $badge_class = 'badge-danger'; break;
                                                case 'maintenance': $badge_class = 'badge-warning'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($room['status']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($room['description']); ?></td>
                                        <td>
                                            <!-- Update Status Form -->
                                            <form method="POST" action="" style="display: inline-block;">
                                                <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
                                                <select name="status" required style="padding: 0.3rem; font-size: 0.85rem; border: 1px solid #ddd; border-radius: 3px;">
                                                    <option value="available" <?php echo $room['status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                                                    <option value="occupied" <?php echo $room['status'] == 'occupied' ? 'selected' : ''; ?>>Occupied</option>
                                                    <option value="maintenance" <?php echo $room['status'] == 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                                </select>
                                                <button type="submit" name="update_status" class="btn btn-primary btn-small">Update</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center;">No rooms found</td>
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
        filterTable('searchInput', 'roomsTable');
    </script>
</body>
</html>
