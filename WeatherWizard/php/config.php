<?php
// Allow cross-origin requests - Update for production use to only allow trusted domains
header("Access-Control-Allow-Origin: https://weatherwizard.kesug.com");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
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

// Disable error display in production
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/error_log.txt');  // Update with secure path for error logging

// Update with your actual database credentials
$host = 'sql103.infinityfree.com';  
$db = 'if0_37324028_weatherwizard'; 
$user = 'if0_37324028';             
$pass = 'q2h79ZWzexr';       
$charset = 'utf8mb4';

// Define DSN for MySQL
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// PDO options to strengthen security
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throws exceptions in case of errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Sets default fetch mode
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Ensures true prepared statements
];

try {
    // Create PDO instance
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Log error securely
    error_log('Database connection failed: ' . $e->getMessage());
    echo 'An error occurred. Please try again later.';
    exit();
}
?>
