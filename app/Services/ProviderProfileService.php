<?php

namespace App\Services;

use App\Models\Provider;
use App\Repositories\Contracts\ProviderRepositoryInterface;
use App\Models\Country;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ProviderProfileService
{
    public function __construct(
        private ProviderRepositoryInterface $providerRepository,
        private ImageUploadService $imageUploadService
    ) {
    }

    public function updateProfile(Provider $provider, array $data, ?UploadedFile $logoFile = null): Provider
    {
        return DB::transaction(function () use ($provider, $data, $logoFile) {
            $egypt = Country::where('iso2', 'EG')->first();
            if (!$egypt) {
                throw new \Exception('Egypt country not found', 500);
            }

            $data['country_id'] = $egypt->id;

            $oldLogoPath = $provider->logo_path;

            if ($logoFile) {
                $logoPath = $this->imageUploadService->storeImage($logoFile, 'providers', $provider->uuid);
                $data['logo_path'] = $logoPath;

                if ($oldLogoPath) {
                    $this->imageUploadService->deleteImage($oldLogoPath);
                }
            }

            return $this->providerRepository->update($provider, $data);
        });
    }
}
