<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

defined('BASEPATH') || exit('No direct script access allowed');

if (!function_exists('package')) {
	function package($packid)
	{
		$ci = loadingInstance();
		$query = $ci->db->get_where('package', ['packid' => $packid]);

		if (0 < $query->num_rows()) {
			return $query->result()[0];
		}
		else {
			return false;
		}
	}
}

if (!function_exists('packageByName')) {
	function packageByName($packname)
	{
		$ci = loadingInstance();
		$query = $ci->db->get_where('package', ['packname' => $packname]);

		if (0 < $query->num_rows()) {
			return $query->result()[0];
		}
		else {
			return 0;
		}
	}
}

if (!function_exists('packByGroup')) {
	function packByGroup($groupname)
	{
		$ci = loadingInstance();
		$query = $ci->db->get_where('packages', ['groupname' => $groupname]);

		if (0 < $query->num_rows()) {
			return $query->result()[0];
		}
		else {
			return false;
		}
	}
}

if (!function_exists('getPackageByID')) {
	function getPackageByID($id)
	{
		$ci = loadingInstance();
		$query = $ci->db->get_where('packages', ['id' => $id]);

		if (0 < $query->num_rows()) {
			return $query->result()[0];
		}
		else {
			return false;
		}
	}
}

if (!function_exists('getJoinPackageByID')) {
	function getJoinPackageByID($id)
	{
		$ci = loadingInstance();
		$ci->db->join('f_packages', 'f_packages.pkgid = packages.id', 'left');
		$query = $ci->db->get_where('packages', ['id' => $id]);

		if (0 < $query->num_rows()) {
			return $query->result()[0];
		}
		else {
			return false;
		}
	}
}

if (!function_exists('getJoinPackageByFadminID')) {
	function getJoinPackageByFadminID($id, $fadminid)
	{
		$ci = loadingInstance();
		$ci->db->join('packages', 'packages.id = f_packages.pkgid', 'left');
		$query = $ci->db->get_where('f_packages', ['fpkgid' => $id, 'fadminid' => $fadminid, 'adminid <=' => 0]);

		if (0 < $query->num_rows()) {
			return $query->result()[0];
		}
		else {
			return false;
		}
	}
}

if (!function_exists('getJoinPackageByFAdminIDPkgID')) {
	function getJoinPackageByFAdminIDPkgID($id, $fadminid)
	{
		$ci = loadingInstance();
		$ci->db->join('packages', 'packages.id = f_packages.pkgid', 'left');
		$query = $ci->db->get_where('f_packages', ['pkgid' => $id, 'fadminid' => $fadminid, 'adminid <=' => 0]);

		if (0 < $query->num_rows()) {
			return $query->result()[0];
		}
		else {
			return false;
		}
	}
}

if (!function_exists('getJoinPackageByAdminIDPkgID')) {
	function getJoinPackageByAdminIDPkgID($id, $adminid)
	{
		$ci = loadingInstance();
		$ci->db->join('packages', 'packages.id = f_packages.pkgid', 'left');
		$query = $ci->db->get_where('f_packages', ['pkgid' => $id, 'fadminid' => 0, 'adminid' => $adminid]);

		if (0 < $query->num_rows()) {
			return $query->result()[0];
		}
		else {
			return false;
		}
	}
}

if (!function_exists('getJoinPackageByAdminID')) {
	function getJoinPackageByAdminID($pkgid)
	{
		$ci = loadingInstance();
		$ci->db->join('packages', 'packages.id = f_packages.pkgid', 'left');
		$query = $ci->db->get_where('f_packages', ['pkgid' => $pkgid, 'fadminid' => 0, 'adminid >' => 0]);

		if (0 < $query->num_rows()) {
			return $query->result()[0];
		}
		else {
			return false;
		}
	}
}

if (!function_exists('getAdminJoinPacakgeByPkgID')) {
	function getAdminJoinPacakgeByPkgID($pkgid)
	{
		$ci = loadingInstance();
		$ci->db->join('f_packages', 'f_packages.pkgid = packages.id', 'left');
		$query = $ci->db->get_where('packages', ['pkgid' => $pkgid, 'adminid >' => 0, 'fadminid <=' => 0]);

		if (0 < $query->num_rows()) {
			return $query->result();
		}
		else {
			return false;
		}
	}
}

if (!function_exists('getFpackageByID')) {
	function getFpackageByID($id)
	{
		$ci = loadingInstance();
		$query = $ci->db->get_where('f_packages', ['fpkgid' => $id]);

		if (0 < $query->num_rows()) {
			return $query->result()[0];
		}
		else {
			return false;
		}
	}
}

?>