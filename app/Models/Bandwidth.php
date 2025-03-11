<?php
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class Bandwidth extends Model
{
    protected $fillable = [
        'name_plan', 
        'rate_down', 
        'rate_down_unit',
        'rate_up', 
        'rate_up_unit', 
        'burst',
        'package_id'
    ];
    
    public function creator()
    {
        return DB::connection('mysql')->table('users')->where('id', $this->created_by)->first();
    }
    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

}
