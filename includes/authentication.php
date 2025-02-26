<?php
require_once 'db_conn.php';

/**
 * Function to authenticate a user using session-based authentication.
 */
function authenticateSession($email, $password) {
    global $conn;

    try {
        // Fetch user data from the database
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Successful login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            return true;
        }
    } catch (PDOException $e) {
        error_log("Session-based authentication failed: " . $e->getMessage());
    }

    return false;
}

/**
 * Function to authenticate a user using token-based authentication.
 */
function authenticateToken($token) {
    global $conn;

    try {
        // Fetch user data from the database
        $stmt = $conn->prepare("SELECT * FROM users WHERE session_token = :token AND token_expiry > NOW()");
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Token is valid
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            return true;
        }
    } catch (PDOException $e) {
        error_log("Token-based authentication failed: " . $e->getMessage());
    }

    return false;
}

/**
 * Function to generate a session token for a user.
 */
function generateSessionToken($userId) {
    global $conn;

    // Generate a random token
    $token = bin2hex(random_bytes(32));
    $expiry = date('Y-m-d H:i:s', strtotime('+1 day')); // Token expires in 1 day

    try {
        // Update the user's session token and expiry in the database
        $stmt = $conn->prepare("UPDATE users SET session_token = :token, token_expiry = :expiry WHERE id = :id");
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':expiry', $expiry);
        $stmt->bindParam(':id', $userId);

        if ($stmt->execute()) {
            return $token;
        }
    } catch (PDOException $e) {
        error_log("Failed to generate session token: " . $e->getMessage());
    }

    return null;
}
?>