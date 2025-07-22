<?php

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\MenuVisibility;
use Illuminate\Support\Facades\DB;

// Set user role to admin
$user = User::where('email', 'admin@admin.com')->first();

if ($user) {
    // Update user role to admin
    $user->update(['role' => 'Admin']);
    echo "Updated {$user->name}'s role to Admin\n";
    
    // Make all menu items visible
    MenuVisibility::query()->update(['is_visible' => true]);
    echo "Made all menu items visible\n";
    
    // Ensure manager dashboard is visible
    MenuVisibility::updateOrCreate(
        ['menu_key' => 'manager_dashboard'],
        [
            'menu_name' => 'Manager Dashboard',
            'is_visible' => true,
        ]
    );
    echo "Ensured Manager Dashboard is visible\n";
    
    echo "\nSuccessfully updated production menu visibility for admin@admin.com\n";
} else {
    echo "User admin@admin.com not found\n";
} 