// Helper function to get a cookie by name
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}

// Helper function to set a cookie
function setCookie(name, value, days) {
    let expires = "";
    if (days) {
        const date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = `${name}=${value || ""}${expires}; path=/; SameSite=Lax`;
    console.log(`Cookie set: ${name}=${value}; expires=${expires}`);
}

// Helper function to delete a cookie
function deleteCookie(name) {
    document.cookie = `${name}=; path=/; max-age=0`;
    console.log(`Cookie deleted: ${name}`);
}

// Variable to store user favorites
let favorites = [];

// Weather fetching function
function getWeather() {
    const locationInput = document.getElementById('locationInput').value;
    if (locationInput === '') {
        alert('Please enter a location');
        return;
    }
    fetchWeatherData(locationInput);
}

// Helper function to fetch weather data and display it
function fetchWeatherData(location) {
    const apiKey = '68492af478c6a18b71129da4b72cf475';
    const apiUrl = `http://api.openweathermap.org/data/2.5/weather?q=${location}&units=metric&appid=${apiKey}`;

    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            if (data.cod !== 200) {
                alert('Location not found!');
            } else {
                const officialCityName = data.name;
                displayWeather(data, officialCityName);
                changeBackgroundColor(data.weather[0].main);
                updateFavoriteStar(officialCityName);
            }
        })
        .catch(error => console.error('Error fetching weather data:', error));
}

// Display weather data in the HTML
function displayWeather(data, cityName) {
    const weatherIconCode = data.weather[0].icon;
    const localIconUrl = `images/${weatherIconCode}.png`;

    const weatherDisplay = document.getElementById('weatherDisplay');
    weatherDisplay.innerHTML = `
        <span id="favoriteIcon" class="star">&#9734;</span>
        <h2>${cityName}</h2>
        <p>${data.weather[0].description}</p>
        <p>Temperature: ${data.main.temp}°C</p>
        <img class="weather-icon" src="${localIconUrl}" alt="Weather icon">
    `;

    const favoriteIcon = document.getElementById('favoriteIcon');
    favoriteIcon.addEventListener('click', () => toggleFavorite(cityName));
}

// Toggle favorite locations
function toggleFavorite(cityName) {
    const userId = getCookie('user_id');
    if (!userId) {
        alert("Please log in to save favorites");
        return;
    }

    if (favorites.includes(cityName)) {
        removeFavorite(cityName);
    } else {
        favorites.push(cityName);
        saveFavoritesToServer(cityName);
    }
}

// Save favorite locations to server
function saveFavoritesToServer(cityName) {
    const userId = getCookie('user_id');
    if (!userId) {
        console.log("Cannot save favorites: user is not logged in");
        return;
    }

    fetch('php/saveFavorites.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ user_id: userId, location: cityName })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Favorite saved successfully:', data);
            updateFavoriteStar(cityName, true);
            updateFavoriteDisplay();
        } else {
            console.error('Failed to save favorite:', data.message);
        }
    })
    .catch(error => console.error('Error saving favorite:', error));
}

// Remove favorite and update the server
function removeFavorite(city) {
    const userId = getCookie('user_id');
    if (!userId) {
        console.log("Cannot remove favorite: user is not logged in");
        return;
    }

    fetch('php/removeFavorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ user_id: userId, location: city })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Favorite removed successfully');
            favorites = favorites.filter(favorite => favorite !== city);
            updateFavoriteStar(city, false);
            updateFavoriteDisplay();
        } else {
            console.error('Failed to remove favorite:', data.message);
        }
    })
    .catch(error => console.error('Error removing favorite:', error));
}

// Update the favorite star icon based on the favorite state
function updateFavoriteStar(cityName, isFavorite) {
    const favoriteIcon = document.getElementById('favoriteIcon');
    if (favoriteIcon) {
        if (isFavorite !== undefined) {
            favoriteIcon.classList.toggle('active', isFavorite);
        } else {
            favoriteIcon.classList.toggle('active', favorites.includes(cityName));
        }

        favoriteIcon.style.color = favoriteIcon.classList.contains('active') ? 'gold' : '#ccc';
    }
}

// Load favorite locations from server
function loadFavorites() {
    const userId = getCookie('user_id');
    if (!userId) {
        console.log("No user logged in, skipping loading favorites");
        return;
    }

    fetch('php/getFavorites.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ user_id: userId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            favorites = data.favorites;
            console.log("Favorites loaded successfully");
            updateFavoriteDisplay();
        } else {
            console.error("Failed to load favorites: ", data.message);
        }
    })
    .catch(error => console.error('Error loading favorites:', error));
}

// Update favorite locations in the HTML
function updateFavoriteDisplay() {
    const favoriteList = document.getElementById('favoritesList');
    favoriteList.innerHTML = '';

    favorites.forEach(city => {
        const li = document.createElement('li');
        li.classList.add('favorite-item');
        li.innerHTML = `
            <span class="favorite-city-name">${city}</span>
            <button class="remove-favorite" onclick="removeFavorite('${city}')">-</button>
        `;
        li.querySelector('.favorite-city-name').addEventListener('click', () => {
            fetchWeatherData(city);
        });
        favoriteList.appendChild(li);
    });
}

// Background color switcher based on weather
function changeBackgroundColor(weatherMain) {
    const body = document.body;

    body.classList.remove('sunny', 'cloudy', 'rainy', 'stormy', 'snowy', 'clear-night', 'mist');

    switch (weatherMain.toLowerCase()) {
        case 'clear':
            body.classList.add('sunny');
            break;
        case 'clouds':
            body.classList.add('cloudy');
            break;
        case 'rain':
            body.classList.add('rainy');
            break;
        case 'thunderstorm':
            body.classList.add('stormy');
            break;
        case 'snow':
            body.classList.add('snowy');
            break;
        case 'mist':
        case 'fog':
            body.classList.add('mist');
            break;
        default:
            body.classList.add('clear-night');
    }
}

// Event listeners and DOMContentLoaded
document.addEventListener('DOMContentLoaded', function () {
    const loginBtn = document.getElementById('loginBtn');
    const logoutBtn = document.getElementById('logoutBtn');
    const closeModalBtn = document.getElementById('closeModal');
    const modal = document.getElementById('authModal');
    const switchBtn = document.getElementById('switchBtn');
    const formTitle = document.getElementById('formTitle');
    const submitBtn = document.getElementById('submitBtn');
    const authForm = document.getElementById('authForm');

    // Show login modal when login button is clicked
    if (loginBtn) {
        loginBtn.addEventListener('click', () => {
            modal.style.display = 'flex';
            formTitle.textContent = 'Login';
            submitBtn.textContent = 'Login';
            authForm.action = 'php/login.php';
        });
    }

    // Switch between login and register
    if (switchBtn) {
        switchBtn.addEventListener('click', () => {
            if (formTitle.textContent === 'Login') {
                formTitle.textContent = 'Register';
                submitBtn.textContent = 'Register';
                authForm.action = 'php/register.php';
                switchBtn.textContent = 'Login';
            } else {
                formTitle.textContent = 'Login';
                submitBtn.textContent = 'Login';
                authForm.action = 'php/login.php';
                switchBtn.textContent = 'Register';
            }
        });
    }

    // Close modal when close button is clicked
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    }

    // Login/Register form submission
    authForm.addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent default form submission
        const formData = new FormData(authForm);
        const actionUrl = authForm.action;

        fetch(actionUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.user_id) {
                setCookie('user_id', data.user_id, 30); // Set cookie for user ID, expires in 30 days
                loginBtn.style.display = 'none';
                logoutBtn.style.display = 'block';
                modal.style.display = 'none'; // Hide modal
                loadFavorites(); // Load favorites after login/register
                console.log(`${formTitle.textContent} successful`);
            } else {
                alert(data.message); // Display error message from server
            }
        })
        .catch(error => {
            console.error('An error occurred:', error);
            alert('An error occurred. Please try again.');
        });
    });

    // Logout functionality
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            fetch('php/logout.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        deleteCookie('user_id');
                        logoutBtn.style.display = 'none';
                        loginBtn.style.display = 'block';
                        favorites = [];
                        updateFavoriteDisplay();
                        console.log('User logged out successfully');
                    } else {
                        console.error('Logout failed:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error logging out:', error);
                    alert('An error occurred during logout.');
                });
        });
    }

    // Check if the user is logged in on page load
    const userId = getCookie('user_id');
    if (userId) {
        loginBtn.style.display = 'none';
        logoutBtn.style.display = 'block';
        loadFavorites();
        console.log('User is logged in on page load');
    } else {
        loginBtn.style.display = 'block';
        logoutBtn.style.display = 'none';
    }
});
