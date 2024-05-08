<?php

namespace Modules\User\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * Class usersInfo
 * @package App\Models
 */
class UserInfo extends Model
{
    /**
    * @SWG\Definition()
    */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'TW_USER_INFO';
    protected $fillable = ['UI_ID','UI_USER_ID'];
    public $timestamps = false;
    protected $primaryKey = 'UI_ID';

    public function createdby()
    {
        return $this->hasOne('Modules\User\app\Models\User', 'USER_ID', 'UI_CREATED_BY');
    }

    public function updatedby()
    {
        return $this->hasOne('Modules\User\app\Models\User', 'USER_ID', 'UI_MODIFIED_BY');
    }

    public function user()
    {
        return $this->hasOne('Modules\User\app\Models\User', 'USER_ID', 'UI_USER_ID');
    }

    public function userLocation()
    {
        return $this->hasOne('Modules\User\app\Models\UserLocation', 'UL_USER_ID', 'UI_USER_ID');
    }

    public function userRole()
    {
        return $this->hasMany('Modules\User\app\Models\UserRole', 'USR_USER_ID', 'UI_USER_ID');
    }

}
