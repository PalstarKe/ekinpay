<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Userservice extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		isLogin();
		isKena();
		ifClient();
		isPermitted('user_module', [11, 12, 13], true);
	}

	public function index()
	{
		isPermitted('user_all_module', [11, 12, 13], true);
		redirect('user/all');
	}

	public function userserviceupdate()
	{
		isPermitted('user_service_details', [11, 12, 13], true);
		$errorMessages = [];
		$successMessages = [];
		if ((settings()[0]->kenalimit <= totalUsers()) || (settings()[0]->kenalimit == NULL)) {
			$this->session->set_flashdata('error', 'Your User Limit is Over. Please Contact Us For Upgrade.');
			redirect('support');
		}

		$userinfoData = [];
		$groupData = [];
		$macData = [];
		$expireData = [];
		$framedData = [];
		$framedmaskData = [];
		$qtTotalSession = NULL;
		$qtUsedSession = NULL;
		$userID = $this->input->post('userID', true);

		if (decryptInputPost($userID)) {
			$userID = decryptInputPost($userID);
		}
		else {
			$this->session->set_flashdata('error', 'Oops! Something Wrong');
			redirect('home');
		}

		$userData = $this->main->getUserinfoByID($userID);

		if (!$userData) {
			$this->session->set_flashdata('error', 'User Not Found.');
			redirect('user/profile/' . $userID);
		}

		isUserAccessible($userID);
		$userData = $userData[0];
		$username = $userData->username;
		$radacctData = getRadcheckByUsername($userData->username);
		$salespersonData = getAdminByID($userData->saleperson);
		$maclock = ($this->input->post('maclock', true) == 'on' ? 1 : 0);
		$nasid = $this->input->post('nasid', true);
		$currentPackageID = $this->input->post('currentPackageID', true);
		$status = $this->input->post('status', true);
		$smsstatus = ($this->input->post('smsstatus', true) == 'on' ? 1 : 0);

		if (checkAdminOrStaff()) {
			$connectiontype = $this->input->post('connectiontype', true);
			$package = $this->input->post('package', true);
			$totalvolume = $this->input->post('totalvolume', true);
			$totalvolume = preg_replace('/[^0-9.]/', '', $totalvolume);
			$usedvolume = $this->input->post('usedvolume', true);
			$discount = $this->input->post('discount', true);
			$saleperson = $this->input->post('saleperson', true);
			$expirationdate = $this->input->post('expirationdatepicker', true);
			$qtTotalSession = $this->input->post('qt_total_session', true);
			$qtUsedSession = $this->input->post('qt_session', true);
		}
		else {
			$connectiontype = $userData->connectiontype;
			$totalvolume = $userData->qt_total / 1024 / 1024 / 1024;
			$usedvolume = $userData->qt_used / 1024 / 1024 / 1024;
			$discount = $userData->discount;
			$saleperson = $userData->saleperson;

			if ($radacctData) {
				$expireTime = $radacctData->value;
				if (isset($expireTime) && !empty($expireTime) && (strtotime($expireTime) <= strtotime(date('d M Y H:i:s')))) {
					$package = $this->input->post('package', true);
				}
				else {
					$package = $userData->package;
				}
			}
			else {
				$package = $userData->package;
			}
		}
		if (!isset($package) || empty($package)) {
			$this->session->set_flashdata('error', 'Oops! No Package Found');
			redirect('user/profile/' . $userID);
		}

		$packageData = $this->main->getJoinPackagesByPkgID($package);

		if (!$packageData) {
			$this->session->set_flashdata('error', 'Oops! No Package Found');
			redirect('user/profile/' . $userID);
		}

		$fpackagePermission = $this->packagesM->profilePackagePermission($package, $userData->saleperson);
		if (!isset($fpackagePermission) || !is_array($fpackagePermission) || !$fpackagePermission[0]) {
			$this->session->set_flashdata('error', $fpackagePermission[1]);
			redirect('user/profile/' . $userID);
		}

		$packageID = $packageData[0]->id;
		$packageGroup = $packageData[0]->groupname;
		$packagePool = $packageData[0]->pool;
		$packageExpirePool = $packageData[0]->expirepool;
		$packageQuata = $packageData[0]->dataqt;
		$packageVolume = $packageData[0]->dataqtvol;
		if (checkAdminOrStaff() || ($salespersonData && ($salespersonData->resl_mac == 1))) {
			$macaddress = $this->input->post('macaddress', true);
		}
		if (checkAdminOrStaff() || ($salespersonData && ($salespersonData->resl_ip_lock == 1))) {
			$staticip = $this->input->post('staticip', true);
		}
		if (checkAdminOrStaff() || (settings()[0]->nasvisibility == 1)) {
			$nasid = $nasid;
		}
		else {
			$nasid = $userData->nasid;
		}
		if (((isset($userData->renew_date) || isset($userData->activation_date)) && (!empty($userData->activation_date) || !empty($userData->renew_date))) || (($userData->status == 0) || ($userData->status == 2) || checkAdminOrStaff())) {
			$userinfoData['status'] = $status;
		}
		if (isset($totalvolume) && (0 <= $totalvolume)) {
			$userinfoData['qt_total'] = $totalvolume * 1024 * 1024 * 1024;
		}
		if (isset($usedvolume) && (0 <= $usedvolume)) {
			$userinfoData['qt_used'] = $usedvolume * 1024 * 1024 * 1024;
		}
		if (isset($qtTotalSession) && (0 <= $qtTotalSession)) {
			$userinfoData['qt_total_session'] = $qtTotalSession;
		}
		if (isset($qtUsedSession) && (0 <= $qtUsedSession)) {
			$userinfoData['qt_session'] = $qtUsedSession;
		}

		if ($currentPackageID !== $package) {
			$userinfoData['package'] = $package;
			$userinfoData['c_package'] = $package;
		}

		$userinfoData['smsstatus'] = $smsstatus;
		$userinfoData['maclock'] = $maclock;
		$userinfoData['nasid'] = $nasid;
		$userinfoData['macaddress'] = $macaddress;
		$userinfoData['staticip'] = $staticip;
		if (isset($expirationdate) && !empty($expirationdate) && strtotime($expirationdate) && (time() < strtotime($expirationdate))) {
			$userinfoData['current_expiration_date'] = $expirationdate;
		}

		if (checkAdminOrStaff()) {
			if (isset($discount) && (0 <= $discount)) {
				$userinfoData['discount'] = $discount;
			}

			if (!empty($saleperson)) {
				$userinfoData['saleperson'] = $saleperson;
			}

			$userinfoData['connectiontype'] = $connectiontype;
		}

		$userinfoUpdateRes = $this->main->singleUpdate('usersinfo', $userinfoData, ['id' => $userID]);
		$preExpireTime = NULL;
		$expiryDateChangedStatus = NULL;

		if ($radacctData) {
			$preExpireTime = $radacctData->value;
		}
		if (isset($preExpireTime) && isset($expirationdate) && !empty($preExpireTime) && !empty($expirationdate) && strtotime($preExpireTime) && strtotime($expirationdate)) {
			if (strtotime($expirationdate) != strtotime($preExpireTime)) {
				$expiryDateChangedStatus = true;
			}
		}
		if (($userData->connectiontype != $connectiontype) || ($userData->package != $package) || ($userData->nasid != $nasid) || ($userData->staticip != $staticip) || ($userData->macaddress != $macaddress) || (isset($expiryDateChangedStatus) && $expiryDateChangedStatus)) {
			$radacctData = getRadacctByUsername($username);
			if ((is_object($radacctData) || is_array($radacctData)) && $radacctData) {
				if (settings()[0]->disconnecttype != 0) {
					try {
						$disconnectRes = $this->radiusM->kickOutUsers($username);

						if ($disconnectRes) {
							array_push($successMessages, 'User Successfully Disconnectd');
						}
						else {
							array_push($errorMessages, 'Failed To Disconnect! Manual Disconnect Action Required.');
						}
					}
					catch (Exception $e) {
						$errorMessage = 'Disconnect Error: ' . $e->getMessage();
						array_push($errorMessages, $errorMessage);
					}
				}
			}
		}

		$groupData['username'] = $userData->username;
		$groupData['groupname'] = $packageGroup;
		$groupData['priority'] = 1;
		$radGroupCheckQuery = $this->main->singleQuery('radusergroup', ['username' => $userData->username]);

		if ($radGroupCheckQuery) {
			$insertGroupRes = $this->main->singleUpdate('radusergroup', $groupData, ['username' => $userData->username]);
		}
		else {
			$insertGroupRes = $this->main->singleInsert('radusergroup', $groupData);
		}
		if (isset($macaddress) && !empty($macaddress)) {
			$macData['username'] = $userData->username;
			$macData['op'] = ':=';
			$macData['attribute'] = 'Calling-Station-Id';
			$macData['value'] = $macaddress;
			$radCheckMacQuery = $this->main->singleQuery('radcheck', ['username' => $userData->username, 'attribute' => 'Calling-Station-Id']);

			if ($radCheckMacQuery) {
				$insertMacRes = $this->main->singleUpdate('radcheck', $macData, ['username' => $userData->username, 'attribute' => 'Calling-Station-Id']);
			}
			else {
				$insertGroupRes = $this->main->singleInsert('radcheck', $macData);
			}
		}
		else {
			$insertGroupRes = $this->main->singleDelete('radcheck', ['username' => $userData->username, 'attribute' => 'Calling-Station-Id']);
		}
		if (isset($expirationdate) && !empty($expirationdate)) {
			$expirationdate = date('d M Y H:i:s', strtotime($expirationdate));
			$expireData['username'] = $userData->username;
			$expireData['attribute'] = 'Expiration';
			$expireData['op'] = ':=';
			$expireData['value'] = $expirationdate;
			$radCheckExpiryQuery = $this->main->singleQuery('radcheck', ['username' => $userData->username, 'attribute' => 'Expiration']);

			if ($radCheckExpiryQuery) {
				$insertExpiryRes = $this->main->singleUpdate('radcheck', $expireData, ['username' => $userData->username, 'attribute' => 'Expiration']);
			}
			else {
				$insertExpiryRes = $this->main->singleInsert('radcheck', $expireData);
			}
		}
		else if (checkAdminOrStaff()) {
			$insertExpiryRes = $this->main->singleDelete('radcheck', ['username' => $userData->username, 'attribute' => 'Expiration']);
		}
		if (isset($staticip) && !empty($staticip)) {
			$framedData['username'] = $userData->username;
			$framedData['op'] = ':=';
			$framedData['attribute'] = 'Framed-IP-Address';
			$framedData['value'] = $staticip;
			$radReplyFramedQuery = $this->main->singleQuery('radreply', ['username' => $userData->username, 'attribute' => 'Framed-IP-Address']);

			if ($radReplyFramedQuery) {
				$insertFramedRes = $this->main->singleUpdate('radreply', $framedData, ['username' => $userData->username, 'attribute' => 'Framed-IP-Address']);
			}
			else {
				$insertFramedRes = $this->main->singleInsert('radreply', $framedData);
			}

			$deletePoolRes = $this->main->singleDelete('radreply', ['username' => $userData->username, 'attribute' => 'Framed-Pool']);
		}
		else {
			$insertExpiryRes = $this->main->singleDelete('radreply', ['username' => $userData->username, 'attribute' => 'Framed-IP-Address']);
			$insertExpiryRes = $this->main->singleDelete('radreply', ['username' => $userData->username, 'attribute' => 'Framed-IP-Netmask']);
		}
		if (!isset($staticip) || empty($staticip)) {
			$radCheckObj = $this->main->singleQuery('radcheck', ['username' => $userData->username, 'attribute' => 'Expiration']);

			if ($radCheckObj) {
				$expireTime = strtotime($radCheckObj[0]->value);
				$yesterday = strtotime(date('Y-m-d H:i:s', strtotime('-1 days')));
				$today = strtotime(date('Y-m-d H:i:s'));
				if (($status == 0) || ($userData->connectionstatus == 0) || ($expireTime <= $today)) {
					$radReplyObj = $this->main->singleQuery('radreply', ['username' => $userData->username, 'attribute' => 'Framed-Pool']);

					if (!$radReplyObj) {
						if (!empty($packageExpirePool)) {
							$dataRadReply['username'] = $userData->username;
							$dataRadReply['attribute'] = 'Framed-Pool';
							$dataRadReply['op'] = ':=';
							$dataRadReply['value'] = $packageExpirePool;
							$this->main->singleInsert('radreply', $dataRadReply);
						}
					}
					else if (!empty($packageExpirePool)) {
						$dataRadReply['username'] = $userData->username;
						$dataRadReply['attribute'] = 'Framed-Pool';
						$dataRadReply['op'] = ':=';
						$dataRadReply['value'] = $packageExpirePool;
						$this->main->singleUpdate('radreply', $dataRadReply, ['username' => $userData->username, 'attribute' => 'Framed-Pool']);
					}
				}
				else if (($status == 2) || ($userData->connectionstatus == 1) || ($today <= $expireTime)) {
					$radReplyObj = $this->main->singleQuery('radreply', ['username' => $userData->username, 'attribute' => 'Framed-Pool']);

					if (!$radReplyObj) {
						if (!empty($packagePool)) {
							$dataRadReply['username'] = $userData->username;
							$dataRadReply['attribute'] = 'Framed-Pool';
							$dataRadReply['op'] = ':=';
							$dataRadReply['value'] = $packagePool;
							$this->main->singleInsert('radreply', $dataRadReply);
						}
					}
					else if (!empty($packagePool)) {
						$dataRadReply['username'] = $userData->username;
						$dataRadReply['attribute'] = 'Framed-Pool';
						$dataRadReply['op'] = ':=';
						$dataRadReply['value'] = $packagePool;
						$this->main->singleUpdate('radreply', $dataRadReply, ['username' => $userData->username, 'attribute' => 'Framed-Pool']);
					}
				}
			}
		}
		if (($packageQuata == 1) && isset($totalvolume) && !empty($totalvolume)) {
			$packVolume['username'] = $userData->username;
			$packVolume['attribute'] = 'Mikrotik-Total-Limit';
			$packVolume['op'] = ':=';
			$packVolume['value'] = $totalvolume * 1024 * 1024 * 1024;
			$radCheckObj = $this->main->singleQuery('radcheck', ['username' => $userData->username, 'attribute' => 'Mikrotik-Total-Limit']);

			if ($radCheckObj) {
				$this->db->where('username', $userData->username);
				$this->db->where('attribute', 'Mikrotik-Total-Limit');
				$stepSecond = $this->db->update('radcheck', $packVolume);
			}
			else {
				$stepSecond = $this->db->insert('radcheck', $packVolume);
			}
		}

		$this->main->singleDelete('radcheck', ['username' => $userData->username, 'attribute' => 'Auth-Type', 'value' => 'Reject']);
		$this->main->insertActivity('User Service Update', $userID);

		if (if_SMSEnable()) {
			if (chkSMSAlert(12) && getSMSAlert(12)) {
				if (getUserSMSStatus($userID) && getUserSalespersonSms($userID, $userData->saleperson)) {
					if ($status == 1) {
						$status = 'Pending';
					}
					else if ($status == 2) {
						$status = 'Active';
					}
					else {
						$status = 'Disable';
					}

					$mobile = getUserInfo($userID)->mobile;
					$smsText = getSMSAlert(12)->template;
					$smsText = str_replace('{status}', $status, $smsText);
					$smsText = getFinalSMSText($smsText, 2, $userID);
					$smsRes = sendSMS($mobile, $smsText);
					$smsData = ['smsAlert' => 12, 'destination' => $mobile, 'message' => $smsText, 'userID' => $userID, 'adminID' => 0];
					$responseSmsDelivery = $this->smsM->insertDelivery($smsRes, $smsData);
				}
			}
		}

		$this->session->set_flashdata('successMessages', $successMessages);
		$this->session->set_flashdata('errorMessages', $errorMessages);

		if ($userinfoData) {
			$this->session->set_flashdata('success', 'User Service Successfully Update');
			redirect('user/profile/' . $userID);
		}
		else {
			$this->session->set_flashdata('error', 'Oops! Something Wrong');
			redirect('user/profile/' . $userID);
		}
	}
}

?>