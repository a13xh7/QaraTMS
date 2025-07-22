<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AppsReport extends Model
{
    use HasFactory;

    protected $connection = 'flutterreport';
    protected $table = 'report';
    public $timestamps = false; // If the table doesn't have created_at and updated_at columns
    
    /**
     * Get all squad names
     * 
     * @return array
     */
    public static function getSquadNames()
    {
        return self::select('squad_name')
            ->groupBy('squad_name')
            ->orderBy('squad_name')
            ->pluck('squad_name')
            ->toArray();
    }
    
    /**
     * Get data for the Apps Automation Dashboard
     * 
     * @param string $environment
     * @param string $squad
     * @param string $startDate
     * @param string $endDate
     * @param int $limit Maximum number of records to return (default 1000)
     * @return array
     */
    public static function getAppsAutomationData($environment = null, $squad = null, $startDate = null, $endDate = null, $limit = 1000)
    {
        $query = self::query();
        
        // Add logging for query debugging
        \Log::info('Apps Dashboard Query Parameters', [
            'environment' => $environment,
            'squad' => $squad,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'limit' => $limit
        ]);
        
        // Filter by environment (env column)
        if ($environment) {
            $query->where('env', $environment);
        }
        
        // Filter by squad
        if ($squad) {
            $query->where('squad_name', $squad);
        }
        
        // Filter by date range with proper format validation
        if ($startDate && $endDate) {
            $startDateTime = date('Y-m-d H:i:s', strtotime($startDate));
            $endDateTime = date('Y-m-d H:i:s', strtotime($endDate));
            $query->whereBetween('date', [$startDateTime, $endDateTime]);
            \Log::info('Adding date range filter', ['startDate' => $startDateTime, 'endDate' => $endDateTime]);
        } else {
            \Log::warning('No date range provided for API dashboard query');
        }
        
        // Only select necessary columns to reduce data transfer
        $query->select(['id', 'squad_name', 'app_version', 'env', 'test_status', 
                       'total_scenario', 'total_passed_scenario', 'total_failed_scenario', 
                       'success_rate', 'failure_rate', 'execution_time', 'branch_name', 'te_pic', 'link_job', 'date']);
        
        // Sort by date in descending order (latest first)
        $query->orderBy('date', 'desc');
        
        // Limit the number of results to prevent timeout
        $query->limit($limit);
        
        // Log the SQL query that will be executed (for debugging)
        $sql = $query->toSql();
        $bindings = $query->getBindings();
        \Log::info('Apps Dashboard Raw SQL Query', ['sql' => $sql, 'bindings' => $bindings]);
        
        // Set a higher time limit for this specific operation
        $originalTimeLimit = ini_get('max_execution_time');
        set_time_limit(120); // 2 minutes
        
        try {
            $result = $query->get();
            \Log::info('Apps Dashboard Query Results', ['count' => $result->count()]);
        } finally {
            // Restore the original time limit
            set_time_limit($originalTimeLimit);
        }
        
        return $result;
    }
    
    /**
     * Get table schema information
     *
     * @return array
     */
    public static function getTableSchema()
    {
        return Schema::connection('flutterreport')->getColumnListing('report');
    }
} 