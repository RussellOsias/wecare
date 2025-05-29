<?php
session_start();
require_once 'includes/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: reset_password.php");
    exit();
}

$email = $_SESSION['reset_email'];
$new_password = trim($_POST['new_password']);
$confirm_password = trim($_POST['confirm_password']);

if ($new_password !== $confirm_password) {
    $_SESSION['error'] = "Passwords do not match.";
    header("Location: reset_password.php");
    exit();
}

if (strlen($new_password) < 8) {
    $_SESSION['error'] = "Password must be at least 8 characters.";
    header("Location: reset_password.php");
    exit();
}

try {
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = :password WHERE email = :email");
    $stmt->execute([
        ':password' => $hashed,
        ':email' => $email
    ]);

    // Optional: Delete used OTP entry
    $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = :email");
    $stmt->execute([':email' => $email]);

    unset($_SESSION['reset_email']);
    $_SESSION['success'] = "Your password has been successfully reset!";
    header("Location: login.php");
    exit();
} catch (PDOException $e) {
    $_SESSION['error'] = "Error updating password.";
    header("Location: reset_password.php");
    exit();
}