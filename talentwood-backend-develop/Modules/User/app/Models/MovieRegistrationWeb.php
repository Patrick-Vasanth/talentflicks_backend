<?php

namespace Modules\User\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\User\Database\factories\MovieRegistrationWebFactory;

class MovieRegistrationWeb extends Model
{
    use HasFactory;

    protected $table = 'movie_registration_website';
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_name',
        'email',
        'phone_number',
        'date_of_birth',
        'gender',
        'movie_title',
        'movie_description',
        'movie_link_url',
        'is_paid'
    ];

    protected static function newFactory(): MovieRegistrationWebFactory
    {
        //return MovieRegistrationWebFactory::new();
    }
}
