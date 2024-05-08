<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegisterPayment extends Model
{
    protected $table = 'TF_REGISTER_PAYMENT';
    protected $fillable = ['RP_ID','RP_TRANSACTION_ID','RP_AMOUNT','RP_PAYMENT_STATUS','RP_RESPONSE_MSG','RP_PROVIDE_REF_ID','RP_MECHANT_ORDER_ID','RP_CHECKSUM'];
    public $timestamps = false;
    protected $primaryKey = 'RP_ID';

}
