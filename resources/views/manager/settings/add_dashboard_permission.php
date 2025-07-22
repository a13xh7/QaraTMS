<?php

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

// Create the permissions if they don't exist
$permissions = [
    'access_manager_dashboard',
    'access_settings'
];

foreach ($permissions as $permName) {
    $permission = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
    echo "Created/Found permission: {$permName}\n";
}

// Get the user
$user = User::where('email', 'admin@admin.com')->first();

if ($user) {
    // Create Admin role if it doesn't exist
    $adminRole = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
    echo "Created/Found role: Admin\n";
    
    // Give the role all permissions
    foreach ($permissions as $permName) {
        $adminRole->givePermissionTo($permName);
        echo "Gave Admin role the {$permName} permission\n";
    }
    
    // Assign the role to the user
    $user->assignRole($adminRole);
    echo "Assigned Admin role to {$user->name}\n";
    
    // Also give the permissions directly to the user for good measure
    foreach ($permissions as $permName) {
        $user->givePermissionTo($permName);
        echo "Gave {$permName} permission directly to {$user->name}\n";
    }
    
    echo "\nSuccessfully set up permissions for {$user->name}\n";
    
    // List all permissions for the user
    echo "\nCurrent permissions for {$user->name}:\n";
    foreach ($user->getAllPermissions() as $perm) {
        echo "- {$perm->name}\n";
    }
} else {
    echo "User admin@admin.com not found\n";
} 