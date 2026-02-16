<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');


$executionStartTime = microtime(true);


$jsonFilePath = "../data/countryBorders.geo.json";


if (!file_exists($jsonFilePath)) {
    echo json_encode([
        'error' => 'JSON file does not exist',
        'status' => [
            'code' => 404,
            'name' => 'Not Found',
            'description' => 'The specified JSON file does not exist.',
            'seconds' => number_format((microtime(true) - $executionStartTime), 3)
        ]
    ]);
    exit;
}


$json = file_get_contents($jsonFilePath);

if ($json === false) {
    echo json_encode([
        'error' => 'Failed to read JSON file',
        'status' => [
            'code' => 500,
            'name' => 'Internal Server Error',
            'description' => 'An error occurred while reading the JSON file.',
            'seconds' => number_format((microtime(true) - $executionStartTime), 3)
        ]
    ]);
    exit;
}


$data = json_decode($json, true);

if ($data === null) {
    echo json_encode([
        'error' => 'Failed to decode JSON',
        'status' => [
            'code' => json_last_error(),
            'name' => 'JSON Decode Error',
            'description' => json_last_error_msg(),
            'seconds' => number_format((microtime(true) - $executionStartTime), 3)
        ]
    ]);
    exit;
}


$features = $data['features'] ?? [];

if (empty($features)) {
    echo json_encode([
        'error' => 'No features found in JSON',
        'status' => [
            'code' => 404,
            'name' => 'Not Found',
            'description' => 'No features found in the JSON data.',
            'seconds' => number_format((microtime(true) - $executionStartTime), 3)
        ]
    ]);
    exit;
}


$countries = [];

foreach ($features as $feature) {
    $iso_a2 = $feature['properties']['iso_a2'] ?? null;
    $name = $feature['properties']['name'] ?? null;

    if ($iso_a2 && $name) {
        $countries[] = [
            'iso_a2' => $iso_a2,
            'name' => $name
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
    'features' => $countries
]);

?>
