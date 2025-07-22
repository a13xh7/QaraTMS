<?php

// Simple script to create menu visibility settings
// Run with: php add_menu_items.php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Check if the MenuVisibility class exists
if (!class_exists('\App\Models\MenuVisibility')) {
    echo "Error: MenuVisibility model does not exist\n";
    exit(1);
}

// Menu items array
$menuItems = [
    [
        'menu_key' => 'bug_budget',
        'menu_name' => 'Bug Budget',
        'is_visible' => true,
    ]
];

// Insert the menu items
foreach ($menuItems as $item) {
    \App\Models\MenuVisibility::updateOrCreate(
        ['menu_key' => $item['menu_key']],
        $item
    );
    echo "Added/updated menu item: {$item['menu_name']}\n";
}

echo "\nMenu items added successfully!\n"; 