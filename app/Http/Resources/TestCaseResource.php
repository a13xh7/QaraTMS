<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\CasePriority;

class TestCaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'project_id' => $this->project_id,
            'project' => [
                'id' => $this->project->id ?? null,
                'name' => $this->project->name ?? null,
            ],
            'suite_id' => $this->suite_id,
            'suite' => [
                'id' => $this->suite->id ?? null,
                'name' => $this->suite->name ?? null,
            ],
            'priority' => $this->priority,
            'priority_label' => CasePriority::from($this->priority)->label(),
            'priority_color' => CasePriority::from($this->priority)->color(),
            'preconditions' => $this->preconditions,
            'steps' => $this->steps,
            'expected_result' => $this->expected_result,
            'tags' => $this->tags,
            'is_active' => $this->is_active,
            'created_by' => $this->created_by,
            'creator' => [
                'id' => $this->creator->id ?? null,
                'name' => $this->creator->name ?? null,
                'email' => $this->creator->email ?? null,
            ],
            'updated_by' => $this->updated_by,
            'updater' => [
                'id' => $this->updater->id ?? null,
                'name' => $this->updater->name ?? null,
                'email' => $this->updater->email ?? null,
            ],
            'test_runs_count' => $this->whenLoaded('testRuns', function () {
                return $this->testRuns->count();
            }),
            'last_execution' => $this->whenLoaded('testRuns', function () {
                return $this->testRuns->sortByDesc('created_at')->first();
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'links' => [
                'self' => route('test-cases.show', $this->id),
                'edit' => route('test-cases.edit', $this->id),
                'delete' => route('test-cases.destroy', $this->id),
            ]
        ];
    }
}
