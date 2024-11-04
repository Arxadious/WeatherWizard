<?php
// Set CORS headers for production use
header("Access-Control-Allow-Origin: https://weatherwizard.kesug.com"); // Restrict to trusted domain for production
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json');

// Additional security headers
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");

// Check for preflight requests (OPTIONS request)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

try {
    session_start(); // Start the session
    session_regenerate_id(true); // Prevent session fixation attacks

    // Unset all session variables
    $_SESSION = array();

    // If it's desired to kill the session, also delete the session cookie.
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    // Destroy the session
    if (!session_destroy()) {
        throw new Exception("Failed to destroy session");
    }

    // Clear the user_id cookie with secure attributes
    if (!setcookie('user_id', '', time() - 3600, "/", "", true, true)) {
        throw new Exception("Failed to clear user_id cookie");
    }

    // Respond with a JSON message confirming logout
    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
} catch (Exception $e) {
    // Log the error for debugging purposes
    error_log($e->getMessage());

    // Respond with an error message if any step fails
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Logout failed: ' . $e->getMessage()]);
}
?>