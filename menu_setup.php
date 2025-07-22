<?php

// Simple script to set default environment settings
// Run with: php menu_setup.php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Set default environment variables
$envPath = __DIR__ . '/.env';
$envContent = file_get_contents($envPath);

$defaultSettings = [
    'CONFLUENCE_BASE_URL' => 'https://your-domain.atlassian.net/wiki',
    'CONFLUENCE_USERNAME' => 'your-email@domain.com',
    'CONFLUENCE_API_TOKEN' => 'your-confluence-api-token',
    'CONFLUENCE_SPACE_KEY' => 'YOUR_SPACE_KEY',
    'JIRA_URL_SEARCH' => 'https://your-domain.atlassian.net/rest/api/2/search',
    'JIRA_USERNAME' => 'your-email@domain.com',
    'JIRA_API_TOKEN' => 'your-jira-api-token',
    'CONFLUENCE_SPACE_KEY2' => 'YOUR_SPACE_KEY2',
    'GITLAB_URL' => 'https://gitlab.com/api/v4',
    'GITLAB_API_URL' => 'https://gitlab.com/api/v4',
    'PROJECT_ID' => 'your-project-id',
    'GITLAB_TOKEN' => 'your-gitlab-token',
    'SLACK_BOT_TOKEN' => 'your-slack-bot-token',
    'SLACK_CHANNEL_ID' => 'your-slack-channel-id',
];

foreach ($defaultSettings as $key => $value) {
    // Safely format value
    if (preg_match('/\s/', $value) || preg_match('/[^\w.-]/', $value)) {
        $value = '"' . addslashes($value) . '"';
    }
    
    // Update or add to .env file
    if (preg_match("/^{$key}=/m", $envContent)) {
        // Replace existing key
        $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
    } else {
        // Add key if it doesn't exist
        $envContent .= PHP_EOL . "{$key}={$value}";
    }
}

// Save updated .env file
file_put_contents($envPath, $envContent);
echo "Default environment settings applied\n";

echo "\nSetup completed successfully!\n"; 