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

    public function bodyParameters(): array
    {
        return [
            'provider_uuid'      => ['description' => 'Filter by provider UUID.', 'example' => null],
            'service_uuid'       => ['description' => 'Filter by service UUID.', 'example' => null],
            'sub_category_uuid'  => ['description' => 'Filter by sub-category UUID.', 'example' => null],
            'scope_type'         => ['description' => 'Filter by pricing scope. One of: default, branch, employee, group.', 'example' => 'default'],
            'branch_uuid'        => ['description' => 'Filter by branch UUID (applies when scope_type is branch).', 'example' => null],
            'employee_uuid'      => ['description' => 'Filter by employee UUID (applies when scope_type is employee).', 'example' => null],
            'pricing_group_uuid' => ['description' => 'Filter by pricing group UUID (applies when scope_type is group).', 'example' => null],
            'active'             => ['description' => 'Filter by active status.', 'example' => true],
            'trashed'            => ['description' => 'Include soft-deleted records. One of: only, with.', 'example' => null],
            'per_page'           => ['description' => 'Number of results per page (1–100).', 'example' => 15],
        ];
    }
}
