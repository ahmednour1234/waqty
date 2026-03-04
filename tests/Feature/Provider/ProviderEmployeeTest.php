<?php

namespace Tests\Feature\Provider;

use App\Models\Country;
use App\Models\City;
use App\Models\Provider;
use App\Models\ProviderBranch;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProviderEmployeeTest extends TestCase
{
    use RefreshDatabase;

    private Provider $provider;
    private string $token;
    private Country $country;
    private City $city;
    private ProviderBranch $branch;
    private Employee $employee;

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

        $response = $this->postJson('/api/provider/auth/login', [
            'email' => 'provider@test.com',
            'password' => 'password123',
        ]);

        $this->token = $this->getTokenFromLoginResponse($response);

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
    }

    public function test_provider_can_list_employees(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/provider/employees');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertIsArray($data);
    }

    public function test_provider_can_create_employee(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/provider/employees', [
                'branch_uuid' => $this->branch->uuid,
                'name' => 'New Employee',
                'email' => 'newemployee@test.com',
                'password' => 'password123',
                'phone' => '+1234567890',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('employees', [
            'email' => 'newemployee@test.com',
        ]);
    }

    public function test_provider_can_view_employee(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/provider/employees/' . $this->employee->uuid);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($this->employee->uuid, $data['uuid']);
    }

    public function test_provider_can_update_employee(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/provider/employees/' . $this->employee->uuid, [
                'name' => 'Updated Employee',
                'phone' => '+9876543210',
            ]);

        $response->assertStatus(200);
        $this->employee->refresh();
        $this->assertEquals('Updated Employee', $this->employee->name);
    }

    public function test_provider_can_delete_employee(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/provider/employees/' . $this->employee->uuid);

        $response->assertStatus(200);
        $this->assertSoftDeleted('employees', ['id' => $this->employee->id]);
    }

    public function test_provider_can_toggle_employee_active(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson('/api/provider/employees/' . $this->employee->uuid . '/active', [
                'active' => false,
            ]);

        $response->assertStatus(200);
        $this->employee->refresh();
        $this->assertFalse($this->employee->active);
    }

    public function test_provider_can_block_employee(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson('/api/provider/employees/' . $this->employee->uuid . '/block', [
                'blocked' => true,
            ]);

        $response->assertStatus(200);
        $this->employee->refresh();
        $this->assertTrue($this->employee->blocked);
    }

    public function test_provider_routes_require_authentication(): void
    {
        $response = $this->getJson('/api/provider/employees');
        $response->assertStatus(401);
    }
}
