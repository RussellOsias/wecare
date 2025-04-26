<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendOTP($email, $otp) {
    // Initialize PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Enable verbose debugging
        $mail->SMTPDebug = 2; // Verbose debug output (set to 0 in production)
        $mail->Debugoutput = function ($str, $level) {
            // Log SMTP debug output to a file
            file_put_contents('logs/smtp_debug.log', $str, FILE_APPEND);
        };

        // Log the start of the email-sending process
        logMessage("Attempting to send OTP to email: $email");

        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'valoaccs1928@gmail.com';
        $mail->Password   = 'zvqmkguzrnwurscd';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('valoaccs1928@gmail.com', 'Verification Code');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'WeCare Verification Code';
        $mail->Body    = "Your OTP is: <b>$otp</b>";

        // Attempt to send the email
        $mail->send();

        // Log success message
        logMessage("OTP successfully sent to email: $email");
        return true;
    } catch (Exception $e) {
        // Log the error details
        logMessage("Failed to send OTP to email: $email. Error: " . $mail->ErrorInfo);

       
        return false;
    }
}

/**
 * Helper function to log messages to a file
 * @param string $message The message to log
 */
function logMessage($message) {
    // Define the log file path
    $logFile = 'logs/send_otp.log';

    // Ensure the logs directory exists
    if (!file_exists('logs')) {
        mkdir('logs', 0777, true);
    }

    // Format the log message with a timestamp
    $logMessage = "[" . date("Y-m-d H:i:s") . "] $message" . PHP_EOL;

    // Append the log message to the log file
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}
?>