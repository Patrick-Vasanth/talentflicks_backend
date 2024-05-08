<?php

namespace Modules\User\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\User\Database\factories\ContactUsFactory;

class ContactUs extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'contact_us';
    protected $fillable = [
        'name',
        'email',
        'message'
    ];

    protected static function newFactory(): ContactUsFactory
    {
        //return ContactUsFactory::new();
    }
}
