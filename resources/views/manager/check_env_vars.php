<?php
// Load Laravel application
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking GitLab Environment Variables\n";
echo "===================================\n\n";

// Check direct .env file contents
echo "1. DIRECT .ENV FILE CONTENTS:\n";
$envFile = file_get_contents(__DIR__ . '/.env');
preg_match_all('/^GITLAB_.*$/m', $envFile, $matches);
foreach ($matches[0] as $line) {
    echo "   $line\n";
}
echo "\n";

// Check using PHP's getenv()
echo "2. USING PHP'S getenv():\n";
echo "   GITLAB_ENABLED: " . getenv('GITLAB_ENABLED') . "\n";
echo "   GITLAB_URL: " . getenv('GITLAB_URL') . "\n";
echo "   GITLAB_TOKEN: " . getenv('GITLAB_TOKEN') . "\n";
echo "   GITLAB_GROUP: " . getenv('GITLAB_GROUP') . "\n";
echo "   GITLAB_CACHE_DURATION: " . getenv('GITLAB_CACHE_DURATION') . "\n\n";

// Check using Laravel's env() function
echo "3. USING LARAVEL'S env() FUNCTION:\n";
echo "   GITLAB_ENABLED: " . env('GITLAB_ENABLED') . "\n";
echo "   GITLAB_URL: " . env('GITLAB_URL') . "\n";
echo "   GITLAB_TOKEN: " . env('GITLAB_TOKEN') . "\n";
echo "   GITLAB_GROUP: " . env('GITLAB_GROUP') . "\n";
echo "   GITLAB_CACHE_DURATION: " . env('GITLAB_CACHE_DURATION') . "\n\n";

// Check using Laravel's config() function
echo "4. USING LARAVEL'S config() FUNCTION (if mapped to config):\n";
echo "   GITLAB_ENABLED via config: " . (config('services.gitlab.enabled') ?? 'not mapped to config') . "\n";
echo "   GITLAB_URL via config: " . (config('services.gitlab.url') ?? 'not mapped to config') . "\n";
echo "   GITLAB_TOKEN via config: " . (config('services.gitlab.token') ? 'set (masked)' : 'not mapped to config') . "\n\n";

// Create an instance of GitLabService and check its values
echo "5. VALUES LOADED IN GitLabService:\n";
try {
    $gitLabService = app(\App\Services\GitLabService::class);
    echo "   GitLabService was instantiated successfully\n";
    echo "   isConfigured(): " . ($gitLabService->isConfigured() ? 'true' : 'false') . "\n";
    // Using reflection to access protected properties
    $reflection = new ReflectionClass($gitLabService);
    
    $apiUrlProp = $reflection->getProperty('apiUrl');
    $apiUrlProp->setAccessible(true);
    $apiTokenProp = $reflection->getProperty('apiToken');
    $apiTokenProp->setAccessible(true);
    $groupProp = $reflection->getProperty('group');
    $groupProp->setAccessible(true);
    $cacheDurationProp = $reflection->getProperty('cacheDuration');
    $cacheDurationProp->setAccessible(true);
    
    echo "   apiUrl: " . $apiUrlProp->getValue($gitLabService) . "\n";
    echo "   apiToken: " . ($apiTokenProp->getValue($gitLabService) ? substr($apiTokenProp->getValue($gitLabService), 0, 5) . '...' : 'not set') . "\n";
    echo "   group: " . $groupProp->getValue($gitLabService) . "\n";
    echo "   cacheDuration: " . $cacheDurationProp->getValue($gitLabService) . "\n";
} catch (Exception $e) {
    echo "   Error instantiating GitLabService: " . $e->getMessage() . "\n";
}
echo "\n";

echo "Recommendations based on checks:\n";
echo "-------------------------------\n";
echo "1. If direct .env file contents show correct values but getenv() doesn't, try restarting your PHP process\n";
echo "2. If env() shows different values than .env file, try running 'php artisan config:clear'\n";
echo "3. Check .env file permissions: " . substr(sprintf('%o', fileperms(__DIR__ . '/.env')), -4) . "\n";
echo "4. Check if Laravel is loading a different .env file: " . app()->environmentFilePath() . "\n";
echo "5. Make sure your web server user can write to the .env file\n"; 