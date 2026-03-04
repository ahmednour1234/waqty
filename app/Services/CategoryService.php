<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CategoryService
{
    public function __construct(
        private CategoryRepositoryInterface $categoryRepository,
        private ImageUploadService $imageUploadService
    ) {
    }

    public function index(array $filters = [], int $perPage = 15)
    {
        return $this->categoryRepository->paginate($filters, $perPage);
    }

    public function store(array $data, ?UploadedFile $image = null): Category
    {
        return DB::transaction(function () use ($data, $image) {
            $category = $this->categoryRepository->create($data);

            if ($image) {
                $imagePath = $this->imageUploadService->storeImage($image, 'categories', $category->uuid);
                $category = $this->categoryRepository->update($category, ['image_path' => $imagePath]);
            }

            return $category;
        });
    }

    public function show(string $uuid): Category
    {
        $category = $this->categoryRepository->findByUuid($uuid);

        if (!$category) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Category not found');
        }

        return $category;
    }

    public function update(string $uuid, array $data, ?UploadedFile $image = null): Category
    {
        return DB::transaction(function () use ($uuid, $data, $image) {
            $category = $this->categoryRepository->findByUuid($uuid);

            if (!$category) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Category not found');
            }

            $oldImagePath = $category->image_path;

            if ($image) {
                $imagePath = $this->imageUploadService->storeImage($image, 'categories', $category->uuid);
                $data['image_path'] = $imagePath;

                if ($oldImagePath) {
                    $this->imageUploadService->deleteImage($oldImagePath);
                }
            }

            return $this->categoryRepository->update($category, $data);
        });
    }

    public function destroy(string $uuid): bool
    {
        $category = $this->categoryRepository->findByUuid($uuid);

        if (!$category) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Category not found');
        }

        return $this->categoryRepository->delete($category);
    }

    public function restore(string $uuid): Category
    {
        $category = $this->categoryRepository->restore($uuid);

        if (!$category) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Category not found');
        }

        return $category;
    }

    public function forceDelete(string $uuid): bool
    {
        $category = Category::withTrashed()->whereUuid($uuid)->first();

        if (!$category) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Category not found');
        }

        if ($category->image_path) {
            $this->imageUploadService->deleteImage($category->image_path);
        }

        return $this->categoryRepository->forceDelete($uuid);
    }

    public function toggleActive(string $uuid, bool $active): Category
    {
        $category = $this->categoryRepository->findByUuid($uuid);

        if (!$category) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Category not found');
        }

        return $this->categoryRepository->update($category, ['active' => $active]);
    }
}
