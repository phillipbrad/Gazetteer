<?php
require __DIR__ . '/../../vendor/autoload.php';


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();


header('Content-type: application/json');
header('Access-Control-Allow-Origin: *');


$executionStartTime = microtime(true);


if (!isset($_GET['iso_a2'])) {
    echo json_encode([
        'error' => 'Country ISO code is required.',
        'status' => [
            'code' => 400,
            'name' => 'Bad Request',
            'description' => 'Country ISO code is required.',
            'seconds' => number_format((microtime(true) - $executionStartTime), 3)
        ]
    ]);
    exit;
}

$iso_a2 = $_GET['iso_a2'];
$geonames_username = $_ENV['GEONAMES_USERNAME'];


$url = "http://api.geonames.org/searchJSON?country=$iso_a2&maxRows=100&featureCode=PPL&orderby=population&username=$geonames_username";


$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);


$response = curl_exec($ch);


if (curl_errno($ch)) {
    $error_msg = 'Curl error: ' . curl_error($ch);
    error_log($error_msg);
    echo json_encode([
        'error' => $error_msg,
        'status' => [
            'code' => curl_errno($ch),
            'name' => 'Failure - cURL',
            'description' => $error_msg,
            'seconds' => number_format((microtime(true) - $executionStartTime), 3)
        ]
    ]);
    curl_close($ch);
    exit;
}

curl_close($ch);


$data = json_decode($response, true);


if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'error' => json_last_error_msg(),
        'status' => [
            'code' => json_last_error(),
            'name' => 'Failure - JSON',
            'description' => json_last_error_msg(),
            'seconds' => number_format((microtime(true) - $executionStartTime), 3)
        ]
    ]);
    exit;
}


$features = [];
if (!empty($data['geonames']) && is_array($data['geonames'])) {
    foreach ($data['geonames'] as $city) {
        if (!empty($city['lat']) && !empty($city['lng']) && isset($city['population']) && $city['population'] > 10000) {
            $features[] = [
                'type' => 'Feature',
                'properties' => [
                    'name' => $city['name'],
                    'population' => $city['population'],
                    'country' => $city['countryName']
                ],
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float)$city['lng'], (float)$city['lat']]
                ]
            ];
        }
    }
}

// Sort cities by population in descending order
// This had to be done as some of the cities features being recieved had 0 population and or were just areas of a city
usort($features, function ($a, $b) {
    return $b['properties']['population'] - $a['properties']['population'];
});

// Limit to top 40 cities by population to the bigger cities
$features = array_slice($features, 0, 40);


echo json_encode([
    'type' => 'FeatureCollection',
    'features' => $features,
    'status' => [
        'code' => 200,
        'name' => 'Success',
        'description' => 'Cities retrieved successfully',
        'seconds' => number_format((microtime(true) - $executionStartTime), 3)
    ]
]);

?>
