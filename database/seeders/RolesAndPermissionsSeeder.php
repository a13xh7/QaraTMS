<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
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
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles
        $roles = [
            'admin',
            'manager',
            'tester',
            'viewer',
            'developer'
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // Assign permissions to roles
        $adminRole = Role::where('name', 'admin')->first();
        $adminRole->syncPermissions($permissions);

        $managerRole = Role::where('name', 'manager')->first();
        $managerRole->syncPermissions([
            'add_edit_projects',
            'add_edit_repositories',
            'add_edit_test_suites',
            'add_edit_test_cases',
            'add_edit_test_plans',
            'add_edit_test_runs',
            'add_edit_documents',
            'access_manager_dashboard'
        ]);

        $testerRole = Role::where('name', 'tester')->first();
        $testerRole->syncPermissions([
            'add_edit_test_cases',
            'add_edit_test_plans',
            'add_edit_test_runs'
        ]);

        $developerRole = Role::where('name', 'developer')->first();
        $developerRole->syncPermissions([
            'add_edit_test_cases',
            'add_edit_test_plans'
        ]);
    }
} 