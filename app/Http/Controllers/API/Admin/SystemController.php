<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemControl;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    public function get()
{
    return SystemControl::firstOrCreate(
        [],
        ['features' => ['quiz'=>true,'chat'=>false]]
    );
}
public function update(Request $r)
{
    $control = SystemControl::first();
    $control->update($r->all());

    return [
        'success' => true,
        'updatedAt' => $control->updated_at
    ];
}


}
