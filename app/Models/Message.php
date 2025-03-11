<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nas extends Model
{
    use HasFactory;

    protected $table = 'nas';

    protected $fillable = [
        'nasname',
        'shortname',
        'secret',
        'nasapi',
        'nasip',
        'parent',
        'nasusername',
        'naspassword',
        'api_port',
        'incoming_port',
    ];

    protected $casts = [
        'nasapi' => 'boolean',
        'api_port' => 'integer',
        'incoming_port' => 'integer',
    ];
}
