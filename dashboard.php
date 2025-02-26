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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link rel="stylesheet" href="./assets/css/index.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
  <div class="dashboard-wrapper">

    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content">
      <header class="dashboard-header">
        <h1>Welcome to Your Dashboard</h1>
        <div class="user-info">
          <span><i class="fas fa-user-circle"></i><?php echo htmlspecialchars($_SESSION['email']) ?></span>
        </div>
      </header>
      <section class="dashboard-stats">

        <button class="card" onclick="window.location.href='#'">
            <i class="fas fa-users"></i> <br> Users: 150
        </button>

        <button class="card" onclick="window.location.href='#'">
            <i class="fas fa-envelope"></i> <br> New Messages: 5
        </button>

        <button class="card" onclick="window.location.href='#'">
            <i class="fas fa-tasks"></i> <br> Tasks Completed: 10
        </button>

      </section>
    
        </div>
      </section>
    </main>
  </div>
  
</body>
</html>