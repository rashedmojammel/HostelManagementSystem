<?php


require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is manager
requireRole('manager');

// Get all students 
$sql = "SELECT * FROM users WHERE role = 'student' ORDER BY full_name ASC";
$students = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <!-- Navigation -->
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
                    <li><a href="students.php" class="active">👨‍🎓 Students</a></li>
                    <li><a href="rooms.php">🏠 Rooms</a></li>
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
                    <h1>👨‍🎓 Students List</h1>
                    <p>View all registered students</p>
                </div>

                <!-- Display messages -->
                <?php echo displayMessages(); ?>

                <!-- Students Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>All Students (<?php echo mysqli_num_rows($students); ?>)</h2>
                        <input type="text" id="searchInput" placeholder="🔍 Search students..." 
                               style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <table id="studentsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Registered</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($students) > 0): ?>
                                <?php while ($student = mysqli_fetch_assoc($students)): ?>
                                    <tr>
                                        <td>#<?php echo $student['user_id']; ?></td>
                                        <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                                        <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                        <td>
                                            <?php if ($student['status'] === 'active'): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-danger">Blocked</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo formatDateTime($student['created_at']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">No students found</td>
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
        filterTable('searchInput', 'studentsTable');
    </script>
</body>
</html>
