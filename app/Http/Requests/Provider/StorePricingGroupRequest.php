<?php

namespace App\Http\Requests\Provider;

use App\Http\Requests\BaseRequest;

class StorePricingGroupRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name'             => ['required', 'array'],
            'name.ar'          => ['required', 'string', 'max:255'],
            'name.en'          => ['required', 'string', 'max:255'],
            'active'           => ['nullable', 'boolean'],
            'employee_uuids'   => ['nullable', 'array'],
            'employee_uuids.*' => ['required', 'string', 'max:26'],
        ];
    }
}
