<?php

namespace Modules\User\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * Class users
 * @package App\Models
 */
class SocialAccount extends Model
{
    /**
     * @SWG\Definition()
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $table = 'TW_USERS_SOCIAL_ACCOUNTS';
    protected $fillable = ['USA_ID', 'USA_USER_ID', 'USA_PROVIDER_ID', 'USA_PROVIDER_NAME', 'USA_CREATED_BY', 'USA_CREATED_DATE'];
    public $timestamps = false;
    protected $primaryKey = 'USA_ID';
}
