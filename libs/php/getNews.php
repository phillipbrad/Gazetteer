<?php
require __DIR__ . '/../../vendor/autoload.php';

// Load .env only if it exists (for local development)
if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
}
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

$countryName = $_GET['countryName'];
$apikey = $_ENV['NEWSAPI_KEY'] ?? getenv('NEWSAPI_KEY'); 

$url = "https://newsapi.org/v2/everything?q=" . urlencode($countryName) . "&pageSize=5&apiKey=$apikey";

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_USERAGENT, 'GAZETTEER');

$response = curl_exec($ch);
if (curl_errno($ch)) {
    $error_msg = 'Curl error: ' . curl_error($ch);
    error_log($error_msg);
    echo json_encode(['error' => $error_msg]);
    curl_close($ch);
    exit;
}
curl_close($ch);

$decode = json_decode($response, true);

if (isset($decode['status']) && $decode['status'] === 'error') {
    error_log("API error: " . $decode['message']);
    echo json_encode(['error' => $decode['message']]);
    exit;
}

echo json_encode($decode);

?>