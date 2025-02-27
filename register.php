<?php
require_once 'includes/db_conn.php';

// Initialize variables for error and success messages
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $first_name = htmlspecialchars($_POST['first_name']);
    $middle_name = htmlspecialchars($_POST['middle_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone_number = intval($_POST['phone_number']);
    $address = htmlspecialchars($_POST['address']);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format. Please enter a valid email (e.g., example@gmail.com).";
    } else {
        // Check if email already exists in the database
        try {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $existing_user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing_user) {
                $error = "Email is already taken. Please choose another one.";
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }

    // Validate password
    if (!$error && strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif (!$error && $password !== $confirm_password) {
        $error = "Passwords do not match.";
    }

    // If no errors, proceed with registration
    if (!$error) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        try {
            // Insert user data into the database
            $stmt = $conn->prepare("INSERT INTO users (first_name, middle_name, last_name, email, password, phone_number, address) 
                                    VALUES (:first_name, :middle_name, :last_name, :email, :password, :phone_number, :address)");
            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':middle_name', $middle_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':phone_number', $phone_number);
            $stmt->bindParam(':address', $address);

            if ($stmt->execute()) {
                $success = "Registration successful!";
                echo "<script>alert('$success'); window.location.href='login.php';</script>";
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <link rel="stylesheet" href="assets/css/style_login.css"> <!-- Link to the CSS file -->
  <style>
    /* Error and Success Messages */
    .message {
      text-align: center;
      margin-bottom: 20px;
      font-size: 1rem;
    }

    .message.success {
      color: #28a745; /* Green for success */
    }

    .message.error {
      color: #dc3545; /* Red for errors */
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <form method="POST" action="">
      <h2>Register</h2>

      <?php if ($error): ?>
        <div class="message error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="message success"><?php echo htmlspecialchars($success); ?></div>
      <?php endif; ?>

      <div class="input-field">
        <input type="text" name="first_name" required>
        <label>First Name</label>
      </div>
      <div class="input-field">
        <input type="text" name="middle_name">
        <label>Middle Name</label>
      </div>
      <div class="input-field">
        <input type="text" name="last_name" required>
        <label>Last Name</label>
      </div>
      <div class="input-field">
        <input type="email" name="email" required>
        <label>Email (e.g., example@gmail.com)</label>
      </div>
      <div class="input-field">
        <input type="password" name="password" required>
        <label>Password (Minimum 8 characters)</label>
      </div>
      <div class="input-field">
        <input type="password" name="confirm_password" required>
        <label>Confirm Password</label>
      </div>
      <div class="input-field">
        <input type="number" name="phone_number" required>
        <label>Phone Number</label>
      </div>
      <div class="input-field">
        <input type="text" name="address" required>
        <label>Address</label>
      </div>
      <button type="submit">Register</button>
      <div class="register">
        <p>Already have an account? <a href="login.php">Login</a></p>
      </div>
    </form>
  </div>
</body>
</html>