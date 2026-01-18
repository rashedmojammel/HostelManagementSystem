<?php
// =============================================
// Admin - Rooms Management
// =============================================

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
requireRole('admin');

// Handle room addition
if (isset($_POST['add_room'])) {
    $room_number = sanitizeInput($_POST['room_number']);
    $room_type = sanitizeInput($_POST['room_type']);
    $capacity = intval($_POST['capacity']);
    $price = floatval($_POST['price']);
    $floor_number = intval($_POST['floor_number']);
    $status = sanitizeInput($_POST['status']);
    $description = sanitizeInput($_POST['description']);
    
    // Insert room
    $sql = "INSERT INTO rooms (room_number, room_type, capacity, price, floor_number, status, description) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssidiss", $room_number, $room_type, $capacity, $price, $floor_number, $status, $description);
    
    if (mysqli_stmt_execute($stmt)) {
        setSuccessMessage('Room added successfully');
    } else {
        setErrorMessage('Failed to add room');
    }
    
    header('Location: rooms.php');
    exit();
}

// Handle room update
if (isset($_POST['update_room'])) {
    $room_id = intval($_POST['room_id']);
    $room_number = sanitizeInput($_POST['room_number']);
    $room_type = sanitizeInput($_POST['room_type']);
    $capacity = intval($_POST['capacity']);
    $price = floatval($_POST['price']);
    $floor_number = intval($_POST['floor_number']);
    $status = sanitizeInput($_POST['status']);
    $description = sanitizeInput($_POST['description']);
    
    // Update room
    $sql = "UPDATE rooms SET room_number = ?, room_type = ?, capacity = ?, price = ?, 
            floor_number = ?, status = ?, description = ? WHERE room_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssidissi", $room_number, $room_type, $capacity, $price, $floor_number, $status, $description, $room_id);
    
    if (mysqli_stmt_execute($stmt)) {
        setSuccessMessage('Room updated successfully');
    } else {
        setErrorMessage('Failed to update room');
    }
    
    header('Location: rooms.php');
    exit();
}

// Handle room deletion
if (isset($_GET['delete'])) {
    $room_id = intval($_GET['delete']);
    
    $sql = "DELETE FROM rooms WHERE room_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $room_id);
    
    if (mysqli_stmt_execute($stmt)) {
        setSuccessMessage('Room deleted successfully');
    } else {
        setErrorMessage('Failed to delete room. Room might have active bookings.');
    }
    
    header('Location: rooms.php');
    exit();
}

// Get room for editing
$edit_room = null;
if (isset($_GET['edit'])) {
    $room_id = intval($_GET['edit']);
    $sql = "SELECT * FROM rooms WHERE room_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $room_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $edit_room = mysqli_fetch_assoc($result);
}

// Get all rooms
$sql = "SELECT * FROM rooms ORDER BY room_number ASC";
$rooms = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooms Management - <?php echo SITE_NAME; ?></title>
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
                    <li><a href="rooms.php" class="active">🏠 Rooms</a></li>
                    <li><a href="bookings.php">📅 Bookings</a></li>
                    <li><a href="payments.php">💳 Payments</a></li>
                    <li><a href="complaints.php">📝 Complaints</a></li>
                    <li><a href="events.php">📢 Events</a></li>
                    <li><a href="settings.php">⚙️ Settings</a></li>
                </ul>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Page Header -->
                <div class="dashboard-header">
                    <h1>🏠 Rooms Management</h1>
                    <p>Add, update, and manage hostel rooms</p>
                </div>

                <!-- Display messages -->
                <?php echo displayMessages(); ?>

                <!-- Add/Edit Room Form -->
                <div class="card">
                    <div class="card-header">
                        <h3><?php echo $edit_room ? '✏️ Edit Room' : '➕ Add New Room'; ?></h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <?php if ($edit_room): ?>
                                <input type="hidden" name="room_id" value="<?php echo $edit_room['room_id']; ?>">
                            <?php endif; ?>
                            
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                                <!-- Room Number -->
                                <div class="form-group">
                                    <label for="room_number">Room Number *</label>
                                    <input type="text" id="room_number" name="room_number" required
                                           value="<?php echo $edit_room ? htmlspecialchars($edit_room['room_number']) : ''; ?>">
                                </div>
                                
                                <!-- Room Type -->
                                <div class="form-group">
                                    <label for="room_type">Room Type *</label>
                                    <select id="room_type" name="room_type" required>
                                        <option value="single" <?php echo ($edit_room && $edit_room['room_type'] == 'single') ? 'selected' : ''; ?>>Single</option>
                                        <option value="double" <?php echo ($edit_room && $edit_room['room_type'] == 'double') ? 'selected' : ''; ?>>Double</option>
                                        <option value="triple" <?php echo ($edit_room && $edit_room['room_type'] == 'triple') ? 'selected' : ''; ?>>Triple</option>
                                        <option value="quad" <?php echo ($edit_room && $edit_room['room_type'] == 'quad') ? 'selected' : ''; ?>>Quad</option>
                                    </select>
                                </div>
                                
                                <!-- Capacity -->
                                <div class="form-group">
                                    <label for="capacity">Capacity *</label>
                                    <input type="number" id="capacity" name="capacity" min="1" max="10" required
                                           value="<?php echo $edit_room ? $edit_room['capacity'] : ''; ?>">
                                </div>
                                
                                <!-- Price -->
                                <div class="form-group">
                                    <label for="price">Price (৳) *</label>
                                    <input type="number" id="price" name="price" min="0" step="0.01" required
                                           value="<?php echo $edit_room ? $edit_room['price'] : ''; ?>">
                                </div>
                                
                                <!-- Floor Number -->
                                <div class="form-group">
                                    <label for="floor_number">Floor Number *</label>
                                    <input type="number" id="floor_number" name="floor_number" min="1" required
                                           value="<?php echo $edit_room ? $edit_room['floor_number'] : ''; ?>">
                                </div>
                                
                                <!-- Status -->
                                <div class="form-group">
                                    <label for="status">Status *</label>
                                    <select id="status" name="status" required>
                                        <option value="available" <?php echo ($edit_room && $edit_room['status'] == 'available') ? 'selected' : ''; ?>>Available</option>
                                        <option value="occupied" <?php echo ($edit_room && $edit_room['status'] == 'occupied') ? 'selected' : ''; ?>>Occupied</option>
                                        <option value="maintenance" <?php echo ($edit_room && $edit_room['status'] == 'maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" rows="3"><?php echo $edit_room ? htmlspecialchars($edit_room['description']) : ''; ?></textarea>
                            </div>
                            
                            <!-- Buttons -->
                            <div class="form-actions">
                                <?php if ($edit_room): ?>
                                    <button type="submit" name="update_room" class="btn btn-success">Update Room</button>
                                    <a href="rooms.php" class="btn btn-secondary">Cancel</a>
                                <?php else: ?>
                                    <button type="submit" name="add_room" class="btn btn-primary">Add Room</button>
                                    <button type="reset" class="btn btn-secondary">Reset</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

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
                                        <td class="actions">
                                            <a href="?edit=<?php echo $room['room_id']; ?>" class="btn btn-primary btn-small">Edit</a>
                                            <a href="?delete=<?php echo $room['room_id']; ?>" 
                                               class="btn btn-danger btn-small"
                                               onclick="return confirm('Delete this room?')">Delete</a>
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
        // Initialize table search
        filterTable('searchInput', 'roomsTable');
    </script>
</body>
</html>
