<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Board;

class BoardController extends Controller
{
    /**
     * Get all boards
     */
    public function index()
    {
        $boards = Board::all();

        return response()->json([
            'success' => true,
            'data' => $boards
        ]);
    }

    /**
     * Create new board
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100'
        ]);

        $board = Board::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Board created successfully',
            'data' => $board
        ], 201);
    }

    /**
     * Get single board with grades and subjects
     */
    public function show($id)
    {
        $board = Board::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $board
        ]);
    }

    /**
     * Update board
     */
    public function update(Request $request, $id)
    {
        $board = Board::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100'
        ]);

        $board->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Board updated successfully',
            'data' => $board
        ]);
    }

    /**
     * Delete board
     */
    public function destroy($id)
    {
        $board = Board::findOrFail($id);
        $board->delete();

        return response()->json([
            'success' => true,
            'message' => 'Board deleted successfully'
        ]);
    }
}
