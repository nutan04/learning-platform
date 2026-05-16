<?php

namespace Tests\Feature;

use App\Models\ActiveUnlockSession;
use App\Models\Child;
use App\Models\ParentUser;
use App\Models\ScreenTimeSetting;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class UsageChartTest extends TestCase
{
    use RefreshDatabase;

    public function test_child_monthly_usage_chart_returns_unlock_hours(): void
    {
        $parent = ParentUser::create([
            'id' => (string) Str::uuid(),
            'mobile_number' => '9000000001',
        ]);

        $child = Child::create([
            'parent_id' => $parent->id,
            'name' => 'Test Child',
            'grade' => 5,
            'board' => 'CBSE',
        ]);

        ScreenTimeSetting::create([
            'child_id' => $child->id,
            'daily_unlock_count' => 3,
            'unlock_duration_minutes' => 60,
            'used_unlocks_today' => 0,
        ]);

        $day = Carbon::now()->day;

        ActiveUnlockSession::create([
            'child_id' => $child->id,
            'start_time' => Carbon::now()->startOfDay()->addHours(10),
            'end_time' => Carbon::now()->startOfDay()->addHours(11),
        ]);

        $response = $this->getJson("/api/child/{$child->id}/usage-chart?type=monthly");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('type', 'monthly');

        $hoursForToday = collect($response->json('data'))
            ->firstWhere('label', $day)['hours'];

        $this->assertEquals(1.0, $hoursForToday);
    }

    public function test_parent_usage_chart_requires_child_id(): void
    {
        $parent = ParentUser::create([
            'id' => (string) Str::uuid(),
            'mobile_number' => '9000000002',
        ]);

        $response = $this->actingAs($parent, 'parent')
            ->getJson("/api/parent-usage/{$parent->id}");

        $response->assertStatus(400)
            ->assertJsonPath('success', false);
    }
}
