<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\ChildLinkCode;
use App\Models\Device;
use App\Models\ParentUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
  public function link(Request $r)
  {
    $link = ChildLinkCode::where('code', $r->childLinkCode)->firstOrFail();

    $device = Device::create([
      'child_id' => $link->child_id,
      'device_uuid' => $r->deviceInfo['uuid'],
      'is_verified' => true
    ]);

    return [
      'success' => true,
      'deviceId' => $device->id,
      'childId' => $link->child_id,
      'parentId' => Child::find($link->child_id)->parent_id,
      "child" => Child::find($link->child_id),
      "parent" =>ParentUser::find(Child::find($link->child_id)->parent_id)
    ];
  }
}
