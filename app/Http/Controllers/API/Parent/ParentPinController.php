<?php

namespace App\Http\Controllers\API\Parent;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\ParentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ParentPinController extends Controller
{
    public function setPin(Request $request, $parentId)
    {
        $request->validate([
            'pin_code' => 'required|digits:6'
        ]);

        $parent = ParentUser::findOrFail($parentId);

        if ($parent->parent_pin) {
            return response()->json([
                'success' => false,
                'message' => 'PIN_ALREADY_SET'
            ], 400);
        }

        $parent->update([
            'parent_pin' => Hash::make($request->pin_code),
            'pin_set_at' => now(),
            'failed_pin_attempts' => 0
        ]);

        return response()->json([
            'success' => true,
            'message' => 'PIN_SET_SUCCESS'
        ]);
    }

    public function changePin(Request $request, $parentId)
{
    $request->validate([
        'old_pin' => 'required|digits:6',
        'new_pin' => 'required|digits:6'
    ]);

    $parent = ParentUser::findOrFail($parentId);

    if (!Hash::check($request->old_pin, $parent->parent_pin)) {
        return response()->json([
            'success' => false,
            'message' => 'INVALID_OLD_PIN'
        ], 401);
    }

    $parent->update([
        'parent_pin' => Hash::make($request->new_pin),
        'pin_set_at' => now(),
        'failed_pin_attempts' => 0
    ]);

    return response()->json([
        'success' => true,
        'message' => 'PIN_CHANGED_SUCCESS'
    ]);
}
public function pinStatus($parentId)
{
    $parent = ParentUser::findOrFail($parentId);

    return response()->json([
        'pinSet' => !is_null($parent->parent_pin)
    ]);
}

public function unlock(Request $request, $childId)
    {
        $request->validate([
            'pin_code' => 'required'
        ]);
        
        $child = Child::findOrFail($childId);
        $parent = ParentUser::findOrFail($child->parent_id);

        // PIN validation
        if (!Hash::check($request->pin_code, $parent->parent_pin)) {
            $parent->increment('failed_pin_attempts');
            $parent->update(['last_failed_pin_at' => now()]);

            return response()->json([
                'success' => false,
                'message' => 'INVALID_PIN'
            ], 401);
        }

        // Reset failures on success
        $parent->update(['failed_pin_attempts' => 0]);

        $child->update([
            'is_unlocked' => true,
            'unlocked_by' => 'PARENT_PIN',
            'unlocked_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'unlocked' => true,
            'unlockMethod' => 'PARENT_PIN'
        ]);
    }
}
