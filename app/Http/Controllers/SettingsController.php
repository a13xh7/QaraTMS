<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\MenuVisibility;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:access_settings');
    }

    /**
     * Display the main settings page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        return view('settings.index');
    }

    /**
     * Display Jira settings page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function jira()
    {
        return view('settings.jira');
    }

    /**
     * Display GitLab settings page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function gitlab()
    {
        return view('settings.gitlab');
    }

    /**
     * Display Confluence settings page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function confluence()
    {
        return view('settings.confluence');
    }

    /**
     * Display Dashboard Access settings page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function dashboardAccess()
    {
        $users = User::all();
        $authorizedUsers = User::permission('access_manager_dashboard')->get();
        
        return view('settings.dashboard_access', compact('users', 'authorizedUsers'));
    }

    /**
     * Update Dashboard Access for users
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateDashboardAccess(Request $request)
    {
        $userIds = $request->input('user_ids', []);
        
        // Remove permission from all users first
        User::permission('access_manager_dashboard')->get()
            ->each(function ($user) {
                $user->revokePermissionTo('access_manager_dashboard');
            });
        
        // Add permission to selected users
        if (!empty($userIds)) {
            User::whereIn('id', $userIds)->get()
                ->each(function ($user) {
                    $user->givePermissionTo('access_manager_dashboard');
                });
        }
        
        return back()->with('success', 'Dashboard access updated successfully');
    }

    /**
     * Display Settings Access page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function settingsAccess()
    {
        $users = User::all();
        $authorizedUsers = User::permission('access_settings')->get();
        
        return view('settings.settings_access', compact('users', 'authorizedUsers'));
    }

    /**
     * Update Settings Access for users
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSettingsAccess(Request $request)
    {
        $userIds = $request->input('user_ids', []);
        $userRoles = $request->input('user_roles', []);
        
        // Remove permission from all users first
        User::permission('access_settings')->get()
            ->each(function ($user) {
                $user->revokePermissionTo('access_settings');
            });
        
        // Add permission to selected users
        if (!empty($userIds)) {
            User::whereIn('id', $userIds)->get()
                ->each(function ($user) {
                    $user->givePermissionTo('access_settings');
                });
        }
        
        // Update user roles
        if (!empty($userRoles)) {
            foreach ($userRoles as $userId => $role) {
                User::where('id', $userId)->update(['role' => $role]);
            }
        }
        
        return back()->with('success', 'Settings access and user roles updated successfully');
    }

    /**
     * Display Menu Visibility settings page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function menuVisibility()
    {
        $menuItems = MenuVisibility::all();
        return view('settings.menu_visibility', compact('menuItems'));
    }

    /**
     * Update Menu Visibility settings
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateMenuVisibility(Request $request)
    {
        $visibleMenus = $request->input('menu_items', []);
        
        // Set all menu items to not visible by default
        MenuVisibility::query()->update(['is_visible' => false]);
        
        // Update selected menu items using the hierarchical approach
        if (!empty($visibleMenus)) {
            foreach ($visibleMenus as $menuKey) {
                // Update this menu item and potentially its children
                MenuVisibility::updateVisibilityWithChildren($menuKey, true);
                
                // Make sure parents of visible items become visible too if needed
                $parentKey = MenuVisibility::getParent($menuKey);
                if ($parentKey) {
                    $parentMenu = MenuVisibility::where('menu_key', $parentKey)->first();
                    if ($parentMenu) {
                        $parentMenu->is_visible = true;
                        $parentMenu->save();
                    }
                }
            }
        }
        
        return back()->with('success', 'Menu visibility settings updated successfully');
    }

    /**
     * Update Jira settings
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateJira(Request $request)
    {
        $request->validate([
            'jira_url' => 'required|url',
            'jira_username' => 'required|email',
            'jira_api_token' => 'required',
        ]);

        // Update the .env file with the new values
        $this->updateEnvironmentFile([
            'JIRA_URL_SEARCH' => $request->input('jira_url'),
            'JIRA_USERNAME' => $request->input('jira_username'),
            'JIRA_API_TOKEN' => $request->input('jira_api_token'),
        ]);

        return back()->with('success', 'Jira settings updated successfully');
    }

    /**
     * Update GitLab settings
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateGitlab(Request $request)
    {
        $request->validate([
            'gitlab_url' => 'required|url',
            'gitlab_token' => 'required|string',
            'gitlab_group' => 'nullable|string',
            'gitlab_cache' => 'required|integer|min:5|max:1440',
            'selected_projects' => 'nullable|string',
        ]);
        
        $enabled = $request->has('gitlab_enabled') ? true : false;
        $url = $request->input('gitlab_url');
        $token = $request->input('gitlab_token');
        $group = $request->input('gitlab_group');
        $cacheDuration = $request->input('gitlab_cache');
        
        // Get selected projects array from JSON string
        $selectedProjects = [];
        $projectsJson = $request->input('selected_projects');
        if (!empty($projectsJson)) {
            try {
                $selectedProjects = json_decode($projectsJson, true);
                if (!is_array($selectedProjects)) {
                    $selectedProjects = [];
                }
            } catch (\Exception $e) {
                \Log::error('Failed to parse selected projects: ' . $e->getMessage());
            }
        }
        
        // Update .env file with new values
        $success = $this->updateEnvironmentFile([
            'GITLAB_ENABLED' => $enabled ? 'true' : 'false',
            'GITLAB_URL' => $url,
            'GITLAB_TOKEN' => $token,
            'GITLAB_GROUP' => $group,
            'GITLAB_CACHE_DURATION' => $cacheDuration,
            'GITLAB_PROJECTS' => implode(',', $selectedProjects),
        ]);
        
        if (!$success) {
            return redirect()->route('settings.gitlab')
                ->with('error', 'Failed to update GitLab settings. Check file permissions on .env file.');
        }
        
        // Clear config cache to ensure new settings are loaded
        try {
            \Artisan::call('config:clear');
        } catch (\Exception $e) {
            // Log the error but continue
            \Log::error('Failed to clear config cache: ' . $e->getMessage());
        }
        
        return redirect()->route('settings.gitlab')
            ->with('success', 'GitLab settings updated successfully. Connection configured for ' . $url);
    }
    
    /**
     * Update environment file with new values
     *
     * @param array $data
     * @return bool
     */
    private function updateEnvironmentFile($data)
    {
        $envFile = app()->environmentFilePath();
        $envContents = file_get_contents($envFile);
        
        foreach ($data as $key => $value) {
            // If value contains spaces or special characters, wrap in quotes
            if (preg_match('/\s/', $value) || preg_match('/[^\w.-]/', $value)) {
                $value = '"' . addslashes($value) . '"';
            }
            
            // Check if key exists and replace it
            if (preg_match("/^{$key}=/m", $envContents)) {
                $envContents = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContents);
            } else {
                // Add key if it doesn't exist
                $envContents .= PHP_EOL . "{$key}={$value}";
            }
        }
        
        return file_put_contents($envFile, $envContents) !== false;
    }

    /**
     * Update Confluence settings
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateConfluence(Request $request)
    {
        $request->validate([
            'confluence_url' => 'required|url',
            'confluence_username' => 'required',
            'confluence_api_token' => 'required',
            'confluence_space_key' => 'required',
            'confluence_space_key2' => 'nullable',
        ]);

        // Update the .env file with the new values
        $this->updateEnvironmentFile([
            'CONFLUENCE_BASE_URL' => $request->input('confluence_url'),
            'CONFLUENCE_USERNAME' => $request->input('confluence_username'),
            'CONFLUENCE_API_TOKEN' => $request->input('confluence_api_token'),
            'CONFLUENCE_SPACE_KEY' => $request->input('confluence_space_key'),
            'CONFLUENCE_SPACE_KEY2' => $request->input('confluence_space_key2', ''),
        ]);

        return back()->with('success', 'Confluence settings updated successfully');
    }

    /**
     * Update Advanced settings
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAdvancedSettings(Request $request)
    {
        // Update the .env file with default values
        $this->updateEnvironmentFile([
            'CONFLUENCE_BASE_URL' => 'https://your-domain.atlassian.net/wiki',
            'CONFLUENCE_USERNAME' => 'your-email@domain.com',
            'CONFLUENCE_API_TOKEN' => 'your-confluence-api-token',
            'CONFLUENCE_SPACE_KEY' => 'YOUR_SPACE_KEY',
            'JIRA_URL_SEARCH' => 'https://your-domain.atlassian.net/rest/api/2/search',
            'JIRA_USERNAME' => 'your-email@domain.com',
            'JIRA_API_TOKEN' => 'your-jira-api-token',
            'CONFLUENCE_SPACE_KEY2' => 'YOUR_SPACE_KEY2',
            'GITLAB_URL' => 'https://gitlab.com/api/v4',
            'GITLAB_API_URL' => 'https://gitlab.com/api/v4',
            'PROJECT_ID' => 'your-project-id',
            'GITLAB_TOKEN' => 'your-gitlab-token',
            'SLACK_BOT_TOKEN' => 'your-slack-bot-token',
            'SLACK_CHANNEL_ID' => 'your-slack-channel-id',
        ]);

        return back()->with('success', 'Default settings applied successfully');
    }

    /**
     * Display Advanced settings page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function advanced()
    {
        return view('settings.advanced');
    }

    /**
     * Display Squad settings page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function squad()
    {
        return view('settings.squad');
    }

    /**
     * Update Squad settings
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateSquad(Request $request)
    {
        $request->validate([
            'squad_name' => 'required|string',
            'squad_members' => 'nullable|array',
        ]);

        // Logic to update squad settings
        // This could store squad data in a database table or config file

        return back()->with('success', 'Squad settings updated successfully');
    }

    /**
     * Display Scoring settings page
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function scoring()
    {
        return view('settings.scoring');
    }

    /**
     * Update Scoring settings
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateScoring(Request $request)
    {
        $request->validate([
            'quality_weight' => 'required|numeric|min:0|max:100',
            'speed_weight' => 'required|numeric|min:0|max:100',
            'contribution_weight' => 'required|numeric|min:0|max:100',
        ]);

        // Logic to update scoring weights
        // This could store scoring data in a database table or env file
        $this->updateEnvironmentFile([
            'QUALITY_WEIGHT' => $request->input('quality_weight'),
            'SPEED_WEIGHT' => $request->input('speed_weight'),
            'CONTRIBUTION_WEIGHT' => $request->input('contribution_weight'),
        ]);

        return back()->with('success', 'Scoring settings updated successfully');
    }

    /**
     * Add a new user
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|string',
        ]);
        
        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);
        
        // Grant settings access if requested
        if ($request->has('access_settings')) {
            $user->givePermissionTo('access_settings');
        }
        
        return redirect()->route('settings.settings_access')
            ->with('success', 'User "' . $user->name . '" has been created successfully.');
    }
} 