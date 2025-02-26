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

echo "<h2>Welcome, " . htmlspecialchars($_SESSION['email']) . "!</h2>";
echo "<p>This is your dashboard.</p>";
echo "<a href='logout.php'>Logout</a>";
?>