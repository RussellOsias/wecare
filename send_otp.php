<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendOTP($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        // Enable debugging
        $mail->SMTPDebug = 2; // 2 = verbose debug output
        $mail->Debugoutput = 'html'; // Output debug messages in HTML format

        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'cocnambawan@gmail.com';
        $mail->Password   = 'efrp wagi phjm gpwh';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('cocnambawan@gmail.com', 'Verfication Code');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Login';
        $mail->Body    = "Your OTP is: <b>$otp</b>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error or display it for debugging
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>