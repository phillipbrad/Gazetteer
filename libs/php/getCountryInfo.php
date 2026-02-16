<?php
require __DIR__ . '/../../vendor/autoload.php';


$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');


$executionStartTime = microtime(true);


if (!isset($_GET['iso2']) || empty($_GET['iso2'])) {
    echo json_encode([
        'status' => [
            'code' => 400,
            'name' => 'Bad Request',
            'description' => 'No ISO2 country code provided',
            'seconds' => number_format((microtime(true) - $executionStartTime), 3)
        ],
        'data' => null
    ]);
    exit;
}

$iso2 = $_GET['iso2'];
$username = $_ENV['GEONAMES_USERNAME'];

$url = "http://api.geonames.org/countryInfoJSON?country=$iso2&username=$username";


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30); 
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);


if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    curl_close($ch);

    echo json_encode([
        'status' => [
            'code' => 500,
            'name' => 'Internal Server Error',
            'description' => 'cURL error: ' . $error_msg,
            'seconds' => number_format((microtime(true) - $executionStartTime), 3)
        ],
        'data' => null
    ]);
    exit;
}

curl_close($ch);


$data = json_decode($response, true);


if ($data === null) {
    echo json_encode([
        'status' => [
            'code' => 500,
            'name' => 'Internal Server Error',
            'description' => 'Failed to decode JSON response',
            'json_last_error_msg' => json_last_error_msg(),
            'seconds' => number_format((microtime(true) - $executionStartTime), 3)
        ],
        'data' => null
    ]);
    exit;
}

if (!isset($data['geonames']) || empty($data['geonames'])) {
    echo json_encode([
        'status' => [
            'code' => 404,
            'name' => 'Not Found',
            'description' => 'No country information found',
            'seconds' => number_format((microtime(true) - $executionStartTime), 3)
        ],
        'data' => null
    ]);
    exit;
}


echo json_encode($data);
?>



