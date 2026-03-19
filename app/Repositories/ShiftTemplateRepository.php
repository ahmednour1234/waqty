<?php

namespace App\Repositories;

use App\Models\Provider;
use App\Models\ShiftTemplate;
use App\Repositories\Contracts\ShiftTemplateRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ShiftTemplateRepository implements ShiftTemplateRepositoryInterface
{
    public function findByUuid(string $uuid): ?ShiftTemplate
    {
        return ShiftTemplate::whereUuid($uuid)->with(['provider'])->first();
    }

    public function paginateProvider(int $providerId, array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ShiftTemplate::where('provider_id', $providerId);

        $this->applyCommonFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function paginateAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ShiftTemplate::with(['provider']);

        if (isset($filters['provider_uuid'])) {
            $provider = Provider::whereUuid($filters['provider_uuid'])->first();
            if ($provider) {
                $query->where('provider_id', $provider->id);
            }
        }

        $this->applyCommonFilters($query, $filters);

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): ShiftTemplate
    {
        return ShiftTemplate::create($data);
    }

    public function update(ShiftTemplate $template, array $data): ShiftTemplate
    {
        $template->update($data);
        return $template->fresh();
    }

    public function softDelete(ShiftTemplate $template): bool
    {
        return (bool) $template->delete();
    }

    public function toggleActive(ShiftTemplate $template, bool $active): ShiftTemplate
    {
        $template->update(['active' => $active]);
        return $template->fresh();
    }

    private function applyCommonFilters($query, array $filters): void
    {
        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->whereRaw("JSON_EXTRACT(name, '$.ar') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("JSON_EXTRACT(name, '$.en') LIKE ?", ["%{$search}%"]);
            });
        }
    }
}
