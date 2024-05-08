<?php

namespace Modules\User\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * Class usertypemaster
 * @package App\Models
 */
class UserRoleMaster extends Model
{
    /**
    * @SWG\Definition()
    */
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'TW_USER_ROLE_MASTER';
    protected $fillable = ['UR_ID'];
    public $timestamps = false;
    protected $primaryKey = 'UR_ID';
}
