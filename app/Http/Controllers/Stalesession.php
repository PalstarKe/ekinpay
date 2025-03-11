<?php
namespace App\Http\Controllers;

defined('BASEPATH') || exit('No direct script access allowed');
use Illuminate\Http\Request;
class Stalesession extends Controller
{
	public $settings;

	public function __construct()
	{
		parent::__construct();
		$this->settings = settings()[0];
	}

	public function index()
	{
		if ($this->settings->auto_clear_log == 1) {
			$this->clearLogAuto();
		}

		if ($this->settings->radiusstalesession == 1) {
			$this->autoClearStaleSessions();
		}

		$this->addRemoveMacLock();
		$this->CoARequest();
	}

	public function clearLogAuto()
	{
		if ($this->settings->auto_clear_log == 1) {
			$this->main->singleInsert('log', ['data' => 'Cron Job', 'msg' => 'Cron Job Auto Log Removed', 'datetime' => date('Y-m-d H:i:s')]);
		}

		$this->db->where('datetime <=', date('Y-m-d 00:00:00'));
		$this->db->delete('livegraph');
		$this->db->flush_cache();
		$this->db->where('datetime <=', date('Y-m-d 00:00:00'));
		$this->db->delete('log');
		$this->db->flush_cache();
	}

	public function autoClearStaleSessions()
	{
		if ($this->settings->auto_clear_log == 1) {
			$this->main->singleInsert('log', ['data' => 'Cron Job', 'msg' => 'Cron Job Auto Remove Stale Sessions', 'datetime' => date('Y-m-d H:i:s')]);
		}

		$clearTimeLimit = $this->settings->stalesession;

		if ($this->settings->radiusstalesession == 1) {
			$onlineusers = $this->adminM->staleSessionUsers();

			if ($onlineusers) {
				$i = 0;

				foreach ($onlineusers as $user) {
					$username = $user->username;
					$acctUpdateTime = $user->acctupdatetime;
					$currentTime = date('Y-m-d H:i:s');
					$acctUpdateTime = date('Y-m-d H:i:s', strtotime('+' . $clearTimeLimit . ' minutes', strtotime($acctUpdateTime)));

					if (strtotime($acctUpdateTime) <= strtotime($currentTime)) {
						$data = [];
						$data['acctstoptime'] = $currentTime;
						$data['acctterminatecause'] = 'Auto Clear Stale Session';
						$result = $this->main->singleUpdate('radacct', $data, ['username' => $username, 'acctstoptime' => NULL]);

						if ($result) {
							$i++;
						}
					}
				}
			}
		}
	}

	public function addRemoveMacLock()
	{
		if ($this->settings->auto_clear_log == 1) {
			$this->main->singleInsert('log', ['data' => 'Cron Job', 'msg' => 'Cron Job Auto MAC Lock/Remove', 'datetime' => date('Y-m-d H:i:s')]);
		}

		if ($this->settings->maclockall == 1) {
			$this->db->join('usersinfo', 'usersinfo.username = radacct.username', 'left');
			$query = $this->db->get_where('radacct', ['radacct.acctstoptime' => NULL]);

			if (0 < $query->num_rows()) {
				foreach ($query->result() as $row) {
					$username = $row->username;
					if (isset($username) && !empty($username)) {
						$userOnlineMac = $row->callingstationid;
						$userMacAddress = getRadcheckMacByUser($username);

						if ($userMacAddress) {
							$userCurrentMac = $userMacAddress->value;

							if ($userCurrentMac !== $userOnlineMac) {
								if (!filter_var($userCurrentMac, FILTER_VALIDATE_MAC)) {
									$macData['username'] = $username;
									$macData['op'] = ':=';
									$macData['attribute'] = 'Calling-Station-Id';
									$macData['value'] = $userOnlineMac;
									$updateMacRes = $this->main->singleUpdate('radcheck', $macData, ['username' => $username, 'attribute' => 'Calling-Station-Id']);
								}
							}
						}
						else {
							$macData['username'] = $username;
							$macData['op'] = ':=';
							$macData['attribute'] = 'Calling-Station-Id';
							$macData['value'] = $userOnlineMac;
							$insertRadRes = $this->main->singleInsert('radcheck', $macData);
						}

						$userDataUpdate['maclock'] = 1;
						$userDataUpdate['macaddress'] = $userOnlineMac;
						$this->main->singleUpdate('usersinfo', $userDataUpdate, ['username' => $username]);
					}
				}
			}
		}
		else {
			$this->db->where('usersinfo.maclock', 1);
			$this->db->or_where('usersinfo.macaddress !=', '');
			$this->db->or_where('radcheck.attribute', 'Calling-Station-Id');
			$this->db->join('radcheck', 'radcheck.username = usersinfo.username', 'left');
			$query = $this->db->get('usersinfo');

			if (0 < $query->num_rows()) {
				foreach ($query->result() as $row) {
					$username = $row->username;

					if ($this->settings->removemaclockall == 1) {
						$deleteRadRes = $this->main->singleDelete('radcheck', ['username' => $username, 'attribute' => 'Calling-Station-Id']);
						$userinfoData['maclock'] = 0;
						$userinfoData['macaddress'] = NULL;
						$userinfoUpdateRes = $this->main->singleUpdate('usersinfo', $userinfoData, ['username' => $username]);
					}
					else if ($row->maclock != 1) {
						$deleteRadRes = $this->main->singleDelete('radcheck', ['username' => $username, 'attribute' => 'Calling-Station-Id']);
						$userinfoData['maclock'] = 0;
						$userinfoData['macaddress'] = NULL;
						$userinfoUpdateRes = $this->main->singleUpdate('usersinfo', $userinfoData, ['username' => $username]);
					}
					else if (isset($username) && !empty($username)) {
						$userOnlineStatus = getRadacctByUsername($username);
						if (isset($userOnlineStatus) && $userOnlineStatus) {
							$userOnlineMac = $userOnlineStatus->callingstationid;
							$userMacAddress = getRadcheckMacByUser($username);

							if ($userMacAddress) {
								$userCurrentMac = $userMacAddress->value;

								if ($userCurrentMac !== $userOnlineMac) {
									if (!filter_var($userCurrentMac, FILTER_VALIDATE_MAC)) {
										$macData['username'] = $username;
										$macData['op'] = ':=';
										$macData['attribute'] = 'Calling-Station-Id';
										$macData['value'] = $userOnlineMac;
										$updateMacRes = $this->main->singleUpdate('radcheck', $macData, ['username' => $username, 'attribute' => 'Calling-Station-Id']);
									}
								}
							}
							else {
								$macData['username'] = $username;
								$macData['op'] = ':=';
								$macData['attribute'] = 'Calling-Station-Id';
								$macData['value'] = $userOnlineMac;
								$insertRadRes = $this->main->singleInsert('radcheck', $macData);
							}

							$userDataUpdate['maclock'] = 1;
							$userDataUpdate['macaddress'] = $userOnlineMac;
							$this->main->singleUpdate('usersinfo', $userDataUpdate, ['username' => $username]);
						}
					}
				}
			}
		}
	}

	public function CoARequest()
	{
		if ($this->settings->auto_clear_log == 1) {
			$this->main->singleInsert('log', ['data' => 'Cron Job', 'msg' => 'Cron Job CoA Request To NAS', 'datetime' => date('Y-m-d H:i:s')]);
		}

		$usernames = [];
		$users = $this->main->getAllOnlineUsers();

		if ($users) {
			foreach ($users as $row) {
				$userData = $this->main->getUserByUsername($row->username);

				if ($userData) {
					$username = $userData->username;
					$package = $userData->package;
					$packageData = $this->main->getJoinPackagesByPkgID($package);

					if ($packageData) {
						$normalBwStatus = false;
						$packageData = $packageData[0];
						$pacakgeDBWStatus = $packageData->dynamicbw;
						if (isset($pacakgeDBWStatus) && ($pacakgeDBWStatus == 1)) {
							$currentTime = strtotime(date('H:i:s'));

							for ($x = 1; $x < 13; $x++) {
								${"pkgDBWStartTime" . $x} = $packageData->{'dbstarttime' . $x};
								${"pkgDBWEndTime" . $x} = $packageData->{'dbendtime' . $x};
								${"pkgDBWLimit" . $x} = $packageData->{'dblimit' . $x};
							}							
							if (isset($pkgDBWStartTime1) && isset($pkgDBWEndTime1) && isset($pkgDBWLimit1) && !empty($pkgDBWStartTime1) && !empty($pkgDBWEndTime1) && !empty($pkgDBWLimit1) && strtotime($pkgDBWStartTime1) && strtotime($pkgDBWEndTime1) && (strtotime($pkgDBWStartTime1) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime1))) {
								$CoADialResponse = $this->radiusM->dialCoARequest($userData, ['rateLimit' => $pkgDBWLimit1]);
								$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit1, $CoADialResponse);
								$normalBwStatus = true;
							}
							else if (isset($pkgDBWStartTime2) && isset($pkgDBWEndTime2) && isset($pkgDBWLimit2) && !empty($pkgDBWStartTime2) && !empty($pkgDBWEndTime2) && !empty($pkgDBWLimit2) && strtotime($pkgDBWStartTime2) && strtotime($pkgDBWEndTime2) && (strtotime($pkgDBWStartTime2) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime2))) {
								$CoADialResponse = $this->radiusM->dialCoARequest($userData, ['rateLimit' => $pkgDBWLimit2]);
								$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit2, $CoADialResponse);
								$normalBwStatus = true;
							}
							else if (isset($pkgDBWStartTime3) && isset($pkgDBWEndTime3) && isset($pkgDBWLimit3) && !empty($pkgDBWStartTime3) && !empty($pkgDBWEndTime3) && !empty($pkgDBWLimit3) && strtotime($pkgDBWStartTime3) && strtotime($pkgDBWEndTime3) && (strtotime($pkgDBWStartTime3) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime3))) {
								$CoADialResponse = $this->radiusM->dialCoARequest($userData, ['rateLimit' => $pkgDBWLimit3]);
								$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit3, $CoADialResponse);
								$normalBwStatus = true;
							}
							else if (isset($pkgDBWStartTime4) && isset($pkgDBWEndTime4) && isset($pkgDBWLimit4) && !empty($pkgDBWStartTime4) && !empty($pkgDBWEndTime4) && !empty($pkgDBWLimit4) && strtotime($pkgDBWStartTime4) && strtotime($pkgDBWEndTime4) && (strtotime($pkgDBWStartTime4) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime4))) {
								$CoADialResponse = $this->radiusM->dialCoARequest($userData, ['rateLimit' => $pkgDBWLimit4]);
								$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit4, $CoADialResponse);
								$normalBwStatus = true;
							}
							else if (isset($pkgDBWStartTime5) && isset($pkgDBWEndTime5) && isset($pkgDBWLimit5) && !empty($pkgDBWStartTime5) && !empty($pkgDBWEndTime5) && !empty($pkgDBWLimit5) && strtotime($pkgDBWStartTime5) && strtotime($pkgDBWEndTime5) && (strtotime($pkgDBWStartTime5) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime5))) {
								$CoADialResponse = $this->radiusM->dialCoARequest($userData, ['rateLimit' => $pkgDBWLimit5]);
								$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit5, $CoADialResponse);
								$normalBwStatus = true;
							}
							else if (isset($pkgDBWStartTime6) && isset($pkgDBWEndTime6) && isset($pkgDBWLimit6) && !empty($pkgDBWStartTime6) && !empty($pkgDBWEndTime6) && !empty($pkgDBWLimit6) && strtotime($pkgDBWStartTime6) && strtotime($pkgDBWEndTime6) && (strtotime($pkgDBWStartTime6) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime6))) {
								$CoADialResponse = $this->radiusM->dialCoARequest($userData, ['rateLimit' => $pkgDBWLimit6]);
								$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit6, $CoADialResponse);
								$normalBwStatus = true;
							}
							else if (isset($pkgDBWStartTime7) && isset($pkgDBWEndTime7) && isset($pkgDBWLimit7) && !empty($pkgDBWStartTime7) && !empty($pkgDBWEndTime7) && !empty($pkgDBWLimit7) && strtotime($pkgDBWStartTime7) && strtotime($pkgDBWEndTime7) && (strtotime($pkgDBWStartTime7) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime7))) {
								$CoADialResponse = $this->radiusM->dialCoARequest($userData, ['rateLimit' => $pkgDBWLimit7]);
								$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit7, $CoADialResponse);
								$normalBwStatus = true;
							}
							else if (isset($pkgDBWStartTime8) && isset($pkgDBWEndTime8) && isset($pkgDBWLimit8) && !empty($pkgDBWStartTime8) && !empty($pkgDBWEndTime8) && !empty($pkgDBWLimit8) && strtotime($pkgDBWStartTime8) && strtotime($pkgDBWEndTime8) && (strtotime($pkgDBWStartTime8) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime8))) {
								$CoADialResponse = $this->radiusM->dialCoARequest($userData, ['rateLimit' => $pkgDBWLimit8]);
								$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit8, $CoADialResponse);
								$normalBwStatus = true;
							}
							else if (isset($pkgDBWStartTime9) && isset($pkgDBWEndTime9) && isset($pkgDBWLimit9) && !empty($pkgDBWStartTime9) && !empty($pkgDBWEndTime9) && !empty($pkgDBWLimit9) && strtotime($pkgDBWStartTime9) && strtotime($pkgDBWEndTime9) && (strtotime($pkgDBWStartTime9) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime9))) {
								$CoADialResponse = $this->radiusM->dialCoARequest($userData, ['rateLimit' => $pkgDBWLimit9]);
								$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit9, $CoADialResponse);
								$normalBwStatus = true;
							}
							else if (isset($pkgDBWStartTime10) && isset($pkgDBWEndTime10) && isset($pkgDBWLimit10) && !empty($pkgDBWStartTime10) && !empty($pkgDBWEndTime10) && !empty($pkgDBWLimit10) && strtotime($pkgDBWStartTime10) && strtotime($pkgDBWEndTime10) && (strtotime($pkgDBWStartTime10) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime10))) {
								$CoADialResponse = $this->radiusM->dialCoARequest($userData, ['rateLimit' => $pkgDBWLimit10]);
								$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit10, $CoADialResponse);
								$normalBwStatus = true;
							}
							else if (isset($pkgDBWStartTime11) && isset($pkgDBWEndTime11) && isset($pkgDBWLimit11) && !empty($pkgDBWStartTime11) && !empty($pkgDBWEndTime11) && !empty($pkgDBWLimit11) && strtotime($pkgDBWStartTime11) && strtotime($pkgDBWEndTime11) && (strtotime($pkgDBWStartTime11) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime11))) {
								$CoADialResponse = $this->radiusM->dialCoARequest($userData, ['rateLimit' => $pkgDBWLimit11]);
								$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit11, $CoADialResponse);
								$normalBwStatus = true;
							}
							else if (isset($pkgDBWStartTime12) && isset($pkgDBWEndTime12) && isset($pkgDBWLimit12) && !empty($pkgDBWStartTime12) && !empty($pkgDBWEndTime12) && !empty($pkgDBWLimit12) && strtotime($pkgDBWStartTime12) && strtotime($pkgDBWEndTime12) && (strtotime($pkgDBWStartTime12) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime12))) {
								$CoADialResponse = $this->radiusM->dialCoARequest($userData, ['rateLimit' => $pkgDBWLimit12]);
								$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit12, $CoADialResponse);
								$normalBwStatus = true;
							}
						}

						$packageQuata = $packageData->dataqt;
						if (isset($packageQuata) && ($packageQuata == 1)) {
							$packageVolume = $packageData->dataqtvol;
							if (isset($packageVolume) && (0 < $packageVolume)) {
								$pkgDQtActionStatus = $packageData->data_quota_exceed_status;
								if (isset($pkgDQtActionStatus) && ($pkgDQtActionStatus == 1)) {
									$pkgDQtActionType = $packageData->data_quota_exceed_type;
									$userCurrrentVol = (float) $userData->qt_used;
									if (isset($pkgDQtActionType) && ($pkgDQtActionType == 1)) {
										if ($packageVolume < $userCurrrentVol) {
											if ($this->settings->disconnecttype != 0) {
												try {
													$disconnectRes = $this->radiusM->kickOutUsers($username);
												}
												catch (Exception $e) {
												}
											}
										}
									}
									else {
										$pkgDFUPQtStatus = $packageData->fupqt;
										$pkgDFUPQtVol = (float) $packageData->fupqtvol;
										$pkgDFUPQtBWLimit = $packageData->fupqtbwlimit;
										if (isset($pkgDFUPQtStatus) && ($pkgDFUPQtStatus == 1)) {
											if (($pkgDFUPQtVol < $userCurrrentVol) && (isset($pkgDFUPQtBWLimit) && !empty($pkgDFUPQtBWLimit))) {
												$CoADialResponse = $this->radiusM->dialCoARequest($userData, ['rateLimit' => $pkgDFUPQtBWLimit]);
												$normalBwStatus = true;
											}
										}

										$this->CoAResponsePrint($username, $packageData->groupname, 'FUP Speed', $pkgDFUPQtBWLimit, $CoADialResponse);
									}
								}
							}
						}

						$pkgSessionQt = $packageData->sessionqt;
						if (isset($pkgSessionQt) && ($pkgSessionQt == 1)) {
							$pkgSessionTime = (float) $packageData->sessiontime;
							if (isset($pkgSessionTime) && (0 < $pkgSessionTime)) {
								$pkgSQtActionStatus = $packageData->session_quota_exceed_status;
								if (isset($pkgSQtActionStatus) && ($pkgSQtActionStatus == 1)) {
									$userCurrrentSession = (float) $userData->qt_session;
									$pkgSFUPQtBWLimit = $packageData->session_fup_bw_limit;
									$pkgSQtActionType = $packageData->session_quota_exceed_type;
									if (isset($pkgSQtActionType) && ($pkgSQtActionType == 1)) {
										if ($pkgSessionTime < $userCurrrentSession) {
											if ($this->settings->disconnecttype != 0) {
												try {
													$disconnectRes = $this->radiusM->kickOutUsers($username);
												}
												catch (Exception $e) {
												}
											}
										}
									}
									else {
										$pkgSFUPQtStatus = $packageData->session_fup_limit_status;
										if (isset($pkgSFUPQtStatus) && ($pkgSFUPQtStatus == 1)) {
											if (($pkgSessionTime < $userCurrrentSession) && (isset($pkgSFUPQtBWLimit) && !empty($pkgSFUPQtBWLimit))) {
												$CoADialResponse = $this->radiusM->dialCoARequest($userData, ['rateLimit' => $pkgSFUPQtBWLimit]);
												$normalBwStatus = true;
											}
										}

										$this->CoAResponsePrint($username, $packageData->groupname, 'Session Speed', $pkgSFUPQtBWLimit, $CoADialResponse);
									}
								}
							}
						}
						if (isset($normalBwStatus) && !$normalBwStatus) {
							$pkgNormalBWLimit = NULL;
							$this->db->reset_query();
							$this->db->select('value');
							$this->db->from('radgroupreply');
							$this->db->where('groupname', $packageData->groupname);
							$this->db->where('attribute', 'Mikrotik-Rate-Limit');
							$this->db->order_by('id', 'desc');
							$this->db->limit(1);
							$bwLimitQueryRes = $this->db->get();
							$this->db->reset_query();

							if (0 < $bwLimitQueryRes->num_rows()) {
								$bwLimitData = $bwLimitQueryRes->result();

								foreach ($bwLimitData as $bwLimitDataArray) {
									$pkgNormalBWLimit = $bwLimitDataArray->value;
								}
							}
							else {
								$pkgNormalBWLimit = NULL;
							}
							if (isset($pkgNormalBWLimit) && !empty($pkgNormalBWLimit) && ($pkgNormalBWLimit !== NULL)) {
								$CoADialResponse = $this->radiusM->dialCoARequest($userData, ['rateLimit' => $pkgNormalBWLimit]);
							}

							$this->CoAResponsePrint($username, $packageData->groupname, 'Normal Speed', $pkgNormalBWLimit, $CoADialResponse);
						}
					}
				}
				else {
					$singleTokenData = $this->db->select('*')->from('token')->where('username', $row->username)->get();

					if (0 < $singleTokenData->num_rows()) {
						$tokenData = $singleTokenData->result()[0];
						$username = $tokenData->username;
						$package = $tokenData->package_id;
						$packageData = $this->main->getJoinPackagesByPkgID($package);

						if ($packageData) {
							$normalBwStatus = false;
							$packageData = $packageData[0];
							$pacakgeDBWStatus = $packageData->dynamicbw;
							if (isset($pacakgeDBWStatus) && ($pacakgeDBWStatus == 1)) {
								$currentTime = strtotime(date('H:i:s'));

								for ($x = 1; $x < 13; $x++) {
									${"pkgDBWStartTime" . $x} = $packageData->{'dbstarttime' . $x};
									${"pkgDBWEndTime" . $x} = $packageData->{'dbendtime' . $x};
									${"pkgDBWLimit" . $x} = $packageData->{'dblimit' . $x};
								}								
								if (isset($pkgDBWStartTime1) && isset($pkgDBWEndTime1) && isset($pkgDBWLimit1) && !empty($pkgDBWStartTime1) && !empty($pkgDBWEndTime1) && !empty($pkgDBWLimit1) && strtotime($pkgDBWStartTime1) && strtotime($pkgDBWEndTime1) && (strtotime($pkgDBWStartTime1) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime1))) {
									$CoADialResponse = $this->tokenM->dialCoARequest($tokenData, ['rateLimit' => $pkgDBWLimit1]);
									$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit1, $CoADialResponse);
									$normalBwStatus = true;
								}
								else if (isset($pkgDBWStartTime2) && isset($pkgDBWEndTime2) && isset($pkgDBWLimit2) && !empty($pkgDBWStartTime2) && !empty($pkgDBWEndTime2) && !empty($pkgDBWLimit2) && strtotime($pkgDBWStartTime2) && strtotime($pkgDBWEndTime2) && (strtotime($pkgDBWStartTime2) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime2))) {
									$CoADialResponse = $this->tokenM->dialCoARequest($tokenData, ['rateLimit' => $pkgDBWLimit2]);
									$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit2, $CoADialResponse);
									$normalBwStatus = true;
								}
								else if (isset($pkgDBWStartTime3) && isset($pkgDBWEndTime3) && isset($pkgDBWLimit3) && !empty($pkgDBWStartTime3) && !empty($pkgDBWEndTime3) && !empty($pkgDBWLimit3) && strtotime($pkgDBWStartTime3) && strtotime($pkgDBWEndTime3) && (strtotime($pkgDBWStartTime3) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime3))) {
									$CoADialResponse = $this->tokenM->dialCoARequest($tokenData, ['rateLimit' => $pkgDBWLimit3]);
									$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit3, $CoADialResponse);
									$normalBwStatus = true;
								}
								else if (isset($pkgDBWStartTime4) && isset($pkgDBWEndTime4) && isset($pkgDBWLimit4) && !empty($pkgDBWStartTime4) && !empty($pkgDBWEndTime4) && !empty($pkgDBWLimit4) && strtotime($pkgDBWStartTime4) && strtotime($pkgDBWEndTime4) && (strtotime($pkgDBWStartTime4) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime4))) {
									$CoADialResponse = $this->tokenM->dialCoARequest($tokenData, ['rateLimit' => $pkgDBWLimit4]);
									$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit4, $CoADialResponse);
									$normalBwStatus = true;
								}
								else if (isset($pkgDBWStartTime5) && isset($pkgDBWEndTime5) && isset($pkgDBWLimit5) && !empty($pkgDBWStartTime5) && !empty($pkgDBWEndTime5) && !empty($pkgDBWLimit5) && strtotime($pkgDBWStartTime5) && strtotime($pkgDBWEndTime5) && (strtotime($pkgDBWStartTime5) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime5))) {
									$CoADialResponse = $this->tokenM->dialCoARequest($tokenData, ['rateLimit' => $pkgDBWLimit5]);
									$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit5, $CoADialResponse);
									$normalBwStatus = true;
								}
								else if (isset($pkgDBWStartTime6) && isset($pkgDBWEndTime6) && isset($pkgDBWLimit6) && !empty($pkgDBWStartTime6) && !empty($pkgDBWEndTime6) && !empty($pkgDBWLimit6) && strtotime($pkgDBWStartTime6) && strtotime($pkgDBWEndTime6) && (strtotime($pkgDBWStartTime6) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime6))) {
									$CoADialResponse = $this->tokenM->dialCoARequest($tokenData, ['rateLimit' => $pkgDBWLimit6]);
									$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit6, $CoADialResponse);
									$normalBwStatus = true;
								}
								else if (isset($pkgDBWStartTime7) && isset($pkgDBWEndTime7) && isset($pkgDBWLimit7) && !empty($pkgDBWStartTime7) && !empty($pkgDBWEndTime7) && !empty($pkgDBWLimit7) && strtotime($pkgDBWStartTime7) && strtotime($pkgDBWEndTime7) && (strtotime($pkgDBWStartTime7) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime7))) {
									$CoADialResponse = $this->tokenM->dialCoARequest($tokenData, ['rateLimit' => $pkgDBWLimit7]);
									$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit7, $CoADialResponse);
									$normalBwStatus = true;
								}
								else if (isset($pkgDBWStartTime8) && isset($pkgDBWEndTime8) && isset($pkgDBWLimit8) && !empty($pkgDBWStartTime8) && !empty($pkgDBWEndTime8) && !empty($pkgDBWLimit8) && strtotime($pkgDBWStartTime8) && strtotime($pkgDBWEndTime8) && (strtotime($pkgDBWStartTime8) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime8))) {
									$CoADialResponse = $this->tokenM->dialCoARequest($tokenData, ['rateLimit' => $pkgDBWLimit8]);
									$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit8, $CoADialResponse);
									$normalBwStatus = true;
								}
								else if (isset($pkgDBWStartTime9) && isset($pkgDBWEndTime9) && isset($pkgDBWLimit9) && !empty($pkgDBWStartTime9) && !empty($pkgDBWEndTime9) && !empty($pkgDBWLimit9) && strtotime($pkgDBWStartTime9) && strtotime($pkgDBWEndTime9) && (strtotime($pkgDBWStartTime9) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime9))) {
									$CoADialResponse = $this->tokenM->dialCoARequest($tokenData, ['rateLimit' => $pkgDBWLimit9]);
									$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit9, $CoADialResponse);
									$normalBwStatus = true;
								}
								else if (isset($pkgDBWStartTime10) && isset($pkgDBWEndTime10) && isset($pkgDBWLimit10) && !empty($pkgDBWStartTime10) && !empty($pkgDBWEndTime10) && !empty($pkgDBWLimit10) && strtotime($pkgDBWStartTime10) && strtotime($pkgDBWEndTime10) && (strtotime($pkgDBWStartTime10) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime10))) {
									$CoADialResponse = $this->tokenM->dialCoARequest($tokenData, ['rateLimit' => $pkgDBWLimit10]);
									$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit10, $CoADialResponse);
									$normalBwStatus = true;
								}
								else if (isset($pkgDBWStartTime11) && isset($pkgDBWEndTime11) && isset($pkgDBWLimit11) && !empty($pkgDBWStartTime11) && !empty($pkgDBWEndTime11) && !empty($pkgDBWLimit11) && strtotime($pkgDBWStartTime11) && strtotime($pkgDBWEndTime11) && (strtotime($pkgDBWStartTime11) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime11))) {
									$CoADialResponse = $this->tokenM->dialCoARequest($tokenData, ['rateLimit' => $pkgDBWLimit11]);
									$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit11, $CoADialResponse);
									$normalBwStatus = true;
								}
								else if (isset($pkgDBWStartTime12) && isset($pkgDBWEndTime12) && isset($pkgDBWLimit12) && !empty($pkgDBWStartTime12) && !empty($pkgDBWEndTime12) && !empty($pkgDBWLimit12) && strtotime($pkgDBWStartTime12) && strtotime($pkgDBWEndTime12) && (strtotime($pkgDBWStartTime12) <= $currentTime) && ($currentTime <= strtotime($pkgDBWEndTime12))) {
									$CoADialResponse = $this->tokenM->dialCoARequest($tokenData, ['rateLimit' => $pkgDBWLimit12]);
									$this->CoAResponsePrint($username, $packageData->groupname, 'BW Speed', $pkgDBWLimit12, $CoADialResponse);
									$normalBwStatus = true;
								}
							}

							$packageQuata = $packageData->dataqt;
							if (isset($packageQuata) && ($packageQuata == 1)) {
								$packageVolume = $packageData->dataqtvol;
								if (isset($packageVolume) && (0 < $packageVolume)) {
									$pkgDQtActionStatus = $packageData->data_quota_exceed_status;
									if (isset($pkgDQtActionStatus) && ($pkgDQtActionStatus == 1)) {
										$pkgDQtActionType = $packageData->data_quota_exceed_type;
										$userCurrrentVol = (float) $userData->qt_used;
										if (isset($pkgDQtActionType) && ($pkgDQtActionType == 1)) {
											if ($packageVolume < $userCurrrentVol) {
												if ($this->settings->disconnecttype != 0) {
													try {
														$disconnectRes = $this->tokenM->kickOutUsers($tokenData);
													}
													catch (Exception $e) {
													}
												}
											}
										}
										else {
											$pkgDFUPQtStatus = $packageData->fupqt;
											$pkgDFUPQtVol = (float) $packageData->fupqtvol;
											$pkgDFUPQtBWLimit = $packageData->fupqtbwlimit;
											if (isset($pkgDFUPQtStatus) && ($pkgDFUPQtStatus == 1)) {
												if (($pkgDFUPQtVol < $userCurrrentVol) && (isset($pkgDFUPQtBWLimit) && !empty($pkgDFUPQtBWLimit))) {
													$CoADialResponse = $this->tokenM->dialCoARequest($tokenData, ['rateLimit' => $pkgDFUPQtBWLimit]);
													$normalBwStatus = true;
												}
											}

											$this->CoAResponsePrint($username, $packageData->groupname, 'FUP Speed', $pkgDFUPQtBWLimit, $CoADialResponse);
										}
									}
								}
							}

							$pkgSessionQt = $packageData->sessionqt;
							if (isset($pkgSessionQt) && ($pkgSessionQt == 1)) {
								$pkgSessionTime = (float) $packageData->sessiontime;
								if (isset($pkgSessionTime) && (0 < $pkgSessionTime)) {
									$pkgSQtActionStatus = $packageData->session_quota_exceed_status;
									if (isset($pkgSQtActionStatus) && ($pkgSQtActionStatus == 1)) {
										$userCurrrentSession = (float) $userData->qt_session;
										$pkgSFUPQtBWLimit = $packageData->session_fup_bw_limit;
										$pkgSQtActionType = $packageData->session_quota_exceed_type;
										if (isset($pkgSQtActionType) && ($pkgSQtActionType == 1)) {
											if ($pkgSessionTime < $userCurrrentSession) {
												if ($this->settings->disconnecttype != 0) {
													try {
														$disconnectRes = $this->tokenM->kickOutUsers($tokenData);
													}
													catch (Exception $e) {
													}
												}
											}
										}
										else {
											$pkgSFUPQtStatus = $packageData->session_fup_limit_status;
											if (isset($pkgSFUPQtStatus) && ($pkgSFUPQtStatus == 1)) {
												if (($pkgSessionTime < $userCurrrentSession) && (isset($pkgSFUPQtBWLimit) && !empty($pkgSFUPQtBWLimit))) {
													$CoADialResponse = $this->tokenM->dialCoARequest($tokenData, ['rateLimit' => $pkgSFUPQtBWLimit]);
													$normalBwStatus = true;
												}
											}

											$this->CoAResponsePrint($username, $packageData->groupname, 'Session Speed', $pkgSFUPQtBWLimit, $CoADialResponse);
										}
									}
								}
							}
							if (isset($normalBwStatus) && !$normalBwStatus) {
								$pkgNormalBWLimit = NULL;
								$this->db->reset_query();
								$this->db->select('value');
								$this->db->from('radgroupreply');
								$this->db->where('groupname', $packageData->groupname);
								$this->db->where('attribute', 'Mikrotik-Rate-Limit');
								$this->db->order_by('id', 'desc');
								$this->db->limit(1);
								$bwLimitQueryRes = $this->db->get();
								$this->db->reset_query();

								if (0 < $bwLimitQueryRes->num_rows()) {
									$bwLimitData = $bwLimitQueryRes->result();

									foreach ($bwLimitData as $bwLimitDataArray) {
										$pkgNormalBWLimit = $bwLimitDataArray->value;
									}
								}
								else {
									$pkgNormalBWLimit = NULL;
								}
								if (isset($pkgNormalBWLimit) && !empty($pkgNormalBWLimit) && ($pkgNormalBWLimit !== NULL)) {
									$CoADialResponse = $this->tokenM->dialCoARequest($tokenData, ['rateLimit' => $pkgNormalBWLimit]);
								}

								$this->CoAResponsePrint($username, $packageData->groupname, 'Normal Speed', $pkgNormalBWLimit, $CoADialResponse);
							}
						}
					}
				}
			}
		}
	}

	public function CoAResponsePrint($username, $packageGroup = NULL, $coaDialSection = NULL, $bwLimit = NULL, $CoADialResponse)
	{
		$printText = '{' . $username . '} {' . $packageGroup . '} {' . $coaDialSection . '} {' . $bwLimit . '}';
		if (isset($CoADialResponse) && !empty($CoADialResponse) && is_array($CoADialResponse)) {
			if (array_key_exists('message', $CoADialResponse)) {
				$printText .= '{' . $CoADialResponse['message'] . '}';
			}
		}
		else if (isset($CoADialResponse) && !empty($CoADialResponse) && is_bool($CoADialResponse)) {
			if ($CoADialResponse) {
				$printText .= ' {CoA : Success}';
			}
			else {
				$printText .= ' {CoA : Failed}';
			}
		}

		echo $printText . PHP_EOL;
	}
}
