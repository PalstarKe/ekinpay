<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentPage extends Model
{
    // Specify the custom table name
    protected $table = 'tbl_payments_page';

    // Disable Laravel's default timestamps since we use custom date columns
    public $timestamps = false;

    // Mass assignable fields
    protected $fillable = [
        'username',
        'transaction_id',
        'transaction_ref',
        'router_name',
        'plan_id',
        'plan_name',
        'amount',
        'phone_number',
        'transaction_status',
        'gateway_response',
        'payment_gateway',
        'payment_method',
        'created_date',
        'payment_date',
        'expired_date',
    ];
}
