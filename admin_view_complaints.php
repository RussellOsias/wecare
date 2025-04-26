<?php
session_start();
require_once 'includes/db_conn.php'; // Database connection file

// Ensure the user is logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all complaints from the database
try {
    $stmt = $conn->query("SELECT 
        c.id, 
        u.first_name, 
        u.last_name, 
        c.title, 
        c.description, 
        c.status, 
        c.priority, 
        c.created_at 
        FROM complaints c 
        JOIN users u ON c.resident_id = u.id 
        WHERE c.status = 'pending' OR c.status = 'in_progress'");
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error fetching complaints: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - View Complaints</title>
  <link rel="stylesheet" href="./assets/css/style.css">
</head>
<body>
  <div class="dashboard-wrapper">
    <!-- Sidebar -->
    <div class="sidebar">
      <div class="logo-container">
        <img src="../assets/images/logo.png" alt="Logo" class="logo">
        <h2>WeCare</h2>
      </div>
      <ul>
        <li><a href="/dashboard.php"><i class="fas fa-home"></i> Home</a></li>
        <li><a href="/admin_view_complaints.php"><i class="fas fa-exclamation-circle"></i> Complaints</a></li>
        <li><a href="#"><i class="fas fa-users"></i> Officers</a></li>
      </ul>
      <button class="logout-btn" onclick="window.location.href='/logout.php'">Logout</button>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <div class="dashboard-header">
        <h1>Pending and In-Progress Complaints</h1>
      </div>

      <?php if (empty($complaints)): ?>
        <p>No complaints found.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Resident</th>
              <th>Title</th>
              <th>Description</th>
              <th>Status</th>
              <th>Priority</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($complaints as $complaint): ?>
              <tr>
                <td><?= htmlspecialchars($complaint['id']) ?></td>
                <td><?= htmlspecialchars($complaint['first_name'] ?? 'Unknown') . ' ' . htmlspecialchars($complaint['last_name'] ?? 'Unknown') ?></td>
                <td><?= htmlspecialchars($complaint['title']) ?></td>
                <td><?= htmlspecialchars($complaint['description']) ?></td>
                <td><?= htmlspecialchars($complaint['status']) ?></td>
                <td><?= htmlspecialchars($complaint['priority'] ?? 'Not Set') ?></td>
                <td>
                  <a href="set_priority.php?id=<?= $complaint['id'] ?>" class="back-btn">Set Priority</a>
                  <a href="assign_officer.php?id=<?= $complaint['id'] ?>" class="back-btn">Assign Officer</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>