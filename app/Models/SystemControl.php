<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemControl extends Model
{
       protected $fillable = [
        'maintenance_mode',
        'emergency_unlock_enabled',
        'features'
    ];

    protected $casts = [
        'features' => 'array'
    ];
}
