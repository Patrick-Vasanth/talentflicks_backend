<?php

namespace Modules\Movies\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Movies\Database\factories\QuizTestFactory;

class QuizTest extends Model
{
    use HasFactory;

    protected $table = 'TF_QUIZ_TESTS';

    /**
     * The attributes that are mass assignable.
     *     public $timestamps = false;
     */

    protected $fillable = ['user_id', 'competition_id', 'average_score', 'test_score', 'result', '-quiz_start_by', '-quiz_approved_by'];
    public $timestamps = false;
    protected static function newFactory(): QuizTestFactory
    {
        //return QuizTestFactory::new();
    }
}
