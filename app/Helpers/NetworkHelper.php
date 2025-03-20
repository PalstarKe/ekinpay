<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use PEAR2\Net\RouterOS\Client;
use PEAR2\Net\RouterOS\Util;

class NetworkHelper
{
    public static function mkClientByID($id)
    {
        $nasObj = DB::table('nas')->where('id', $id)->first();
        
        if ($nasObj) {
            $nasAPIPort = !empty($nasObj->api_port) ? $nasObj->api_port : 8728;

            try {
                return new Client($nasObj->nasip, $nasObj->nasusername, $nasObj->naspassword, $nasAPIPort, false, 3);
            } catch (\Exception $e) {
                return false;
            }
        }
        return false;
    }

    public static function mkUtilByID($id)
    {
        $client = self::mkClientByID($id);
        return $client ? new Util($client) : false;
    }

    public static function getNas($id)
    {
        return DB::table('nas')->where('id', $id)->first() ?: false;
    }

    public static function getMonitoring($id)
    {
        return DB::table('monitoring')->where('networkID', $id)->orderByDesc('networkID')->first() ?: false;
    }

    public static function mkClientByRouterID($id)
    {
        $nasObj = DB::table('routers')->where('routerID', $id)->first();
        
        if ($nasObj) {
            $nasAPIPort = !empty($nasObj->api_port) ? $nasObj->api_port : 8728;

            try {
                return new Client($nasObj->nasip, $nasObj->nasusername, $nasObj->naspassword, $nasAPIPort, false, 3);
            } catch (\Exception $e) {
                return false;
            }
        }
        return false;
    }

    public static function mkUtilByRouterID($id)
    {
        $client = self::mkClientByRouterID($id);
        return $client ? new Util($client) : false;
    }

    public static function getRoutersMonitoring($ip)
    {
        return DB::table('routersmonitoring')->where('nasIP', $ip)->orderByDesc('networkID')->first() ?: false;
    }

    public static function getRouters($id)
    {
        return DB::table('routers')->where('routerID', $id)->first() ?: false;
    }
}
