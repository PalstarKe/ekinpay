<?php
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{


    protected $fillable = [
        'created_by',
        'name_plan',
        'price', 
        'type', 
        'typebp', 
        'limit_type',
        'time_limit', 
        'time_unit', 
        'data_limit', 
        'data_unit',
        'validity', 
        'validity_unit', 
        'shared_users',
        'tax_value',
        'tax_type',
        'fup_limit',
        'fup_unit',
        'fup_down_speed',
        'fup_down_unit',
        'fup_up_speed',
        'fup_up_unit',
        // 'is_radius', 
        // 'enabled', 
        'device', 
        'assigned_to'
    ];
    protected $casts = [
        'assigned_to' => 'array',
    ];
    public function bandwidth()
    {
        return $this->hasOne(Bandwidth::class, 'package_id');
    }
    
    public function creator()
    {
        return DB::connection('mysql')->table('users')->where('id', $this->created_by)->first();
    }
    public function routers()
    {
        return $this->belongsToMany(Router::class, 'router_packages');
    }
}
