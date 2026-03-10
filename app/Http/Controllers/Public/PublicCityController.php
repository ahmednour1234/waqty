<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\PublicCityResource;
use App\Http\Helpers\ApiResponse;
use App\Models\City;
use App\Models\Country;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Public')]
#[Subgroup('Cities', 'List and show cities')]
class PublicCityController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->input('per_page', 15);
            $countryUuid = $request->input('country_uuid');

            $query = City::where('active', true)
                ->whereNull('deleted_at');

            if ($countryUuid) {
                $country = Country::whereUuid($countryUuid)->first();
                if ($country) {
                    $query->where('country_id', $country->id);
                }
            }

            $cities = $query->orderBy('sort_order')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return ApiResponse::success(
                PublicCityResource::collection($cities->items()),
                null,
                200,
                [
                    'pagination' => [
                        'current_page' => $cities->currentPage(),
                        'per_page' => $cities->perPage(),
                        'total' => $cities->total(),
                        'last_page' => $cities->lastPage(),
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
            $city = City::whereUuid($uuid)
                ->where('active', true)
                ->whereNull('deleted_at')
                ->first();

            if (!$city) {
                return ApiResponse::error('api.general.not_found', 404);
            }

            return ApiResponse::success(new PublicCityResource($city));
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
