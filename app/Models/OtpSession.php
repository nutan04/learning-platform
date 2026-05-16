<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpSession extends Model
{
       protected $fillable = [
        'mobile_number',
        'otp',
        'expires_at',
    ];

    public $timestamps = false;
}
