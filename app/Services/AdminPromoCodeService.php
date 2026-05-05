<?php

namespace App\Services;

use App\Models\PromoCode;
use App\Repositories\Contracts\AdminPromoCodeRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class AdminPromoCodeService
{
    public function __construct(
        private AdminPromoCodeRepositoryInterface $repository,
    ) {}

    public function index(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    public function show(string $uuid): PromoCode
    {
        $promoCode = $this->repository->findByUuid($uuid);

        if (!$promoCode) {
            throw new ModelNotFoundException('Promo code not found.');
        }

        return $promoCode;
    }

    public function validate(string $code, float $orderAmount = 0): PromoCode
    {
        $promoCode = $this->repository->findByCode($code);

        if (!$promoCode) {
            throw new ModelNotFoundException('Promo code not found.');
        }

        if (!$promoCode->active) {
            throw ValidationException::withMessages(['code' => 'This promo code is inactive.']);
        }

        if ($promoCode->isExpired()) {
            throw ValidationException::withMessages(['code' => 'This promo code has expired.']);
        }

        if ($promoCode->isExhausted()) {
            throw ValidationException::withMessages(['code' => 'This promo code has reached its usage limit.']);
        }

        if ($orderAmount < $promoCode->min_order) {
            throw ValidationException::withMessages([
                'code' => 'Minimum order amount of ' . $promoCode->min_order . ' EGP required.',
            ]);
        }

        return $promoCode;
    }

    public function create(array $data, int $adminId): PromoCode
    {
        $code = strtoupper(trim($data['code']));

        if ($this->repository->findByCode($code)) {
            throw ValidationException::withMessages(['code' => 'A promo code with this code already exists.']);
        }

        return $this->repository->create([
            'code'                => $code,
            'type'                => $data['type']          ?? 'percentage',
            'value'               => $data['value'],
            'min_order'           => $data['min_order']     ?? 0,
            'max_discount'        => $data['max_discount']  ?? null,
            'usage_limit'         => $data['usage_limit']   ?? null,
            'usage_count'         => 0,
            'valid_until'         => $data['valid_until'],
            'active'              => $data['active']        ?? true,
            'created_by_admin_id' => $adminId,
        ]);
    }

    public function update(string $uuid, array $data): PromoCode
    {
        $promoCode = $this->show($uuid);

        if (!empty($data['code'])) {
            $newCode    = strtoupper(trim($data['code']));
            $existing   = $this->repository->findByCode($newCode);
            if ($existing && $existing->id !== $promoCode->id) {
                throw ValidationException::withMessages(['code' => 'A promo code with this code already exists.']);
            }
            $data['code'] = $newCode;
        }

        $updateData = array_filter([
            'code'         => $data['code']         ?? null,
            'type'         => $data['type']         ?? null,
            'value'        => $data['value']        ?? null,
            'min_order'    => $data['min_order']    ?? null,
            'max_discount' => array_key_exists('max_discount', $data) ? $data['max_discount'] : 'skip',
            'usage_limit'  => array_key_exists('usage_limit', $data)  ? $data['usage_limit']  : 'skip',
            'valid_until'  => $data['valid_until']  ?? null,
        ], fn($v) => $v !== null && $v !== 'skip');

        // Handle nullable fields explicitly
        if (array_key_exists('max_discount', $data)) {
            $updateData['max_discount'] = $data['max_discount'];
        }
        if (array_key_exists('usage_limit', $data)) {
            $updateData['usage_limit'] = $data['usage_limit'];
        }

        return $this->repository->update($promoCode, $updateData);
    }

    public function setActive(string $uuid, bool $active): PromoCode
    {
        $promoCode = $this->show($uuid);
        return $this->repository->update($promoCode, ['active' => $active]);
    }

    public function destroy(string $uuid): void
    {
        $promoCode = $this->show($uuid);
        $this->repository->delete($promoCode);
    }
}
