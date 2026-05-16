<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Psy\Util\Str;

class ActiveUnlockSession extends Model
{
   public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'child_id',
        'start_time',
        'end_time',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }
}
