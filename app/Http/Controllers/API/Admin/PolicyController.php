<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\GlobalPolicy;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    public function getScreenTime()
{
    $policy = GlobalPolicy::where('type','screen_time')
        ->latest()->first();

    return response()->json($policy?->data ?? [
        'defaultDailyUnlocks' => 3,
        'defaultUnlockDuration' => 30
    ]);
}
public function updateScreenTime(Request $r)
{
    $policy = GlobalPolicy::create([
        'type' => 'screen_time',
        'data' => [
            'defaultDailyUnlocks' => $r->defaultDailyUnlocks,
            'defaultUnlockDuration' => $r->defaultUnlockDuration
        ]
    ]);

    return [
        'success' => true,
        'policyId' => $policy->id,
        'updatedAt' => $policy->created_at
    ];
}
public function getLearningGate()
{
    $policy = GlobalPolicy::where('type','learning_gate')
        ->latest()->first();

    return response()->json($policy?->data ?? [
        'totalQuizQuestions' => 5,
        'minQuizPassScore' => 3
    ]);
}
public function updateLearningGate(Request $r)
{
    GlobalPolicy::create([
        'type' => 'learning_gate',
        'data' => [
            'totalQuizQuestions' => $r->totalQuizQuestions,
            'minQuizPassScore' => $r->minQuizPassScore
        ]
    ]);

    return ['success'=>true];
}

public function index()
    {
        $policies = GlobalPolicy::all();

        return response()->json([
            'success' => true,
            'data' => $policies
        ]);
    }

     public function update(Request $request, $id)
    {
        $policy = GlobalPolicy::findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|string|max:255',
            'data' => 'required'
        ]);

        $policy->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Global policy updated successfully',
            'data' => $policy
        ]);
    }


}
