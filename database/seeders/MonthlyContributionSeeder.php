<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonthlyContributionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $developers = [
            'john_doe' => ['name' => 'John Doe', 'mrCreated' => 15, 'mrApproved' => 22, 'repoPushes' => 45],
            'jane_smith' => ['name' => 'Jane Smith', 'mrCreated' => 20, 'mrApproved' => 18, 'repoPushes' => 38],
            'bob_johnson' => ['name' => 'Bob Johnson', 'mrCreated' => 8, 'mrApproved' => 12, 'repoPushes' => 30],
            'alice_williams' => ['name' => 'Alice Williams', 'mrCreated' => 25, 'mrApproved' => 15, 'repoPushes' => 50],
        ];
        
        $years = [2023, 2024, 2025];
        
        foreach ($years as $year) {
            for ($month = 1; $month <= 12; $month++) {
                $monthName = Carbon::create($year, $month, 1)->format('F');
                
                foreach ($developers as $username => $dev) {
                    // Add some randomness to the data
                    $mrCreated = $dev['mrCreated'] + rand(-5, 5);
                    $mrApproved = $dev['mrApproved'] + rand(-5, 5);
                    $repoPushes = $dev['repoPushes'] + rand(-10, 10);
                    
                    // Ensure values don't go below zero
                    $mrCreated = max(0, $mrCreated);
                    $mrApproved = max(0, $mrApproved);
                    $repoPushes = max(0, $repoPushes);
                    
                    $totalEvents = $mrCreated + $mrApproved + $repoPushes;
                    
                    // Skip some months randomly to simulate missing data
                    if (rand(0, 10) > 8) {
                        continue;
                    }
                    
                    // Insert data
                    DB::table('monthly_contributions')->insertOrIgnore([
                        'year' => $year,
                        'month' => $month,
                        'month_name' => $monthName,
                        'username' => $username,
                        'name' => $dev['name'],
                        'mr_created' => $mrCreated,
                        'mr_approved' => $mrApproved,
                        'repo_pushes' => $repoPushes,
                        'total_events' => $totalEvents,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
