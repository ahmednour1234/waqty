<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $hasTemplate = $this->filled('shift_template_uuid');

        return [
            // Template OR manual times
            'shift_template_uuid' => ['nullable', 'string', 'exists:shift_templates,uuid'],
            'start_time'          => [Rule::requiredIf(!$hasTemplate), 'nullable', 'date_format:H:i'],
            'end_time'            => [Rule::requiredIf(!$hasTemplate), 'nullable', 'date_format:H:i'],
            'break_start'         => ['nullable', 'date_format:H:i'],
            'break_end'           => ['nullable', 'date_format:H:i'],

            // Date scheduling: explicit dates OR weekday range (at least one required)
            'dates'               => ['required_without:weekdays', 'nullable', 'array', 'min:1'],
            'dates.*'             => ['date_format:Y-m-d'],

            'weekdays'            => ['required_without:dates', 'nullable', 'array', 'min:1'],
            'weekdays.*'          => ['string', Rule::in(['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'])],
            'from_date'           => ['required_with:weekdays', 'nullable', 'date_format:Y-m-d'],
            'to_date'             => ['required_with:weekdays', 'nullable', 'date_format:Y-m-d', 'after_or_equal:from_date'],

            // Optional metadata
            'branch_uuid'         => ['nullable', 'string', 'exists:provider_branches,uuid'],
            'title'               => ['nullable', 'string', 'max:255'],
            'notes'               => ['nullable', 'string', 'max:5000'],

            // Employees
            'employee_uuids'      => ['nullable', 'array'],
            'employee_uuids.*'    => ['string', 'exists:employees,uuid'],

            'active'              => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_time.required'    => __('api.shifts.start_time_required'),
            'end_time.required'      => __('api.shifts.end_time_required'),
            'dates.required_without' => __('api.shifts.dates_or_weekdays_required'),
            'weekdays.required_without' => __('api.shifts.dates_or_weekdays_required'),
            'from_date.required_with'   => __('api.shifts.from_date_required'),
            'to_date.required_with'     => __('api.shifts.to_date_required'),
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'shift_template_uuid' => ['description' => 'UUID of a shift template to pull times from', 'required' => false, 'example' => null],
            'start_time'          => ['description' => 'Manual start time (HH:MM); required when no template', 'required' => false, 'example' => '07:00'],
            'end_time'            => ['description' => 'Manual end time (HH:MM); required when no template',   'required' => false, 'example' => '15:00'],
            'break_start'         => ['description' => 'Break start time (HH:MM)',   'required' => false, 'example' => '12:00'],
            'break_end'           => ['description' => 'Break end time (HH:MM)',     'required' => false, 'example' => '13:00'],
            'dates'               => ['description' => 'Explicit date list (Y-m-d); required if weekdays not provided', 'required' => false, 'example' => ['2026-03-22', '2026-03-23']],
            'weekdays'            => ['description' => 'Days of week (sun..sat); required if dates not provided', 'required' => false, 'example' => ['sat', 'sun']],
            'from_date'           => ['description' => 'Range start date (Y-m-d); required with weekdays', 'required' => false, 'example' => '2026-03-01'],
            'to_date'             => ['description' => 'Range end date (Y-m-d); required with weekdays',   'required' => false, 'example' => '2026-03-31'],
            'branch_uuid'         => ['description' => 'Branch UUID',       'required' => false, 'example' => null],
            'title'               => ['description' => 'Shift title',       'required' => false, 'example' => 'Week 12 Morning'],
            'notes'               => ['description' => 'Internal notes',    'required' => false, 'example' => null],
            'employee_uuids'      => ['description' => 'Employee UUIDs to assign to every generated shift date', 'required' => false, 'example' => []],
            'active'              => ['description' => 'Active status',     'required' => false, 'example' => true],
        ];
    }
}
