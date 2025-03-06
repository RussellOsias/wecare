<?php
session_start();
require_once 'includes/db_conn.php';
require_once 'includes/authentication.php';

date_default_timezone_set('Asia/Manila');


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['captcha_code'] = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];
    $captcha = $_POST['captcha'] ?? '';

  
    if (strcasecmp($captcha, $_SESSION['captcha_code']) !== 0) {
        $errors[] = "CAPTCHA verification failed";
    } else {
        if (authenticateSession($email, $password)) {
            if ($_SESSION['role'] === 'admin') {
                $token = generateSessionToken($_SESSION['user_id']);
                if ($token) {
                    setcookie('auth_token', $token, time() + 86400, "/");
                }

                $login_time = date("Y-m-d H:i:s");
                $stmt = $conn->prepare("INSERT INTO admin_logs (user_id, email, login_time) VALUES (:user_id, :email, :login_time)");
                $stmt->bindParam(':user_id', $_SESSION['user_id']);
                $stmt->bindParam(':email', $_SESSION['email']);
                $stmt->bindParam(':login_time', $login_time);
                $stmt->execute();

                header("Location: dashboard.php");
                exit();
            } else {
                $errors[] = "Access denied. Only Admin users can log in.";
                unset($_SESSION['user_id'], $_SESSION['email'], $_SESSION['role']);
            }
        } else {
            $errors[] = "Invalid email or password.";
        }
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: login.php");
        exit();
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

    .password-toggle {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      color: #999;
    }

    .captcha-group {
      display: flex;
      align-items: center;
      margin: 25px 0;
      gap: 10px;
    }

    .captcha-input {
      flex: 1;
      padding: 10px;
      border: 1px solid #dddfe2;
      border-radius: 4px;
    }

    .captcha-display {
      display: flex;
      align-items: center;
      background: #f0f0f0;
      padding: 8px 12px;
      border-radius: 4px;
      cursor: pointer;
      font-weight: bold;
      text-transform: uppercase;
    }

    .captcha-refresh {
      margin-left: 8px;
      cursor: pointer;
      color: #666;
      transition: color 0.3s;
    }

    .captcha-refresh:hover {
      color: #1877f2;
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <form method="POST" action="">
      <h2>Login</h2>

      <?php if (!empty($_SESSION['errors'])): ?>
        <?php foreach ($_SESSION['errors'] as $error): ?>
          <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endforeach; ?>
        <?php unset($_SESSION['errors']); ?>
      <?php endif; ?>

      <div class="input-field">
        <input type="email" name="email" required>
        <label>Enter your email</label>
      </div>

      <div class="input-field">
        <input type="password" name="password" id="password" required>
        <label>Enter your password</label>
        <i class="fas fa-eye password-toggle" onclick="togglePassword()"></i>
      </div>

      <div class="captcha-group">
        <input type="text" name="captcha" class="captcha-input" placeholder="Enter CAPTCHA" required>
        <div class="captcha-display" onclick="generateCaptcha()">
          <?= htmlspecialchars($_SESSION['captcha_code']) ?>
          <i class="fas fa-sync-alt captcha-refresh"></i>
        </div>
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

  <script>
    function togglePassword() {
      const passwordField = document.getElementById('password');
      const toggleIcon = document.querySelector('.password-toggle');
      
      if (passwordField.type === "password") {
        passwordField.type = "text";
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
      } else {
        passwordField.type = "password";
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
      }
    }

    function generateCaptcha() {
      fetch('generate_captcha.php')
        .then(response => response.text())
        .then(code => {
          document.querySelector('.captcha-display').firstChild.textContent = code;
        });
    }

    // Initial CAPTCHA setup
    document.querySelector('.captcha-display').addEventListener('click', generateCaptcha);
  </script>
</body>
</html>