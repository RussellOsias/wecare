<aside class="sidebar">
    <div class="logo-container">
        <img src="../assets/images/logo.png" alt="Logo" class="logo">
        <h2>We Care</h2>
    </div>

    <ul>
        <li><a href="/dashboard.php"><i class="fas fa-home"></i> Home</a></li>
        
        <?php if (in_array($_SESSION['role'], ['admin', 'officer'])): ?>
        <li><a href="/manage_users.php"><i class="fas fa-users"></i> User Management</a></li>
        <?php endif; ?>
        
        <li><a href="/profile.php"><i class="fas fa-user"></i> Profile</a></li>
        
        <?php if ($_SESSION['role'] === 'admin'): ?>
        <li><a href="/admin_logs.php"><i class="fas fa-bell"></i> Admin Logs</a></li>
        <?php endif; ?>

        <?php if (in_array($_SESSION['role'], ['admin', 'officer','resident'])): ?>
        <li><a href="message/index.php"><i class="fa fa-comments"></i> Message</a></li>
        <?php endif; ?>
        
        <?php if (in_array($_SESSION['role'], ['admin', 'officer'])): ?>
        <li><a href="/admin_view_complaints.php"><i class="fa fa-exclamation-circle"></i> Complaints</a></li>
        <?php endif; ?>
    </ul>

    <button class="logout-btn" onclick="window.location.href='/logout.php'">
        <i class="fas fa-sign-out-alt"></i> Logout
    </button>
</aside>