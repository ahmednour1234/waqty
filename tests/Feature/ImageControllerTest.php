<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Provider;
use App\Models\ProviderBranch;
use App\Models\Employee;
use App\Models\Country;
use App\Models\City;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ImageControllerTest extends TestCase
{
    use RefreshDatabase;

    private Country $country;
    private City $city;
    private Provider $provider;
    private ProviderBranch $branch;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

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

    public function test_can_serve_category_image(): void
    {
        $category = Category::create([
            'name' => ['ar' => 'فئة', 'en' => 'Category'],
            'active' => true,
        ]);

        $imagePath = 'categories/test.jpg';
        Storage::disk('public')->put($imagePath, 'fake image content');
        $category->update(['image_path' => $imagePath]);

        $response = $this->get('/api/images/categories/' . $category->uuid);

        $response->assertStatus(200);
    }

    public function test_can_serve_subcategory_image(): void
    {
        $category = Category::create([
            'name' => ['ar' => 'فئة', 'en' => 'Category'],
            'active' => true,
        ]);

        $subcategory = Subcategory::create([
            'category_id' => $category->id,
            'name' => ['ar' => 'فئة فرعية', 'en' => 'Subcategory'],
            'active' => true,
        ]);

        $imagePath = 'subcategories/test.jpg';
        Storage::disk('public')->put($imagePath, 'fake image content');
        $subcategory->update(['image_path' => $imagePath]);

        $response = $this->get('/api/images/subcategories/' . $subcategory->uuid);

        $response->assertStatus(200);
    }

    public function test_can_serve_provider_image(): void
    {
        $imagePath = 'providers/test.jpg';
        Storage::disk('public')->put($imagePath, 'fake image content');
        $this->provider->update(['logo_path' => $imagePath]);

        $response = $this->get('/api/images/providers/' . $this->provider->uuid);

        $response->assertStatus(200);
    }

    public function test_can_serve_branch_image(): void
    {
        $imagePath = 'branches/test.jpg';
        Storage::disk('public')->put($imagePath, 'fake image content');
        $this->branch->update(['logo_path' => $imagePath]);

        $response = $this->get('/api/images/branches/' . $this->branch->uuid);

        $response->assertStatus(200);
    }

    public function test_can_serve_employee_image(): void
    {
        $imagePath = 'employees/test.jpg';
        Storage::disk('public')->put($imagePath, 'fake image content');
        $this->employee->update(['logo_path' => $imagePath]);

        $response = $this->get('/api/images/employees/' . $this->employee->uuid);

        $response->assertStatus(200);
    }

    public function test_returns_404_for_invalid_type(): void
    {
        $response = $this->get('/api/images/invalid/123');

        $response->assertStatus(404);
    }

    public function test_returns_404_for_nonexistent_uuid(): void
    {
        $response = $this->get('/api/images/categories/nonexistent-uuid');

        $response->assertStatus(404);
    }

    public function test_returns_404_when_image_file_missing(): void
    {
        $category = Category::create([
            'name' => ['ar' => 'فئة', 'en' => 'Category'],
            'active' => true,
        ]);

        $response = $this->get('/api/images/categories/' . $category->uuid);

        $response->assertStatus(404);
    }
}
