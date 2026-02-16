<?php

// Increase execution time for slow Overpass API queries
set_time_limit(60);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');


if (!isset($_GET['iso_a2']) || empty($_GET['iso_a2'])) {
    echo json_encode(['error' => 'Country ISO code is required.']);
    exit;
}

$iso_a2 = strtoupper($_GET['iso_a2']);

// Overpass Query - More comprehensive search for railway stations
$query = "
[out:json][timeout:30];
area['ISO3166-1'='$iso_a2'][admin_level=2]->.searchArea;
(
  node[railway=station](area.searchArea);
  node[railway=halt](area.searchArea);
);
out body 100;
";

$overpassUrl = "https://overpass-api.de/api/interpreter?data=" . urlencode($query);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $overpassUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);


if ($response === false) {
    echo json_encode(['error' => 'cURL Error: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);


$data = json_decode($response, true);


if (!isset($data['elements']) || empty($data['elements'])) {
    echo json_encode(['error' => 'No railways found.']);
    exit;
}


$features = [];
foreach ($data['elements'] as $element) {
    if (isset($element['lat'], $element['lon'])) {
        $features[] = [
            'type' => 'Feature',
            'properties' => [
                'name' => $element['tags']['name'] ?? 'Unknown Station'
            ],
            'geometry' => [
                'type' => 'Point',
                'coordinates' => [(float)$element['lon'], (float)$element['lat']]
            ]
        ];
    }
}


echo json_encode([
    'type' => 'FeatureCollection',
    'features' => $features
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
