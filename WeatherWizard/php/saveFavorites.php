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
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

require 'config.php';
session_start();

// Validate session data
if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}
$user_id = intval($_SESSION['user_id']); // Get user ID from session

// Decode incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Validate and sanitize input
$location = isset($data['location']) ? filter_var($data['location'], FILTER_SANITIZE_FULL_SPECIAL_CHARS) : null;
$action = isset($data['action']) ? filter_var($data['action'], FILTER_SANITIZE_STRING) : null;

// Validate input data
$allowed_actions = ['add', 'remove'];
if (empty($location) || !in_array($action, $allowed_actions)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input. Location or action is missing or incorrect.']);
    exit();
}

// Prepare and execute the SQL statement based on action
try {
    if ($action === 'add') {
        // Insert favorite into database and prevent duplicates
        $stmt = $pdo->prepare('INSERT IGNORE INTO favorites (user_id, location) VALUES (?, ?)');
        $success = $stmt->execute([$user_id, $location]);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Favorite saved successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save favorite']);
        }
    } elseif ($action === 'remove') {
        // Delete favorite from the database
        $stmt = $pdo->prepare('DELETE FROM favorites WHERE user_id = ? AND location = ?');
        $success = $stmt->execute([$user_id, $location]);

        if ($success && $stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Favorite removed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove favorite']);
        }
    }
} catch (PDOException $e) {
    // Handle any errors that occurred during the process
    error_log($e->getMessage()); // Log error internally
    echo json_encode(['success' => false, 'message' => 'An error occurred while processing your request']);
    exit();
}
?>