<?php
session_start();

require_once 'includes/db_conn.php';
require_once 'includes/authentication.php';
require_once 'send_otp.php'; // Include the OTP sending function

date_default_timezone_set('Asia/Manila');

// Regenerate CAPTCHA on page load
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['captcha_code'] = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 6);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];
    $captcha = $_POST['captcha'] ?? '';
    
    // CAPTCHA validation
    if (strcasecmp($captcha, $_SESSION['captcha_code']) !== 0) {
        $errors[] = "CAPTCHA verification failed";
    } else {
        if (authenticateSession($email, $password)) {
            // Generate OTP
            $otp = rand(100000, 999999);
            $_SESSION['otp'] = $otp;
            $_SESSION['otp_email'] = $email;

            // Send OTP
            if (sendOTP($email, $otp)) {
                // Clear failed attempts on successful login
                $stmt = $conn->prepare("DELETE FROM login_attempts WHERE email = :email");
                $stmt->execute([':email' => $email]);
                
                header("Location: verify_otp.php");
                exit();
            } else {
                $errors[] = "Failed to send OTP. Please try again.";
            }
        } else {
            // Log failed attempt
            $stmt = $conn->prepare("INSERT INTO login_attempts (email, successful) VALUES (:email, 0)");
            $stmt->execute([':email' => $email,]);
            
            // Check failed attempts in last hour
            $stmt = $conn->prepare("SELECT COUNT(*) as attempts FROM login_attempts 
                                  WHERE email = :email
                                  AND attempt_time > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                                  AND successful = 0");
            $stmt->execute([':email' => $email]);
            $attempts = $stmt->fetch(PDO::FETCH_ASSOC)['attempts'];
            
            if ($attempts >= 3) {
                // Get user ID for notification
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
                $stmt->execute([':email' => $email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user) {
                    // Create security notification
                    $stmt = $conn->prepare("INSERT INTO notifications (user_id, message) 
                                          VALUES (:user_id, :message)");
                    $stmt->execute([
                        ':user_id' => $user['id'],
                        ':message' => "Security Alert: 3 failed login attempts detected at " . date('F j, Y, g:i a')
                    ]);
                }
                
                $errors[] = "Too many failed attempts.";
            } else {
                $errors[] = "Invalid email or password. Attempts remaining: " . (3 - $attempts);
            }
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
       <a href="forgot_password.php">Forgot password?</a>
      </div>

      <button type="submit">Log In</button>
      <div class="register">
    
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