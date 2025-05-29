<?php
session_start();
require_once 'includes/db_conn.php';

// Get all notifications for current user
$stmt = $conn->prepare("SELECT * FROM notifications 
                       WHERE user_id = :user_id 
                       ORDER BY created_at DESC");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark all as read
$stmt = $conn->prepare("UPDATE notifications SET is_read = 1 
                       WHERE user_id = :user_id AND is_read = 0");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Notifications</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin_activity_logs.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        /* Notification-specific styles */
        .notification-type {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 500;
            text-transform: capitalize;
        }
        
        .notification-type.security {
            background-color: #fce8e6;
            color: #d93025;
        }
        
        .notification-type.system {
            background-color: #e8f0fe;
            color: #1a73e8;
        }
        
        .notification-type.update {
            background-color: #e6f4ea;
            color: #188038;
        }
        
        .unread-notification {
            background-color: #f8f9fa;
            border-left: 3px solid #1a73e8;
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="container">
                <h2 style="color: #1a73e8;">Security Notifications</h2>
                
                <div class="filter-container">
                    <div class="filter-group">
                        <input type="text" id="search" class="filter-input" placeholder="Search notifications...">
                        <i class="fas fa-search" style="color: #1a73e8;"></i>
                    </div>
                    
                    <div class="filter-group">
                        <select id="typeFilter" class="filter-input">
                            <option value="all">All Types</option>
                            <option value="security">Security Alerts</option>
                            <option value="system">System Updates</option>
                            <option value="update">Profile Updates</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <select id="statusFilter" class="filter-input">
                            <option value="all">All Statuses</option>
                            <option value="unread">Unread Only</option>
                            <option value="read">Read Only</option>
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
                                <th>Type</th>
                                <th>Message</th>
                                <th>Status</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notifications as $notification): 
                                $type = strtolower(explode(':', $notification['message'])[0]);
                                $type = str_replace(' ', '-', $type);
                                if (!in_array($type, ['security', 'system', 'update'])) {
                                    $type = 'system';
                                }
                            ?>
                                <tr class="<?php echo $notification['is_read'] ? '' : 'unread-notification'; ?>">
                                    <td>
                                        <span class="notification-type <?php echo $type; ?>">
                                            <?php echo $type; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($notification['message']); ?></td>
                                    <td>
                                        <?php if ($notification['is_read']): ?>
                                            <span class="status-badge read">Read</span>
                                        <?php else: ?>
                                            <span class="status-badge unread">New</span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-timestamp="<?php echo strtotime($notification['created_at']); ?>">
                                        <?php echo date("F j, Y, g:i a", strtotime($notification['created_at'])); ?>
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
                const message = row.cells[1].textContent.toLowerCase();
                
                if (message.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Type filtering
        document.getElementById('typeFilter').addEventListener('change', function() {
            const filter = this.value;
            const rows = document.querySelectorAll('.log-table tbody tr');
            
            rows.forEach(row => {
                const typeSpan = row.cells[0].querySelector('.notification-type');
                const type = Array.from(typeSpan.classList)
                                 .find(cls => cls !== 'notification-type');
                
                if (filter === 'all' || type === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Status filtering
        document.getElementById('statusFilter').addEventListener('change', function() {
            const filter = this.value;
            const rows = document.querySelectorAll('.log-table tbody tr');
            
            rows.forEach(row => {
                const isUnread = row.classList.contains('unread-notification');
                
                if (filter === 'all' || 
                   (filter === 'unread' && isUnread) || 
                   (filter === 'read' && !isUnread)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Date filtering (same as admin logs)
        document.getElementById('timeFilter').addEventListener('change', function() {
            const filter = this.value;
            const dateRange = document.getElementById('dateRange');
            const rows = document.querySelectorAll('.log-table tbody tr');
            
            dateRange.style.display = filter === 'custom' ? 'flex' : 'none';

            rows.forEach(row => {
                const timestamp = parseInt(row.cells[3].getAttribute('data-timestamp')) * 1000;
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
                    const timestamp = new Date(row.cells[3].getAttribute('data-timestamp') * 1000);
                    
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