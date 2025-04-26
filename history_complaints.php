<?php
session_start();
require_once 'includes/db_conn.php';

// Ensure the user is logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch resolved complaints
try {
    $stmt = $conn->query("
        SELECT 
            hc.id, 
            u.first_name AS resident_first_name, 
            u.last_name AS resident_last_name, 
            o.first_name AS officer_first_name, 
            o.last_name AS officer_last_name, 
            hc.title, 
            hc.description, 
            hc.priority, 
            hc.resolution_notes, 
            hc.personnel_involved, 
            hc.resolved_at 
        FROM history_complaints hc 
        JOIN users u ON hc.resident_id = u.id 
        JOIN users o ON hc.resolved_by = o.id
        ORDER BY hc.resolved_at DESC
    ");
    $resolved_complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error fetching resolved complaints: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Resolved Complaints</title>
  <link rel="stylesheet" href="./assets/css/admin_complaint.css">
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
        <li><a href="/history_complaints.php"><i class="fas fa-history"></i> Resolved Complaints</a></li>
      </ul>
      <button class="logout-btn" onclick="window.location.href='/logout.php'">Logout</button>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <div class="dashboard-header">
        <h1>Resolved Complaints</h1>
      </div>

      <?php if (empty($resolved_complaints)): ?>
        <p>No resolved complaints found.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Resident</th>
              <th>Title</th>
              <th>Description</th>
              <th>Priority</th>
              <th>Resolved By</th>
              <th>Resolution Notes</th>
              <th>Personnel Involved</th>
              <th>Resolved At</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($resolved_complaints as $complaint): ?>
              <tr>
                <td><?= htmlspecialchars($complaint['id']) ?></td>
                <td><?= htmlspecialchars($complaint['resident_first_name'] . ' ' . $complaint['resident_last_name']) ?></td>
                <td><?= htmlspecialchars($complaint['title']) ?></td>
                <td><?= htmlspecialchars($complaint['description']) ?></td>
                <td><?= htmlspecialchars($complaint['priority']) ?></td>
                <td><?= htmlspecialchars($complaint['officer_first_name'] . ' ' . $complaint['officer_last_name']) ?></td>
                <td><?= htmlspecialchars($complaint['resolution_notes']) ?></td>
                <td><?= htmlspecialchars($complaint['personnel_involved']) ?></td>
                <td><?= htmlspecialchars($complaint['resolved_at']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>