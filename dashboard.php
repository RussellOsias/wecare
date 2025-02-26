<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

echo "<h2>Welcome, " . htmlspecialchars($_SESSION['email']) . "!</h2>";
echo "<p>This is your dashboard.</p>";
echo "<a href='logout.php'>Logout</a>";
?>