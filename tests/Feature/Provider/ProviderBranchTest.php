<?php

namespace Tests\Feature\Provider;

use App\Models\City;
use App\Models\Country;
use App\Models\Provider;
use App\Models\ProviderBranch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProviderBranchTest extends TestCase
{
    use RefreshDatabase;

    private Provider $provider;
    private string $token;
    private Country $egypt;
    private City $city;

    protected function setUp(): void
    {
        parent::setUp();

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
            'password' => Hash::make('password'),
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
            'active' => true,
        ]);

        $response = $this->postJson('/api/provider/auth/login', [
            'email' => 'provider@test.com',
            'password' => 'password',
        ]);

        $this->token = $response->json('data.token');
    }

    public function test_provider_can_create_branch(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/provider/branches', [
                'name' => 'Main Branch',
                'phone' => '01234567890',
                'city_uuid' => $this->city->uuid,
                'latitude' => 30.0444,
                'longitude' => 31.2357,
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'uuid',
                'name',
                'phone',
                'is_main',
            ],
        ]);

        $this->assertDatabaseHas('provider_branches', [
            'provider_id' => $this->provider->id,
            'name' => 'Main Branch',
            'is_main' => true,
        ]);
    }

    public function test_first_branch_becomes_main_automatically(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/provider/branches', [
                'name' => 'First Branch',
                'city_uuid' => $this->city->uuid,
            ]);

        $response->assertStatus(201);
        $this->assertTrue($response->json('data.is_main'));

        $response2 = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/provider/branches', [
                'name' => 'Second Branch',
                'city_uuid' => $this->city->uuid,
            ]);

        $response2->assertStatus(201);
        $this->assertFalse($response2->json('data.is_main'));
    }

    public function test_provider_can_list_own_branches(): void
    {
        ProviderBranch::create([
            'provider_id' => $this->provider->id,
            'name' => 'Branch 1',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
            'is_main' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/provider/branches');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['uuid', 'name'],
            ],
        ]);
    }

    public function test_provider_cannot_access_other_provider_branches(): void
    {
        $otherProvider = Provider::create([
            'name' => 'Other Provider',
            'email' => 'other@test.com',
            'password' => Hash::make('password'),
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
            'active' => true,
        ]);

        $branch = ProviderBranch::create([
            'provider_id' => $otherProvider->id,
            'name' => 'Other Branch',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/provider/branches/' . $branch->uuid);

        $response->assertStatus(404);
    }

    public function test_provider_can_set_main_branch(): void
    {
        $branch1 = ProviderBranch::create([
            'provider_id' => $this->provider->id,
            'name' => 'Branch 1',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
            'is_main' => true,
        ]);

        $branch2 = ProviderBranch::create([
            'provider_id' => $this->provider->id,
            'name' => 'Branch 2',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
            'is_main' => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson('/api/provider/branches/' . $branch2->uuid . '/main');

        $response->assertStatus(200);
        $this->assertTrue($branch2->fresh()->is_main);
        $this->assertFalse($branch1->fresh()->is_main);
    }

    public function test_provider_can_update_branch(): void
    {
        $branch = ProviderBranch::create([
            'provider_id' => $this->provider->id,
            'name' => 'Original Name',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/provider/branches/' . $branch->uuid, [
                'name' => 'Updated Name',
                'phone' => '01234567890',
            ]);

        $response->assertStatus(200);
        $this->assertEquals('Updated Name', $branch->fresh()->name);
    }

    public function test_provider_can_delete_branch(): void
    {
        $branch = ProviderBranch::create([
            'provider_id' => $this->provider->id,
            'name' => 'To Delete',
            'country_id' => $this->egypt->id,
            'city_id' => $this->city->id,
            'is_main' => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/provider/branches/' . $branch->uuid);

        $response->assertStatus(200);
        $this->assertSoftDeleted('provider_branches', ['id' => $branch->id]);
    }
}
