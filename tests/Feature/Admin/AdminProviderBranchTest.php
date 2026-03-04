<?php

namespace Tests\Feature\Admin;

use App\Models\Admin;
use App\Models\City;
use App\Models\Country;
use App\Models\Provider;
use App\Models\ProviderBranch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminProviderBranchTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private string $token;
    private Provider $provider;
    private Country $egypt;
    private City $city;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => 'password',
            'active' => true,
        ]);

        $this->egypt = Country::create([
            'name' => ['ar' => 'مصر', 'en' => 'Egypt'],
            'iso2' => 'EG',
            'active' => true,
        ]);

        $this->city = City::create([
            'country_id' => $this->egypt->id,
            'name' => ['ar' => 'القاهرة', 'en' => 'Cairo'],
            'active' => true,
        ]);

        $this->provider = Provider::create([
            'name' => 'Test Provider',
            'email' => 'provider@test.com',
            'password' => 'password',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
            'active' => true,
        ]);

        $response = $this->postJson('/api/admin/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $this->token = $response->json('data.token');
    }

    public function test_admin_can_list_all_branches(): void
    {
        ProviderBranch::create([
            'provider_id' => $this->provider->id,
            'name' => 'Branch 1',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/admin/provider-branches');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['uuid', 'name', 'provider_uuid'],
            ],
        ]);
    }

    public function test_admin_can_filter_by_provider_uuid(): void
    {
        $otherProvider = Provider::create([
            'name' => 'Other Provider',
            'email' => 'other@test.com',
            'password' => 'password',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
            'active' => true,
        ]);

        ProviderBranch::create([
            'provider_id' => $this->provider->id,
            'name' => 'Branch 1',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
        ]);

        ProviderBranch::create([
            'provider_id' => $otherProvider->id,
            'name' => 'Branch 2',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/admin/provider-branches?provider_uuid=' . $this->provider->uuid);

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals('Branch 1', $data[0]['name']);
    }

    public function test_admin_can_update_branch_status(): void
    {
        $branch = ProviderBranch::create([
            'provider_id' => $this->provider->id,
            'name' => 'Test Branch',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
            'active' => true,
            'blocked' => false,
            'banned' => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson('/api/admin/provider-branches/' . $branch->uuid . '/status', [
                'blocked' => true,
            ]);

        $response->assertStatus(200);
        $this->assertTrue($branch->fresh()->blocked);
    }

    public function test_admin_can_soft_delete_branch(): void
    {
        $branch = ProviderBranch::create([
            'provider_id' => $this->provider->id,
            'name' => 'To Delete',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/admin/provider-branches/' . $branch->uuid);

        $response->assertStatus(200);
        $this->assertSoftDeleted('provider_branches', ['id' => $branch->id]);
    }

    public function test_admin_can_restore_branch(): void
    {
        $branch = ProviderBranch::create([
            'provider_id' => $this->provider->id,
            'name' => 'To Restore',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
        ]);

        $branch->delete();

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/admin/provider-branches/' . $branch->uuid . '/restore');

        $response->assertStatus(200);
        $this->assertDatabaseHas('provider_branches', [
            'id' => $branch->id,
            'deleted_at' => null,
        ]);
    }
}

