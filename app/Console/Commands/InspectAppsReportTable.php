<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InspectAppsReportTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inspect:apps-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inspect the testreport.apps_report table structure';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Inspecting testreport.apps_report table...');
        
        // Check if the connection exists
        try {
            DB::connection('testreport')->getPdo();
            $this->info('Successfully connected to testreport database.');
        } catch (\Exception $e) {
            $this->error('Connection to testreport database failed: ' . $e->getMessage());
            return 1;
        }
        
        // Get columns
        try {
            $columns = Schema::connection('testreport')->getColumnListing('apps_report');
            
            if (empty($columns)) {
                $this->error('No columns found in apps_report table.');
                return 1;
            }
            
            $this->info('Columns in apps_report table:');
            foreach ($columns as $column) {
                $this->line('- ' . $column);
            }
            
            // Check for specific columns
            $this->info("\nChecking for specific columns:");
            $this->line('environment: ' . (in_array('environment', $columns) ? 'Found ✓' : 'Not found ✗'));
            $this->line('env: ' . (in_array('env', $columns) ? 'Found ✓' : 'Not found ✗'));
            $this->line('env_name: ' . (in_array('env_name', $columns) ? 'Found ✓' : 'Not found ✗'));
            $this->line('created_at: ' . (in_array('created_at', $columns) ? 'Found ✓' : 'Not found ✗'));
            $this->line('timestamp: ' . (in_array('timestamp', $columns) ? 'Found ✓' : 'Not found ✗'));
            $this->line('test_date: ' . (in_array('test_date', $columns) ? 'Found ✓' : 'Not found ✗'));
            
            // Get a sample row to see actual data
            $sampleRow = DB::connection('testreport')->table('apps_report')->first();
            
            if ($sampleRow) {
                $this->info("\nSample row data:");
                foreach ((array)$sampleRow as $column => $value) {
                    $this->line($column . ': ' . $value);
                }
            } else {
                $this->warn('No data found in apps_report table.');
            }
            
        } catch (\Exception $e) {
            $this->error('Error getting columns: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
} 