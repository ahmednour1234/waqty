<?php

namespace App\Http\Helpers;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    public static function success($data = null, ?string $message = null, int $status = 200, $meta = null): JsonResponse
    {
        $response = [
            'success' => true,
        ];

        if ($message !== null) {
            $response['message'] = __($message);
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        if ($meta !== null) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    public static function error(string $message, int $status = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => __($message),
        ];

        if ($errors !== null) {
            $localizedErrors = [];
            foreach ($errors as $key => $errorMessages) {
                if (is_array($errorMessages)) {
                    $localizedErrors[$key] = array_map(fn($msg) => __($msg), $errorMessages);
                } else {
                    $localizedErrors[$key] = __($errorMessages);
                }
            }
            $response['errors'] = $localizedErrors;
        }

        return response()->json($response, $status);
    }
}
