<?php
session_start();
require_once '../includes/db_conn.php';

// Redirect if not authenticated or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$receiver_id = intval($_POST['receiver_id']);
$message = htmlspecialchars($_POST['message']);
$image_path = null;

// Handle image upload
if ($_FILES['image']['size'] > 0) {
    $target_dir = "../uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true); // Create the uploads directory if it doesn't exist
    }
    $file_name = basename($_FILES['image']['name']);
    $file_path = $target_dir . uniqid() . '_' . $file_name;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
        $image_path = $file_path; // Save the file path in the database
    } else {
        echo "<script>alert('Failed to upload image. Please try again.');</script>";
        exit();
    }
}

try {
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, image_path) VALUES (:sender_id, :receiver_id, :message, :image_path)");
    $stmt->bindParam(':sender_id', $user_id);
    $stmt->bindParam(':receiver_id', $receiver_id);
    $stmt->bindParam(':message', $message);
    $stmt->bindParam(':image_path', $image_path);

    if ($stmt->execute()) {
        header("Location: index.php?user_id=$receiver_id");
        exit();
    } else {
        echo "<script>alert('Failed to send message. Please try again.');</script>";
    }
} catch (PDOException $e) {
    echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
}
?>