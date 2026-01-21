<?php

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is student
requireRole('student');

$student_id = $_SESSION['user_id'];

// Get room ID from URL
if (!isset($_GET['room_id'])) {
    setErrorMessage('Invalid room selection');
    header('Location: rooms.php');
    exit();
}

$room_id = intval($_GET['room_id']);

// Get room details
$sql = "SELECT * FROM rooms WHERE room_id = ? AND status = 'available'";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $room_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$room = mysqli_fetch_assoc($result);

if (!$room) {
    setErrorMessage('Room not available for booking');
    header('Location: rooms.php');
    exit();
}

// Handle booking submission
if (isset($_POST['book_room'])) {
    $check_in_date = sanitizeInput($_POST['check_in_date']);
    $booking_date = date('Y-m-d');
    
    // Validate check-in date
    if (strtotime($check_in_date) < strtotime(date('Y-m-d'))) {
        setErrorMessage('Check-in date cannot be in the past');
    } else {
        // Insert booking
        $sql = "INSERT INTO bookings (student_id, room_id, booking_date, check_in_date, status) 
                VALUES (?, ?, ?, ?, 'pending')";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iiss", $student_id, $room_id, $booking_date, $check_in_date);
        
        if (mysqli_stmt_execute($stmt)) {
            $booking_id = mysqli_insert_id($conn);
            
            // Create payment record
            $sql = "INSERT INTO payments (booking_id, student_id, amount, status) 
                    VALUES (?, ?, ?, 'pending')";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iid", $booking_id, $student_id, $room['price']);
            mysqli_stmt_execute($stmt);
            
            setSuccessMessage('Booking request submitted successfully! Waiting for admin approval.');
            header('Location: bookings.php');
            exit();
        } else {
            setErrorMessage('Failed to submit booking request');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Room - <?php echo SITE_NAME; ?></title>
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
                    <li><a href="profile.php">👤 Profile</a></li>
                </ul>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Page Header -->
                <div class="dashboard-header">
                    <h1>📅 Book Room <?php echo htmlspecialchars($room['room_number']); ?></h1>
                    <p>Complete your room booking</p>
                </div>

                <!-- Display messages -->
                <?php echo displayMessages(); ?>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <!-- Room Details -->
                    <div class="card">
                        <div class="card-header">
                            <h3>🏠 Room Details</h3>
                        </div>
                        <div class="card-body">
                            <table style="width: 100%;">
                                <tr>
                                    <td style="padding: 0.5rem 0;"><strong>Room Number:</strong></td>
                                    <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($room['room_number']); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 0.5rem 0;"><strong>Room Type:</strong></td>
                                    <td style="padding: 0.5rem 0;"><?php echo ucfirst($room['room_type']); ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 0.5rem 0;"><strong>Capacity:</strong></td>
                                    <td style="padding: 0.5rem 0;"><?php echo $room['capacity']; ?> Person(s)</td>
                                </tr>
                                <tr>
                                    <td style="padding: 0.5rem 0;"><strong>Floor:</strong></td>
                                    <td style="padding: 0.5rem 0;">Floor <?php echo $room['floor_number']; ?></td>
                                </tr>
                                <tr>
                                    <td style="padding: 0.5rem 0;"><strong>Monthly Rent:</strong></td>
                                    <td style="padding: 0.5rem 0; font-size: 1.3rem; color: #27ae60; font-weight: bold;">
                                        ৳<?php echo number_format($room['price'], 2); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 0.5rem 0;"><strong>Description:</strong></td>
                                    <td style="padding: 0.5rem 0;"><?php echo htmlspecialchars($room['description']); ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- Booking Form -->
                    <div class="card">
                        <div class="card-header">
                            <h3>📝 Booking Information</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <!-- Student Name (Read-only) -->
                                <div class="form-group">
                                    <label>Your Name</label>
                                    <input type="text" value="<?php echo htmlspecialchars($_SESSION['full_name']); ?>" disabled>
                                </div>

                                <!-- Room Number (Read-only) -->
                                <div class="form-group">
                                    <label>Room Number</label>
                                    <input type="text" value="Room <?php echo htmlspecialchars($room['room_number']); ?>" disabled>
                                </div>

                                <!-- Check-in Date -->
                                <div class="form-group">
                                    <label for="check_in_date">Check-in Date *</label>
                                    <input type="date" id="check_in_date" name="check_in_date" 
                                           min="<?php echo date('Y-m-d'); ?>" required>
                                </div>

                                <!-- Monthly Rent -->
                                <div class="form-group">
                                    <label>Monthly Rent</label>
                                    <input type="text" value="৳<?php echo number_format($room['price'], 2); ?>" disabled>
                                </div>

                                <!-- Info Box -->
                                <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                                    <p style="margin: 0; font-size: 0.9rem;">
                                        ℹ️ <strong>Note:</strong> Your booking will be pending until approved by the admin. 
                                        You will be notified once your booking is approved.
                                    </p>
                                </div>

                                <!-- Buttons -->
                                <div class="form-actions">
                                    <button type="submit" name="book_room" class="btn btn-success">
                                        ✅ Confirm Booking
                                    </button>
                                    <a href="rooms.php" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="../assets/js/script.js"></script>
</body>
</html>
