<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    // Specify the custom table name
    protected $table = 'tbl_logs';

    // Disable Laravel's default timestamps, as we don't have created_at/updated_at columns.
    public $timestamps = false;

    // Mass assignable fields.
    protected $fillable = [
        'date',
        'type',
        'description',
        'userid',
        'ip',
    ];
}
