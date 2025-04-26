<?php
session_start();
require_once 'includes/db_conn.php';

// Ensure the user is logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get the complaint ID from the URL
$complaint_id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $priority = $_POST['priority'];

    try {
        $stmt = $conn->prepare("UPDATE complaints SET priority = :priority WHERE id = :id");
        $stmt->bindParam(':priority', $priority);
        $stmt->bindParam(':id', $complaint_id);
        $stmt->execute();

        header("Location: admin_view_complaints.php");
        exit();
    } catch (Exception $e) {
        die("Error setting priority: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Set Priority</title>
  <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
  <div class="dashboard-wrapper">
    <!-- Sidebar -->
    <div class="sidebar">
      <div class="logo-container">
        <img src="../images/logo.png" alt="Logo" class="logo"> <!-- Replace with your logo -->
        <h2>WeCare</h2>
      </div>
      <ul>
        <li><a href="#"><i class="fas fa-home"></i> Dashboard</a></li>
        <li><a href="admin_view_complaints.php"><i class="fas fa-exclamation-circle"></i> Complaints</a></li>
        <li><a href="#"><i class="fas fa-users"></i> Officers</a></li>
      </ul>
      <button class="logout-btn">Logout</button>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <div class="dashboard-header">
        <h1>Set Priority for Complaint #<?= htmlspecialchars($complaint_id) ?></h1>
      </div>

      <form method="POST" class="input-field">
        <label for="priority">Priority:</label>
        <select id="priority" name="priority" required>
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
        </select>
        <button type="submit" class="back-btn">Set Priority</button>
      </form>
    </div>
  </div>
</body>
</html>