<?php

namespace Modules\User\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * Class usertypemaster
 * @package App\Models
 */
class UserTypeMaster extends Model
{
    /**
    * @SWG\Definition()
    */
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    
    protected $table = 'TW_USER_TYPE_MASTER';
    protected $fillable = ['UT_ID'];
    public $timestamps = false;
    protected $primaryKey = 'UT_ID';
}
