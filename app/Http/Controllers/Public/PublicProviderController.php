<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\PublicProviderResource;
use App\Http\Helpers\ApiResponse;
use App\Models\Provider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Public')]
#[Subgroup('Providers', 'List and show providers')]
class PublicProviderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->input('per_page', 15);

            $query = Provider::where('active', true)
                ->where('blocked', false)
                ->where('banned', false)
                ->whereNull('deleted_at')
                ->with(['category', 'city', 'mainBranch' => function ($q) {
                    $q->where('active', true)
                      ->where('blocked', false)
                      ->where('banned', false)
                      ->whereNull('deleted_at')
                      ->with('city');
                }]);

            if ($request->has('category_uuid')) {
                $category = \App\Models\Category::whereUuid($request->input('category_uuid'))->first();
                if ($category) {
                    $query->where('category_id', $category->id);
                }
            }

            if ($request->has('city_uuid')) {
                $city = \App\Models\City::whereUuid($request->input('city_uuid'))->first();
                if ($city) {
                    $query->where('city_id', $city->id);
                }
            }

            if ($request->has('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                });
            }

            $providers = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return ApiResponse::success(
                PublicProviderResource::collection($providers->items()),
                null,
                200,
                [
                    'pagination' => [
                        'current_page' => $providers->currentPage(),
                        'per_page' => $providers->perPage(),
                        'total' => $providers->total(),
                        'last_page' => $providers->lastPage(),
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
            $provider = Provider::whereUuid($uuid)
                ->where('active', true)
                ->where('blocked', false)
                ->where('banned', false)
                ->whereNull('deleted_at')
                ->with(['category', 'city', 'mainBranch' => function ($q) {
                    $q->where('active', true)
                      ->where('blocked', false)
                      ->where('banned', false)
                      ->whereNull('deleted_at')
                      ->with('city');
                }])
                ->first();

            if (!$provider) {
                return ApiResponse::error('api.general.not_found', 404);
            }

            return ApiResponse::success(new PublicProviderResource($provider));
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
