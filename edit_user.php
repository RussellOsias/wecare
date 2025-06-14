<?php
session_start();
require_once 'includes/db_conn.php';

// Redirect if not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get user ID from query string
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid user ID.");
}
$user_id = intval($_GET['id']);

// Fetch user data
try {
    $stmt = $conn->prepare("SELECT id, first_name, last_name, email, role FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User not found.");
    }
} catch (PDOException $e) {
    die("Error fetching user: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = htmlspecialchars($_POST['first_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $role = htmlspecialchars($_POST['role']);
    
    // Use original email from database, ignore submitted email
    $email = $user['email'];
    
    // Initialize an array to track changes
    $changes = [];
    
    // Check each field for changes (excluding email)
    if ($user['first_name'] !== $first_name) {
        $changes[] = "First name changed from '{$user['first_name']}' to '$first_name'";
    }
    if ($user['last_name'] !== $last_name) {
        $changes[] = "Last name changed from '{$user['last_name']}' to '$last_name'";
    }
    if ($user['role'] !== $role) {
        $changes[] = "Role changed from '{$user['role']}' to '$role'";
    }

    try {
        $conn->beginTransaction();

        // Prepare the base update query
        $updateFields = ["first_name = :first_name", "last_name = :last_name", "role = :role"];
        $params = [
            ':first_name' => $first_name,
            ':last_name' => $last_name,
            ':role' => $role,
            ':id' => $user_id
        ];

        // Check if password is provided and matches confirmation
        $new_password = trim($_POST['new_password'] ?? '');
        $confirm_password = trim($_POST['confirm_password'] ?? '');

        if (!empty($new_password)) {
            if ($new_password === $confirm_password) {
                $hashedPassword = password_hash($new_password, PASSWORD_DEFAULT);
                $updateFields[] = "password = :password";
                $params[':password'] = $hashedPassword;
                $changes[] = "Password was updated.";
            } else {
                throw new Exception("Passwords do not match.");
            }
        }

        // Build and execute the final query
        $updateQuery = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = :id";
        $stmt = $conn->prepare($updateQuery);
        $stmt->execute($params);

        // Log activity only if there are changes
        if (!empty($changes)) {
            // Get admin's full name
            $admin_stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = :admin_id");
            $admin_stmt->bindParam(':admin_id', $_SESSION['user_id']);
            $admin_stmt->execute();
            $admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);
            $admin_name = $admin['first_name'] . ' ' . $admin['last_name'];

            // Get user's full name (using updated values)
            $user_name = $first_name . ' ' . $last_name;

            // Format the action log
            $action = "$admin_name updated user $user_name: " . implode(', ', $changes);

            // Log to admin_activity_logs with activity_type
            $log_stmt = $conn->prepare("INSERT INTO admin_activity_logs 
                (admin_id, activity_type, action, user_affected_id) 
                VALUES (:admin_id, :activity_type, :action, :user_affected_id)");

            $log_stmt->bindParam(':admin_id', $_SESSION['user_id']);
            $log_stmt->bindValue(':activity_type', 'user_update');
            $log_stmt->bindParam(':action', $action);
            $log_stmt->bindParam(':user_affected_id', $user_id);
            $log_stmt->execute();
        }

        $conn->commit();

        echo "<script>alert('User updated successfully!'); window.location.href='manage_users.php';</script>";
    } catch (Exception $e) {
        $conn->rollBack();
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="container">
                <h2>Edit User</h2>
                <form method="POST" action="">
                    <div class="input-field">
                        <label for="first_name">First Name:</label>
                        <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    <div class="input-field">
                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                    <div class="input-field">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" readonly disabled>
                    </div>
                    <div class="input-field">
                        <label for="role">Role:</label>
                        <select id="role" name="role" required>
                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="resident" <?= $user['role'] === 'resident' ? 'selected' : '' ?>>Resident</option>
                            <option value="officer" <?= $user['role'] === 'officer' ? 'selected' : '' ?>>Officer</option>
                        </select>
                    </div>

                    <!-- New Password Fields -->
                    <div class="input-field">
                        <label for="new_password">New Password (optional):</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    <div class="input-field">
                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>

                    <button type="submit">Update User</button>
                </form>
                <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
                <a href="manage_users.php" class="back-btn" style="margin-left: 10px;">Back to Manage Users</a>
            </div>
        </main>
    </div>
</body>
</html>