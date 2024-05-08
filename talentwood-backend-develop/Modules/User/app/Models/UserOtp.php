<?php

namespace Modules\User\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * Class usersInfo
 * @package App\Models
 */
class UserOtp extends Model
{
    /**
    * @SWG\Definition()
    */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'TW_USER_OTP';
    protected $fillable = ['UO_ID','UO_USER_ID', 'UO_USER_PHONE', 'UO_OTP'];
    public $timestamps = false;
    protected $primaryKey = 'UO_ID';

    
}
