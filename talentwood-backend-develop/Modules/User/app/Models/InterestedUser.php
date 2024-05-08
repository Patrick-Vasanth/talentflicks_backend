<?php

namespace Modules\User\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\User\Database\factories\InterestedUserFactory;

class InterestedUser extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'interested';
    protected $fillable = [
        'user_name',
        'email',
        'mobile_number'
    ];

    protected static function newFactory(): InterestedUserFactory
    {
        //return InterestedUserFactory::new();
    }
}
