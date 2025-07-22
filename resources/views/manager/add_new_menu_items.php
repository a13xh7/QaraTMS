<?php

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MenuVisibility;

// Define all menu items with their visibility settings
$menuItems = [
    // Manager Dashboard items
    ['menu_key' => 'smoke_detector', 'menu_name' => 'Smoke Detector', 'is_visible' => true],
    ['menu_key' => 'post_mortems', 'menu_name' => 'Post Mortems', 'is_visible' => true],
    ['menu_key' => 'deployment_fail_rate', 'menu_name' => 'Deployment Fail Rate', 'is_visible' => true],
    ['menu_key' => 'lead_time_mrs', 'menu_name' => 'Lead Time MRs', 'is_visible' => true],
    ['menu_key' => 'jira_lead_time', 'menu_name' => 'JIRA Lead Time', 'is_visible' => true],
    ['menu_key' => 'monthly_contribution', 'menu_name' => 'Monthly Contribution MR', 'is_visible' => true],
    
    // Analytics Dashboard items
    ['menu_key' => 'analytics_dashboard', 'menu_name' => 'Analytics Dashboard', 'is_visible' => true],
    ['menu_key' => 'testing_progress', 'menu_name' => 'Testing Progress Dashboard', 'is_visible' => true],
    ['menu_key' => 'bug_budget', 'menu_name' => 'Bug Budget Dashboard', 'is_visible' => true],
    ['menu_key' => 'defect_analytics', 'menu_name' => 'Defect Analytics Dashboard', 'is_visible' => true],
    ['menu_key' => 'api_dashboard', 'menu_name' => 'API Automation Dashboard', 'is_visible' => true],
    ['menu_key' => 'apps_dashboard', 'menu_name' => 'Apps Automation Dashboard', 'is_visible' => true],
];

// Add/update each menu item
foreach ($menuItems as $item) {
    $menuItem = MenuVisibility::firstOrNew(['menu_key' => $item['menu_key']]);
    $menuItem->menu_name = $item['menu_name'];
    $menuItem->is_visible = $item['is_visible'];
    $menuItem->save();
    
    echo "Menu item '{$item['menu_key']}' " . ($menuItem->wasRecentlyCreated ? 'created' : 'updated') . 
         " with visibility set to " . ($item['is_visible'] ? 'visible' : 'hidden') . "\n";
}

echo "\nMenu visibility settings have been updated successfully!\n"; 