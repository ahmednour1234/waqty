<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => ['sometimes', 'array'],
            'name.ar'            => ['sometimes', 'required', 'string', 'max:255'],
            'name.en'            => ['sometimes', 'required', 'string', 'max:255'],
            'description'        => ['sometimes', 'array'],
            'description.ar'     => ['sometimes', 'required', 'string', 'max:1000'],
            'description.en'     => ['sometimes', 'required', 'string', 'max:1000'],
            'sub_category_uuid'  => ['sometimes', 'required', 'string', 'exists:subcategories,uuid'],
            'image'              => [
                'nullable',
                'file',
                'mimes:jpeg,png,webp',
                'max:2048',
            ],
            'active'             => ['nullable', 'boolean'],
            'estimated_duration_minutes' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:1440'],
            'tax_enabled'                => ['sometimes', 'nullable', 'boolean'],
            'tax_percentage'             => ['sometimes', 'nullable', 'numeric', 'min:0', 'max:100', 'required_if:tax_enabled,true'],
        ];
    }

    public function messages(): array
    {
        return [
            'sub_category_uuid.exists' => __('api.general.not_found'),
            'image.mimes'              => __('api.upload.invalid_file_type'),
            'image.max'                => __('api.upload.image_too_large'),
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name.ar' => [
                'description' => 'Service name in Arabic',
                'required' => false,
                'example' => 'تنظيف منازل',
            ],
            'name.en' => [
                'description' => 'Service name in English',
                'required' => false,
                'example' => 'Home Cleaning',
            ],
            'description.ar' => [
                'description' => 'Service description in Arabic',
                'required' => false,
                'example' => 'خدمة تنظيف شاملة للمنازل.',
            ],
            'description.en' => [
                'description' => 'Service description in English',
                'required' => false,
                'example' => 'Comprehensive home cleaning service.',
            ],
            'sub_category_uuid' => [
                'description' => 'Subcategory UUID',
                'required' => false,
                'example' => '01JQ0G7F8H5P9M2W3X4Y5Z6A7B',
            ],
            'image' => [
                'description' => 'Service image (jpeg, png, webp, max 2MB)',
                'required' => false,
                'example' => null,
            ],
            'active' => [
                'description' => 'Service active status',
                'required' => false,
                'example' => true,
            ],
            'estimated_duration_minutes' => [
                'description' => 'Estimated service duration in minutes (e.g. 60 for one hour)',
                'required' => false,
                'example' => 60,
            ],
            'tax_enabled' => [
                'description' => 'Whether tax is applied to this service',
                'required' => false,
                'example' => false,
            ],
            'tax_percentage' => [
                'description' => 'Tax percentage (0–100). Required when tax_enabled is true.',
                'required' => false,
                'example' => 15,
            ],
        ];
    }
}
