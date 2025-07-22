<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\CasePriority;

class TestCaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'project_id' => 'required|exists:projects,id',
            'suite_id' => 'required|exists:suites,id',
            'priority' => 'required|in:' . implode(',', array_column(CasePriority::cases(), 'value')),
            'preconditions' => 'nullable|string',
            'steps' => 'nullable|string',
            'expected_result' => 'nullable|string',
            'tags' => 'nullable|string',
            'is_active' => 'boolean'
        ];

        // Add conditional rules based on the request method
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            // For updates, make some fields optional
            $rules['title'] = 'sometimes|required|string|max:255';
            $rules['project_id'] = 'sometimes|required|exists:projects,id';
            $rules['suite_id'] = 'sometimes|required|exists:suites,id';
            $rules['priority'] = 'sometimes|required|in:' . implode(',', array_column(CasePriority::cases(), 'value'));
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The test case title is required.',
            'title.max' => 'The test case title cannot exceed 255 characters.',
            'project_id.required' => 'Please select a project.',
            'project_id.exists' => 'The selected project is invalid.',
            'suite_id.required' => 'Please select a test suite.',
            'suite_id.exists' => 'The selected test suite is invalid.',
            'priority.required' => 'Please select a priority level.',
            'priority.in' => 'The selected priority is invalid.',
            'description.string' => 'The description must be a valid text.',
            'preconditions.string' => 'The preconditions must be a valid text.',
            'steps.string' => 'The steps must be a valid text.',
            'expected_result.string' => 'The expected result must be a valid text.',
            'tags.string' => 'The tags must be a valid text.',
            'is_active.boolean' => 'The active status must be true or false.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'test case title',
            'description' => 'description',
            'project_id' => 'project',
            'suite_id' => 'test suite',
            'priority' => 'priority',
            'preconditions' => 'preconditions',
            'steps' => 'steps',
            'expected_result' => 'expected result',
            'tags' => 'tags',
            'is_active' => 'active status'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Ensure boolean fields are properly cast
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN)
            ]);
        }

        // Trim whitespace from string fields
        $this->merge([
            'title' => trim($this->title ?? ''),
            'description' => trim($this->description ?? ''),
            'preconditions' => trim($this->preconditions ?? ''),
            'steps' => trim($this->steps ?? ''),
            'expected_result' => trim($this->expected_result ?? ''),
            'tags' => trim($this->tags ?? '')
        ]);
    }
}
