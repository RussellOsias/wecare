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

// Fetch all officers
try {
    $stmt = $conn->query("SELECT id, first_name, last_name FROM users WHERE role = 'officer'");
    $officers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error fetching officers: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $officer_id = $_POST['officer_id'];

    try {
        $stmt = $conn->prepare("UPDATE complaints SET assigned_officer_id = :officer_id, status = 'in_progress' WHERE id = :id");
        $stmt->bindParam(':officer_id', $officer_id);
        $stmt->bindParam(':id', $complaint_id);
        $stmt->execute();

        header("Location: admin_view_complaints.php");
        exit();
    } catch (Exception $e) {
        die("Error assigning officer: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Assign Officer</title>
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
        <h1>Assign Officer to Complaint #<?= htmlspecialchars($complaint_id) ?></h1>
      </div>

      <form method="POST" class="input-field">
        <label for="officer_id">Select Officer:</label>
        <select id="officer_id" name="officer_id" required>
          <?php foreach ($officers as $officer): ?>
            <option value="<?= $officer['id'] ?>"><?= htmlspecialchars($officer['first_name'] . ' ' . $officer['last_name']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="back-btn">Assign Officer</button>
      </form>
    </div>
  </div>
</body>
</html>