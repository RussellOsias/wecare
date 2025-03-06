<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['user_id'])) {
    exit();
}

$search = $_GET['search'] ?? '';

try {
    $stmt = $conn->prepare("
        SELECT id, first_name, last_name, email, role, profile_picture 
        FROM users 
        WHERE (CONCAT(first_name, ' ', last_name) LIKE :search 
            OR email LIKE :search 
            OR role LIKE :search)
        AND role IN ('admin', 'officer', 'resident')
    ");
    $stmt->bindValue(':search', '%' . $search . '%');
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    exit();
}

foreach ($users as $user) {
    $profile_picture = !empty($user['profile_picture']) 
        ? '../'.$user['profile_picture'] 
        : '../assets/images/default_profile.png';

    echo '
    <div class="user-item" onclick="selectUser('.$user['id'].')" style="cursor: pointer;">
        <img src="'.$profile_picture.'" alt="Profile" class="profile-pic">
        <div>
            <span>'.htmlspecialchars($user['first_name'].' '.$user['last_name']).'</span>
            <span class="role-badge">'.ucfirst($user['role']).'</span>
            <small>'.htmlspecialchars($user['email']).'</small>
        </div>
    </div>';
}
?>