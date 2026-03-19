<?php

namespace App\Services;

use App\Models\Shift;
use App\Models\ShiftTemplate;
use App\Repositories\Contracts\ShiftRepositoryInterface;
use App\Repositories\Contracts\ShiftTemplateRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminShiftService
{
    public function __construct(
        private ShiftRepositoryInterface         $shiftRepository,
        private ShiftTemplateRepositoryInterface $templateRepository
    ) {}

    // ─── Shifts ────────────────────────────────────────────────────────────

    public function indexShifts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->shiftRepository->paginateAdmin($filters, $perPage);
    }

    public function showShift(string $uuid): Shift
    {
        $shift = $this->shiftRepository->findByUuid($uuid);

        if (!$shift) {
            throw new ModelNotFoundException('Shift not found');
        }

        return $shift;
    }

    // ─── Shift Templates ───────────────────────────────────────────────────

    public function indexTemplates(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->templateRepository->paginateAdmin($filters, $perPage);
    }

    public function showTemplate(string $uuid): ShiftTemplate
    {
        $template = $this->templateRepository->findByUuid($uuid);

        if (!$template) {
            throw new ModelNotFoundException('Shift template not found');
        }

        return $template;
    }
}
