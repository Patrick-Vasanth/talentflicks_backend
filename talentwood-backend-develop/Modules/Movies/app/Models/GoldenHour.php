<?php

namespace Modules\Movies\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Movies\Database\factories\GoldenHourFactory;

class GoldenHour extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'TF_GOLDEN_HOURS';
    protected $fillable = [
        'GH_MOVIE_ID', 'GH_START_DATE', 'GH_START_TIME', 'GH_END_DATE', 'GH_END_TIME', 'GH_STATUS', 'GH_CREATED_AT', ' GH_UPDATED_AT'
    ];

    public function movie()
    {
        return $this->belongsTo(Movies::class, 'GH_MOVIE_ID', 'MOVIE_ID');
    }
    protected static function newFactory(): GoldenHourFactory
    {
        //return GoldenHourFactory::new();
    }
}
