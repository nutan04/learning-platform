<?php

namespace App\Http\Controllers\API\Parent;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\QuizSession;
use App\Models\ScreenTimeSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActiveUnlockSession;
use App\Services\UsageChartService;
use Carbon\Carbon;
use App\Models\ParentUser;
use Illuminate\Support\Facades\DB;

class ParentController extends Controller
{
    public function updateProfile(Request $request)
    {
        $request->validate([
            'fullName' => 'nullable|string|max:255',
            'email' => 'nullable|email',
            'profileImageUrl' => 'nullable|url',
            'address' => 'nullable|string',
            'pinCode' => 'nullable|digits:6',
        ]);

        $parent = Auth::guard('parent')->user();

        $parent->update([
            'full_name' => $request->fullName,
            'email' => $request->email,
            'profile_image_url' => $request->profileImageUrl,
            'address' => $request->address,
            'pin_code' => $request->pinCode,
        ]);

        return response()->json([
            'success' => true,
            'updatedProfile' => [
                'parentId' => $parent->id,
                'fullName' => $parent->full_name,
                'email' => $parent->email,
                'profileImageUrl' => $parent->profile_image_url,
                'address' => $parent->address,
                'pinCode' => $parent->pin_code,
            ]
        ]);
    }
    public function uploadProfilePhoto(Request $request)
    {
        $request->validate([
            'file' => 'required|image|max:2048'
        ]);

        // dummy storage (local)
        $path = $request->file('file')->store('profiles', 'public');

        $parent = Auth::guard('parent')->user();
        $parent->update([
            'profile_image_url' => asset('storage/' . $path)
        ]);

        return response()->json([
            'success' => true,
            'profileImageUrl' => asset('storage/' . $path)
        ]);
    }

    public function index()
    {
        $parent = Auth::guard('parent')->user();

        $children = Child::where('parent_id', $parent->id)->get();

        $childrenData = $children->map(function ($child) {

            // screen-time settings
            $screenTime = ScreenTimeSetting::where('child_id', $child->id)->first();

            // active unlock session (if any)
            $unlockSession = ActiveUnlockSession::where('child_id', $child->id)
                ->where('end_time', '>', now())
                ->first();

            return [
                'childId' => $child->id,
                'name' => $child->name,
                'grade' => $child->grade,
                'isActive' => true,

                // screen-time settings
                'dailyUnlockCount' => $screenTime?->daily_unlock_count ?? 0,
                'unlockDurationMinutes' => $screenTime?->unlock_duration_minutes ?? 0,
                'usedUnlocksToday' => $screenTime?->used_unlocks_today ?? 0,

                // unlock session info
                'isUnlocked' => $unlockSession ? true : false,
                'remainingUnlockMinutes' => $unlockSession
                    ? Carbon::now()->diffInMinutes($unlockSession->end_time)
                    : 0,
            ];
        });

        return response()->json([
            'parentName' => $parent->full_name,
            'activeChildrenCount' => $children->count(),
            'children' => $childrenData,
            'screenTimeSummary' => [
                'totalUnlocks' => 5,
                'usedToday' => 1,
                'remaining' => 4
            ],
            'analytics' => [
                'barGraphData' => [],
                'screenTimeTrends' => []
            ]
        ]);
    }

    public function get($childId)
    {
        $parentId = auth('parent')->id();

       $data= Child::where('id', $childId)
            ->where('parent_id', $parentId)
            ->first();
            
            if(empty($data)){
                return response()->json([
                    'message' => 'Child not found'
                ], 404);
            }
        $screenTime = ScreenTimeSetting::where('child_id', $childId)->firstOrFail();

        $session = ActiveUnlockSession::where('child_id', $childId)
            ->where('end_time', '>', now())
            ->first();

        return response()->json([
            'dailyUnlockCount' => $screenTime->daily_unlock_count,
            'unlockDurationMinutes' => $screenTime->unlock_duration_minutes,
            'usedUnlocks' => $screenTime->used_unlocks_today,
            'remainingUnlocks' => max(
                $screenTime->daily_unlock_count - $screenTime->used_unlocks_today,
                0
            ),
            'startTime' => $screenTime->start_time,
            'endTime' => $screenTime->end_time,
            "used_unlocks_today" => $screenTime->used_unlocks_today,
            'currentSession' => [
                'isUnlocked' => (bool) $session,
                'remainingMinutes' => $session
                    ? now()->diffInMinutes($session->end_time)
                    : 0,
            ]
        ]);
    }

    public function update(Request $r, $childId)
    {
        try{
        $parentId = auth('parent')->id();
        Child::where('id', $childId)
            ->where('parent_id', $parentId)
            ->firstOrFail();
        
        $r->validate([
            'defaultDailyUnlocks' => 'required|integer|min:1',
            'defaultUnlockDuration' => 'required|integer|min:5',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
        ]);
        ScreenTimeSetting::where('child_id', $childId)->update([
            'daily_unlock_count' => $r->defaultDailyUnlocks,
            'unlock_duration_minutes' => $r->defaultUnlockDuration,
            'start_time' => $r->start_time,
            'end_time' => $r->end_time,
        ]);

        return response()->json(['success' => true, 'message' => 'Screen time rules updated']);
        } catch (\Throwable $th) {
            dd($th);
        }
    }

    public function show($childId)
    {
        $sessions = QuizSession::where('child_id', $childId)->get();

        $total   = $sessions->count();
        $passed  = $sessions->where('is_passed', true)->count();

        return response()->json([
            'dailyQuizScore' => $sessions->last()?->score ?? 0,
            'totalRewards'   => $sessions->sum('reward_points'),
            'accuracyPercentage' => $total > 0
                ? round(($passed / $total) * 100, 2)
                : 0,
            'weeklyProgress' => [],
            'strengthWeaknessSummary' => [
                'strong' => [],
                'weak'   => []
            ]
        ]);
    }

    public function getProfile()
    {
        $parent = Auth::guard('parent')->user();

        $linkedChildren = Child::where('parent_id', $parent->id)
            ->pluck('id');

        return response()->json([
            'fullName' => $parent->full_name,
            'email' => $parent->email,
            'address' => $parent->address,
            'pinCode' => $parent->pin_code,
            'profileImageUrl' => $parent->profile_image_url,
            'linkedChildren' => $linkedChildren,
        ]);
    }

    public function createProfile(Request $request)
    {
        $request->validate([
            'fullName' => 'required|string|max:255',
            'email' => 'nullable|email',
            'mobile_number' => 'required|string',
            'address' => 'required|string',
            'pinCode' => 'required|digits:6',
            'profileImageUrl' => 'nullable|string', // can be empty string
        ]);

        $parent = Auth::guard('parent')->user();

        // Prevent re-creation
        if ($parent->email) {
            return response()->json([
                'message' => 'Profile already created'
            ], 409);
        }

        $parent->update([
            'full_name' => $request->fullName,
            'email' => $request->email,
            'address' => $request->address,
            'pin_code' => $request->pinCode,
            'profile_image_url' => $request->profileImageUrl ?: null,
        ]);

        return response()->json([
            'id' => $parent->id,
            'fullName' => $parent->full_name,
            'email' => $parent->email,
            'address' => $parent->address,
            'pinCode' => $parent->pin_code,
            'profileImageUrl' => $parent->profile_image_url,
            'createdAt' => $parent->created_at,
        ], 201);
    }

    public function register(Request $request)
    {
        try {
           
        $parentId = auth('parent')->id();

        Child::where('id', $request->childId)
            ->where('parent_id', $parentId)
            ->firstOrFail();

        $request->validate([
            'dailyUnlockCount' => 'required|integer|min:1',
            'unlockDurationMinutes' => 'required|integer|min:5',
            'childId' => 'required|exists:children,id',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
        ]);
        $screenTime = ScreenTimeSetting::updateOrCreate(
            ['child_id' => $request->childId],
            [
                'daily_unlock_count' => $request->dailyUnlockCount,
                'unlock_duration_minutes' => $request->unlockDurationMinutes,
                'used_unlocks_today' => 0,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time
            ]
        );

        return response()->json([
            'success' => true,
            'screenTime' => $screenTime
        ]);
         //code...
        } catch (\Throwable $th) {
           dd($th);
        }
    }
    
     public function destroy($parentId)
    {
        try {

            $parentId = auth('parent')->id();

            // ensure parent owns the child
            $child = ParentUser::where('id', $parentId)
                ->firstOrFail();
            $child->delete();

            return response()->json([
                'success' => true,
                'message' => 'Parent deleted successfully'
            ]);
            //code...
        } catch (\Throwable $th) {
            dd($th);
        }
    }
    
    public function parentUsageChart(Request $request, $parentId, UsageChartService $usageChart)
    {
        $type = $request->query('type', 'monthly');
        $childId = $request->query('child_id') ?? $request->query('childId');

        if (!$childId) {
            return response()->json([
                'success' => false,
                'message' => 'child_id is required',
            ], 400);
        }

        Child::where('id', $childId)
            ->where('parent_id', $parentId)
            ->firstOrFail();

        if ($type === 'monthly') {
            return response()->json($usageChart->monthly(
                $childId,
                (int) $request->query('year', now()->year),
                (int) $request->query('month', now()->month)
            ));
        }

        if ($type === 'yearly') {
            return response()->json($usageChart->yearly(
                $childId,
                (int) $request->query('year', now()->year)
            ));
        }

        return response()->json([
            'success' => false,
            'message' => 'Invalid type. Use monthly or yearly.',
        ], 400);
    }

    public function parentPerformanceChart(Request $request, $parentId)
    {
        $childId = $request->query('child_id');

        if (!$childId) {
            return response()->json([
                'success' => false,
                'message' => 'child_id is required'
            ], 400);
        }

        // Ensure child belongs to parent
        Child::where('id', $childId)
            ->where('parent_id', $parentId)
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




}
