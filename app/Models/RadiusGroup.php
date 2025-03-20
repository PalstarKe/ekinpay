<?php
namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class RadiusGroup extends Model
{
    protected $fillable = ['name', 'package_id'];

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }
}
