<?php

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MenuVisibility;

// Define menu hierarchies
$menuHierarchy = [
    'analytics_dashboard' => [
        'grafana_automation_report',
        'defect_analytics_dashboard',
        'testing_progress'
    ],
    'grafana_automation_report' => [
        'api_dashboard',
        'apps_dashboard'
    ],
    'defect_analytics_dashboard' => [
        'defect_analytics',
        'bug_budget'
    ]
];

// Make sure all necessary menu items exist
$menuItems = [
    // Main analytics dashboard
    ['menu_key' => 'analytics_dashboard', 'menu_name' => 'Analytics Dashboard', 'is_visible' => true, 'parent_key' => null],
    
    // Second level items
    ['menu_key' => 'grafana_automation_report', 'menu_name' => 'Grafana Automation Report', 'is_visible' => true, 'parent_key' => 'analytics_dashboard'],
    ['menu_key' => 'defect_analytics_dashboard', 'menu_name' => 'Defect Analytics Dashboard', 'is_visible' => true, 'parent_key' => 'analytics_dashboard'],
    ['menu_key' => 'testing_progress', 'menu_name' => 'Testing Progress Dashboard', 'is_visible' => true, 'parent_key' => 'analytics_dashboard'],
    
    // Third level items
    ['menu_key' => 'api_dashboard', 'menu_name' => 'API Automation Dashboard', 'is_visible' => true, 'parent_key' => 'grafana_automation_report'],
    ['menu_key' => 'apps_dashboard', 'menu_name' => 'Apps Automation Dashboard', 'is_visible' => true, 'parent_key' => 'grafana_automation_report'],
    ['menu_key' => 'defect_analytics', 'menu_name' => 'Defect Analytics', 'is_visible' => true, 'parent_key' => 'defect_analytics_dashboard'],
    ['menu_key' => 'bug_budget', 'menu_name' => 'Bug Budget Dashboard', 'is_visible' => true, 'parent_key' => 'defect_analytics_dashboard'],
];

// Update or create each menu item
foreach ($menuItems as $item) {
    $menuItem = MenuVisibility::firstOrNew(['menu_key' => $item['menu_key']]);
    $menuItem->menu_name = $item['menu_name'];
    $menuItem->is_visible = $item['is_visible'];
    $menuItem->parent_key = $item['parent_key'];
    $menuItem->save();
    
    echo "Menu item '{$item['menu_key']}' " . ($menuItem->wasRecentlyCreated ? 'created' : 'updated') . 
         " with parent: " . ($item['parent_key'] ?? 'none') . "\n";
}

// Update other existing menu items without hierarchy
$otherItems = MenuVisibility::whereNotIn('menu_key', array_column($menuItems, 'menu_key'))->get();
foreach ($otherItems as $item) {
    // Find if this item is a child in our hierarchy
    $parent = null;
    foreach ($menuHierarchy as $parentKey => $children) {
        if (in_array($item->menu_key, $children)) {
            $parent = $parentKey;
            break;
        }
    }
    
    if ($parent) {
        $item->parent_key = $parent;
        $item->save();
        echo "Updated existing menu item '{$item->menu_key}' with parent: {$parent}\n";
    }
}

echo "\nMenu hierarchy has been updated successfully!\n"; 