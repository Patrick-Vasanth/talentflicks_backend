<?php

namespace Modules\Movies\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Movies\Database\factories\QuizTestAnswerFactory;

class QuizTestAnswer extends Model
{
    use HasFactory;

    protected $table = 'TF_QUIZ_TEST_ANSWERS';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['user_id', 'test_id', 'question_id', 'correct', 'option_id'];

    public $timestamps = false;

    protected static function newFactory(): QuizTestAnswerFactory
    {
        //return QuizTestAnswerFactory::new();
    }
}
