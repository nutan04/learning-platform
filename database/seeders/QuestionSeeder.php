<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Question;
use Illuminate\Support\Str;

class QuestionSeeder extends Seeder
{
    public function run()
    {
        $questions = [
            [
                'grade' => 5,
                'board' => 'CBSE',
                'question_text' => 'What is 2 + 2?',
                'options' => ['A' => '3', 'B' => '4', 'C' => '5', 'D' => '6'],
                'correct_answer' => 'B',
            ],
            [
                'grade' => 5,
                'board' => 'CBSE',
                'question_text' => 'What is 10 / 2?',
                'options' => ['A' => '2', 'B' => '3', 'C' => '5', 'D' => '10'],
                'correct_answer' => 'C',
            ],
            [
                'grade' => 5,
                'board' => 'CBSE',
                'question_text' => 'Which planet is known as the Red Planet?',
                'options' => ['A' => 'Earth', 'B' => 'Mars', 'C' => 'Jupiter', 'D' => 'Venus'],
                'correct_answer' => 'B',
            ],
            [
                'grade' => 5,
                'board' => 'CBSE',
                'question_text' => 'How many days are there in a week?',
                'options' => ['A' => '5', 'B' => '6', 'C' => '7', 'D' => '8'],
                'correct_answer' => 'C',
            ],
            [
                'grade' => 5,
                'board' => 'CBSE',
                'question_text' => 'What is the capital of India?',
                'options' => ['A' => 'Mumbai', 'B' => 'Delhi', 'C' => 'Chennai', 'D' => 'Kolkata'],
                'correct_answer' => 'B',
            ],
            [
                'grade' => 6,
                'board' => 'CBSE',
                'question_text' => 'What is 12 × 2?',
                'options' => ['A' => '12', 'B' => '14', 'C' => '24', 'D' => '22'],
                'correct_answer' => 'C',
            ],
        ];

        foreach ($questions as $q) {
            Question::create([
                'id' => (string) Str::uuid(),
                'grade' => $q['grade'],
                'board' => $q['board'],
                'question_text' => $q['question_text'],
                'options' => $q['options'],
                'correct_answer' => $q['correct_answer'],
            ]);
        }
    }
}
