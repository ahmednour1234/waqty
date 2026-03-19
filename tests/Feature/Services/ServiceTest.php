<?php

namespace Tests\Feature\Services;

use App\Models\Category;
use App\Models\Employee;
use App\Models\Provider;
use App\Models\Service;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    private Provider $provider;
    private Provider $otherProvider;
    private Employee $employee;
    private Subcategory $subCategory;
    private string $providerToken;
    private string $otherProviderToken;
    private string $employeeToken;
    private string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $category = Category::create([
            'name'   => ['ar' => 'فئة', 'en' => 'Category'],
            'active' => true,
        ]);

        $this->subCategory = Subcategory::create([
            'category_id' => $category->id,
            'name'        => ['ar' => 'قسم فرعي', 'en' => 'Sub Category'],
            'active'      => true,
        ]);

        $this->provider = Provider::create([
            'name'     => 'Provider One',
            'email'    => 'provider1@test.com',
            'password' => 'password',
            'active'   => true,
            'blocked'  => false,
            'banned'   => false,
        ]);

        $this->otherProvider = Provider::create([
            'name'     => 'Provider Two',
            'email'    => 'provider2@test.com',
            'password' => 'password',
            'active'   => true,
            'blocked'  => false,
            'banned'   => false,
        ]);

        $this->employee = Employee::create([
            'provider_id' => $this->provider->id,
            'name'        => 'Employee One',
            'email'       => 'employee1@test.com',
            'password'    => 'password',
            'active'      => true,
            'blocked'     => false,
        ]);

        $this->providerToken = $this->getTokenFromLoginResponse(
            $this->postJson('/api/provider/auth/login', [
                'email'    => 'provider1@test.com',
                'password' => 'password',
            ])
        );

        $this->otherProviderToken = $this->getTokenFromLoginResponse(
            $this->postJson('/api/provider/auth/login', [
                'email'    => 'provider2@test.com',
                'password' => 'password',
            ])
        );

        $this->employeeToken = $this->getTokenFromLoginResponse(
            $this->postJson('/api/employee/auth/login', [
                'email'    => 'employee1@test.com',
                'password' => 'password',
            ])
        );
    }

    // ─── PROVIDER CREATES OWN SERVICE ──────────────────────────────────────────

    public function test_provider_can_create_service(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->providerToken)
            ->postJson('/api/provider/services', [
                'name'               => ['ar' => 'خدمة تجريبية', 'en' => 'Test Service'],
                'sub_category_uuid'  => $this->subCategory->uuid,
                'active'             => true,
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['success', 'data' => ['uuid', 'name', 'active']]);
        $this->assertDatabaseHas('services', [
            'provider_id'     => $this->provider->id,
            'sub_category_id' => $this->subCategory->id,
        ]);
    }

    public function test_store_requires_both_name_locales(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->providerToken)
            ->postJson('/api/provider/services', [
                'name'              => ['ar' => 'خدمة'],
                'sub_category_uuid' => $this->subCategory->uuid,
            ]);

        $response->assertStatus(422);
    }

    // ─── PROVIDER CANNOT ACCESS ANOTHER PROVIDER'S SERVICE ─────────────────────

    public function test_provider_cannot_view_another_providers_service(): void
    {
        $service = Service::create([
            'provider_id'     => $this->otherProvider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'خدمة أخرى', 'en' => 'Other Service'],
            'active'          => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->providerToken)
            ->getJson("/api/provider/services/{$service->uuid}");

        $response->assertStatus(403);
    }

    public function test_provider_cannot_update_another_providers_service(): void
    {
        $service = Service::create([
            'provider_id'     => $this->otherProvider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'خدمة أخرى', 'en' => 'Other Service'],
            'active'          => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->providerToken)
            ->putJson("/api/provider/services/{$service->uuid}", [
                'name' => ['ar' => 'اسم جديد', 'en' => 'New Name'],
            ]);

        $response->assertStatus(403);
    }

    public function test_provider_cannot_delete_another_providers_service(): void
    {
        $service = Service::create([
            'provider_id'     => $this->otherProvider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'خدمة أخرى', 'en' => 'Other Service'],
            'active'          => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->providerToken)
            ->deleteJson("/api/provider/services/{$service->uuid}");

        $response->assertStatus(403);
    }

    // ─── EMPLOYEE CAN ONLY VIEW OWN PROVIDER SERVICES ──────────────────────────

    public function test_employee_can_list_own_provider_services(): void
    {
        Service::create([
            'provider_id'     => $this->provider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'خدمتي', 'en' => 'My Service'],
            'active'          => true,
        ]);

        Service::create([
            'provider_id'     => $this->otherProvider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'خدمة أخرى', 'en' => 'Other Service'],
            'active'          => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->employeeToken)
            ->getJson('/api/employee/services');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    public function test_employee_cannot_view_other_provider_service(): void
    {
        $service = Service::create([
            'provider_id'     => $this->otherProvider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'خدمة أخرى', 'en' => 'Other Service'],
            'active'          => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->employeeToken)
            ->getJson("/api/employee/services/{$service->uuid}");

        $response->assertStatus(403);
    }

    // ─── PUBLIC SHOWS ONLY ACTIVE / NON-DELETED SERVICES ────────────────────────

    public function test_public_lists_only_active_services(): void
    {
        Service::create([
            'provider_id'     => $this->provider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'نشطة', 'en' => 'Active'],
            'active'          => true,
        ]);

        Service::create([
            'provider_id'     => $this->provider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'غير نشطة', 'en' => 'Inactive'],
            'active'          => false,
        ]);

        $response = $this->getJson('/api/public/services');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.name', 'Active');
    }

    public function test_public_hides_soft_deleted_services(): void
    {
        $service = Service::create([
            'provider_id'     => $this->provider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'محذوفة', 'en' => 'Deleted'],
            'active'          => true,
        ]);
        $service->delete();

        $response = $this->getJson('/api/public/services');

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    }

    public function test_public_hides_services_of_blocked_provider(): void
    {
        $blockedProvider = Provider::create([
            'name'     => 'Blocked Provider',
            'email'    => 'blocked@test.com',
            'password' => 'password',
            'active'   => true,
            'blocked'  => true,
            'banned'   => false,
        ]);

        Service::create([
            'provider_id'     => $blockedProvider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'خدمة محظورة', 'en' => 'Blocked Service'],
            'active'          => true,
        ]);

        $response = $this->getJson('/api/public/services');

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    }

    // ─── FILTER: sub_category_uuid ──────────────────────────────────────────────

    public function test_public_filter_by_sub_category_uuid(): void
    {
        $otherSub = Subcategory::create([
            'category_id' => $this->subCategory->category_id,
            'name'        => ['ar' => 'قسم آخر', 'en' => 'Other Sub'],
            'active'      => true,
        ]);

        Service::create([
            'provider_id'     => $this->provider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'خدمة 1', 'en' => 'Service 1'],
            'active'          => true,
        ]);

        Service::create([
            'provider_id'     => $this->provider->id,
            'sub_category_id' => $otherSub->id,
            'name'            => ['ar' => 'خدمة 2', 'en' => 'Service 2'],
            'active'          => true,
        ]);

        $response = $this->getJson('/api/public/services?sub_category_uuid=' . $this->subCategory->uuid);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.sub_category_uuid', $this->subCategory->uuid);
    }

    // ─── FILTER: provider_uuid ──────────────────────────────────────────────────

    public function test_public_filter_by_provider_uuid(): void
    {
        Service::create([
            'provider_id'     => $this->provider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'خدمة موفر 1', 'en' => 'Provider 1 Service'],
            'active'          => true,
        ]);

        Service::create([
            'provider_id'     => $this->otherProvider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'خدمة موفر 2', 'en' => 'Provider 2 Service'],
            'active'          => true,
        ]);

        $response = $this->getJson('/api/public/services?provider_uuid=' . $this->provider->uuid);

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.provider_uuid', $this->provider->uuid);
    }

    // ─── IMAGE UPLOAD SECURITY ───────────────────────────────────────────────────

    public function test_image_upload_rejects_svg(): void
    {
        $svg = UploadedFile::fake()->createWithContent(
            'test.svg',
            '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'
        );

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->providerToken)
            ->postJson('/api/provider/services', [
                'name'              => ['ar' => 'خدمة', 'en' => 'Service'],
                'sub_category_uuid' => $this->subCategory->uuid,
                'image'             => $svg,
            ]);

        $response->assertStatus(422);
    }

    public function test_image_upload_rejects_fake_mime(): void
    {
        // PHP file renamed to .jpg (fake mime type)
        $fakeImage = UploadedFile::fake()->createWithContent(
            'shell.jpg',
            '<?php system($_GET["cmd"]); ?>'
        );

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->providerToken)
            ->postJson('/api/provider/services', [
                'name'              => ['ar' => 'خدمة', 'en' => 'Service'],
                'sub_category_uuid' => $this->subCategory->uuid,
                'image'             => $fakeImage,
            ]);

        $response->assertStatus(422);
    }

    public function test_image_upload_accepts_valid_webp(): void
    {
        $image = UploadedFile::fake()->image('photo.jpg', 100, 100);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->providerToken)
            ->postJson('/api/provider/services', [
                'name'              => ['ar' => 'خدمة', 'en' => 'Service'],
                'sub_category_uuid' => $this->subCategory->uuid,
                'image'             => $image,
            ]);

        $response->assertStatus(201);
        $this->assertNotNull(Service::first()->image_path);
    }

    // ─── LOCALIZATION ───────────────────────────────────────────────────────────

    public function test_public_returns_arabic_name_with_ar_header(): void
    {
        Service::create([
            'provider_id'     => $this->provider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'خدمة عربية', 'en' => 'Arabic Service'],
            'active'          => true,
        ]);

        $response = $this->withHeader('Accept-Language', 'ar')
            ->getJson('/api/public/services');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.name', 'خدمة عربية');
    }

    public function test_public_returns_english_name_with_en_header(): void
    {
        Service::create([
            'provider_id'     => $this->provider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'خدمة عربية', 'en' => 'English Service'],
            'active'          => true,
        ]);

        $response = $this->withHeader('Accept-Language', 'en')
            ->getJson('/api/public/services');

        $response->assertStatus(200);
        $response->assertJsonPath('data.0.name', 'English Service');
    }

    // ─── ADMIN CAN VIEW ALL AND SOFT DELETE/RESTORE ─────────────────────────────

    public function test_admin_can_view_all_services(): void
    {
        Service::create([
            'provider_id'     => $this->provider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'خدمة أ', 'en' => 'Service A'],
            'active'          => true,
        ]);

        Service::create([
            'provider_id'     => $this->otherProvider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'خدمة ب', 'en' => 'Service B'],
            'active'          => true,
        ]);

        $admin = \App\Models\Admin::create([
            'name'     => 'Admin',
            'email'    => 'admin@test.com',
            'password' => 'password',
            'active'   => true,
        ]);

        $loginResponse = $this->postJson('/api/admin/auth/login', [
            'email'    => 'admin@test.com',
            'password' => 'password',
        ]);
        $adminToken = $loginResponse->json('data.token');

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->getJson('/api/admin/services');

        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    public function test_admin_can_soft_delete_and_restore_service(): void
    {
        $service = Service::create([
            'provider_id'     => $this->provider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'خدمة', 'en' => 'Service'],
            'active'          => true,
        ]);

        $admin = \App\Models\Admin::create([
            'name'     => 'Admin',
            'email'    => 'admin@test.com',
            'password' => 'password',
            'active'   => true,
        ]);

        $loginResponse = $this->postJson('/api/admin/auth/login', [
            'email'    => 'admin@test.com',
            'password' => 'password',
        ]);
        $adminToken = $loginResponse->json('data.token');

        // Soft delete
        $deleteResponse = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->deleteJson("/api/admin/services/{$service->uuid}");
        $deleteResponse->assertStatus(200);
        $this->assertSoftDeleted('services', ['id' => $service->id]);

        // Restore
        $restoreResponse = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->postJson("/api/admin/services/{$service->uuid}/restore");
        $restoreResponse->assertStatus(200);
        $this->assertNotSoftDeleted('services', ['id' => $service->id]);
    }

    // ─── RESOURCES DIFFER BY CONTEXT ────────────────────────────────────────────

    public function test_provider_resource_does_not_include_timestamps_that_admin_sees(): void
    {
        Service::create([
            'provider_id'     => $this->provider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'خدمة', 'en' => 'Service'],
            'active'          => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->providerToken)
            ->getJson('/api/provider/services');

        $response->assertStatus(200);
        // Provider resource must NOT include deleted_at or provider_uuid
        $item = $response->json('data.0');
        $this->assertArrayNotHasKey('deleted_at', $item);
        $this->assertArrayNotHasKey('provider_uuid', $item);
        // But must include both locales of name
        $this->assertIsArray($item['name']);
    }

    public function test_employee_resource_returns_localized_name_string(): void
    {
        Service::create([
            'provider_id'     => $this->provider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'خدمة الموظف', 'en' => 'Employee Service'],
            'active'          => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->employeeToken)
            ->withHeader('Accept-Language', 'en')
            ->getJson('/api/employee/services');

        $response->assertStatus(200);
        $item = $response->json('data.0');
        // Employee resource: name is a localized STRING (not array)
        $this->assertIsString($item['name']);
        $this->assertEquals('Employee Service', $item['name']);
    }

    public function test_public_resource_does_not_expose_timestamps(): void
    {
        Service::create([
            'provider_id'     => $this->provider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'عام', 'en' => 'Public'],
            'active'          => true,
        ]);

        $response = $this->getJson('/api/public/services');

        $response->assertStatus(200);
        $item = $response->json('data.0');
        $this->assertArrayNotHasKey('created_at', $item);
        $this->assertArrayNotHasKey('updated_at', $item);
        $this->assertArrayNotHasKey('deleted_at', $item);
    }

    // ─── PROVIDER SOFT DELETE OWN SERVICE ───────────────────────────────────────

    public function test_provider_can_soft_delete_own_service(): void
    {
        $service = Service::create([
            'provider_id'     => $this->provider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'خدمة', 'en' => 'Service'],
            'active'          => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->providerToken)
            ->deleteJson("/api/provider/services/{$service->uuid}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('services', ['id' => $service->id]);
    }

    // ─── PROVIDER TOGGLE ACTIVE ──────────────────────────────────────────────────

    public function test_provider_can_toggle_service_active(): void
    {
        $service = Service::create([
            'provider_id'     => $this->provider->id,
            'sub_category_id' => $this->subCategory->id,
            'name'            => ['ar' => 'خدمة', 'en' => 'Service'],
            'active'          => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->providerToken)
            ->patchJson("/api/provider/services/{$service->uuid}/active", [
                'active' => false,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('services', ['id' => $service->id, 'active' => false]);
    }
}
