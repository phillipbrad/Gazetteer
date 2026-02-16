<?php
header('Content-Type: application/json');

$countryAbbreviations = [
    'Central African Rep.' => 'Central African Republic',
    'Dem. Rep. Congo' => 'Democratic Republic of the Congo',
    'Czech Rep.' => 'Czech Republic',
    'Falkland Is.' => 'Falkland Islands',
    "Côte d'Ivoire" => 'Ivory Coast',
    'N. Cyprus' => 'Northern Cyprus',
    'Timor-Leste' => 'East Timor',
    'Palestine' => 'State of Palestine',
    'Somaliland' => 'Somaliland',
];

if (!isset($_GET['countryName']) || empty($_GET['countryName'])) {
    echo json_encode(['error' => 'Missing country name']);
    exit;
}

$countryName = urldecode($_GET['countryName']);  

if (array_key_exists($countryName, $countryAbbreviations)) {
    $countryName = $countryAbbreviations[$countryName];
}

$countryName = str_replace(' ', '_', $countryName);

$url = "https://en.wikipedia.org/api/rest_v1/page/summary/$countryName";

$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_USERAGENT, 'Gazetteer/1.0 (Educational Project; https://github.com)');

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(['error' => 'cURL error: ' . curl_error($ch)]);
    curl_close($ch);
    exit;
}

curl_close($ch);

if (empty($response)) {
    echo json_encode(['error' => 'Empty response from Wikipedia API']);
    exit;
}

$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'error' => 'Error decoding JSON response',
        'json_error' => json_last_error_msg(),
        'raw_response' => substr($response, 0, 500)
    ]);
    exit;
}

// Check if Wikipedia returned an error
if (isset($data['type']) && $data['type'] === 'https://mediawiki.org/wiki/HyperSwitch/errors/not_found') {
    echo json_encode(['error' => 'Wikipedia page not found for: ' . $countryName]);
    exit;
}

echo json_encode($data);
?>
