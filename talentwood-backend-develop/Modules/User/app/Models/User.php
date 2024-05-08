<?php

namespace Modules\User\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Modules\Movies\app\Models\PreBookTicketModel;

/**
 * Class users
 * @package App\Models
 */
class User extends Model
{
    /**
     * @SWG\Definition()
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'TW_USERS';
    protected $fillable = ['USER_ID'];
    public $timestamps = false;
    protected $primaryKey = 'USER_ID';
    protected $hidden = array('USER_PASSWORD');

    public function usertype()
    {
        return $this->hasOne('Modules\User\app\Models\UserTypeMaster', 'UT_ID', 'USER_TYPE');
    }

    public function userinfo()
    {
        return $this->hasOne('Modules\User\app\Models\UserInfo', 'UI_USER_ID', 'USER_ID');
    }

    public function usertoken()
    {
        return $this->hasOne('Modules\User\app\Models\UserToken', 'UT_USER_ID', 'USER_ID');
    }

    public function userLocation()
    {
        return $this->hasOne('Modules\User\app\Models\UserLocation', 'UL_USER_ID', 'USER_ID');
    }

    public function userRole()
    {
        return $this->hasMany('Modules\User\app\Models\UserRole', 'USR_USER_ID', 'USER_ID');
    }

    //for tickets-t

    public function userPrebookTickets()
    {
        return $this->hasMany(PreBookTicketModel::class, 'PREBOOK_USER_ID', 'USER_ID');
    }
}
