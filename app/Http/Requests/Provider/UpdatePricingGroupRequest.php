<?php

namespace App\Http\Requests\Provider;

use App\Http\Requests\BaseRequest;

class UpdatePricingGroupRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name'             => ['sometimes', 'required', 'array'],
            'name.ar'          => ['sometimes', 'required', 'string', 'max:255'],
            'name.en'          => ['sometimes', 'required', 'string', 'max:255'],
            'active'           => ['nullable', 'boolean'],
            'employee_uuids'   => ['nullable', 'array'],
            'employee_uuids.*' => ['required', 'string', 'max:26'],
        ];
    }
}
