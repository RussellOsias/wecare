<?php
session_start();
require_once 'includes/db_conn.php';

// Set the correct timezone
date_default_timezone_set('America/New_York'); // Replace with your desired timezone

if (isset($_SESSION['user_id'])) {
    // Clear the session token in the database
    $stmt = $conn->prepare("UPDATE users SET session_token = NULL, token_expiry = NULL WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();

    // Update the logout time in the admin_logs table
    $logout_time = date("Y-m-d H:i:s"); // Valid DATETIME format for MySQL
    $stmt = $conn->prepare("UPDATE admin_logs SET logout_time = :logout_time WHERE user_id = :user_id AND logout_time IS NULL ORDER BY login_time DESC LIMIT 1");
    $stmt->bindParam(':logout_time', $logout_time);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
}

// Destroy the session and clear the token cookie
session_destroy();
setcookie('auth_token', '', time() - 3600, "/"); // Expire the token cookie
header("Location: login.php");
exit();
?>