<?php

require __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

header('Content-Type: application/json');

$latitude = $_GET['lat'];
$longitude = $_GET['lng'];

$apikey = $_ENV['OPENWEATHER_API_KEY'];

$url = "https://api.openweathermap.org/data/3.0/onecall?lat={$latitude}&lon={$longitude}&units=metric&exclude=hourly,minutely,alerts&appid={$apikey}"; 

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $url);

$response = curl_exec($ch);
curl_close($ch);

$decode = json_decode($response, true);

echo json_encode($decode);

?>