<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MsReport;
use App\Models\AppsReport;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the analytics dashboard
     */
    public function analytics()
    {
        return view('dashboard.analytics');
    }

    /**
     * Display the bug budget dashboard
     */
    public function bugBudget()
    {
        return view('dashboard.bug_budget');
    }

    /**
     * Display the defect analytics dashboard
     */
    public function defectAnalytics()
    {
        return view('dashboard.defect_analytics');
    }

    /**
     * Display the testing progress dashboard
     */
    public function testingProgress()
    {
        return view('dashboard.testing_progress');
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
            
            // Prepare data for charts
            $chartData = $this->prepareApiChartData($reportData);
            
            // Add query stats to debug
            $dateDebug['result_count'] = $reportData->count();
            $dateDebug['has_data'] = $reportData->isNotEmpty();
            $dateDebug['record_limit'] = $recordLimit;
            
            // Check if we hit the record limit
            if ($reportData->count() >= $recordLimit) {
                $dateDebug['warning'] = "Record limit of $recordLimit reached. Some data may not be displayed.";
                \Log::warning("Apps Dashboard record limit reached", ['limit' => $recordLimit]);
            }
            
        } catch (\Exception $e) {
            // Handle database connection errors
            \Log::error('Apps Dashboard Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $reportData = collect([]);
            $paginatedData = $this->paginateCollection(collect([]), $perPage, 1, $request);
            $chartData = [
                'successLabels' => [],
                'successRates' => [],
                'passedScenarios' => [],
                'failedScenarios' => [],
                'executionTimes' => [],
            ];
            
            $dateDebug['error'] = $e->getMessage();
            $dateDebug['error_trace'] = $e->getTraceAsString();
        }
        
        return view('analytics.apps_dashboard', compact(
            'environments',
            'squads',
            'selectedEnvironment',
            'selectedSquad',
            'startDate',
            'endDate',
            'reportData',
            'paginatedData',
            'perPage',
            'chartData',
            'dateDebug'
        ));
    }

    /**
     * Display the API automation dashboard
     *
     * @param Request $request
     * @return View
     */
    public function apiDashboard(Request $request)
    {
        // Define environments as hardcoded options
        $environments = ['staging', 'production', 'uat'];
        $selectedEnvironment = $request->input('environment', 'staging');
        
        // Get available microservices from the database
        try {
            // Cache microservice names for 24 hours to reduce database calls
            $microservices = \Cache::remember('microservice_names', 60*60*24, function() {
                return MsReport::getMicroserviceNames();
            });
        } catch (\Exception $e) {
            $microservices = [];
            \Log::error('Failed to get microservice names', ['error' => $e->getMessage()]);
        }
        
        $selectedMicroservice = $request->input('microservice');
        
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
        \Log::info('API Dashboard Controller Parameters', [
            'raw_start' => $request->input('start_date'),
            'raw_end' => $request->input('end_date'),
            'formatted_start' => $startDate,
            'formatted_end' => $endDate,
            'query_start' => $startDateForQuery,
            'query_end' => $endDateForQuery,
            'environment' => $selectedEnvironment,
            'microservice' => $selectedMicroservice
        ]);
        
        // Pagination parameters
        $perPage = (int)$request->input('per_page', 10);
        // Ensure per_page is one of the allowed values
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 25;
        }
        
        // Get the current page from the request
        $page = (int)$request->input('page', 1);
        if ($page < 1) {
            $page = 1;
        }
        
        // Maximum number of records to return from database query
        $recordLimit = 2000;
        
        // Generate a unique cache key based on query parameters
        $cacheKey = "api_dashboard_data_" . md5(
            $selectedEnvironment . 
            $selectedMicroservice . 
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
            $dbStatus = DB::connection('testreport')->getPdo() ? 'connected' : 'failed';
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
            $reportData = \Cache::remember($cacheKey, 30, function() use ($selectedEnvironment, $selectedMicroservice, $startDateForQuery, $endDateForQuery, $recordLimit) {
                \Log::info('Cache miss - loading data from database');
                return MsReport::getApiAutomationData(
                    $selectedEnvironment,
                    $selectedMicroservice,
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
            \Log::info('API Dashboard Query Results', [
                'count' => $reportData->count(),
                'has_data' => $reportData->isNotEmpty(),
                'from_cache' => $dateDebug['from_cache']
            ]);
            
            // Create paginated data
            $paginatedData = $this->paginateCollection($reportData, $perPage, $page, $request);
            
            // Prepare data for charts
            $chartData = $this->prepareApiChartData($reportData);
            
            // Add query stats to debug
            $dateDebug['result_count'] = $reportData->count();
            $dateDebug['has_data'] = $reportData->isNotEmpty();
            $dateDebug['record_limit'] = $recordLimit;
            
            // Check if we hit the record limit
            if ($reportData->count() >= $recordLimit) {
                $dateDebug['warning'] = "Record limit of $recordLimit reached. Some data may not be displayed.";
                \Log::warning("API Dashboard record limit reached", ['limit' => $recordLimit]);
            }
            
        } catch (\Exception $e) {
            // Handle database connection errors
            \Log::error('API Dashboard Error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $reportData = collect([]);
            $paginatedData = $this->paginateCollection(collect([]), $perPage, 1, $request);
            $chartData = [
                'successLabels' => [],
                'successRates' => [],
                'passedScenarios' => [],
                'failedScenarios' => [],
                'executionTimes' => [],
            ];
            
            $dateDebug['error'] = $e->getMessage();
            $dateDebug['error_trace'] = $e->getTraceAsString();
        }
        
        return view('analytics.api_dashboard', compact(
            'environments',
            'microservices',
            'selectedEnvironment',
            'selectedMicroservice',
            'startDate',
            'endDate',
            'reportData',
            'paginatedData',
            'perPage',
            'chartData',
            'dateDebug'
        ));
    }

    /**
     * Create a length-aware paginator from a collection
     *
     * @param \Illuminate\Support\Collection $collection
     * @param int $perPage
     * @param int $page
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    private function paginateCollection($collection, $perPage, $page, $request)
    {
        // Calculate the offset for the current page
        $offset = ($page - 1) * $perPage;
        
        // Get only the items for the current page
        $items = $collection->slice($offset, $perPage)->values();
        
        // Create a length-aware paginator - we'll preserve existing URL query params
        $paginator = new LengthAwarePaginator(
            $items, 
            $collection->count(), 
            $perPage, 
            $page, 
            [
                'path' => $request->url(),
                'query' => $request->query()
            ]
        );
        
        return $paginator;
    }

    /**
     * Prepare data for API automation charts
     * 
     * @param Collection $reportData
     * @return array
     */
    private function prepareApiChartData($reportData)
    {
        // Group data by microservice
        $microserviceData = [];
        $successRates = [];
        $testCounts = [];
        
        foreach ($reportData as $report) {
            $msName = $report->ms_name;
            
            if (!isset($microserviceData[$msName])) {
                $microserviceData[$msName] = [
                    'total' => 0,
                    'passed' => 0,
                    'failed' => 0
                ];
            }
            
            $microserviceData[$msName]['total']++;
            
            if (strtolower($report->ms_status) === 'passed') {
                $microserviceData[$msName]['passed']++;
            } else {
                $microserviceData[$msName]['failed']++;
            }
        }
        
        // Calculate success rates
        foreach ($microserviceData as $msName => $data) {
            $successRate = $data['total'] > 0 ? ($data['passed'] / $data['total']) * 100 : 0;
            $successRates[$msName] = round($successRate, 2);
            $testCounts[$msName] = $data['total'];
        }
        
        return [
            'microserviceData' => $microserviceData,
            'successRates' => $successRates,
            'testCounts' => $testCounts
        ];
    }
}
