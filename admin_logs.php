<?php
session_start();
require_once 'includes/db_conn.php';

// Redirect if not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Set the correct timezone
date_default_timezone_set('America/New_York'); // Replace with your desired timezone

// Fetch admin logs
try {
    $stmt = $conn->prepare("SELECT * FROM admin_logs ORDER BY login_time DESC");
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
    <title>Admin Logs</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="container">
                <h2>Admin Logs</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Login Time</th>
                            <th>Logout Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($log['email']); ?></td>
                                <td>
                                    <?php
                                    // Convert login_time to human-readable format
                                    $login_time = strtotime($log['login_time']);
                                    echo date("F j, Y, g:i a", $login_time);
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    // Check if logout_time exists and convert to human-readable format
                                    if ($log['logout_time']) {
                                        $logout_time = strtotime($log['logout_time']);
                                        echo date("F j, Y, g:i a", $logout_time);
                                    } else {
                                        echo 'Still Logged In';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>