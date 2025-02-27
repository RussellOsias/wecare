<?php
session_start();
require_once 'includes/db_conn.php';
require_once 'includes/authentication.php';

// Check if the user is authenticated via session
if (!isset($_SESSION['user_id'])) {
    // If not, check for token-based authentication
    if (isset($_COOKIE['auth_token'])) {
        if (!authenticateToken($_COOKIE['auth_token'])) {
            header("Location: login.php");
            exit();
        }
    } else {
        header("Location: login.php");
        exit();
    }
}

// Fetch total users and role counts
try {
    $stmt = $conn->query("SELECT COUNT(*) AS total_users FROM users");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    $stmt = $conn->query("SELECT role, COUNT(*) AS count FROM users GROUP BY role");
    $roleCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) {
    die("Error fetching user data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link rel="stylesheet" href="./assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
  <div class="dashboard-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content">
      <header class="dashboard-header">
        <h1>Welcome to Your Dashboard</h1>
        <div class="user-info">
          <span><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['email']); ?></span>
        </div>
      </header>
      <section class="dashboard-stats">
        <a href="manage_users.php" class="card">
          <i class="fas fa-users"></i> 
          <div>
            <span>Total Users</span>
            <strong><?php echo $totalUsers; ?></strong>
          </div>
        </a>
        <div class="card">
          <i class="fas fa-user-shield"></i> 
          <div>
            <span>Admins</span>
            <strong><?php echo $roleCounts['admin'] ?? 0; ?></strong>
          </div>
        </div>
        <div class="card">
          <i class="fas fa-user-tie"></i> 
          <div>
            <span>Officers</span>
            <strong><?php echo $roleCounts['officer'] ?? 0; ?></strong>
          </div>
        </div>
        <div class="card">
          <i class="fas fa-user"></i> 
          <div>
            <span>Residents</span>
            <strong><?php echo $roleCounts['resident'] ?? 0; ?></strong>
          </div>
        </div>
      </section>
    </main>
  </div>
</body>
</html>