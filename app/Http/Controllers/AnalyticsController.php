<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MsReport;
use App\Models\AppsReport;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Display the analytics dashboard
     */
    public function index()
    {
        return view('dashboard.analytics');
    }

    /**
     * Display the Apps automation dashboard
     *
     * @param Request $request
     * @return View
     */
    public function appsDashboard(Request $request)
    {
        // Define environments as hardcoded options
        $environments = ['staging', 'production', 'uat'];
        $selectedEnvironment = $request->input('environment', 'staging');

        try {
            // Cache squad names for 24 hours to reduce database calls
            $squads = \Cache::remember('squad_names', 60*60*24, function() {
                return AppsReport::getSquadNames();
            });
        } catch (\Exception $e) {
            $squads = [];
            \Log::error('Failed to get squad names', ['error' => $e->getMessage()]);
        }

        $selectedSquad = $request->input('squad');

        // Handle date inputs properly
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Default date range (last 30 days) if not provided
        if (empty($startDate)) {
            $startDate = now()->subMonth()->format('Y-m-d');
        }
        
        if (empty($endDate)) {
            $endDate = now()->format('Y-m-d');
        }
        
        // Format dates properly for database query
        $startDateForQuery = date('Y-m-d 00:00:00', strtotime($startDate));
        $endDateForQuery = date('Y-m-d 23:59:59', strtotime($endDate));
        
        // Log query parameters
        \Log::info('Apps Dashboard Controller Parameters', [
            'raw_start' => $request->input('start_date'),
            'raw_end' => $request->input('end_date'),
            'formatted_start' => $startDate,
            'formatted_end' => $endDate,
            'query_start' => $startDateForQuery,
            'query_end' => $endDateForQuery,
            'environment' => $selectedEnvironment,
            'squad' => $selectedSquad
        ]);

        // Pagination parameters
        $perPage = (int)$request->input('per_page', 10);
        // Ensure per_page is one of the allowed values
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }
        
        // Get the current page from the request
        $page = (int)$request->input('page', 1);
        if ($page < 1) {
            $page = 1;
        }
        
        // Maximum number of records to return from database query
        $recordLimit = 2000;
        
        // Generate a unique cache key based on query parameters
        $cacheKey = "apps_dashboard_data_" . md5(
            $selectedEnvironment . 
            $selectedSquad . 
            $startDateForQuery . 
            $endDateForQuery
        );

        // Debugging info
        $debug = $request->has('debug');
        $dateDebug = [
            'raw_start' => $request->input('start_date'),
            'raw_end' => $request->input('end_date'),
            'parsed_start' => $startDate,
            'parsed_end' => $endDate,
            'query_start' => $startDateForQuery,
            'query_end' => $endDateForQuery,
            'cache_key' => $cacheKey,
            'record_limit' => $recordLimit,
        ];
        
        // Force refresh cache if requested
        $refreshCache = $request->has('refresh_cache');
        if ($refreshCache) {
            \Cache::forget($cacheKey);
            $dateDebug['cache_refreshed'] = true;
        } else {
            $dateDebug['cache_refreshed'] = false;
        }
        
        try {
            // Get the database connection to test it
            $dbStatus = DB::connection('flutterreport')->getPdo() ? 'connected' : 'failed';
            $dateDebug['db_status'] = $dbStatus;
            
            // Only query schema if in debug mode to improve performance
            if ($debug) {
                try {
                    $columns = MsReport::getTableSchema();
                    $dateDebug['table_columns'] = $columns;
                    
                    // Check if 'date' column exists
                    $dateDebug['has_date_column'] = in_array('date', $columns);
                    
                    // Get a sample record to check date format
                    $sampleRecord = MsReport::first();
                    if ($sampleRecord) {
                        $dateDebug['sample_date'] = $sampleRecord->date;
                        $dateDebug['sample_record_id'] = $sampleRecord->id;
                    }
                } catch (\Exception $e) {
                    $dateDebug['schema_error'] = $e->getMessage();
                    \Log::error('Schema retrieval error', ['error' => $e->getMessage()]);
                }
            }
            
            // Get the report data with caching (30 minutes) to prevent timeouts on subsequent requests
            $reportData = \Cache::remember($cacheKey, 30, function() use ($selectedEnvironment, $selectedSquad, $startDateForQuery, $endDateForQuery, $recordLimit) {
                \Log::info('Cache miss - loading data from database');
                return AppsReport::getAppsAutomationData(
                    $selectedEnvironment,
                    $selectedSquad,
                    $startDateForQuery,
                    $endDateForQuery,
                    $recordLimit
                );
            });
            
            $dateDebug['cache_key'] = $cacheKey;
            $dateDebug['from_cache'] = !$refreshCache && \Cache::has($cacheKey);
            
            // Add cache timestamp information
            $dateDebug['cache_created_at'] = date('Y-m-d H:i:s');
            
            // For from_cache=true, we can only estimate expiration based on the 30-minute setting
            if ($dateDebug['from_cache']) {
                $dateDebug['cache_expires_at'] = date('Y-m-d H:i:s', strtotime('+30 minutes'));
            }
            
            // Log query results
            \Log::info('Apps Dashboard Query Results', [
                'count' => $reportData->count(),
                'has_data' => $reportData->isNotEmpty(),
                'from_cache' => $dateDebug['from_cache']
            ]);
            
            // Create paginated data
            $paginatedData = $this->paginateCollection($reportData, $perPage, $page, $request);
            
            return view('analytics.apps_dashboard', [
                'reportData' => $reportData,
                'paginatedData' => $paginatedData,
                'environments' => $environments,
                'selectedEnvironment' => $selectedEnvironment,
                'squads' => $squads,
                'selectedSquad' => $selectedSquad,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'debug' => $debug,
                'dateDebug' => $dateDebug,
                'perPage' => $perPage,
                'totalRecords' => $reportData->count(),
                'recordLimit' => $recordLimit
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Apps Dashboard Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'parameters' => [
                    'environment' => $selectedEnvironment,
                    'squad' => $selectedSquad,
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]);
            
            // Create empty paginated data for error case
            $emptyData = collect([]);
            $paginatedData = $this->paginateCollection($emptyData, $perPage, $page, $request);
            
            return view('analytics.apps_dashboard', [
                'reportData' => $emptyData,
                'paginatedData' => $paginatedData,
                'environments' => $environments,
                'selectedEnvironment' => $selectedEnvironment,
                'squads' => $squads,
                'selectedSquad' => $selectedSquad,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'error' => 'Failed to load data: ' . $e->getMessage(),
                'debug' => $debug,
                'dateDebug' => $dateDebug,
                'perPage' => $perPage,
                'totalRecords' => 0,
                'recordLimit' => $recordLimit
            ]);
        }
    }

    /**
     * Display the API dashboard
     *
     * @param Request $request
     * @return View
     */
    public function apiDashboard(Request $request)
    {
        // Define environments as hardcoded options
        $environments = ['staging', 'production', 'uat'];
        $selectedEnvironment = $request->input('environment', 'staging');

        try {
            // Cache squad names for 24 hours to reduce database calls
            $squads = \Cache::remember('squad_names', 60*60*24, function() {
                return MsReport::getSquadNames();
            });
        } catch (\Exception $e) {
            $squads = [];
            \Log::error('Failed to get squad names', ['error' => $e->getMessage()]);
        }

        $selectedSquad = $request->input('squad');

        // Handle date inputs properly
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Default date range (last 30 days) if not provided
        if (empty($startDate)) {
            $startDate = now()->subMonth()->format('Y-m-d');
        }
        
        if (empty($endDate)) {
            $endDate = now()->format('Y-m-d');
        }
        
        // Format dates properly for database query
        $startDateForQuery = date('Y-m-d 00:00:00', strtotime($startDate));
        $endDateForQuery = date('Y-m-d 23:59:59', strtotime($endDate));
        
        // Pagination parameters
        $perPage = (int)$request->input('per_page', 10);
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }
        
        $page = (int)$request->input('page', 1);
        if ($page < 1) {
            $page = 1;
        }
        
        $recordLimit = 2000;
        
        // Generate cache key
        $cacheKey = "api_dashboard_data_" . md5(
            $selectedEnvironment . 
            $selectedSquad . 
            $startDateForQuery . 
            $endDateForQuery
        );
        
        $refreshCache = $request->has('refresh_cache');
        if ($refreshCache) {
            \Cache::forget($cacheKey);
        }
        
        try {
            $reportData = \Cache::remember($cacheKey, 30, function() use ($selectedEnvironment, $selectedSquad, $startDateForQuery, $endDateForQuery, $recordLimit) {
                return MsReport::getApiAutomationData(
                    $selectedEnvironment,
                    $selectedSquad,
                    $startDateForQuery,
                    $endDateForQuery,
                    $recordLimit
                );
            });
            
            $paginatedData = $this->paginateCollection($reportData, $perPage, $page, $request);
            
            // Get microservices data for API dashboard
            $microservices = \Cache::remember('microservices_list', 60*60*24, function() {
                return MsReport::getMicroserviceNames();
            });
            $selectedMicroservice = $request->input('microservice');
            
            return view('analytics.api_dashboard', [
                'reportData' => $reportData,
                'paginatedData' => $paginatedData,
                'environments' => $environments,
                'selectedEnvironment' => $selectedEnvironment,
                'squads' => $squads,
                'selectedSquad' => $selectedSquad,
                'microservices' => $microservices,
                'selectedMicroservice' => $selectedMicroservice,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'perPage' => $perPage,
                'totalRecords' => $reportData->count(),
                'recordLimit' => $recordLimit
            ]);
            
        } catch (\Exception $e) {
            \Log::error('API Dashboard Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Get microservices data for API dashboard
            $microservices = \Cache::remember('microservices_list', 60*60*24, function() {
                return MsReport::getMicroserviceNames();
            });
            $selectedMicroservice = $request->input('microservice');
            
            // Create empty paginated data for error case
            $emptyData = collect([]);
            $paginatedData = $this->paginateCollection($emptyData, $perPage, $page, $request);
            
            return view('analytics.api_dashboard', [
                'reportData' => $emptyData,
                'paginatedData' => $paginatedData,
                'environments' => $environments,
                'selectedEnvironment' => $selectedEnvironment,
                'squads' => $squads,
                'selectedSquad' => $selectedSquad,
                'microservices' => $microservices,
                'selectedMicroservice' => $selectedMicroservice,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'error' => 'Failed to load data: ' . $e->getMessage(),
                'perPage' => $perPage,
                'totalRecords' => 0,
                'recordLimit' => $recordLimit
            ]);
        }
    }

    /**
     * Paginate a collection
     */
    private function paginateCollection($collection, $perPage, $page, $request)
    {
        $offset = ($page - 1) * $perPage;
        $items = $collection->slice($offset, $perPage);
        
        return new LengthAwarePaginator(
            $items,
            $collection->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'pageName' => 'page',
            ]
        );
    }
}
