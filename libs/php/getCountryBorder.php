<?php
header('Content-Type: application/json'); 
header('Access-Control-Allow-Origin: *');

if (!isset($_GET['iso_a2'])) { 
    echo json_encode(['error' => 'No ISO2 code provided']); 
    exit; 
}

$iso_a2 = $_GET['iso_a2']; 

$data = json_decode(file_get_contents("../data/countryBorders.geo.json"), true); 

$features = $data['features']; 

$border = null; 

foreach ($features as $feature) { 
    if ($feature['properties']['iso_a2'] === $iso_a2) { 
        $border = $feature; 
        break; 
    }
}

if ($border) {
    echo json_encode($border); 
} else {
    echo json_encode(['error' => 'Country not found']); 
}
?>
