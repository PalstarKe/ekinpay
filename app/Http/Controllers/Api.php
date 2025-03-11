<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Api extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		isAdmin();
		isKena();
		isLogin();
		ifClient();
	}

	public function index()
	{
		$util = new PEAR2\Net\RouterOS\Util($client = new PEAR2\Net\RouterOS\Client('192.168.0.5', 'admin', ''));
		$util->setMenu('/ip arp');

		foreach ($util->getAll() as $item) {
			echo 'IP: ';
			echo $item->getProperty('address');
			echo ' MAC: ';
			echo $item->getProperty('mac-address');
			echo "\n";
		}
	}
}

?>