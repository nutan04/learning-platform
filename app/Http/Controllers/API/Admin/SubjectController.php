<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;

class SubjectController extends Controller
{
    /**
     * Get all subjects
     */
    public function index()
    {
        $subjects = Subject::get();

        return response()->json([
            'success' => true,
            'data' => $subjects
        ]);
    }

    /**
     * Create new subject
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'grade_id' => 'required',
            'name' => 'required|string|max:100',
            'board_id'=>'required|string|max:100'
        ]);

        $subject = Subject::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Subject created successfully',
            'data' => $subject
        ], 201);
    }

    /**
     * Get single subject
     */
    public function show($id)
    {
        $subject = Subject::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $subject
        ]);
    }

    /**
     * Update subject
     */
    public function update(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);

        $validated = $request->validate([
            'grade_id' => 'required',
            'name' => 'required|string|max:100',
            'board_id'=>'required|string|max:100'
        ]);

        $subject->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Subject updated successfully',
            'data' => $subject
        ]);
    }

    /**
     * Delete subject
     */
    public function destroy($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();

        return response()->json([
            'success' => true,
            'message' => 'Subject deleted successfully'
        ]);
    }
}
