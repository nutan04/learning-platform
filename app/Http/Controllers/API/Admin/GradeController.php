<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Grade;

class GradeController extends Controller
{
    /**
     * Get all grades
     */
    public function index()
    {
        $grades = Grade::all();

        return response()->json([
            'success' => true,
            'data' => $grades
        ]);
    }

    /**
     * Create new grade
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50'
        ]);

        $grade = Grade::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Grade created successfully',
            'data' => $grade
        ], 201);
    }

    /**
     * Get single grade with subjects
     */
    public function show($id)
    {
        $grade = Grade::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $grade
        ]);
    }

    /**
     * Update grade
     */
    public function update(Request $request, $id)
    {
        $grade = Grade::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:50'
        ]);

        $grade->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Grade updated successfully',
            'data' => $grade
        ]);
    }

    /**
     * Delete grade
     */
    public function destroy($id)
    {
        $grade = Grade::findOrFail($id);
        $grade->delete();

        return response()->json([
            'success' => true,
            'message' => 'Grade deleted successfully'
        ]);
    }
}
