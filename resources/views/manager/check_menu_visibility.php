<?php

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MenuVisibility;

// Get all menu items
$menuItems = MenuVisibility::all();

echo "Menu Visibility Items in Database:\n";
echo "=================================\n";

foreach ($menuItems as $item) {
    echo sprintf(
        "%-30s | %-40s | %s\n", 
        $item->menu_key, 
        $item->menu_name, 
        $item->is_visible ? 'VISIBLE' : 'HIDDEN'
    );
}

echo "\nTotal items: " . $menuItems->count() . "\n"; 