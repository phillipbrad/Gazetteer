<?php

require __DIR__ . '/../../vendor/autoload.php';

// Load .env only if it exists (for local development)
if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
}
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');

$executionStartTime = microtime(true);

if (!isset($_GET['iso_a2'])) {
    echo json_encode([
        'status' => [
            'code' => 400,
            'name' => "Failure - Missing Parameter",
            'description' => 'Country ISO code is required.',
            'seconds' => number_format((microtime(true) - $executionStartTime), 3)
        ],
        'data' => null
    ]);
    exit;
}

$iso_a2 = strtoupper(trim($_GET['iso_a2']));

$apiKey = $_ENV['API_NINJAS_API_KEY'];
$url = "https://api.api-ninjas.com/v1/airports?country=$iso_a2";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "X-Api-Key: $apiKey"
]);

$response = curl_exec($ch);

$cURLERROR = curl_errno($ch);
curl_close($ch);

if ($cURLERROR) {
    echo json_encode([
        'status' => [
            'code' => $cURLERROR,
            'name' => "Failure - cURL",
            'description' => curl_strerror($cURLERROR),
            'seconds' => number_format((microtime(true) - $executionStartTime), 3)
        ],
        'data' => null
    ]);
    exit;
}


$data = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'status' => [
            'code' => json_last_error(),
            'name' => "Failure - JSON",
            'description' => json_last_error_msg(),
            'seconds' => number_format((microtime(true) - $executionStartTime), 3)
        ],
        'data' => null
    ]);
    exit;
}


if (empty($data)) {
    echo json_encode([
        'status' => [
            'code' => 404,
            'name' => "Failure - API",
            'description' => "No airports found",
            'seconds' => number_format((microtime(true) - $executionStartTime), 3)
        ],
        'data' => null
    ]);
    exit;
}


$features = [];
foreach ($data as $airport) {
    if (!isset($airport['latitude'], $airport['longitude'])) {
        continue;
    }

    $features[] = [
        'type' => 'Feature',
        'properties' => [
            'name' => $airport['name'],
            'city' => $airport['city'],
            'country' => $airport['country']
        ],
        'geometry' => [
            'type' => 'Point',
            'coordinates' => [(float)$airport['longitude'], (float)$airport['latitude']]
        ]
    ];
}

echo json_encode([
    'features' => $features,
    'status' => [
        'code' => 200,
        'name' => "Success",
        'description' => "Airports data retrieved successfully",
        'seconds' => number_format((microtime(true) - $executionStartTime), 3)
    ]
]);
