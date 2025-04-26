<?php
session_start();
require_once 'includes/db_conn.php'; // Database connection file

// Ensure the user is logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle priority filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where_clause = '';
switch ($filter) {
    case 'high':
        $where_clause = "AND c.priority = 'high'";
        break;
    case 'medium':
        $where_clause = "AND c.priority = 'medium'";
        break;
    case 'low':
        $where_clause = "AND c.priority = 'low'";
        break;
    default:
        $where_clause = "";
}

// Fetch all complaints from the database
try {
    $stmt = $conn->prepare("
        SELECT 
            c.id, 
            u.first_name AS resident_first_name, 
            u.last_name AS resident_last_name, 
            o.first_name AS officer_first_name, 
            o.last_name AS officer_last_name, 
            c.title, 
            c.description, 
            c.status, 
            c.priority, 
            c.created_at 
        FROM complaints c 
        JOIN users u ON c.resident_id = u.id 
        LEFT JOIN users o ON c.assigned_officer_id = o.id 
        WHERE (c.status = 'pending' OR c.status = 'in_progress') $where_clause
        ORDER BY 
            CASE 
                WHEN c.priority = 'high' THEN 1 
                WHEN c.priority = 'medium' THEN 2 
                WHEN c.priority = 'low' THEN 3 
                ELSE 4 
            END
    ");
    $stmt->execute();
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
        <li><a href="#"><i class="fas fa-users"></i> Officers</a></li>
      </ul>
      <button class="logout-btn" onclick="window.location.href='/logout.php'">Logout</button>
    </div>

    <!-- Main Content -->
    <div class="main-content">
      <div class="dashboard-header">
        <h1>Pending and In-Progress Complaints</h1>
        <div class="filter-dropdown">
          <label for="priority-filter">Filter by Priority:</label>
          <select id="priority-filter" onchange="location = this.value;">
            <option value="?filter=all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
            <option value="?filter=high" <?= $filter === 'high' ? 'selected' : '' ?>>High</option>
            <option value="?filter=medium" <?= $filter === 'medium' ? 'selected' : '' ?>>Medium</option>
            <option value="?filter=low" <?= $filter === 'low' ? 'selected' : '' ?>>Low</option>
          </select>
        </div>
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
              <th>Assigned Officer</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($complaints as $complaint): ?>
              <tr>
                <td><?= htmlspecialchars($complaint['id']) ?></td>
                <td><?= htmlspecialchars($complaint['resident_first_name'] ?? 'Unknown') . ' ' . htmlspecialchars($complaint['resident_last_name'] ?? 'Unknown') ?></td>
                <td><?= htmlspecialchars($complaint['title']) ?></td>
                <td><?= htmlspecialchars($complaint['description']) ?></td>
                <td><?= htmlspecialchars($complaint['status']) ?></td>
                <td><?= htmlspecialchars($complaint['priority'] ?? 'Not Set') ?></td>
                <td><?= htmlspecialchars($complaint['officer_first_name'] ?? 'Not Assigned') . ' ' . htmlspecialchars($complaint['officer_last_name'] ?? '') ?></td>
                <td>
                  <div class="action-buttons">
                    <a href="set_priority.php?id=<?= $complaint['id'] ?>" class="btn btn-priority">Set Priority</a>
                    <a href="assign_officer.php?id=<?= $complaint['id'] ?>" class="btn btn-officer">Assign Officer</a>
                  </div>
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