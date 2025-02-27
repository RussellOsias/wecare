<?php
session_start();
require_once 'includes/db_conn.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
try {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        die("User not found.");
    }
} catch (PDOException $e) {
    die("Error fetching user: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = htmlspecialchars($_POST['first_name']);
    $middle_name = htmlspecialchars($_POST['middle_name']);
    $last_name = htmlspecialchars($_POST['last_name']);
    $email = htmlspecialchars($_POST['email']);
    $phone_number = intval($_POST['phone_number']);
    $address = htmlspecialchars($_POST['address']);

    // Handle profile picture upload
    $profile_picture = $user['profile_picture']; // Default to existing picture
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'assets/images/profiles/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Create directory if it doesn't exist
        }

        $file_name = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $file_path)) {
            $profile_picture = $file_path; // Save the new file path
        } else {
            echo "<script>alert('Failed to upload profile picture.');</script>";
        }
    }

    try {
        $stmt = $conn->prepare("UPDATE users SET 
            first_name = :first_name, 
            middle_name = :middle_name, 
            last_name = :last_name, 
            email = :email, 
            phone_number = :phone_number, 
            address = :address,
            profile_picture = :profile_picture 
            WHERE id = :id");

        $stmt->bindParam(':first_name', $first_name);
        $stmt->bindParam(':middle_name', $middle_name);
        $stmt->bindParam(':last_name', $last_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone_number', $phone_number);
        $stmt->bindParam(':address', $address);
        $stmt->bindParam(':profile_picture', $profile_picture);
        $stmt->bindParam(':id', $user_id);

        if ($stmt->execute()) {
            echo "<script>alert('Profile updated successfully!');</script>";
            // Refresh user data after update
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindParam(':id', $user_id);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
        } else {
            echo "<script>alert('Failed to update profile. Please try again.');</script>";
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Management</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Profile Page Styles */
        .profile-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: rgba(255, 255, 255, 0.1); /* Semi-transparent background */
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-header img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #007bff;
            margin-bottom: 10px;
        }

        .profile-header h2 {
            font-size: 1.8rem;
            color: #fff;
            margin-bottom: 5px;
        }

        .profile-header p {
            font-size: 1rem;
            color: #ccc;
        }

        .profile-form {
            display: flex;
            flex-direction: column;
        }

        .profile-form .input-field {
            margin-bottom: 20px;
        }

        .profile-form label {
            font-size: 1rem;
            color: #fff;
            margin-bottom: 8px;
        }

        .profile-form input, .profile-form select {
            width: 100%;
            height: 40px;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid #007bff;
            border-radius: 5px;
            font-size: 16px;
            color: #000;
            outline: none;
        }

        .profile-form button {
            padding: 10px;
            background: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: 0.3s ease;
        }

        .profile-form button:hover {
            background: #0056b3;
        }

        .profile-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .profile-actions a {
            color: #007bff;
            text-decoration: none;
            font-size: 1rem;
        }

        .profile-actions a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="profile-container">
                <div class="profile-header">
                    <img src="<?php echo $user['profile_picture'] ? $user['profile_picture'] : 'assets/images/default_profile.png'; ?>" alt="Profile Picture">
                    <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>

                <form method="POST" action="" enctype="multipart/form-data" class="profile-form">
                    <h3>Edit Profile</h3>
                    <div class="input-field">
                        <label for="first_name">First Name:</label>
                        <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                    </div>
                    <div class="input-field">
                        <label for="middle_name">Middle Name:</label>
                        <input type="text" id="middle_name" name="middle_name" value="<?php echo htmlspecialchars($user['middle_name']); ?>">
                    </div>
                    <div class="input-field">
                        <label for="last_name">Last Name:</label>
                        <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                    </div>
                    <div class="input-field">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="input-field">
                        <label for="phone_number">Phone Number:</label>
                        <input type="number" id="phone_number" name="phone_number" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
                    </div>
                    <div class="input-field">
                        <label for="address">Address:</label>
                        <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required>
                    </div>
                    <div class="input-field">
                        <label for="profile_picture">Profile Picture:</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                    </div>
                    <button type="submit">Save Changes</button>
                </form>

                <div class="profile-actions">
                    <a href="change_password.php">Change Password</a>
                    <a href="dashboard.php">Back to Dashboard</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>