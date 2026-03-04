<?php

namespace App\Database\Query;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ScopedQueryBuilder extends Builder
{
    public function get($columns = ['*'])
    {
        $this->validateUserContext();
        return parent::get($columns);
    }

    public function first($columns = ['*'])
    {
        $this->validateUserContext();
        return parent::first($columns);
    }

    public function find($id, $columns = ['*'])
    {
        $this->validateUserContext();
        return parent::find($id, $columns);
    }

    public function count($columns = '*')
    {
        $this->validateUserContext();
        return parent::count($columns);
    }

    public function exists()
    {
        $this->validateUserContext();
        return parent::exists();
    }

    protected function validateUserContext(): void
    {
        if (!Auth::check()) {
            Log::warning('Query executed without user context', [
                'query' => $this->toSql(),
                'bindings' => $this->getBindings(),
            ]);
        }
    }
}
