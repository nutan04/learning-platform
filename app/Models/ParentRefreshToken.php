<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentRefreshToken extends Model
{
   protected $fillable = [
        'parent_id',
        'token_hash',
        'expires_at'
    ];
}
