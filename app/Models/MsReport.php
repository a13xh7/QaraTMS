<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class MsReport extends Model
{
    use HasFactory;

    protected $connection = 'testreport';
    protected $table = 'ms_report';
    public $timestamps = false; // If the table doesn't have created_at and updated_at columns
    
    /**
     * Get all microservice names
     * 
     * @return array
     */
    public static function getMicroserviceNames()
    {
        return self::select('ms_name')
            ->groupBy('ms_name')
            ->orderBy('ms_name')
            ->pluck('ms_name')
            ->toArray();
    }
    
    /**
     * Get data for the API Automation Dashboard
     * 
     * @param string $environment
     * @param string $microservice
     * @param string $startDate
     * @param string $endDate
     * @param int $limit Maximum number of records to return (default 1000)
     * @return array
     */
    public static function getApiAutomationData($environment = null, $microservice = null, $startDate = null, $endDate = null, $limit = 1000)
    {
        $query = self::query();
        
        // Add logging for query debugging
        \Log::info('API Dashboard Query Parameters', [
            'environment' => $environment,
            'microservice' => $microservice,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'limit' => $limit
        ]);
        
        // Filter by environment (ms_env column)
        if ($environment) {
            $query->where('ms_env', $environment);
        }
        
        // Filter by microservice
        if ($microservice) {
            $query->where('ms_name', $microservice);
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
        $query->select(['id', 'ms_name', 'ms_env', 'ms_status', 'ms_execution_time', 
                       'ms_total_scenario', 'ms_total_passed_scenario', 'ms_total_failed_scenario', 
                       'ms_success_rate', 'date']);
        
        // Sort by date in descending order (latest first)
        $query->orderBy('date', 'desc');
        
        // Limit the number of results to prevent timeout
        $query->limit($limit);
        
        // Log the SQL query that will be executed (for debugging)
        $sql = $query->toSql();
        $bindings = $query->getBindings();
        \Log::info('API Dashboard Raw SQL Query', ['sql' => $sql, 'bindings' => $bindings]);
        
        // Set a higher time limit for this specific operation
        $originalTimeLimit = ini_get('max_execution_time');
        set_time_limit(120); // 2 minutes
        
        try {
            $result = $query->get();
            \Log::info('API Dashboard Query Results', ['count' => $result->count()]);
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
        return Schema::connection('testreport')->getColumnListing('ms_report');
    }
} 