<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class RadiusHelper
{
    public static function getConnectStatus($id)
    {
        $username = self::getUserInfo($id)->username ?? null;
        if (!$username) return false;

        return DB::table('radacct')
            ->whereNull('acctstoptime')
            ->where('username', $username)
            ->orderByDesc('radacctid')
            ->exists();
    }

    public static function checkTokenStatus($username)
    {
        return DB::table('radacct')
            ->where('username', $username)
            ->orderByDesc('radacctid')
            ->exists();
    }

    public static function getRadacctByUsername($username = null, ?array $data = null)
    {
        $query = DB::table('radacct');

        if ($data) {
            if (isset($data['limit'])) {
                if (is_array($data['limit'])) {
                    $query->limit(...array_values($data['limit']));
                }
            }

            if (isset($data['order_by']) && is_array($data['order_by'])) {
                $query->orderBy(...array_values($data['order_by']));
            }

            if (!empty($data['username'])) {
                $query->where('username', $data['username']);
            }

            if (!empty($data['acctstoptime'])) {
                if (in_array($data['acctstoptime'], ['not null', 'offline', 'IS NOT NULL'])) {
                    $query->whereNotNull('acctstoptime');
                } else {
                    $query->where('acctstoptime', $data['acctstoptime']);
                }
            }
        } else {
            $query->where('username', $username)
                ->whereNull('acctstoptime')
                ->orderByDesc('radacctid')
                ->limit(1);
        }

        return $query->first() ?: false;
    }

    public static function groupByID($id)
    {
        return DB::table('radgroupreply')->where('id', $id)->first() ?: false;
    }

    public static function getRadcheckDataByUsername($username, $dataType = null)
    {
        $query = DB::table('radcheck')->where('username', $username);

        if ($query->exists()) {
            return ($dataType === 'array') ? $query->get()->toArray() : $query->get();
        }

        return false;
    }

    public static function getRadcheckPassByUsername($username)
    {
        return DB::table('radcheck')
            ->where('username', $username)
            ->where('attribute', 'Cleartext-Password')
            ->first() ?: false;
    }

    public static function getRadcheckByUsername($username)
    {
        return DB::table('radcheck')
            ->where('username', $username)
            ->where('attribute', 'Expiration')
            ->first() ?: false;
    }

    public static function getRadcheckMacByUser($username)
    {
        return DB::table('radcheck')
            ->where('username', $username)
            ->where('attribute', 'Calling-Station-Id')
            ->first() ?: false;
    }

    public static function getRadreplyIPByUser($username)
    {
        return DB::table('radreply')
            ->where('username', $username)
            ->where('attribute', 'Framed-IP-Address')
            ->first() ?: false;
    }

    private static function getUserInfo($id)
    {
        return DB::table('customers')->where('id', $id)->first();
    }
}
