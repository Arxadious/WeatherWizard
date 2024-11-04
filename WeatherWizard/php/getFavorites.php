<?php

// Allow CORS for requests - Update for production to only allow trusted domains
header("Access-Control-Allow-Origin: https://weatherwizard.kesug.com");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Set additional security headers
header("X-Content-Type-Options: nosniff"); // Prevent MIME-type sniffing
header("X-Frame-Options: DENY"); // Prevent clickjacking
header("X-XSS-Protection: 1; mode=block"); // Enable XSS filtering

// Check for preflight requests (OPTIONS request)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

include 'config.php'; // Include the database connection

session_start(); // Start the session

// Regenerate session ID to prevent fixation attacks
session_regenerate_id(true);

// Validate session data
if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Get the logged-in user's ID
$user_id = intval($_SESSION['user_id']);

try {
    // Fetch the user's favorite locations from the database
    $sql = "SELECT location FROM favorites WHERE user_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    
    $favorites = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $favorites[] = htmlspecialchars($row['location'], ENT_QUOTES, 'UTF-8'); // Sanitize output
    }
    
    // Return the favorites in JSON format
    echo json_encode(['success' => true, 'favorites' => $favorites]);
} catch (Exception $e) {
    // Log error and return a generic message
    error_log($e->getMessage()); // Log the error internally
    echo json_encode(['success' => false, 'message' => 'Failed to load favorites']);
    exit();
}
?>