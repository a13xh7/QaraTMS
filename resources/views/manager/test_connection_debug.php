<?php
// Test script to debug GitLab connection issues

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

// Check if the CSRF protection is working
echo "CSRF Token: " . csrf_token() . "\n\n";

// Check environment variables
echo "Environment Variables:\n";
echo "GITLAB_ENABLED: " . env('GITLAB_ENABLED') . "\n";
echo "GITLAB_URL: " . (env('GITLAB_URL') ? 'Set (value hidden)' : 'Not set') . "\n";
echo "GITLAB_TOKEN: " . (env('GITLAB_TOKEN') ? 'Set (value hidden)' : 'Not set') . "\n";
echo "GITLAB_GROUP: " . env('GITLAB_GROUP') . "\n\n";

// Check route definitions
echo "Route for test-gitlab-connection exists: " . 
     (in_array('api/test-gitlab-connection', array_keys(Route::getRoutes()->getRoutesByMethod()['POST'] ?? [])) ? 'Yes' : 'No') . "\n\n";

// Output form HTML for manual testing
echo "HTML for a test form (you can paste this into a test.html file):\n";
echo "<form id='testForm'>\n";
echo "  <input type='text' id='gitlab_url' value='" . (env('GITLAB_URL') ?: 'https://gitlab.com/api/v4') . "'>\n";
echo "  <input type='text' id='gitlab_token' value='your-token-here'>\n";
echo "  <input type='text' id='gitlab_group' value='" . (env('GITLAB_GROUP') ?: '') . "'>\n";
echo "  <button type='button' id='testButton'>Test Connection</button>\n";
echo "</form>\n";
echo "<div id='result'></div>\n\n";

echo "<script>
document.getElementById('testButton').addEventListener('click', function() {
  const url = document.getElementById('gitlab_url').value;
  const token = document.getElementById('gitlab_token').value;
  const group = document.getElementById('gitlab_group').value;
  
  fetch('/api/test-gitlab-connection', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '" . csrf_token() . "'
    },
    body: JSON.stringify({url, token, group})
  })
  .then(response => response.json())
  .then(data => {
    document.getElementById('result').innerHTML = 
      'Success: ' + data.success + '<br>Message: ' + data.message;
  })
  .catch(error => {
    document.getElementById('result').innerHTML = 'Error: ' + error;
  });
});
</script>\n";

echo "\nDiagnostic complete."; 