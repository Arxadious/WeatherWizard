<?php
include 'config.php';  // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Hash the password for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Check if the username already exists
    $sql = "SELECT id FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);

    if ($stmt->rowCount() > 0) {
        echo "Username already exists. Please choose another.";
    } else {
        // Insert the new user into the database
        $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$username, $hashed_password])) {
            echo "Registration successful. You can now <a href='login.php'>login</a>";
        } else {
            echo "Error: Could not register user.";
        }
    }
}
?>
