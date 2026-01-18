<?php
// =============================================
// Manager - Meals Management
// =============================================

require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is manager
requireRole('manager');

// Handle meal addition
if (isset($_POST['add_meal'])) {
    $meal_date = sanitizeInput($_POST['meal_date']);
    $meal_type = sanitizeInput($_POST['meal_type']);
    $menu_items = sanitizeInput($_POST['menu_items']);
    $added_by = $_SESSION['user_id'];
    
    // Check if meal already exists for this date and type
    $sql_check = "SELECT meal_id FROM meals WHERE meal_date = ? AND meal_type = ?";
    $stmt_check = mysqli_prepare($conn, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "ss", $meal_date, $meal_type);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) > 0) {
        setErrorMessage('Meal already exists for this date and type');
    } else {
        $sql = "INSERT INTO meals (meal_date, meal_type, menu_items, added_by) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssi", $meal_date, $meal_type, $menu_items, $added_by);
        
        if (mysqli_stmt_execute($stmt)) {
            setSuccessMessage('Meal added successfully');
        } else {
            setErrorMessage('Failed to add meal');
        }
    }
    
    header('Location: meals.php');
    exit();
}

// Handle meal update
if (isset($_POST['update_meal'])) {
    $meal_id = intval($_POST['meal_id']);
    $meal_date = sanitizeInput($_POST['meal_date']);
    $meal_type = sanitizeInput($_POST['meal_type']);
    $menu_items = sanitizeInput($_POST['menu_items']);
    
    $sql = "UPDATE meals SET meal_date = ?, meal_type = ?, menu_items = ? WHERE meal_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssi", $meal_date, $meal_type, $menu_items, $meal_id);
    
    if (mysqli_stmt_execute($stmt)) {
        setSuccessMessage('Meal updated successfully');
    } else {
        setErrorMessage('Failed to update meal');
    }
    
    header('Location: meals.php');
    exit();
}

// Handle meal deletion
if (isset($_GET['delete'])) {
    $meal_id = intval($_GET['delete']);
    
    $sql = "DELETE FROM meals WHERE meal_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $meal_id);
    
    if (mysqli_stmt_execute($stmt)) {
        setSuccessMessage('Meal deleted successfully');
    } else {
        setErrorMessage('Failed to delete meal');
    }
    
    header('Location: meals.php');
    exit();
}

// Get meal for editing
$edit_meal = null;
if (isset($_GET['edit'])) {
    $meal_id = intval($_GET['edit']);
    $sql = "SELECT * FROM meals WHERE meal_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $meal_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $edit_meal = mysqli_fetch_assoc($result);
}

// Get all meals
$sql = "SELECT m.*, u.full_name as added_by_name 
        FROM meals m 
        JOIN users u ON m.added_by = u.user_id 
        ORDER BY m.meal_date DESC, m.meal_type ASC";
$meals = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meals Management - <?php echo SITE_NAME; ?></title>
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
                    <li><a href="students.php">👨‍🎓 Students</a></li>
                    <li><a href="rooms.php">🏠 Rooms</a></li>
                    <li><a href="bookings.php">📅 Bookings</a></li>
                    <li><a href="complaints.php">📝 Complaints</a></li>
                    <li><a href="meals.php" class="active">🍽️ Meals</a></li>
                    <li><a href="profile.php">👤 Profile</a></li>
                </ul>
            </aside>

            <!-- Main Content -->
            <main class="main-content">
                <!-- Page Header -->
                <div class="dashboard-header">
                    <h1>🍽️ Meals Management</h1>
                    <p>Add and manage daily meal schedules</p>
                </div>

                <!-- Display messages -->
                <?php echo displayMessages(); ?>

                <!-- Add/Edit Meal Form -->
                <div class="card">
                    <div class="card-header">
                        <h3><?php echo $edit_meal ? '✏️ Edit Meal' : '➕ Add New Meal'; ?></h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <?php if ($edit_meal): ?>
                                <input type="hidden" name="meal_id" value="<?php echo $edit_meal['meal_id']; ?>">
                            <?php endif; ?>
                            
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                                <!-- Meal Date -->
                                <div class="form-group">
                                    <label for="meal_date">Meal Date *</label>
                                    <input type="date" id="meal_date" name="meal_date" required
                                           value="<?php echo $edit_meal ? $edit_meal['meal_date'] : date('Y-m-d'); ?>">
                                </div>
                                
                                <!-- Meal Type -->
                                <div class="form-group">
                                    <label for="meal_type">Meal Type *</label>
                                    <select id="meal_type" name="meal_type" required>
                                        <option value="breakfast" <?php echo ($edit_meal && $edit_meal['meal_type'] == 'breakfast') ? 'selected' : ''; ?>>Breakfast</option>
                                        <option value="lunch" <?php echo ($edit_meal && $edit_meal['meal_type'] == 'lunch') ? 'selected' : ''; ?>>Lunch</option>
                                        <option value="dinner" <?php echo ($edit_meal && $edit_meal['meal_type'] == 'dinner') ? 'selected' : ''; ?>>Dinner</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Menu Items -->
                            <div class="form-group">
                                <label for="menu_items">Menu Items *</label>
                                <textarea id="menu_items" name="menu_items" rows="3" required 
                                          placeholder="e.g., Rice, Chicken Curry, Dal, Salad"><?php echo $edit_meal ? htmlspecialchars($edit_meal['menu_items']) : ''; ?></textarea>
                            </div>
                            
                            <!-- Buttons -->
                            <div class="form-actions">
                                <?php if ($edit_meal): ?>
                                    <button type="submit" name="update_meal" class="btn btn-success">Update Meal</button>
                                    <a href="meals.php" class="btn btn-secondary">Cancel</a>
                                <?php else: ?>
                                    <button type="submit" name="add_meal" class="btn btn-primary">Add Meal</button>
                                    <button type="reset" class="btn btn-secondary">Reset</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Meals Table -->
                <div class="table-container">
                    <div class="table-header">
                        <h2>All Meals (<?php echo mysqli_num_rows($meals); ?>)</h2>
                        <input type="text" id="searchInput" placeholder="🔍 Search meals..." 
                               style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 5px;">
                    </div>
                    <table id="mealsTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date</th>
                                <th>Meal Type</th>
                                <th>Menu Items</th>
                                <th>Added By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($meals) > 0): ?>
                                <?php while ($meal = mysqli_fetch_assoc($meals)): ?>
                                    <tr>
                                        <td>#<?php echo $meal['meal_id']; ?></td>
                                        <td><?php echo formatDate($meal['meal_date']); ?></td>
                                        <td>
                                            <span class="badge badge-info"><?php echo ucfirst($meal['meal_type']); ?></span>
                                        </td>
                                        <td><?php echo htmlspecialchars($meal['menu_items']); ?></td>
                                        <td><?php echo htmlspecialchars($meal['added_by_name']); ?></td>
                                        <td class="actions">
                                            <a href="?edit=<?php echo $meal['meal_id']; ?>" class="btn btn-primary btn-small">Edit</a>
                                            <a href="?delete=<?php echo $meal['meal_id']; ?>" 
                                               class="btn btn-danger btn-small"
                                               onclick="return confirm('Delete this meal?')">Delete</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">No meals found</td>
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
        filterTable('searchInput', 'mealsTable');
    </script>
</body>
</html>
