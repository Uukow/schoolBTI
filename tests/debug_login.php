<?php
// Debug script to capture raw output from login API
// Usage: php tests/debug_login.php

$url = 'http://localhost/bti/api/auth/login.php';
$data = json_encode(['username' => 'admin', 'password' => 'password']); // Use dummy or known creds

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
if ($response === false) {
    echo "Curl Error: " . curl_error($ch) . "\n";
}
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response Length: " . strlen($response) . "\n";
echo "Raw Response:\n";
echo "--------------------------------------------------\n";
echo $response . "\n";
echo "--------------------------------------------------\n";
