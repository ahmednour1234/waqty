<?php

namespace Tests\Feature\Pricing;

use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Employee;
use App\Models\PricingGroup;
use App\Models\PricingGroupEmployee;
use App\Models\Provider;
use App\Models\ProviderBranch;
use App\Models\Service;
use App\Models\ServicePrice;
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeServicePricingTest extends TestCase
{
    use RefreshDatabase;

    private Provider $provider;
    private Employee $employee;
    private string $employeeToken;
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

        $this->employee = Employee::create([
            'provider_id' => $this->provider->id,
            'branch_id'   => $this->branch->id,
            'name'        => 'Test Employee',
            'email'       => 'employee@test.com',
            'password'    => 'password123',
            'active'      => true,
            'blocked'     => false,
        ]);

        $response = $this->postJson('/api/employee/auth/login', [
            'email'    => 'employee@test.com',
            'password' => 'password123',
        ]);
        $this->employeeToken = $this->getTokenFromLoginResponse($response);

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
    // Auth requirement
    // -------------------------------------------------------------------------

    public function test_endpoint_requires_employee_auth(): void
    {
        $this->getJson('/api/employee/service-pricing/services/' . $this->service->uuid . '/price')
            ->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Price resolution
    // -------------------------------------------------------------------------

    public function test_employee_gets_default_price_when_no_specific_rule(): void
    {
        ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'price'       => '50.00',
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->employeeToken)
            ->getJson('/api/employee/service-pricing/services/' . $this->service->uuid . '/price');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.final_price', '50.00')
            ->assertJsonPath('data.resolved_from', 'default');
    }

    public function test_employee_specific_price_takes_priority_over_default(): void
    {
        // Default price
        ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'price'       => '50.00',
            'active'      => true,
        ]);

        // Employee-specific price
        ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'employee_id' => $this->employee->id,
            'price'       => '35.00',
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->employeeToken)
            ->getJson('/api/employee/service-pricing/services/' . $this->service->uuid . '/price');

        $response->assertStatus(200)
            ->assertJsonPath('data.final_price', '35.00')
            ->assertJsonPath('data.resolved_from', 'employee');
    }

    public function test_group_price_takes_priority_over_branch_price(): void
    {
        $group = PricingGroup::create([
            'provider_id' => $this->provider->id,
            'name'        => ['ar' => 'مجموعة', 'en' => 'Group'],
            'active'      => true,
        ]);

        PricingGroupEmployee::create([
            'pricing_group_id' => $group->id,
            'employee_id'      => $this->employee->id,
        ]);

        // Branch price
        ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'branch_id'   => $this->branch->id,
            'price'       => '60.00',
            'active'      => true,
        ]);

        // Group price
        ServicePrice::create([
            'provider_id'      => $this->provider->id,
            'service_id'       => $this->service->id,
            'pricing_group_id' => $group->id,
            'price'            => '40.00',
            'active'           => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->employeeToken)
            ->getJson('/api/employee/service-pricing/services/' . $this->service->uuid . '/price');

        $response->assertStatus(200)
            ->assertJsonPath('data.final_price', '40.00')
            ->assertJsonPath('data.resolved_from', 'group');
    }

    public function test_inactive_employee_price_is_not_used(): void
    {
        // Active default price
        ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'price'       => '50.00',
            'active'      => true,
        ]);

        // Inactive employee price — should be ignored
        ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'employee_id' => $this->employee->id,
            'price'       => '10.00',
            'active'      => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->employeeToken)
            ->getJson('/api/employee/service-pricing/services/' . $this->service->uuid . '/price');

        $response->assertStatus(200)
            ->assertJsonPath('data.final_price', '50.00')
            ->assertJsonPath('data.resolved_from', 'default');
    }

    public function test_soft_deleted_price_is_not_resolved(): void
    {
        $price = ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'price'       => '55.00',
            'active'      => true,
        ]);

        $price->delete();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->employeeToken)
            ->getJson('/api/employee/service-pricing/services/' . $this->service->uuid . '/price');

        $response->assertStatus(404);
    }

    public function test_inactive_group_price_is_not_used(): void
    {
        $group = PricingGroup::create([
            'provider_id' => $this->provider->id,
            'name'        => ['ar' => 'مجموعة', 'en' => 'Group'],
            'active'      => false,  // inactive group
        ]);

        PricingGroupEmployee::create([
            'pricing_group_id' => $group->id,
            'employee_id'      => $this->employee->id,
        ]);

        ServicePrice::create([
            'provider_id'      => $this->provider->id,
            'service_id'       => $this->service->id,
            'pricing_group_id' => $group->id,
            'price'            => '20.00',
            'active'           => true,
        ]);

        // Fallback default price
        ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'price'       => '45.00',
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->employeeToken)
            ->getJson('/api/employee/service-pricing/services/' . $this->service->uuid . '/price');

        $response->assertStatus(200)
            ->assertJsonPath('data.final_price', '45.00')
            ->assertJsonPath('data.resolved_from', 'default');
    }

    public function test_returns_404_when_no_price_configured(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->employeeToken)
            ->getJson('/api/employee/service-pricing/services/' . $this->service->uuid . '/price');

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Ownership security
    // -------------------------------------------------------------------------

    public function test_employee_cannot_resolve_price_for_other_providers_service(): void
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

        $foreignService = Service::create([
            'sub_category_id' => $this->service->sub_category_id,
            'name'            => ['ar' => 'خدمة أجنبية', 'en' => 'Foreign Service'],
        ]);
        $other->services()->attach($foreignService->id, ['active' => true]);

        ServicePrice::create([
            'provider_id' => $other->id,
            'service_id'  => $foreignService->id,
            'price'       => '99.00',
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->employeeToken)
            ->getJson('/api/employee/service-pricing/services/' . $foreignService->uuid . '/price');

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Response shape — employee only sees minimal/safe fields
    // -------------------------------------------------------------------------

    public function test_employee_response_does_not_expose_source_uuid(): void
    {
        ServicePrice::create([
            'provider_id' => $this->provider->id,
            'service_id'  => $this->service->id,
            'price'       => '50.00',
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->employeeToken)
            ->getJson('/api/employee/service-pricing/services/' . $this->service->uuid . '/price');

        $response->assertStatus(200);
        $data = $response->json('data');

        // These internal fields must not be present
        $this->assertArrayNotHasKey('source_uuid', $data);
        $this->assertArrayNotHasKey('provider_uuid', $data);

        // These safe fields must be present
        $this->assertArrayHasKey('service_uuid', $data);
        $this->assertArrayHasKey('final_price', $data);
        $this->assertArrayHasKey('resolved_from', $data);
    }
}
