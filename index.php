<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WeatherWizard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1 class="logo">WeatherWizard</h1>
        <div class="search-bar">
            <input type="text" id="locationInput" placeholder="Search: City or Zip Code">
            <button onclick="getWeather()">Search</button>
        </div>
        <nav>
            <button id="loginBtn" class="auth-btn">Login</button>
            <button id="logoutBtn" class="auth-btn" style="display:none;">Logout</button>
        </nav>
    </header>

    <main>
        <!-- Favorites Section -->
        <div id="favoritesListContainer">
            <h3>Favorites</h3>
            <ul id="favoritesList"></ul>
        </div>

        <!-- Weather Display -->
        <div id="weatherDisplay" class="weather-info"></div>
    </main>

    <!-- Login/Register Modal -->
    <div id="authModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close-btn" id="closeModal">&times;</span>
            <form id="authForm" action="php/register.php" method="POST">
                <h2 id="formTitle">Login</h2>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <button type="submit" id="submitBtn">Login</button>
            </form>
            <p id="switchText">Don't have an account? <a href="#" id="switchBtn">Register</a></p>
        </div>
    </div>

    <footer>
        <p>Powered by OpenWeatherMap</p>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>
