<?php
session_start();
include 'config.php';

if (isset($_GET['location'])) {
    $location = $_GET['location'];
    $apiKey = '68492af478c6a18b71129da4b72cf475'; 
    $apiUrl = "http://api.openweathermap.org/data/2.5/weather?q={$location}&units=metric&appid={$apiKey}";

    $response = file_get_contents($apiUrl);
    $weatherData = json_decode($response, true);

    if ($weatherData['cod'] === 200) {
        $result = [
            'location' => $weatherData['name'],
            'weather' => $weatherData['weather'][0]['description'],
            'temperature' => $weatherData['main']['temp'],
            'icon' => $weatherData['weather'][0]['icon']
        ];

        if (isset($_SESSION['user_id'])) {
            $stmt = $pdo->prepare('INSERT INTO favorites (user_id, location) VALUES (?, ?)');
            $stmt->execute([$_SESSION['user_id'], $weatherData['name']]);
        }
    } else {
        $result = ['error' => 'Location not found'];
    }
    echo json_encode($result);
} else {
    echo json_encode(['error' => 'No location provided']);
}
?>
