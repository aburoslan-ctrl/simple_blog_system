<?php
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

$url = "http://aburoslan.site/blog_system/api/users/auth/login.php";

$postData = [
    'email' => 'albaskuri@gmail.com',
    'password' => 'Albaskuri@1'
];

// Initialize cURL
$ch = curl_init($url);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
$response = curl_exec($ch);



// Check cURL errors
if ($response === false) {
    die(json_encode(['error' => 'cURL Error: ' . curl_error($ch)]));
}

curl_close($ch);

// Show raw response for debugging (optional)
// echo $response;

// Decode JSON response
$result = json_decode($response, true);

// Check if JSON is valid
// if (json_last_error() !== JSON_ERROR_NONE) {
//     die("Invalid JSON response: " . json_last_error_msg());
// }
if (isset($result['access_token'])) {
    echo json_encode(['access_token' => $result['access_token']]);
} elseif (isset($result['data']['access_token'])) {
    echo json_encode(['access_token' => $result['data']['access_token']]);
} else {
    echo json_encode(['error' => 'Access token not found', 'response' => $result]);
}

