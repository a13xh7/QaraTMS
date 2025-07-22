<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;
use App\Jobs\ProcessGitLabMergeRequests;

class GitLabService
{
    protected $apiUrl;
    protected $apiToken;
    protected $group;
    protected $cacheDuration;
    protected $maxPages;
    protected $maxRetries;
    protected $retryDelay;
    protected $rateLimitRemaining;
    protected $rateLimitReset;
    protected $circuitBreaker;
    protected $circuitBreakerThreshold;
    protected $circuitBreakerTimeout;

    public function __construct()
    {
        $this->apiUrl = rtrim(env('GITLAB_URL', 'https://gitlab.com'), '/');
        $this->apiToken = env('GITLAB_TOKEN', '');
        $this->group = env('GITLAB_GROUP', 'admin');
        $this->cacheDuration = env('GITLAB_CACHE_DURATION', 60); // minutes
        $this->maxPages = env('GITLAB_MAX_PAGES', 50);
        $this->maxRetries = env('GITLAB_MAX_RETRIES', 3);
        $this->retryDelay = env('GITLAB_RETRY_DELAY', 1000);
        $this->rateLimitRemaining = env('GITLAB_RATE_LIMIT_REMAINING', 30);
        $this->rateLimitReset = env('GITLAB_RATE_LIMIT_RESET', 60);
        $this->circuitBreaker = false;
        $this->circuitBreakerThreshold = env('GITLAB_CIRCUIT_BREAKER_THRESHOLD', 5);
        $this->circuitBreakerTimeout = env('GITLAB_CIRCUIT_BREAKER_TIMEOUT', 1000);
    }

    /**
     * Validate GitLab configuration
     *
     * @return array Array containing validation status and any error messages
     */
    protected function validateConfiguration()
    {
        $errors = [];

        if (empty($this->apiToken)) {
            $errors[] = 'GitLab API token not configured.';
        }

        if (empty($this->apiUrl)) {
            $errors[] = 'GitLab API URL not configured.';
        }

        if (!filter_var($this->apiUrl, FILTER_VALIDATE_URL)) {
            $errors[] = 'Invalid GitLab API URL format.';
        }

        if (empty($this->group)) {
            $errors[] = 'GitLab group not configured.';
        }

        if (empty($this->cacheDuration) || !is_numeric($this->cacheDuration)) {
            $errors[] = 'Invalid cache duration configured.';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Make a unified HTTP request to GitLab API
     *
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $params Query parameters
     * @param array $headers Additional headers
     * @return array Response data and status
     */
    protected function makeRequest($method, $endpoint, $params = [], $headers = [])
    {
        if ($this->circuitBreaker) {
            return [
                'success' => false,
                'error' => 'Circuit breaker is open. Service temporarily unavailable.',
                'status' => 503
            ];
        }

        if (!$this->checkRateLimit()) {
            return [
                'success' => false,
                'error' => 'Rate limit exceeded. Please try again later.',
                'status' => 429
            ];
        }

        $defaultHeaders = [
            'PRIVATE-TOKEN' => $this->apiToken,
            'Content-Type' => 'application/json'
        ];

        $headers = array_merge($defaultHeaders, $headers);
        $url = "{$this->apiUrl}/api/v4/{$endpoint}";

        try {
            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->$method($url, $params);

            $this->updateRateLimit($response);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                    'status' => $response->status()
                ];
            }

            if ($response->status() >= 500) {
                $this->updateCircuitBreaker();
            }

            return [
                'success' => false,
                'error' => $response->body(),
                'status' => $response->status()
            ];
        } catch (Exception $e) {
            $this->updateCircuitBreaker();
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 500
            ];
        }
    }

    /**
     * Check if rate limit is exceeded
     *
     * @return bool
     */
    protected function checkRateLimit()
    {
        $remaining = Cache::get('gitlab_rate_limit_remaining', $this->rateLimitRemaining);
        return $remaining > 0;
    }

    /**
     * Update rate limit information from response headers
     *
     * @param \Illuminate\Http\Client\Response $response
     */
    protected function updateRateLimit($response)
    {
        $remaining = $response->header('RateLimit-Remaining');
        $reset = $response->header('RateLimit-Reset');

        if ($remaining !== null) {
            Cache::put('gitlab_rate_limit_remaining', $remaining, $this->rateLimitReset);
        }

        if ($reset !== null) {
            Cache::put('gitlab_rate_limit_reset', $reset, $this->rateLimitReset);
        }
    }

    /**
     * Update circuit breaker state
     */
    protected function updateCircuitBreaker()
    {
        $failures = Cache::increment('gitlab_circuit_breaker_failures');
        
        if ($failures >= $this->circuitBreakerThreshold) {
            $this->circuitBreaker = true;
            Cache::put('gitlab_circuit_breaker_state', true, $this->circuitBreakerTimeout);
            
            // Reset circuit breaker after timeout
            Cache::put('gitlab_circuit_breaker_reset', time() + $this->circuitBreakerTimeout, $this->circuitBreakerTimeout);
        }
    }

    /**
     * Reset circuit breaker
     */
    protected function resetCircuitBreaker()
    {
        $this->circuitBreaker = false;
        Cache::forget('gitlab_circuit_breaker_failures');
        Cache::forget('gitlab_circuit_breaker_state');
    }

    /**
     * Process merge requests asynchronously using job queue
     *
     * @param string $startDate
     * @param string $endDate
     * @return string Job ID
     */
    public function processMergeRequestsAsync($startDate, $endDate)
    {
        $validation = $this->validateConfiguration();
        if (!$validation['valid']) {
            throw new Exception(implode(' ', $validation['errors']));
        }

        $job = new ProcessGitLabMergeRequests($startDate, $endDate);
        $jobId = Queue::push($job);

        return $jobId;
    }

    /**
     * Get merged MRs from GitLab for a specific date range
     *
     * @param string $startDate Start date in YYYY-MM-DD format
     * @param string $endDate End date in YYYY-MM-DD format
     * @param string|null $groupPath GitLab group path
     * @return array Response containing data and status
     */
    public function getMergedMRsByDateRange($startDate, $endDate, $groupPath = null)
    {
        $validation = $this->validateConfiguration();
        if (!$validation['valid']) {
            return [
                'success' => false,
                'error' => implode(' ', $validation['errors']),
                'data' => []
            ];
        }

        $groupPath = $groupPath ?? $this->group;
        $cacheKey = "gitlab_mrs_{$groupPath}_{$startDate}_{$endDate}";
        
        return Cache::remember($cacheKey, $this->cacheDuration * 60, function () use ($startDate, $endDate, $groupPath) {
            $startDateObj = Carbon::parse($startDate)->startOfDay();
            $endDateObj = Carbon::parse($endDate)->endOfDay();
            
            $allMergedMRs = [];
            $hasNextPage = true;
            $endCursor = null;
            $pageCount = 0;

            while ($hasNextPage && $pageCount < $this->maxPages) {
                $pageCount++;
                $variables = [
                    'fullPath' => $groupPath,
                    'startDate' => $startDateObj->toIso8601String(),
                    'endDate' => $endDateObj->toIso8601String(),
                    'after' => $endCursor
                ];

                $result = $this->graphqlRequest($this->getMergeRequestsQuery(), $variables);
                
                if (!$result || !isset($result['group']['mergeRequests']['nodes'])) {
                    break;
                }

                $mergedMRs = $result['group']['mergeRequests']['nodes'];
                $allMergedMRs = array_merge($allMergedMRs, $mergedMRs);

                $pageInfo = $result['group']['mergeRequests']['pageInfo'];
                $hasNextPage = $pageInfo['hasNextPage'];
                $endCursor = $pageInfo['endCursor'];
            }
            
            $processedMRs = $this->processMergeRequests($allMergedMRs);
            
            return [
                'success' => true,
                'data' => $processedMRs,
                'total' => count($processedMRs)
            ];
        });
    }

    /**
     * Process merge requests to calculate lead times and other metrics
     *
     * @param array $mergeRequests Array of merge requests from GitLab API
     * @param bool $isGraphQL Whether the data comes from GraphQL API
     * @return array Processed merge requests with calculated metrics
     */
    protected function processMergeRequests($mergeRequests, $isGraphQL = true)
    {
        $processedMRs = [];

        foreach ($mergeRequests as $mr) {
            try {
                $processedMR = $this->processSingleMergeRequest($mr, $isGraphQL);
                if ($processedMR) {
                    $processedMRs[] = $processedMR;
                }
            } catch (Exception $e) {
                Log::error("Error processing MR: " . $e->getMessage());
                continue;
            }
        }

        return $processedMRs;
    }

    /**
     * Process a single merge request
     *
     * @param array $mr Merge request data
     * @param bool $isGraphQL Whether the data comes from GraphQL API
     * @return array|null Processed merge request or null if invalid
     */
    protected function processSingleMergeRequest($mr, $isGraphQL)
    {
        // Parse dates based on API source
        $createdAt = $isGraphQL ? 
            Carbon::parse($mr['createdAt']) : 
            Carbon::parse($mr['created_at']);
        
        $mergedAt = $isGraphQL ? 
            Carbon::parse($mr['mergedAt']) : 
            Carbon::parse($mr['merged_at']);
        
        // Calculate lead times
        $leadTimeDays = $this->calculateBusinessDays($createdAt, $mergedAt);
        $leadTimeHours = $this->calculateHoursToMerge($createdAt, $mergedAt);
        
        // Get commit information
        $firstCommitToMergeDays = null;
        $firstCommitToMergeHours = null;
        
        if ($isGraphQL) {
            $commitDates = array_map(function($commit) {
                return Carbon::parse($commit['committedDate']);
            }, $mr['commits']['nodes']);
        } else {
            $commitDates = array_map(function($commit) {
                return Carbon::parse($commit['created_at']);
            }, $mr['commits'] ?? []);
        }

        if (!empty($commitDates)) {
            $earliestCommitDate = min($commitDates);
            $firstCommitToMergeDays = $this->calculateBusinessDays($earliestCommitDate, $mergedAt);
            $firstCommitToMergeHours = $this->calculateHoursToMerge($earliestCommitDate, $mergedAt);
        }
        
        // Extract project information
        $projectPath = $isGraphQL ? 
            ($mr['targetProject']['fullPath'] ?? '') : 
            ($mr['project']['path_with_namespace'] ?? '');
        
        // Extract labels
        $labels = $isGraphQL ? 
            array_map(function($label) { return $label['title']; }, $mr['labels']['nodes']) : 
            ($mr['labels'] ?? []);
        
        return [
            'iid' => $mr['iid'],
            'title' => $mr['title'],
            'author' => $isGraphQL ? 
                $mr['author']['name'] : 
                ($mr['author']['name'] ?? 'Unknown'),
            'created_at' => $createdAt->format('Y-m-d H:i:s'),
            'merged_at' => $mergedAt->format('Y-m-d H:i:s'),
            'lead_time_days' => $leadTimeDays,
            'lead_time_hours' => $leadTimeHours,
            'first_commit_to_merge_days' => $firstCommitToMergeDays,
            'first_commit_to_merge_hours' => $firstCommitToMergeHours,
            'labels' => implode(', ', $labels),
            'url' => $isGraphQL ? $mr['webUrl'] : $mr['web_url'],
            'project' => $projectPath,
            'repository' => $projectPath,
            'source_branch' => $mr['sourceBranch'] ?? $mr['source_branch'] ?? '',
            'created_at_human' => $createdAt->diffForHumans()
        ];
    }

    /**
     * Make a GraphQL API request to GitLab
     *
     * @param string $query The GraphQL query
     * @param array $variables Variables for the query
     * @return array|null The response data or null on failure
     */
    public function graphqlRequest($query, $variables = [])
    {
        if (!$this->validateConfiguration()) {
            return null;
        }

        try {
            $baseUrl = preg_replace('/\/api\/v4$/', '', $this->apiUrl);
            $graphqlUrl = "{$baseUrl}/api/graphql";
            
            Log::info("Making GitLab GraphQL request to: {$graphqlUrl}");
            Log::debug("GraphQL Query Variables: " . json_encode($variables));
            
            $requestBody = ['query' => $query];
            if (!empty($variables)) {
                $requestBody['variables'] = $variables;
            }
            
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$this->apiToken}"
            ])->post($graphqlUrl, $requestBody);

            if ($response->failed()) {
                $statusCode = $response->status();
                $responseBody = $response->body();
                Log::error("GitLab GraphQL request failed with status {$statusCode}: {$responseBody}");
                return null;
            }

            $result = $response->json();

            if (isset($result['errors'])) {
                $errorMessage = json_encode($result['errors']);
                Log::error("GitLab GraphQL Error: {$errorMessage}");
                return null;
            }

            return $result['data'] ?? null;
        } catch (Exception $e) {
            Log::error('GitLab GraphQL Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the GraphQL query for merge requests
     *
     * @return string
     */
    protected function getMergeRequestsQuery()
    {
        return <<<'GRAPHQL'
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
                sourceBranch
                targetBranch
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
    }

    /**
     * Calculates business days between two dates (excluding weekends)
     *
     * @param Carbon $startDate The start date
     * @param Carbon $endDate The end date
     * @return int Number of business days
     */
    protected function calculateBusinessDays($startDate, $endDate)
    {
        $count = 0;
        $currentDate = clone $startDate;
        
        while ($currentDate->lte($endDate)) {
            // Skip weekends (6 = Saturday, 0 = Sunday)
            $dayOfWeek = $currentDate->dayOfWeek;
            if ($dayOfWeek !== 0 && $dayOfWeek !== 6) {
                $count++;
            }
            $currentDate->addDay();
        }
        
        return $count;
    }

    /**
     * Calculates total hours between two dates (including weekends)
     *
     * @param Carbon $startDate The start date
     * @param Carbon $endDate The end date
     * @return float Number of hours, rounded to 2 decimal places
     */
    protected function calculateHoursToMerge($startDate, $endDate)
    {
        $diffInSeconds = $endDate->diffInSeconds($startDate);
        $hours = $diffInSeconds / 3600;
        return round($hours, 2);
    }

    /**
     * Checks if GitLab integration is properly configured
     *
     * @return bool
     */
    public function isConfigured()
    {
        $enabled = env('GITLAB_ENABLED');
        $isEnabled = $enabled === 'true' || $enabled === true;
        
        return $this->validateConfiguration() && $isEnabled;
    }

    /**
     * Get list of all projects in the group
     *
     * @return array List of projects with id, name, and path
     */
    public function getAllProjects()
    {
        $cacheKey = "gitlab_projects_{$this->group}";
        
        return Cache::remember($cacheKey, $this->cacheDuration * 60, function () {
            $query = <<<'GRAPHQL'
            query {
              group(fullPath: $fullPath) {
                projects(first: 100) {
                  nodes {
                    id
                    name
                    fullPath
                    description
                    webUrl
                  }
                }
              }
            }
            GRAPHQL;

            $variables = [
                'fullPath' => $this->group
            ];
            
            $result = $this->graphqlRequest($query, $variables);
            
            if (!$result || !isset($result['group']['projects']['nodes'])) {
                return [];
            }
            
            return $result['group']['projects']['nodes'];
        });
    }
    
    /**
     * Get the configured GitLab group
     *
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Get the configured GitLab API token (masked for security)
     *
     * @return string
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * Get the configured GitLab API URL
     *
     * @return string
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }

    /**
     * Get a list of project paths from the GITLAB_PROJECTS environment variable
     * 
     * @return array
     */
    public function getProjectPaths()
    {
        $projectsString = env('GITLAB_PROJECTS', '');
        if (empty($projectsString)) {
            return [];
        }
        
        // Split the comma-separated string and trim whitespace
        $projects = array_map('trim', explode(',', $projectsString));
        return $projects;
    }

    /**
     * Get merged MRs from multiple GitLab projects for a specific date range
     *
     * @param string $startDate Start date in YYYY-MM-DD format
     * @param string $endDate End date in YYYY-MM-DD format
     * @return array Array of merged MRs with calculated metrics
     */
    public function getMergedMRsFromProjects($startDate, $endDate)
    {
        try {
            // Set a longer timeout for this operation
            ini_set('max_execution_time', 300); // 5 minutes
            
            $cacheKey = "gitlab_projects_mrs_{$startDate}_{$endDate}";
            
            // Try to use the cached data first
            $cachedData = Cache::get($cacheKey);
            if ($cachedData) {
                Log::info("Using cached MR data with " . count($cachedData) . " MRs");
                return $cachedData;
            }
            
            // Get all projects
            $projectPaths = $this->getProjectPaths();
            
            if (empty($projectPaths)) {
                Log::warning("No projects found in GITLAB_PROJECTS environment variable");
                return [];
            }

            Log::info("Found " . count($projectPaths) . " projects to process");
            
            $allMergedMRs = [];
            $startDateObj = Carbon::parse($startDate)->startOfDay();
            $endDateObj = Carbon::parse($endDate)->endOfDay();
            
            // Log date range being queried
            Log::info("Querying MRs between {$startDateObj->toIso8601String()} and {$endDateObj->toIso8601String()}");
            
            foreach ($projectPaths as $projectPath) {
                if (empty($projectPath)) continue;
                
                try {
                    $baseUrl = $this->apiUrl;
                    // Double encode the project path for GitLab API
                    $encodedProjectPath = str_replace('.', '%2E', rawurlencode($projectPath));
                    Log::info("Processing project: {$projectPath} (encoded: {$encodedProjectPath})");
                    
                    $page = 1;
                    $hasMorePages = true;
                    $projectMRCount = 0;
                    $projectSkippedCount = 0;
                    
                    while ($hasMorePages) {
                        try {
                            $url = "{$baseUrl}/projects/{$encodedProjectPath}/merge_requests";
                            $params = [
                                'state' => 'merged',
                                'merged_after' => $startDateObj->toIso8601String(),
                                'merged_before' => $endDateObj->toIso8601String(),
                                'per_page' => 50,
                                'page' => $page,
                                'with_labels_details' => true
                            ];
                            
                            Log::info("Fetching page {$page} for project {$projectPath}");
                            Log::debug("Request URL: {$url}");
                            Log::debug("Request params: " . json_encode($params));
                            
                            $response = Http::timeout(30) 
                                ->withHeaders(['PRIVATE-TOKEN' => $this->apiToken])
                                ->get($url, $params);
                            
                            if ($response->failed()) {
                                Log::error("Failed to fetch MRs for project {$projectPath} on page {$page}. Status: " . $response->status());
                                Log::error("Response: " . $response->body());
                                break;
                            }
                            
                            $mergeRequests = $response->json();
                            
                            if (empty($mergeRequests)) {
                                Log::info("No more MRs found for project {$projectPath} on page {$page}");
                                $hasMorePages = false;
                                continue;
                            }
                            
                            $currentPageCount = count($mergeRequests);
                            Log::info("Found {$currentPageCount} MRs on page {$page} for project {$projectPath}");
                            
                            // Process each MR on this page
                            foreach ($mergeRequests as $mr) {
                                try {
                                    // Log MR details for debugging
                                    Log::debug("Processing MR {$mr['iid']} from {$projectPath}:");
                                    Log::debug("Created at: {$mr['created_at']}");
                                    Log::debug("Merged at: {$mr['merged_at']}");
                                    Log::debug("State: {$mr['state']}");
                                    
                                    if ($mr['state'] !== 'merged' || empty($mr['merged_at'])) {
                                        Log::debug("Skipping MR {$mr['iid']}: Not merged or missing merged_at date");
                                        $projectSkippedCount++;
                                        continue;
                                    }
                                    
                                    $createdAt = Carbon::parse($mr['created_at']);
                                    $mergedAt = Carbon::parse($mr['merged_at']);
                                    
                                    // Validate date range
                                    if ($mergedAt->lt($startDateObj) || $mergedAt->gt($endDateObj)) {
                                        Log::debug("Skipping MR {$mr['iid']}: Outside date range");
                                        $projectSkippedCount++;
                                        continue;
                                    }
                                    
                                    // Extract repository name from project path
                                    $repository = $projectPath;
                                    
                                    // Fetch commit information with retry logic
                                    $firstCommitToMergeDays = null;
                                    $firstCommitToMergeHours = null;
                                    
                                    for ($retryCount = 0; $retryCount < 3; $retryCount++) {
                                        try {
                                            $commitsUrl = "{$baseUrl}/projects/{$encodedProjectPath}/merge_requests/{$mr['iid']}/commits";
                                            $commitsResponse = Http::timeout(10)
                                                ->withHeaders(['PRIVATE-TOKEN' => $this->apiToken])
                                                ->get($commitsUrl);
                                            
                                            if (!$commitsResponse->failed()) {
                                                $commits = $commitsResponse->json();
                                                if (!empty($commits)) {
                                                    $commitDates = array_map(function($commit) {
                                                        return Carbon::parse($commit['created_at']);
                                                    }, $commits);
                                                    
                                                    if (!empty($commitDates)) {
                                                        $earliestCommitDate = min($commitDates);
                                                        $firstCommitToMergeDays = $this->calculateBusinessDays($earliestCommitDate, $mergedAt);
                                                        $firstCommitToMergeHours = $this->calculateHoursToMerge($earliestCommitDate, $mergedAt);
                                                    }
                                                }
                                                break; // Success, exit retry loop
                                            }
                                        } catch (Exception $e) {
                                            Log::warning("Retry {$retryCount} failed for commits of MR {$mr['iid']}: " . $e->getMessage());
                                            if ($retryCount < 2) sleep(1); // Wait before retry
                                        }
                                    }
                                    
                                    $allMergedMRs[] = [
                                        'iid' => $mr['iid'],
                                        'title' => $mr['title'],
                                        'author' => $mr['author']['name'] ?? 'Unknown',
                                        'created_at' => $createdAt->format('Y-m-d H:i:s'),
                                        'merged_at' => $mergedAt->format('Y-m-d H:i:s'),
                                        'lead_time_days' => $this->calculateBusinessDays($createdAt, $mergedAt),
                                        'lead_time_hours' => $this->calculateHoursToMerge($createdAt, $mergedAt),
                                        'first_commit_to_merge_days' => $firstCommitToMergeDays,
                                        'first_commit_to_merge_hours' => $firstCommitToMergeHours,
                                        'labels' => implode(', ', $mr['labels'] ?? []),
                                        'url' => $mr['web_url'],
                                        'repository' => $repository,
                                        'source_branch' => $mr['source_branch'] ?? '',
                                        'created_at_human' => $createdAt->diffForHumans()
                                    ];
                                    
                                    $projectMRCount++;
                                    
                                    // Cache intermediate results every 20 MRs
                                    if (count($allMergedMRs) % 20 === 0) {
                                        Cache::put($cacheKey, $allMergedMRs, now()->addHours(1));
                                        Log::info("Cached {$projectMRCount} MRs for project {$projectPath}");
                                    }
                                    
                                } catch (Exception $e) {
                                    Log::error("Error processing MR {$mr['iid']} for project {$projectPath}: " . $e->getMessage());
                                    continue;
                                }
                            }
                            
                            // Check if we have more pages
                            $totalPages = $response->header('X-Total-Pages');
                            $hasMorePages = $page < $totalPages;
                            $page++;
                            
                            // Add a small delay between pages to avoid rate limiting
                            if ($hasMorePages) {
                                usleep(200000); // 0.2 second delay
                            }
                            
                        } catch (Exception $e) {
                            Log::error("Error fetching page {$page} for project {$projectPath}: " . $e->getMessage());
                            break;
                        }
                    }
                    
                    Log::info("Completed processing project {$projectPath}. Found {$projectMRCount} MRs, Skipped {$projectSkippedCount} MRs");
                    
                } catch (Exception $e) {
                    Log::error("Error fetching project {$projectPath}: " . $e->getMessage());
                    continue;
                }
            }
            
            Log::info("Total MRs found across all projects: " . count($allMergedMRs));
            
            // Sort MRs by merged_at date
            usort($allMergedMRs, function($a, $b) {
                return strtotime($b['merged_at']) - strtotime($a['merged_at']);
            });
            
            // Cache the final results
            Cache::put($cacheKey, $allMergedMRs, now()->addHours(1));
            
            return $allMergedMRs;
            
        } catch (Exception $e) {
            Log::error("Fatal error in getMergedMRsFromProjects: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Process merge requests from REST API to calculate lead times and other metrics
     *
     * @param array $mergeRequests Array of merge requests from GitLab REST API
     * @return array Processed merge requests with calculated metrics
     */
    protected function processRestApiMergeRequests($mergeRequests)
    {
        $processedMRs = [];

        foreach ($mergeRequests as $mr) {
            // Skip if not merged
            if ($mr['state'] !== 'merged' || empty($mr['merged_at'])) {
                continue;
            }
            
            // Parse dates
            $createdAt = Carbon::parse($mr['created_at']);
            $mergedAt = Carbon::parse($mr['merged_at']);
            
            // Calculate lead times
            $leadTimeDays = $this->calculateBusinessDays($createdAt, $mergedAt);
            $leadTimeHours = $this->calculateHoursToMerge($createdAt, $mergedAt);
            
            // Get commit information
            $firstCommitToMergeDays = null;
            $firstCommitToMergeHours = null;
            
            // Try to get commit information from the API response
            if (isset($mr['commits'])) {
                $commitDates = [];
                foreach ($mr['commits'] as $commit) {
                    if (isset($commit['created_at'])) {
                        $commitDates[] = Carbon::parse($commit['created_at']);
                    }
                }
                if (!empty($commitDates)) {
                    $earliestCommitDate = min($commitDates);
                    $firstCommitToMergeDays = $this->calculateBusinessDays($earliestCommitDate, $mergedAt);
                    $firstCommitToMergeHours = $this->calculateHoursToMerge($earliestCommitDate, $mergedAt);
                }
            }
            
            // Extract repository information
            $repository = '';
            if (isset($mr['references']['full'])) {
                $pathParts = explode('/', $mr['references']['full']);
                $repository = end($pathParts);
                $repository = preg_replace('/!.*$/', '', $repository);
            } elseif (isset($mr['project']['path_with_namespace'])) {
                $pathParts = explode('/', $mr['project']['path_with_namespace']);
                $repository = end($pathParts);
            } elseif (isset($mr['target_project_id'])) {
                $repository = "project-" . $mr['target_project_id'];
            }
            
            // Extract labels
            $labels = isset($mr['labels']) ? implode(', ', $mr['labels']) : '';
            
            // Get source branch information
            $sourceBranch = $mr['source_branch'] ?? '';
            
            $processedMRs[] = [
                'iid' => $mr['iid'],
                'title' => $mr['title'],
                'author' => isset($mr['author']) ? $mr['author']['name'] : 'Unknown',
                'created_at' => $createdAt->format('Y-m-d H:i:s'),
                'merged_at' => $mergedAt->format('Y-m-d H:i:s'),
                'lead_time_days' => $leadTimeDays,
                'lead_time_hours' => $leadTimeHours,
                'first_commit_to_merge_days' => $firstCommitToMergeDays,
                'first_commit_to_merge_hours' => $firstCommitToMergeHours,
                'labels' => $labels,
                'url' => $mr['web_url'],
                'repository' => $repository,
                'source_branch' => $sourceBranch,
                'created_at_human' => $createdAt->diffForHumans()
            ];
        }

        return $processedMRs;
    }
} 