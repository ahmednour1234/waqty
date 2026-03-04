<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class UserScope implements Scope
{
    public function apply(Builder $builder, $model): void
    {
        if (Auth::check() && $model instanceof Model) {
            $builder->where($model->getTable().'.user_id', Auth::id());
        }
    }

    public function extend(Builder $builder): void
    {
        $builder->macro('withoutUserScope', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}

trait UserScoped
{
    protected static function bootUserScoped(): void
    {
        static::addGlobalScope(new UserScope);

        static::creating(function ($model) {
            if (Auth::check() && !isset($model->user_id)) {
                $model->user_id = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check() && $model->isDirty() && !$model->user_id) {
                $model->user_id = Auth::id();
            }
        });
    }

    public function getUserIdColumn(): string
    {
        return 'user_id';
    }
}
