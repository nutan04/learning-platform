<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    //
    public function sendOtp(Request $r){
  OtpSession::updateOrCreate(
    ['mobile_number'=>$r->mobileNumber],
    ['otp'=>123456,'expires_at'=>now()->addMinutes(5)]
  );
  return ['success'=>true];
}

public function verifyOtp(Request $r){
  $otp = OtpSession::where('mobile_number',$r->mobileNumber)
    ->where('otp',$r->otp)
    ->where('expires_at','>',now())->firstOrFail();

  $parent = ParentUser::firstOrCreate([
     'mobile_number'=>$r->mobileNumber
  ]);

  $token = auth('parent')->login($parent);
  return ['token'=>$token,'parentId'=>$parent->id];
}

}
