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
</head>
<body>
  <h2>Assign Officer to Complaint #<?= htmlspecialchars($complaint_id) ?></h2>
  <form method="POST">
    <label for="officer_id">Select Officer:</label>
    <select id="officer_id" name="officer_id" required>
      <?php foreach ($officers as $officer): ?>
        <option value="<?= $officer['id'] ?>"><?= htmlspecialchars($officer['first_name'] . ' ' . $officer['last_name']) ?></option>
      <?php endforeach; ?>
    </select><br><br>
    <button type="submit">Assign Officer</button>
  </form>
</body>
</html>