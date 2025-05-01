<?php
session_start();
require_once 'includes/db_conn.php';

// Redirect if not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

// Handle AJAX request for deleting a user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = intval($data['user_id']);

    try {
        // Begin transaction
        $conn->beginTransaction();

        // First get user details before deleting
        $stmt = $conn->prepare("SELECT first_name, last_name, email, role FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            throw new Exception("User not found.");
        }

        // Delete the user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $user_id);
        $stmt->execute();

        // Get admin's details who performed the deletion
        $admin_stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = :admin_id");
        $admin_stmt->bindParam(':admin_id', $_SESSION['user_id']);
        $admin_stmt->execute();
        $admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);
        $admin_name = $admin['first_name'] . ' ' . $admin['last_name'];

        // Prepare log message
        $action = "$admin_name deleted {$user['role']} account: {$user['first_name']} {$user['last_name']} ({$user['email']})";

        // Log to admin_activity_logs
        $log_stmt = $conn->prepare("INSERT INTO admin_activity_logs 
                                  (admin_id, activity_type, action, user_affected_id) 
                                  VALUES (:admin_id, :activity_type, :action, :user_affected_id)");
        $log_stmt->bindParam(':admin_id', $_SESSION['user_id']);
        $log_stmt->bindValue(':activity_type', 'user_deletion');
        $log_stmt->bindParam(':action', $action);
        $log_stmt->bindParam(':user_affected_id', $user_id);
        $log_stmt->execute();

        // Commit transaction
        $conn->commit();

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}
?>