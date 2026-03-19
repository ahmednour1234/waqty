<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Helpers\ApiResponse;
use App\Http\Requests\Provider\StoreShiftTemplateRequest;
use App\Http\Requests\Provider\ToggleShiftTemplateActiveRequest;
use App\Http\Requests\Provider\UpdateShiftTemplateRequest;
use App\Http\Resources\Provider\ProviderShiftTemplateResource;
use App\Services\ProviderShiftTemplateService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Subgroup;

#[Group('Provider')]
#[Subgroup('Shift Templates', 'Manage reusable shift time templates')]
class ProviderShiftTemplateController extends Controller
{
    public function __construct(private ProviderShiftTemplateService $service) {}

    /**
     * List shift templates.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = [
                'active' => $request->has('active') ? filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN) : null,
                'search' => $request->input('search'),
            ];
            $perPage   = (int) $request->input('per_page', 15);
            $paginated = $this->service->index(array_filter($filters, fn($v) => $v !== null), $perPage);

            return ApiResponse::success(
                ProviderShiftTemplateResource::collection($paginated->items()),
                null, 200,
                ['pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'per_page'     => $paginated->perPage(),
                    'total'        => $paginated->total(),
                    'last_page'    => $paginated->lastPage(),
                ]]
            );
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Create a shift template.
     */
    public function store(StoreShiftTemplateRequest $request): JsonResponse
    {
        try {
            $template = $this->service->store($request->validated());
            return ApiResponse::success(new ProviderShiftTemplateResource($template), 'api.shift_templates.created', 201);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Show a shift template.
     */
    public function show(string $uuid): JsonResponse
    {
        try {
            return ApiResponse::success(new ProviderShiftTemplateResource($this->service->show($uuid)));
        } catch (ModelNotFoundException) {
            return ApiResponse::error('api.shift_templates.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Update a shift template.
     */
    public function update(UpdateShiftTemplateRequest $request, string $uuid): JsonResponse
    {
        try {
            $template = $this->service->update($uuid, $request->validated());
            return ApiResponse::success(new ProviderShiftTemplateResource($template), 'api.shift_templates.updated');
        } catch (ModelNotFoundException) {
            return ApiResponse::error('api.shift_templates.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Delete a shift template.
     */
    public function destroy(string $uuid): JsonResponse
    {
        try {
            $this->service->destroy($uuid);
            return ApiResponse::success(null, 'api.shift_templates.deleted');
        } catch (ModelNotFoundException) {
            return ApiResponse::error('api.shift_templates.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }

    /**
     * Toggle active status of a shift template.
     */
    public function toggleActive(ToggleShiftTemplateActiveRequest $request, string $uuid): JsonResponse
    {
        try {
            $active   = filter_var($request->validated('active'), FILTER_VALIDATE_BOOLEAN);
            $template = $this->service->toggleActive($uuid, $active);
            return ApiResponse::success(new ProviderShiftTemplateResource($template), 'api.shift_templates.updated');
        } catch (ModelNotFoundException) {
            return ApiResponse::error('api.shift_templates.not_found', 404);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
