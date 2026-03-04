<?php

namespace App\Models;

use App\Models\Concerns\UserScoped;
use Illuminate\Database\Eloquent\Model as EloquentModel;

abstract class BaseModel extends EloquentModel
{
    use UserScoped;

    protected $guarded = ['id', 'created_at', 'updated_at'];
}
