<?php
require_once 'includes/db_conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = htmlspecialchars($_POST['first_name']);
    $middle_name = htmlspecialchars($_POST['middle_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $phone_number = intval($_POST['phone_number']);
    $address = htmlspecialchars($_POST['address']);

    try {
        $stmt = $conn->prepare("INSERT INTO users (first_name, middle_name, last_name, email, password, phone_number, address) 
                                VALUES (:first_name, :middle_name, :last_name, :email, :password, :phone_number, :address)");
        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':middle_name', $middle_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':address', $address);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Registration failed. Please try again.');</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
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
</head>
<body>
  <div class="wrapper">
    <form method="POST" action="">
      <h2>Register</h2>
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
        <label>Email</label>
      </div>
      <div class="input-field">
        <input type="password" name="password" required>
        <label>Password</label>
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