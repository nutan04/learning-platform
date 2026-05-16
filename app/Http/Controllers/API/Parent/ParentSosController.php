<?php

namespace App\Http\Controllers\API\Parent;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\SosRequest;
use Illuminate\Http\Request;

class ParentSosController extends Controller
{
    public function list()
{
    return SosRequest::with('child:id,name')
        ->whereIn(
        'child_id',
        Child::where('parent_id',auth('parent')->id())->pluck('id')
    )->get();
}

public function approve($id)
{
    SosRequest::where('id',$id)->update([
        'status'=>'approved',
        'approved_by'=>auth('parent')->id(),
        'approved_at'=>now()
    ]);
    return ['success'=>true];
}

public function reject($id)
{
    SosRequest::where('id',$id)->update(['status'=>'rejected']);
    return ['success'=>true];
}

}
