<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\ProviderUpdateProfileRequest;
use App\Http\Resources\Provider\ProviderSelfResource;
use App\Http\Helpers\ApiResponse;
use App\Services\ProviderProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Header;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Response;

#[Group('Provider APIs')]
class ProviderProfileController extends Controller
{
    public function __construct(
        private ProviderProfileService $providerProfileService
    ) {
    }

    #[Header('Accept-Language', 'ar|en')]
    #[Header('Authorization', 'Bearer {token}')]
    #[Header('Content-Type', 'multipart/form-data')]
    #[BodyParam('name', 'string', 'Provider name', required: true, example: 'Provider Name')]
    #[BodyParam('phone', 'string', 'Phone number', required: false, example: '+1234567890')]
    #[BodyParam('category_id', 'integer', 'Category ID', required: false)]
    #[BodyParam('city_id', 'integer', 'City ID', required: true)]
    #[BodyParam('logo', 'file', 'Provider logo image (jpeg/png/webp, max 2MB, no SVG). MIME type validation rejects fake mimes.', required: false)]
    #[Response(['success' => true, 'message' => 'تم التحديث بنجاح', 'data' => ['uuid' => '<ULID>', 'name' => 'Provider Name']], 200)]
    #[Response(['success' => false, 'message' => 'نوع الملف غير مدعوم', 'errors' => ['logo' => ['يجب أن يكون الملف صورة']]], 400, 'Invalid file type (SVG rejected, fake MIME rejected)')]
    #[Response(['success' => false, 'message' => 'حجم الملف كبير جداً', 'errors' => ['logo' => ['الحد الأقصى للحجم 2MB']]], 400, 'File too large')]
    #[Response(['success' => false, 'message' => 'فشل التحقق'], 422)]
    #[Response(['success' => false, 'message' => 'غير مصرح'], 401)]
    #[Response(['success' => false, 'message' => 'الحساب غير نشط أو محظور أو محظور'], 403)]
    public function update(ProviderUpdateProfileRequest $request): JsonResponse
    {
        try {
            $provider = Auth::guard('provider')->user();
            $logo = $request->file('logo');

            $provider = $this->providerProfileService->updateProfile(
                $provider,
                $request->validated(),
                $logo
            );

            $provider->load(['category', 'country', 'city']);

            return ApiResponse::success(new ProviderSelfResource($provider), 'api.general.updated');
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error($e->getMessage(), 400);
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), 500);
        }
    }
}
