<?php


require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is admin
requireRole('admin');

// Handle event addition
if (isset($_POST['add_event'])) {
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $event_date = sanitizeInput($_POST['event_date']);
    $event_time = sanitizeInput($_POST['event_time']);
    $location = sanitizeInput($_POST['location']);
    $created_by = $_SESSION['user_id'];
    
    $sql = "INSERT INTO events (title, description, event_date, event_time, location, created_by) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssi", $title, $description, $event_date, $event_time, $location, $created_by);
    
    if (mysqli_stmt_execute($stmt)) {
        setSuccessMessage('Event added successfully');
    } else {
        setErrorMessage('Failed to add event');
    }
    
    header('Location: events.php');
    exit();
}

// Handle event update
if (isset($_POST['update_event'])) {
    $event_id = intval($_POST['event_id']);
    $title = sanitizeInput($_POST['title']);
    $description = sanitizeInput($_POST['description']);
    $event_date = sanitizeInput($_POST['event_date']);
    $event_time = sanitizeInput($_POST['event_time']);
    $location = sanitizeInput($_POST['location']);
    
    $sql = "UPDATE events SET title = ?, description = ?, event_date = ?, event_time = ?, location = ? 
            WHERE event_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssi", $title, $description, $event_date, $event_time, $location, $event_id);
    
    if (mysqli_stmt_execute($stmt)) {
        setSuccessMessage('Event updated successfully');
    } else {
        setErrorMessage('Failed to update event');
    }
    
    header('Location: events.php');
    exit();
}

// Handle event deletion
if (isset($_GET['delete'])) {
    $event_id = intval($_GET['delete']);
    
    $sql = "DELETE FROM events WHERE event_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $event_id);
    
    if (mysqli_stmt_execute($stmt)) {
        setSuccessMessage('Event deleted successfully');
    } else {
        setErrorMessage('Failed to delete event');
    }
    
    header('Location: events.php');
    exit();
}

// Get event for editing
$edit_event = null;
if (isset($_GET['edit'])) {
    $event_id = intval($_GET['edit']);
    $sql = "SELECT * FROM events WHERE event_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $event_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $edit_event = mysqli_fetch_assoc($result);
}

// Get all events
$sql = "SELECT e.*, u.full_name as creator_name 
        FROM events e 
        JOIN users u ON e.created_by = u.user_id 
        ORDER BY e.event_date DESC";
$events = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events Management - <?php echo SITE_NAME; ?></title>
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
                    <li><a href="payments.php">💳 Payments</a></li>
                    <li><a href="complaints.php">📝 Complaints</a></li>
                    <li><a href="events.php" class="active">📢 Events</a></li>
                    <li><a href="settings.php">⚙️ Settings</a></li>
                </ul>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Page Header -->
                <div class="dashboard-header">
                    <h1>📢 Events Management</h1>
                    <p>Create and manage hostel events</p>
                </div>

                <!-- Display messages -->
                <?php echo displayMessages(); ?>

                <!-- Add/Edit Event Form -->
                <div class="card">
                    <div class="card-header">
                        <h3><?php echo $edit_event ? '✏️ Edit Event' : '➕ Add New Event'; ?></h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <?php if ($edit_event): ?>
                                <input type="hidden" name="event_id" value="<?php echo $edit_event['event_id']; ?>">
                            <?php endif; ?>
                            
                            <!-- Title -->
                            <div class="form-group">
                                <label for="title">Event Title *</label>
                                <input type="text" id="title" name="title" required
                                       value="<?php echo $edit_event ? htmlspecialchars($edit_event['title']) : ''; ?>">
                            </div>
                            
                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                                <!-- Event Date -->
                                <div class="form-group">
                                    <label for="event_date">Event Date *</label>
                                    <input type="date" id="event_date" name="event_date" required
                                           value="<?php echo $edit_event ? $edit_event['event_date'] : ''; ?>">
                                </div>
                                
                                <!-- Event Time -->
                                <div class="form-group">
                                    <label for="event_time">Event Time *</label>
                                    <input type="time" id="event_time" name="event_time" required
                                           value="<?php echo $edit_event ? $edit_event['event_time'] : ''; ?>">
                                </div>
                                
                                <!-- Location -->
                                <div class="form-group">
                                    <label for="location">Location *</label>
                                    <input type="text" id="location" name="location" required
                                           value="<?php echo $edit_event ? htmlspecialchars($edit_event['location']) : ''; ?>">
                                </div>
                            </div>
                            
                            <!-- Description -->
                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" rows="4"><?php echo $edit_event ? htmlspecialchars($edit_event['description']) : ''; ?></textarea>
                            </div>
                            
                            <!-- Buttons -->
                            <div class="form-actions">
                                <?php if ($edit_event): ?>
                                    <button type="submit" name="update_event" class="btn btn-success">Update Event</button>
                                    <a href="events.php" class="btn btn-secondary">Cancel</a>
                                <?php else: ?>
                                    <button type="submit" name="add_event" class="btn btn-primary">Add Event</button>
                                    <button type="reset" class="btn btn-secondary">Reset</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Events Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>All Events (<?php echo mysqli_num_rows($events); ?>)</h2>
                        <input type="text" id="searchInput" placeholder="🔍 Search events..." 
                               style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <table id="eventsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Title</th>
                                <th>Date & Time</th>
                                <th>Location</th>
                                <th>Description</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($events) > 0): ?>
                                <?php while ($event = mysqli_fetch_assoc($events)): ?>
                                    <tr>
                                        <td>#<?php echo $event['event_id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($event['title']); ?></strong></td>
                                        <td>
                                            <?php echo formatDate($event['event_date']); ?><br>
                                            <small><?php echo date('h:i A', strtotime($event['event_time'])); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars($event['location']); ?></td>
                                        <td style="max-width: 300px;"><?php echo htmlspecialchars($event['description']); ?></td>
                                        <td><?php echo htmlspecialchars($event['creator_name']); ?></td>
                                        <td class="actions">
                                            <a href="?edit=<?php echo $event['event_id']; ?>" class="btn btn-primary btn-small">Edit</a>
                                            <a href="?delete=<?php echo $event['event_id']; ?>" 
                                               class="btn btn-danger btn-small"
                                               onclick="return confirm('Delete this event?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">No events found</td>
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
        filterTable('searchInput', 'eventsTable');
    </script>
</body>
</html>
