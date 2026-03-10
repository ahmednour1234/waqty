<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\PublicCategoryResource;
use App\Http\Helpers\ApiResponse;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Public')]
#[Subgroup('Categories', 'List and show categories')]
class PublicCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->input('per_page', 15);

            $categories = Category::where('active', true)
                ->whereNull('deleted_at')
                ->orderBy('sort_order')
                ->orderBy('created_at', 'desc')
                ->with(['subcategories' => function ($query) {
                    $query->where('active', true)->whereNull('deleted_at')->orderBy('sort_order');
                }])
                ->paginate($perPage);

            return ApiResponse::success(
                PublicCategoryResource::collection($categories->items()),
                null,
                200,
                [
                    'pagination' => [
                        'current_page' => $categories->currentPage(),
                        'per_page' => $categories->perPage(),
                        'total' => $categories->total(),
                        'last_page' => $categories->lastPage(),
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
            $category = Category::whereUuid($uuid)
                ->where('active', true)
                ->whereNull('deleted_at')
                ->with(['subcategories' => function ($query) {
                    $query->where('active', true)->whereNull('deleted_at')->orderBy('sort_order');
                }])
                ->first();

            if (!$category) {
                return ApiResponse::error('api.general.not_found', 404);
            }

            return ApiResponse::success(new PublicCategoryResource($category));
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
