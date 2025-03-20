<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppConfig extends Model
{
    // Specify the table name if it doesn't follow Laravel's naming convention
    protected $table = 'tbl_appconfig';

    // Disable timestamps if you are not using created_at/updated_at columns
    public $timestamps = false;

    // Define the fillable fields
    protected $fillable = ['setting', 'value'];
}
