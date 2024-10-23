<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Check for preflight requests (OPTIONS request)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

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
        setcookie('user_id', $user_id, time() + (86400 * 30), "/"); // Set cookie for 30 days

        echo json_encode(['success' => true, 'message' => 'Registration successful']);
    }
}
?>
