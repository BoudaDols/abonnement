<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

abstract class BaseModel extends Model
{
    protected static function schema(): \Illuminate\Database\Schema\Builder
    {
        return Capsule::schema();
    }
}