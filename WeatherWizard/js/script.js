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
}

// Helper function to delete a cookie
function deleteCookie(name) {
    document.cookie = `${name}=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC; SameSite=Lax`;
}

// Variable to store user favorites
let favorites = [];

// Weather fetching function
function getWeather() {
    const locationInput = document.getElementById('locationInput').value.trim();
    if (!locationInput || /[^a-zA-Z0-9\s,]/.test(locationInput)) {
        alert('Please enter a valid location');
        return;
    }
    fetchWeatherData(locationInput);
}

// Helper function to fetch weather data and display it
function fetchWeatherData(location) {
    const apiKey = '68492af478c6a18b71129da4b72cf475';
    const apiUrl = `https://api.openweathermap.org/data/2.5/weather?q=${encodeURIComponent(location)}&units=metric&appid=${apiKey}`;

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

    const action = favorites.includes(cityName) ? 'remove' : 'add';

    fetch('php/saveFavorites.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ user_id: userId, location: cityName, action: action })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (action === 'add') {
                favorites.push(cityName);
            } else {
                favorites = favorites.filter(favorite => favorite !== cityName);
            }
            updateFavoriteStar(cityName);
            updateFavoriteDisplay();
        } else {
            console.error('Failed to save favorite:', data.message);
        }
    })
    .catch(error => console.error('Error saving favorite:', error));
}

// Update the favorite star icon based on the favorite state
function updateFavoriteStar(cityName) {
    const favoriteIcon = document.getElementById('favoriteIcon');
    if (favoriteIcon) {
        favoriteIcon.classList.toggle('active', favorites.includes(cityName));
        favoriteIcon.style.color = favoriteIcon.classList.contains('active') ? 'gold' : '#ccc';
    }
}

// Load favorite locations from server
function loadFavorites() {
    const userId = getCookie('user_id');
    if (!userId) {
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
            favorites = data.favorites; // Update with new data from server
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
            <button class="remove-favorite" data-location="${city}">-</button>
        `;

        // Add event listener to fetch weather data for the selected city
        li.querySelector('.favorite-city-name').addEventListener('click', () => {
            fetchWeatherData(city);
        });

        // Add event listener to remove the favorite city
        li.querySelector('.remove-favorite').addEventListener('click', () => {
            toggleFavorite(city);
        });

        favoriteList.appendChild(li);
    });
}

// Change background color based on weather and time
function changeBackgroundColor(weatherCondition) {
    const body = document.body;
    let color = '#ffffff';
    switch (weatherCondition.toLowerCase()) {
        case 'clear':
            color = isDayTime() ? '#87CEEB' : '#2E3B4E';
            break;
        case 'clouds':
            color = '#B0C4DE';
            break;
        case 'rain':
        case 'drizzle':
            color = '#778899';
            break;
        case 'thunderstorm':
            color = '#3A3A3C';
            break;
        case 'snow':
            color = '#ECEFF1';
            break;
        case 'mist':
        case 'fog':
            color = '#A9A9A9';
            break;
        default:
            color = '#ffffff';
            break;
    }
    body.style.backgroundColor = color;
}

// Determine if it is daytime or nighttime
function isDayTime() {
    const hours = new Date().getHours();
    return hours >= 6 && hours <= 18;
}

// Logout function to handle session termination
function logout() {
    deleteCookie('user_id'); // Delete user cookie
    // Hide logout button and show login button
    document.getElementById('loginBtn').style.display = 'block';
    document.getElementById('logoutBtn').style.display = 'none';
    // Clear favorites list after logout
    document.getElementById('favoritesList').innerHTML = '';
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
        e.preventDefault();
        const formData = new FormData(authForm);
        const actionUrl = authForm.action;

        // Password validation - must be at least 5 characters, contain at least one letter, one number, and one special character
        const password = formData.get('password');
        const passwordRegex = /^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{5,}$/;

        if (!passwordRegex.test(password)) {
            alert('Password must be at least 5 characters long and contain at least one letter, one number, and one special character.');
            return;
        }

        fetch(actionUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.user_id) {
                setCookie('user_id', data.user_id, 30);
                loginBtn.style.display = 'none';
                logoutBtn.style.display = 'block';
                modal.style.display = 'none';
                loadFavorites();
            } else {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('An error occurred:', error);
            alert('An error occurred. Please try again.');
        });
    });

    // Attach the logout function to the logout button
    if (logoutBtn) {
        logoutBtn.addEventListener('click', () => {
            logout();
        });
    }

    // Check if the user is logged in on page load
    const userId = getCookie('user_id');
    if (userId) {
        loginBtn.style.display = 'none';
        logoutBtn.style.display = 'block';
        loadFavorites();
    } else {
        loginBtn.style.display = 'block';
        logoutBtn.style.display = 'none';
    }
});
