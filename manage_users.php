<?php
session_start();
require_once 'includes/db_conn.php';

// Redirect if not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle AJAX request for searching users
if (isset($_GET['search'])) {
    $search = htmlspecialchars($_GET['search']);
    try {
        $query = "SELECT id, first_name, last_name, email, role FROM users WHERE 
                  CONCAT(first_name, ' ', last_name) LIKE :search OR email LIKE :search";
        $stmt = $conn->prepare($query);
        $searchTerm = '%' . $search . '%';
        $stmt->bindParam(':search', $searchTerm);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return JSON response for AJAX
        echo json_encode($users);
        exit();
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="container">
                <h2>Manage Users</h2>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div class="search-bar">
                        <input type="text" id="searchInput" placeholder="Search by name or email">
                    </div>
                    <a href="add_users.php" class="back-btn">Add Users</a>
                </div>
                <table id="userTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- User rows will be dynamically populated here -->
                    </tbody>
                </table>
                <a href="dashboard.php" class="back-btn">Back to Dashboard</a>
            </div>
        </main>
    </div>
    <script>
        // Fetch users and populate the table
        async function fetchUsers(search = '') {
            const response = await fetch(`manage_users.php?search=${encodeURIComponent(search)}`);
            const data = await response.json();
            const tbody = document.querySelector('#userTable tbody');
            tbody.innerHTML = ''; // Clear existing rows

            if (data.error) {
                console.error(data.error);
                return;
            }

            data.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${user.id}</td>
                    <td>${user.first_name} ${user.last_name}</td>
                    <td>${user.email}</td>
                    <td>${user.role}</td>
                    <td>
                        <button class="action-btn edit-btn" onclick="window.location.href='edit_user.php?id=${user.id}'">Edit</button>
                        <button class="action-btn delete-btn" onclick="confirmDelete(${user.id})">Delete</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        // Initial load
        fetchUsers();

        // Real-time search functionality
        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('input', function () {
            const query = this.value.trim();
            fetchUsers(query);
        });

        // Confirm deletion with a pop-up
        async function confirmDelete(userId) {
            const isConfirmed = confirm("Are you sure you want to delete this user?");
            if (isConfirmed) {
                const response = await fetch('delete_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ user_id: userId })
                });
                const result = await response.json();
                if (result.success) {
                    alert('User deleted successfully!');
                    fetchUsers(); // Refresh the table
                } else {
                    alert('Failed to delete user: ' + result.message);
                }
            }
        }
    </script>
</body>
</html>