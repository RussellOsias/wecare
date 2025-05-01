<?php
session_start();
require_once 'includes/db_conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

date_default_timezone_set('Asia/Manila');

try {
    // Fetch activity logs with admin names and affected user names
    $stmt = $conn->prepare("
        SELECT 
            al.*,
            admin.first_name as admin_first_name,
            admin.last_name as admin_last_name,
            affected.first_name as affected_first_name,
            affected.last_name as affected_last_name,
            al.user_affected_id as affected_id
        FROM 
            admin_activity_logs al
        LEFT JOIN 
            users admin ON al.admin_id = admin.id
        LEFT JOIN 
            users affected ON al.user_affected_id = affected.id
        ORDER BY 
            al.timestamp DESC
    ");
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching admin logs: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Activity Logs</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        .main-content {
            padding: 20px;
            margin-left: 250px;
            transition: margin-left 0.3s;
        }

        .filter-container {
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-input {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #000;
        }

        .filter-btn {
            padding: 8px 16px;
            background: #0011ff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .log-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .log-table th {
            position: sticky;
            top: 0;
            background: #d9ddeb;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #ddd;
            color: #000;
        }

        .log-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            color: #221d1d;
        }

        .log-table tr:hover {
            background-color: #f8f9fa;
        }

        .table-container {
            max-height: 600px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .activity-type {
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
            text-transform: capitalize;
        }

        .profile-update {
            background: #d1ecf1;
            color: #0c5460;
        }

        .user-creation {
            background: #d4edda;
            color: #155724;
        }

        .user-update {
            background: #cce5ff;
            color: #004085;
        }

        .user-deletion {
            background: #f8d7da;
            color: #721c24;
        }

        .complaint-assignment {
            background: #fff3cd;
            color: #856404;
        }

        .complaint-priority {
            background: #e2e3e5;
            color: #383d41;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }
            
            .filter-container {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="container">
                <h2 style="color: #1a73e8;">Admin Activity Logs</h2>
                
                <div class="filter-container">
                    <div class="filter-group">
                        <input type="text" id="search" class="filter-input" placeholder="Search logs...">
                        <i class="fas fa-search" style="color: #1a73e8;"></i>
                    </div>
                    
                    <div class="filter-group">
                        <select id="activityFilter" class="filter-input">
                            <option value="all">All Activities</option>
                            <option value="user_creation">User Creation</option>
                            <option value="user_update">User Update</option>
                            <option value="profile_update">Profile Update</option> <!-- Added this line -->
                            <option value="user_deletion">User Deletion</option>
                            <option value="complaint_assignment">Complaint Assignment</option>
                            <option value="complaint_priority">Complaint Priority</option>
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
                                <th>Admin</th>
                                <th>Activity Type</th>
                                <th>Action</th>
                                <th>Affected User</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['admin_first_name'] . ' ' . $log['admin_last_name']); ?></td>
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
                const admin = row.cells[0].textContent.toLowerCase();
                const action = row.cells[2].textContent.toLowerCase();
                const affectedUser = row.cells[3].textContent.toLowerCase();
                
                if (admin.includes(searchTerm) || action.includes(searchTerm) || affectedUser.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Activity type filtering - fixed version
        document.getElementById('activityFilter').addEventListener('change', function() {
            const filter = this.value;
            const rows = document.querySelectorAll('.log-table tbody tr');
            
            rows.forEach(row => {
                // Get the activity type from the class name of the span
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