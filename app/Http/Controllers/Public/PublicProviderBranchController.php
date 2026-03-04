<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\PublicProviderBranchResource;
use App\Http\Helpers\ApiResponse;
use App\Services\PublicProviderBranchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicProviderBranchController extends Controller
{
    public function __construct(
        private PublicProviderBranchService $branchService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'provider_uuid' => $request->input('provider_uuid'),
                'country_uuid' => $request->input('country_uuid'),
                'city_uuid' => $request->input('city_uuid'),
                'category_uuid' => $request->input('category_uuid'),
                'per_page' => (int) $request->input('per_page', 15),
            ];

            $paginated = $this->branchService->index($filters);

            return ApiResponse::success(
                PublicProviderBranchResource::collection($paginated->items()),
                null,
                200,
                [
                    'pagination' => [
                        'current_page' => $paginated->currentPage(),
                        'per_page' => $paginated->perPage(),
                        'total' => $paginated->total(),
                        'last_page' => $paginated->lastPage(),
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
            $branch = $this->branchService->show($uuid);
            return ApiResponse::success(
                new PublicProviderBranchResource($branch->load(['country', 'city']))
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::error('api.general.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
