<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\Public\PublicGovernorateResource;
use App\Http\Helpers\ApiResponse;
use App\Models\Governorate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Public')]
#[Subgroup('Governorates', 'List and show governorates')]
class PublicGovernorateController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $governorates = Governorate::where('active', true)
                ->whereNull('deleted_at')
                ->orderBy('sort_order')
                ->orderBy('created_at', 'desc')
                ->get();

            return ApiResponse::success(PublicGovernorateResource::collection($governorates));
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    public function show(string $uuid): JsonResponse
    {
        try {
            $governorate = Governorate::whereUuid($uuid)
                ->where('active', true)
                ->whereNull('deleted_at')
                ->with(['cities' => function ($query) {
                    $query->where('active', true)->whereNull('deleted_at')->orderBy('sort_order');
                }])
                ->first();

            if (!$governorate) {
                return ApiResponse::error('api.general.not_found', 404);
            }

            return ApiResponse::success(new PublicGovernorateResource($governorate));
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
