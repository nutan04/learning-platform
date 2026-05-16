<?php

namespace App\Http\Controllers\API\Child;

use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\Question;
use App\Models\QuizAnswer;
use App\Models\QuizSession;
use App\Models\ScreenTimeSetting;
use Illuminate\Http\Request;
use App\Models\GlobalPolicy;

class QuizController extends Controller
{
    public function config($childId)
    {
         $policy = GlobalPolicy::where('type', 'learning_gate')->first();
        return response()->json([
            'totalQuestions' =>$policy->data['totalQuizQuestions'],
            'minRequiredCorrect' =>$policy->data['minQuizPassScore'] 
        ]);
    }

    public function start(Request $request, $childId)
    {
        $policy = GlobalPolicy::where('type', 'learning_gate')->first();
        $limit=$policy->data['totalQuizQuestions'];
        $questions = Question::where('grade', $request->grade)
            ->where('board', $request->board)
            ->where('subject', $request->subject)
            ->inRandomOrder()
            ->limit($limit)
            ->get(['id', 'question_text', 'options', 'subject']);

        $session = QuizSession::create([
            'child_id' => $childId,
            'total_questions' => $limit
        ]);

        return response()->json([
            'sessionId' => $session->id,
            'questions' => $questions
        ]);
    }


    public function answer(Request $request, $sessionId)
    {
        $request->validate([
            'questionId' => 'required|exists:questions,id',
            'selectedAnswer' => 'required'
        ]);

        $session = QuizSession::findOrFail($sessionId);

        if ($session->completed_at) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz already completed'
            ], 400);
        }

        $question = Question::findOrFail($request->questionId);
        $isCorrect = $question->correct_answer === $request->selectedAnswer;

        QuizAnswer::create([
            'quiz_session_id' => $sessionId,
            'question_id' => $question->id,
            'selected_answer' => $request->selectedAnswer,
            'is_correct' => $isCorrect
        ]);

        if ($isCorrect) {
            $session->increment('correct_answers');
        }

        return response()->json([
            'success' => true,
            'isCorrect' => $isCorrect,
            'correctAnswer' => $question->correct_answer
        ]);
    }

    public function complete($sessionId)
    {
        $session = QuizSession::findOrFail($sessionId);

        if ($session->completed_at) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz already completed'
            ], 400);
        }

             // Get policy
        $policy = GlobalPolicy::where('type', 'learning_gate')->first();
    
        $minPassScore = $policy && isset($policy->data['minQuizPassScore'])
            ? $policy->data['minQuizPassScore']
            : 3; // fallback default
    
        $passed = $session->correct_answers >= $minPassScore;

        $session->update([
            'is_passed' => $passed,
            'completed_at' => now()
        ]);

        $child = Child::findOrFail($session->child_id);

        if ($passed) {
            ScreenTimeSetting::where('child_id', $session->child_id)
                ->increment('used_unlocks_today');

            return response()->json([
                'success' => true,
                'isPassed' => true,
                'correctAnswers' => $session->correct_answers,
                'unlockGranted' => true
            ]);
        }

        $child->update(['is_unlocked' => false]);

        return response()->json([
            'success' => true,
            'isPassed' => false,
            'failureReason' => 'FAILED_QUIZ'
        ]);
    }
}
