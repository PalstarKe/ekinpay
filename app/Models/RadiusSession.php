<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadiusSession extends Model
{
    protected $fillable = ['radius_user_id', 'session_id', 'ip_address', 'status'];

    public function radiusUser()
    {
        return $this->belongsTo(RadiusUser::class, 'radius_user_id');
    }
}
