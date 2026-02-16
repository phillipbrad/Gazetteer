<?php
require __DIR__ . '/../../vendor/autoload.php';

use Dotenv\Dotenv;

// Load .env only if it exists (for local development)
if (file_exists(__DIR__ . '/../../.env')) {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
}


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');


$executionStartTime = microtime(true);


$api_key = $_ENV['RAPIDAPI_KEY'] ?? getenv('RAPIDAPI_KEY');

// Get currency parameter from request, default to USD
$currency = isset($_GET['currency']) ? strtoupper($_GET['currency']) : 'USD';

// Define the currencies to compare against
$to_currencies = ["CAD", "AUD", "USD", "GBP", "EUR", "CZK", "CNY"];

// Remove the selected currency to avoid self-comparison
$filtered_currencies = [];
foreach ($to_currencies as $to_currency) {
    if ($to_currency !== $currency) {
        $filtered_currencies[] = $to_currency;
    }
}

// Convert the filtered currencies array into a comma-separated string
$to_currencies_str = implode(",", $filtered_currencies);


$api_url = "https://currency-converter5.p.rapidapi.com/currency/convert";
$url = "{$api_url}?format=json&from={$currency}&to={$to_currencies_str}&amount=1&language=en";


$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30, 
    CURLOPT_HTTPHEADER => [
        "x-rapidapi-host: currency-converter5.p.rapidapi.com",
        "x-rapidapi-key: $api_key"
    ],
]);


$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);


if (curl_errno($ch)) {
    echo json_encode([
        'status' => 'error',
        'error' => 'cURL error: ' . curl_error($ch),
        'seconds' => number_format((microtime(true) - $executionStartTime), 3)
    ]);
    curl_close($ch);
    exit;
}

curl_close($ch);

// Check HTTP response code
if ($http_code !== 200) {
    echo json_encode([
        'status' => 'error',
        'error' => "HTTP error: Received status code $http_code",
        'response' => $response,
        'url' => $url,
        'api_key_present' => !empty($api_key),
        'seconds' => number_format((microtime(true) - $executionStartTime), 3)
    ]);
    exit;
}


$data = json_decode($response, true);


if ($data === null) {
    echo json_encode([
        'status' => 'error',
        'error' => 'Failed to decode JSON response: ' . json_last_error_msg(),
        'seconds' => number_format((microtime(true) - $executionStartTime), 3)
    ]);
    exit;
}

// Add debug info if API key is missing
if (empty($api_key)) {
    echo json_encode([
        'status' => 'error',
        'error' => 'API key is missing',
        'debug' => [
            '_ENV' => isset($_ENV['RAPIDAPI_KEY']) ? 'set' : 'not set',
            'getenv' => getenv('RAPIDAPI_KEY') ? 'set' : 'not set'
        ],
        'seconds' => number_format((microtime(true) - $executionStartTime), 3)
    ]);
    exit;
}

// Check if the API response has an error
if (isset($data['error'])) {
    echo json_encode([
        'status' => 'error',
        'error' => $data['error'],
        'api_response' => $data,
        'seconds' => number_format((microtime(true) - $executionStartTime), 3)
    ]);
    exit;
}

echo json_encode($data);
?>
