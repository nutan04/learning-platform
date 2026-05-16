<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChildLinkCode extends Model
{
      protected $fillable = [
        'child_id',
        'code',
        'expires_at',
    ];

    public $timestamps = false;
}
