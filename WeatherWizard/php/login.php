<?php
session_start();
include 'config.php';  // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Look up the user in the database
    $sql = "SELECT id, password FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // Successful login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $username;
        echo "Login successful. Welcome, " . $username;

        header("Location: index.html");
        exit();
    } else {
        echo "Invalid username or password.";
    }
}
?>
