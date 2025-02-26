<?php
session_start();
require_once 'includes/db_conn.php';

if (isset($_SESSION['user_id'])) {
    // Clear the session token in the database
    $stmt = $conn->prepare("UPDATE users SET session_token = NULL, token_expiry = NULL WHERE id = :id");
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
}

// Destroy the session and clear the token cookie
session_destroy();
setcookie('auth_token', '', time() - 3600, "/"); // Expire the token cookie
header("Location: login.php");
exit();
?>