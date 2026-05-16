<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceRefreshToken extends Model
{
     protected $fillable = [
        'device_id',
        'child_id',
        'token_hash',
        'expires_at'
    ];
}
