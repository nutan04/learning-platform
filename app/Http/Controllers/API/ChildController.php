<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ActiveUnlockSession;
use App\Models\Child;
use App\Models\ChildLinkCode;
use App\Models\Device;
use App\Models\EmergencyContact;
use App\Models\ParentUser;
use App\Models\QuizSession;
use App\Models\ScreenTimeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use DB;

class ChildController extends Controller
{
    public function store(Request $r)
    {
        $r->validate([
            'name' => 'required|string|max:255',
            'grade' => 'required|integer|min:1|max:12',
            'board' => 'required|string|max:50',

            // new fields
            'age' => 'required|integer|min:1|max:18',
            'state' => 'required|string|max:255',
            'schoolName' => 'required|string|max:255',
            'schoolAddress' => 'nullable|string|max:500'
        ]);

        $child = Child::create([
            'parent_id' => auth('parent')->id(),
            'name' => $r->name,
            'grade' => $r->grade,
            'board' => $r->board,

            // new fields mapped correctly
            'age' => $r->age,
            'state' => $r->state,
            'school_name' => $r->schoolName,
            'school_address' => $r->schoolAddress,
            
        ]);

        return response()->json([
            'success' => true,
            'childId' => $child->id,
            'child' => $child
        ]);
    }

    public function show($childId)
    {
        $parentId = auth('parent')->id();
        $child = Child::where('id', $childId)
            ->where('parent_id', $parentId)
            ->firstOrFail();
        
        $linkCode = ChildLinkCode::where('child_id',$childId)->first();

        return response()->json([
            'success' => true,
            'child' => [
                'childId' => $child->id,
                'name' => $child->name,
                'grade' => $child->grade,
                'board' => $child->board,
                'age' => $child->age,
                'state' => $child->state,
                'schoolName' => $child->school_name,
                'schoolAddress' => $child->school_address,
                'subjects' => $child->subjects,
                'createdAt' => $child->created_at,
                'generatedCode' => $linkCode->code
            ]
        ]);
    }

    public function update(Request $r, $childId)
    {
        $parentId = auth('parent')->id();

        // ensure parent owns the child
        $child = Child::where('id', $childId)
            ->where('parent_id', $parentId)
            ->firstOrFail();

        $r->validate([
            'name' => 'required|string|max:255',
            'grade' => 'required|integer|min:1|max:12',
            'board' => 'required|string|max:50',

            'age' => 'required|integer|min:1|max:18',
            'state' => 'required|string|max:255',
            'schoolName' => 'required|string|max:255',
            'schoolAddress' => 'nullable|string|max:500',
            'subjects' => 'required|array|min:1',
            'subjects.*' => 'string|max:100',
        ]);

        $child->update([
            'name' => $r->name,
            'grade' => $r->grade,
            'board' => $r->board,
            'age' => $r->age,
            'state' => $r->state,
            'school_name' => $r->schoolName,
            'school_address' => $r->schoolAddress,
            'subjects' => $r->subjects,
        ]);

        return response()->json([
            'success' => true,
            'child' => [
                'childId' => $child->id,
                'name' => $child->name,
                'grade' => $child->grade,
                'board' => $child->board,
                'age' => $child->age,
                'state' => $child->state,
                'schoolName' => $child->school_name,
                'schoolAddress' => $child->school_address,
                'subjects' => $child->subjects,
                'updatedAt' => $child->updated_at,
            ]
        ]);
    }

    public function generateCode($id)
    {
        $code = strtoupper(Str::random(6));
        ChildLinkCode::create([
            'child_id' => $id,
            'code' => $code,
        ]);
        $children = Child::find($id);
        return ['childLinkCode' => $code, "Child Details" => $children];
    }

    public function destroy($childId)
    {
        try {

            $parentId = auth('parent')->id();

            // ensure parent owns the child
            $child = Child::where('id', $childId)
                ->where('parent_id', $parentId)
                ->firstOrFail();
            $child->delete();

            return response()->json([
                'success' => true,
                'message' => 'Child deleted successfully'
            ]);
            //code...
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    public function getScreenTime($childId)
    {


        $screenTime = ScreenTimeSetting::where('child_id', $childId)->firstOrFail();

        $session = ActiveUnlockSession::where('child_id', $childId)
            ->where('end_time', '>', now())
            ->first();

        return response()->json([
            'isLocked' => !$session,
            "dailyUnlockCount" => $screenTime->daily_unlock_count,
            'unlockDurationMinutes' => $screenTime->unlock_duration_minutes,
            'startTime' => $screenTime->start_time,
            'endTime' => $screenTime->end_time,
            'remainingUnlockMinutes' => $session
                ? now()->diffInMinutes($session->end_time)
                : 0,
            'remainingUnlocks' => max(
                $screenTime->daily_unlock_count - $screenTime->used_unlocks_today,
                0
            ),
            
        ]);
    }
    
    public function usageChart(Request $request, $childId)
    {
        // Ensure child exists
        Child::where('id', $childId)->firstOrFail();
    
        $type = $request->query('type', 'monthly');
    
        if ($type === 'monthly') {
            return $this->monthlyUsage($childId, $request);
        }
    
        if ($type === 'yearly') {
            return $this->yearlyUsage($childId, $request);
        }
    
        return response()->json([
            'success' => false,
            'message' => 'Invalid type. Use monthly or yearly.'
        ], 400);
    }

    private function monthlyUsage($childId, Request $request)
    {
        $year  = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);
    
        $startOfMonth = Carbon::create($year, $month)->startOfMonth();
        $endOfMonth   = Carbon::create($year, $month)->endOfMonth();
    
        $daysInMonth = $startOfMonth->daysInMonth;
    
        $sessions = ActiveUnlockSession::select(
                DB::raw('DAY(start_time) as day'),
                DB::raw('SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as minutes')
            )
            ->where('child_id', $childId)
            ->whereBetween('start_time', [$startOfMonth, $endOfMonth])
            ->groupBy('day')
            ->get()
            ->keyBy('day');
    
        $data = [];
    
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $minutes = $sessions[$day]->minutes ?? 0;
    
            $data[] = [
                'label' => $day,
                'hours' => round($minutes / 60, 2)
            ];
        }
    
        return response()->json([
            'childId' => $childId,
            'type' => 'monthly',
            'year' => (int)$year,
            'month' => (int)$month,
            'data' => $data
        ]);
    }
    private function yearlyUsage($childId, Request $request)
    {
        $year = $request->query('year', now()->year);
    
        $startOfYear = Carbon::create($year)->startOfYear();
        $endOfYear   = Carbon::create($year)->endOfYear();
    
        $sessions = ActiveUnlockSession::select(
                DB::raw('MONTH(start_time) as month'),
                DB::raw('SUM(TIMESTAMPDIFF(MINUTE, start_time, end_time)) as minutes')
            )
            ->where('child_id', $childId)
            ->whereBetween('start_time', [$startOfYear, $endOfYear])
            ->groupBy('month')
            ->get()
            ->keyBy('month');
    
        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Aug',
            9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dec'
        ];
    
        $data = [];
    
        foreach ($months as $num => $label) {
            $minutes = $sessions[$num]->minutes ?? 0;
    
            $data[] = [
                'label' => $label,
                'hours' => round($minutes / 60, 2)
            ];
        }
    
        return response()->json([
            'childId' => $childId,
            'type' => 'yearly',
            'year' => (int)$year,
            'data' => $data
        ]);
    }

       public function performanceChart(Request $request, $childId)
    {
        Child::where('id', $childId)
            ->firstOrFail();

        $type = $request->query('type', 'weekly');

        if ($type === 'weekly') {
            return $this->weeklyPerformance($childId);
        }

        if ($type === 'monthly') {
            return $this->monthlyPerformance($request, $childId);
        }

        if ($type === 'yearly') {
            return $this->yearlyPerformance($childId);
        }

        return response()->json([
            'message' => 'Invalid type'
        ], 400);
    }

    private function weeklyPerformance($childId)
    {
        $start = now()->startOfWeek();
        $end = now()->endOfWeek();

        $sessions = QuizSession::select(
            DB::raw('DAYNAME(created_at) as day'),
            DB::raw('SUM(total_questions) as total'),
            DB::raw('SUM(correct_answers) as correct')
        )
            ->where('child_id', $childId)
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        $data = [];

        foreach ($days as $day) {
            $total = $sessions[$day]->total ?? 0;
            $correct = $sessions[$day]->correct ?? 0;

            $accuracy = $total > 0
                ? round(($correct / $total) * 100, 2)
                : 0;

            $data[] = [
                'label' => substr($day, 0, 3),
                'accuracy' => $accuracy
            ];
        }

        return response()->json([
            'type' => 'weekly',
            'data' => $data
        ]);
    }

    private function monthlyPerformance(Request $request, $childId)
    {
        $month = $request->query('month', now()->month);
        $year = $request->query('year', now()->year);

        $start = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $end = \Carbon\Carbon::create($year, $month, 1)->endOfMonth();

        $sessions = QuizSession::select(
            DB::raw('DAY(created_at) as day'),
            DB::raw('SUM(total_questions) as total'),
            DB::raw('SUM(correct_answers) as correct')
        )
            ->where('child_id', $childId)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->groupBy('day')
            ->get()
            ->keyBy('day');

        $daysInMonth = $start->daysInMonth;

        $data = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {

            $total = $sessions[$day]->total ?? 0;
            $correct = $sessions[$day]->correct ?? 0;

            $accuracy = $total > 0
                ? round(($correct / $total) * 100, 2)
                : 0;

            $data[] = [
                'label' => (string)$day,
                'accuracy' => $accuracy
            ];
        }

        return response()->json([
            'type' => 'monthly',
            'month' => $month,
            'year' => $year,
            'data' => $data
        ]);
    }

    private function yearlyPerformance($childId)
    {
        $start = now()->startOfYear();
        $end = now()->endOfYear();

        $sessions = QuizSession::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(total_questions) as total'),
            DB::raw('SUM(correct_answers) as correct')
        )
            ->where('child_id', $childId)
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $months = [
            1 => 'Jan',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'May',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Aug',
            9 => 'Sep',
            10 => 'Oct',
            11 => 'Nov',
            12 => 'Dec'
        ];

        $data = [];

        foreach ($months as $num => $label) {
            $total = $sessions[$num]->total ?? 0;
            $correct = $sessions[$num]->correct ?? 0;

            $accuracy = $total > 0
                ? round(($correct / $total) * 100, 2)
                : 0;

            $data[] = [
                'label' => $label,
                'accuracy' => $accuracy
            ];
        }

        return response()->json([
            'type' => 'yearly',
            'data' => $data
        ]);
    }
    public function parentDetails($parentId, $childId)
    {
        $exists_child = Child::where('id', $childId)->exists();
        $exists_no_of_unlocks = ScreenTimeSetting::where('child_id', $childId)->whereNotNull('daily_unlock_count')->exists();
        $exists_unlock_duration = ScreenTimeSetting::where('child_id', $childId)->whereNotNull('unlock_duration_minutes')->exists();
        $exists_time = ScreenTimeSetting::where('child_id', $childId)->whereNotNull('start_time')->exists();
        $exists_emergancy_contact = EmergencyContact::where('parent_id', $parentId)->exists();
        $exists_parent_pw = ParentUser::where('id', $parentId)->whereNotNull('parent_pin')->exists();

        return response()->json([
            'Add_Child' => $exists_child,
            'No_of_Unlocks' => $exists_no_of_unlocks,
            'Unlock_Duration' => $exists_unlock_duration,
            'Time' => $exists_time,
            'Emergency_Contact' => $exists_emergancy_contact,
            'Parent_PW' => $exists_parent_pw
        ]);
    }
    public function showDetails($id){
        $child = Child::findOrFail($id);
        $linkCode = ChildLinkCode::where('child_id',$id)->first();
        return response()->json([
            'child' => $child,
            'childLinkCode' => $linkCode ? $linkCode->code : null
        ]);
    }

}
