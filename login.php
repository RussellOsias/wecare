<?php
session_start();
require_once 'includes/db_conn.php';
require_once 'includes/authentication.php';

// Set the correct timezone
date_default_timezone_set('Asia/Manila'); // Replace with your desired timezone

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    // Authenticate the user
    if (authenticateSession($email, $password)) {
        // Check if the user is an admin
        if ($_SESSION['role'] === 'admin') {
            // Generate session token for admin
            $token = generateSessionToken($_SESSION['user_id']);
            if ($token) {
                setcookie('auth_token', $token, time() + (86400), "/"); // Set token as a cookie
            }

            // Record login time in the admin_logs table
            $login_time = date("Y-m-d H:i:s"); // Valid DATETIME format for MySQL
            $stmt = $conn->prepare("INSERT INTO admin_logs (user_id, email, login_time) VALUES (:user_id, :email, :login_time)");
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            $stmt->bindParam(':email', $_SESSION['email']);
            $stmt->bindParam(':login_time', $login_time);
            $stmt->execute();

            header("Location: dashboard.php");
            exit();
        } else {
            // Display error message for non-admin users
            echo "<script>alert('You are not authorized to log in as an admin.');</script>";
            session_destroy(); // Destroy session to prevent partial login
        }
    } else {
        echo "<script>alert('Invalid email or password.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="assets/css/style_login.css">
</head>
<body>
  <div class="wrapper">
    <form method="POST" action="">
      <h2>Login</h2>
      <div class="input-field">
        <input type="email" name="email" required>
        <label>Enter your email</label>
      </div>
      <div class="input-field">
        <input type="password" name="password" required>
        <label>Enter your password</label>
      </div>
      <div class="forget">
        <label for="remember">
          <input type="checkbox" id="remember">
          <p>Remember me</p>
        </label>
        <a href="#">Forgot password?</a>
      </div>
      <button type="submit">Log In</button>
      <div class="register">
        <p>Don't have an account? <a href="register.php">Register</a></p>
      </div>
    </form>
  </div>
</body>
</html>