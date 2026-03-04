<?php

namespace Tests\Feature\Provider;

use App\Models\Country;
use App\Models\City;
use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProviderLogoUploadTest extends TestCase
{
    use RefreshDatabase;

    private Provider $provider;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

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

        $response = $this->postJson('/api/provider/auth/login', [
            'email' => 'provider@test.com',
            'password' => 'password123',
        ]);

        $this->token = $response->json('data.token');
    }

    public function test_upload_rejects_svg(): void
    {
        $svg = UploadedFile::fake()->create('test.svg', 100, 'image/svg+xml');

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/provider/profile', [
                'name' => 'Updated Provider',
                'city_id' => $this->provider->city_id,
                'logo' => $svg,
            ]);

        $response->assertStatus(422);
    }

    public function test_upload_rejects_fake_mime_type(): void
    {
        $file = UploadedFile::fake()->create('test.jpg', 100, 'application/x-php');

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/provider/profile', [
                'name' => 'Updated Provider',
                'city_id' => $this->provider->city_id,
                'logo' => $file,
            ]);

        $response->assertStatus(422);
    }

    public function test_upload_re_encodes_image(): void
    {
        $image = UploadedFile::fake()->image('test.jpg', 100, 100);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/provider/profile', [
                'name' => 'Updated Provider',
                'city_id' => $this->provider->city_id,
                'logo' => $image,
            ]);

        $response->assertStatus(200);
        $this->provider->refresh();
        $this->assertNotNull($this->provider->logo_path);
        $this->assertStringEndsWith('.webp', $this->provider->logo_path);
    }

    public function test_old_logo_deleted_on_replace(): void
    {
        $oldImage = UploadedFile::fake()->image('old.jpg', 100, 100);
        $oldLogoPath = 'providers/' . $this->provider->uuid . '/old.webp';
        $this->provider->update(['logo_path' => $oldLogoPath]);
        Storage::disk('public')->put($oldLogoPath, 'fake-content');

        $newImage = UploadedFile::fake()->image('new.jpg', 100, 100);

        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->putJson('/api/provider/profile', [
                'name' => 'Updated Provider',
                'city_id' => $this->provider->city_id,
                'logo' => $newImage,
            ]);

        $response->assertStatus(200);
        $this->assertFalse(Storage::disk('public')->exists($oldLogoPath));
    }
}

