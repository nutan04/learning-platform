<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Board;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;



class QuestionController extends Controller
{
    public function index(Request $r)
    {
        return Question::where('grade', $r->grade)
            ->where('board', $r->board)
            ->paginate(10);
    }
    public function upload(Request $r)
    {
        // Dummy response â€“ real Excel parsing in prod
        return [
            'success' => true,
            'inserted' => 10,
            'failed' => 0
        ];
    }
    /**
     * Add new question
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'grade' => 'required|string|max:50',
            'board' => 'required|string|max:50',
            'question_text' => [
                'required',
                'string',
                Rule::unique('questions', 'question_text')
            ],
            'options' => 'required|array|min:2',
            'correct_answer' => 'required|string',
            'subject' => 'nullable|string'
        ], [
            'question_text.unique' => 'This question already exists. Duplicate questions are not allowed.',
            'question_text.required' => 'Question text is required.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()
            ], 422);
        }

        $question = Question::create($validator->validated());

        return response()->json([
            'success' => true,
            'message' => 'Question created successfully',
            'data' => $question
        ], 201);
    }

    /**
     * Update question
     */
public function update(Request $request, $id)
{
    $question = Question::findOrFail($id);

    $validator = Validator::make($request->all(), [
        'grade' => 'sometimes|string|max:50',
        'board' => 'sometimes|string|max:50',
        'question_text' => [
            'sometimes',
            'string',
            Rule::unique('questions', 'question_text')->ignore($question->id)
        ],
        'options' => 'sometimes|array|min:2',
        'correct_answer' => 'sometimes|string',
        'subject' => 'nullable|string|max:50'
    ], [
        'question_text.unique' => 'This question already exists. Duplicate questions are not allowed.'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
            'errors' => $validator->errors()
        ], 422);
    }

    $question->update($validator->validated());

    return response()->json([
        'success' => true,
        'message' => 'Question updated successfully',
        'data' => $question
    ]);
}

    /**
     * Delete question
     */
    public function destroy($id)
    {
        $question = Question::findOrFail($id);
        $question->delete();

        return response()->json([
            'success' => true,
            'message' => 'Question deleted successfully'
        ]);
    }

    // public function bulkUpload(Request $request)
    // {
    //     $request->validate([
    //         'file' => 'required|file|mimes:xlsx,csv'
    //     ]);

    //     $rows = Excel::toArray([], $request->file('file'))[0];

    //     // Remove header
    //     $header = array_shift($rows);

    //     if (count($rows) > 1000) {
    //         return response()->json([
    //             'message' => 'Maximum 1000 questions allowed per upload'
    //         ], 422);
    //     }

    //     $insertData = [];
    //     $duplicates = [];

    //     foreach ($rows as $index => $row) {

    //         $questionText = $row[3];

    //         // Check duplicate in DB
    //         if (Question::where('question_text', $questionText)->exists()) {
    //             $duplicates[] = [
    //                 'row' => $index + 2,
    //                 'question' => $questionText
    //             ];
    //             continue;
    //         }

    //         $insertData[] = [
    //             'id' => Str::uuid(),
    //             'grade' => $row[0],
    //             'board' => $row[1],
    //             'subject' => $row[2],
    //             'question_text' => $questionText,
    //             'options' => json_encode([
    //                 'A' => $row[4],
    //                 'B' => $row[5],
    //                 'C' => $row[6],
    //                 'D' => $row[7],
    //             ]),
    //             'correct_answer' => $row[8]
    //         ];
    //     }

    //     // Insert valid questions
    //     if (!empty($insertData)) {
    //         Question::insert($insertData);
    //     }

    //     return response()->json([
    //         'message' => 'Upload completed',
    //         'inserted' => count($insertData),
    //         'duplicates' => $duplicates
    //     ]);
    // }
    
    
public function bulkUpload(Request $request)
{
    $request->validate([
        'file' => 'required|file|mimes:xlsx,csv'
    ]);

    $rows = Excel::toArray([], $request->file('file'))[0];

    // Remove header
    $header = array_shift($rows);

    if (count($rows) > 1000) {
        return response()->json([
            'message' => 'Maximum 1000 questions allowed per upload'
        ], 422);
    }

    $insertData = [];
    $duplicates = [];

    foreach ($rows as $index => $row) {

        $boardCode = $row[1];
        $subjectCode = $row[2];
        $questionText = $row[3];

        // Find board using code
        $board = Board::where('unique_board_id', $boardCode)->first();

        // Find subject using code
        $subject = Subject::where('unique_subject_id', $subjectCode)->first();

        if (!$board || !$subject) {
            continue; // skip if invalid code
        }

        // Check duplicate question
        if (Question::where('question_text', $questionText)->exists()) {
            $duplicates[] = [
                'row' => $index + 2,
                'question' => $questionText
            ];
            continue;
        }

        $insertData[] = [
            'id' => Str::uuid(),
            'grade' => $row[0],
            'board' => $board->name,     // storing board name
            'subject' => $subject->name, // storing subject name
            'question_text' => $questionText,
            'options' => json_encode([
                'A' => $row[4],
                'B' => $row[5],
                'C' => $row[6],
                'D' => $row[7],
            ]),
            'correct_answer' => $row[8]
        ];
    }

    if (!empty($insertData)) {
        Question::insert($insertData);
    }

    return response()->json([
        'message' => 'Upload completed',
        'inserted' => count($insertData),
        'duplicates' => $duplicates
    ]);
}
}
