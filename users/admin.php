<?php
session_start();
require_once '../includes/db_conn.php';

// Redirect if not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle AJAX request for fetching admins
if (isset($_GET['search'])) {
    $search = htmlspecialchars($_GET['search']);
    try {
        $query = "SELECT id, first_name, last_name, email, role FROM users WHERE role = 'admin'";
        if ($search) {
            $query .= " AND (CONCAT(first_name, ' ', last_name) LIKE :search OR email LIKE :search)";
        }
        $stmt = $conn->prepare($query);
        if ($search) {
            $stmt->bindValue(':search', '%' . $search . '%');
        }
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Admin Users</title>
    <link rel="stylesheet" href="../assets/css/style.css">
       <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <style>
    .search-bar input,
    .filter-bar select {
        padding: 8px 12px;
        border: 1px solid #ccc;
        border-radius: 6px;
        font-size: 14px;
        outline: none;
        transition: border-color 0.3s;
    }

    .search-bar input:focus,
    .filter-bar select:focus {
        border-color: #007bff;
    }

    .back-btn {
        background-color: #007bff;
        color: #fff;
        text-decoration: none;
        padding: 8px 14px;
        border-radius: 6px;
        font-weight: 500;
        transition: background-color 0.3s;
        font-size: 14px;
    }

    .back-btn:hover {
        background-color: #0056b3;
    }

    .action-btn {
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        cursor: pointer;
        margin-right: 6px;
    }

    .edit-btn {
        background-color: #ffc107;
        color: #000;
    }

    .edit-btn:hover {
        background-color: #e0a800;
    }

    .delete-btn {
        background-color: #dc3545;
        color: #fff;
    }

    .delete-btn:hover {
        background-color: #bd2130;
    }

  
</style>

</head>

<body>
    <div class="dashboard-wrapper">
        <?php include '../includes/sidebar.php'; ?> <!-- moved here -->

        <main class="main-content">
            <div class="container">
                <h2>Admin Users</h2>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div class="search-bar">
                        <input type="text" id="searchInput" placeholder="Search by name or email">
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <a href="../users/admin.php" class="back-btn">Admins</a>
                        <a href="../users/officer.php" class="back-btn">Officers</a>
                        <a href="../users/resident.php" class="back-btn">Residents</a>
                        <?php if ($_SESSION['role'] === 'admin'): ?>
                            <a href="../add_users.php" class="back-btn">Add Users</a>
                        <?php endif; ?>
                    </div>
                    <a href="../manage_users.php" class="back-btn">Back to User Management</a>
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

                <a href="../dashboard.php" class="back-btn">Back to Dashboard</a>
            </div>
        </main>
    </div>

    <script>
        async function fetchUsers(search = '') {
            const response = await fetch(`admin.php?search=${encodeURIComponent(search)}`);
            const data = await response.json();
            const tbody = document.querySelector('#userTable tbody');
            tbody.innerHTML = '';

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
                        <button class="action-btn edit-btn" onclick="window.location.href='../edit_user.php?id=${user.id}'">Edit</button>
                        <button class="action-btn delete-btn" onclick="confirmDelete(${user.id})">Delete</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        fetchUsers();

        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('input', function () {
            const query = this.value.trim();
            fetchUsers(query);
        });

        async function confirmDelete(userId) {
            const isConfirmed = confirm("Are you sure you want to delete this user?");
            if (isConfirmed) {
                const response = await fetch('../delete_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ user_id: userId })
                });
                const result = await response.json();
                if (result.success) {
                    alert('User deleted successfully!');
                    fetchUsers();
                } else {
                    alert('Failed to delete user: ' + result.message);
                }
            }
        }
    </script>
</body>
</html>
