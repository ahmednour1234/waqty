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
}
