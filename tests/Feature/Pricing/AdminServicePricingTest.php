<?php

namespace Tests\Feature\Pricing;

use App\Models\Admin;
use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Employee;
use App\Models\PricingGroup;
use App\Models\Provider;
use App\Models\ProviderBranch;
use App\Models\Service;
use App\Models\ServicePrice;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminServicePricingTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private string $adminToken;
    private Provider $provider;
    private Service $service;
    private ProviderBranch $branch;
    private Country $country;
    private City $city;
    private Category $category;
    private Subcategory $subcategory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name'     => 'Test Admin',
            'email'    => 'admin@test.com',
            'password' => 'password',
            'active'   => true,
        ]);

        $response = $this->postJson('/api/admin/auth/login', [
            'email'    => 'admin@test.com',
            'password' => 'password',
        ]);
        $this->adminToken = $this->getTokenFromLoginResponse($response);

        $this->country = Country::create([
            'name'       => ['ar' => 'مصر', 'en' => 'Egypt'],
            'iso2'       => 'EG',
            'phone_code' => '+20',
            'active'     => true,
        ]);

        $this->city = City::create([
            'country_id' => $this->country->id,
            'name'       => ['ar' => 'القاهرة', 'en' => 'Cairo'],
            'active'     => true,
        ]);

        $this->provider = Provider::create([
            'name'       => 'Test Provider',
            'email'      => 'provider@test.com',
            'password'   => 'password123',
            'country_id' => $this->country->id,
            'city_id'    => $this->city->id,
            'active'     => true,
            'blocked'    => false,
            'banned'     => false,
        ]);

        $this->branch = ProviderBranch::create([
            'provider_id' => $this->provider->id,
            'name'        => 'Test Branch',
            'country_id'  => $this->country->id,
            'city_id'     => $this->city->id,
            'status'      => 'active',
        ]);

        $this->category = Category::create([
            'name'       => ['ar' => 'فئة', 'en' => 'Category'],
            'slug'       => 'category',
            'active'     => true,
            'sort_order' => 1,
        ]);

        $this->subcategory = Subcategory::create([
            'category_id' => $this->category->id,
            'name'        => ['ar' => 'فئة فرعية', 'en' => 'Subcategory'],
            'slug'        => 'subcategory',
            'active'      => true,
            'sort_order'  => 1,
        ]);

        $this->service = Service::create([
            'sub_category_id' => $this->subcategory->id,
            'name'            => ['ar' => 'خدمة', 'en' => 'Service'],
        ]);

        $this->provider->services()->attach($this->service->id, ['active' => true]);
    }

    // -------------------------------------------------------------------------
    // Service prices list
    // -------------------------------------------------------------------------

    public function test_admin_can_list_all_service_prices(): void
    {
        ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'price'       => '50.00',
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/admin/service-prices');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
    }

    public function test_admin_service_prices_list_requires_auth(): void
    {
        $this->getJson('/api/admin/service-prices')->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Service price show
    // -------------------------------------------------------------------------

    public function test_admin_can_show_any_service_price(): void
    {
        $price = ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'price'       => '50.00',
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/admin/service-prices/' . $price->uuid);

        $response->assertStatus(200)
            ->assertJsonPath('data.uuid', $price->uuid)
            ->assertJsonStructure(['data' => ['uuid', 'provider_uuid', 'service_uuid', 'scope_type', 'price', 'active']]);
    }

    public function test_admin_response_includes_deleted_at_field(): void
    {
        $price = ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'price'       => '50.00',
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/admin/service-prices/' . $price->uuid);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['deleted_at']]);
    }

    // -------------------------------------------------------------------------
    // Pricing groups list
    // -------------------------------------------------------------------------

    public function test_admin_can_list_all_pricing_groups(): void
    {
        PricingGroup::create([
            'provider_id' => $this->provider->id,
            'name'        => ['ar' => 'مجموعة', 'en' => 'Group'],
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/admin/pricing-groups');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
    }

    public function test_admin_pricing_groups_list_requires_auth(): void
    {
        $this->getJson('/api/admin/pricing-groups')->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Pricing group show
    // -------------------------------------------------------------------------

    public function test_admin_can_show_any_pricing_group(): void
    {
        $group = PricingGroup::create([
            'provider_id' => $this->provider->id,
            'name'        => ['ar' => 'مجموعة', 'en' => 'Admin Group'],
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/admin/pricing-groups/' . $group->uuid);

        $response->assertStatus(200)
            ->assertJsonPath('data.uuid', $group->uuid)
            ->assertJsonStructure(['data' => ['uuid', 'provider_uuid', 'name', 'active', 'deleted_at']]);
    }

    // -------------------------------------------------------------------------
    // Admin cannot mutate pricing data (no write routes)
    // -------------------------------------------------------------------------

    public function test_admin_cannot_create_service_price(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/admin/service-prices', [
                'service_uuid' => $this->service->uuid,
                'price'        => '50.00',
            ]);

        // Route does not exist → 404 or 405
        $response->assertStatus(fn ($s) => $s === 404 || $s === 405);
    }

    public function test_admin_cannot_create_pricing_group(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->postJson('/api/admin/pricing-groups', [
                'name'   => ['ar' => 'مجموعة', 'en' => 'Group'],
                'active' => true,
            ]);

        $response->assertStatus(fn ($s) => $s === 404 || $s === 405);
    }

    // -------------------------------------------------------------------------
    // Soft-deleted prices visible to admin with trashed=only
    // -------------------------------------------------------------------------

    public function test_admin_can_view_soft_deleted_prices_with_trashed_filter(): void
    {
        $price = ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'price'       => '50.00',
            'active'      => true,
        ]);

        $price->delete();

        // Without trashed filter — should not appear
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/admin/service-prices');

        $uuids = collect($response->json('data'))->pluck('uuid')->toArray();
        $this->assertNotContains($price->uuid, $uuids);

        // With trashed=only — should appear
        $response2 = $this->withHeader('Authorization', 'Bearer ' . $this->adminToken)
            ->getJson('/api/admin/service-prices?trashed=only');

        $uuids2 = collect($response2->json('data'))->pluck('uuid')->toArray();
        $this->assertContains($price->uuid, $uuids2);
    }
}
