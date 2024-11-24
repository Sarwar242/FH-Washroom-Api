<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MaintenanceReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'toilet_id' => 'required|exists:toilets,id',
            'issue_type' => 'required|in:plumbing,cleaning,repair,other',
            'description' => 'required|string|max:500',
            'priority' => 'required|in:low,medium,high,urgent'
        ];
    }
}
