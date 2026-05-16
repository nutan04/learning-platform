<?php

namespace App\Http\Controllers\API\Child;

use App\Http\Controllers\Controller;
use App\Models\ScreenTimeSetting;
use Illuminate\Http\Request;

class LockController extends Controller
{
    public function lockStatus($childId)
{
    $setting = ScreenTimeSetting::firstOrCreate([
        'child_id' => $childId
    ]);

    return response()->json([
        'isLocked' => $setting->used_unlocks_today >= $setting->daily_unlock_count,
        'usedUnlocksToday' => $setting->used_unlocks_today,
        'dailyUnlockCount' => $setting->daily_unlock_count,
        'remainingUnlocks' => max(
            0,
            $setting->daily_unlock_count - $setting->used_unlocks_today
        )
    ]);
}

}
