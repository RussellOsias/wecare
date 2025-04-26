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
    $stmt = $conn->query("SELECT c.id, u.first_name, u.last_name, c.title, c.description, c.status, c.priority, c.created_at 
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
</head>
<body>
  <h2>Pending and In-Progress Complaints</h2>
  <table border="1">
    <tr>
      <th>ID</th>
      <th>Resident</th>
      <th>Title</th>
      <th>Description</th>
      <th>Status</th>
      <th>Priority</th>
      <th>Actions</th>
    </tr>
    <?php foreach ($complaints as $complaint): ?>
      <tr>
        <td><?= htmlspecialchars($complaint['id']) ?></td>
        <td><?= htmlspecialchars($complaint['first_name'] . ' ' . $complaint['last_name']) ?></td>
        <td><?= htmlspecialchars($complaint['title']) ?></td>
        <td><?= htmlspecialchars($complaint['description']) ?></td>
        <td><?= htmlspecialchars($complaint['status']) ?></td>
        <td><?= htmlspecialchars($complaint['priority'] ?? 'Not Set') ?></td>
        <td>
          <a href="set_priority.php?id=<?= $complaint['id'] ?>">Set Priority</a> |
          <a href="assign_officer.php?id=<?= $complaint['id'] ?>">Assign Officer</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>