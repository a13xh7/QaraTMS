<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuVisibilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing menu data
        DB::table('menu_visibilities')->truncate();

        $menus = [
            [
                'id' => 1,
                'menu_key' => 'smoke_detector',
                'menu_name' => 'Smoke Detector',
                'is_visible' => 1,
                'parent_key' => null,
                'created_at' => '2025-05-07 15:11:58',
                'updated_at' => '2025-07-10 16:31:43'
            ],
            [
                'id' => 2,
                'menu_key' => 'post_mortems',
                'menu_name' => 'Post Mortems',
                'is_visible' => 1,
                'parent_key' => null,
                'created_at' => '2025-05-07 15:11:58',
                'updated_at' => '2025-07-10 16:31:43'
            ],
            [
                'id' => 3,
                'menu_key' => 'deployment_fail_rate',
                'menu_name' => 'Deployment Fail Rate',
                'is_visible' => 1,
                'parent_key' => null,
                'created_at' => '2025-05-07 15:11:58',
                'updated_at' => '2025-07-10 16:31:43'
            ],
            [
                'id' => 4,
                'menu_key' => 'lead_time_mrs',
                'menu_name' => 'Lead Time MRs',
                'is_visible' => 1,
                'parent_key' => null,
                'created_at' => '2025-05-07 15:11:58',
                'updated_at' => '2025-07-10 16:31:43'
            ],
            [
                'id' => 5,
                'menu_key' => 'jira_lead_time',
                'menu_name' => 'JIRA Lead Time',
                'is_visible' => 1,
                'parent_key' => null,
                'created_at' => '2025-05-07 15:11:58',
                'updated_at' => '2025-07-10 16:31:43'
            ],
            [
                'id' => 6,
                'menu_key' => 'monthly_contribution',
                'menu_name' => 'Monthly Contribution MR',
                'is_visible' => 1,
                'parent_key' => null,
                'created_at' => '2025-05-07 15:11:58',
                'updated_at' => '2025-07-10 16:31:43'
            ],
            [
                'id' => 7,
                'menu_key' => 'analytics_dashboard',
                'menu_name' => 'Analytics Dashboard',
                'is_visible' => 1,
                'parent_key' => null,
                'created_at' => '2025-05-07 15:11:58',
                'updated_at' => '2025-07-10 16:31:43'
            ],
            [
                'id' => 8,
                'menu_key' => 'testing_progress',
                'menu_name' => 'Testing Progress Dashboard',
                'is_visible' => 1,
                'parent_key' => null,
                'created_at' => '2025-05-07 15:11:58',
                'updated_at' => '2025-07-10 16:31:43'
            ],
            [
                'id' => 9,
                'menu_key' => 'bug_budget',
                'menu_name' => 'Bug Budget Dashboard',
                'is_visible' => 1,
                'parent_key' => null,
                'created_at' => '2025-05-07 15:11:58',
                'updated_at' => '2025-07-10 16:31:43'
            ],
            [
                'id' => 10,
                'menu_key' => 'defect_analytics',
                'menu_name' => 'Defect Analytics Dashboard',
                'is_visible' => 1,
                'parent_key' => null,
                'created_at' => '2025-05-07 15:11:58',
                'updated_at' => '2025-07-10 16:31:43'
            ],
            [
                'id' => 11,
                'menu_key' => 'apps_dashboard',
                'menu_name' => 'Apps Automation Dashboard',
                'is_visible' => 1,
                'parent_key' => null,
                'created_at' => '2025-05-07 15:11:58',
                'updated_at' => '2025-07-10 16:31:43'
            ],
            [
                'id' => 12,
                'menu_key' => 'api_dashboard',
                'menu_name' => 'API Automation Dashboard',
                'is_visible' => 1,
                'parent_key' => null,
                'created_at' => '2025-05-07 15:11:58',
                'updated_at' => '2025-07-10 16:31:43'
            ],
            [
                'id' => 13,
                'menu_key' => 'compliance',
                'menu_name' => 'Compliance',
                'is_visible' => 0,
                'parent_key' => null,
                'created_at' => '2025-05-07 15:11:58',
                'updated_at' => '2025-07-10 16:31:43'
            ],
            [
                'id' => 14,
                'menu_key' => 'compliance.sops',
                'menu_name' => 'SOP & QA Docs',
                'is_visible' => 0,
                'parent_key' => null,
                'created_at' => '2025-05-07 15:11:58',
                'updated_at' => '2025-07-10 16:31:43'
            ],
            [
                'id' => 15,
                'menu_key' => 'compliance.decision_logs',
                'menu_name' => 'Decision Logs',
                'is_visible' => 0,
                'parent_key' => null,
                'created_at' => '2025-05-07 15:11:58',
                'updated_at' => '2025-07-10 16:31:43'
            ],
            [
                'id' => 16,
                'menu_key' => 'compliance.test_exceptions',
                'menu_name' => 'Test Exceptions',
                'is_visible' => 0,
                'parent_key' => null,
                'created_at' => '2025-05-07 15:11:58',
                'updated_at' => '2025-07-10 16:31:43'
            ],
            [
                'id' => 17,
                'menu_key' => 'compliance.audit_readiness',
                'menu_name' => 'Audit Readiness',
                'is_visible' => 0,
                'parent_key' => null,
                'created_at' => '2025-05-07 15:11:58',
                'updated_at' => '2025-07-10 16:31:43'
            ],
            [
                'id' => 18,
                'menu_key' => 'compliance.knowledge_transfers',
                'menu_name' => 'Knowledge Transfers',
                'is_visible' => 0,
                'parent_key' => null,
                'created_at' => '2025-05-07 15:11:58',
                'updated_at' => '2025-07-10 16:31:43'
            ],
            [
                'id' => 19,
                'menu_key' => 'grafana_automation_report',
                'menu_name' => 'Grafana Automation Report (Parent)',
                'is_visible' => 1,
                'parent_key' => null,
                'created_at' => '2025-07-10 16:26:52',
                'updated_at' => '2025-07-10 16:31:43'
            ],
            [
                'id' => 20,
                'menu_key' => 'defect_analytics_dashboard',
                'menu_name' => 'Defect Analytics Dashboard (Parent)',
                'is_visible' => 1,
                'parent_key' => null,
                'created_at' => '2025-07-10 16:26:52',
                'updated_at' => '2025-07-10 16:31:43'
            ]
        ];

        foreach ($menus as $menu) {
            DB::table('menu_visibilities')->insert($menu);
        }

        if ($this->command) {
            $this->command->info('Menu visibility seeding completed successfully!');
        }
    }
}
