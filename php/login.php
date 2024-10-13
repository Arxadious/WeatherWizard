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

    // Prepare and execute query to get the user data
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verify the password hash matches the provided password
    if ($user && password_verify($password, $user['password'])) {
        // Set session and cookie for successful login
        $_SESSION['user_id'] = $user['id'];
        setcookie('user_id', $user['id'], time() + (86400 * 30), "/"); // Set cookie for 30 days
        
        // Respond with success and provide user_id in the response
        echo json_encode(['success' => true, 'message' => 'Login successful', 'user_id' => $user['id']]);
    } else {
        // Send error response for invalid credentials
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    }
} else {
    // Send error response if request method is not POST
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>
