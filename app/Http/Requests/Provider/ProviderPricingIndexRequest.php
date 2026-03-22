<?php

namespace App\Http\Requests\Provider;

use App\Http\Requests\BaseRequest;

class ProviderPricingIndexRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'service_uuid'       => ['nullable', 'string', 'max:26'],
            'sub_category_uuid'  => ['nullable', 'string', 'max:26'],
            'scope_type'         => ['nullable', 'string', 'in:default,branch,employee,group'],
            'branch_uuid'        => ['nullable', 'string', 'max:26'],
            'employee_uuid'      => ['nullable', 'string', 'max:26'],
            'pricing_group_uuid' => ['nullable', 'string', 'max:26'],
            'active'             => ['nullable', 'boolean'],
            'per_page'           => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
