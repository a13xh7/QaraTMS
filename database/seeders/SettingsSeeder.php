<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            [
                'key' => 'site_name',
                'value' => 'QaraTMS',
                'description' => 'Site Name',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'site_description',
                'value' => 'Test Management System',
                'description' => 'Site Description',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'site_logo',
                'value' => 'logo.png',
                'description' => 'Site Logo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'site_favicon',
                'value' => 'favicon.ico',
                'description' => 'Site Favicon',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'mail_from_address',
                'value' => 'noreply@qaratms.com',
                'description' => 'Mail From Address',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'mail_from_name',
                'value' => 'QaraTMS',
                'description' => 'Mail From Name',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'jira_enabled',
                'value' => 'true',
                'description' => 'Enable Jira Integration',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'jira_url',
                'value' => 'https://admin.atlassian.net',
                'description' => 'Jira URL',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'jira_username',
                'value' => env('JIRA_USERNAME'),
                'description' => 'Jira Username',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'jira_api_token',
                'value' => env('JIRA_API_TOKEN'),
                'description' => 'Jira API Token',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'jira_default_project',
                'value' => 'AL',
                'description' => 'Jira Default Project',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'jira_cache_duration',
                'value' => '30',
                'description' => 'Jira Cache Duration (minutes)',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'gitlab_token',
                'value' => env('GITLAB_TOKEN'),
                'type' => 'string',
                'group' => 'gitlab',
                'description' => 'GitLab Personal Access Token',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'gitlab_url',
                'value' => env('GITLAB_URL', 'https://gitlab.com/api/v4'),
                'type' => 'string',
                'group' => 'gitlab',
                'description' => 'GitLab API URL',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'gitlab_group',
                'value' => 'admin',
                'type' => 'string',
                'group' => 'gitlab',
                'description' => 'GitLab Group Name',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'gitlab_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'group' => 'gitlab',
                'description' => 'Enable/Disable GitLab Integration',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'gitlab_cache',
                'value' => '60',
                'type' => 'integer',
                'group' => 'gitlab',
                'description' => 'GitLab Cache Duration in minutes',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'key' => 'gitlab_projects',
                'value' => env('GITLAB_PROJECTS'),
                'type' => 'string',
                'group' => 'gitlab',
                'description' => 'GitLab Projects to Track',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->insert($setting);
        }
    }
} 