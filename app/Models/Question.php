<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Question extends Model
{
     public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'grade',
        'board',
        'question_text',
        'options',
        'correct_answer',
        'subject'
    ];

    protected $casts = [
        'options' => 'array'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($m) => $m->id = (string) Str::uuid());
    }
    public $timestamps = false;
}
