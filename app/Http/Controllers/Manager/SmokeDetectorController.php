<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class SmokeDetectorController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:access_manager_dashboard');
    }
    
    /**
     * Show the smoke detector dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // In a real application, you would fetch this data from your monitoring system
        // For demonstration purposes, we'll use dummy data
        
        // Check if we should use cached data or fetch fresh data
        $cacheKey = 'smoke_detector_data_' . auth()->id();
        $cachedData = Cache::get($cacheKey);
        
        if (!$cachedData || request()->has('refresh')) {
            // This would be an API call to your monitoring system in a real application
            $data = $this->fetchMonitoringData();
            
            // Cache the data for 5 minutes
            Cache::put($cacheKey, $data, now()->addMinutes(5));
        } else {
            $data = $cachedData;
        }
        
        return view('manager.smoke_detector', $data);
    }
    
    /**
     * Fetch monitoring data from various sources.
     *
     * @return array
     */
    private function fetchMonitoringData()
    {
        // In a real application, you would make API calls to your monitoring systems
        // For demonstration, we're using static data
        
        // Simulate API latency
        usleep(rand(100000, 300000));
        
        return [
            'healthScore' => [
                'current' => 85,
                'previous' => 80,
                'status' => 'good',
            ],
            'responseTime' => [
                'current' => 243,
                'previous' => 258,
                'status' => 'normal',
            ],
            'errorRate' => [
                'current' => 3.2,
                'previous' => 2.4,
                'status' => 'elevated',
            ],
            'uptime' => [
                'current' => 99.97,
                'previous' => 99.95,
                'status' => 'excellent',
            ],
            'chartData' => [
                'responseTime' => [
                    'days' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    'values' => [245, 258, 273, 264, 251, 240, 243],
                    'p95Values' => [298, 312, 345, 325, 303, 290, 297],
                ],
                'errorRates' => [
                    'services' => ['Payment', 'Cart', 'Auth', 'Product', 'User', 'Order', 'Notification'],
                    'values' => [5.2, 2.8, 0.3, 1.9, 4.1, 0.5, 2.1],
                ],
            ],
            'alerts' => [
                [
                    'type' => 'danger',
                    'icon' => 'bi-exclamation-triangle-fill',
                    'title' => 'High Error Rate',
                    'time' => '2 hours ago',
                    'description' => 'Payment service showing elevated error rates (5.2%) for 15 minutes.',
                    'service' => 'UserService - Error code: 504',
                    'status' => 'Critical',
                    'statusClass' => 'bg-danger',
                ],
                [
                    'type' => 'warning',
                    'icon' => 'bi-exclamation-circle-fill',
                    'title' => 'Slow Response Time',
                    'time' => '6 hours ago',
                    'description' => 'Cart API response time spiked to 850ms for 10 minutes.',
                    'service' => 'CartService',
                    'status' => 'Warning',
                    'statusClass' => 'bg-warning text-dark',
                ],
                [
                    'type' => 'warning',
                    'icon' => 'bi-exclamation-circle-fill',
                    'title' => 'Database Connection Pool',
                    'time' => '1 day ago',
                    'description' => 'Database connection pool usage above 85% threshold.',
                    'service' => 'ProductService',
                    'status' => 'Warning',
                    'statusClass' => 'bg-warning text-dark',
                ],
                [
                    'type' => 'success',
                    'icon' => 'bi-check-circle-fill',
                    'title' => 'Issue Resolved',
                    'time' => '2 days ago',
                    'description' => 'CPU utilization returned to normal levels after scaling.',
                    'service' => 'AuthService',
                    'status' => 'Resolved',
                    'statusClass' => 'bg-success',
                ],
            ],
            'topIssues' => [
                [
                    'service' => 'PaymentService',
                    'issue' => 'Gateway timeout errors',
                    'impact' => 85,
                    'status' => 'Open',
                    'statusClass' => 'bg-danger',
                ],
                [
                    'service' => 'CartService',
                    'issue' => 'Slow response on /checkout',
                    'impact' => 65,
                    'status' => 'Investigating',
                    'statusClass' => 'bg-warning text-dark',
                ],
                [
                    'service' => 'UserService',
                    'issue' => 'Database connection pool',
                    'impact' => 40,
                    'status' => 'Monitoring',
                    'statusClass' => 'bg-info',
                ],
                [
                    'service' => 'ProductService',
                    'issue' => 'Memory leak in search',
                    'impact' => 25,
                    'status' => 'Fixed',
                    'statusClass' => 'bg-success',
                ],
            ],
        ];
    }
    
    /**
     * Get specific alert details.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAlertDetails(Request $request, $id)
    {
        // In a real application, you would fetch this from your monitoring system
        // For demo purposes, we'll return dummy data
        return response()->json([
            'id' => $id,
            'title' => 'Alert Details',
            'description' => 'Detailed information about alert #' . $id,
            'timeline' => [
                ['time' => '10:15 AM', 'event' => 'Alert triggered'],
                ['time' => '10:17 AM', 'event' => 'Notification sent to on-call engineer'],
                ['time' => '10:22 AM', 'event' => 'Investigation started'],
                ['time' => '10:35 AM', 'event' => 'Root cause identified'],
            ],
            'metrics' => [
                'before' => [243, 245, 250, 520, 480, 350, 290],
                'after' => [290, 270, 260, 250, 245, 240, 243],
            ]
        ]);
    }
    
    /**
     * Fetch real-time data for the dashboard.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchRealTimeData(Request $request)
    {
        // In a real application, you would fetch this from your monitoring system
        // For demo purposes, we'll return slightly modified data to simulate changes
        
        $data = $this->fetchMonitoringData();
        
        // Simulate slight changes in data
        $data['responseTime']['current'] = rand(230, 260);
        $data['errorRate']['current'] = round(rand(28, 36) / 10, 1);
        
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'timestamp' => now()->toIso8601String()
        ]);
    }
} 