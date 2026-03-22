<?php

namespace App\Http\Requests;

use App\Http\Requests\BaseRequest;

class ResolveServicePriceRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true; // public or authenticated endpoints
    }

    public function rules(): array
    {
        return [
            'provider_uuid'  => ['nullable', 'string', 'max:26'],
            'branch_uuid'    => ['nullable', 'string', 'max:26'],
            'employee_uuid'  => ['nullable', 'string', 'max:26'],
        ];
    }
}
