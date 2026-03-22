<?php

namespace App\Http\Requests\Provider;

use App\Http\Requests\BaseRequest;

class UpdateServicePriceRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'price'              => ['sometimes', 'required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
            'active'             => ['nullable', 'boolean'],
            'branch_uuid'        => ['nullable', 'string', 'max:26'],
            'employee_uuid'      => ['nullable', 'string', 'max:26'],
            'pricing_group_uuid' => ['nullable', 'string', 'max:26'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $scopeFields = array_filter([
                $this->input('branch_uuid'),
                $this->input('employee_uuid'),
                $this->input('pricing_group_uuid'),
            ]);

            if (count($scopeFields) > 1) {
                $validator->errors()->add('scope', __('api.service_prices.multiple_scopes'));
            }
        });
    }

    public function bodyParameters(): array
    {
        return [
            'price'              => ['description' => 'New price value (decimal, min 0)', 'example' => '75.00'],
            'active'             => ['description' => 'Whether the price rule is active', 'example' => true],
            'branch_uuid'        => ['description' => 'Update scope to this branch UUID (exclusive with employee_uuid, pricing_group_uuid)', 'example' => null],
            'employee_uuid'      => ['description' => 'Update scope to this employee UUID (exclusive)', 'example' => null],
            'pricing_group_uuid' => ['description' => 'Update scope to this pricing group UUID (exclusive)', 'example' => null],
        ];
    }
}
