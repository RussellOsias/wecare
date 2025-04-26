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
</head>
<body>
  <h2>Set Priority for Complaint #<?= htmlspecialchars($complaint_id) ?></h2>
  <form method="POST">
    <label for="priority">Priority:</label>
    <select id="priority" name="priority" required>
      <option value="low">Low</option>
      <option value="medium">Medium</option>
      <option value="high">High</option>
    </select><br><br>
    <button type="submit">Set Priority</button>
  </form>
</body>
</html>