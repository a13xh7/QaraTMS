<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminUser = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'role' => 'Administrator'
            ]
        );

        // Create all permissions
        $permissions = [
            'manage_users',
            'add_edit_projects',
            'delete_projects',
            'add_edit_repositories',
            'delete_repositories',
            'add_edit_test_suites',
            'delete_test_suites',
            'add_edit_test_cases',
            'delete_test_cases',
            'add_edit_test_plans',
            'delete_test_plans',
            'add_edit_test_runs',
            'delete_test_runs',
            'add_edit_documents',
            'delete_documents',
            'access_manager_dashboard',
            'access_settings'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Give all permissions to admin user
        $adminUser->givePermissionTo($permissions);

        // Give specific permissions to 
        $moderatorUser->givePermissionTo([
            'manage_users',
            'add_edit_projects',
            'delete_projects',
            'add_edit_repositories',
            'delete_repositories',
            'add_edit_test_suites',
            'delete_test_suites',
            'add_edit_test_cases',
            'delete_test_cases',
            'add_edit_test_plans',
            'delete_test_plans',
            'add_edit_test_runs',
            'delete_test_runs',
            'add_edit_documents',
            'delete_documents'
        ]);
    }
}
