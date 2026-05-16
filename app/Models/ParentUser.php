<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

class ParentUser extends Authenticatable implements JWTSubject
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'mobile_number',
        'is_verified',
        'last_login_at',
        'full_name',
        'email',
        'profile_image_url',
        'address',
        'pin_code',
        'parent_pin',
        'pin_set_at',
        'failed_pin_attempts',
        'last_failed_pin_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
    public function children()
    {
        return $this->hasMany(Child::class, 'parent_id');
    }
}
