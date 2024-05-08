<?php

namespace Modules\Movies\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Movies\Database\factories\MoviesFactory;

class Movies extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'TF_MOVIE_MASTERS';

    public $timestamps = false;
    protected $fillable = [
        'movie_comp_id', 'MOVIE_NAME', 'MOVIE_DESCRIPTION', 'MOVIE_RUNTIME', 'MOVIE_DIRECTOR_NAME', 'MOVIE_STAR_CAST', 'MOVIE_URL_LINK', 'MOVIE_GENRE', 'MOVIE_LANGUAGE', 'MOVIE_STATUS', 'MOVIE_BANNER', 'MOVIE_POSTER'
    ];

    public function goldenhour()
    {

        return $this->hasOne(GoldenHour::class, 'GH_MOVIE_ID', 'MOVIE_ID');
    }

    public function prebooktickets()
    {
        return $this->hasMany(PreBookTicketModel::class, 'PREBOOK_MOVIE_ID', 'MOVIE_ID');
    }
    protected static function newFactory(): MoviesFactory
    {
        //return MoviesFactory::new();
    }
    public function competition()
    {

        return $this->belongsTo(QuizCompetitionModel::class, 'movie_comp_id', 'comp_id');
    }
}
