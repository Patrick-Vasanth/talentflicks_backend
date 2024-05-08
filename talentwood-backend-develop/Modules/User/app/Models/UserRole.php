<?php

namespace Modules\User\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * Class usersInfo
 * @package App\Models
 */
class UserRole extends Model
{
    /**
    * @SWG\Definition()
    */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'TW_USERS_ROLE';
    protected $fillable = ['USR_ID','USR_USER_ID'];
    public $timestamps = false;
    protected $primaryKey = 'USR_ID';

    public function user()
    {
        return $this->hasOne('Modules\User\app\Models\User', 'USER_ID', 'USR_USER_ID');
    }

    public function userrolemaster()
    {
        return $this->hasOne('Modules\User\app\Models\UserRoleMaster', 'UR_ID', 'USR_USER_ROLE_ID');
    }

}
