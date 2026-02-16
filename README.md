# Gazetteer

## Overview
This project is a web application that provides detailed country information, including borders, weather, news, Wikipedia summaries, nearby cities, hospitals, airports, currency conversion, railways, and archaeological sites. The application uses multiple APIs to fetch real-time data and display it interactively on a map.

## Features
- Fetch and display country borders
- Get real-time weather data
- Fetch Wikipedia summaries
- Retrieve a list of major cities
- Locate hospitals and airports
- Get currency conversion rates
- Display the latest news for the selected country
- Fetch railways for the selected country
- Fetch archaeological sites for the selected country
- Detect user location and center the map accordingly

## Technologies Used
- JavaScript (jQuery, Leaflet.js)
- PHP (API integrations, backend processing)
- HTML/CSS (Bootstrap for UI)

## APIs Required
This project requires API keys from various providers. Ensure you have the following API keys before running the application:

- **OpenWeather API** (`OPENWEATHER_API_KEY`) - Fetches weather data
- **GeoNames API** (`GEONAMES_USERNAME`) - Fetches country and city details
- **RapidAPI** (`RAPIDAPI_KEY`) - Used for various data retrievals
- **Geoapify API** (`GEOAPIFY_API_KEY`) - Fetches hospitals and places of interest
- **OpenCage API** (`OPENCAGE_API_KEY`) - Geocoding services
- **NewsAPI** (`NEWSAPI_KEY`) - Fetches the latest news articles
- **API Ninjas** (`API_NINJAS_API_KEY`) - Fetches airport data

## Setup Instructions

### 1. Download and Place the Project
Ensure the project files are placed in the appropriate directory on your internal server or local environment. If using XAMPP, place them inside the htdocs folder.

### 2. Install Dependencies
Ensure you have **Composer** installed, then run:
```bash
composer install
```

### 3. Set Up Environment Variables
Create a `.env` file in the project root directory and add the following:
```
OPENWEATHER_API_KEY=your_openweather_api_key
MAGICAPI_API_KEY=your_magicapi_key
GEONAMES_USERNAME=your_geonames_username
RAPIDAPI_KEY=your_rapidapi_key
GEOAPIFY_API_KEY=your_geoapify_key
OPENCAGE_API_KEY=your_opencage_key
NEWSAPI_KEY=your_newsapi_key
API_NINJAS_API_KEY=your_api_ninjas_key
```

### 4. Start the Development Server
If using a local server like XAMPP, place the project inside the `htdocs` folder and start Apache and MySQL. Otherwise, use PHP's built-in server:
```bash
php -S localhost:8000
```

## API Endpoints

### `libs/php/getCountryInfo.php`
Fetches country information based on country ISO code.

### `libs/php/getLocation.php`
Retrieves the user's location based on IP or GPS data.

### `libs/php/getWeather.php`
Retrieves weather data for a given latitude and longitude.

### `libs/php/getArchaeologicalSite.php`
Fetches archaeological sites for the selected country.

### `libs/php/getWikipediaInfo.php`
Retrieves a wikipedia summary of the selected country.

### `libs/php/getCities.php`
Retrieves a list of major cities within a country.

### `libs/php/getHospitals.php`
Fetches hospital locations using Geoapify API.

### `libs/php/getAirports.php`
Retrieves airport locations for a given country.

### `libs/php/getCurrencyConversion.php`
Fetches currency exchange rates based on the country's currency.

### `libs/php/getNews.php`
Fetches the latest news articles related to the selected country.

### `libs/php/getRailways.php` 
Fetches railway data for the selected country.

## License
This project is licensed under the MIT License.




