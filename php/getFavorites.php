<?php 
// getFavorites.php

// Allow CORS for requests
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Check for preflight requests (OPTIONS request)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'config.php'; // Include the database connection

// Start the session to access user data
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

try {
    // Fetch the user's favorite locations from the database
    $sql = "SELECT location FROM favorites WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    
    $favorites = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $favorites[] = $row['location'];
    }
    
    // Return the favorites in JSON format
    echo json_encode(['success' => true, 'favorites' => $favorites]);
} catch (Exception $e) {
    // Handle any errors that occurred during the process
    echo json_encode(['success' => false, 'message' => 'Failed to load favorites', 'error' => $e->getMessage()]);
    exit();
}
?>
