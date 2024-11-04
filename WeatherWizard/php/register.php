<?php
// Allow only trusted domains in production
header("Access-Control-Allow-Origin: https://weatherwizard.kesug.com");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

// Check for preflight requests (OPTIONS request)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'config.php';
session_start();

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Sanitize email and password input
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = trim($_POST['password']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            exit();
        }

        // Update the password validation to reflect new rules
        if (strlen($password) < 5 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password) || !preg_match('/[\W_]/', $password)) {
            echo json_encode(['success' => false, 'message' => 'Password must be at least 5 characters long and contain at least one letter, one number, and one special character (e.g., A1@)']);
            exit();
        }

        // Hash the password for security using ARGON2ID
        $hashed_password = password_hash($password, PASSWORD_ARGON2ID);

        // Check if the email already exists in the database
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);

        if ($stmt->rowCount() > 0) {
            // Send error response for existing email
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
        } else {
            // Insert new user into the database
            $sql = "INSERT INTO users (email, password) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email, $hashed_password]);

            // Set session and cookie for successful registration
            $user_id = $pdo->lastInsertId();
            $_SESSION['user_id'] = $user_id;
            setcookie('user_id', $user_id, time() + (86400 * 30), "/", "", true, true); // Set cookie with HttpOnly and Secure flags

            echo json_encode(['success' => true, 'message' => 'Registration successful']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
} catch (Exception $e) {
    // Handle any errors that occurred during the process
    echo json_encode(['success' => false, 'message' => 'An error occurred', 'error' => $e->getMessage()]);
    exit();
}
?>