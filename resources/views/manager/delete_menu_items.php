<?php

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MenuVisibility;

// List of menu keys to delete
$menuKeysToDelete = [
    'audit_management',
    'risk_management',
    'capa',
    'training',
    'nonconformance',
    'document_control'
];

// Delete each menu item
foreach ($menuKeysToDelete as $menuKey) {
    $menuItem = MenuVisibility::where('menu_key', $menuKey)->first();
    
    if ($menuItem) {
        $menuItem->delete();
        echo "Menu item '{$menuKey}' has been deleted.\n";
    } else {
        echo "Menu item '{$menuKey}' not found in the database.\n";
    }
}

echo "\nUnwanted menu items have been removed successfully!\n"; 