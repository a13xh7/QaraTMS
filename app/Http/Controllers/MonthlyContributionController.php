<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\GitLabService;
use App\Services\MonthlyContributionService;
use App\Models\MonthlyContribution;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MonthlyContributionController extends Controller
{
   
    /**
     * Display the Monthly Contribution Analysis page
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $latestUpdatedAt = MonthlyContribution::max('updated_at');
        $latestUpdatedAtGMT7 = Carbon::parse($latestUpdatedAt)->timezone('Asia/Jakarta');
        
        $availableYears = MonthlyContribution::getUniqueYears();
        
        $availableMonthNames = MonthlyContribution::pluck('month_name')
                                            ->unique()
                                            ->values()
                                            ->toArray();
        
        $query = MonthlyContribution::query();
        
        if (!$request->filled('year') || $request->input('year') == 'all') {
            $year = $availableYears; 
        } else {
            $year = [$request->input('year')];
        }

        $query = $query->whereIn('year', $year);

        if (!$request->filled('month') || $request->input('month') == 'all') {
            $month = $availableMonthNames;
        } else {
            $month = [$request->input('month')]; 
        }

        $query = $query->whereIn('month_name', $month);
        $responseData = $query->get();

        $initialData = $this->getContributionsFromDatabase($responseData);
        $totalMRCreated = $responseData->sum('mr_created');
        $totalMRApproved = $responseData->sum('mr_approved');
        $totalMRPush = $responseData->sum('repo_pushes');
        $totalEvent =$responseData->sum('total_events');

        $topContributionData = $this->getTopContributionsData($responseData);
        $trendData = $this->getTrendData($responseData, $year, $month);
        
        return view('manager.monthly_contribution', [
            'initialData' => $initialData,
            'latestUpdatedAtGMT7' => $latestUpdatedAtGMT7,
            'availableYears' => $availableYears,
            'availableMonth' => $availableMonthNames,
            'topContributionData' => $topContributionData,
            'trendData' => $trendData,
            'totalMRCreated' => $totalMRCreated,
            'totalMRApproved' => $totalMRApproved,
            'totalMRPush' => $totalMRPush,
            'totalEvent' => $totalEvent
        ]);
    }
    
    /**
     * Helper method to determine filter values from request.
     *
     * @param  Request  $request
     * @param  string  $paramName  The name of the request parameter (e.g., 'year', 'month').
     * @param  Collection  $allAvailableValues  A collection of all possible unique values for this filter.
     * @return array  An array of values to use in the whereIn clause, or empty array if 'all' is selected.
     */
    protected function getFilterValues(Request $request, string $paramName, Collection $allAvailableValues): array
    {
        $filterValue = $request->input($paramName);

        // If parameter is not filled or is 'all', return all unique values from the database
        if (!$request->filled($paramName) || $filterValue == 'all') {
            // Return the values from the collection as an array
            return $allAvailableValues->values()->toArray();
        }

        // If a specific value is provided, return it as a single-element array
        // Ensure it's treated as an array for whereIn
        return is_array($filterValue) ? $filterValue : [$filterValue];
    }

    /**
     * Get contributions from database for all available years or a specific year
     * 
     * @param string $year 'all' for all years or specific year
     * @param string $month 'all' for all months or specific month
     * @return array
     */
    private function getContributionsFromDatabase($contributions)
    {
        // Convert to array format compatible with the frontend
        return $contributions->map(function($item) {
            return [
                'year' => $item->year,
                'month' => $item->month,
                'monthName' => $item->month_name,
                'name' => $item->name,
                'mrCreated' => $item->mr_created,
                'mrApproved' => $item->mr_approved,
                'repoPushes' => $item->repo_pushes,
                'totalEvents' => $item->total_events
            ];
        })->toArray();
    }

    private function getTopContributionsData($contributions)
    {
        $aggregatedData = $contributions->groupBy('name')
            ->map(function ($contributorItems, $contributorName) {
                return [
                    'name' => $contributorName,
                    'totalEvents' => $contributorItems->sum('total_events'),
                    'mrCreated' => $contributorItems->sum('mr_created'),
                    'mrApproved' => $contributorItems->sum('mr_approved'),
                    'repoPushes' => $contributorItems->sum('repo_pushes'),
                ];
            })
            ->values();

        $sortedAggregatedData = $aggregatedData->sortByDesc('totalEvents')->take(10)->values();;
        $formattedData = [
            'labels' => $sortedAggregatedData->pluck('name')->toArray(),
            'totalEvents' => $sortedAggregatedData->pluck('totalEvents')->toArray(),
            'mrCreated' => $sortedAggregatedData->pluck('mrCreated')->toArray(),
            'mrApproved' => $sortedAggregatedData->pluck('mrApproved')->toArray(),
            'repoPushes' => $sortedAggregatedData->pluck('repoPushes')->toArray(),
        ];

        return response()->json($formattedData);
    }

    private function getTrendData($contributions)
    {
        $monthNames = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];
        
        $aggregated = $contributions->groupBy(function ($item) {
                return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
            })
            ->map(function ($monthItems, $yearMonthKey) use ($monthNames) {
                list($year, $monthNum) = explode('-', $yearMonthKey);
                $monthNum = (int) $monthNum;
                $label = ($monthNames[$monthNum] ?? 'Unknown Month') . ' ' . $year;
                $sortKey = $yearMonthKey;

                return [
                    'label' => $label,
                    'totalEvents' => $monthItems->sum('total_events'),
                    'mrCreated' => $monthItems->sum('mr_created'),
                    'mrApproved' => $monthItems->sum('mr_approved'),
                    'repoPushes' => $monthItems->sum('repo_pushes'),
                    'sortKey' => $sortKey,
                ];
            })
            ->values();

        $sortedAggregated = $aggregated->sortBy('sortKey')->values();

        $formattedData = [
            'labels' => $sortedAggregated->pluck('label')->toArray(),
            'totalEvents' => $sortedAggregated->pluck('totalEvents')->toArray(),
            'mrCreated' => $sortedAggregated->pluck('mrCreated')->toArray(),
            'mrApproved' => $sortedAggregated->pluck('mrApproved')->toArray(),
            'repoPushes' => $sortedAggregated->pluck('repoPushes')->toArray(),
        ];

        Log::info("Aggregated Data by Month (PHP): ", ['data' => $formattedData]);

        return response()->json($formattedData);
    }
}
