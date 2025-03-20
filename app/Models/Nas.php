<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

class Nas extends Model
{
    // protected $connection = 'radius';

    protected $table = 'nas';
    use Notifiable;

    protected $fillable = [
        'created_by',
        'nasname',
        'shortname',
        'secret',
        'nasapi',
        'type',
        'server',
        'community',
        'description',
        'api_port',
        'checkedTime',
        'status',

    ];

    protected $casts = [
        'nasapi' => 'boolean',
    ];
    
    public function routers()
    {
        return $this->hasMany(Router::class, 'nas_id', 'id');
    }

    public function creator()
    {
        return DB::connection('mysql')->table('users')->where('id', $this->created_by)->first();
    }
}
