<?php

namespace App\Http\Requests\Provider;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class StoreServicePriceRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'service_uuid'       => ['required', 'string', 'max:26'],
            'price'              => ['required', 'numeric', 'min:0', 'regex:/^\d+(\.\d{1,2})?$/'],
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

    /**
     * @hideFromAPIDocumentation
     */
    public function bodyParameters(): array
    {
        return [
            'service_uuid'       => ['description' => 'UUID of the service to price', 'example' => '01JQXYZ1234567890ABCDEFGH'],
            'price'              => ['description' => 'Price value (decimal, min 0)', 'example' => '99.99'],
            'active'             => ['description' => 'Whether the price is active', 'example' => true],
            'branch_uuid'        => ['description' => 'Branch UUID for branch-specific price (exclusive with employee_uuid, pricing_group_uuid)', 'example' => null],
            'employee_uuid'      => ['description' => 'Employee UUID for employee-specific price (exclusive)', 'example' => null],
            'pricing_group_uuid' => ['description' => 'Pricing group UUID for group-specific price (exclusive)', 'example' => null],
        ];
    }
}
