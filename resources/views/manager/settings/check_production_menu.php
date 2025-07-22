<?php

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MenuVisibility;
use App\Models\User;

// Check user role
$user = User::where('email', 'admin@admin.com')->first();
if ($user) {
    echo "User: {$user->name} (ID: {$user->id})\n";
    echo "Role: {$user->role}\n\n";
} else {
    echo "User admin@admin.com not found\n\n";
}

// Get all menu items
$menuItems = MenuVisibility::all();

echo "Menu Visibility Items in Production Database:\n";
echo "==========================================\n";

if ($menuItems->isEmpty()) {
    echo "No menu items found in the database.\n";
} else {
    foreach ($menuItems as $item) {
        echo sprintf(
            "%-30s | %-40s | %s\n", 
            $item->menu_key, 
            $item->menu_name, 
            $item->is_visible ? 'VISIBLE' : 'HIDDEN'
        );
    }
    
    echo "\nTotal items: " . $menuItems->count() . "\n";
} 