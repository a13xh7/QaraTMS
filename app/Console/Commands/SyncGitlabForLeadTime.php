<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\GitlabMrLeadTime;

class SyncGitlabForLeadTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gitlab:leadtime {mode=daily : Run in daily mode (last 30 days) or full mode (from beginning of 2025)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Gitlab data for mr lead time calculation';

    private string|null $gitlabUrl;
    private string|null $gitlabGraphqlUrl;
    private string|null $gitlabGroup;
    private string|null $token;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->gitlabUrl = env('GITLAB_API_URL');
        $this->gitlabGraphqlUrl = env('GITLAB_GRAPHQL_URL');
        $this->gitlabGroup = env('GITLAB_GROUP');
        $this->token = env('GITLAB_TOKEN');
    }

    /**
     * Execute the console command (method yang dipanggil oleh cron).
     *
     * @return int
     */
    public function handle()
    {
       
        $mode = strtolower($this->argument('mode'));
        $this->info("Mode argument value: " . $mode);
        $isDaily = ($mode === 'daily');
        
        if ($isDaily) {
            $startDate = Carbon::now()->subDays(7)->startOfDay(); // Last 7 days
            $endDate = Carbon::now()->endOfDay();
            $this->info("Running in DAILY mode: Fetching GitLab MRs for the last 7 days");
        } else {
            $startDate = Carbon::createFromDate(2025, 1, 1)->startOfDay(); // From beginning of 2025
            $endDate = Carbon::now()->endOfDay();
            $this->info("Running in FULL mode: Fetching GitLab MRs from the beginning of 2025");
        }
        $period = CarbonPeriod::create($startDate, $endDate);

        try {
            $groupPaths = $this->getSubgroups();
            
            // Exit if no groups were found
            if (empty($groupPaths)) {
                $this->error("No groups found. Check GitLab token and group name.");
                return 1;
            }

            $this->info("Found " . count($groupPaths) . " subgroups");

            $allMergedMRs = $this->fetchMRsFromAllGroups($groupPaths, $startDate, $endDate);

            // $this->info("All merged MRs: " . json_encode($allMergedMRs));

            $this->processMRs($allMergedMRs);

            $this->info("Successfully processed " . count($allMergedMRs) . " merge requests");

            return 0;
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error("File: " . $e->getFile() . " Line: " . $e->getLine());
            \Log::error('GitLab Sync Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }

    /**
     * Get all subgroups of the main group
     *
     * @return array Array of group paths
     */
    protected function getSubgroups(): array
    {
        $query = <<<'GRAPHQL'
        query getSubgroups($fullPath: ID!) {
          group(fullPath: $fullPath) {
            descendantGroups {
              nodes {
                fullPath
              }
            }
          }
        }
        GRAPHQL;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$this->token}"
        ])->post($this->gitlabGraphqlUrl, [
            'query' => $query,
            'variables' => [
                'fullPath' => $this->gitlabGroup
            ]
        ]);

        if ($response->failed()) {
            $this->error("Failed to fetch subgroups: " . $response->status());
            Log::error('GitLab API Error: ' . $response->body());
            return [];
        }

        $result = $response->json();
        $groupPaths = [$this->gitlabGroup]; // Start with the main group

        if (!empty($result['data']['group']['descendantGroups']['nodes'])) {
            foreach ($result['data']['group']['descendantGroups']['nodes'] as $group) {
                $groupPaths[] = $group['fullPath'];
            }
        }

        return $groupPaths;
    }

    /**
     * Fetch MRs from all groups
     *
     * @param array $groupPaths Array of group paths
     * @param Carbon $startDate Start date for MR filtering
     * @param Carbon $endDate End date for MR filtering
     * @return array Array of merged MRs
     */
    protected function fetchMRsFromAllGroups(array $groupPaths, Carbon $startDate, Carbon $endDate): array
    {
        $allMergedMRs = [];

        foreach ($groupPaths as $groupPath) {
            $this->info("Fetching MRs from group: {$groupPath}");
            
            $hasNextPage = true;
            $endCursor = null;

            while ($hasNextPage) {
                $mergedMRs = $this->fetchMRsFromGroup($groupPath, $startDate, $endDate, $endCursor);
                if (!isset($mergedMRs['mrs'])) {
                    break;
                }

                $allMergedMRs = array_merge($allMergedMRs, $mergedMRs['mrs']);
                $hasNextPage = $mergedMRs['hasNextPage'];
                $endCursor = $mergedMRs['endCursor'];

                if ($hasNextPage) {
                    // Sleep to avoid rate limiting
                    sleep(1);
                }
            }
        }

        return $allMergedMRs;
    }

    /**
     * Fetch MRs from a single group with pagination
     *
     * @param string $groupPath Group path
     * @param Carbon $startDate Start date for MR filtering
     * @param Carbon $endDate End date for MR filtering
     * @param string|null $endCursor Pagination cursor
     * @return array Array with MRs and pagination info
     */
    protected function fetchMRsFromGroup(string $groupPath, Carbon $startDate, Carbon $endDate, ?string $endCursor = null): array
    {
        $query = <<<'GRAPHQL'
        query getGroupMergedMRs($fullPath: ID!, $startDate: Time!, $endDate: Time!, $after: String) {
          group(fullPath: $fullPath) {
            mergeRequests(state: merged, first: 100, after: $after, mergedAfter: $startDate, mergedBefore: $endDate) {
              pageInfo {
                hasNextPage
                endCursor
              }
              nodes {
                iid
                title
                author {
                  name
                }
                createdAt
                mergedAt
                labels {
                  nodes {
                    title
                  }
                }
                webUrl
                targetProject {
                  id
                  fullPath
                  name
                  group {
                    fullPath
                  }
                }
                commits(first: 100) {
                  nodes {
                    committedDate
                  }
                }
              }
            }
          }
        }
        GRAPHQL;

        $variables = [
            'fullPath' => $groupPath,
            'startDate' => $startDate->toIso8601String(),
            'endDate' => $endDate->toIso8601String(),
            'after' => $endCursor
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$this->token}"
        ])->post($this->gitlabGraphqlUrl, [
            'query' => $query,
            'variables' => $variables
        ]);

        if ($response->failed()) {
            $this->error("Failed to fetch MRs from {$groupPath}: " . $response->status());
            return ['mrs' => [], 'hasNextPage' => false, 'endCursor' => null];
        }

        $result = $response->json();

        if (isset($result['errors'])) {
            $this->error("GraphQL errors for {$groupPath}: " . json_encode($result['errors']));
            Log::error('GitLab GraphQL Error', $result['errors']);
            return ['mrs' => [], 'hasNextPage' => false, 'endCursor' => null];
        }

        if (!isset($result['data']['group']['mergeRequests']['nodes'])) {
            $this->info("No MRs found in {$groupPath}");
            return ['mrs' => [], 'hasNextPage' => false, 'endCursor' => null];
        }

        $mrs = [];
        foreach ($result['data']['group']['mergeRequests']['nodes'] as $mr) {
            // Find the earliest commit date
            $commitDates = array_map(function($commit) {
                return Carbon::parse($commit['committedDate']);
            }, $mr['commits']['nodes']);

            $earliestCommitDate = empty($commitDates) ? null : min($commitDates);

            $mrs[] = [
                'iid' => $mr['iid'],
                'title' => $mr['title'],
                'author_name' => $mr['author']['name'],
                'created_at' => $mr['createdAt'],
                'merged_at' => $mr['mergedAt'],
                'labels' => array_map(function($label) {
                    return $label['title'];
                }, $mr['labels']['nodes']),
                'web_url' => $mr['webUrl'],
                'target_project_path' => $mr['targetProject']['fullPath'],
                'first_commit_date' => $earliestCommitDate ? $earliestCommitDate->toIso8601String() : null
            ];
        }

        $pageInfo = $result['data']['group']['mergeRequests']['pageInfo'];

        return [
            'mrs' => $mrs,
            'hasNextPage' => $pageInfo['hasNextPage'],
            'endCursor' => $pageInfo['endCursor']
        ];
    }

    /**
     * Process and store the MRs data
     *
     * @param array $mergedMRs Array of MR data
     */
    protected function processMRs(array $mergedMRs): void
    {
        if (empty($mergedMRs)) {
            $this->info("No merge requests to process");
            return;
        }

        $newCount = 0;
        $updatedCount = 0;

        foreach ($mergedMRs as $mr) {
            $createdAt = Carbon::parse($mr['created_at']);
            $mergedAt = Carbon::parse($mr['merged_at']);
            $firstCommitDate = !empty($mr['first_commit_date']) ? Carbon::parse($mr['first_commit_date']) : null;

            $businessDays = $this->calculateBusinessDays($createdAt, $mergedAt);
            $hoursToMerge = $this->calculateHoursToMerge($createdAt, $mergedAt);
            
            $firstCommitToMergeDays = $firstCommitDate ? 
                $this->calculateBusinessDays($firstCommitDate, $mergedAt) : null;
            $firstCommitToMergeHours = $firstCommitDate ? 
                $this->calculateHoursToMerge($firstCommitDate, $mergedAt) : null;

            // Clean project path
            $projectPath = str_replace("{$this->gitlabGroup}/", '', $mr['target_project_path']);

            $mrRecord = [
                'mr_id' => $mr['iid'],
                'title' => $mr['title'],
                'author' => $mr['author_name'],
                'mr_created_at' => $createdAt,
                'mr_merged_at' => $mergedAt,
                'first_commit_at' => $firstCommitDate,
                'time_to_merge_days' => $businessDays,
                'time_to_merge_hours' => $hoursToMerge,
                'labels' => implode(', ', $mr['labels']),
                'url' => $mr['web_url'],
                'repository' => $projectPath,
                'first_commit_to_merge_days' => $firstCommitToMergeDays,
                'first_commit_to_merge_hours' => $firstCommitToMergeHours,
            ];

            $this->saveGitlabMrLeadTime($mrRecord);
        }

        $this->info("Processed {$newCount} new and {$updatedCount} updated MRs");
    }

    /**
     * Store or update a Gitlab MR Lead Time record using the Eloquent model.
     *
     * @param array $mrRecord
     * @return void
     */
    protected function saveGitlabMrLeadTime(array $mrRecord): void
    {
        GitlabMrLeadTime::updateOrCreate(
            [
                'mr_id' => $mrRecord['mr_id'],
                'repository' => $mrRecord['repository']
            ],
            
            $mrRecord
        );
    }

    /**
     * Calculate business days between two dates (excluding weekends)
     *
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return int Number of business days
     */
    protected function calculateBusinessDays(Carbon $startDate, Carbon $endDate): int
    {
        $days = 0;
        $period = CarbonPeriod::create($startDate, '1 day', $endDate);

        foreach ($period as $date) {
            if (!$date->isWeekend()) {
                $days++;
            }
        }

        return $days;
    }

    /**
     * Calculate total hours between two dates
     *
     * @param Carbon $startDate Start date
     * @param Carbon $endDate End date
     * @return float Number of hours
     */
    protected function calculateHoursToMerge(Carbon $startDate, Carbon $endDate): float
    {
        return round($startDate->diffInSeconds($endDate) / 3600, 2);
    }
}
