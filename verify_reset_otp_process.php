<?php
session_start();
require_once 'includes/db_conn.php';

date_default_timezone_set('Asia/Manila'); // Make sure timezone matches

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Invalid request method.";
    header("Location: verify_reset_otp.php");
    exit();
}

$otp_entered = trim($_POST['otp']);
$email = $_SESSION['otp_email'] ?? null;

file_put_contents('logs/otp_debug.log', "[" . date("Y-m-d H:i:s") . "] Email: $email | Entered OTP: $otp_entered\n", FILE_APPEND);

if (!$email) {
    $_SESSION['error'] = "Session expired. Please try again.";
    header("Location: forgot_password.php");
    exit();
}

try {
    // Fetch OTP record
    $stmt = $conn->prepare("SELECT otp, expires_at FROM password_resets WHERE email = :email");
    $stmt->execute([':email' => $email]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
        $_SESSION['error'] = "No OTP found for this email.";
        header("Location: verify_reset_otp.php");
        exit();
    }

    $stored_otp = $record['otp'];
    $expires_at = $record['expires_at'];

    // Compare OTP
    if ($otp_entered !== $stored_otp) {
        $_SESSION['error'] = "Invalid OTP.";
        header("Location: verify_reset_otp.php");
        exit();
    }

    // Check expiration
    $now = date("Y-m-d H:i:s");
    if ($expires_at < $now) {
        $_SESSION['error'] = "OTP has expired.";
        header("Location: verify_reset_otp.php");
        exit();
    }

    // ✅ All checks passed
    $_SESSION['reset_email'] = $email;
    unset($_SESSION['otp_email']);

    // Delete OTP after use
    $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = :email");
    $stmt->execute([':email' => $email]);

    header("Location: reset_password.php");
    exit();

} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    $_SESSION['error'] = "Database error occurred.";
    header("Location: verify_reset_otp.php");
    exit();
}