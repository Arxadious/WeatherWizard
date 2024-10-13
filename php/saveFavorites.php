<?php
// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

session_start();
require 'config.php';

// Decode incoming JSON data
$data = json_decode(file_get_contents('php://input'), true);
$user_id = $data['user_id'];
$location = $data['location'];

// Insert the favorite into the database if it does not already exist
$stmt = $pdo->prepare('INSERT INTO favorites (user_id, location) VALUES (?, ?) ON DUPLICATE KEY UPDATE location = location');
$success = $stmt->execute([$user_id, $location]);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Favorite saved successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save favorite']);
}
?>
