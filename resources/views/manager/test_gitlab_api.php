<?php
// Script to test the GitLab API connection directly

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

echo "GitLab API Test Script\n";
echo "======================\n\n";

// 1. Check if GitLab environment variables are set
echo "Checking environment variables:\n";
$gitlab_enabled = env('GITLAB_ENABLED');
$gitlab_url = env('GITLAB_URL');
$gitlab_token = env('GITLAB_TOKEN');
$gitlab_group = env('GITLAB_GROUP');

echo "GITLAB_ENABLED: " . ($gitlab_enabled ? $gitlab_enabled : 'Not set') . "\n";
echo "GITLAB_URL: " . ($gitlab_url ? 'Set (value hidden)' : 'Not set') . "\n";
echo "GITLAB_TOKEN: " . ($gitlab_token ? 'Set (value hidden)' : 'Not set') . "\n";
echo "GITLAB_GROUP: " . ($gitlab_group ? $gitlab_group : 'Not set') . "\n\n";

// Function to make the test connection
function testConnection($url, $token, $group = null) {
    echo "Testing connection with:\n";
    echo "URL: $url\n";
    echo "Token: [Hidden for security]\n";
    if ($group) {
        echo "Group: $group\n";
    }
    echo "\n";
    
    try {
        // Attempt to connect to GitLab API
        $response = Http::withHeaders([
            'PRIVATE-TOKEN' => $token
        ])->get($url . '/user');
        
        echo "Response status: " . $response->status() . "\n";
        echo "Response body: " . substr($response->body(), 0, 500) . "...\n\n";
        
        if ($response->failed()) {
            echo "❌ Connection failed.\n";
            return false;
        }
        
        $userData = $response->json();
        echo "✅ Successfully connected to GitLab as " . ($userData['name'] ?? 'Unknown') . "\n";
        
        // If a group was specified, check if it exists and is accessible
        if (!empty($group)) {
            echo "Checking group '$group'...\n";
            $groupResponse = Http::withHeaders([
                'PRIVATE-TOKEN' => $token
            ])->get($url . '/groups/' . urlencode($group));
            
            echo "Group response status: " . $groupResponse->status() . "\n";
            
            if ($groupResponse->failed()) {
                echo "❌ Group verification failed: Group not found or not accessible.\n";
                return false;
            }
            echo "✅ Group verification successful.\n";
        }
        
        return true;
    } catch (\Exception $e) {
        echo "❌ Exception occurred: " . $e->getMessage() . "\n";
        return false;
    }
}

// Check if our test-gitlab-connection route exists
echo "\nChecking routes/api.php for test-gitlab-connection route:\n";
$routes = Route::getRoutes()->getRoutesByMethod()['POST'] ?? [];
$testRouteExists = false;
foreach ($routes as $uri => $route) {
    if ($uri === 'api/test-gitlab-connection') {
        echo "✅ Route exists: $uri\n";
        $testRouteExists = true;
        break;
    }
}
if (!$testRouteExists) {
    echo "❌ Route 'api/test-gitlab-connection' does not exist!\n";
}

// Test with the environment variables
if ($gitlab_url && $gitlab_token) {
    echo "\nTesting connection with environment variables:\n";
    testConnection($gitlab_url, $gitlab_token, $gitlab_group);
} else {
    echo "\nSkipping connection test with environment variables as URL or token is missing.\n";
}

// Test with a predefined test URL (you may need to modify this)
echo "\nTesting with default GitLab.com API endpoint:\n";
testConnection('https://gitlab.com/api/v4', 'your-token-here');

echo "\nTest script complete.\n"; 