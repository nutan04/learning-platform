<?php

namespace App\Http\Controllers\API\Parent;

use App\Http\Controllers\Controller;
use App\Models\EmergencyContact;
use Illuminate\Http\Request;

class EmergencyContactController extends Controller
{
    public function index()
{
    return EmergencyContact::where(
        'parent_id', auth('parent')->id()
    )->get();
}

public function store(Request $r)
{
    return EmergencyContact::create([
        'parent_id' => auth('parent')->id(),
        'name' => $r->name,
        'relation' => $r->relation,
        'phone_number' => $r->phoneNumber,
        'is_active' => true
    ]);
}

public function update(Request $r, $id)
{
    EmergencyContact::where('id',$id)->update($r->all());
    return ['success'=>true];
}

public function destroy($id)
{
    EmergencyContact::where('id',$id)->delete();
    return ['success'=>true];
}

public function parentEmergencyContact($id){
 $emergencyContact = EmergencyContact::where('parent_id',$id)->get();
    return ['success'=>true,"EmergencyContact"=>$emergencyContact];
}

}
