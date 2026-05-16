<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['name', 'unique_subject_id','board_id','grade_id'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subject) {
            $last = self::latest('id')->first();
            $number = $last ? $last->id + 1 : 1;
            $subject->unique_subject_id = 'SB_' . str_pad($number, 2, '0', STR_PAD_LEFT);
        });
    }
}
