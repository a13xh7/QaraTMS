<?php

echo "GitLab Integration Settings Diagnostic\n";
echo "=====================================\n\n";

// 1. Check if .env file exists and is readable
$envPath = __DIR__ . '/.env';
echo "Checking .env file at: $envPath\n";

if (!file_exists($envPath)) {
    echo "ERROR: .env file doesn't exist!\n";
    exit(1);
}

if (!is_readable($envPath)) {
    echo "ERROR: .env file is not readable!\n";
    exit(1);
}

echo "SUCCESS: .env file exists and is readable.\n\n";

// 2. Read current GitLab settings
echo "Current GitLab Settings:\n";
echo "GITLAB_ENABLED: " . getenv('GITLAB_ENABLED') . "\n";
echo "GITLAB_URL: " . getenv('GITLAB_URL') . "\n";
echo "GITLAB_TOKEN: " . getenv('GITLAB_TOKEN') . "\n";
echo "GITLAB_GROUP: " . getenv('GITLAB_GROUP') . "\n";
echo "GITLAB_CACHE_DURATION: " . getenv('GITLAB_CACHE_DURATION') . "\n\n";

// 3. Check if .env file is writable
if (!is_writable($envPath)) {
    echo "ERROR: .env file is not writable! Please check permissions.\n";
    echo "Current permissions: " . substr(sprintf('%o', fileperms($envPath)), -4) . "\n";
    echo "Owner: " . posix_getpwuid(fileowner($envPath))['name'] . "\n";
    echo "Current PHP user: " . get_current_user() . "\n";
    exit(1);
}

echo "SUCCESS: .env file is writable.\n\n";

// 4. Try to update the .env file directly
echo "Attempting to write test value to .env file...\n";

// Read the current file content
$envContent = file_get_contents($envPath);
$testKey = 'GITLAB_TEST_KEY';
$testValue = 'test_value_' . time();

// Check if the key already exists
if (preg_match("/^{$testKey}=/m", $envContent)) {
    // Replace existing line
    $envContent = preg_replace("/^{$testKey}=.*/m", "{$testKey}={$testValue}", $envContent);
} else {
    // Add new line
    $envContent .= "\n{$testKey}={$testValue}\n";
}

// Write the content back
$writeResult = file_put_contents($envPath, $envContent);

if ($writeResult === false) {
    echo "ERROR: Failed to write to .env file!\n";
    exit(1);
}

echo "SUCCESS: Test value written to .env file.\n\n";

// 5. Check if getenv() reflects the change immediately
echo "Checking if getenv() picks up the new value immediately:\n";
echo "GITLAB_TEST_KEY via getenv(): " . getenv($testKey) . "\n";
echo "Note: getenv() might not reflect changes without reloading the environment.\n\n";

// 6. Check if env() helper would see the change
echo "The Laravel env() helper would typically need cache clearing or restart to see changes.\n";
echo "Suggestion: Run 'php artisan config:clear' after saving settings.\n\n";

// 7. Test reading using direct file access
echo "Re-reading the .env file directly to verify change:\n";
$newEnvContent = file_get_contents($envPath);
preg_match("/^{$testKey}=(.*)$/m", $newEnvContent, $matches);
echo "GITLAB_TEST_KEY in file: " . ($matches[1] ?? "Not found") . "\n\n";

echo "Diagnostic complete. If all tests passed but settings still don't persist,\n";
echo "try clearing the Laravel config cache with 'php artisan config:clear'.\n"; 