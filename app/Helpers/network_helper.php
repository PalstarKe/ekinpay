<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

defined('BASEPATH') || exit('No direct script access allowed');

if (!function_exists('mkClientByID')) {
	function mkClientByID($id)
	{
		$ci = loadingInstance();
		$nasObj = $ci->main->singleQuery('nas', ['id' => $id]);

		if ($nasObj) {
			$nasAPIPort = (isset($nasObj[0]->api_port) && !empty($nasObj[0]->api_port) ? $nasObj[0]->api_port : 8728);

			try {
				return new PEAR2\Net\RouterOS\Client($nasObj[0]->nasip, $nasObj[0]->nasusername, $nasObj[0]->naspassword, $nasAPIPort, false, 3);
			}
			catch (Exception $e) {
				return false;
			}
		}
		else {
			return false;
		}
	}
}

if (!function_exists('mkUtilByID')) {
	function mkUtilByID($id)
	{
		if (!mkClientByID($id)) {
			return false;
		}
		else {
			return new PEAR2\Net\RouterOS\Util(mkClientByID($id));
		}
	}
}

if (!function_exists('getNas')) {
	function getNas($id)
	{
		$ci = loadingInstance();
		$nasObj = $ci->main->singleQuery('nas', ['id' => $id]);

		if ($nasObj) {
			return $nasObj;
		}
		else {
			return false;
		}
	}
}

if (!function_exists('getMonitoring')) {
	function getMonitoring($id)
	{
		$ci = loadingInstance();
		$ci->db->limit(1);
		$ci->db->order_by('networkID', 'DESC');
		$nasObj = $ci->db->get_where('monitoring', ['networkID' => $id]);

		if (0 < $nasObj->num_rows()) {
			return $nasObj->result()[0];
		}
		else {
			return false;
		}
	}
}

if (!function_exists('mkClientByRouterID')) {
	function mkClientByRouterID($id)
	{
		$ci = loadingInstance();
		$nasObj = $ci->main->singleQuery('routers', ['routerID' => $id]);

		if ($nasObj) {
			$nasAPIPort = (isset($nasObj[0]->api_port) && !empty($nasObj[0]->api_port) ? $nasObj[0]->api_port : 8728);

			try {
				return new PEAR2\Net\RouterOS\Client($nasObj[0]->nasip, $nasObj[0]->nasusername, $nasObj[0]->naspassword, $nasAPIPort, false, 3);
			}
			catch (Exception $e) {
				return false;
			}
		}
		else {
			return false;
		}
	}
}

if (!function_exists('mkUtilByRouterID')) {
	function mkUtilByRouterID($id)
	{
		if (!mkClientByRouterID($id)) {
			return false;
		}
		else {
			return new PEAR2\Net\RouterOS\Util(mkClientByRouterID($id));
		}
	}
}

if (!function_exists('getRoutersMonitoring')) {
	function getRoutersMonitoring($ip)
	{
		$ci = loadingInstance();
		$ci->db->limit(1);
		$ci->db->order_by('networkID', 'DESC');
		$nasObj = $ci->db->get_where('routersmonitoring', ['nasIP' => $ip]);

		if (0 < $nasObj->num_rows()) {
			return $nasObj->result()[0];
		}
		else {
			return false;
		}
	}
}

if (!function_exists('getRouters')) {
	function getRouters($id)
	{
		$ci = loadingInstance();
		$nasObj = $ci->main->singleQuery('routers', ['routerID' => $id]);

		if ($nasObj) {
			return $nasObj[0];
		}
		else {
			return false;
		}
	}
}

?>