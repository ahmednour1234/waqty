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

class EmployeeProfileTest extends TestCase
{
    use RefreshDatabase;

    private Employee $employee;
    private string $token;
    private Country $country;
    private City $city;
    private Provider $provider;
    private ProviderBranch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->country = Country::create([
            'name' => ['ar' => 'مصر', 'en' => 'Egypt'],
            'iso2' => 'EG',
            'phone_code' => '+20',
            'active' => true,
        ]);

        $this->city = City::create([
            'country_id' => $this->country->id,
            'name' => ['ar' => 'القاهرة', 'en' => 'Cairo'],
            'active' => true,
        ]);

        $this->provider = Provider::create([
            'name' => 'Test Provider',
            'email' => 'provider@test.com',
            'password' => 'password123',
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'active' => true,
            'blocked' => false,
            'banned' => false,
        ]);

        $this->branch = ProviderBranch::create([
            'provider_id' => $this->provider->id,
            'name' => 'Test Branch',
            'country_id' => $this->country->id,
            'city_id' => $this->city->id,
            'status' => 'active',
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

        $response = $this->postJson('/api/employee/auth/login', [
            'email' => 'employee@test.com',
            'password' => 'password123',
        ]);

        $this->token = $this->getTokenFromLoginResponse($response);
    }

    public function test_employee_can_update_profile(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/employee/profile', [
                'name' => 'Updated Employee',
                'phone' => '+1234567890',
            ]);

        $response->assertStatus(200);
        $this->employee->refresh();
        $this->assertEquals('Updated Employee', $this->employee->name);
    }

    public function test_employee_can_change_password(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/employee/change-password', [
                'current_password' => 'password123',
                'new_password' => 'newpassword123',
                'new_password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(200);
    }

    public function test_employee_cannot_change_password_with_wrong_current_password(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/employee/change-password', [
                'current_password' => 'wrongpassword',
                'new_password' => 'newpassword123',
                'new_password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(400);
    }

    public function test_employee_routes_require_authentication(): void
    {
        $response = $this->putJson('/api/employee/profile', [
            'name' => 'Updated Employee',
        ]);

        $response->assertStatus(401);
    }
}
