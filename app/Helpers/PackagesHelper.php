<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;

class PackagesHelper
{
    public static function getPackageById($packid)
    {
        return DB::table('package')->where('packid', $packid)->first() ?: false;
    }

    public static function getPackageByName($packname)
    {
        return DB::table('package')->where('packname', $packname)->first() ?: false;
    }

    public static function getPackageByGroup($groupname)
    {
        return DB::table('packages')->where('groupname', $groupname)->first() ?: false;
    }

    public static function getPackageDetailsById($id)
    {
        return DB::table('packages')->where('id', $id)->first() ?: false;
    }

    public static function getJoinedPackageById($id)
    {
        return DB::table('packages')
            ->leftJoin('f_packages', 'f_packages.pkgid', '=', 'packages.id')
            ->where('packages.id', $id)
            ->first() ?: false;
    }

    public static function getJoinedPackageByFadminId($id, $fadminid)
    {
        return DB::table('f_packages')
            ->leftJoin('packages', 'packages.id', '=', 'f_packages.pkgid')
            ->where(['fpkgid' => $id, 'fadminid' => $fadminid])
            ->where('adminid', '<=', 0)
            ->first() ?: false;
    }

    public static function getJoinedPackageByFadminIdPkgId($id, $fadminid)
    {
        return DB::table('f_packages')
            ->leftJoin('packages', 'packages.id', '=', 'f_packages.pkgid')
            ->where(['pkgid' => $id, 'fadminid' => $fadminid])
            ->where('adminid', '<=', 0)
            ->first() ?: false;
    }

    public static function getJoinedPackageByAdminIdPkgId($id, $adminid)
    {
        return DB::table('f_packages')
            ->leftJoin('packages', 'packages.id', '=', 'f_packages.pkgid')
            ->where(['pkgid' => $id, 'fadminid' => 0, 'adminid' => $adminid])
            ->first() ?: false;
    }

    public static function getJoinedPackageByAdminId($pkgid)
    {
        return DB::table('f_packages')
            ->leftJoin('packages', 'packages.id', '=', 'f_packages.pkgid')
            ->where(['pkgid' => $pkgid, 'fadminid' => 0])
            ->where('adminid', '>', 0)
            ->first() ?: false;
    }

    public static function getAdminJoinedPackageByPkgId($pkgid)
    {
        return DB::table('packages')
            ->leftJoin('f_packages', 'f_packages.pkgid', '=', 'packages.id')
            ->where(['pkgid' => $pkgid])
            ->where('adminid', '>', 0)
            ->where('fadminid', '<=', 0)
            ->get() ?: false;
    }

    public static function getFpackageById($id)
    {
        return DB::table('f_packages')->where('fpkgid', $id)->first() ?: false;
    }
}