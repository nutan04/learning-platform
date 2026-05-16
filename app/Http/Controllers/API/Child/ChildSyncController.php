<?php

namespace App\Http\Controllers\API\Child;

use App\Http\Controllers\Controller;
use App\Models\ActiveUnlockSession;
use App\Models\Child;
use App\Models\ScreenTimeSetting;
use Illuminate\Http\Request;

class ChildSyncController extends Controller
{
    public function sync($childId)
    {
        $child = Child::findOrFail($childId);

        $screenTime = ScreenTimeSetting::where('child_id', $childId)->firstOrFail();

        $activeSession = ActiveUnlockSession::where('child_id', $childId)
            ->where('end_time', '>', now())
            ->first();

        // If unlock expired → lock child
        if (!$activeSession) {
            $child->update(['is_unlocked' => false]);
        }

        return response()->json([
            'shouldLock' => !$activeSession,
            'remainingUnlocks' => max(
                $screenTime->daily_unlock_count - $screenTime->used_unlocks_today,
                0
            ),
            'currentPolicy' => [
                'dailyUnlockCount' => $screenTime->daily_unlock_count,
                'unlockDurationMinutes' => $screenTime->unlock_duration_minutes
            ]
        ]);
    }
}
