<?php
session_start();
require_once 'includes/db_conn.php';

// Ensure only officers can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

date_default_timezone_set('Asia/Manila');

try {
    // Fetch activity logs with officer names and affected user names
    $stmt = $conn->prepare("
        SELECT 
            oal.*,
            officer.first_name as officer_first_name,
            officer.last_name as officer_last_name,
            affected.first_name as affected_first_name,
            affected.last_name as affected_last_name,
            oal.user_affected_id as affected_id
        FROM 
            officer_activity_logs oal
        LEFT JOIN 
            users officer ON oal.officer_id = officer.id
        LEFT JOIN 
            users affected ON oal.user_affected_id = affected.id
        ORDER BY 
            oal.timestamp DESC
    ");
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching officer logs: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Officer Activity Logs</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin_activity_logs.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <main class="main-content">
            <div class="container">
                <h2 style="color: #1a73e8;">Officer Activity Logs</h2>
                <div class="filter-container">
                    <div class="filter-group">
                        <input type="text" id="search" class="filter-input" placeholder="Search logs...">
                        <i class="fas fa-search" style="color: #1a73e8;"></i>
                    </div>
                    <div class="filter-group">
                        <select id="activityFilter" class="filter-input">
                            <option value="all">All Activities</option>
                            <option value="complaint_resolution">Complaint Resolution</option>
                            <option value="profile_update">Profile Update</option>
                            <option value="personnel_assignment">Personnel Assignment</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <select id="timeFilter" class="filter-input">
                            <option value="all">All Time</option>
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="7days">Last 7 Days</option>
                            <option value="30days">Last 30 Days</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    <div class="filter-group date-range" id="dateRange" style="display: none;">
                        <input type="date" id="startDate" class="filter-input">
                        <input type="date" id="endDate" class="filter-input">
                        <i class="fas fa-calendar-alt" style="color: #1a73e8;"></i>
                    </div>
                </div>
                <div class="table-container">
                    <table class="log-table">
                        <thead>
                            <tr>
                                <th>Officer</th>
                                <th>Activity Type</th>
                                <th>Action</th>
                                <th>Affected User</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['officer_first_name'] . ' ' . $log['officer_last_name']); ?></td>
                                    <td>
                                        <span class="activity-type <?php echo str_replace('_', '-', $log['activity_type']); ?>">
                                            <?php echo str_replace('_', ' ', $log['activity_type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($log['action']); ?></td>
                                    <td>
                                        <?php if ($log['user_affected_id']): ?>
                                            <?php if (!empty($log['affected_first_name'])): ?>
                                                <?php echo htmlspecialchars($log['affected_first_name'] . ' ' . $log['affected_last_name']); ?>
                                            <?php else: ?>
                                                User #<?php echo htmlspecialchars($log['user_affected_id']); ?>
                                                <?php if ($log['activity_type'] === 'user_deletion'): ?>
                                                    (Deleted)
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td data-timestamp="<?php echo strtotime($log['timestamp']); ?>">
                                        <?php echo date("F j, Y, g:i a", strtotime($log['timestamp'])); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    <script>
        // Search functionality
        document.getElementById('search').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('.log-table tbody tr');
            rows.forEach(row => {
                const officer = row.cells[0].textContent.toLowerCase();
                const action = row.cells[2].textContent.toLowerCase();
                const affectedUser = row.cells[3].textContent.toLowerCase();
                if (officer.includes(searchTerm) || action.includes(searchTerm) || affectedUser.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Activity type filtering
        document.getElementById('activityFilter').addEventListener('change', function() {
            const filter = this.value;
            const rows = document.querySelectorAll('.log-table tbody tr');
            rows.forEach(row => {
                const activityTypeSpan = row.cells[1].querySelector('.activity-type');
                const activityType = Array.from(activityTypeSpan.classList)
                                        .find(cls => cls !== 'activity-type');
                if (filter === 'all' || activityType.replace('-', '_') === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Date filtering
        document.getElementById('timeFilter').addEventListener('change', function() {
            const filter = this.value;
            const dateRange = document.getElementById('dateRange');
            const rows = document.querySelectorAll('.log-table tbody tr');
            dateRange.style.display = filter === 'custom' ? 'flex' : 'none';
            rows.forEach(row => {
                const timestamp = parseInt(row.cells[4].getAttribute('data-timestamp')) * 1000;
                const now = Date.now();
                let startDate, endDate;
                switch(filter) {
                    case 'today':
                        startDate = new Date().setHours(0,0,0,0);
                        endDate = new Date().setHours(23,59,59,999);
                        break;
                    case 'yesterday':
                        startDate = new Date(new Date().setDate(new Date().getDate()-1)).setHours(0,0,0,0);
                        endDate = new Date(new Date().setDate(new Date().getDate()-1)).setHours(23,59,59,999);
                        break;
                    case '7days':
                        startDate = new Date(now - 7*24*60*60*1000);
                        endDate = now;
                        break;
                    case '30days':
                        startDate = new Date(now - 30*24*60*60*1000);
                        endDate = now;
                        break;
                    default:
                        row.style.display = '';
                        return;
                }
                if (timestamp >= startDate && timestamp <= endDate) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Custom date range filtering
        document.querySelectorAll('#dateRange input').forEach(input => {
            input.addEventListener('change', function() {
                const startDate = document.getElementById('startDate').valueAsDate;
                const endDate = document.getElementById('endDate').valueAsDate;
                const rows = document.querySelectorAll('.log-table tbody tr');
                if (!startDate || !endDate) return;
                rows.forEach(row => {
                    const timestamp = new Date(row.cells[4].getAttribute('data-timestamp') * 1000);
                    if (timestamp >= startDate && timestamp <= endDate) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>