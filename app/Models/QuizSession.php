<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class QuizSession extends Model
{
   public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['child_id','correct_answers','is_passed','total_questions'];

    protected static function boot()
    {
        parent::boot();
        static::creating(fn($m) => $m->id = (string) Str::uuid());
    }
    public $timestamps = false;
}
