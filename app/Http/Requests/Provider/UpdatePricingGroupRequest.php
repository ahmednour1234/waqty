<?php

namespace App\Http\Requests\Provider;

use App\Http\Requests\BaseRequest;

class UpdatePricingGroupRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name'             => ['sometimes', 'required', 'array'],
            'name.ar'          => ['sometimes', 'required', 'string', 'max:255'],
            'name.en'          => ['sometimes', 'required', 'string', 'max:255'],
            'active'           => ['nullable', 'boolean'],
            'employee_uuids'   => ['nullable', 'array'],
            'employee_uuids.*' => ['required', 'string', 'max:26'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name'           => ['description' => 'Updated localized name', 'example' => ['ar' => 'مجموعة محدثة', 'en' => 'Updated Group']],
            'name.ar'        => ['description' => 'Updated Arabic name', 'example' => 'مجموعة محدثة'],
            'name.en'        => ['description' => 'Updated English name', 'example' => 'Updated Group'],
            'active'         => ['description' => 'Whether the group is active', 'example' => true],
            'employee_uuids' => ['description' => 'Updated employee UUIDs for this group', 'example' => ['01JQXYZ1234567890ABCDEFGH']],
        ];
    }
}
