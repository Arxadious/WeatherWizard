<?php
// Allow only trusted domains in production
header("Access-Control-Allow-Origin: https://weatherwizard.kesug.com");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
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

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'Error', 'message' => 'Invalid request method']);
    exit();
}

session_start();
require 'config.php';

// Validate session data
if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    echo json_encode(['status' => 'Error', 'message' => 'User not logged in']);
    exit();
}
$user_id = intval($_SESSION['user_id']);

// Decode incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Validate and sanitize the homepage location input
if (!isset($data['location']) || empty($data['location'])) {
    echo json_encode(['status' => 'Error', 'message' => 'Location is required']);
    exit();
}

$homepageLocation = filter_var($data['location'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

try {
    // Prepare and execute the statement to update homepage location
    $stmt = $pdo->prepare('UPDATE users SET homepage_location = ? WHERE id = ?');
    $success = $stmt->execute([$homepageLocation, $user_id]);

    if ($success && $stmt->rowCount() > 0) {
        echo json_encode(['status' => 'Success', 'message' => 'Homepage location set']);
    } else {
        echo json_encode(['status' => 'Error', 'message' => 'Failed to set homepage location']);
    }
} catch (PDOException $e) {
    error_log($e->getMessage()); // Log the error internally
    echo json_encode(['status' => 'Error', 'message' => 'An error occurred while processing your request']);
    exit();
}
?>