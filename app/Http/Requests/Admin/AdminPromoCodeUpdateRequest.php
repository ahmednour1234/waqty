<?php

namespace App\Http\Requests\Admin;

use App\Models\PromoCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminPromoCodeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'         => ['sometimes', 'string', 'max:50', 'alpha_dash'],
            'type'         => ['sometimes', Rule::in(PromoCode::TYPES)],
            'value'        => ['sometimes', 'numeric', 'min:0.01'],
            'min_order'    => ['nullable', 'numeric', 'min:0'],
            'max_discount' => ['nullable', 'numeric', 'min:0'],
            'usage_limit'  => ['nullable', 'integer', 'min:1'],
            'valid_until'  => ['sometimes', 'date', 'after_or_equal:today'],
            'active'       => ['sometimes', 'boolean'],
        ];
    }
}
