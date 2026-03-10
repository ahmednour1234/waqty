<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\PublicSubcategoryResource;
use App\Http\Helpers\ApiResponse;
use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Public')]
#[Subgroup('Subcategories', 'List subcategories')]
class PublicSubcategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Subcategory::where('active', true)
                ->whereNull('deleted_at')
                ->orderBy('sort_order');

            if ($request->has('category_uuid')) {
                $category = Category::whereUuid($request->input('category_uuid'))
                    ->where('active', true)
                    ->whereNull('deleted_at')
                    ->first();

                if ($category) {
                    $query->where('category_id', $category->id);
                } else {
                    return ApiResponse::success([], null, 200, ['pagination' => ['current_page' => 1, 'per_page' => 15, 'total' => 0, 'last_page' => 1]]);
                }
            }

            $perPage = (int) $request->input('per_page', 15);
            $subcategories = $query->paginate($perPage);

            return ApiResponse::success(
                PublicSubcategoryResource::collection($subcategories->items()),
                null,
                200,
                [
                    'pagination' => [
                        'current_page' => $subcategories->currentPage(),
                        'per_page' => $subcategories->perPage(),
                        'total' => $subcategories->total(),
                        'last_page' => $subcategories->lastPage(),
                    ],
                ]
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
