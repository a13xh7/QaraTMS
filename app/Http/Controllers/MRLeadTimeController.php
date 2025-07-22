<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\GitlabMrLeadTime;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use Illuminate\Support\Collection;

class MRLeadTimeController extends Controller
{
    public function index(Request $request)
    {
        $latestUpdatedAt = GitlabMrLeadTime::max('updated_at');
        $latestUpdatedAtGMT7 = Carbon::parse($latestUpdatedAt)->timezone('Asia/Jakarta');

        if (!$request->filled('date_range') && !$request->filled('start_date')) {
            $endDateFormat = (new DateTime())->format(DateTime::ATOM);
            $startDateFormat = new DateTime();
            $startDateFormat->modify('-1 month')->format(DateTime::ATOM);

            $startDate = Carbon::parse($startDateFormat);
            $endDate = Carbon::parse($endDateFormat);
        } else {
             $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
        }

        $repository = $request->input('repository');
        $author = $request->input('author');

        $query = GitlabMrLeadTime::query();

        if ($startDate) {
            $query->whereDate('mr_created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('mr_created_at', '<=', $endDate);
        }
        if ($request->filled('project_name') && $request->input('project_name') !== 'all') {
            $query->where('repository', 'LIKE', '%' . $request->input('project_name') . '%');
        }
        if ($request->filled('author_name') && $request->input('author_name') !== 'all') {
            $query->where('author', $request->input('author_name'));
        }


        $mrs = $query->get();

        $endDate = Carbon::now()->endOfDay();
        $startDate = Carbon::now()->subMonth()->startOfDay();

        if ($request->filled('start_date')) {

            $startDate = Carbon::parse($request->input('start_date'))->startOfDay();
        }
        if ($request->filled('end_date')) {
            $endDate = Carbon::parse($request->input('end_date'))->endOfDay();
        }

        $dataChart = $this->prepareGitlabChartData($mrs, $startDate, $endDate);

        // Summary
        $totalMRs = $mrs->count();
        $avgLeadTimeDays = number_format($mrs->avg('time_to_merge_days') ?? 0, 2);
        $avgLeadTimeHours = number_format($mrs->avg('time_to_merge_hours') ?? 0, 2);
        $avgFirstCommitToMergeDays = number_format($mrs->avg('first_commit_to_merge_days') ?? 0, 2);
        $avgFirstCommitToMergeHours = number_format($mrs->avg('first_commit_to_merge_hours') ?? 0, 2);
        $mrCountPerAuthor = $mrs->groupBy('author')->map(function ($group) {
            return $group->count();
        })->sortDesc();

        $avgTotalMrPerAuthor = $mrCountPerAuthor->avg();
        $avgTotalMrPerAuthor = number_format($avgTotalMrPerAuthor ?? 0, 2);

        $repositories = GitlabMrLeadTime::select('repository')->distinct()->pluck('repository');
        $authors = GitlabMrLeadTime::select('author')->distinct()->pluck('author');

        return view('manager.lead_time_mrs', [
            'mrs' => $mrs,
            'totalMRs' => $totalMRs,
            'avgLeadTimeDays' => $avgLeadTimeDays,
            'avgLeadTimeHours' => $avgLeadTimeHours,
            'avgFirstCommitToMergeDays' => $avgFirstCommitToMergeDays,
            'avgFirstCommitToMergeHours' => $avgFirstCommitToMergeHours,
            'repositories' => $repositories,
            'authors' => $authors,
            'avgTotalMrPerAuthor' => $avgTotalMrPerAuthor,
            'mrCountPerAuthor' => $mrCountPerAuthor,
            'chartData' => $dataChart,
            'latestUpdatedAtGMT7' => $latestUpdatedAtGMT7,
        ]);
    }

    /**
     * Prepare data from Gitlab MR Lead Times for various charts.
     *
     * @return array
     */
    protected function prepareGitlabChartData($mrs, $startDate, $endDate): array
    {
        $chartData = [];

        $mrsWithFirstCommit = $mrs->filter(function ($mr) {
            return !empty($mr->first_commit_at);
        });

        $authorDataGrouped = $mrsWithFirstCommit->groupBy('author');

        $authorLabels = [];
        $authorMrCounts = [];
        $authorAvgLeadTimes = [];

        $authorMrCountsSorted = $authorDataGrouped->map->count()->sortDesc();

        foreach ($authorMrCountsSorted as $author => $count) {
            $authorLabels[] = $author;
            $authorMrCounts[] = $count;

            $avgDays = $authorDataGrouped[$author]->avg('first_commit_to_merge_days') ?? 0;
            $authorAvgLeadTimes[] = number_format($avgDays, 2);
        }

        $chartData['authors'] = [
            'labels' => $authorLabels,
            'mrCounts' => $authorMrCounts,
            'leadTimes' => $authorAvgLeadTimes,
        ];

        $durationInDays = 0;

        if ($startDate && $endDate) {
             if ($startDate->gt($endDate)) {
                 [$startDate, $endDate] = [$endDate, $startDate];
             }
            $durationInDays = $startDate->diffInDays($endDate);
        }

        $mrsGroupedForTrend = new Collection();
        $trendLabels = [];
        $trendDataDays = [];
        $dateFormat = 'Y-m-d';

        if ($durationInDays > 0 && $durationInDays < 30) {
            $mrsGroupedForTrend = $mrs->groupBy(function($mr) {
                return Carbon::parse($mr->mr_merged_at)->format('Y-m-d');
            })->sortKeys();
            $dateFormat = 'Y-m-d'; // Format label harian

        } elseif ($durationInDays >= 30 && $durationInDays < 60) {
            $mrsGroupedForTrend = $mrs->groupBy(function($mr) {
                return Carbon::parse($mr->mr_merged_at)->startOfWeek()->format('Y-m-d');
            })->sortKeys();
             $dateFormat = 'W Y';

        } else {
             $mrsGroupedForTrend = $mrs->groupBy(function($mr) {
                return Carbon::parse($mr->mr_merged_at)->format('Y-m');
            })->sortKeys();
            $dateFormat = 'M Y';
        }

        $periodStartDate = $startDate ? $startDate->copy()->startOfDay() : ($mrs->min('mr_merged_at') ? Carbon::parse($mrs->min('mr_merged_at'))->startOfDay() : Carbon::now()->subMonths(6)->startOfMonth());
        $periodEndDate = $endDate ? $endDate->copy()->endOfDay() : ($mrs->max('mr_merged_at') ? Carbon::parse($mrs->max('mr_merged_at'))->endOfDay() : Carbon::now()->endOfMonth());

        if (!$periodStartDate || !$periodEndDate || $periodStartDate->gt($periodEndDate)) {
            $periodStartDate = Carbon::now()->subYear()->startOfYear();
            $periodEndDate = Carbon::now()->endOfYear();
        }
        
        $period = CarbonPeriod::create($periodStartDate, $periodEndDate);
        $allLabels = [];
        foreach ($period as $date) {
            if ($date->isWeekend() && $dateFormat === 'Y-m-d') {
                continue;
            }

             if ($dateFormat === 'Y-m-d') {
                 $allLabels[$date->format('Y-m-d')] = $date->format($dateFormat);
             } elseif ($dateFormat === 'W Y') {
                 $weekKey = $date->copy()->startOfWeek()->format('Y-m-d');
                 if (!isset($allLabels[$weekKey])) {
                      $allLabels[$weekKey] = $date->format($dateFormat);
                 }
             } elseif ($dateFormat === 'M Y') {
                 $monthKey = $date->copy()->format('Y-m');
                  if (!isset($allLabels[$monthKey])) {
                      $allLabels[$monthKey] = $date->format($dateFormat);
                  }
             }

             if ($date->gt($periodEndDate) && ($dateFormat === 'Y-m-d' || $date->format($dateFormat) !== $periodEndDate->format($dateFormat))) {
                  if ($dateFormat === 'W Y' && $date->copy()->startOfWeek()->gt($periodEndDate->copy()->startOfWeek())) break;
                  if ($dateFormat === 'M Y' && $date->copy()->startOfMonth()->gt($periodEndDate->copy()->startOfMonth())) break;
                  if ($dateFormat === 'Y-m-d') break;
             }
        }

        ksort($allLabels);

        $trendLabels = array_values($allLabels);
        $trendDataDays = [];

        foreach ($allLabels as $key => $label) {
            if ($mrsGroupedForTrend->has($key)) {
                $avgLeadTimePeriod = $mrsGroupedForTrend[$key]->avg('time_to_merge_days') ?? 0;
                $trendDataDays[] = number_format($avgLeadTimePeriod, 2);
            } else {
                $trendDataDays[] = 0;
            }
        }

         $chartData['trend'] = [
            'labels' => $trendLabels,
            'datasets' => [
                [
                    'label' => 'Average Lead Time (Days)',
                    'data' => $trendDataDays,
                ]
            ],
        ];

        $mrsGroupedByRepo = $mrs->groupBy('repository');

        $projectLabels = [];
        $projectDataDays = [];

        foreach ($mrsGroupedByRepo as $repo => $group) {
            $projectLabels[] = basename($repo);
            $avgLeadTimeRepo = $group->avg('time_to_merge_days') ?? 0;
            $projectDataDays[] = number_format($avgLeadTimeRepo, 2);
        }

        $chartData['projects'] = [
             'labels' => $projectLabels,
             'datasets' => [
                [
                    'label' => 'Average Lead Time (Days) by Project',
                    'data' => $projectDataDays,
                ]
             ],
        ];

        return $chartData;
    }
}
