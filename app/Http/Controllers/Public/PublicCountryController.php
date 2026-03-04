<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\PublicCountryResource;
use App\Http\Helpers\ApiResponse;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicCountryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->input('per_page', 15);

            $countries = Country::where('active', true)
                ->whereNull('deleted_at')
                ->orderBy('sort_order')
                ->orderBy('created_at', 'desc')
                ->with(['cities' => function ($query) {
                    $query->where('active', true)->whereNull('deleted_at')->orderBy('sort_order');
                }])
                ->paginate($perPage);

            return ApiResponse::success(
                PublicCountryResource::collection($countries->items()),
                null,
                200,
                [
                    'pagination' => [
                        'current_page' => $countries->currentPage(),
                        'per_page' => $countries->perPage(),
                        'total' => $countries->total(),
                        'last_page' => $countries->lastPage(),
                    ],
                ]
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function show(string $uuid): JsonResponse
    {
        try {
            $country = Country::whereUuid($uuid)
                ->where('active', true)
                ->whereNull('deleted_at')
                ->with(['cities' => function ($query) {
                    $query->where('active', true)->whereNull('deleted_at')->orderBy('sort_order');
                }])
                ->first();

            if (!$country) {
                return ApiResponse::error('api.general.not_found', 404);
            }

            return ApiResponse::success(new PublicCountryResource($country));
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
