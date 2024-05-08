<?php

namespace Modules\Movies\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Movies\Database\factories\QuizQuestionOptnModelFactory;

class QuizQuestionOptnModel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */

    protected $table = 'TF_QUIZ_QUESTIONS_OPTIONS';
    protected $fillable = ['qo_qt_id', 'qo_option_value', 'qo_correct',];

    protected static function newFactory(): QuizQuestionOptnModelFactory
    {
        //return QuizQuestionOptnModelFactory::new();
    }

    public function question()
    {
        return $this->belongsTo(QuizQuestionModel::class, 'qo_qt_id', 'qt_id');
    }
}
