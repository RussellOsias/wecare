<aside class="sidebar">
    <div class="logo-container">
        <img src="../assets/images/logo.png" alt="Logo" class="logo">
        <h2>WeCare</h2>
    </div>

    <nav class="nav-sections">
        <ul class="nav-list">
            <li><a href="/dashboard.php"><i class="fas fa-home"></i><span>Home</span></a></li>

            <?php if (in_array($_SESSION['role'], ['admin', 'officer'])): ?>
            <li><a href="/manage_users.php"><i class="fas fa-users-cog"></i><span>User Management</span></a></li>
            <li><a href="/notification.php"><i class="fas fa-bell"></i><span>Notifications</span></a></li>
            <?php endif; ?>

            <li><a href="/profile.php"><i class="fas fa-user-circle"></i><span>Profile</span></a></li>

            <?php if ($_SESSION['role'] === 'admin'): ?>
            <li class="nav-heading">Admin Logs</li>
            <li><a href="/admin_logs.php"><i class="fas fa-sign-in-alt"></i><span>Admin Log-ins</span></a></li>
            <li><a href="/admin_activity_logs.php"><i class="fas fa-clipboard-list"></i><span>Admin Activity</span></a></li>
            <li><a href="/officer_logs.php"><i class="fas fa-user-clock"></i><span>Officer Log-ins</span></a></li>
            <li><a href="/officer_activity_logs.php"><i class="fas fa-tasks"></i><span>Officer Activity</span></a></li>
            <?php endif; ?>

            <?php if (in_array($_SESSION['role'], ['admin', 'officer'])): ?>
            <li class="nav-heading">Complaints</li>
            <li><a href="/admin_view_complaints.php"><i class="fas fa-exclamation-circle"></i><span>Complaints</span></a></li>
            <li><a href="/history_complaints.php"><i class="fas fa-history"></i><span>Complaint History</span></a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <button class="logout-btn" onclick="window.location.href='/logout.php'">
        <i class="fas fa-sign-out-alt"></i><span>Logout</span>
    </button>
</aside>
