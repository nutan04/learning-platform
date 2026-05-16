<?php

namespace App\Services;

use App\Models\ActiveUnlockSession;
use App\Models\ScreenTimeSetting;
use Carbon\Carbon;

class UnlockSessionService
{
    public function start(string $childId, ?int $durationMinutes = null): ActiveUnlockSession
    {
        $settings = ScreenTimeSetting::where('child_id', $childId)->firstOrFail();
        $duration = $durationMinutes ?? (int) $settings->unlock_duration_minutes;

        $this->closeActiveSessions($childId);

        $now = Carbon::now();

        return ActiveUnlockSession::create([
            'child_id' => $childId,
            'start_time' => $now,
            'end_time' => $now->copy()->addMinutes(max($duration, 1)),
        ]);
    }

    public function closeActiveSessions(string $childId): void
    {
        ActiveUnlockSession::where('child_id', $childId)
            ->where('end_time', '>', now())
            ->update(['end_time' => now()]);
    }
}
