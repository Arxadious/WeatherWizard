<?php
header("Access-Control-Allow-Origin: https://weatherwizard.kesug.com"); // Restrict to trusted domain for production
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
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

include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate email input
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password']);

    // Validate input
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required.']);
        exit();
    }

    if ($email === false) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        exit();
    }

    try {
        // Limit login attempts: Check if the user has exceeded the allowed attempts
        $lockout_time = 300; // Lockout for 5 minutes after too many failed attempts
        $max_attempts = 5;
        $sql = "SELECT failed_attempts, last_failed_attempt, password, id FROM users WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $failed_attempts = $user['failed_attempts'];
            $last_failed_attempt = strtotime($user['last_failed_attempt']);
            $current_time = time();

            // Check if the account is locked due to too many failed attempts
            if ($failed_attempts >= $max_attempts && ($current_time - $last_failed_attempt) < $lockout_time) {
                $remaining_lockout_time = $lockout_time - ($current_time - $last_failed_attempt);
                $lockout_datetime = date('Y-m-d H:i:s', $last_failed_attempt);
                echo json_encode([
                    'success' => false,
                    'message' => "Account locked due to too many failed login attempts. You were locked out on $lockout_datetime. Please try again in " . round($remaining_lockout_time / 60) . " minute(s)."
                ]);
                exit();
            }

            // Verify the password hash matches the provided password
            if (password_verify($password, $user['password'])) {
                // Reset failed attempts after successful login
                $sql = "UPDATE users SET failed_attempts = 0, last_failed_attempt = NULL WHERE email = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$email]);

                // Set session and cookie for successful login
                session_regenerate_id(true); // Prevent session fixation attacks
                $_SESSION['user_id'] = $user['id'];
                setcookie('user_id', $user['id'], time() + (86400 * 30), "/", "", true, true);

                // Respond with success and provide user_id in the response
                echo json_encode(['success' => true, 'message' => 'Login successful', 'user_id' => $user['id']]);
            } else {
                // Increment failed attempts on unsuccessful login
                $sql = "UPDATE users SET failed_attempts = failed_attempts + 1, last_failed_attempt = NOW() WHERE email = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$email]);

                // Calculate remaining attempts
                $remaining_attempts = $max_attempts - ($user['failed_attempts'] + 1);
                if ($remaining_attempts > 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid email or password. You have ' . $remaining_attempts . ' attempt(s) left.']);
                } else {
                    $lockout_datetime = date('Y-m-d H:i:s'); // Timestamp of lockout
                    echo json_encode(['success' => false, 'message' => "Too many failed attempts. Your account is now locked. You were locked out on $lockout_datetime. Please try again later."]);
                }
            }
        } else {
            // Send error response for invalid credentials without leaking info about which part is incorrect
            echo json_encode(['success' => false, 'message' => 'Invalid email or password.']);
        }
    } catch (Exception $e) {
        // Handle any errors that occurred during the process
        error_log($e->getMessage()); // Log the error internally
        echo json_encode(['success' => false, 'message' => 'An error occurred while logging in. Please try again later.']);
        exit();
    }
} else {
    // Send error response if request method is not POST
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
