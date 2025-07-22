<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\JiraLeadTime;
class SyncJiraLeadTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jira:leadtime';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync lead time from Jira';

    private string|null $username;
    private string|null $token;
    private $baseUrl = 'https://admin.atlassian.net';
    private $pathSearchIssue = '/rest/api/3/search';
    private $pathIssue = '/rest/api/3/search';
    private $listProjectKey = ['AL', 'AO', 'PC', 'MM', 'XTEAM', 'PW', 'SKUI', 'SOD'];

    public function __construct()
    {
        parent::__construct();
        $this->username = env('CONFLUENCE_USERNAME');
        $this->token = env('CONFLUENCE_API_TOKEN');
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Jira Lead Time sync...');
        foreach ($this->listProjectKey as $projectKey) {
            try {
                $endDate = (new \DateTime('now', new \DateTimeZone('Asia/Jakarta')))->format('Y-m-d');
                $startDate = \Carbon\Carbon::parse($endDate)->subDays(30)->toDateString();
                $totalIssue = $this->getTotalIssue($projectKey, $startDate, $endDate);
                $this->info('Total issue: ' . $totalIssue);

                if ($totalIssue < 100) {
                    $this->info('Total issue is less than 100');
                    $issues = $this->getIssues($projectKey, $startDate, $endDate, 0);
                    foreach ($issues as $issue) {
                        $this->processIssue($issue);
                    }
                } else {
                    $loop = ceil($totalIssue / 100);
                    $startAt = 0;
                    for ($i = 0; $i < $loop; $i++) {
                        $this->info('Sync project: ' . $i . ' of ' . $loop . ' - ' . $startAt . ' to ' . ($startAt + 100));
                        $issues = $this->getIssues($projectKey, $startDate, $endDate, $startAt);
                        foreach ($issues as $issue) {
                           $this->processIssue($issue);
                        }
                        $startAt += 100;
                    }
                }
            } catch (\Exception $e) {
                Log::error('Jira Sync Error: ' . $e->getMessage());
                $this->error('Jira Sync Error: ' . $e->getMessage());
            }
        }
    }

    private function getTotalIssue($projectKey, $startDate, $endDate) {
        $jql = "project in ({$projectKey}) AND created >= \"{$startDate}\" AND created <= \"{$endDate}\"";

        $response = Http::withBasicAuth($this->username, $this->token)
            ->accept('application/json')
            ->get("{$this->baseUrl}{$this->pathIssue}", [
                'jql' => $jql,
                'startAt' => 0,
                'maxResults' => 0
            ]);
        $data = $response->json();
        return $data['total'];
    }
    private function getIssues($projectKey, $startDate, $endDate, $startAt)
    {
        $jql = "project in ({$projectKey}) AND updated >= \"{$startDate}\" AND updated <= \"{$endDate}\"";

        $response = Http::withBasicAuth($this->username, $this->token)
            ->accept('application/json')
            ->get("{$this->baseUrl}{$this->pathSearchIssue}", [
                'jql' => $jql,
                'startAt' => $startAt,
                'maxResults' => 100,
                'fields' => '*all',
                'expand' => 'changelog'
            ]);
        $data = $response->json();
        return $data['issues'];
    }

    private function processIssue(array $issue)
    {
        $created = new \DateTime($issue['fields']['created']);
        $started = isset($issue['fields']['customfield_10015']) ? new \DateTime($issue['fields']['customfield_10015']) : null;
        $completed = isset($issue['fields']['resolutiondate']) ? new \DateTime($issue['fields']['resolutiondate']) : null;

        $sprintField = $issue['fields']['customfield_10020'] ?? null;
        $sprint = ($sprintField && count($sprintField) > 0 && isset($sprintField[0]['name'])) ? $sprintField[0]['name'] : 'No Sprint';

        $currentStatus = [
            'status' => $issue['fields']['status']['name'],
            'startDate' => $created
        ];

        $statusChanges = [];

        if (isset($issue['changelog']['histories'])) {
            $histories = $issue['changelog']['histories'];
            usort($histories, function ($a, $b) {
                return strtotime($a['created']) <=> strtotime($b['created']);
            });

            foreach ($histories as $history) {
                foreach ($history['items'] as $item) {
                    if ($item['field'] === 'status') {
                        $changeDate = new \DateTime($history['created']);
                        $statusChanges[] = [
                            'from' => $item['fromString'] ?? null,
                            'to' => $item['toString'] ?? null,
                            'date' => $changeDate
                        ];
                    }
                }
            }
        }

        $statusNames = [];
        if (isset($issue['changelog']['histories'])) {
            foreach ($issue['changelog']['histories'] as $history) {
                foreach ($history['items'] as $item) {
                    if ($item['field'] === 'status') {
                        if (isset($item['fromString'])) $statusNames[$item['fromString']] = true;
                        if (isset($item['toString'])) $statusNames[$item['toString']] = true;
                    }
                }
            }
        }

        $times = [
            'toDo' => $this->getTimeInStatusDays($statusChanges, 'To Do'),
            'inProgress' => $this->getTimeInStatusDays($statusChanges, 'In Progress'),
            'codeReview' => $this->getTimeInStatusDays($statusChanges, 'Code Review'),
            'waitingForTest' => $this->getTimeInStatusDays($statusChanges, 'Waiting for test'),
            'testing' => $this->getTimeInStatusDays($statusChanges, 'Testing'),
            'waitingForAcceptance' => $this->getTimeInStatusDays($statusChanges, 'Waiting for acceptance'),
            'waitingForDeployment' => $this->getTimeInStatusDays($statusChanges, 'Waiting for deployment'),
            'done' => $this->getTimeInStatusDays($statusChanges, 'Done')
        ];

        //POST DATA TO DATABASE
        $result = [
            'project' => $issue['fields']['project']['key'] ?? '',
            'sprint' => $sprint,
            'key' => $issue['key'] ?? '',
            'summary' => $issue['fields']['summary'] ?? '',
            'type' => $issue['fields']['issuetype']['name'] ?? '',
            'status' => $issue['fields']['status']['name'] ?? '',
            'created' => $created ? $created->format('c') : null,
            'started' => $started ? $started->format('c') : null,
            'completed' => $completed ? $completed->format('c') : null,
            'leadTime' => $completed ? ceil(($completed->getTimestamp() - $created->getTimestamp()) / (60 * 60 * 24)) : '',
            'cycleTime' => $this->calculateCycleTime($started, $completed),
            'toDo' => $times['toDo'],
            'inProgress' => $times['inProgress'],
            'codeReview' => $times['codeReview'],
            'waitingForTest' => $times['waitingForTest'],
            'testing' => $times['testing'],
            'waitingForAcceptance' => $times['waitingForAcceptance'],
            'waitingForDeployment' => $times['waitingForDeployment'],
            'done' => $times['done'],
            'assignee' => $issue['fields']['assignee']['displayName'] ?? 'Unassigned'
        ];
        
        $jiraLeadTime = JiraLeadTime::where('jira_key', $result['key'])->first();
        if (!$jiraLeadTime) {
            $this->info('Jira lead time not found for key: ' . $result['key']);
            $this->postDataToDatabase($result);
        } else {
            if ($result['status'] !== $jiraLeadTime->issue_status) {
                $this->info('Jira lead time found for key: and status is change ' . $result['key']);
                $this->updateDataToDatabase($result, $jiraLeadTime->id);  
            } else {
                $this->info('Jira lead time found for key: but status is not change ' . $result['key']);
            } 
        }
        return $result;
    }

    /**
     * Calculate time in status in days (rounded up)
     */
    private function getTimeInStatusDays(array $statusChanges, string $targetStatus)
    {
        $totalDays = 0;
        $currentPeriodStart = null;

        // Helper function to get DateTime from different formats
        $getDateTime = function($dateValue) {
            if ($dateValue instanceof \DateTime) {
                return $dateValue;
            } elseif (is_array($dateValue) && isset($dateValue['date'])) {
                return new \DateTime($dateValue['date']);
            } elseif (is_string($dateValue)) {
                return new \DateTime($dateValue);
            } else {
                throw new \Exception("Unexpected date format in status changes");
            }
        };

        // Sort status changes by date
        usort($statusChanges, function ($a, $b) use ($getDateTime) {
            $dateA = $getDateTime($a['date']);
            $dateB = $getDateTime($b['date']);
            return $dateA <=> $dateB;
        });

        // Process each status change
        foreach ($statusChanges as $index => $change) {
            $changeDate = $getDateTime($change['date']);
            
            // If we're entering the target status
            if ($change['to'] === $targetStatus) {
                $currentPeriodStart = $changeDate;
            }
            
            // If we're leaving the target status
            if ($change['from'] === $targetStatus && $currentPeriodStart) {
                // Calculate days between start and end of this status
                $daysDiff = ($changeDate->getTimestamp() - $currentPeriodStart->getTimestamp()) / (60 * 60 * 24);
                $totalDays += ceil($daysDiff); // Round up to whole days
                $currentPeriodStart = null;
            }
        }

        // If still in the target status (last status with no end date)
        $lastChange = end($statusChanges);
        reset($statusChanges);
        
        if ($currentPeriodStart && $lastChange['to'] === $targetStatus) {
            // Find the next Sunday from the current period start
            $dayOfWeek = (int)$currentPeriodStart->format('w');
            $daysUntilSunday = $dayOfWeek === 0 ? 7 : 7 - $dayOfWeek;
            $endOfWeek = (clone $currentPeriodStart)->modify("+{$daysUntilSunday} days");
            
            $daysDiff = ($endOfWeek->getTimestamp() - $currentPeriodStart->getTimestamp()) / (60 * 60 * 24);
            $totalDays += ceil($daysDiff);
        }

        return $totalDays;
    }

    /**
     * Calculate cycle time in days (rounded up)
     */
    private function calculateCycleTime($started, $completed)
    {
        if (!$started || !$completed) {
            return 0;
        }

        $daysDiff = ($completed->getTimestamp() - $started->getTimestamp()) / (60 * 60 * 24);
        return ceil($daysDiff);
    }

    private function updateDataToDatabase($data, $id)
    {
        $this->info('Updating data to database: ' . json_encode($data));
        $jiraLeadTime = JiraLeadTime::findOrFail($id);

        $jiraLeadTime->project_key = $data['project'];
        $jiraLeadTime->sprint = $data['sprint'];
        $jiraLeadTime->jira_key = $data['key'];
        $jiraLeadTime->summary = $data['summary'];
        $jiraLeadTime->issue_type = $data['type'];
        $jiraLeadTime->issue_status = $data['status'];
        $jiraLeadTime->issue_created_date = $data['created'];
        $jiraLeadTime->issue_started_date = $data['started'];
        $jiraLeadTime->issue_completed_date = $data['completed'];
        $jiraLeadTime->lead_time = $data['leadTime'];
        $jiraLeadTime->cycle_time = $data['cycleTime'];
        $jiraLeadTime->todo_time = $data['toDo'];
        $jiraLeadTime->in_progress_time = $data['inProgress'];
        $jiraLeadTime->code_review_time = $data['codeReview'];
        $jiraLeadTime->waiting_for_test_time = $data['waitingForTest'];
        $jiraLeadTime->testing_time = $data['testing'];
        $jiraLeadTime->waiting_for_acceptance_time = $data['waitingForAcceptance'];
        $jiraLeadTime->waiting_for_deployment_time = $data['waitingForDeployment'];
        $jiraLeadTime->done_time = $data['done'];
        $jiraLeadTime->assignee = $data['assignee'];
        
        $jiraLeadTime->save();
    }

    private function postDataToDatabase($data)
    {
        $this->info('Posting data to database: ' . json_encode($data));
        $jiraLeadTime = new JiraLeadTime();
        $jiraLeadTime->project_key = $data['project'];
        $jiraLeadTime->sprint = $data['sprint'];
        $jiraLeadTime->jira_key = $data['key'];
        $jiraLeadTime->summary = $data['summary'];
        $jiraLeadTime->issue_type = $data['type'];
        $jiraLeadTime->issue_status = $data['status'];
        $jiraLeadTime->issue_created_date = $data['created'];
        $jiraLeadTime->issue_started_date = $data['started'];
        $jiraLeadTime->issue_completed_date = $data['completed'];
        $jiraLeadTime->lead_time = $data['leadTime'];
        $jiraLeadTime->cycle_time = $data['cycleTime'];
        $jiraLeadTime->todo_time = $data['toDo'];
        $jiraLeadTime->in_progress_time = $data['inProgress'];
        $jiraLeadTime->code_review_time = $data['codeReview'];
        $jiraLeadTime->waiting_for_test_time = $data['waitingForTest'];
        $jiraLeadTime->testing_time = $data['testing'];
        $jiraLeadTime->waiting_for_acceptance_time = $data['waitingForAcceptance'];
        $jiraLeadTime->waiting_for_deployment_time = $data['waitingForDeployment'];
        $jiraLeadTime->done_time = $data['done'];
        $jiraLeadTime->assignee = $data['assignee'];
        
        $jiraLeadTime->save();
    }
}
