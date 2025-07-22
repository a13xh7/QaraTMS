<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            ['name' => 'Quality Team', 'email' => 'quality-team@gmail.com', 'password' => 'password'],
        ];

        // Create the 'add_edit_documents' permission if it doesn't exist
        Permission::firstOrCreate(['name' => 'add_edit_documents', 'guard_name' => 'web']);

        // Create or update each user
        foreach ($users as $userData) {
            try {
                $user = User::updateOrCreate(
                    ['email' => $userData['email']],
                    [
                        'name' => $userData['name'],
                        'password' => Hash::make($userData['password']),
                    ]
                );
                
                if ($this->command) {
                    $this->command->info("Added/updated user: {$userData['name']} ({$userData['email']})");
                }
                
                // Give 'add_edit_documents' permission to all users
                $user->givePermissionTo('add_edit_documents');
                
                if ($this->command) {
                    $this->command->info("Granted 'add_edit_documents' permission to {$userData['name']}");
                }
            } catch (\Exception $e) {
                if ($this->command) {
                    $this->command->error("Error creating user {$userData['email']}: {$e->getMessage()}");
                }
            }
        }

        if ($this->command) {
            $this->command->info('User seeding completed successfully!');
        }
    }
} 