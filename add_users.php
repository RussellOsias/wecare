<?php
session_start();
require_once 'includes/db_conn.php';

// Redirect if not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = htmlspecialchars($_POST['first_name']);
    $middle_name = htmlspecialchars($_POST['middle_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $phone_number = intval($_POST['phone_number']);
    $address = htmlspecialchars($_POST['address']);
    $role = htmlspecialchars($_POST['role']);

    try {
        $conn->beginTransaction();
        
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (first_name, middle_name, last_name, email, password, phone_number, address, role) 
                              VALUES (:first_name, :middle_name, :last_name, :email, :password, :phone_number, :address, :role)");
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':middle_name', $middle_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':role', $role);
        $stmt->execute();
        
        // Get the ID of the newly created user
        $new_user_id = $conn->lastInsertId();
        
        // Get admin's details
        $admin_stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = :admin_id");
        $admin_stmt->bindParam(':admin_id', $_SESSION['user_id']);
        $admin_stmt->execute();
        $admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);
        $admin_name = $admin['first_name'] . ' ' . $admin['last_name'];
        
        // Prepare detailed log message
        $action = "$admin_name created new $role: $first_name $last_name ($email)";
        
        // Log to admin_activity_logs
        $log_stmt = $conn->prepare("INSERT INTO admin_activity_logs 
                                  (admin_id, activity_type, action, user_affected_id) 
                                  VALUES (:admin_id, :activity_type, :action, :user_affected_id)");
        $log_stmt->bindParam(':admin_id', $_SESSION['user_id']);
        $log_stmt->bindValue(':activity_type', 'User Creation');
        $log_stmt->bindParam(':action', $action);
        $log_stmt->bindParam(':user_affected_id', $new_user_id);
        $log_stmt->execute();
        
        $conn->commit();
        
        echo "<script>alert('User added successfully!'); window.location.href='manage_users.php';</script>";
    } catch (PDOException $e) {
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
    <title>Add User</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="container">
                <h2>Add User</h2>
                <form method="POST" action="">
                    <div class="input-field">
                        <label for="first_name">First Name:</label>
                        <input type="text" id="first_name" name="first_name" required>
                    </div>
                    <div class="input-field">
                        <label for="middle_name">Middle Name:</label>
                        <input type="text" id="middle_name" name="middle_name" required>
                    </div>
                    <div class="input-field">
                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" required>
                    </div>
                    <div class="input-field">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="input-field">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="input-field">
                        <label for="phone_number">Phone Number:</label>
                        <input type="number" id="phone_number" name="phone_number" required>
                    </div>
                    <div class="input-field">
                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address" required>
                    </div>
                    <div class="input-field">
                        <label for="role">Role:</label>
                        <select id="role" name="role" required>
                            <option value="admin">Admin</option>
                            <option value="resident">Resident</option>
                            <option value="officer">Officer</option>
                        </select>
                    </div>
                    <button type="submit">Add User</button>
                </form>
                <a href="manage_users.php" class="back-btn">Back to Manage Users</a>
            </div>
        </main>
    </div>
</body>
</html>