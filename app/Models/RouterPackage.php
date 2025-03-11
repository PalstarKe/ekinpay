<?php
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class RouterPackage extends Model
{
    protected $fillable = ['router_id', 'package_id'];

    public function router()
    {
        return $this->belongsTo(Router::class, 'router_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }
}
