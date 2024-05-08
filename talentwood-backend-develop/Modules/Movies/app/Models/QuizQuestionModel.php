<?php

namespace Modules\Movies\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Movies\Database\factories\QuizQuestionModelFactory;

class QuizQuestionModel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * 
     */
    protected $table = 'TF_QUIZ_QUESTIONS';
    protected $fillable = ['qt_competition_id', 'qt_text', 'qt_quiz_image', 'qt_status'];

    protected static function newFactory(): QuizQuestionModelFactory
    {
        //return QuizQuestionModelFactory::new();
    }

    public function options()
    {
        return $this->hasMany(QuizQuestionOptnModel::class, 'qo_qt_id', 'qt_id');
    }

    public function competition_movie()
    {
        return $this->belongsTo(QuizCompetitionModel::class, 'qt_competition_id', 'comp_id');
    }
}
