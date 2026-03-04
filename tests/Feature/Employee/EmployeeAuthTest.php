<?php

namespace Tests\Feature\Employee;

use App\Models\Country;
use App\Models\City;
use App\Models\Provider;
use App\Models\ProviderBranch;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmployeeAuthTest extends TestCase
{
    use RefreshDatabase;

    private Employee $employee;
    private Provider $provider;
    private ProviderBranch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $country = Country::create([
            'name' => ['ar' => 'مصر', 'en' => 'Egypt'],
            'iso2' => 'EG',
            'phone_code' => '+20',
            'active' => true,
        ]);

        $city = City::create([
            'country_id' => $country->id,
            'name' => ['ar' => 'القاهرة', 'en' => 'Cairo'],
            'active' => true,
        ]);

        $this->provider = Provider::create([
            'name' => 'Test Provider',
            'email' => 'provider@test.com',
            'password' => 'password123',
            'country_id' => $country->id,
            'city_id' => $city->id,
            'active' => true,
            'blocked' => false,
            'banned' => false,
        ]);

        $this->branch = ProviderBranch::create([
            'provider_id' => $this->provider->id,
            'name' => 'Main Branch',
            'country_id' => $country->id,
            'city_id' => $city->id,
            'active' => true,
            'is_main' => true,
        ]);

        $this->employee = Employee::create([
            'provider_id' => $this->provider->id,
            'branch_id' => $this->branch->id,
            'name' => 'Test Employee',
            'email' => 'employee@test.com',
            'password' => 'password123',
            'active' => true,
            'blocked' => false,
        ]);
    }

    public function test_employee_can_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/employee/auth/login', [
            'email' => 'employee@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'token',
                'token_type',
                'expires_in',
                'employee',
            ],
        ]);
    }

    public function test_employee_cannot_login_if_inactive(): void
    {
        $this->employee->update(['active' => false]);

        $response = $this->postJson('/api/employee/auth/login', [
            'email' => 'employee@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    public function test_employee_cannot_login_if_blocked(): void
    {
        $this->employee->update(['blocked' => true]);

        $response = $this->postJson('/api/employee/auth/login', [
            'email' => 'employee@test.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    public function test_employee_cannot_access_protected_routes_without_token(): void
    {
        $response = $this->getJson('/api/employee/auth/me');

        $response->assertStatus(401);
    }
}
