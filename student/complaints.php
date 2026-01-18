<?php
// =============================================
// Student - My Complaints
// =============================================

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is student
requireRole('student');

$student_id = $_SESSION['user_id'];

// Handle complaint submission
if (isset($_POST['submit_complaint'])) {
    $complaint_type = sanitizeInput($_POST['complaint_type']);
    $subject = sanitizeInput($_POST['subject']);
    $description = sanitizeInput($_POST['description']);
    
    if (empty($subject) || empty($description)) {
        setErrorMessage('Please fill in all required fields');
    } elseif (strlen($description) < 10) {
        setErrorMessage('Description must be at least 10 characters');
    } else {
        $sql = "INSERT INTO complaints (student_id, complaint_type, subject, description) 
                VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "isss", $student_id, $complaint_type, $subject, $description);
        
        if (mysqli_stmt_execute($stmt)) {
            setSuccessMessage('Complaint submitted successfully!');
        } else {
            setErrorMessage('Failed to submit complaint');
        }
        
        header('Location: complaints.php');
        exit();
    }
}

// Get all complaints for this student
$sql = "SELECT c.*, m.full_name as manager_name 
        FROM complaints c 
        LEFT JOIN users m ON c.manager_id = m.user_id 
        WHERE c.student_id = ? 
        ORDER BY c.created_at DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$complaints = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Complaints - <?php echo SITE_NAME; ?></title>
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
                    <li><a href="complaints.php" class="active">📝 Complaints</a></li>
                    <li><a href="meals.php">🍽️ Meal Schedule</a></li>
                    <li><a href="events.php">📢 Events</a></li>
                    <li><a href="profile.php">👤 Profile</a></li>
                </ul>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Page Header -->
                <div class="dashboard-header">
                    <h1>📝 My Complaints</h1>
                    <p>Submit and track your complaints</p>
                </div>

                <!-- Display messages -->
                <?php echo displayMessages(); ?>

                <!-- Submit Complaint Form -->
                <div class="card">
                    <div class="card-header">
                        <h3>➕ Submit New Complaint</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" onsubmit="return validateComplaintForm(event)">
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                                <!-- Complaint Type -->
                                <div class="form-group">
                                    <label for="complaint_type">Complaint Type *</label>
                                    <select id="complaint_type" name="complaint_type" required>
                                        <option value="">Select Type</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="cleaning">Cleaning</option>
                                        <option value="food">Food</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>

                                <!-- Subject -->
                                <div class="form-group">
                                    <label for="subject">Subject *</label>
                                    <input type="text" id="subject" name="subject" required
                                           placeholder="Brief description of the issue">
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="form-group">
                                <label for="description">Detailed Description *</label>
                                <textarea id="description" name="description" rows="4" required
                                          placeholder="Please provide detailed information about your complaint..."></textarea>
                                <small style="color: #7f8c8d;">Minimum 10 characters</small>
                            </div>

                            <!-- Submit Button -->
                            <div class="form-group">
                                <button type="submit" name="submit_complaint" class="btn btn-primary">
                                    📤 Submit Complaint
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Complaints Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>My Complaint History (<?php echo mysqli_num_rows($complaints); ?>)</h2>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Type</th>
                                <th>Subject</th>
                                <th>Description</th>
                                <th>Assigned To</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Last Update</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($complaints) > 0): ?>
                                <?php while ($complaint = mysqli_fetch_assoc($complaints)): ?>
                                    <tr>
                                        <td><strong>#<?php echo $complaint['complaint_id']; ?></strong></td>
                                        <td><?php echo ucfirst($complaint['complaint_type']); ?></td>
                                        <td><?php echo htmlspecialchars($complaint['subject']); ?></td>
                                        <td style="max-width: 300px;"><?php echo htmlspecialchars($complaint['description']); ?></td>
                                        <td>
                                            <?php echo $complaint['manager_name'] ? htmlspecialchars($complaint['manager_name']) : 'Not Assigned'; ?>
                                        </td>
                                        <td>
                                            <?php
                                            $badge_class = '';
                                            switch ($complaint['status']) {
                                                case 'resolved': $badge_class = 'badge-success'; break;
                                                case 'pending': $badge_class = 'badge-warning'; break;
                                                case 'assigned': $badge_class = 'badge-info'; break;
                                                case 'in_progress': $badge_class = 'badge-info'; break;
                                                case 'closed': $badge_class = 'badge-secondary'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $complaint['status'])); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDateTime($complaint['created_at']); ?></td>
                                        <td><?php echo formatDateTime($complaint['updated_at']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 3rem;">
                                        <h3>📭 No Complaints Yet</h3>
                                        <p>You haven't submitted any complaints.</p>
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
