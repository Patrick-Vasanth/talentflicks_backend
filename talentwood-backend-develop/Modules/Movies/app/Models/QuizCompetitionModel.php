<?php

namespace Modules\Movies\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Movies\Database\factories\QuizCompetitionModelFactory;

class QuizCompetitionModel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */

    protected $table = 'TF_QUIZ_COMPETITIONS';
    protected $fillable = ['comp_name', 'comp_START_DATE', 'comp_END_DATE', 'comp_status'];

    protected static function newFactory(): QuizCompetitionModelFactory
    {
        //return QuizCompetitionModelFactory::new();
    }

    public function questions()
    {
        return $this->hasMany(QuizQuestionModel::class, 'qt_competition_id', 'comp_id');
    }

    public function movies()
    {
        return $this->hasMany(Movies::class, 'movie_comp_id', 'comp_id');
    }
}
