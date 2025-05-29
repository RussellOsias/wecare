<?php
session_start();
require_once 'includes/db_conn.php';

// Ensure user is logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle filters and search inputs
$priority_filter = isset($_GET['priority_filter']) ? $_GET['priority_filter'] : 'all';
$search_title_desc = isset($_GET['search_title_desc']) ? trim($_GET['search_title_desc']) : '';
$search_resident = isset($_GET['search_resident']) ? trim($_GET['search_resident']) : '';

$where_clauses = [];
$params = [];

// Status filter (fixed to 'resolved')
$where_clauses[] = "c.status = 'resolved'";

// Priority filter
if (in_array($priority_filter, ['high', 'medium', 'low'])) {
    $where_clauses[] = "c.priority = :priority_filter";
    $params[':priority_filter'] = $priority_filter;
}

// Search title or description
if ($search_title_desc !== '') {
    $where_clauses[] = "(c.title LIKE :search_title_desc OR c.description LIKE :search_title_desc)";
    $params[':search_title_desc'] = "%$search_title_desc%";
}

// Search resident name (first or last)
if ($search_resident !== '') {
    $where_clauses[] = "(u.first_name LIKE :search_resident OR u.last_name LIKE :search_resident)";
    $params[':search_resident'] = "%$search_resident%";
}

$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Fetch resolved complaints
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
            c.created_at,
            c.resolved_at,
            c.resolution_notes
        FROM history_complaints c
        JOIN users u ON c.resident_id = u.id
        LEFT JOIN users o ON c.assigned_officer_id = o.id
        $where_sql
        ORDER BY c.resolved_at DESC
    ");
    $stmt->execute($params);
    $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Error fetching history complaints: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Admin - History of Complaints</title>
  <link rel="stylesheet" href="./assets/css/admin_complaint.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    /* Copying your original styles */
    .filters-wrapper {
      display: flex;
      flex-wrap: wrap;
      gap: 15px;
      margin-bottom: 20px;
      align-items: center;
    }
    .filter-group {
      display: flex;
      flex-direction: column;
    }
    .filter-group label {
      font-weight: 600;
      margin-bottom: 5px;
    }
    input[type="search"] {
      padding: 7px 10px;
      font-size: 1rem;
      border: 1px solid #ccc;
      border-radius: 4px;
      width: 200px;
      max-width: 100%;
    }
    select {
      padding: 7px 10px;
      font-size: 1rem;
      border-radius: 4px;
      border: 1px solid #ccc;
      background: #fff;
    }
    @media (max-width: 900px) {
      table, thead, tbody, th, td, tr {
        display: block;
      }
      thead tr {
        display: none;
      }
      tbody tr {
        margin-bottom: 1.5rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 10px;
      }
      tbody td {
        padding-left: 50%;
        position: relative;
        text-align: left;
        border: none;
        border-bottom: 1px solid #eee;
      }
      tbody td:before {
        position: absolute;
        top: 10px;
        left: 15px;
        width: 45%;
        padding-right: 10px;
        white-space: nowrap;
        font-weight: 600;
        content: attr(data-label);
      }
    }
  </style>
</head>
<body>
  <div class="dashboard-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    <div class="main-content">
      <div class="dashboard-header">
        <h1>History of Resolved Complaints</h1>
      </div>

      <!-- Filters -->
      <form method="GET" action="" class="filters-wrapper" onsubmit="return true;">
        <div class="filter-group">
          <label for="priority-filter">Filter by Priority:</label>
          <select id="priority-filter" name="priority_filter" onchange="this.form.submit()">
            <option value="all" <?= $priority_filter === 'all' ? 'selected' : '' ?>>All</option>
            <option value="high" <?= $priority_filter === 'high' ? 'selected' : '' ?>>High</option>
            <option value="medium" <?= $priority_filter === 'medium' ? 'selected' : '' ?>>Medium</option>
            <option value="low" <?= $priority_filter === 'low' ? 'selected' : '' ?>>Low</option>
          </select>
        </div>
        <div class="filter-group">
          <label for="search-title-desc">Search Title or Description:</label>
          <input
            type="search"
            id="search-title-desc"
            name="search_title_desc"
            placeholder="Search title or description"
            value="<?= htmlspecialchars($search_title_desc) ?>"
            oninput="debounceSubmit(this.form)"
          />
        </div>
        <div class="filter-group">
          <label for="search-resident">Search Resident Name:</label>
          <input
            type="search"
            id="search-resident"
            name="search_resident"
            placeholder="Search resident name"
            value="<?= htmlspecialchars($search_resident) ?>"
            oninput="debounceSubmit(this.form)"
          />
        </div>
        <noscript>
          <button type="submit">Apply Filters</button>
        </noscript>
      </form>

      <!-- Table -->
      <?php if (empty($complaints)): ?>
        <p>No resolved complaints found.</p>
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
              <th>Resolved At</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($complaints as $complaint): ?>
              <tr>
                <td data-label="ID"><?= htmlspecialchars($complaint['id']) ?></td>
                <td data-label="Resident"><?= htmlspecialchars($complaint['resident_first_name'] ?? 'Unknown') . ' ' . htmlspecialchars($complaint['resident_last_name'] ?? 'Unknown') ?></td>
                <td data-label="Title"><?= htmlspecialchars($complaint['title']) ?></td>
                <td data-label="Description"><?= htmlspecialchars($complaint['description']) ?></td>
                <td data-label="Status"><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $complaint['status']))) ?></td>
                <td data-label="Priority"><?= htmlspecialchars(ucfirst($complaint['priority'] ?? 'Not Set')) ?></td>
                <td data-label="Assigned Officer"><?= htmlspecialchars($complaint['officer_first_name'] ?? 'Not Assigned') . ' ' . htmlspecialchars($complaint['officer_last_name'] ?? '') ?></td>
                <td data-label="Resolved At"><?= htmlspecialchars(date('F j, Y g:i A', strtotime($complaint['resolved_at']))) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

  <script>
    // Debounce function for search inputs
    let debounceTimeout;
    function debounceSubmit(form) {
      clearTimeout(debounceTimeout);
      debounceTimeout = setTimeout(() => {
        form.submit();
      }, 500);
    }
  </script>
</body>
</html>s