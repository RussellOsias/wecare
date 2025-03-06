<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['user_id'])) {
    exit();
}

$user_id = $_SESSION['user_id'];
$receiver_id = $_GET['user_id'] ?? null;

if (!$receiver_id) exit();

try {
    $stmt = $conn->prepare("
        SELECT m.id, m.sender_id, m.message, m.image_path, m.created_at, 
               u.first_name, u.last_name, u.profile_picture
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (m.sender_id = :sender_id AND m.receiver_id = :receiver_id)
           OR (m.sender_id = :receiver_id AND m.receiver_id = :sender_id)
        ORDER BY m.created_at ASC
    ");
    $stmt->bindParam(':sender_id', $user_id);
    $stmt->bindParam(':receiver_id', $receiver_id);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    exit();
}

foreach ($messages as $message) {
    $sender = $message['sender_id'] == $user_id ? 'sent' : 'received';
    echo '
    <div class="message '.$sender.'">
        <div class="message-content">
            '.($message['image_path'] ? '<img src="'.$message['image_path'].'" class="message-image">' : '').'
            '.htmlspecialchars($message['message']).'
            <small>'.date('M j, Y, g:i a', strtotime($message['created_at'])).'</small>
        </div>';
        
        if ($message['sender_id'] == $user_id) {
            echo '
            <div class="message-options" data-id="'.$message['id'].'">
                <button onclick="editMessage('.$message['id'].')">Edit</button>
                <button onclick="deleteMessage('.$message['id'].')">Delete</button>
            </div>';
        }
    echo '</div>';
}
?>