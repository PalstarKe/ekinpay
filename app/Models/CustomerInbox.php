<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerInbox extends Model
{
    // Specify the table name
    protected $table = 'tbl_customers_inbox';

    // Disable timestamps if you don't have created_at/updated_at columns
    public $timestamps = false;

    // Allow mass assignment for the following columns
    protected $fillable = [
        'customer_id',
        'date_created',
        'date_read',
        'subject',
        'body',
        'from',
        'admin_id',
    ];
}
