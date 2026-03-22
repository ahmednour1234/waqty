<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseRequest;

class AdminPricingIndexRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'provider_uuid'      => ['nullable', 'string', 'max:26'],
            'service_uuid'       => ['nullable', 'string', 'max:26'],
            'sub_category_uuid'  => ['nullable', 'string', 'max:26'],
            'scope_type'         => ['nullable', 'string', 'in:default,branch,employee,group'],
            'branch_uuid'        => ['nullable', 'string', 'max:26'],
            'employee_uuid'      => ['nullable', 'string', 'max:26'],
            'pricing_group_uuid' => ['nullable', 'string', 'max:26'],
            'active'             => ['nullable', 'boolean'],
            'trashed'            => ['nullable', 'string', 'in:only,with'],
            'per_page'           => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
