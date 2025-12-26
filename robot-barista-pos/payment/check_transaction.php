<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Load payment configuration
require_once __DIR__ . '/../config/payment.php';

// Log for debugging
error_log("=== Check Transaction Request ===");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $md5 = $input['md5'] ?? '';

    error_log("Received MD5: " . $md5);

    if (empty($md5)) {
        echo json_encode([
            'responseCode' => 1,
            'responseMessage' => 'MD5 is required',
            'data' => null
        ]);
        exit;
    }

    // Get configuration
    $config = getBakongConfig();
    $url = $config['api_url'];
    $token = $config['api_token'];

    error_log("API URL: " . $url);
    error_log("Using token: " . substr($token, 0, 20) . "...");

    $data = json_encode(['md5' => $md5]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    error_log("HTTP Code: " . $httpCode);
    error_log("Response: " . $response);
    
    if ($curlError) {
        error_log("CURL Error: " . $curlError);
    }

    if ($response && $httpCode === 200) {
        $responseData = json_decode($response, true);
        error_log("Response Code: " . ($responseData['responseCode'] ?? 'unknown'));
        echo $response;
    } else {
        echo json_encode([
            'responseCode' => 1,
            'responseMessage' => 'Failed to check transaction',
            'data' => null,
            'httpCode' => $httpCode,
            'error' => $curlError
        ]);
    }
} else {
    echo json_encode([
        'responseCode' => 1,
        'responseMessage' => 'Invalid request method'
    ]);
}
