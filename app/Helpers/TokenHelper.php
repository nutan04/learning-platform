<?php
namespace App\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class TokenHelper
{
    public static function generateRefreshToken()
    {
        $plain = Str::random(64);
        return [
            'plain' => $plain,
            'hash' => hash('sha256', $plain)
        ];
    }
}
