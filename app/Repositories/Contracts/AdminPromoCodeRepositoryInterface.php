<?php

namespace App\Repositories\Contracts;

use App\Models\PromoCode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AdminPromoCodeRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function findByUuid(string $uuid): ?PromoCode;

    public function findByCode(string $code): ?PromoCode;

    public function create(array $data): PromoCode;

    public function update(PromoCode $promoCode, array $data): PromoCode;

    public function delete(PromoCode $promoCode): void;

    public function incrementUsage(PromoCode $promoCode): void;
}
