function getWeather() {
    const location = document.getElementById('locationInput').value;
    if (location === '') {
        alert('Please enter a location');
        return;
    }

    const apiKey = '68492af478c6a18b71129da4b72cf475';  // Your OpenWeatherMap API key
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
    const weatherIconCode = data.weather[0].icon;  // Get the weather icon code from the API response
    const localIconUrl = `images/${weatherIconCode}.png`;  // Use the icon code to fetch the correct image

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
            body.classList.add('clear-night');  // Default background for unknown weather conditions
    }
}
