<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Define permissions to be created
        $permissions = [
            'access_manager_dashboard',
            'access_settings'
        ];

        // Create permissions if they don't exist
        foreach ($permissions as $permissionName) {
            if (!Permission::where('name', $permissionName)->exists()) {
                Permission::create(['name' => $permissionName]);
            }
        }

        // Add these permissions to the specified users
        $authorizedEmails = [
            'admin@admin.com',

        ];

        foreach ($authorizedEmails as $email) {
            $user = User::where('email', $email)->first();
            if ($user) {
                $user->givePermissionTo(['access_manager_dashboard', 'access_settings']);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove permissions from users first
        $users = User::permission(['access_manager_dashboard', 'access_settings'])->get();
        foreach ($users as $user) {
            $user->revokePermissionTo(['access_manager_dashboard', 'access_settings']);
        }

        // Delete the permissions
        Permission::whereIn('name', ['access_manager_dashboard', 'access_settings'])->delete();
    }
}; 