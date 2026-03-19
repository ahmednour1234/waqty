<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Employee;
use App\Models\Provider;
use App\Models\ProviderBranch;
use App\Models\Service;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ImageController extends Controller
{
    public function serve(string $type, string $uuid): Response
    {
        $allowedTypes = ['categories', 'subcategories', 'providers', 'branches', 'employees', 'services'];
        if (!in_array($type, $allowedTypes)) {
            abort(404);
        }

        $model = match ($type) {
            'categories' => Category::whereUuid($uuid)->first(),
            'subcategories' => Subcategory::whereUuid($uuid)->first(),
            'providers' => Provider::whereUuid($uuid)->first(),
            'branches' => ProviderBranch::whereUuid($uuid)->first(),
            'employees' => Employee::whereUuid($uuid)->first(),
            'services' => Service::whereUuid($uuid)->first(),
            default => null,
        };

        $path = match ($type) {
            'providers', 'branches', 'employees' => $model->logo_path ?? null,
            default => $model->image_path ?? null,
        };

        if (!$model || !$path) {
            abort(404);
        }
        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $fullPath = Storage::disk('public')->path($path);
        $mimeType = Storage::disk('public')->mimeType($path);

        if (!str_starts_with($mimeType, 'image/')) {
            abort(404);
        }

        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
