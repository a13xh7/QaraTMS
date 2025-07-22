<?php

// Bootstrap Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$userRoles = [
    'Emile' => 'TE Manager'
];

$updated = 0;

foreach ($userRoles as $name => $role) {
    $user = User::where('name', 'like', "%{$name}%")->first();
    
    if ($user) {
        $user->update(['role' => $role]);
        echo "Updated {$user->name}'s role to {$role}\n";
        $updated++;
    } else {
        echo "User '{$name}' not found\n";
    }
}

echo "\nComplete! Updated roles for {$updated} users.\n"; 