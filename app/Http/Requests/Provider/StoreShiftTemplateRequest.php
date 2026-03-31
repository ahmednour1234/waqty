<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class StoreShiftTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'array'],
            'name.ar'     => ['required', 'string', 'max:255'],
            'name.en'     => ['required', 'string', 'max:255'],
            'start_time'  => ['required', 'date_format:H:i'],
            'end_time'    => ['required', 'date_format:H:i', 'after:start_time'],
            'break_start' => ['nullable', 'date_format:H:i'],
            'break_end'   => ['nullable', 'date_format:H:i', 'after:break_start'],
            'color'       => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'active'      => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'end_time.after'    => __('api.shifts.end_time_after_start'),
            'break_end.after'   => __('api.shifts.break_end_after_start'),
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name.ar'     => ['description' => 'Template name in Arabic',  'required' => true,  'example' => 'وردية الصباح'],
            'name.en'     => ['description' => 'Template name in English', 'required' => true,  'example' => 'Morning Shift'],
            'start_time'  => ['description' => 'Shift start time (HH:MM)', 'required' => true,  'example' => '07:00'],
            'end_time'    => ['description' => 'Shift end time (HH:MM)',   'required' => true,  'example' => '15:00'],
            'break_start' => ['description' => 'Break start time (HH:MM)','required' => false, 'example' => '12:00'],
            'break_end'   => ['description' => 'Break end time (HH:MM)',   'required' => false, 'example' => '13:00'],
            'active'      => ['description' => 'Whether template is active','required' => false, 'example' => true],
        ];
    }
}
