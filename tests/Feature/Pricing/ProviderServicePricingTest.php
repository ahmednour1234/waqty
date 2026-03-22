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

class ProviderServicePricingTest extends TestCase
{
    use RefreshDatabase;

    private Provider $provider;
    private string $token;
    private Country $country;
    private City $city;
    private ProviderBranch $branch;
    private Employee $employee;
    private Service $service;
    private Category $category;
    private Subcategory $subcategory;

    protected function setUp(): void
    {
        parent::setUp();

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

        $response = $this->postJson('/api/provider/auth/login', [
            'email'    => 'provider@test.com',
            'password' => 'password123',
        ]);
        $this->token = $this->getTokenFromLoginResponse($response);

        $this->branch = ProviderBranch::create([
            'provider_id' => $this->provider->id,
            'name'        => 'Test Branch',
            'country_id'  => $this->country->id,
            'city_id'     => $this->city->id,
            'status'      => 'active',
        ]);

        $this->employee = Employee::create([
            'provider_id' => $this->provider->id,
            'branch_id'   => $this->branch->id,
            'name'        => 'Test Employee',
            'email'       => 'employee@test.com',
            'password'    => 'password123',
            'active'      => true,
            'blocked'     => false,
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

        // Attach service to provider
        $this->provider->services()->attach($this->service->id, ['active' => true]);
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_provider_can_list_own_service_prices(): void
    {
        ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'price'       => '50.00',
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/provider/service-prices');

        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data'])
            ->assertJson(['success' => true]);
    }

    public function test_provider_cannot_access_service_prices_unauthenticated(): void
    {
        $this->getJson('/api/provider/service-prices')->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Store – default scope
    // -------------------------------------------------------------------------

    public function test_provider_can_create_default_price(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/provider/service-prices', [
                'service_uuid' => $this->service->uuid,
                'price'        => '75.00',
                'active'       => true,
            ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.scope_type', 'default')
            ->assertJsonPath('data.price', '75.00');

        $this->assertDatabaseHas('service_prices', [
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'branch_id'   => null,
            'employee_id' => null,
            'pricing_group_id' => null,
            'price'       => '75.00',
        ]);
    }

    // -------------------------------------------------------------------------
    // Store – branch scope
    // -------------------------------------------------------------------------

    public function test_provider_can_create_branch_price(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/provider/service-prices', [
                'service_uuid' => $this->service->uuid,
                'branch_uuid'  => $this->branch->uuid,
                'price'        => '60.00',
                'active'       => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.scope_type', 'branch');

        $this->assertDatabaseHas('service_prices', [
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'branch_id'   => $this->branch->id,
            'price'       => '60.00',
        ]);
    }

    // -------------------------------------------------------------------------
    // Store – employee scope
    // -------------------------------------------------------------------------

    public function test_provider_can_create_employee_price(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/provider/service-prices', [
                'service_uuid'  => $this->service->uuid,
                'employee_uuid' => $this->employee->uuid,
                'price'         => '80.00',
                'active'        => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.scope_type', 'employee');

        $this->assertDatabaseHas('service_prices', [
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'employee_id' => $this->employee->id,
            'price'       => '80.00',
        ]);
    }

    // -------------------------------------------------------------------------
    // Store – group scope
    // -------------------------------------------------------------------------

    public function test_provider_can_create_group_price(): void
    {
        $group = PricingGroup::create([
            'provider_id' => $this->provider->id,
            'name'        => ['ar' => 'مجموعة', 'en' => 'Group'],
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/provider/service-prices', [
                'service_uuid'       => $this->service->uuid,
                'pricing_group_uuid' => $group->uuid,
                'price'              => '55.00',
                'active'             => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.scope_type', 'group');

        $this->assertDatabaseHas('service_prices', [
            'provider_id'      => $this->provider->id,
            'service_id'       => $this->service->id,
            'pricing_group_id' => $group->id,
            'price'            => '55.00',
        ]);
    }

    // -------------------------------------------------------------------------
    // Store – security
    // -------------------------------------------------------------------------

    public function test_provider_cannot_price_service_not_attached_to_them(): void
    {
        $otherService = Service::create([
            'sub_category_id' => $this->subcategory->id,
            'name'            => ['ar' => 'خدمة أخرى', 'en' => 'Other Service'],
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/provider/service-prices', [
                'service_uuid' => $otherService->uuid,
                'price'        => '50.00',
            ]);

        $response->assertStatus(422);
    }

    public function test_provider_cannot_use_another_providers_branch(): void
    {
        $other = Provider::create([
            'name'       => 'Other Provider',
            'email'      => 'other@test.com',
            'password'   => 'password123',
            'country_id' => $this->country->id,
            'city_id'    => $this->city->id,
            'active'     => true,
            'blocked'    => false,
            'banned'     => false,
        ]);

        $otherBranch = ProviderBranch::create([
            'provider_id' => $other->id,
            'name'        => 'Other Branch',
            'country_id'  => $this->country->id,
            'city_id'     => $this->city->id,
            'status'      => 'active',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/provider/service-prices', [
                'service_uuid' => $this->service->uuid,
                'branch_uuid'  => $otherBranch->uuid,
                'price'        => '50.00',
            ]);

        $response->assertStatus(422);
    }

    public function test_provider_cannot_use_another_providers_employee(): void
    {
        $other = Provider::create([
            'name'       => 'Other Provider',
            'email'      => 'other2@test.com',
            'password'   => 'password123',
            'country_id' => $this->country->id,
            'city_id'    => $this->city->id,
            'active'     => true,
            'blocked'    => false,
            'banned'     => false,
        ]);

        $otherBranch = ProviderBranch::create([
            'provider_id' => $other->id,
            'name'        => 'Other Branch',
            'country_id'  => $this->country->id,
            'city_id'     => $this->city->id,
            'status'      => 'active',
        ]);

        $otherEmployee = Employee::create([
            'provider_id' => $other->id,
            'branch_id'   => $otherBranch->id,
            'name'        => 'Other Employee',
            'email'       => 'other-emp@test.com',
            'password'    => 'password123',
            'active'      => true,
            'blocked'     => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/provider/service-prices', [
                'service_uuid'  => $this->service->uuid,
                'employee_uuid' => $otherEmployee->uuid,
                'price'         => '50.00',
            ]);

        $response->assertStatus(422);
    }

    public function test_provider_cannot_set_multiple_scopes_at_once(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/provider/service-prices', [
                'service_uuid'  => $this->service->uuid,
                'branch_uuid'   => $this->branch->uuid,
                'employee_uuid' => $this->employee->uuid,
                'price'         => '50.00',
            ]);

        $response->assertStatus(422);
    }

    public function test_duplicate_default_price_is_rejected(): void
    {
        ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'price'       => '50.00',
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/provider/service-prices', [
                'service_uuid' => $this->service->uuid,
                'price'        => '60.00',
            ]);

        $response->assertStatus(422);
    }

    public function test_duplicate_branch_price_is_rejected(): void
    {
        ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'branch_id'   => $this->branch->id,
            'price'       => '50.00',
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/provider/service-prices', [
                'service_uuid' => $this->service->uuid,
                'branch_uuid'  => $this->branch->uuid,
                'price'        => '60.00',
            ]);

        $response->assertStatus(422);
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_provider_can_show_own_price(): void
    {
        $price = ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'price'       => '50.00',
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/provider/service-prices/' . $price->uuid);

        $response->assertStatus(200)
            ->assertJsonPath('data.uuid', $price->uuid);
    }

    public function test_provider_cannot_show_another_providers_price(): void
    {
        $other = Provider::create([
            'name'       => 'Other Provider',
            'email'      => 'other3@test.com',
            'password'   => 'password123',
            'country_id' => $this->country->id,
            'city_id'    => $this->city->id,
            'active'     => true,
            'blocked'    => false,
            'banned'     => false,
        ]);

        $otherService = Service::create([
            'sub_category_id' => $this->subcategory->id,
            'name'            => ['ar' => 'خدمة أخرى', 'en' => 'Other Service'],
        ]);
        $other->services()->attach($otherService->id, ['active' => true]);

        $price = ServicePrice::create([
            'provider_id' => $other->id,
            'service_id'  => $otherService->id,
            'price'       => '50.00',
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/provider/service-prices/' . $price->uuid);

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_provider_can_update_price(): void
    {
        $price = ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'price'       => '50.00',
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/provider/service-prices/' . $price->uuid, [
                'price' => '99.00',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.price', '99.00');

        $this->assertDatabaseHas('service_prices', [
            'id'    => $price->id,
            'price' => '99.00',
        ]);
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_provider_can_delete_price(): void
    {
        $price = ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'price'       => '50.00',
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/provider/service-prices/' . $price->uuid);

        $response->assertStatus(200);

        $this->assertSoftDeleted('service_prices', ['id' => $price->id]);
    }

    // -------------------------------------------------------------------------
    // Toggle active
    // -------------------------------------------------------------------------

    public function test_provider_can_toggle_price_active(): void
    {
        $price = ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'price'       => '50.00',
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson('/api/provider/service-prices/' . $price->uuid . '/active');

        $response->assertStatus(200);

        $this->assertDatabaseHas('service_prices', [
            'id'     => $price->id,
            'active' => false,
        ]);
    }
}
