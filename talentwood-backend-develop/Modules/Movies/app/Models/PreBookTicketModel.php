<?php

namespace Modules\Movies\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Movies\Database\factories\PreBookTicketModelFactory;

class PreBookTicketModel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'TF_USER_PREBOOK_TICKETS';

    protected $fillable = ['PREBOOK_USER_ID', 'PREBOOK_MOVIE_ID', 'PREBOOK_IS_GOLDEN'];

    public $timestamps = false;
    protected static function newFactory(): PreBookTicketModelFactory
    {
        //return PreBookTicketModelFactory::new();
    }
}
