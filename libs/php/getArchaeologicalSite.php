<?php
require __DIR__ . '/../../vendor/autoload.php';


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();


header('Content-Type: application/json');
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


$country = $_GET['iso_a2'];
$username = $_ENV['GEONAMES_USERNAME'];

$url = "http://api.geonames.org/searchJSON?q=archaeological&country=$country&maxRows=50&username=$username";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);


if (curl_errno($ch)) {
    echo json_encode([
        'error' => curl_error($ch),
        'status' => [
            'code' => curl_errno($ch),
            'name' => "Failure - cURL",
            'description' => curl_error($ch),
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
            'name' => "Failure - JSON",
            'description' => json_last_error_msg(),
            'seconds' => number_format((microtime(true) - $executionStartTime), 3)
        ]
    ]);
    exit;
}


if (!isset($data['geonames']) || empty($data['geonames'])) {
    echo json_encode([
        'error' => 'No archaeological sites found.',
        'status' => [
            'code' => 404,
            'name' => 'Not Found',
            'description' => 'No archaeological sites found.',
            'seconds' => number_format((microtime(true) - $executionStartTime), 3)
        ]
    ]);
    exit;
}


$features = [];
foreach ($data['geonames'] as $site) {
    if (isset($site['lat'], $site['lng'])) {
        $features[] = [
            'type' => 'Feature',
            'properties' => [
                'name' => $site['name'],
                'description' => $site['fcodeName']
            ],
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [(float)$site['lng'], (float)$site['lat']]
            ]
        ];
    }
}


echo json_encode([
    'status' => [
        'code' => 200,
        'name' => 'Success',
        'description' => 'Data retrieved successfully',
        'seconds' => number_format((microtime(true) - $executionStartTime), 3)
    ],
    'features' => $features
]);
?>

