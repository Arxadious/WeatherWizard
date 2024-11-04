<?php
// Set CORS headers - Restrict for production
header("Access-Control-Allow-Origin: https://weatherwizard.kesug.com");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: text/html; charset=UTF-8'); // Set proper content type with character set

// Set additional security headers
header("X-Content-Type-Options: nosniff"); // Prevent MIME-type sniffing
header("X-Frame-Options: DENY"); // Prevent clickjacking
header("X-XSS-Protection: 1; mode=block"); // Enable XSS filtering

// Check for preflight requests (OPTIONS request)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();
session_regenerate_id(true); // Regenerate session ID to prevent fixation attacks

// Enhance session security with stronger cookie settings
if (session_status() === PHP_SESSION_ACTIVE) {
    setcookie(session_name(), session_id(), [
        'httponly' => true,
        'secure' => true, // Ensure HTTPS is used
        'samesite' => 'Strict' // Protect against CSRF attacks
    ]);
}

// Restore session from cookie if not already set
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    $_SESSION['user_id'] = filter_var($_COOKIE['user_id'], FILTER_VALIDATE_INT); // Restore session from cookie and validate as integer
}
?>
<div id="favoritesListContainer">
    <h3>Your Favorites</h3>
    <ul id="favoritesList"></ul>
</div>