<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Child extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';
   protected $fillable = [
        'parent_id',
        'name',
        'grade',
        'board',
        'age',
        'state',
        'school_name',
        'school_address',
        'strong_subjects',
        'weak_subjects',
    ];

    protected $casts = [
        'strong_subjects' => 'array',
        'weak_subjects' => 'array',
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

    public function parent()
    {
        return $this->belongsTo(ParentUser::class, 'parent_id');
    }
}
