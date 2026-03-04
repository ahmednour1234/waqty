<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Subcategory;
use App\Repositories\Contracts\SubcategoryRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class SubcategoryService
{
    public function __construct(
        private SubcategoryRepositoryInterface $subcategoryRepository,
        private ImageUploadService $imageUploadService
    ) {
    }

    public function index(array $filters = [], int $perPage = 15)
    {
        return $this->subcategoryRepository->paginate($filters, $perPage);
    }

    public function store(array $data, ?UploadedFile $image = null): Subcategory
    {
        return DB::transaction(function () use ($data, $image) {
            if (isset($data['category_uuid'])) {
                $category = Category::whereUuid($data['category_uuid'])->first();
                if (!$category) {
                    throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Category not found');
                }
                $data['category_id'] = $category->id;
                unset($data['category_uuid']);
            }

            $subcategory = $this->subcategoryRepository->create($data);

            if ($image) {
                $imagePath = $this->imageUploadService->storeImage($image, 'subcategories', $subcategory->uuid);
                $subcategory = $this->subcategoryRepository->update($subcategory, ['image_path' => $imagePath]);
            }

            return $subcategory;
        });
    }

    public function show(string $uuid): Subcategory
    {
        $subcategory = $this->subcategoryRepository->findByUuid($uuid);

        if (!$subcategory) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Subcategory not found');
        }

        return $subcategory;
    }

    public function update(string $uuid, array $data, ?UploadedFile $image = null): Subcategory
    {
        return DB::transaction(function () use ($uuid, $data, $image) {
            $subcategory = $this->subcategoryRepository->findByUuid($uuid);

            if (!$subcategory) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Subcategory not found');
            }

            if (isset($data['category_uuid'])) {
                $category = Category::whereUuid($data['category_uuid'])->first();
                if (!$category) {
                    throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Category not found');
                }
                $data['category_id'] = $category->id;
                unset($data['category_uuid']);
            }

            $oldImagePath = $subcategory->image_path;

            if ($image) {
                $imagePath = $this->imageUploadService->storeImage($image, 'subcategories', $subcategory->uuid);
                $data['image_path'] = $imagePath;

                if ($oldImagePath) {
                    $this->imageUploadService->deleteImage($oldImagePath);
                }
            }

            return $this->subcategoryRepository->update($subcategory, $data);
        });
    }

    public function destroy(string $uuid): bool
    {
        $subcategory = $this->subcategoryRepository->findByUuid($uuid);

        if (!$subcategory) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Subcategory not found');
        }

        return $this->subcategoryRepository->delete($subcategory);
    }

    public function restore(string $uuid): Subcategory
    {
        $subcategory = $this->subcategoryRepository->restore($uuid);

        if (!$subcategory) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Subcategory not found');
        }

        return $subcategory;
    }

    public function forceDelete(string $uuid): bool
    {
        $subcategory = Subcategory::withTrashed()->whereUuid($uuid)->first();

        if (!$subcategory) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Subcategory not found');
        }

        if ($subcategory->image_path) {
            $this->imageUploadService->deleteImage($subcategory->image_path);
        }

        return $this->subcategoryRepository->forceDelete($uuid);
    }

    public function toggleActive(string $uuid, bool $active): Subcategory
    {
        $subcategory = $this->subcategoryRepository->findByUuid($uuid);

        if (!$subcategory) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Subcategory not found');
        }

        return $this->subcategoryRepository->update($subcategory, ['active' => $active]);
    }
}
