<?php
// Set CORS headers - Restrict for production
header("Access-Control-Allow-Origin: https://weatherwizard.kesug.com");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
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

session_start();
session_regenerate_id(true); // Regenerate session ID to prevent fixation
include 'config.php';

// Check if location is provided
if (isset($_GET['location'])) {
    $location = filter_var($_GET['location'], FILTER_SANITIZE_FULL_SPECIAL_CHARS); // Sanitize the user input

    // External API details
    $apiKey = '68492af478c6a18b71129da4b72cf475'; 
    $apiUrl = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($location) . "&units=metric&appid=" . $apiKey;

    // Fetch weather data with error handling
    $response = @file_get_contents($apiUrl);
    if ($response === FALSE) {
        echo json_encode(['error' => 'Failed to connect to weather service']);
        exit();
    }

    $weatherData = json_decode($response, true);

    // Check if the response is successful
    if (isset($weatherData['cod']) && $weatherData['cod'] === 200) {
        $result = [
            'location' => htmlspecialchars($weatherData['name'], ENT_QUOTES, 'UTF-8'), // Sanitize output
            'weather' => htmlspecialchars($weatherData['weather'][0]['description'], ENT_QUOTES, 'UTF-8'),
            'temperature' => $weatherData['main']['temp'],
            'icon' => htmlspecialchars($weatherData['weather'][0]['icon'], ENT_QUOTES, 'UTF-8')
        ];

        // Save to favorites if user is logged in
        if (isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) {
            $user_id = intval($_SESSION['user_id']);
            try {
                // Prevent duplicate entries
                $checkSql = 'SELECT 1 FROM favorites WHERE user_id = ? AND location = ?';
                $checkStmt = $pdo->prepare($checkSql);
                $checkStmt->execute([$user_id, $weatherData['name']]);
                
                if ($checkStmt->fetch() === false) {
                    // Insert into favorites if it does not exist
                    $stmt = $pdo->prepare('INSERT INTO favorites (user_id, location) VALUES (?, ?)');
                    $stmt->execute([$user_id, $weatherData['name']]);
                }
            } catch (Exception $e) {
                error_log($e->getMessage()); // Log the error internally
            }
        }
    } else {
        $result = ['error' => 'Location not found'];
    }

    echo json_encode($result);
} else {
    echo json_encode(['error' => 'No location provided']);
}
?>