<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$receiver_id = intval($_POST['receiver_id'] ?? 0);
$message_text = htmlspecialchars($_POST['message'] ?? '');
$image_path = null;

// Handle image upload
if (!empty($_FILES['image']['name'])) {
    $target_dir = "../uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $file_name = uniqid() . '_' . basename($_FILES['image']['name']);
    $file_path = $target_dir . $file_name;
    
    if (move_uploaded_file($_FILES['image']['tmp_name'], $file_path)) {
        $image_path = $file_path;
    } else {
        echo json_encode(['success' => false, 'error' => 'Image upload failed']);
        exit();
    }
}

try {
    $stmt = $conn->prepare("
        INSERT INTO messages 
        (sender_id, receiver_id, message, image_path) 
        VALUES 
        (:sender_id, :receiver_id, :message, :image_path)
    ");
    $stmt->bindParam(':sender_id', $_SESSION['user_id']);
    $stmt->bindParam(':receiver_id', $receiver_id);
    $stmt->bindParam(':message', $message_text);
    $stmt->bindParam(':image_path', $image_path);
    
    $success = $stmt->execute();
    
    echo json_encode(['success' => $success]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>