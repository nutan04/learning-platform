<?php

use App\Http\Controllers\API\Admin\AdminDashboardController;
use App\Http\Controllers\API\Admin\AdmnAuthController;
use App\Http\Controllers\API\Admin\PolicyController;
use App\Http\Controllers\API\Admin\QuestionController;
use App\Http\Controllers\API\Admin\SystemController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\Child\ChildDeviceController;
use App\Http\Controllers\API\Child\ChildSosController;
use App\Http\Controllers\API\Child\ChildSyncController;
use App\Http\Controllers\API\Child\ChildUnlockController;
use App\Http\Controllers\API\Child\LockController;
use App\Http\Controllers\API\Child\QuizController;
use App\Http\Controllers\API\ChildController;
use App\Http\Controllers\API\DeviceController;
use App\Http\Controllers\API\Parent\EmergencyContactController;
use App\Http\Controllers\API\Parent\ParentController;
use App\Http\Controllers\API\Parent\ParentPinController;
use App\Http\Controllers\API\Parent\ParentSosController;
use App\Http\Controllers\API\Admin\BoardController;
use App\Http\Controllers\API\Admin\GradeController;
use App\Http\Controllers\API\Admin\SubjectController;


Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'GET API working successfully',
        'time' => now(),
    ]);
});
Route::post('/auth/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);

Route::middleware('auth:parent')->group(function () {
    Route::post('/child', [ChildController::class, 'store']);
    Route::get('/child/{id}', [ChildController::class, 'show']);
    Route::put('/child/{id}',[ChildController::class, 'update']);
    Route::post('/child/{id}/generate-code', [ChildController::class, 'generateCode']);
    Route::get('/emergency-contacts', [EmergencyContactController::class, 'index']);
    Route::post('/emergency-contacts', [EmergencyContactController::class, 'store']);
    Route::put('/emergency-contacts/{id}', [EmergencyContactController::class, 'update']);
    Route::delete('/emergency-contacts/{id}', [EmergencyContactController::class, 'destroy']);

    Route::get('/sos-requests', [ParentSosController::class, 'list']);
    Route::post('/sos-requests/{id}/approve', [ParentSosController::class, 'approve']);
    Route::post('/sos-requests/{id}/reject', [ParentSosController::class, 'reject']);
    Route::get('/parent/profile',[ParentController::class, 'getProfile']);
    Route::put('/parent/profile',[ParentController::class, 'updateProfile']);
    Route::post('/parent/profile',[ParentController::class, 'createProfile']);
    Route::post('/upload/profile-photo',[ParentController::class, 'uploadProfilePhoto']);
    Route::get('/parent/dashboard',[ParentController::class, 'index']);
    Route::get('/child/{childId}/learning-progress',[ParentController::class, 'show']);
    Route::delete('/child/{id}',[ChildController::class, 'destroy']);
    Route::post('child/screen-time',[ParentController::class, 'Register']);
    Route::get('{childId}/screen-time',[ParentController::class, 'get']);
    Route::put('{childId}/screen-time',[ParentController::class, 'update']);
    Route::post('parent/{parentId}/pin/set', [ParentPinController::class, 'setPin']);
    Route::post('parent/{parentId}/pin/change', [ParentPinController::class, 'changePin']);
    Route::get('parent/{parentId}/pin/status', [ParentPinController::class, 'pinStatus']);
    Route::get('boards', [BoardController::class ,'index']);

    // Grades
    Route::get('grades', [GradeController::class ,'index']);

    // Subjects
    Route::get('subjects', [SubjectController::class,'index']);
    Route::delete('/parent/{id}',[ParentController::class, 'destroy']);
    Route::get('/parent-usage/{parentId}', [ParentController::class, 'parentUsageChart']);
    Route::get('/parent-performance/{parentId}', [ParentController::class, 'parentPerformanceChart']);
    


});

Route::post('/child/link-device', [DeviceController::class, 'link']);

Route::prefix('child')->group(function () {

    Route::get('{childId}/lock-status', [LockController::class, 'lockStatus']);
    Route::get('{childId}/quiz/config', [QuizController::class, 'config']);
    Route::post('{childId}/quiz/start', [QuizController::class, 'start']);
    Route::post('quiz/{sessionId}/answer', [QuizController::class, 'answer']);
    Route::post('quiz/{sessionId}/complete', [QuizController::class, 'complete']);
    Route::post('device/register', [ChildDeviceController::class, 'register']);
    Route::post('{childId}/current-unlock-session', [ChildUnlockController::class, 'currentSession']);
    Route::get('{childId}/sync-status', [ChildSyncController::class, 'sync']);
    Route::get('emergency-contacts/{parentID}', [EmergencyContactController::class, 'parentEmergencyContact']);
    Route::get('{childId}/screen-time',[ChildController::class, 'getScreenTime']);
    Route::put('{childId}/screen-time',[ChildController::class, 'updateScreenTime']);
    Route::delete('/child/{id}',[ChildController::class, 'destroy']);
    Route::post('{childId}/parent-mode/unlock', [ParentPinController::class, 'unlock']);
    Route::get('{childId}/usage-chart',[ChildController::class, 'usageChart']);
    Route::get('{childId}/performance-chart',[ChildController::class, 'performanceChart']);
    Route::get('{parentId}/{childId}/parent-details',[ChildController::class, 'parentDetails']);
   

});

// Child
Route::post('/child/{childId}/sos/request', [ChildSosController::class, 'request']);
Route::get('/child/sos/{id}/status', [ChildSosController::class, 'status']);

// Admin
Route::post('/admin/login', [AdmnAuthController::class, 'login']);
Route::get('/admin/dashboard/overview', [AdminDashboardController::class, 'overview']);

Route::prefix('admin')->group(function () {

    Route::get('policies/screen-time', [PolicyController::class, 'getScreenTime']);
    Route::put('policies/screen-time', [PolicyController::class, 'updateScreenTime']);

    Route::get('policies/learning-gate', [PolicyController::class, 'getLearningGate']);
    Route::put('policies/learning-gate', [PolicyController::class, 'updateLearningGate']);

    Route::prefix('questions')->group(function () {
    Route::get('/', [QuestionController::class, 'index']);       // list
    Route::post('/', [QuestionController::class, 'store']);      // add
    Route::put('{id}', [QuestionController::class, 'update']);   // edit
    Route::delete('{id}', [QuestionController::class, 'destroy']); // delete
    });

    Route::get('system/controls', [SystemController::class, 'get']);
    Route::patch('system/controls', [SystemController::class, 'update']);

    Route::prefix('global-policies')->group(function () {
    Route::get('/', [PolicyController::class, 'index']);
    Route::put('{id}', [PolicyController::class, 'update']);
    // Boards
  
});
Route::get('sos-requests', [AdminDashboardController::class, 'sosRequests']);
Route::get('registration-stats', [AdminDashboardController::class, 'registrationStats']);
 Route::get('parents-child-detail', [AdminDashboardController::class, 'index']);
   Route::apiResource('boards', BoardController::class);

    // Grades
    Route::apiResource('grades', GradeController::class);

    // Subjects
    Route::apiResource('subjects', SubjectController::class);
    Route::post('/questions/bulk-upload', [QuestionController::class, 'bulkUpload']);
});
