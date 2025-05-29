<?php
session_start();

if (empty($_SESSION['otp_email'])) {
    header("Location: forgot_password.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="assets/css/style_login.css">
</head>
<body>
<div class="wrapper">
    <form method="POST" action="verify_reset_otp_process.php">
        <h2>Verify OTP</h2>

        <?php if (!empty($_SESSION['error'])): ?>
            <div style="color: red;"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <p>An OTP has been sent to <strong><?= htmlspecialchars($_SESSION['otp_email']) ?></strong>. Please check your inbox and enter the code below:</p>

        <div class="input-field">
            <input type="text" name="otp" maxlength="6" required>
            <label>Enter OTP</label>
        </div>

        <button type="submit">Verify OTP</button>
        <div class="register">
            <a href="forgot_password.php">Change Email</a>
        </div>
    </form>
</div>
</body>
</html>