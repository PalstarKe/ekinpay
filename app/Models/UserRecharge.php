<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRecharge extends Model
{
    // Specify the custom table name
    protected $table = 'tbl_user_recharges';

    // Disable Laravel's default timestamps (since we're using custom date/time columns)
    public $timestamps = false;

    // Allow mass assignment on these columns
    protected $fillable = [
        'customer_id',
        'username',
        'plan_id',
        'namebp',
        'recharged_on',
        'recharged_time',
        'expiration',
        'time',
        'status',
        'method',
        'routers',
        'type',
        'admin_id',
        'mpesacode',
        'account',
    ];
}
