<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class MonthlyContributionService
{
    protected $gitlabToken;
    protected $gitlabGraphQLUrl;
    protected $groupName;

    public function __construct()
    {
        $this->gitlabToken = env('GITLAB_TOKEN');
        if (!$this->gitlabToken) {
            throw new \RuntimeException('GitLab token is not configured.');
        }
        
        $this->gitlabGraphQLUrl = env('GITLAB_GRAPHQL_URL', 'https://gitlab.com/api/graphql');
        $this->groupName = env('GITLAB_GROUP', 'admin');
    }

    /**
     * Get squad for a user from config
     */
    public function getSquadForUser($name)
    {
        return config('contributors.' . $name, 'Unknown');
    }

    /**
     * Fetch monthly contributions from GitLab with caching
     */
    public function fetchMonthlyContributions($year, $month)
    {
        $cacheKey = "gitlab_contributions_{$year}_{$month}";
        
        return Cache::remember($cacheKey, now()->addHours(1), function () use ($year, $month) {
            $monthFormatted = str_pad($month, 2, '0', STR_PAD_LEFT);
            $fromDate = "{$year}-{$monthFormatted}-01";
            $lastDay = date('t', strtotime($fromDate));
            $toDate = "{$year}-{$monthFormatted}-{$lastDay}";
            $monthName = date('F', mktime(0, 0, 0, $month, 1, $year));
            
            Log::info('Fetching GitLab contributions', [
                'year' => $year,
                'month' => $month,
                'fromDate' => $fromDate,
                'toDate' => $toDate,
                'groupName' => $this->groupName,
                'graphQLUrl' => $this->gitlabGraphQLUrl
            ]);
            
            $query = <<<GRAPHQL
query getGroupContributions(\$groupName: ID!, \$fromDate: ISO8601Date!, \$toDate: ISO8601Date!) {
  group(fullPath: \$groupName) {
    contributions(from: \$fromDate, to: \$toDate) {
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

            $variables = [
                'groupName' => $this->groupName,
                'fromDate' => $fromDate,
                'toDate' => $toDate
            ];

            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => "Bearer {$this->gitlabToken}"
                ])->post($this->gitlabGraphQLUrl, [
                    'query' => $query,
                    'variables' => $variables
                ]);

                if (!$response->successful()) {
                    Log::error('GitLab GraphQL Error', [
                        'status' => $response->status(),
                        'response' => $response->body()
                    ]);
                    return [];
                }

                $data = $response->json();
                
                if (isset($data['errors'])) {
                    Log::error('GitLab GraphQL returned errors', [
                        'errors' => $data['errors']
                    ]);
                    return [];
                }
                
                if (!isset($data['data']) || !isset($data['data']['group']) || !isset($data['data']['group']['contributions'])) {
                    Log::error('Unexpected GitLab GraphQL response structure', [
                        'data' => $data
                    ]);
                    return [];
                }
                
                if (empty($data['data']['group']['contributions']['edges'])) {
                    Log::warning('No contribution data found', [
                        'year' => $year,
                        'month' => $month
                    ]);
                    return [];
                }

                $result = [];
                foreach ($data['data']['group']['contributions']['edges'] as $edge) {
                    $node = $edge['node'];
                    $user = $node['user'];
                    
                    $result[] = [
                        'year' => (int)$year,
                        'month' => (int)$month,
                        'monthName' => $monthName,
                        'username' => $user['username'] ?? '',
                        'name' => $user['name'] ?? $user['username'],
                        'squad' => $this->getSquadForUser($user['name'] ?? $user['username']),
                        'mrCreated' => $node['mergeRequestsCreated'],
                        'mrApproved' => $node['mergeRequestsApproved'],
                        'repoPushes' => $node['repoPushed'],
                        'totalEvents' => $node['totalEvents']
                    ];
                }
                
                return $result;
            } catch (\Exception $e) {
                Log::error('Exception while fetching GitLab contributions', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return [];
            }
        });
    }

    /**
     * Generate test data for development environment
     */
    public function generateTestData($year, $month)
    {
        if (!app()->environment('local')) {
            return [];
        }

        $data = [];
        $contributors = config('contributors');
        
        if ($month !== 'all') {
            foreach ($contributors as $name => $squad) {
                $data[] = [
                    'year' => $year,
                    'month' => $month,
                    'name' => $name,
                    'squad' => $squad,
                    'mrCreated' => rand(10, 100),
                    'mrApproved' => rand(5, 50),
                    'repoPushes' => rand(30, 150),
                    'totalEvents' => rand(50, 300)
                ];
            }
        } else {
            for ($m = 1; $m <= 12; $m++) {
                foreach ($contributors as $name => $squad) {
                    $data[] = [
                        'year' => $year,
                        'month' => $m,
                        'name' => $name,
                        'squad' => $squad,
                        'mrCreated' => rand(10, 100),
                        'mrApproved' => rand(5, 50),
                        'repoPushes' => rand(30, 150),
                        'totalEvents' => rand(50, 300)
                    ];
                }
            }
        }

        return $data;
    }
} 