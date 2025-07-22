<?php
// A direct test script for the GitLab API integration

// Configuration
$apiUrl = 'http://localhost:8001/api/test-gitlab-connection';
$gitlabUrl = 'https://gitlab.com/api/v4';
$gitlabToken = 'your-token-here'; // Replace with your actual token
$gitlabGroup = 'admin';

echo "Direct GitLab API Test\n";
echo "=====================\n\n";

// Prepare the request data
$data = [
    'url' => $gitlabUrl,
    'token' => $gitlabToken,
    'group' => $gitlabGroup
];

// Initialize cURL session
$ch = curl_init($apiUrl);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest'
]);

// Enable verbose output
curl_setopt($ch, CURLOPT_VERBOSE, true);
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

// Execute the request
echo "Sending request to: $apiUrl\n";
echo "With data: " . json_encode($data, JSON_PRETTY_PRINT) . "\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Get verbose information
rewind($verbose);
$verboseLog = stream_get_contents($verbose);

// Check for errors
if ($response === false) {
    echo "cURL Error: " . curl_error($ch) . "\n";
    echo "Verbose Log:\n$verboseLog\n";
} else {
    echo "HTTP Status Code: $httpCode\n\n";
    
    // Try to parse response as JSON
    $responseData = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "Response (JSON):\n" . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
        
        // Display success/error message
        if (isset($responseData['success'])) {
            if ($responseData['success']) {
                echo "\n✅ SUCCESS: " . ($responseData['message'] ?? 'Connection successful') . "\n";
            } else {
                echo "\n❌ ERROR: " . ($responseData['message'] ?? 'Connection failed') . "\n";
            }
        }
    } else {
        echo "Response (raw):\n$response\n";
    }
    
    echo "\nVerbose Log:\n$verboseLog\n";
}

// Close cURL session
curl_close($ch);

echo "\nTest completed.\n"; 