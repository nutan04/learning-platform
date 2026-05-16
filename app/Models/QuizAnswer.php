<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizAnswer extends Model
{
      protected $fillable = [
        'quiz_session_id',
        'question_id',
        'selected_answer',
        'is_correct'
    ];
    public $timestamps = false;
}
