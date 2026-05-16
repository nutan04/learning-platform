<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GlobalPolicy extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['type','data'];

    protected $casts = [
        'data' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($m) => $m->id = (string) Str::uuid());
    }
}
