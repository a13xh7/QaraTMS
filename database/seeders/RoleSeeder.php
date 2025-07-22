<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = DB::table('users')->get();
        
        // Get all roles
        $roles = Role::all();
        
        if ($users->isEmpty() || $roles->isEmpty()) {
            return;
        }

        $updated = 0;

        foreach ($users as $user) {
            // Skip admin users (they already have admin role)
            if ($user->email === 'admin@admin.com') {
                continue;
            }

            // Assign random role to each user
            $randomRole = $roles->random();
            
            // Remove any existing roles
            DB::table('model_has_roles')->where('model_id', $user->id)->delete();
            
            // Assign new role
            DB::table('model_has_roles')->insert([
                'role_id' => $randomRole->id,
                'model_type' => 'App\Models\User',
                'model_id' => $user->id
            ]);

            $updated++;
            
            // Removed command->info() call to avoid null error in tinker
        }

        // Removed command->info() call to avoid null error in tinker

        // Count users by role
        $roleCounts = [];
        foreach ($roles as $role) {
            $count = DB::table('model_has_roles')
                ->where('role_id', $role->id)
                ->count();
            $roleCounts[$role->name] = $count;
        }

        // Removed command->info() call to avoid null error in tinker

        foreach ($roleCounts as $role => $count) {
            // Removed command->info() call to avoid null error in tinker
        }

        // Removed command->info() call to avoid null error in tinker

        foreach ($roleCounts as $role => $count) {
            // Removed command->info() call to avoid null error in tinker
        }
    }
} 