<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RadiusUser extends Model
{
    protected $fillable = ['user_id', 'nas_id', 'username', 'password'];

    public function user()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    public function nas()
    {
        return $this->belongsTo(Nas::class, 'nas_id');
    }
}
