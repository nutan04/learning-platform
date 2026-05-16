<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\OtpSession;
use App\Models\ParentRefreshToken;
use App\Models\ParentUser;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Helpers\TokenHelper;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
 public function sendOtp(Request $r)
    {
        $r->validate([
            'mobileNumber' => 'required|digits:10'
        ]);

        // Generate random OTP
        $otp = rand(100000, 999999);


        // Store or update OTP
        OtpSession::updateOrCreate(
            ['mobile_number' => $r->mobileNumber],
            [
                'otp' => $otp,
                'expires_at' => Carbon::now()->addMinutes(5)
            ]
        );

        // Send OTP via Fast2SMS
        $response = Http::withHeaders([
            'authorization' => "fLrhsOn26j7Ib3JdUuxXVPl9KH1BaGge80tRSp5AMNCq4oEzZytav26U4Exb9d0uyVl7QHLjJZWTR1cw",
        ])->asForm()->post('https://www.fast2sms.com/dev/bulkV2', [
            'route' => 'dlt',
            'sender_id' => 'LNXTRA',
            'message' => '212546', // ✅ THIS IS TEMPLATE ID (NOT TEXT)
            'variables_values' => $otp,
            'numbers' => $r->mobileNumber,
            'flash' => 0
        ]);

        return [
            'success' => true,
            'message' => 'OTP sent',
            // remove this in production
            'otp' => $otp,
            'sms_response' => $response->json()
        ];
    }

  public function verifyOtp(Request $r) {
    try {
        OtpSession::where('mobile_number', $r->mobileNumber)
            ->where('otp', $r->otp)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        $parent = ParentUser::firstOrCreate(
            ['mobile_number' => $r->mobileNumber],
            ['is_verified' => true]
        );

        // 🔑 1. ACCESS TOKEN (short-lived JWT)
        $accessToken = auth('parent')->login($parent);

        // 🔁 2. REFRESH TOKEN (long-lived)
        $refresh = TokenHelper::generateRefreshToken();

        ParentRefreshToken::create([
            'parent_id'  => $parent->id,
            'token_hash' => $refresh['hash'],
            'expires_at' => now()->addDays(30),
        ]);

        return [
            'success'      => true,
            'parentId'     => $parent->id,
            'accessToken'  => $accessToken,
            'refreshToken' => $refresh['plain'],
            'expiresIn'    => 900, // 15 minutes
            'isNewUser'    => $parent->wasRecentlyCreated
        ];

    } catch (\Throwable $th) {
      dd($th);
        return [
            'success' => false,
            'message' => 'Invalid OTP'
        ];
    }
  }

  public function refresh(Request $request)
{
    $request->validate([
        'refreshToken' => 'required'
    ]);

    $hashed = hash('sha256', $request->refreshToken);

    $record = ParentRefreshToken::where('token_hash', $hashed)
        ->whereNull('revoked_at')
        ->where('expires_at', '>', now())
        ->firstOrFail();

    $parent = ParentUser::findOrFail($record->parent_id);

    // revoke old refresh token
    $record->update(['revoked_at' => now()]);

    // generate new refresh token
    $newRefresh = TokenHelper::generateRefreshToken();

    ParentRefreshToken::create([
        'parent_id'  => $parent->id,
        'token_hash' => $newRefresh['hash'],
        'expires_at' => now()->addDays(30),
    ]);

    return response()->json([
        'accessToken'  => auth('parent')->login($parent),
        'refreshToken' => $newRefresh['plain'],
        'expiresIn'    => 900
    ]);
}

}
