<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\MonthlyContribution;

class SyncGitlabMRContributor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gitlab:contributor {mode=monthly : Run in monthly mode or full mode (from beginning of 2023)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Gitlab data for mr contributor';

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
        $isMonthly = ($mode === 'monthly');
        
        $listOfYears = range(2023, Carbon::now()->year);
        $listOfMonth = [1,2,3,4,5,6,7,8,9,10,11,12];
        try {
            if ($isMonthly) {
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                
                $this->info("Running in MONTHLY mode: Fetching GitLab MRs contributor for current month");
                $this->getContribution($startDate, $endDate);
            } else {
                $currentYear = Carbon::now()->year;
                $currentMonth = Carbon::now()->month;
                
                foreach ($listOfYears as $year) {
                    foreach ($listOfMonth as $month) {
                        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
                        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

                        $this->getContribution($startDate, $endDate);
                        if ($year == $currentYear && $month == $currentMonth) {
                            $this->info('Stop');
                            $this->info($startDate . '   ' . $endDate);
                            break;
                        }
                    }
                }
                
                $this->info("Running in FULL mode: Fetching GitLab MRs Contributor from the beginning of 2023");
            }

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
     * Get contribution
     */
    protected function getContribution(Carbon $startDate, Carbon $endDate)
    {
        $query = <<<'GRAPHQL'
        query getGroupContributions($groupName: ID!, $fromDate: ISO8601Date!, $toDate: ISO8601Date!) {
            group(fullPath: $groupName) {
            contributions(from: $fromDate, to: $toDate) {
                edges {
                node {
                    user {
                    username
                    name
                    }
                    mergeRequestsCreated
                    mergeRequestsApproved
                    repoPushed
                    totalEvents
                }
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
                'groupName' => $this->gitlabGroup,
                'fromDate' => $startDate->toIso8601String(),
                'toDate' => $endDate->toIso8601String()
            ]
        ]);

        $listBot = array(
            'data-access-token-20250106',
            'jira-deployment-notifier',
            'toggles-history-daily-updater',
            'admin bot',
            'infra-ssl-letsencrypt-00',

        );
        $this->info("Get Data From : " . $startDate . " until " . $endDate);
        $body = json_decode($response->body(), true);
        $contributorData = $body['data']['group']['contributions']['edges'];

        foreach ($contributorData as $contributor) {
            $name = $contributor['node']['user']['name'];
            
            if (in_array($name, $listBot)) {
                $this->info("Skipping bot: {$name}");
                continue;
            }

            $contributorRecord = [
                'name' => $contributor['node']['user']['name'],
                'username' => $contributor['node']['user']['username'],
                'year' => Carbon::createFromFormat('Y-m-d H:i:s', $startDate)->year,
                'month' => Carbon::createFromFormat('Y-m-d H:i:s', $startDate)->month,
                'month_name' => Carbon::createFromFormat('Y-m-d H:i:s', $startDate)->englishMonth,
                'squad' => null,
                'mr_created' => $contributor['node']['mergeRequestsCreated'],
                'mr_approved' => $contributor['node']['mergeRequestsApproved'],
                'repo_pushes' => $contributor['node']['repoPushed'],
                'total_events' => $contributor['node']['totalEvents'],
            ];
            $this->saveGitlabMrContributor($contributorRecord);
        }
        if ($response->failed()) {
            $this->error("Failed to fetch mr contributor: " . $response->status());
            Log::error('GitLab API Error: ' . $response->body());
            return [];
        }

        return 0;
    }

    /**
     * Store or update a Gitlab Contributor.
     *
     * @param array
     * @return void
     */
    protected function saveGitlabMrContributor(array $userContributor): void
    {
        MonthlyContribution::updateOrCreate(
            [
                'name' => $userContributor['name'],
                'year' => $userContributor['year'],
                'month' => $userContributor['month']
            ],
            
            $userContributor
        );
    }

}
