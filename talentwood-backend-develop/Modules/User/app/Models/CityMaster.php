<?php

namespace Modules\User\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * Class usersInfo
 * @package App\Models
 */
class CityMaster extends Model
{
    /**
    * @SWG\Definition()
    */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'TW_CITY_MASTER';
    protected $fillable = ['CITY_ID','CITY_NAME','CITY_STATE_ID'];
    public $timestamps = false;
    protected $primaryKey = 'CITY_ID';

}
