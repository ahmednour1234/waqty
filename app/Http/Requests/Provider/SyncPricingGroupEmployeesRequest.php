<?php

namespace App\Http\Requests\Provider;

use App\Http\Requests\BaseRequest;

class SyncPricingGroupEmployeesRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'employee_uuids'   => ['required', 'array', 'min:0'],
            'employee_uuids.*' => ['required', 'string', 'max:26'],
        ];
    }
}
