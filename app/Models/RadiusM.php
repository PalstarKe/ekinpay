<?php
namespace App\Models;

use App\Models\Utility;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
class RadiusM extends Model
{
	public $settings;

	public function __construct()
	{
		parent::__construct();
		$this->settings = settings()[0];
	}

	public function kickOutUsers($username, $lineStatus = NULL, $connectionType = NULL, $existance = NULL)
	{
		$userData = $this->main->getUserByUsername($username);

		if ($userData) {
			$onlineUsersData = getRadacctByUsername($username);
			if (is_object($onlineUsersData) && $onlineUsersData) {
				if (property_exists($onlineUsersData, 'nasipaddress') && property_exists($onlineUsersData, 'acctsessionid') && property_exists($onlineUsersData, 'framedipaddress')) {
					$nasIPAddress = $onlineUsersData->nasipaddress;
					$acctSessionID = $onlineUsersData->acctsessionid;
					$framedIPAddress = $onlineUsersData->framedipaddress;
					$nasObj = $this->main->singleQuery('nas', ['nasname' => $nasIPAddress]);

					if (!$nasObj) {
						$userProfileNasID = $userData->nasid;
						$nasObj = $this->main->singleQuery('nas', ['id' => $userProfileNasID]);

						if ($nasObj) {
							if (settings()[0]->disconnecttype == 1) {
								return $this->kickOutUsersByAPI($nasObj, $userData);
							}
							else if (settings()[0]->disconnecttype == 2) {
								$nasIPAddress = $nasObj[0]->nasname;
								$radiusAttributes = [];
								$radiusAttributes['nasIPAddress'] = $nasIPAddress;
								$radiusAttributes['acctSessionID'] = $acctSessionID;
								$radiusAttributes['framedIPAddress'] = $framedIPAddress;
								return $this->kickOutUsersByRadius($nasObj, $userData, $radiusAttributes);
							}
							else {
								throw new Exception('No Disconnect Type Found!');
							}
						}
						else {
							throw new Exception('User\'s NAS Not Found or NAS Not Added in System Yet!');
						}
					}
					else if (settings()[0]->disconnecttype == 1) {
						return $this->kickOutUsersByAPI($nasObj, $userData);
					}
					else if (settings()[0]->disconnecttype == 2) {
						$radiusAttributes = [];
						$radiusAttributes['nasIPAddress'] = $nasIPAddress;
						$radiusAttributes['acctSessionID'] = $acctSessionID;
						$radiusAttributes['framedIPAddress'] = $framedIPAddress;
						return $this->kickOutUsersByRadius($nasObj, $userData, $radiusAttributes);
					}
					else {
						throw new Exception('No Disconnect Type Found!');
					}
				}
				else {
					throw new Exception('User\'s Online Attributes Not Found!');
				}
			}
			else {
				throw new Exception('User Not Online!');
			}
		}
		else {
			throw new Exception('User Not Found!');
		}
	}

	public function kickOutUsersByRadius($nasObj, $userData, array $attributes)
	{
		$username = $userData->username;
		$nasport = (!empty($nasObj[0]->incoming_port) ? $nasObj[0]->incoming_port : 3799);
		$command = 'disconnect';
		$nassecret = $nasObj[0]->secret;
		$nasname = $nasObj[0]->nasname;
		$args = escapeshellarg($nasname . ':' . $nasport) . ' ' . escapeshellarg($command) . ' ' . escapeshellarg($nassecret);
		$query = 'User-Name=' . escapeshellarg($username) . ',Acct-Session-Id=' . escapeshellarg($attributes['acctSessionID']) . ',Framed-IP-Address=' . escapeshellarg($attributes['framedIPAddress']);
		$radclient = 'radclient -xr 1';
		$cmd = 'echo ' . escapeshellcmd($query) . ' | ' . $radclient . ' ' . $args . ' 2>&1';
		$res = shell_exec($cmd);
		if (isset($res) && (strpos($res, 'Received Disconnect-ACK') !== false)) {
			return true;
		}
		else {
			return false;
		}
	}

	public function kickOutUsersByAPI($nasObj, $userData)
	{
		$nasID = $nasObj[0]->id;
		$nasAPI = $nasObj[0]->nasapi;

		if ($nasAPI == 1) {
			$client = mkClientByID($nasID);

			if ($client) {
				if (($userData->connectiontype == 1) || ($userData->connectiontype == 3)) {
					$printRequest = new PEAR2\Net\RouterOS\Request('/ppp/active/print');
					$printRequest->setArgument('.proplist', '.id');
					$printRequest->setQuery(PEAR2\Net\RouterOS\Query::where('name', $userData->username));
					$apiID = $client->sendSync($printRequest)->getProperty('.id');
					$disableRequest = new PEAR2\Net\RouterOS\Request('/ppp/active/remove');
					$disableRequest->setArgument('numbers', $apiID);
					$client->sendSync($disableRequest);
					return true;
				}
				else if (($userData->connectiontype == 2) || ($userData->connectiontype == 4)) {
					$printRequest = new PEAR2\Net\RouterOS\Request('/ip/hotspot/active/print');
					$printRequest->setArgument('.proplist', '.id');
					$printRequest->setQuery(PEAR2\Net\RouterOS\Query::where('name', $userData->username));
					$apiID = $client->sendSync($printRequest)->getProperty('.id');
					$disableRequest = new PEAR2\Net\RouterOS\Request('/ip/hotspot/active/remove');
					$disableRequest->setArgument('numbers', $apiID);
					$client->sendSync($disableRequest);
					return true;
				}
				else {
					return false;
				}
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}

	public function dialCoARequest($userData, array $CoAData)
	{
		if (isset($userData) && (is_array($userData) || is_object($userData))) {
			$username = $userData->username;
			$onlineUsersData = getRadacctByUsername($username);
			if (is_object($onlineUsersData) && $onlineUsersData) {
				if (property_exists($onlineUsersData, 'nasipaddress') && property_exists($onlineUsersData, 'acctsessionid') && property_exists($onlineUsersData, 'framedipaddress')) {
					$nasIPAddress = $onlineUsersData->nasipaddress;
					$acctSessionID = $onlineUsersData->acctsessionid;
					$framedIPAddress = $onlineUsersData->framedipaddress;
					$radiusAttributes = [];
					$radiusAttributes['nasIPAddress'] = $nasIPAddress;
					$radiusAttributes['acctSessionID'] = $acctSessionID;
					$radiusAttributes['framedIPAddress'] = $framedIPAddress;
					$nasObj = $this->main->singleQuery('nas', ['nasname' => $nasIPAddress]);

					if (!$nasObj) {
						$userProfileNasID = $userData->nasid;
						$nasObj = $this->main->singleQuery('nas', ['id' => $userProfileNasID]);

						if ($nasObj) {
							$nasIPAddress = $nasObj[0]->nasname;
							$radiusAttributes['nasIPAddress'] = $nasIPAddress;
							return $this->dialCoAByRadius($nasObj, $userData, $radiusAttributes, $CoAData);
						}
						else {
							return ['status' => false, 'message' => 'User\'s NAS Not Found or NAS Not Added in System Yet!' . $username];
						}
					}
					else {
						return $this->dialCoAByRadius($nasObj, $userData, $radiusAttributes, $CoAData);
					}
				}
				else {
					return ['status' => false, 'message' => 'User\'s Online Attributes Not Found!'];
				}
			}
			else {
				return ['status' => false, 'message' => 'User Not Online! ' . $username];
			}
		}
		else {
			return ['status' => false, 'message' => 'User Not Found!'];
		}
	}

	public function dialCoAByRadius($nasObj, $userData, array $attributes, array $CoAData)
	{
		$username = $userData->username;
		$nasname = $nasObj[0]->nasname;
		$nasport = (!empty($nasObj[0]->incoming_port) ? $nasObj[0]->incoming_port : 3799);
		$nassecret = $nasObj[0]->secret;
		$res = shell_exec('echo "User-Name=' . $username . ',Acct-Session-Id=' . $attributes['acctSessionID'] . ',Framed-IP-Address=' . $attributes['framedIPAddress'] . ',Mikrotik-Rate-Limit=\'' . $CoAData['rateLimit'] . '\'" | radclient -xr 1 ' . $nasname . ':' . $nasport . ' coa ' . $nassecret);
		if (isset($res) && (strpos($res, 'Received CoA-ACK') !== false)) {
			return true;
		}
		else {
			return false;
		}
	}

	public function dialCoAByAPI($nasObj, $userData)
	{
		$nasID = $nasObj[0]->id;
		$nasAPI = $nasObj[0]->nasapi;

		if ($nasAPI == 1) {
			$client = mkClientByID($nasID);

			if ($client) {
				if (($userData->connectiontype == 1) || ($userData->connectiontype == 3)) {
					return true;
				}
				else if (($userData->connectiontype == 2) || ($userData->connectiontype == 4)) {
					return true;
				}
				else {
					return false;
				}
			}
			else {
				return false;
			}
		}
		else {
			return false;
		}
	}
}

?>