<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false]);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$message_id = $data['id'] ?? null;
$new_text = $data['message'] ?? '';

if (!$message_id || !$new_text) {
    echo json_encode(['success' => false]);
    exit();
}

try {
    // Check ownership
    $stmt = $conn->prepare("SELECT sender_id FROM messages WHERE id = :id");
    $stmt->bindParam(':id', $message_id);
    $stmt->execute();
    $message = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($message['sender_id'] != $_SESSION['user_id']) {
        echo json_encode(['success' => false]);
        exit();
    }

    // Update message
    $stmt = $conn->prepare("UPDATE messages SET message = :message, edited_at = NOW() WHERE id = :id");
    $stmt->bindParam(':message', $new_text);
    $stmt->bindParam(':id', $message_id);
    $success = $stmt->execute();
    
    echo json_encode(['success' => $success]);
} catch (PDOException $e) {
    echo json_encode(['success' => false]);
}
?>