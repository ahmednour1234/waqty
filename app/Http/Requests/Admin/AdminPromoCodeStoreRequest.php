<?php

namespace App\Http\Requests\Admin;

use App\Models\PromoCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminPromoCodeStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'         => ['required', 'string', 'max:50', 'alpha_dash'],
            'type'         => ['nullable', Rule::in(PromoCode::TYPES)],
            'value'        => ['required', 'numeric', 'min:0.01'],
            'min_order'    => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit'  => ['nullable', 'integer', 'min:1'],
            'valid_until'  => ['required', 'date', 'after_or_equal:today'],
            'active'       => ['nullable', 'boolean'],
        ];
    }
}
