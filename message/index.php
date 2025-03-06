<?php
session_start();
require_once '../includes/db_conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Search handler
if (isset($_GET['search'])) {
    $search = htmlspecialchars($_GET['search']);
} else {
    $search = '';
}

// Fetch users with roles and profile pictures
try {
    $query = "SELECT id, first_name, last_name, email, role, profile_picture 
              FROM users 
              WHERE role IN ('admin', 'officer', 'resident')";
    
    if ($search) {
        $query .= " AND (CONCAT(first_name, ' ', last_name) LIKE :search 
                        OR email LIKE :search 
                        OR role LIKE :search)";
    }
    
    $stmt = $conn->prepare($query);
    if ($search) {
        $stmt->bindValue(':search', '%' . $search . '%');
    }
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}

// Fetch messages with images
$selected_user_id = $_GET['user_id'] ?? null;
$messages = [];
if ($selected_user_id) {
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
        $stmt->bindParam(':receiver_id', $selected_user_id);
        $stmt->execute();
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messaging</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
         /* Chat Styles */
         .chat-container {
            display: flex;
            height: calc(100vh - 150px);
        }

        .user-list {
            width: 30%;
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            overflow-y: auto;
            border-right: 1px solid rgba(255, 255, 255, 0.2);
        }

        .user-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .user-item:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .user-item img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #007bff;
        }

        .chat-box {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .chat-header {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .message-list {
            flex-grow: 1;
            overflow-y: auto;
            padding: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .message {
            margin-bottom: 10px;
        }

        .message.sent {
            text-align: right;
            color: #007bff;
        }

        .message.received {
            text-align: left;
            color: #ccc;
        }

        .message-form {
            display: flex;
            gap: 10px;
        }

        .message-form input {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #007bff;
            border-radius: 5px;
        }

        .message-form button {
            padding: 10px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .message-form button:hover {
            background: #0056b3;
        }

        .user-item img { width: 40px; height: 40px; border-radius: 50%; margin-right: 10px; }
        .message img { max-width: 200px; margin: 5px 0; }
        .message-options { margin-left: auto; cursor: pointer; }
        /* Add to your CSS file */
.role-badge {
    color: #ff0000; /* Red color */
    background: #ffe6e6;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 0.8rem;
    margin-left: 5px;
}

.profile-pic {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 10px;

}
.chat-profile {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 15px;
}
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include '../includes/sidebar.php'; ?>
        
        <main class="main-content">
            <header class="dashboard-header">
                <h1>Messaging</h1>
            </header>

            <div class="container">
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search users..." value="<?= $search ?>">
                </div>

                <div class="chat-container">
                    <div class="user-list" id="userList">
                        <?php foreach ($users as $user): ?>
                        
<div class="user-item" onclick="window.location.href='?user_id=<?php echo $user['id']; ?>'" style="cursor: pointer;">
    <img src="<?php echo !empty($user['profile_picture']) ? '../'.$user['profile_picture'] : '../assets/images/default_profile.png'; ?>" 
         alt="Profile" class="profile-pic">
    <div>
        <span><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
        <span class="role-badge"><?php echo ucfirst($user['role']); ?></span>
        <small><?php echo htmlspecialchars($user['email']); ?></small>
    </div>
</div>
                        <?php endforeach; ?>
                    </div>

                    <div class="chat-box">
                        <?php if ($selected_user_id): ?>
                            <div class="chat-header">
                                <img src="<?= $messages[0]['profile_picture'] ?? '../assets/images/default_profile.png' ?>" 
                                     alt="Profile" class="chat-profile">
                                <div>
                                    <span><?= htmlspecialchars($users[array_search($selected_user_id, array_column($users, 'id'))]['first_name']) ?></span>
                                    <span class="role-badge"><?= ucfirst($users[array_search($selected_user_id, array_column($users, 'id'))]['role']) ?></span>
                                </div>
                            </div>

                            <div class="message-list" id="messageList">
                                <?php foreach ($messages as $message): ?>
                                    <div class="message <?= $message['sender_id'] == $user_id ? 'sent' : 'received' ?>">
                                        <div class="message-content">
                                            <?php if ($message['image_path']): ?>
                                                <img src="<?= $message['image_path'] ?>" class="message-image">
                                            <?php endif; ?>
                                            <?= htmlspecialchars($message['message']) ?>
                                            <small><?= date('M j, Y, g:i a', strtotime($message['created_at'])) ?></small>
                                        </div>
                                        
                                        <?php if ($message['sender_id'] == $user_id): ?>
                                            <div class="message-options" data-id="<?= $message['id'] ?>">
                                                <button onclick="editMessage(<?= $message['id'] ?>)">Edit</button>
                                                <button onclick="deleteMessage(<?= $message['id'] ?>)">Delete</button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <form id="messageForm" enctype="multipart/form-data">
                                <input type="file" name="image" accept="image/*" id="imageInput">
                                <input type="text" name="message" placeholder="Type your message..." required>
                                <button type="submit">Send</button>
                            </form>
                        <?php else: ?>
                            <p>Select a user to start chatting</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // AJAX Search
        document.getElementById('searchInput').addEventListener('input', function() {
            const query = this.value.trim();
            fetch(`search_users.php?search=${encodeURIComponent(query)}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('userList').innerHTML = data;
                });
        });

        // Message handling
        document.getElementById('messageForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('receiver_id', <?= $selected_user_id ?>);
            formData.append('message', this.message.value);
            if (this.image.files.length) {
                formData.append('image', this.image.files[0]);
            }

            fetch('send_message.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('messageForm').reset();
                    document.getElementById('imageInput').value = '';
                    loadMessages(<?= $selected_user_id ?>);
                }
            });
        });

        function loadMessages(userId) {
            fetch(`get_messages.php?user_id=${userId}`)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('messageList').innerHTML = data;
                });
        }

        function deleteMessage(id) {
            if (confirm('Are you sure?')) {
                fetch('delete_message.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) loadMessages(<?= $selected_user_id ?>);
                });
            }
        }

        function editMessage(id) {
            const newText = prompt('Edit your message:');
            if (newText) {
                fetch('edit_message.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, message: newText })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) loadMessages(<?= $selected_user_id ?>);
                });
            }
        }
    </script>
</body>
</html>