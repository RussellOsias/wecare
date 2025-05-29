<?php
session_start();
require_once 'includes/db_conn.php'; // Database connection file

// Ensure the user is logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle delete request
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);

    // Get the complaint title and resident name before deleting
    $stmt = $conn->prepare("SELECT c.title, u.first_name, u.last_name FROM complaints c JOIN users u ON c.resident_id = u.id WHERE c.id = ?");
    $stmt->execute([$delete_id]);
    $complaint = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($complaint) {
        $title = $complaint['title'];
        $resident_name = $complaint['first_name'] . ' ' . $complaint['last_name'];

        // Delete the complaint
        $stmt = $conn->prepare("DELETE FROM complaints WHERE id = ?");
        $stmt->execute([$delete_id]);

        // Set flash message
        $_SESSION['delete_success'] = "Resident complaint titled \"{$title}\" by {$resident_name} deleted successfully.";

        // Redirect to avoid resubmission
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
        exit();
    } else {
        // If complaint not found, just redirect silently or show error
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
        exit();
    }
}

// Handle filters and search
$filter_priority = isset($_GET['filter_priority']) ? $_GET['filter_priority'] : 'all';
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : 'pending'; // default to pending and in_progress?
$search_title_desc = isset($_GET['search_title_desc']) ? trim($_GET['search_title_desc']) : '';
$search_resident = isset($_GET['search_resident']) ? trim($_GET['search_resident']) : '';

// Build WHERE clauses dynamically
$where_clauses = [];
$params = [];

// Status filter - allow 'all' or specific
if ($filter_status !== 'all') {
    if ($filter_status === 'pending_or_in_progress') {
        $where_clauses[] = "(c.status = 'pending' OR c.status = 'in_progress')";
    } else {
        $where_clauses[] = "c.status = ?";
        $params[] = $filter_status;
    }
} else {
    // if 'all' selected, no status filter needed
}

// Priority filter
if (in_array($filter_priority, ['high', 'medium', 'low'])) {
    $where_clauses[] = "c.priority = ?";
    $params[] = $filter_priority;
}

// Search title or description
if ($search_title_desc !== '') {
    $where_clauses[] = "(c.title LIKE ? OR c.description LIKE ?)";
    $like_search = "%$search_title_desc%";
    $params[] = $like_search;
    $params[] = $like_search;
}

// Search resident name
if ($search_resident !== '') {
    $where_clauses[] = "(u.first_name LIKE ? OR u.last_name LIKE ?)";
    $like_resident = "%$search_resident%";
    $params[] = $like_resident;
    $params[] = $like_resident;
}

// Combine where clauses
$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = 'AND ' . implode(' AND ', $where_clauses);
}

// Fetch complaints
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
        WHERE 1=1
        $where_sql
        ORDER BY 
            CASE 
                WHEN c.priority = 'high' THEN 1 
                WHEN c.priority = 'medium' THEN 2 
                WHEN c.priority = 'low' THEN 3 
                ELSE 4 
            END, c.created_at DESC
    ");
    $stmt->execute($params);
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error fetching complaints: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin - View Complaints</title>
  <link rel="stylesheet" href="./assets/css/admin_complaint.css" />
  <style>
    /* Styles for new UI elements */
    .filter-container {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-bottom: 20px;
      align-items: center;
    }
    .filter-container label {
      font-weight: bold;
    }
    .filter-container select, 
    .filter-container input[type="search"] {
      padding: 6px 8px;
      border: 1px solid #ccc;
      border-radius: 4px;
      min-width: 150px;
    }
    /* Success message style */
    .success-message {
      background-color: #d4edda;
      color: #155724;
      padding: 12px 15px;
      margin-bottom: 15px;
      border-radius: 5px;
      border: 1px solid #c3e6cb;
      font-weight: 600;
    }

    /* Responsive table scroll */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
    }
    th, td {
      padding: 10px;
      border: 1px solid #ddd;
      text-align: left;
      vertical-align: middle;
    }
    th {
      background-color: #f4f4f4;
    }
    .action-buttons a, .action-buttons button {
      margin-right: 5px;
      padding: 6px 10px;
      text-decoration: none;
      border-radius: 4px;
      font-size: 0.9em;
      cursor: pointer;
      border: none;
      display: inline-block;
    }
    .btn-priority {
      background-color: #007bff;
      color: white;
    }
    .btn-officer {
      background-color: #28a745;
      color: white;
    }
    .btn-delete {
      background-color: #dc3545;
      color: white;
    }

    @media (max-width: 700px) {
      .filter-container {
        flex-direction: column;
        align-items: stretch;
      }
      table, thead, tbody, th, td, tr {
        display: block;
      }
      tr {
        margin-bottom: 15px;
      }
      th {
        background: #f4f4f4;
        font-weight: bold;
      }
      td {
        position: relative;
        padding-left: 50%;
        text-align: right;
        border: none;
        border-bottom: 1px solid #ddd;
      }
      td:before {
        content: attr(data-label);
        position: absolute;
        left: 10px;
        width: 45%;
        padding-left: 10px;
        font-weight: bold;
        text-align: left;
      }
      .action-buttons a, .action-buttons button {
        width: 100%;
        margin: 5px 0;
      }
    }
  </style>
</head>
<body>
  <div class="dashboard-wrapper">
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
      <h1>Manage Complaints</h1>

      <!-- Flash message for delete success -->
      <?php if (isset($_SESSION['delete_success'])): ?>
        <div class="success-message">
          <?= htmlspecialchars($_SESSION['delete_success']) ?>
        </div>
        <?php unset($_SESSION['delete_success']); ?>
      <?php endif; ?>

      <!-- Filters and search -->
      <form method="GET" action="" class="filter-container" role="search" aria-label="Complaint Filters and Search">
        <label for="filter_status">Status:</label>
        <select name="filter_status" id="filter_status" onchange="this.form.submit()">
          <option value="pending_or_in_progress" <?= ($filter_status === 'pending_or_in_progress') ? 'selected' : '' ?>>Pending & In Progress</option>
          <option value="pending" <?= ($filter_status === 'pending') ? 'selected' : '' ?>>Pending</option>
          <option value="in_progress" <?= ($filter_status === 'in_progress') ? 'selected' : '' ?>>In Progress</option>
          <option value="resolved" <?= ($filter_status === 'resolved') ? 'selected' : '' ?>>Resolved</option>
          <option value="all" <?= ($filter_status === 'all') ? 'selected' : '' ?>>All</option>
        </select>

        <label for="filter_priority">Priority:</label>
        <select name="filter_priority" id="filter_priority" onchange="this.form.submit()">
          <option value="all" <?= $filter_priority === 'all' ? 'selected' : '' ?>>All</option>
          <option value="high" <?= $filter_priority === 'high' ? 'selected' : '' ?>>High</option>
          <option value="medium" <?= $filter_priority === 'medium' ? 'selected' : '' ?>>Medium</option>
          <option value="low" <?= $filter_priority === 'low' ? 'selected' : '' ?>>Low</option>
        </select>

        <label for="search_title_desc">Search Title/Description:</label>
        <input
          type="search"
          name="search_title_desc"
          id="search_title_desc"
          placeholder="Search title or description"
          value="<?= htmlspecialchars($search_title_desc) ?>"
          onkeydown="if(event.key==='Enter'){this.form.submit();}"
          aria-label="Search complaints by title or description"
        />

        <label for="search_resident">Search Resident Name:</label>
        <input
          type="search"
          name="search_resident"
          id="search_resident"
          placeholder="Search resident name"
          value="<?= htmlspecialchars($search_resident) ?>"
          onkeydown="if(event.key==='Enter'){this.form.submit();}"
          aria-label="Search complaints by resident name"
        />

        <button type="submit" style="padding: 6px 10px; border-radius:4px; background-color:#007bff; color:#fff; border:none; cursor:pointer;">
          Search
        </button>
      </form>

      <?php if (empty($complaints)): ?>
        <p>No complaints found.</p>
      <?php else: ?>
        <table role="table" aria-label="Complaints Table">
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
                <td data-label="ID"><?= htmlspecialchars($complaint['id']) ?></td>
                <td data-label="Resident"><?= htmlspecialchars($complaint['resident_first_name'] ?? 'Unknown') . ' ' . htmlspecialchars($complaint['resident_last_name'] ?? 'Unknown') ?></td>
                <td data-label="Title"><?= htmlspecialchars($complaint['title']) ?></td>
                <td data-label="Description"><?= htmlspecialchars($complaint['description']) ?></td>
                <td data-label="Status"><?= htmlspecialchars($complaint['status']) ?></td>
                <td data-label="Priority"><?= htmlspecialchars($complaint['priority'] ?? 'Not Set') ?></td>
                <td data-label="Assigned Officer"><?= htmlspecialchars($complaint['officer_first_name'] ?? 'Not Assigned') . ' ' . htmlspecialchars($complaint['officer_last_name'] ?? '') ?></td>
                <td data-label="Actions">
                  <div class="action-buttons">
                    <a href="set_priority.php?id=<?= $complaint['id'] ?>" class="btn-priority" aria-label="Set priority for complaint <?= htmlspecialchars($complaint['id']) ?>">Set Priority</a>
                    <a href="assign_officer.php?id=<?= $complaint['id'] ?>" class="btn-officer" aria-label="Assign officer for complaint <?= htmlspecialchars($complaint['id']) ?>">Assign Officer</a>
                    <a 
                      href="?delete=<?= $complaint['id'] ?>" 
                      class="btn-delete" 
                      onclick="return confirm('Are you sure you want to delete this complaint?');"
                      aria-label="Delete complaint <?= htmlspecialchars($complaint['id']) ?>"
                    >Delete</a>
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
