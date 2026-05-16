<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    protected $fillable = ['name', 'unique_board_id'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($board) {
            $last = self::latest('id')->first();
            $number = $last ? $last->id + 1 : 1;
            $board->unique_board_id = 'BD_' . str_pad($number, 2, '0', STR_PAD_LEFT);
        });
    }
}
