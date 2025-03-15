<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsDelivered extends Model
{
    use HasFactory;

    protected $table = 'smsdelivered';

    protected $fillable = [
        'created_by',
        'responseid',
        'smsalert',
        'destination',
        'message',
        'datetime',
        'adminid',
        'userid',
        'sms_api_response'
    ];
}
