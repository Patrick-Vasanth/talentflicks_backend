<?php

namespace Modules\Movies\app\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Movies\Database\factories\BannersFactory;

class Banners extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $table = 'TF_BANNER_IMAGES';
    public $timestamps = false;
    protected $fillable = ["BANNER_TITLE", "BANNER_DESC", "BANNER_IMAGE_NAME", "BANNER_IMAGE_ALT", "BANNER_IS_ACTIVE"];

    protected static function newFactory(): BannersFactory
    {
        //return BannersFactory::new();
    }
}
