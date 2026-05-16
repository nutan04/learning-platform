<?php

namespace App\Http\Controllers\API\Child;

use App\Http\Controllers\Controller;
use App\Models\ActiveUnlockSession;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ChildUnlockController extends Controller
{
    public function currentSession($childId)
    {
        $session = ActiveUnlockSession::where('child_id', $childId)
            ->where('end_time', '>', now())
            ->first();

        if (!$session) {
            return response()->json([
                'isUnlocked' => false
            ]);
        }

        return response()->json([
            'isUnlocked' => true,
            'remainingMinutes' => now()->diffInMinutes(
                Carbon::parse($session->end_time)
            ),
            'currentSessionEnd' => $session->end_time
        ]);
    }
}
