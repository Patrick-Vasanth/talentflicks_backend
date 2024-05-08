<?php

namespace Modules\User\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * Class usersInfo
 * @package App\Models
 */
class StateMaster extends Model
{
    /**
    * @SWG\Definition()
    */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'TW_STATE_MASTER';
    protected $fillable = ['STATE_ID','STATE_NAME'];
    public $timestamps = false;
    protected $primaryKey = 'STATE_ID';

    public function citymaster()
    {
        return $this->hasMany('Modules\User\app\Models\CityMaster', 'CITY_STATE_ID', 'STATE_ID');
    }

}
