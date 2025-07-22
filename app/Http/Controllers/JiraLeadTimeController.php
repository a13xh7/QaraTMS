<?php

namespace App\Http\Controllers;

use App\Models\JiraLeadTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use DateTime;


class JiraLeadTimeController extends Controller
{
    /**
     * Display the JIRA Lead Time page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        Log::info('Jira Lead Time page accessed' . json_encode($request->all()));
        $latestUpdatedAt = JiraLeadTime::max('updated_at');
        $latestUpdatedAtGMT7 = Carbon::parse($latestUpdatedAt)->timezone('Asia/Jakarta');

        $issueTypes = array_unique(JiraLeadTime::pluck('issue_type')->toArray());
        $projects = array_unique(JiraLeadTime::pluck('project_key')->toArray());

        if (!$request->filled('date_range') && !$request->filled('start_date')) {
            $endDateFormat = (new DateTime())->format(DateTime::ATOM);
            $startDateFormat = new DateTime();
            $startDateFormat->modify('-1 month')->format(DateTime::ATOM);

            $start = Carbon::parse($startDateFormat);
            $end = Carbon::parse($endDateFormat);
        } else {
            Log::info('Jira Lead Time page accessed with apply options');
            $startDate = $request->input('start_date');
            $startDate = new DateTime($startDate);
            $startDateFormat = $startDate->format(DateTime::ATOM);

            $endDate = $request->input('end_date');
            $endDate = new DateTime($endDate);
            $endDateFormat = $endDate->format(DateTime::ATOM);


            $start = Carbon::parse($request->input('start_date'));
            $end = Carbon::parse($request->input('end_date'));
        }

        $query = JiraLeadTime::where('issue_completed_date', '>=', $startDateFormat)
                ->where('issue_completed_date', '<=', $endDateFormat);
                
        if ($request->filled('issue_type') && $request->input('issue_type') !== 'all') {
            $query = $query->where('issue_type', $request->input('issue_type'));
        } 

        if ($request->filled('project_name') && $request->input('project_name') !== 'all') {
            $query = $query->where('project_key', $request->input('project_name'));

        }
        $responses = $query->orderByDesc('issue_completed_date')->get();
        
        $currentIssueTypes = array_unique($responses->pluck('issue_type')->toArray());
        $currentProjects = array_unique($responses->pluck('project_key')->toArray());
        $totalIssue = $responses->count();

        $avgLeadTime = number_format($responses->avg('lead_time') ?? 0, 2);
        $completeRate = $totalIssue > 0 ? number_format($responses->where('issue_status', 'Done')->count() / $totalIssue * 100, 2) : '0.00';
        $blockedRate = $totalIssue > 0 ? number_format($responses->where('issue_status', 'Blocked')->count() / $totalIssue * 100, 2) : '0.00';

        $diffInDays = $start->diffInDays($end);

        // $groupFormat = 'Y-m-d';
        if ($diffInDays <= 31) {
            // Group per hari
            $groupFormat = 'Y-m-d';
        } else {
            // Group per bulan
            $groupFormat = 'Y-m';
        }

        $trendDataAndLabel = $responses
            ->groupBy(function($item) use ($groupFormat) {
                return Carbon::parse($item->issue_completed_date)->format($groupFormat);
            })
            ->map(function($group) {
                return round($group->avg('lead_time'), 2);
            })
            ->reverse();;

        $timeData = $responses
            ->groupBy(function($item) use ($groupFormat) {
                return Carbon::parse($item->issue_completed_date)->format($groupFormat);
            })
            ->map(function($group) {
                return $group->groupBy('issue_type')->map(function($issues) {
                    return round($issues->avg('lead_time'), 2);
                });
            })
            ->reverse();;

        $projectTimeData = $responses
            ->groupBy(function($item) use ($groupFormat) {
                return Carbon::parse($item->issue_completed_date)->format($groupFormat);
            })
            ->map(function($group) {
                return $group->groupBy('project_key')->map(function($issues) {
                    return round($issues->avg('lead_time'), 2);
                });
            })
            ->reverse();;

        $labels = $trendDataAndLabel->keys()->toArray();
        $trendData = $trendDataAndLabel->values()->toArray();

        $datasets = [];
        foreach ($currentIssueTypes as $type) {
            $datasets[] = [
                'label' => $type,
                'data' => array_map(function($monthData) use ($type) {
                    return $monthData[$type] ?? null;
                }, $timeData->toArray()),
                // Optionally add 'borderColor', 'backgroundColor', etc.
            ];
        }
        
        $projectDatasets = [];
        foreach ($currentProjects as $project) {
            $projectDatasets[] = [
                'label' => $project,
                'data' => array_map(function($monthData) use ($project) {
                    return $monthData[$project] ?? null;
                }, $projectTimeData->toArray()),
                // Optionally add color, etc.
            ];
        }

        // create Lead Time Trend by Issue Type  data for graph
        return view('manager.jira_lead_time')
            ->with('responses', $responses)
            ->with('totalIssue', $totalIssue)
            ->with('issueTypes', $issueTypes)
            ->with('projects', $projects)
            ->with('avgLeadTime', $avgLeadTime)
            ->with('completeRate', $completeRate)
            ->with('blockedRate', $blockedRate)
            ->with('labels', $labels)
            ->with('datasets', $datasets)
            ->with('trendData', $trendData)
            ->with('projectDatasets', $projectDatasets)
            ->with('latestUpdatedAtGMT7', $latestUpdatedAtGMT7);
    }
}
