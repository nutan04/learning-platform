<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\Device;
use App\Models\ParentUser;
use App\Models\SosRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function overview()
    {
        return [
            'totalParents' => ParentUser::count(),
            'totalChildren' => Child::count(),
            'activeDevices' => Device::count(),
            'totalSOS' => SosRequest::count()
        ];
    }

    public function index()
    {
        $parents = ParentUser::with('children')
            ->withCount('children')
            ->get();

        return response()->json([
            'success' => true,
            'total_parents' => $parents->count(),
            'data' => $parents
        ]);
    }

    public function sosRequests()
    {
        $sosRequests = SOSRequest::with([
            'child.parent',
            'approver'
        ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($sos) {
                return [
                    'sos_id' => $sos->id,
                    'child_name' => $sos->child->name ?? null,
                    'parent_name' => $sos->child->parent->full_name ?? null,
                    'parent_phone' => $sos->child->parent->mobile_number ?? null,
                    'status' => $sos->status,
                    'approved_by' => $sos->approver->full_name  ?? null,
                    'approved_at' => $sos->approved_at,
                    'created_at' => $sos->created_at,
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'SOS requests fetched successfully',
            'data' => $sosRequests
        ]);
    }
    
    public function registrationStats(Request $request)
{
    $type = $request->type ?? 'monthly';

    if ($type === 'monthly') {

        $year  = $request->year ?? date('Y');
        $month = $request->month ?? date('m');

        // Total days in month
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        $labels = range(1, $daysInMonth);

        $parentData = array_fill(0, $daysInMonth, 0);
        $childData  = array_fill(0, $daysInMonth, 0);

        // Parent registrations per day
        $parents = DB::table('parent_users')
            ->selectRaw("DAY(created_at) as day, COUNT(*) as total")
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->groupBy('day')
            ->get();

        foreach ($parents as $row) {
            $parentData[$row->day - 1] = $row->total;
        }

        // Children registrations per day
        $children = DB::table('children')
            ->selectRaw("DAY(created_at) as day, COUNT(*) as total")
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->groupBy('day')
            ->get();

        foreach ($children as $row) {
            $childData[$row->day - 1] = $row->total;
        }

        return response()->json([
            'type'     => 'monthly',
            'year'     => (int)$year,
            'month'    => (int)$month,
            'labels'   => $labels,
            'parent'   => $parentData,
            'children' => $childData
        ]);
    }

    elseif ($type === 'yearly') {

        $year = $request->year ?? date('Y');

        $labels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

        $parentData = array_fill(0, 12, 0);
        $childData  = array_fill(0, 12, 0);

        // Parent per month
        $parents = DB::table('parent_users')
            ->selectRaw("MONTH(created_at) as month, COUNT(*) as total")
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->get();

        foreach ($parents as $row) {
            $parentData[$row->month - 1] = $row->total;
        }

        // Children per month
        $children = DB::table('children')
            ->selectRaw("MONTH(created_at) as month, COUNT(*) as total")
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->get();

        foreach ($children as $row) {
            $childData[$row->month - 1] = $row->total;
        }

        return response()->json([
            'type'     => 'yearly',
            'year'     => (int)$year,
            'labels'   => $labels,
            'parent'   => $parentData,
            'children' => $childData
        ]);
    }
}
   
}
