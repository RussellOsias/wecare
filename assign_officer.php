<?php
session_start();
require_once 'includes/db_conn.php';

// Ensure the user is logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get the complaint ID from the URL
$complaint_id = $_GET['id'] ?? null;
if (!$complaint_id) {
    die("Complaint ID is required.");
}

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
    $officer_id = $_POST['officer_id'] === '' ? null : $_POST['officer_id'];

    try {
        $conn->beginTransaction();

        // Update complaint assignment, allow NULL if blank
        $stmt = $conn->prepare("UPDATE complaints SET assigned_officer_id = :officer_id, status = 'in_progress' WHERE id = :id");
        $stmt->bindParam(':officer_id', $officer_id);
        $stmt->bindParam(':id', $complaint_id);
        $stmt->execute();

        // Get officer's name for logging (handle null/blank)
        if ($officer_id) {
            $officer_stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
            $officer_stmt->execute([$officer_id]);
            $officer = $officer_stmt->fetch(PDO::FETCH_ASSOC);
            $officer_name = $officer['first_name'] . ' ' . $officer['last_name'];
        } else {
            $officer_name = '(no officer assigned)';
        }

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
            'complaint_assignment',
            $action,
            $complaint['resident_id']
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Assign Officer</title>
  <link rel="stylesheet" href="./assets/css/style.css" />
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    /* Style only select and button */
    form.input-field select {
      padding: 8px 12px;
      border-radius: 5px;
      border: 1.5px solid #3498db;
      font-size: 1rem;
      color: #2c3e50;
      background-color: white;
      outline: none;
      transition: border-color 0.3s ease;
      width: 100%;
      max-width: 300px;
    }
    form.input-field select:focus {
      border-color: #2980b9;
      box-shadow: 0 0 5px #2980b9;
    }

    form.input-field button.back-btn {
      margin-top: 20px;
      padding: 10px 18px;
      background-color: #3498db;
      border: none;
      border-radius: 5px;
      color: white;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 0.3s ease;
      width: 100%;
      max-width: 180px;
    }
    form.input-field button.back-btn:hover {
      background-color: #2980b9;
    }
  </style>
</head>
<body>
  <div class="dashboard-wrapper">
     <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
      <div class="dashboard-header">
        <h1>Assign Officer to Complaint #<?= htmlspecialchars($complaint_id) ?></h1>
      </div>

      <form method="POST" class="input-field" autocomplete="off">
        <label for="officer_id">Select Officer:</label>
        <select id="officer_id" name="officer_id">
          <option value="" <?= $complaint['assigned_officer_id'] === null ? 'selected' : '' ?>>-- No Officer Assigned --</option>
          <?php foreach ($officers as $officer): ?>
            <option value="<?= $officer['id'] ?>" <?= $complaint['assigned_officer_id'] == $officer['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($officer['first_name'] . ' ' . $officer['last_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="back-btn">Assign Officer</button>
      </form>
    </div>
  </div>
</body>
</html>
