<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Country;
use App\Models\Provider;
use App\Models\ProviderBranch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProviderBranchSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Provider $provider;
    private string $token;
    private Country $egypt;
    private City $city;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

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

    public function test_upload_rejects_svg(): void
    {
        $svg = UploadedFile::fake()->create('test.svg', 100, 'image/svg+xml');

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/provider/branches', [
                'name' => 'Test Branch',
                'city_uuid' => $this->city->uuid,
                'logo' => $svg,
            ]);

        $response->assertStatus(422);
    }

    public function test_upload_validates_file_size(): void
    {
        $largeImage = UploadedFile::fake()->image('test.jpg', 100, 100)->size(3000);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/provider/branches', [
                'name' => 'Test Branch',
                'city_uuid' => $this->city->uuid,
                'logo' => $largeImage,
            ]);

        $response->assertStatus(422);
    }

    public function test_upload_stores_with_random_name(): void
    {
        $image = UploadedFile::fake()->image('original-name.jpg', 100, 100);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/provider/branches', [
                'name' => 'Test Branch',
                'city_uuid' => $this->city->uuid,
                'logo' => $image,
            ]);

        $response->assertStatus(201);
        $branch = ProviderBranch::first();
        $this->assertNotNull($branch->logo_path);
        $this->assertStringNotContainsString('original-name', $branch->logo_path);
        $this->assertStringEndsWith('.webp', $branch->logo_path);
    }

    public function test_country_id_always_set_to_egypt(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/provider/branches', [
                'name' => 'Test Branch',
                'city_uuid' => $this->city->uuid,
            ]);

        $response->assertStatus(201);
        $branch = ProviderBranch::first();
        $this->assertEquals($this->egypt->id, $branch->country_id);
    }
}
