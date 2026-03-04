<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageUploadService
{
    public function validateImage(UploadedFile $file): void
    {
        $allowedMimes = config('upload.allowed_mime_types', []);
        $allowedExtensions = config('upload.allowed_extensions', []);
        $maxSize = config('upload.max_file_size', 2048) * 1024; // Convert KB to bytes

        if (!in_array($file->getMimeType(), $allowedMimes)) {
            throw new \InvalidArgumentException('api.upload.invalid_file_type');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $allowedExtensions)) {
            throw new \InvalidArgumentException('api.upload.invalid_file_extension');
        }

        if ($file->getSize() > $maxSize) {
            throw new \InvalidArgumentException('api.upload.image_too_large');
        }

        $imageInfo = @getimagesize($file->getRealPath());
        if ($imageInfo === false) {
            throw new \InvalidArgumentException('api.upload.file_not_image');
        }

        $allowedImageTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];
        if (!in_array($imageInfo[2], $allowedImageTypes)) {
            throw new \InvalidArgumentException('api.upload.unsupported_image_type');
        }
    }

    public function processImage(UploadedFile $file, string $directory): string
    {
        $this->validateImage($file);

        $imageInfo = getimagesize($file->getRealPath());
        $sourceImage = null;

        switch ($imageInfo[2]) {
            case IMAGETYPE_JPEG:
                $sourceImage = imagecreatefromjpeg($file->getRealPath());
                break;
            case IMAGETYPE_PNG:
                $sourceImage = imagecreatefrompng($file->getRealPath());
                break;
            case IMAGETYPE_WEBP:
                $sourceImage = imagecreatefromwebp($file->getRealPath());
                break;
            default:
                throw new \InvalidArgumentException('api.upload.unsupported_image_type');
        }

        if ($sourceImage === false) {
            throw new \RuntimeException('api.upload.failed_to_process');
        }

        $filename = Str::random(40) . '.webp';
        $fullPath = $directory . '/' . $filename;

        $webpPath = storage_path('app/public/' . $fullPath);
        $webpDir = dirname($webpPath);
        if (!is_dir($webpDir)) {
            mkdir($webpDir, 0755, true);
        }

        $success = imagewebp($sourceImage, $webpPath, 85);
        imagedestroy($sourceImage);

        if (!$success) {
            throw new \RuntimeException('api.upload.failed_to_save');
        }

        return $fullPath;
    }

    public function storeImage(UploadedFile $file, string $type, string $uuid): string
    {
        $directory = config('upload.storage_path.' . $type, $type) . '/' . $uuid;
        return $this->processImage($file, $directory);
    }

    public function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
            
            $directory = dirname($path);
            if (Storage::disk('public')->exists($directory) && 
                count(Storage::disk('public')->files($directory)) === 0) {
                Storage::disk('public')->deleteDirectory($directory);
            }
        }
    }
}
