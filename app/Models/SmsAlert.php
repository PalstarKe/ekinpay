<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsAlert extends Model
{
    use HasFactory;

    protected $table = 'smsalerts';

    protected $primaryKey = 'id';

    protected $fillable = [
        'type',
        'status',
        'template',
        'created_by',
    ];

    public $timestamps = true;
}
