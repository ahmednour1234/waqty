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

    public function bodyParameters(): array
    {
        return [
            'service_uuid'       => ['description' => 'Filter by service UUID', 'example' => '01JQXYZ1234567890ABCDEFGH'],
            'sub_category_uuid'  => ['description' => 'Filter by subcategory UUID', 'example' => null],
            'scope_type'         => ['description' => 'Filter by scope: default, branch, employee, or group', 'example' => 'default'],
            'branch_uuid'        => ['description' => 'Filter by branch UUID', 'example' => null],
            'employee_uuid'      => ['description' => 'Filter by employee UUID', 'example' => null],
            'pricing_group_uuid' => ['description' => 'Filter by pricing group UUID', 'example' => null],
            'active'             => ['description' => 'Filter by active status', 'example' => true],
            'per_page'           => ['description' => 'Results per page (1–100)', 'example' => 15],
        ];
    }
}
