<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
// use App\Models\RadCheck;
use App\Models\Log;

class AutorenewController extends Controller
{
    protected $settings;

    public function __construct()
    {
        $this->settings = config('settings');
    }

    public function index()
    {
        $this->userStatusChanged();
        $this->autorenew();
        $this->sendDataToServer();
        $this->removeFaultyUserAccess();
    }

    public function userStatusChanged()
    {
        $users =Customer::select('Customer.id', 'Customer.username', 'Customer.status', 'radcheck.attribute', 'radcheck.value as expiration')
            ->leftJoin('radcheck', 'radcheck.username', '=', 'users.username')
            ->where('radcheck.attribute', 'Expiration')
            ->where('users.status', 1)
            ->get();

        foreach ($users as $user) {
            if (!empty($user->expiration)) {
                $user->update(['status' => 2]);
            }
        }
    }

    public function removeFaultyUserAccess()
    {
        if ($this->settings['auto_clear_log']) {
            Log::create(['data' => 'Cron Job', 'msg' => 'Cron Job Ban Faulty Users', 'datetime' => now()]);
        }

        $users = User::select('users.id', 'users.username', 'users.status')
            ->leftJoin('radcheck', function ($join) {
                $join->on('radcheck.username', '=', 'users.username')
                     ->where('radcheck.attribute', '=', 'Cleartext-Password');
            })
            ->where('users.status', 1)
            ->get();

        foreach ($users as $user) {
            $exists = RadCheck::where(['username' => $user->username, 'attribute' => 'Auth-Type', 'value' => 'Reject'])->exists();

            if (!$exists) {
                RadCheck::create(['username' => $user->username, 'attribute' => 'Auth-Type', 'op' => ':=', 'value' => 'Reject']);
            }
        }
    }

    public function autorenew()
    {
        // if ($this->settings['auto_clear_log']) {
        //     Log::create(['data' => 'Cron Job', 'msg' => 'Cron Job Auto Renew', 'datetime' => now()]);
        // }

        $users = Customer::select('users.id', 'users.username')
            ->leftJoin('radcheck', function ($join) {
                $join->on('radcheck.username', '=', 'users.username')
                     ->where('radcheck.attribute', '=', 'Expiration');
            })
            ->whereRaw("STR_TO_DATE(radcheck.value, '%d %b %Y %H:%i:%s') <= ?", [now()])
            ->where('users.status', 2)
            ->get();

        foreach ($users as $user) {
            // Your renewal logic here...
        }
    }

    public function sendDataToServer()
    {
        $macAddress = shell_exec("cat /sys/class/net/eth0/address");
        $onlineUsers = DB::table('radacct')->whereNull('acctstoptime')->count();
        $totalUsers = User::count();
        $server = request()->server('SERVER_NAME', 'N/A');

        $data = [
            'loginBy' => 'System',
            'loginPass' => 'System',
            'loginRole' => 'System',
            'loginMac' => $macAddress ?: 'N/A',
            'appUrl' => $server,
            'loginCode' => $this->settings['kenadekha'],
            'onlineUsers' => $onlineUsers,
            'totalUsers' => $totalUsers,
        ];

        // Send data to external server if needed...
    }
}
