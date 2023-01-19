<?php

namespace Database\Seeders;

use App\User;
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
        $adminUser = User::create([
            'name' => 'Admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('password')
        ]);

        Permission::create(['name' => 'manage_users']);

        Permission::create(['name' => 'add_edit_projects']);
        Permission::create(['name' => 'delete_projects']);

        Permission::create(['name' => 'add_edit_repositories']);
        Permission::create(['name' => 'delete_repositories']);

        Permission::create(['name' => 'add_edit_test_suites']);
        Permission::create(['name' => 'delete_test_suites']);

        Permission::create(['name' => 'add_edit_test_cases']);
        Permission::create(['name' => 'delete_test_cases']);

        Permission::create(['name' => 'add_edit_test_plans']);
        Permission::create(['name' => 'delete_test_plans']);

        Permission::create(['name' => 'add_edit_test_runs']);
        Permission::create(['name' => 'delete_test_runs']);

        Permission::create(['name' => 'add_edit_documents']);
        Permission::create(['name' => 'delete_documents']);

        $adminUser->givePermissionTo([
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
