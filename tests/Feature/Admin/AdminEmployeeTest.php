<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\Country;
use App\Models\City;
use App\Models\Provider;
use App\Models\ProviderBranch;
use App\Models\Employee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminEmployeeTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private string $token;
    private Country $country;
    private City $city;
    private Provider $provider;
    private ProviderBranch $branch;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'active' => true,
        ]);

        $response = $this->postJson('/api/admin/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $this->token = $response->json('data.token');

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
            'password' => Hash::make('password123'),
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
            'password' => Hash::make('password123'),
            'active' => true,
            'blocked' => false,
        ]);
    }

    public function test_admin_can_list_employees(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/admin/employees');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertIsArray($data);
    }

    public function test_admin_can_view_employee(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/admin/employees/' . $this->employee->uuid);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals($this->employee->uuid, $data['uuid']);
    }

    public function test_admin_can_update_employee_status(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson('/api/admin/employees/' . $this->employee->uuid . '/status', [
                'active' => false,
            ]);

        $response->assertStatus(200);
        $this->employee->refresh();
        $this->assertFalse($this->employee->active);
    }

    public function test_admin_can_delete_employee(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/admin/employees/' . $this->employee->uuid);

        $response->assertStatus(200);
        $this->assertSoftDeleted('employees', ['id' => $this->employee->id]);
    }

    public function test_admin_can_restore_employee(): void
    {
        $this->employee->delete();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/admin/employees/' . $this->employee->uuid . '/restore');

        $response->assertStatus(200);
        $this->employee->refresh();
        $this->assertNull($this->employee->deleted_at);
    }

    public function test_admin_routes_require_authentication(): void
    {
        $response = $this->getJson('/api/admin/employees');
        $response->assertStatus(401);
    }
}
