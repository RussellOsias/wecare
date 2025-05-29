<?php
require_once 'includes/db_conn.php';
session_start();

if ($_SESSION['role'] !== 'admin') exit("Unauthorized");

// Copy all filter logic from history_complaints.php here
// ...

header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="history_complaints.xls"');

echo "<table border='1'>
        <tr>
            <th>ID</th><th>Resident</th><th>Title</th><th>Description</th><th>Status</th><th>Priority</th><th>Resolved At</th>
        </tr>";
foreach ($complaints as $row) {
    echo "<tr>
            <td>{$row['id']}</td>
            <td>" . htmlspecialchars($row['resident_first_name'] . ' ' . $row['resident_last_name']) . "</td>
            <td>" . htmlspecialchars($row['title']) . "</td>
            <td>" . nl2br(htmlspecialchars($row['description'])) . "</td>
            <td>" . ucfirst(str_replace('_', ' ', $row['status'])) . "</td>
            <td>" . ucfirst($row['priority'] ?? 'Not Set') . "</td>
            <td>" . htmlspecialchars($row['resolved_at']) . "</td>
          </tr>";
}
echo "</table>";