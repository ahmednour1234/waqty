<?php

namespace App\Http\Requests\Provider;

use App\Http\Requests\BaseRequest;

class SyncPricingGroupEmployeesRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'employee_uuids'   => ['required', 'array', 'min:0'],
            'employee_uuids.*' => ['required', 'string', 'max:26'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'employee_uuids' => [
                'description' => 'List of employee UUIDs to sync/add/remove. Pass an empty array to remove all members.',
                'example'     => ['01JQXYZ1234567890ABCDEFGH', '01JQXYZ9876543210ZYXWVUTS'],
            ],
        ];
    }
}
