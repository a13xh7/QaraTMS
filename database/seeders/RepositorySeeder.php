<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RepositorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the AF project ID
        $afProjectId = DB::table('projects')->where('title', 'AF')->value('id');
        
        if (!$afProjectId) {
            // Removed command->error() call to avoid null error in tinker
            return;
        }

        $repositories = [
            [
                'project_id' => $afProjectId,
                'title' => 'User Experience',
                'prefix' => 'SHP',
                'description' => 'Shopex E-commerce Platform',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'project_id' => $afProjectId,
                'title' => 'Payment',
                'prefix' => 'PAY',
                'description' => 'Payment Gateway Integration',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'project_id' => $afProjectId,
                'title' => 'Growth',
                'prefix' => 'GRW',
                'description' => 'Growth and Voucher Management System',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($repositories as $repository) {
            DB::table('repositories')->insertOrIgnore($repository);
        }

        // Removed command->info() call to avoid null error in tinker
    }
} 