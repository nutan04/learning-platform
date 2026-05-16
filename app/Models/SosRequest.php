<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SosRequest extends Model
{
   public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'child_id',
        'status',
        'approved_by',
        'approved_at'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($m) => $m->id = (string) Str::uuid());
    }

    public function child()
    {
        return $this->belongsTo(Child::class, 'child_id');
    }

     public function approver()
    {
        return $this->belongsTo(ParentUser::class, 'approved_by');
    }
}
