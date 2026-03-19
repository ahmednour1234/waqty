<?php

namespace App\Services;

use App\Models\ShiftTemplate;
use App\Repositories\Contracts\ShiftTemplateRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class ProviderShiftTemplateService
{
    public function __construct(
        private ShiftTemplateRepositoryInterface $templateRepository
    ) {}

    public function index(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $provider = Auth::guard('provider')->user();
        return $this->templateRepository->paginateProvider($provider->id, $filters, $perPage);
    }

    public function store(array $data): ShiftTemplate
    {
        $provider = Auth::guard('provider')->user();

        $data['provider_id'] = $provider->id;

        return $this->templateRepository->create($data);
    }

    public function show(string $uuid): ShiftTemplate
    {
        $provider = Auth::guard('provider')->user();
        $template = $this->templateRepository->findByUuid($uuid);

        if (!$template || $template->provider_id !== $provider->id) {
            throw new ModelNotFoundException('Shift template not found');
        }

        return $template;
    }

    public function update(string $uuid, array $data): ShiftTemplate
    {
        $template = $this->show($uuid);
        return $this->templateRepository->update($template, $data);
    }

    public function destroy(string $uuid): bool
    {
        $template = $this->show($uuid);
        return $this->templateRepository->softDelete($template);
    }

    public function toggleActive(string $uuid, bool $active): ShiftTemplate
    {
        $template = $this->show($uuid);
        return $this->templateRepository->toggleActive($template, $active);
    }
}
