<?php

namespace App\Http\Controllers\API\Child;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;

class ChildDeviceController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'deviceId' => 'required|uuid',
            'childId'  => 'required|uuid',
            'isPrimary'=> 'required|boolean',
        ]);

        $device = Device::where('id', $request->deviceId)
            ->where('child_id', $request->childId)
            ->firstOrFail();
        // if first device, force primary
        $hasPrimary = Device::where('child_id', $request->childId)
            ->where('is_primary', true)
            ->exists();

        $device->update([
            'is_verified' => true,
            'is_primary'  => $hasPrimary ? $request->isPrimary : true,
        ]);

        return response()->json([
            'success' => true,
            'isDeviceVerified' => true
        ]);
    }
}
