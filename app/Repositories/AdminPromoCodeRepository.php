<?php

namespace App\Repositories;

use App\Models\PromoCode;
use App\Repositories\Contracts\AdminPromoCodeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminPromoCodeRepository implements AdminPromoCodeRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = PromoCode::with('createdByAdmin');

        if (!empty($filters['search'])) {
            $query->where('code', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['active'])) {
            $query->where('active', $filters['active']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['expired'])) {
            if ($filters['expired']) {
                $query->where('valid_until', '<', now()->toDateString());
            } else {
                $query->where('valid_until', '>=', now()->toDateString());
            }
        }

        if (!empty($filters['trashed']) && $filters['trashed'] === 'only') {
            $query->onlyTrashed();
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function findByUuid(string $uuid): ?PromoCode
    {
        return PromoCode::withTrashed()
            ->with('createdByAdmin')
            ->where('uuid', $uuid)
            ->first();
    }

    public function findByCode(string $code): ?PromoCode
    {
        return PromoCode::where('code', strtoupper($code))->first();
    }

    public function create(array $data): PromoCode
    {
        $promoCode = PromoCode::create($data);
        return $promoCode->load('createdByAdmin');
    }

    public function update(PromoCode $promoCode, array $data): PromoCode
    {
        $promoCode->update($data);
        return $promoCode->fresh('createdByAdmin');
    }

    public function delete(PromoCode $promoCode): void
    {
        $promoCode->delete();
    }

    public function incrementUsage(PromoCode $promoCode): void
    {
        $promoCode->increment('usage_count');
    }
}
