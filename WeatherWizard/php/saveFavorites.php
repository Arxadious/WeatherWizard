<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Check for preflight requests (OPTIONS request)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'config.php';

// Decode incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);
$user_id = isset($data['user_id']) ? $data['user_id'] : null;
$location = isset($data['location']) ? $data['location'] : null;
$action = isset($data['action']) ? $data['action'] : null;

// Validate input data
if (empty($user_id) || empty($location) || empty($action)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input. User ID, location, or action is missing.']);
    exit();
}

// Prepare and execute the SQL statement based on action
try {
    if ($action === 'add') {
        $stmt = $pdo->prepare('INSERT INTO favorites (user_id, location) VALUES (?, ?) ON DUPLICATE KEY UPDATE location = location');
        $success = $stmt->execute([$user_id, $location]);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Favorite saved successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save favorite']);
        }
    } elseif ($action === 'remove') {
        $stmt = $pdo->prepare('DELETE FROM favorites WHERE user_id = ? AND location = ?');
        $success = $stmt->execute([$user_id, $location]);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Favorite removed successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to remove favorite']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
