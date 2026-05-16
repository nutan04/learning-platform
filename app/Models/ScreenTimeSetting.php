<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScreenTimeSetting extends Model
{
  protected $primaryKey = 'child_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'child_id',
        'daily_unlock_count',
        'unlock_duration_minutes',
        'used_unlocks_today',
        "start_time",
        "end_time",
    ];
}
