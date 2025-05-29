<?php
session_start();

if (empty($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="assets/css/style_login.css">
</head>
<body>
    <div class="wrapper">
        <form method="POST" action="reset_password_process.php">
            <h2>Reset Password</h2>

            <?php if (!empty($_SESSION['error'])): ?>
                <div style="color: red; margin-bottom: 10px;">
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <p>Enter your new password below:</p>

            <div class="input-field">
                <input type="password" name="new_password" id="new_password" required>
                <label>New Password</label>
            </div>

            <div class="input-field">
                <input type="password" name="confirm_password" id="confirm_password" required>
                <label>Confirm Password</label>
            </div>

            <button type="submit">Reset Password</button>
            <div class="register">
                <a href="login.php">Back to Login</a>
            </div>
        </form>
    </div>
</body>
</html>