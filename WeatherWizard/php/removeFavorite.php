<?php
// Allow only trusted domains in production
header("Access-Control-Allow-Origin: https://weatherwizard.kesug.com");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// Security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

session_start();
require 'config.php';

// Validate session data
if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    echo json_encode(['status' => 'Error', 'message' => 'User not logged in']);
    exit();
}

// Check for preflight requests (OPTIONS request)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check for correct request method
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'Error', 'message' => 'Invalid request method']);
    exit();
}

// Decode incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Validate and sanitize input
if (empty($data['location'])) {
    echo json_encode(['status' => 'Error', 'message' => 'Invalid input']);
    exit();
}

$user_id = intval($_SESSION['user_id']); // User ID from session
$location = filter_var($data['location'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

try {
    // Delete the favorite from the database using prepared statement
    $stmt = $pdo->prepare('DELETE FROM favorites WHERE user_id = ? AND location = ?');
    $success = $stmt->execute([$user_id, $location]);

    if ($success && $stmt->rowCount() > 0) {
        echo json_encode(['status' => 'Favorite removed']);
    } else {
        echo json_encode(['status' => 'Error', 'message' => 'Failed to remove favorite or favorite not found']);
    }
} catch (Exception $e) {
    // Log the error internally
    error_log($e->getMessage()); 
    echo json_encode(['status' => 'Error', 'message' => 'An error occurred while removing favorite']);
    exit();
}
?>