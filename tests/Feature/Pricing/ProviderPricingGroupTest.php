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
use App\Models\Subcategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderPricingGroupTest extends TestCase
{
    use RefreshDatabase;

    private Provider $provider;
    private string $token;
    private Country $country;
    private City $city;
    private ProviderBranch $branch;
    private Employee $employee1;
    private Employee $employee2;

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

        $this->employee1 = Employee::create([
            'provider_id' => $this->provider->id,
            'branch_id'   => $this->branch->id,
            'name'        => 'Employee One',
            'email'       => 'emp1@test.com',
            'password'    => 'password123',
            'active'      => true,
            'blocked'     => false,
        ]);

        $this->employee2 = Employee::create([
            'provider_id' => $this->provider->id,
            'branch_id'   => $this->branch->id,
            'name'        => 'Employee Two',
            'email'       => 'emp2@test.com',
            'password'    => 'password123',
            'active'      => true,
            'blocked'     => false,
        ]);
    }

    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function test_provider_can_list_pricing_groups(): void
    {
        PricingGroup::create([
            'provider_id' => $this->provider->id,
            'name'        => ['ar' => 'مجموعة', 'en' => 'Group'],
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/provider/pricing-groups');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------

    public function test_provider_can_create_pricing_group_without_employees(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/provider/pricing-groups', [
                'name'   => ['ar' => 'مجموعة VIP', 'en' => 'VIP Group'],
                'active' => true,
            ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.name.en', 'VIP Group');

        $this->assertDatabaseHas('pricing_groups', [
            'provider_id' => $this->provider->id,
        ]);
    }

    public function test_provider_can_create_pricing_group_with_multiple_employees(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/provider/pricing-groups', [
                'name'           => ['ar' => 'مجموعة', 'en' => 'Group'],
                'active'         => true,
                'employee_uuids' => [$this->employee1->uuid, $this->employee2->uuid],
            ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);

        $group = PricingGroup::where('provider_id', $this->provider->id)->first();
        $this->assertNotNull($group);

        $this->assertDatabaseHas('pricing_group_employees', [
            'pricing_group_id' => $group->id,
            'employee_id'      => $this->employee1->id,
        ]);
        $this->assertDatabaseHas('pricing_group_employees', [
            'pricing_group_id' => $group->id,
            'employee_id'      => $this->employee2->id,
        ]);
    }

    public function test_provider_cannot_assign_foreign_employees_to_group(): void
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

        $otherEmployee = Employee::create([
            'provider_id' => $other->id,
            'branch_id'   => $otherBranch->id,
            'name'        => 'Foreign Employee',
            'email'       => 'foreign@test.com',
            'password'    => 'password123',
            'active'      => true,
            'blocked'     => false,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/provider/pricing-groups', [
                'name'           => ['ar' => 'مجموعة', 'en' => 'Group'],
                'active'         => true,
                'employee_uuids' => [$otherEmployee->uuid],
            ]);

        $response->assertStatus(422);
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function test_provider_can_show_own_pricing_group(): void
    {
        $group = PricingGroup::create([
            'provider_id' => $this->provider->id,
            'name'        => ['ar' => 'مجموعة', 'en' => 'My Group'],
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/provider/pricing-groups/' . $group->uuid);

        $response->assertStatus(200)
            ->assertJsonPath('data.uuid', $group->uuid);
    }

    public function test_provider_cannot_show_another_providers_group(): void
    {
        $other = Provider::create([
            'name'       => 'Other Provider',
            'email'      => 'other4@test.com',
            'password'   => 'password123',
            'country_id' => $this->country->id,
            'city_id'    => $this->city->id,
            'active'     => true,
            'blocked'    => false,
            'banned'     => false,
        ]);

        $group = PricingGroup::create([
            'provider_id' => $other->id,
            'name'        => ['ar' => 'مجموعة', 'en' => 'Other Group'],
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/provider/pricing-groups/' . $group->uuid);

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------

    public function test_provider_can_update_pricing_group(): void
    {
        $group = PricingGroup::create([
            'provider_id' => $this->provider->id,
            'name'        => ['ar' => 'قديم', 'en' => 'Old Name'],
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/provider/pricing-groups/' . $group->uuid, [
                'name' => ['ar' => 'جديد', 'en' => 'New Name'],
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name.en', 'New Name');
    }

    // -------------------------------------------------------------------------
    // Destroy
    // -------------------------------------------------------------------------

    public function test_provider_can_delete_pricing_group(): void
    {
        $group = PricingGroup::create([
            'provider_id' => $this->provider->id,
            'name'        => ['ar' => 'مجموعة', 'en' => 'Group'],
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/provider/pricing-groups/' . $group->uuid);

        $response->assertStatus(200);
        $this->assertSoftDeleted('pricing_groups', ['id' => $group->id]);
    }

    // -------------------------------------------------------------------------
    // Toggle active
    // -------------------------------------------------------------------------

    public function test_provider_can_toggle_pricing_group_active(): void
    {
        $group = PricingGroup::create([
            'provider_id' => $this->provider->id,
            'name'        => ['ar' => 'مجموعة', 'en' => 'Group'],
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson('/api/provider/pricing-groups/' . $group->uuid . '/active');

        $response->assertStatus(200);
        $this->assertDatabaseHas('pricing_groups', ['id' => $group->id, 'active' => false]);
    }

    // -------------------------------------------------------------------------
    // Sync employees (PUT)
    // -------------------------------------------------------------------------

    public function test_provider_can_sync_group_employees(): void
    {
        $group = PricingGroup::create([
            'provider_id' => $this->provider->id,
            'name'        => ['ar' => 'مجموعة', 'en' => 'Group'],
            'active'      => true,
        ]);

        // Initially assign employee1
        PricingGroupEmployee::create([
            'pricing_group_id' => $group->id,
            'employee_id'      => $this->employee1->id,
        ]);

        // Sync to only employee2
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/provider/pricing-groups/' . $group->uuid . '/employees', [
                'employee_uuids' => [$this->employee2->uuid],
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('pricing_group_employees', [
            'pricing_group_id' => $group->id,
            'employee_id'      => $this->employee1->id,
        ]);
        $this->assertDatabaseHas('pricing_group_employees', [
            'pricing_group_id' => $group->id,
            'employee_id'      => $this->employee2->id,
        ]);
    }

    public function test_provider_can_sync_group_employees_to_empty(): void
    {
        $group = PricingGroup::create([
            'provider_id' => $this->provider->id,
            'name'        => ['ar' => 'مجموعة', 'en' => 'Group'],
            'active'      => true,
        ]);

        PricingGroupEmployee::create([
            'pricing_group_id' => $group->id,
            'employee_id'      => $this->employee1->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/provider/pricing-groups/' . $group->uuid . '/employees', [
                'employee_uuids' => [],
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('pricing_group_employees', [
            'pricing_group_id' => $group->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Add employees (POST)
    // -------------------------------------------------------------------------

    public function test_provider_can_add_employees_to_group(): void
    {
        $group = PricingGroup::create([
            'provider_id' => $this->provider->id,
            'name'        => ['ar' => 'مجموعة', 'en' => 'Group'],
            'active'      => true,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/provider/pricing-groups/' . $group->uuid . '/employees', [
                'employee_uuids' => [$this->employee1->uuid, $this->employee2->uuid],
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('pricing_group_employees', [
            'pricing_group_id' => $group->id,
            'employee_id'      => $this->employee1->id,
        ]);
        $this->assertDatabaseHas('pricing_group_employees', [
            'pricing_group_id' => $group->id,
            'employee_id'      => $this->employee2->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Remove employees (DELETE)
    // -------------------------------------------------------------------------

    public function test_provider_can_remove_employees_from_group(): void
    {
        $group = PricingGroup::create([
            'provider_id' => $this->provider->id,
            'name'        => ['ar' => 'مجموعة', 'en' => 'Group'],
            'active'      => true,
        ]);

        PricingGroupEmployee::create([
            'pricing_group_id' => $group->id,
            'employee_id'      => $this->employee1->id,
        ]);

        PricingGroupEmployee::create([
            'pricing_group_id' => $group->id,
            'employee_id'      => $this->employee2->id,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/provider/pricing-groups/' . $group->uuid . '/employees', [
                'employee_uuids' => [$this->employee1->uuid],
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseMissing('pricing_group_employees', [
            'pricing_group_id' => $group->id,
            'employee_id'      => $this->employee1->id,
        ]);
        $this->assertDatabaseHas('pricing_group_employees', [
            'pricing_group_id' => $group->id,
            'employee_id'      => $this->employee2->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Unauthenticated access
    // -------------------------------------------------------------------------

    public function test_unauthenticated_access_to_pricing_groups_is_rejected(): void
    {
        $this->getJson('/api/provider/pricing-groups')->assertStatus(401);
    }
}
