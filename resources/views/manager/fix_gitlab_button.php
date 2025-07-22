<?php
// Fix script for GitLab button issue

$filepath = __DIR__ . '/resources/views/settings/gitlab.blade.php';
$content = file_get_contents($filepath);

// Backup the original file
copy($filepath, $filepath . '.backup');

// Explicit fixes:
// 1. Add a direct onclick attribute to the button
$content = preg_replace(
    '/<button type="button" class="btn btn-secondary" id="testConnection".*?>/i',
    '<button type="button" class="btn btn-secondary" id="testConnection" onclick="testGitLabConnection()" style="cursor: pointer !important;">',
    $content
);

// 2. Override any CSS that might be disabling the button
$styleAddition = <<<'EOT'
<style>
#testConnection {
    pointer-events: auto !important;
    cursor: pointer !important;
    opacity: 1 !important;
}
</style>
EOT;

// Insert style in the head section
if (strpos($content, '@section(\'head\')') !== false) {
    $content = preg_replace(
        '/@section\(\'head\'\)(.*?)@endsection/s',
        "@section('head')$1\n$styleAddition\n@endsection",
        $content
    );
} else {
    // Add a new head section if it doesn't exist
    $content = preg_replace(
        '/@section\(\'title\', \'GitLab Settings\'\)/s',
        "@section('title', 'GitLab Settings')\n\n@section('head')\n<meta name=\"csrf-token\" content=\"{{ csrf_token() }}\">\n$styleAddition\n@endsection",
        $content
    );
}

// 3. Make sure the toggle uses a direct onclick attribute
$content = preg_replace(
    '/<input class="form-check-input" type="checkbox" id="gitlab_enabled" name="gitlab_enabled" value="1".*?>/i',
    '<input class="form-check-input" type="checkbox" id="gitlab_enabled" name="gitlab_enabled" value="1" {{ env(\'GITLAB_ENABLED\') == \'true\' ? \'checked\' : \'\' }} onclick="updateToggleLabel(this.checked)">',
    $content
);

// 4. Simplify the JavaScript to ensure it works reliably
$jsSection = <<<'EOT'
@section('scripts')
<script>
    // Simple direct function to update toggle label
    function updateToggleLabel(isChecked) {
        document.getElementById('gitlab_enable_label').textContent = 
            isChecked ? 'Disable GitLab Integration' : 'Enable GitLab Integration';
    }
    
    // Direct button click handler with simplified implementation
    function testGitLabConnection() {
        console.log('Test button clicked');
        
        // Get form values
        const url = document.getElementById('gitlab_url').value;
        const token = document.getElementById('gitlab_token').value;
        const group = document.getElementById('gitlab_group').value;
        const testBtn = document.getElementById('testConnection');
        
        // Validate inputs
        if (!url || !token) {
            alert('Please enter GitLab URL and Access Token before testing');
            return;
        }
        
        // Show loading state
        const originalText = testBtn.innerHTML;
        testBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Testing...';
        testBtn.disabled = true;
        
        // Get CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            alert('CSRF token not found. Please refresh the page.');
            testBtn.innerHTML = originalText;
            testBtn.disabled = false;
            return;
        }
        
        // Make request with XMLHttpRequest for better compatibility
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/api/test-gitlab-connection');
        xhr.setRequestHeader('Content-Type', 'application/json');
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        
        xhr.onload = function() {
            let success = false;
            let message = '';
            
            try {
                const response = JSON.parse(xhr.responseText);
                success = response.success;
                message = response.message || '';
            } catch (e) {
                message = 'Invalid response from server';
            }
            
            if (success) {
                testBtn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Success';
                testBtn.classList.remove('btn-secondary');
                testBtn.classList.add('btn-success');
            } else {
                testBtn.innerHTML = '<i class="bi bi-x-circle-fill"></i> Failed';
                testBtn.classList.remove('btn-secondary');
                testBtn.classList.add('btn-danger');
                alert('Connection failed: ' + message);
            }
            
            // Reset button after 3 seconds
            setTimeout(function() {
                testBtn.innerHTML = originalText;
                testBtn.classList.remove('btn-success', 'btn-danger');
                testBtn.classList.add('btn-secondary');
                testBtn.disabled = false;
            }, 3000);
        };
        
        xhr.onerror = function() {
            testBtn.innerHTML = '<i class="bi bi-x-circle-fill"></i> Error';
            testBtn.classList.remove('btn-secondary');
            testBtn.classList.add('btn-danger');
            alert('Network error occurred. Please check your connection.');
            
            setTimeout(function() {
                testBtn.innerHTML = originalText;
                testBtn.classList.remove('btn-danger');
                testBtn.classList.add('btn-secondary');
                testBtn.disabled = false;
            }, 3000);
        };
        
        // Send the request
        xhr.send(JSON.stringify({
            url: url,
            token: token,
            group: group
        }));
    }
    
    // Toggle password visibility
    document.getElementById('toggleToken')?.addEventListener('click', function() {
        const tokenField = document.getElementById('gitlab_token');
        if (tokenField) {
            tokenField.type = tokenField.type === 'password' ? 'text' : 'password';
            this.querySelector('i').classList.toggle('bi-eye');
            this.querySelector('i').classList.toggle('bi-eye-slash');
        }
    });
    
    // Copy webhook URL
    document.getElementById('copyWebhook')?.addEventListener('click', function() {
        const webhookField = document.getElementById('webhookUrl');
        if (webhookField) {
            webhookField.select();
            try {
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(webhookField.value);
                } else {
                    document.execCommand('copy');
                }
                this.innerHTML = '<i class="bi bi-clipboard-check"></i>';
                setTimeout(() => {
                    this.innerHTML = '<i class="bi bi-clipboard"></i>';
                }, 2000);
            } catch (e) {
                alert('Failed to copy: ' + e);
            }
        }
    });
    
    // Add click handler for test button as fallback
    window.addEventListener('load', function() {
        const testBtn = document.getElementById('testConnection');
        if (testBtn) {
            testBtn.addEventListener('click', testGitLabConnection);
        }
    });
</script>
@endsection
EOT;

// Replace the scripts section
$content = preg_replace('/@section\(\'scripts\'\).*?@endsection/s', $jsSection, $content);

// Save the updated file
file_put_contents($filepath, $content);

echo "GitLab settings page has been updated with the following fixes:\n";
echo "1. Added direct onclick handler to the Test Connection button\n";
echo "2. Added CSS to ensure the button is clickable\n";
echo "3. Simplified the JavaScript code for better reliability\n";
echo "4. Added direct onclick handler to the toggle checkbox\n";
echo "5. Added a fallback click handler that attaches on window load\n\n";
echo "Original file backed up to: {$filepath}.backup\n";
echo "Please restart your server for the changes to take effect.\n"; 