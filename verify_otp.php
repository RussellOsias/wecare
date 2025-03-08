<?php
session_start();

// Include necessary files
require_once 'includes/db_conn.php';
require_once 'includes/authentication.php';

// Redirect if OTP is not set
if (!isset($_SESSION['otp'])) {
    header("Location: login.php");
    exit();
}

// Handle OTP verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_otp = $_POST['otp'];

    if ($user_otp == $_SESSION['otp']) {
        // OTP is correct, proceed to dashboard
        unset($_SESSION['otp']);
        unset($_SESSION['otp_email']);

        // Role-based redirection
        switch ($_SESSION['role']) {
            case 'admin':
                $token = generateSessionToken($_SESSION['user_id']);
                if ($token) {
                    setcookie('auth_token', $token, time() + 86400, "/");
                }
                
                // Log admin
                $login_time = date("Y-m-d H:i:s");
                $stmt = $conn->prepare("INSERT INTO admin_logs (user_id, email, login_time) VALUES (:user_id, :email, :login_time)");
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->bindParam(':email', $_SESSION['email']);
                $stmt->bindParam(':login_time', $login_time);
                $stmt->execute();
                
                header("Location: dashboard.php");
                exit();
            
            case 'officer':
            case 'resident':
                header("Location: dashboard.php");
                exit();
            
            default:
                $errors[] = "Access denied. Invalid role.";
                session_destroy();
            }
    } else {
        $errors[] = "Invalid OTP. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify OTP</title>
  <link rel="stylesheet" href="assets/css/style_login.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <style>
    .error-message {
      color: #ff4444;
      border: 2px solid #ff4444;
      background: #ffeeee;
      padding: 12px;
      border-radius: 4px;
      text-align: center;
      margin: 15px 0;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <form method="POST" action="">
      <h2>Verify OTP</h2>

      <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $error): ?>
          <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>
      <?php endif; ?>

      <div class="input-field">
        <input type="text" name="otp" required>
        <label>Enter OTP</label>
      </div>

      <button type="submit">Verify</button>
    </form>
  </div>
</body>
</html>