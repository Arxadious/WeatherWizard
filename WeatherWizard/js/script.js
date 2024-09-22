// Weather fetching function
function getWeather() {
    const location = document.getElementById('locationInput').value;
    if (location === '') {
        alert('Please enter a location');
        return;
    }

    const apiKey = '68492af478c6a18b71129da4b72cf475';  
    const apiUrl = `http://api.openweathermap.org/data/2.5/weather?q=${location}&units=metric&appid=${apiKey}`;

    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            if (data.cod !== 200) {
                alert('Location not found!');
            } else {
                displayWeather(data);
                changeBackgroundColor(data.weather[0].main);  // Change background based on weather
            }
        })
        .catch(error => console.error('Error fetching weather data:', error));
}

function displayWeather(data) {
    const weatherIconCode = data.weather[0].icon;  
    const localIconUrl = `images/${weatherIconCode}.png`;  

    const weatherDisplay = document.getElementById('weatherDisplay');
    weatherDisplay.innerHTML = `
        <h2>${data.name}</h2>
        <p>${data.weather[0].description}</p>
        <p>Temperature: ${data.main.temp}°C</p>
        <img class="weather-icon" src="${localIconUrl}" alt="Weather icon">
    `;
}

function changeBackgroundColor(weatherMain) {
    const body = document.body;

    // Clear existing classes
    body.classList.remove('sunny', 'cloudy', 'rainy', 'stormy', 'snowy', 'clear-night', 'mist');

    // Add a new class based on the weather condition
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

// Authentication modal handling
const loginBtn = document.getElementById('loginBtn');
const logoutBtn = document.getElementById('logoutBtn');
const authModal = document.getElementById('authModal');
const closeModal = document.getElementById('closeModal');  
const authForm = document.getElementById('authForm');
const formTitle = document.getElementById('formTitle');
const submitBtn = document.getElementById('submitBtn');
const switchBtn = document.getElementById('switchBtn');
const switchText = document.getElementById('switchText');

// Show the login modal when the Login button is clicked
loginBtn.addEventListener('click', () => {
    authModal.style.display = 'flex'; 
});

// Close the modal when the close (X) button is clicked
closeModal.addEventListener('click', () => {
    authModal.style.display = 'none'; 
});

// Close modal on form submit (add proper validation later)
authForm.addEventListener('submit', (e) => {
    e.preventDefault();  

    // Simulate successful login or registration
    authModal.style.display = 'none'; 
    loginBtn.style.display = 'none';  
    logoutBtn.style.display = 'block'; 
});

// Toggle between Login and Register
switchBtn.addEventListener('click', (e) => {
    e.preventDefault();
    if (formTitle.textContent === 'Login') {
        formTitle.textContent = 'Register';
        submitBtn.textContent = 'Register';
        switchText.innerHTML = 'Already have an account? <a href="#" id="switchBtn">Login</a>';
    } else {
        formTitle.textContent = 'Login';
        submitBtn.textContent = 'Login';
        switchText.innerHTML = 'Don\'t have an account? <a href="#" id="switchBtn">Register</a>';
    }
});

// Show login again on logout
logoutBtn.addEventListener('click', () => {
    logoutBtn.style.display = 'none';  
    loginBtn.style.display = 'block';  
});
