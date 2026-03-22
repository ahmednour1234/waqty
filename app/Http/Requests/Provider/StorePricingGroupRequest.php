<?php

namespace App\Http\Requests\Provider;

use App\Http\Requests\BaseRequest;

class StorePricingGroupRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name'             => ['required', 'array'],
            'name.ar'          => ['required', 'string', 'max:255'],
            'name.en'          => ['required', 'string', 'max:255'],
            'active'           => ['nullable', 'boolean'],
            'employee_uuids'   => ['nullable', 'array'],
            'employee_uuids.*' => ['required', 'string', 'max:26'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'name'           => ['description' => 'Localized name of the pricing group', 'example' => ['ar' => 'مجموعة VIP', 'en' => 'VIP Group']],
            'name.ar'        => ['description' => 'Arabic name', 'example' => 'مجموعة VIP'],
            'name.en'        => ['description' => 'English name', 'example' => 'VIP Group'],
            'active'         => ['description' => 'Whether the group is active', 'example' => true],
            'employee_uuids' => ['description' => 'UUIDs of employees to assign to this group (must belong to the authenticated provider)', 'example' => ['01JQXYZ1234567890ABCDEFGH']],
        ];
    }
}
