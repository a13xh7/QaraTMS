<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AdminSeeder::class,
            UserSeeder::class,
            RoleSeeder::class,
            TestSeeder::class,
            SettingsSeeder::class,
            ProjectSeeder::class,
            RepositorySeeder::class,
            SuiteSeeder::class,
            MenuVisibilitySeeder::class,
            MonthlyContributionSeeder::class,
        ]);
    }
} 