<?php
session_start();
require_once 'includes/db_conn.php'; // Database connection file

// Ensure the user is logged in as a resident
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'resident') {
    header("Location: login.php");
    exit();
}

// Get the resident's ID from the session
$resident_id = $_SESSION['user_id'];

// Collect form data
$title = trim($_POST['title']);
$description = trim($_POST['description']);

// Insert the complaint into the database
try {
    $stmt = $conn->prepare("INSERT INTO complaints (resident_id, title, description) VALUES (:resident_id, :title, :description)");
    $stmt->bindParam(':resident_id', $resident_id);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':description', $description);
    $stmt->execute();

    echo "Complaint submitted successfully!";
} catch (Exception $e) {
    echo "Error submitting complaint: " . $e->getMessage();
}
?>