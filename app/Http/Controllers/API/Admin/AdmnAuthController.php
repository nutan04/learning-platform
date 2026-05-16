<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdmnAuthController extends Controller
{
    public function login(Request $r)
{
    $admin = Admin::where('email',$r->email)->first();

    if(!$admin || !Hash::check($r->password,$admin->password)){
        return response()->json(['message'=>'Invalid'],401);
    }

    $token = auth('admin')->login($admin);

    return [
        'token'=>$token,
        'role'=>$admin->role
    ];
}

}
