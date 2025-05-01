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

// Fetch complaint details with resident information
try {
    $stmt = $conn->prepare("SELECT c.*, u.id as resident_id FROM complaints c JOIN users u ON c.resident_id = u.id WHERE c.id = ?");
    $stmt->execute([$complaint_id]);
    $complaint = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$complaint) {
        die("Complaint not found.");
    }
} catch (Exception $e) {
    die("Error fetching complaint: " . $e->getMessage());
}

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
        $conn->beginTransaction();
        
        // Update complaint assignment
        $stmt = $conn->prepare("UPDATE complaints SET assigned_officer_id = :officer_id, status = 'in_progress' WHERE id = :id");
        $stmt->bindParam(':officer_id', $officer_id);
        $stmt->bindParam(':id', $complaint_id);
        $stmt->execute();
        
        // Get officer's name for logging
        $officer_stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
        $officer_stmt->execute([$officer_id]);
        $officer = $officer_stmt->fetch(PDO::FETCH_ASSOC);
        $officer_name = $officer['first_name'] . ' ' . $officer['last_name'];
        
        // Get admin's details who made the assignment
        $admin_stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
        $admin_stmt->execute([$_SESSION['user_id']]);
        $admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);
        $admin_name = $admin['first_name'] . ' ' . $admin['last_name'];
        
        // Log activity with admin name
        $action = "$admin_name assigned officer $officer_name to complaint #$complaint_id";
        
        $log_stmt = $conn->prepare("INSERT INTO admin_activity_logs 
            (admin_id, activity_type, action, user_affected_id) 
            VALUES (?, ?, ?, ?)");
        
        $log_stmt->execute([
            $_SESSION['user_id'],
            'complaint_assignment',  // Specific activity type
            $action,
            $complaint['resident_id']  // The resident who made the complaint
        ]);
        
        $conn->commit();
        
        header("Location: admin_view_complaints.php");
        exit();
    } catch (Exception $e) {
        $conn->rollBack();
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
        <img src="../images/logo.png" alt="Logo" class="logo">
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