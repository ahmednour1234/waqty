<?php

namespace Tests\Feature\Pricing;

use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Employee;
use App\Models\Provider;
use App\Models\ProviderBranch;
use App\Models\Service;
use App\Models\ServicePrice;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicServicePricingTest extends TestCase
{
    use RefreshDatabase;

    private Provider $provider;
    private Service $service;
    private ProviderBranch $branch;
    private Country $country;
    private City $city;

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

        $this->branch = ProviderBranch::create([
            'provider_id' => $this->provider->id,
            'name'        => 'Test Branch',
            'country_id'  => $this->country->id,
            'city_id'     => $this->city->id,
            'status'      => 'active',
        ]);

        $category = Category::create([
            'name'       => ['ar' => 'فئة', 'en' => 'Category'],
            'slug'       => 'category',
            'active'     => true,
            'sort_order' => 1,
        ]);

        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name'        => ['ar' => 'فئة فرعية', 'en' => 'Subcategory'],
            'slug'        => 'subcategory',
            'active'      => true,
            'sort_order'  => 1,
        ]);

        $this->service = Service::create([
            'sub_category_id' => $subcategory->id,
            'name'            => ['ar' => 'خدمة', 'en' => 'Service'],
        ]);

        $this->provider->services()->attach($this->service->id, ['active' => true]);
    }

    // -------------------------------------------------------------------------
    // Basic resolution
    // -------------------------------------------------------------------------

    public function test_public_can_resolve_price_without_auth(): void
    {
        ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'price'       => '75.00',
            'active'      => true,
        ]);

        $response = $this->getJson('/api/public/service-pricing/services/' . $this->service->uuid . '/price');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.final_price', '75.00');
    }

    public function test_returns_404_when_no_price_configured(): void
    {
        $response = $this->getJson('/api/public/service-pricing/services/' . $this->service->uuid . '/price');

        $response->assertStatus(404);
    }

    public function test_returns_404_for_nonexistent_service(): void
    {
        $response = $this->getJson('/api/public/service-pricing/services/nonexistent-uuid/price');

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Provider filter
    // -------------------------------------------------------------------------

    public function test_public_can_filter_by_provider_uuid(): void
    {
        ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'price'       => '65.00',
            'active'      => true,
        ]);

        $response = $this->getJson(
            '/api/public/service-pricing/services/' . $this->service->uuid . '/price'
            . '?provider_uuid=' . $this->provider->uuid
        );

        $response->assertStatus(200)
            ->assertJsonPath('data.final_price', '65.00')
            ->assertJsonPath('data.provider_uuid', $this->provider->uuid);
    }

    public function test_blocked_provider_uuid_filter_returns_404(): void
    {
        $blocked = Provider::create([
            'name'       => 'Blocked Provider',
            'email'      => 'blocked@test.com',
            'password'   => 'password123',
            'country_id' => $this->country->id,
            'city_id'    => $this->city->id,
            'active'     => true,
            'blocked'    => true,
            'banned'     => false,
        ]);

        ServicePrice::create([
            'provider_id' => $blocked->id,
            'service_id'  => $this->service->id,
            'price'       => '50.00',
            'active'      => true,
        ]);

        $response = $this->getJson(
            '/api/public/service-pricing/services/' . $this->service->uuid . '/price'
            . '?provider_uuid=' . $blocked->uuid
        );

        $response->assertStatus(404);
    }

    public function test_banned_provider_uuid_filter_returns_404(): void
    {
        $banned = Provider::create([
            'name'       => 'Banned Provider',
            'email'      => 'banned@test.com',
            'password'   => 'password123',
            'country_id' => $this->country->id,
            'city_id'    => $this->city->id,
            'active'     => true,
            'blocked'    => false,
            'banned'     => true,
        ]);

        ServicePrice::create([
            'provider_id' => $banned->id,
            'service_id'  => $this->service->id,
            'price'       => '50.00',
            'active'      => true,
        ]);

        $response = $this->getJson(
            '/api/public/service-pricing/services/' . $this->service->uuid . '/price'
            . '?provider_uuid=' . $banned->uuid
        );

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Response shape — public sees minimal fields only
    // -------------------------------------------------------------------------

    public function test_public_response_shape_is_minimal(): void
    {
        ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'price'       => '75.00',
            'active'      => true,
        ]);

        $response = $this->getJson('/api/public/service-pricing/services/' . $this->service->uuid . '/price');

        $response->assertStatus(200);
        $data = $response->json('data');

        // Required public fields
        $this->assertArrayHasKey('service_uuid', $data);
        $this->assertArrayHasKey('final_price', $data);
        $this->assertArrayHasKey('provider_uuid', $data);
        $this->assertArrayHasKey('provider_name', $data);

        // Internal fields must NOT be exposed
        $this->assertArrayNotHasKey('source_uuid', $data);
        $this->assertArrayNotHasKey('resolved_from', $data);
        $this->assertArrayNotHasKey('branch_uuid', $data);
    }

    // -------------------------------------------------------------------------
    // Response differs from employee resource
    // -------------------------------------------------------------------------

    public function test_public_and_employee_resources_have_different_shapes(): void
    {
        $employee = Employee::create([
            'provider_id' => $this->provider->id,
            'branch_id'   => $this->branch->id,
            'name'        => 'Test Employee',
            'email'       => 'employee@test.com',
            'password'    => 'password123',
            'active'      => true,
            'blocked'     => false,
        ]);

        ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'price'       => '75.00',
            'active'      => true,
        ]);

        // Public response
        $publicResponse = $this->getJson(
            '/api/public/service-pricing/services/' . $this->service->uuid . '/price'
        );
        $publicData = $publicResponse->json('data');

        // Login as employee
        $loginResponse = $this->postJson('/api/employee/auth/login', [
            'email'    => 'employee@test.com',
            'password' => 'password123',
        ]);
        $employeeToken = $this->getTokenFromLoginResponse($loginResponse);

        $employeeResponse = $this->withHeader('Authorization', 'Bearer ' . $employeeToken)
            ->getJson('/api/employee/service-pricing/services/' . $this->service->uuid . '/price');
        $employeeData = $employeeResponse->json('data');

        // Public has provider info; employee does not
        $this->assertArrayHasKey('provider_uuid', $publicData);
        $this->assertArrayNotHasKey('provider_uuid', $employeeData);

        // Employee has resolved_from; public does not
        $this->assertArrayHasKey('resolved_from', $employeeData);
        $this->assertArrayNotHasKey('resolved_from', $publicData);
    }

    // -------------------------------------------------------------------------
    // Branch-scoped resolution via public endpoint
    // -------------------------------------------------------------------------

    public function test_public_can_resolve_branch_specific_price(): void
    {
        // Default price
        ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'price'       => '100.00',
            'active'      => true,
        ]);

        // Branch-specific price
        ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'branch_id'   => $this->branch->id,
            'price'       => '80.00',
            'active'      => true,
        ]);

        $response = $this->getJson(
            '/api/public/service-pricing/services/' . $this->service->uuid . '/price'
            . '?branch_uuid=' . $this->branch->uuid
        );

        $response->assertStatus(200)
            ->assertJsonPath('data.final_price', '80.00');
    }
}
