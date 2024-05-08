<?php

namespace Modules\User\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * Class usersInfo
 * @package App\Models
 */
class UserLocation extends Model
{
    /**
    * @SWG\Definition()
    */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'TW_USER_LOCATION';
    protected $fillable = ['UL_ID','UL_USER_ID'];
    public $timestamps = false;
    protected $primaryKey = 'UL_ID';

    public function user()
    {
        return $this->hasOne('Modules\User\app\Models\User', 'USER_ID', 'UL_USER_ID');
    }

}
