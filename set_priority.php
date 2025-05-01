<?php
session_start();
require_once 'includes/db_conn.php';

// Admin check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$complaint_id = $_GET['id'];

// Fetch current complaint with resident info
try {
    $stmt = $conn->prepare("SELECT c.*, u.id as resident_id FROM complaints c JOIN users u ON c.resident_id = u.id WHERE c.id = ?");
    $stmt->execute([$complaint_id]);
    $complaint = $stmt->fetch();
    
    if (!$complaint) {
        die("Complaint not found.");
    }
} catch (PDOException $e) {
    die("Error fetching complaint: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $priority = $_POST['priority'];
    
    if ($complaint['priority'] !== $priority) {
        try {
            $conn->beginTransaction();
            
            // Update priority
            $stmt = $conn->prepare("UPDATE complaints SET priority = ? WHERE id = ?");
            $stmt->execute([$priority, $complaint_id]);
            
            // Get admin's details who made the change
            $admin_stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
            $admin_stmt->execute([$_SESSION['user_id']]);
            $admin = $admin_stmt->fetch();
            $admin_name = $admin['first_name'] . ' ' . $admin['last_name'];
            
            // Log activity with admin name
            $action = "$admin_name changed priority from {$complaint['priority']} to $priority for complaint #$complaint_id";
            
            $log_stmt = $conn->prepare("INSERT INTO admin_activity_logs 
                (admin_id, activity_type, action, user_affected_id) 
                VALUES (?, ?, ?, ?)");
            
            $log_stmt->execute([
                $_SESSION['user_id'],
                'Complaint Priority',  // Specific activity type
                $action,
                $complaint['resident_id']  // The resident who made the complaint
            ]);
            
            $conn->commit();
            
            header("Location: admin_view_complaints.php");
            exit();
        } catch (PDOException $e) {
            $conn->rollBack();
            die("Error: " . $e->getMessage());
        }
    } else {
        echo "<script>alert('No priority change detected.');</script>";
    }
}
?>

<!-- Your exact original HTML (unchanged) -->
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
        <h1>Set Priority for Complaint #<?= htmlspecialchars($complaint_id) ?></h1>
      </div>

      <form method="POST" class="input-field">
        <label for="priority">Priority:</label>
        <select id="priority" name="priority" required>
          <option value="low" <?= $complaint['priority'] === 'low' ? 'selected' : '' ?>>Low</option>
          <option value="medium" <?= $complaint['priority'] === 'medium' ? 'selected' : '' ?>>Medium</option>
          <option value="high" <?= $complaint['priority'] === 'high' ? 'selected' : '' ?>>High</option>
        </select>
        <button type="submit" class="back-btn">Set Priority</button>
      </form>
    </div>
  </div>
</body>
</html>