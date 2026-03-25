<?php

namespace App\Http\Requests\Provider;

use Illuminate\Foundation\Http\FormRequest;

class BulkAttachServicesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'services'          => ['required', 'array', 'min:1'],
            'services.*.uuid'   => ['sometimes', 'nullable', 'string', 'exists:services,uuid'],
            'services.*.name_ar' => ['sometimes', 'nullable', 'string', 'max:255'],
            'services.*.name_en' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($v) {
            foreach ((array) $this->input('services', []) as $i => $item) {
                $hasUuid = !empty($item['uuid']);
                $hasName = !empty($item['name_ar']) || !empty($item['name_en']);

                if (!$hasUuid && !$hasName) {
                    $v->errors()->add(
                        "services.{$i}",
                        __('validation.custom.service_item_required')
                    );
                }

                if (!$hasUuid && $hasName) {
                    if (empty($item['name_ar'])) {
                        $v->errors()->add("services.{$i}.name_ar", __('validation.required', ['attribute' => 'name_ar']));
                    }
                    if (empty($item['name_en'])) {
                        $v->errors()->add("services.{$i}.name_en", __('validation.required', ['attribute' => 'name_en']));
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'services.required'         => __('api.general.validation_failed'),
            'services.array'            => __('api.general.validation_failed'),
            'services.min'              => __('api.general.validation_failed'),
            'services.*.uuid.exists'    => __('api.general.not_found'),
            'services.*.name_ar.max'    => __('api.general.validation_failed'),
            'services.*.name_en.max'    => __('api.general.validation_failed'),
        ];
    }

    /**
     * Scribe body parameter metadata.
     */
    public function bodyParameters(): array
    {
        return [
            'services' => [
                'description' => 'Array of service payloads. Each item must contain either uuid, or both name_ar and name_en.',
                'example' => [
                    ['uuid' => '01JABCDEF1234567890ABCDEFGH'],
                    ['name_ar' => 'خدمة جديدة', 'name_en' => 'New Service'],
                ],
            ],
            'services.*.uuid' => [
                'description' => 'Existing service UUID to attach.',
                'example' => '01JABCDEF1234567890ABCDEFGH',
            ],
            'services.*.name_ar' => [
                'description' => 'Arabic service name (required with name_en when uuid is not provided).',
                'example' => 'قص شعر',
            ],
            'services.*.name_en' => [
                'description' => 'English service name (required with name_ar when uuid is not provided).',
                'example' => 'Hair Cut',
            ],
        ];
    }
}
