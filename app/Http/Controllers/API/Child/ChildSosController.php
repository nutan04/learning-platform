<?php

namespace App\Http\Controllers\API\Child;

use App\Http\Controllers\Controller;
use App\Models\SosRequest;
use Illuminate\Http\Request;

class ChildSosController extends Controller
{
    public function request($childId)
{
    $sos = SosRequest::create([
        'child_id'=>$childId
    ]);

    return [
        'success'=>true,
        'sosRequestId'=>$sos->id,
        'status'=>'pending'
    ];
}

public function status($id)
{
    return SosRequest::findOrFail($id);
}

}
