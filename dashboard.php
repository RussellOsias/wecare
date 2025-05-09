<?php
session_start();
require_once 'includes/db_conn.php';
require_once 'includes/authentication.php';

// Redirect if not authenticated
if (!isset($_SESSION['user_id'])) {
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

$user_id = $_SESSION['user_id'];

// Fetch user data (name, profile picture, and role)
try {
    $stmt = $conn->prepare("SELECT first_name, last_name, profile_picture, role FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User not found.");
    }
} catch (PDOException $e) {
    die("Error fetching user data: " . $e->getMessage());
}

// Fetch total users and role counts
try {
    // Total users
    $stmt = $conn->query("SELECT COUNT(*) AS total_users FROM users");
    $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    // Users by role
    $stmt = $conn->query("SELECT role, COUNT(*) AS count FROM users GROUP BY role");
    $roleCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Total complaints
    $stmt = $conn->query("SELECT COUNT(*) AS total_complaints FROM complaints");
    $totalComplaints = $stmt->fetch(PDO::FETCH_ASSOC)['total_complaints'];

    // Pending complaints
    $stmt = $conn->query("SELECT COUNT(*) AS pending_complaints FROM complaints WHERE status = 'pending'");
    $pendingComplaints = $stmt->fetch(PDO::FETCH_ASSOC)['pending_complaints'];

    // In-progress complaints
    $stmt = $conn->query("SELECT COUNT(*) AS in_progress_complaints FROM complaints WHERE status = 'in_progress'");
    $inProgressComplaints = $stmt->fetch(PDO::FETCH_ASSOC)['in_progress_complaints'];

    // Resolved complaints (from history_complaints)
    $stmt = $conn->query("SELECT COUNT(*) AS resolved_complaints FROM history_complaints WHERE status = 'resolved'");
    $resolvedComplaints = $stmt->fetch(PDO::FETCH_ASSOC)['resolved_complaints'];
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<style>
  
    .dashboard-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px;
      background: rgba(255, 255, 255, 0.1); 
      border-bottom: 1px solid rgba(255, 255, 255, 0.2);
    }

    .dashboard-header h1 {
      font-size: 1.8rem;
      color: #fff;
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .user-info img {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      object-fit: cover;
      border: 2px solid #007bff;
    }

    .user-info span {
      font-size: 1rem;
      color: #fff;
    }

    /* Dashboard Stats Section */
    .dashboard-stats {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      padding: 20px;
    }

    .card {
      background: rgba(255, 255, 255, 0.1);
      border-radius: 10px;
      padding: 20px;
      text-align: center;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
      transition: transform 0.3s ease;
    }

    .card:hover {
      transform: translateY(-5px);
    }

    .card i {
      font-size: 2rem;
      color: #007bff;
      margin-bottom: 10px;
    }

    .card div {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    .card span {
      font-size: 1rem;
      color: #ccc;
    }

    .card strong {
      font-size: 1.5rem;
      color: #fff;
    }

    /* Disabled card style */
    .card.disabled {
      pointer-events: none;
      opacity: 0.6;
    }

    @media (max-width: 768px) {
      .dashboard-header h1 {
        font-size: 1.5rem;
      }

      .user-info span {
        font-size: 0.9rem;
      }
    }
  </style>
<body>
  <div class="dashboard-wrapper">
    <?php include 'includes/sidebar.php'; ?>
    
    <main class="main-content">
      <header class="dashboard-header">
        <h1>Welcome to Your Dashboard</h1>
        <div class="user-info">
          <img src="<?php echo $user['profile_picture'] ? htmlspecialchars($user['profile_picture']) : 'assets/images/default_profile.png'; ?>" alt="Profile Picture">
          <span><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
        </div>
      </header>

      <!-- User Statistics -->
      <section class="dashboard-stats">
        <div class="card">
          <i class="fas fa-users"></i>
          <div>
            <span>Total Users</span>
            <strong><?php echo $totalUsers; ?></strong>
          </div>
        </div>
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

      <!-- Complaint Statistics -->
      <section class="dashboard-stats">
        <div class="card">
          <i class="fas fa-tasks"></i>
          <div>
            <span>Total Complaints</span>
            <strong><?php echo $totalComplaints; ?></strong>
          </div>
        </div>
        <div class="card">
          <i class="fas fa-hourglass-half"></i>
          <div>
            <span>Pending Complaints</span>
            <strong><?php echo $pendingComplaints; ?></strong>
          </div>
        </div>
        <div class="card">
          <i class="fas fa-cogs"></i>
          <div>
            <span>In Progress Complaints</span>
            <strong><?php echo $inProgressComplaints; ?></strong>
          </div>
        </div>
        <div class="card">
          <i class="fas fa-check-circle"></i>
          <div>
            <span>Resolved Complaints</span>
            <strong><?php echo $resolvedComplaints; ?></strong>
          </div>
        </div>
      </section>
    </main>
  </div>
</body>
</html>