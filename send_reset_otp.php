<?php
session_start();
require_once 'includes/db_conn.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set timezone (important!)
date_default_timezone_set('Asia/Manila');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $_SESSION['error'] = "Please enter a valid email address.";
        header("Location: forgot_password.php");
        exit();
    }

    // Check if user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION['error'] = "No account found with that email.";
        header("Location: forgot_password.php");
        exit();
    }

    // Generate OTP
    $otp = rand(100000, 999999);
    $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    try {
        // Clear old OTPs for this email
        $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = :email");
        $stmt->execute([':email' => $email]);

        // Save new OTP
        $stmt = $conn->prepare("
            INSERT INTO password_resets (email, otp, expires_at)
            VALUES (:email, :otp, :expires_at)
        ");
        $stmt->execute([
            ':email' => $email,
            ':otp' => $otp,
            ':expires_at' => $expires_at
        ]);

        // Send OTP via PHPMailer
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'valoaccs1928@gmail.com';
            $mail->Password   = 'zvqmkguzrnwurscd'; // App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Recipients
            $mail->setFrom('valoaccs1928@gmail.com', 'WeCare Support');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request - WeCare';
            $mail->Body    = "Your password reset code is: <b>$otp</b><br>This code will expire in 5 minutes.";

            $mail->send();

            $_SESSION['otp_email'] = $email;
            unset($_SESSION['reset_email']); // Clean up previous reset session
            header("Location: verify_reset_otp.php");
            exit();
        } catch (Exception $e) {
            error_log("Mail Error: " . $mail->ErrorInfo);
            $_SESSION['error'] = "Failed to send OTP via email.";
            header("Location: forgot_password.php");
            exit();
        }

    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error: " . $e->getMessage();
        header("Location: forgot_password.php");
        exit();
    }
}