<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    */

    'guards' => [

        // Default Laravel web guard
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // ✅ PARENT JWT GUARD (REQUIRED)
        'parent' => [
            'driver' => 'jwt',
            'provider' => 'parents',
        ],
        
        'admin' => [
        'driver' => 'jwt',
        'provider' => 'admins',
    ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [

        // Default Laravel users
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        // ✅ PARENT PROVIDER (REQUIRED)
        'parents' => [
            'driver' => 'eloquent',
            'model' => App\Models\ParentUser::class,
        ],
        'admins' => [
        'driver' => 'eloquent',
        'model' => App\Models\Admin::class,
    ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */

    'password_timeout' => 10800,

];
