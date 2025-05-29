<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="assets/css/style_login.css">
</head>
<body>
    <div class="wrapper">
        <form method="POST" action="send_reset_otp.php">
            <h2>Forgot Password</h2>

            <?php if (!empty($_SESSION['error'])): ?>
                <div style="color: red; margin-bottom: 10px;">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['success'])): ?>
                <div style="color: green; margin-bottom: 10px;">
                    <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <p>Enter your registered email address below to receive a reset code.</p>

            <div class="input-field">
                <input type="email" name="email" required>
                <label>Email Address</label>
            </div>

            <button type="submit">Send Reset Code</button>
            <div class="register">
                <a href="login.php">Back to Login</a>
            </div>
        </form>
    </div>
</body>
</html>