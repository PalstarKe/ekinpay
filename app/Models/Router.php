<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Router extends Model
{
    protected $connection = 'mysql'; // Use the billing system's database
    protected $table = 'routers'; // Explicitly define table name
    protected $fillable = [
        'created_by',
        'nas_id',
        'name',
        'ip_address',
        'type',
        'location',
        'secret'
    ];

    public function creator()
    {
        return DB::connection('mysql')->table('users')->where('id', $this->created_by)->first();
    }
    public function nas()
    {
        return $this->belongsTo(Nas::class, 'nas_id', 'id');
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'router_packages');
    }
}

