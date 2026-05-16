<?php

namespace App\Services;

use App\Models\ActiveUnlockSession;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class UsageChartService
{
    public function monthly(string $childId, int $year, int $month): array
    {
        $year = $this->normalizeYear($year);
        $month = $this->normalizeMonth($month);

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = Carbon::create($year, $month, 1)->endOfMonth();

        $minutesByDay = $this->aggregateMinutesByDay(
            $this->sessionsInRange($childId, $start, $end)
        );

        $data = [];
        for ($day = 1; $day <= $start->daysInMonth; $day++) {
            $minutes = $minutesByDay[$day] ?? 0;
            $data[] = [
                'label' => $day,
                'hours' => round($minutes / 60, 2),
            ];
        }

        return [
            'success' => true,
            'childId' => $childId,
            'type' => 'monthly',
            'year' => $year,
            'month' => $month,
            'data' => $data,
        ];
    }

    public function yearly(string $childId, int $year): array
    {
        $year = $this->normalizeYear($year);

        $start = Carbon::create($year, 1, 1)->startOfYear();
        $end = Carbon::create($year, 12, 31)->endOfYear();

        $minutesByMonth = $this->aggregateMinutesByMonth(
            $this->sessionsInRange($childId, $start, $end)
        );

        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec',
        ];

        $data = [];
        foreach ($months as $num => $label) {
            $minutes = $minutesByMonth[$num] ?? 0;
            $data[] = [
                'label' => $label,
                'hours' => round($minutes / 60, 2),
            ];
        }

        return [
            'success' => true,
            'childId' => $childId,
            'type' => 'yearly',
            'year' => $year,
            'data' => $data,
        ];
    }

    private function sessionsInRange(string $childId, Carbon $start, Carbon $end): Collection
    {
        return ActiveUnlockSession::query()
            ->where('child_id', $childId)
            ->whereBetween('start_time', [$start, $end])
            ->whereNotNull('end_time')
            ->get(['start_time', 'end_time']);
    }

    private function aggregateMinutesByDay(Collection $sessions): array
    {
        $result = [];

        foreach ($sessions as $session) {
            $start = Carbon::parse($session->start_time);
            $end = Carbon::parse($session->end_time);
            $minutes = $this->sessionDurationMinutes($start, $end);

            if ($minutes === 0) {
                continue;
            }

            $day = (int) $start->day;
            $result[$day] = ($result[$day] ?? 0) + $minutes;
        }

        return $result;
    }

    private function aggregateMinutesByMonth(Collection $sessions): array
    {
        $result = [];

        foreach ($sessions as $session) {
            $start = Carbon::parse($session->start_time);
            $end = Carbon::parse($session->end_time);
            $minutes = $this->sessionDurationMinutes($start, $end);

            if ($minutes === 0) {
                continue;
            }

            $month = (int) $start->month;
            $result[$month] = ($result[$month] ?? 0) + $minutes;
        }

        return $result;
    }

    private function normalizeYear(int $year): int
    {
        if ($year < 2000 || $year > 2100) {
            return (int) now()->year;
        }

        return $year;
    }

    private function normalizeMonth(int $month): int
    {
        if ($month < 1 || $month > 12) {
            return (int) now()->month;
        }

        return $month;
    }

    private function sessionDurationMinutes(Carbon $start, Carbon $end): int
    {
        if ($end->lte($start)) {
            return 0;
        }

        return (int) $start->diffInMinutes($end);
    }
}
