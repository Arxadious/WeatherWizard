<?php
// Set CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Check for preflight requests (OPTIONS request)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
require 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
$homepageLocation = $data['location'];

$user_id = $_SESSION['user_id'] ?? $_COOKIE['user_id'];

$stmt = $pdo->prepare('UPDATE users SET homepage_location = ? WHERE id = ?');
$stmt->execute([$homepageLocation, $user_id]);

echo json_encode(['status' => 'Homepage location set']);
?>
