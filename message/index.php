<?php
session_start();
require_once '../includes/db_conn.php';

// Redirect if not authenticated or not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle search functionality
$search = '';
if (isset($_GET['search'])) {
    $search = htmlspecialchars($_GET['search']);
}

// Fetch users based on search query
try {
    $query = "SELECT id, first_name, last_name, email FROM users WHERE role IN ('admin', 'officer', 'resident')";
    if ($search) {
        $query .= " AND (CONCAT(first_name, ' ', last_name) LIKE :search OR email LIKE :search)";
    }
    $stmt = $conn->prepare($query);
    if ($search) {
        $stmt->bindValue(':search', '%' . $search . '%');
    }
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching users: " . $e->getMessage());
}

// Fetch messages for the selected user
$selected_user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
$messages = [];
if ($selected_user_id) {
    try {
        $stmt = $conn->prepare("
            SELECT m.id, m.sender_id, m.message, m.created_at, u.first_name, u.last_name
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
        die("Error fetching messages: " . $e->getMessage());
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
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div class="search-bar">
                        <input type="text" id="searchInput" placeholder="Search by name or email" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>

                <div class="chat-container">
                    <!-- User List -->
                    <div class="user-list">
                        <?php foreach ($users as $user): ?>
                            <div class="user-item" onclick="window.location.href='?user_id=<?php echo $user['id']; ?>'">
                                <img src="../assets/images/default_profile.png" alt="Profile Picture">
                                <div>
                                    <span><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></span>
                                    <span style="font-size: 0.9rem; color: #ccc;"><?php echo htmlspecialchars($user['email']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Chat Box -->
                    <div class="chat-box">
                        <?php if ($selected_user_id): ?>
                            <div class="chat-header">
                                Chat with <?php echo htmlspecialchars($users[array_search($selected_user_id, array_column($users, 'id'))]['first_name']); ?>
                            </div>

                            <div class="message-list">
                                <?php foreach ($messages as $message): ?>
                                    <div class="message <?php echo $message['sender_id'] == $user_id ? 'sent' : 'received'; ?>">
                                        <strong><?php echo htmlspecialchars($message['first_name'] . ' ' . $message['last_name']); ?>:</strong>
                                        <?php echo htmlspecialchars($message['message']); ?>
                                        <small style="display: block; font-size: 0.8rem; color: #aaa;"><?php echo date('M j, Y, g:i a', strtotime($message['created_at'])); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <form class="message-form" method="POST" action="send_message.php">
                                <input type="hidden" name="receiver_id" value="<?php echo $selected_user_id; ?>">
                                <input type="text" name="message" placeholder="Type your message..." required>
                                <button type="submit">Send</button>
                            </form>
                        <?php else: ?>
                            <p>Select a user to start chatting.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Real-time search functionality
        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('input', function () {
            const query = this.value.trim();
            window.location.href = `?search=${encodeURIComponent(query)}`;
        });
    </script>
</body>
</html>