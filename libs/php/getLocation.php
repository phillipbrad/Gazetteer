<?php

header('Content-Type: application/json');

require __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env only if it exists (for local development)
if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
}

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if (!isset($_GET['latitude']) || !isset($_GET['longitude'])) {
    echo json_encode(['error' => 'Latitude and Longitude are required.']);
    exit;
}

$latitude = $_GET['latitude'];
$longitude = $_GET['longitude'];

$apiKey = $_ENV['OPENCAGE_API_KEY'] ?? getenv('OPENCAGE_API_KEY');

$url = 'https://api.opencagedata.com/geocode/v1/json?q=' . urlencode($latitude . ',' . $longitude) . '&key=' . $apiKey;

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(['error' => 'Request Error: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);

$data = json_decode($response, true);

if (isset($data['results']) && count($data['results']) > 0) {
    $location = $data['results'][0]['formatted'] ?? 'Unknown location';
    $countryCode = strtoupper($data['results'][0]['components']['country_code'] ?? '');

    echo json_encode([
        'latitude' => $latitude,
        'longitude' => $longitude,
        'location' => $location,
        'iso2' => $countryCode
    ]);
} else {
    echo json_encode(['error' => 'No results found for the given coordinates.']);
}
