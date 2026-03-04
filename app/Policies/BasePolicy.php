<?php

namespace App\Policies;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

abstract class BasePolicy
{
    public function viewAny(Authenticatable $user): bool
    {
        return $this->hasUserContext($user);
    }

    public function view(Authenticatable $user, Model $model): bool
    {
        return $this->hasUserContext($user) && $this->isOwner($user, $model);
    }

    public function create(Authenticatable $user): bool
    {
        return $this->hasUserContext($user);
    }

    public function update(Authenticatable $user, Model $model): bool
    {
        return $this->hasUserContext($user) && $this->isOwner($user, $model);
    }

    public function delete(Authenticatable $user, Model $model): bool
    {
        return $this->hasUserContext($user) && $this->isOwner($user, $model);
    }

    protected function hasUserContext(Authenticatable $user): bool
    {
        return $user->id !== null;
    }

    protected function isOwner(Authenticatable $user, Model $model): bool
    {
        $userIdColumn = method_exists($model, 'getUserIdColumn')
            ? $model->getUserIdColumn()
            : 'user_id';

        return isset($model->{$userIdColumn}) && $model->{$userIdColumn} === $user->id;
    }
}
