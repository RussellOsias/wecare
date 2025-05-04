<?php
session_start();
require_once 'includes/db_conn.php';

// Ensure only authorized users (admins or officers) can access this page
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'officer')) {
    header("Location: login.php");
    exit();
}

date_default_timezone_set('Asia/Manila');

try {
    // Fetch officer logs from the database
    $stmt = $conn->prepare("
        SELECT 
            ol.*,
            u.first_name, 
            u.last_name
        FROM 
            officer_logs ol
        LEFT JOIN 
            users u ON ol.user_id = u.id
        ORDER BY 
            ol.login_time DESC
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
    <title>Officer Logs</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        /* Styles for the table and filters */
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
            color: rgb(0, 0, 0);
        }
        .filter-btn {
            padding: 8px 16px;
            background: rgb(0, 17, 255);
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
            background: rgb(217, 221, 235);
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #ddd;
            color: rgb(0, 0, 0);
        }
        .log-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            color: rgb(34, 29, 29);
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
        .status {
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
            text-transform: uppercase;
        }
        .success {
            background: #d4edda;
            color: #155724; /* Green status text */
        }
        .warning {
            background: #fff3cd;
            color: rgb(133, 4, 4); /* Yellow status text */
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
                <h2 style="color: #1a73e8;">Officer Logs</h2>
                <div class="filter-container">
                    <div class="filter-group">
                        <input type="text" id="search" class="filter-input" placeholder="Search logs...">
                        <i class="fas fa-search" style="color: #1a73e8;"></i>
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
                                <th>Officer Name</th>
                                <th>Email</th>
                                <th>Login Time</th>
                                <th>Logout Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($log['first_name'] . ' ' . $log['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($log['email']); ?></td>
                                    <td data-timestamp="<?php echo strtotime($log['login_time']); ?>">
                                        <?php echo date("F j, Y, g:i a", strtotime($log['login_time'])); ?>
                                    </td>
                                    <td>
                                        <?php if ($log['logout_time']): ?>
                                            <?php echo date("F j, Y, g:i a", strtotime($log['logout_time'])); ?>
                                        <?php else: ?>
                                            Still Logged In
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status <?php echo $log['logout_time'] ? 'success' : 'warning'; ?>">
                                            <?php echo $log['logout_time'] ? 'Completed' : 'Active'; ?>
                                        </span>
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
                const officerName = row.cells[0].textContent.toLowerCase();
                const email = row.cells[1].textContent.toLowerCase();
                const loginTime = row.cells[2].textContent.toLowerCase();
                const logoutTime = row.cells[3].textContent.toLowerCase();
                if (
                    officerName.includes(searchTerm) ||
                    email.includes(searchTerm) ||
                    loginTime.includes(searchTerm) ||
                    logoutTime.includes(searchTerm)
                ) {
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
                const timestamp = parseInt(row.cells[2].getAttribute('data-timestamp')) * 1000;
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
                    const timestamp = new Date(row.cells[2].getAttribute('data-timestamp') * 1000);
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