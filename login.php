<?php
session_start();
require_once 'includes/db_conn.php';
require_once 'includes/authentication.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    if (authenticateSession($email, $password)) {
        // Generate a session token for token-based authentication
        $token = generateSessionToken($_SESSION['user_id']);
        if ($token) {
            setcookie('auth_token', $token, time() + (86400), "/"); // Set token as a cookie
        }

        header("Location: dashboard.php");
        exit();
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
  <link rel="stylesheet" href="assets/css/style.css"> <!-- Link to the CSS file -->
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