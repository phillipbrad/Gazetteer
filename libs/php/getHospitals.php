<?php


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if (!isset($_GET['iso_a2'])) {
    echo json_encode(['error' => 'Country ISO code is required.']);
    exit;
}

$iso_a2 = strtoupper($_GET['iso_a2']);

// Overpass Query 
$overpassQuery = "
[out:json][timeout:25];
area['ISO3166-1'='$iso_a2']->.searchArea;
(
  node[amenity=hospital](area.searchArea);

  relation[amenity=hospital](area.searchArea);
);
out body 50;  // 
";

$overpassUrl = "https://overpass-api.de/api/interpreter?data=" . urlencode($overpassQuery);

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $overpassUrl);

$response = curl_exec($ch);

if ($response === false) {
    echo json_encode(['error' => 'cURL Error: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);

$data = json_decode($response, true);

if (!isset($data['elements']) || empty($data['elements'])) {
    echo json_encode(['error' => 'No hospitals found.']);
    exit;
}


$features = [];
foreach ($data['elements'] as $element) {
    if (isset($element['lat'], $element['lon'])) {
        $features[] = [
            'type' => 'Feature',
            'properties' => [
                'name' => $element['tags']['name'] ?? 'Unknown Hospital'
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
