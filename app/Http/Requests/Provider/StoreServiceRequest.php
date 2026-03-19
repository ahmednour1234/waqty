<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => ['required', 'array'],
            'name.ar'            => ['required', 'string', 'max:255'],
            'name.en'            => ['required', 'string', 'max:255'],
            'description'        => ['required', 'array'],
            'description.ar'     => ['required', 'string', 'max:1000'],
            'description.en'     => ['required', 'string', 'max:1000'],
            'sub_category_uuid'  => ['required', 'string', 'exists:subcategories,uuid'],
            'image'              => [
                'nullable',
                'file',
                'mimes:jpeg,png,webp',
                'max:2048',
            ],
            'active'             => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.ar.required'          => __('api.general.validation_failed'),
            'name.en.required'          => __('api.general.validation_failed'),
            'description.ar.required'   => __('api.general.validation_failed'),
            'description.en.required'   => __('api.general.validation_failed'),
            'sub_category_uuid.exists'  => __('api.general.not_found'),
            'image.mimes'               => __('api.upload.invalid_file_type'),
            'image.max'                 => __('api.upload.image_too_large'),
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name.ar' => [
                'description' => 'Service name in Arabic',
                'required' => true,
                'example' => 'تنظيف منازل',
            ],
            'name.en' => [
                'description' => 'Service name in English',
                'required' => true,
                'example' => 'Home Cleaning',
            ],
            'description.ar' => [
                'description' => 'Service description in Arabic',
                'required' => true,
                'example' => 'خدمة تنظيف شاملة للمنازل.',
            ],
            'description.en' => [
                'description' => 'Service description in English',
                'required' => true,
                'example' => 'Comprehensive home cleaning service.',
            ],
            'sub_category_uuid' => [
                'description' => 'Subcategory UUID',
                'required' => true,
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
        ];
    }
}
