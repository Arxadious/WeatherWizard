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

// Delete the favorite from the database
$stmt = $pdo->prepare('DELETE FROM favorites WHERE user_id = ? AND location = ?');
$success = $stmt->execute([$user_id, $location]);

if ($success && $stmt->rowCount() > 0) {
    echo json_encode(['status' => 'Favorite removed']);
} else {
    echo json_encode(['status' => 'Error', 'message' => 'Failed to remove favorite or favorite not found']);
}
?>
