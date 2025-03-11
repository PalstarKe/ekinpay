<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Activation extends CI_Controller
{
	public $settings;

	public function __construct()
	{
		parent::__construct();
		isLogin();
		isKena();
		ifClient();
		isPermitted('user_module', [11, 12, 13], true);
		$this->settings = settings()[0];
	}

	public function index()
	{
		isPermitted('user_all_module', [11, 12, 13], true);
		redirect('user/all');
	}

	public function ajaxfetch()
	{
		$requestData = $_REQUEST;
		if (!isset($requestData) || empty($requestData)) {
			$this->session->set_flashdata('error', 'Forbidden! You Can\'t Direct Access.');
			redirect('home');
		}

		$packageID = $this->input->post('package');
		$newCustomExpirationTime = $this->input->post('custom_time');
		$newCustomExpirationDate = $this->input->post('custom_date');
		$newCustomExpirationType = $this->input->post('custom_date_type');
		$data = [];
		$data['error'] = '';
		$data['statusName'] = 'N/A';
		$data['balanceStatus'] = 'N/A';
		$data['expiryStatus'] = 'N/A';
		$data['currentPackageList'] = 'N/A';
		$data['currentPackageName'] = 'N/A';
		$data['pppoeProfileList'] = '';
		$data['hotspotProfileList'] = '';
		$data['hotspotServerList'] = '';
		$data['interfaceList'] = '';
		$htmlPackages = NULL;
		$customExpiryDate = NULL;
		$userID = $this->input->post('userid', true);

		if (decryptInputPost($userID)) {
			$userID = decryptInputPost($userID);
		}
		else {
			$data['error'] = 'User ID Not Found.';
		}
		if (isset($newCustomExpirationDate) && !empty($newCustomExpirationDate) && strtotime($newCustomExpirationDate) && (strtotime($newCustomExpirationDate) < time())) {
			$data['error'] = 'You Can\'t Select Datetime In Past.';
		}
		if (isset($newCustomExpirationDate) && !empty($newCustomExpirationDate) && strtotime($newCustomExpirationDate)) {
			$newCustomExpirationDate = date('Y-m-d H:i:s', strtotime($newCustomExpirationDate));
			$customExpiryDate = [];
			$resellerSettings = resellersSettings();
			if (checkReseller() && $resellerSettings && ($resellerSettings->resl_allow_custom_expiry == 1)) {
				$customExpiryDate['customExpiryType'] = $newCustomExpirationType;
				$customExpiryDate['customDate'] = $newCustomExpirationDate;
			}
			else if (checkAdminOrStaff()) {
				$customExpiryDate['customExpiryType'] = $newCustomExpirationType;
				$customExpiryDate['customDate'] = $newCustomExpirationDate;
			}

			$data['customExpiryType'] = $newCustomExpirationType;
			$data['customDate'] = $newCustomExpirationDate;
		}

		$userData = $this->main->getUserinfoByID($userID);

		if ($userData) {
			$userData = $userData[0];
			if (!isset($packageID) || empty($packageID)) {
				$packageID = $userData->package;
			}

			$data['userid'] = $userData->id;
			$data['username'] = $userData->username;
			$data['userStatus'] = $userData->status;
			$userSalesPerson = $userData->saleperson;
			$connectionType = $userData->connectiontype;
			$nasID = $userData->nasid;
			$userRadCheckData = getRadcheckByUsername($userData->username);
			$data['expiryStatus'] = false;

			if ($userRadCheckData) {
				$expireTime = $userRadCheckData->value;
				if (isset($expireTime) && !empty($expireTime)) {
					if (strtotime(date('d M Y H:i:s')) <= strtotime($expireTime)) {
						$data['expiryStatus'] = true;
					}
				}
			}

			$userCurrentBalance = $this->main->getLGBLByUserID($userID);
			$data['balanceStatus'] = $userCurrentBalance;
			$userStatusData = getTypesByData($userData->status, 'userstatus');

			if ($userStatusData) {
				$data['statusName'] = $userStatusData->description;
			}
			else {
				$data['error'] = 'User Status Not Found';
			}

			if ($this->main->getJoinPackagesByPkgID($packageID)) {
				$fpackagePermission = $this->packagesM->profilePackagePermission($packageID, $userSalesPerson);
				if (isset($fpackagePermission) && is_array($fpackagePermission) && $fpackagePermission[0]) {
					$packageData = $this->main->getJoinPackagesByPkgID($packageID);
					$expirationData = $this->expirationM->expireDate($userData, $packageData, $newCustomExpirationTime, $customExpiryDate);
					$data['expiration'] = $expirationData['expiration'];
					$data['packageData'] = $expirationData['packageData'];
					$data['fixedExpiryDate'] = $expirationData['fixedExpiryDate'];
					$packageAccounting = $this->accountingM->packageAccounting($userData, $packageData, NULL, $expirationData, NULL, $customExpiryDate, 1);
					$data['packageAccounting'] = $packageAccounting;
				}
			}
			else {
				$data['error'] = 'User Package Error';
			}

			$userSalesPersonData = $this->main->getAdminByAdminID($userSalesPerson);

			if ($userSalesPersonData) {
				$userResellerRole = $userSalesPersonData[0]->role;
				if (($userResellerRole == 11) || ($userResellerRole == 12) || ($userResellerRole == 13)) {
					$allPackages = $this->main->getJoinFPackagesByAdminID($userData->saleperson);
					$currentFPackage = $this->main->singleQuery('f_packages', ['fadminid' => $userData->saleperson, 'pkgid' => $packageID]);
				}
				else {
					$allPackages = $this->main->getJoinFPackagesByRealAdminID($userData->saleperson);
					$currentFPackage = $this->main->singleQuery('f_packages', ['adminid' => $userData->saleperson, 'pkgid' => $packageID]);
				}

				if ($currentFPackage) {
					$userPackageData = $this->main->getGMPackagesByID($packageID);
					$data['currentPackageName'] = $packageName = ($userPackageData ? $userPackageData[0]->name : 'N/A');
					$htmlPackages .= '<option selected="" value="' . $currentFPackage[0]->pkgid . '">' . $packageName . ' (*)</option>';
				}

				if ($allPackages) {
					foreach ($allPackages as $pack) {
						$htmlPackages .= '<option value="' . $pack->id . '">' . $pack->name . '</option>';
					}

					$data['currentPackageList'] = $htmlPackages;
				}
				else {
					$data['error'] = 'Reseller Package Not Found, Set Reseller Package.';
				}

				if ($connectionType == 3) {
					$nasObj = $this->main->singleQuery('nas', ['id' => $nasID]);

					if ($nasObj) {
						$nasAPI = $nasObj[0]->nasapi;

						if ($nasAPI == 1) {
							$routerObj = mkUtilByID($nasID);

							if ($routerObj) {
								$pppoeProfiles = $routerObj->setMenu('/ppp profile')->getAll();
								$profileList = '<option value=\'\'>Select Profile</option>';

								foreach ($pppoeProfiles as $profile) {
									$profileList .= '<option value=' . $profile->getProperty('name') . '>' . $profile->getProperty('name') . '</option>';
								}

								$data['pppoeProfileList'] = $profileList;
							}
							else {
								$data['error'] = 'Oops! Something Went Wrong. Please, Check Your NAS.';
							}
						}
						else {
							$data['error'] = 'API is not enable on this NAS. Please enable API of this NAS.';
						}
					}
					else {
						$data['error'] = 'Oops! Something Went Wrong. Please, Check Your NAS.';
					}
				}
				else if ($connectionType == 4) {
					$nasObj = $this->main->singleQuery('nas', ['id' => $nasID]);

					if ($nasObj) {
						$nasAPI = $nasObj[0]->nasapi;

						if ($nasAPI == 1) {
							$routerObj = mkUtilByID($nasID);

							if ($routerObj) {
								$hotspotProfiles = $routerObj->setMenu('/ip hotspot user profile')->getAll();
								$profileList = '<option value=\'\'>Select Profile</option>';

								foreach ($hotspotProfiles as $profile) {
									$profileList .= '<option value=' . $profile->getProperty('name') . '>' . $profile->getProperty('name') . '</option>';
								}

								$data['hotspotProfileList'] = $profileList;
								$hotspotServers = $routerObj->setMenu('/ip hotspot')->getAll();
								$serverList = '<option value=\'\'>Select Hotspot Server</option>';

								foreach ($hotspotServers as $server) {
									$serverList .= '<option value=' . $server->getProperty('name') . '>' . $server->getProperty('name') . '</option>';
								}

								$data['hotspotServerList'] = $serverList;
							}
							else {
								$data['error'] = 'Oops! Something Went Wrong. Please, Check Your NAS.';
							}
						}
						else {
							$data['error'] = 'API is not enable on this NAS. Please enable API of this NAS.';
						}
					}
					else {
						$data['error'] = 'Oops! Something Went Wrong. NAS Not Found.';
					}
				}
				else if ($connectionType == 5) {
					$nasObj = $this->main->singleQuery('nas', ['id' => $nasID]);

					if ($nasObj) {
						$nasAPI = $nasObj[0]->nasapi;

						if ($nasAPI == 1) {
							$routerObj = mkUtilByID($nasID);

							if ($routerObj) {
								$interfaces = $routerObj->setMenu('/interface')->getAll();
								$interfaceList = '<option value=\'\'>Select Interface</option>';

								foreach ($interfaces as $interface) {
									$interfaceList .= '<option value=' . $interface->getProperty('name') . '>' . $interface->getProperty('name') . '</option>';
								}

								$data['interfaceList'] = $interfaceList;
							}
							else {
								$data['error'] = 'Oops! Something Went Wrong. Please, Check Your NAS.';
							}
						}
						else {
							$data['error'] = 'API is not enable on this NAS. Please enable API of this NAS.';
						}
					}
					else {
						$data['error'] = 'Oops! Something Went Wrong. NAS Not Found.';
					}
				}
			}
			else {
				$data['error'] = 'Salesperson Not Found!';
			}
		}
		else {
			$data['error'] = 'User Not Found!';
		}

		echo json_encode($data);
	}

	public function activation()
	{
		isPermitted('user_activation', [11, 12, 13], true);
		$errorMessages = [];
		$successMessages = [];
		if (($this->settings->kenalimit <= totalUsers()) || ($this->settings->kenalimit == NULL)) {
			$this->session->set_flashdata('error', 'Your User Limit is Over. Please Contact Us For Upgrade.');
			redirect('support');
		}

		$userID = $this->input->post('userID', true);

		if (decryptInputPost($userID)) {
			$userID = decryptInputPost($userID);
		}
		else {
			$this->session->set_flashdata('error', 'Oops! Something Wrong');
			redirect('home');
		}

		$username = $this->input->post('username', true);
		$package = $this->input->post('package', true);
		$expireTime = $this->input->post('expirytime', true);
		$pppoeProfile = $this->input->post('pppoeprofile', true);
		$hotspotProfile = $this->input->post('hotspotprofile', true);
		$hotspotServer = $this->input->post('hotspotserver', true);
		$interface = $this->input->post('interface', true);
		$otherData = ['pppoeProfile' => $pppoeProfile, 'hotspotProfile' => $hotspotProfile, 'hotspotServer' => $hotspotServer, 'interface' => $interface];
		$cost1level = $this->input->post('cost1level', true);
		$cost2level = $this->input->post('cost2level', true);
		$cost3level = $this->input->post('cost3level', true);
		$cost4level = $this->input->post('cost4level', true);
		$cost5level = $this->input->post('cost5level', true);
		$cost1 = (float) $this->input->post('cost1', true);
		$cost2 = (float) $this->input->post('cost2', true);
		$cost3 = (float) $this->input->post('cost3', true);
		$cost4 = (float) $this->input->post('cost4', true);
		$cost5 = (float) $this->input->post('cost5', true);
		$extraServiceFee = $cost1 + $cost2 + $cost3 + $cost4 + $cost5;
		$customExpiryType = $this->input->post('custom_expiry_type', true);
		$customDate = $this->input->post('custom_date', true);
		if (($customExpiryType == 2) && !strtotime($customDate)) {
			$this->session->set_flashdata('error', 'Invalid Custom Expiry Date Format');
			redirect('user/all');
		}
		if (isset($customDate) && !empty($customDate) && strtotime($customDate) && (strtotime($customDate) < time())) {
			$this->session->set_flashdata('error', 'You Can\'t Select Datetime In Past');
			redirect('user/all');
		}

		$customExpiryDate = [];
		$resellerSettings = resellersSettings();
		if (checkReseller() && $resellerSettings && ($resellerSettings->resl_allow_custom_expiry == 1)) {
			$customExpiryDate['customExpiryType'] = $customExpiryType;
			$customExpiryDate['customDate'] = $customDate;
		}
		else if (checkAdminOrStaff()) {
			$customExpiryDate['customExpiryType'] = $customExpiryType;
			$customExpiryDate['customDate'] = $customDate;
		}

		$currentRoleID = $this->session->user_id;

		if (!$this->main->getUserinfoByID($userID)) {
			$this->session->set_flashdata('error', 'No User Found.');
			redirect('user/all');
		}

		isUserAccessible($userID);
		$userData = $this->main->getUserinfoByID($userID)[0];
		$username = $userData->username;
		$discount = $userData->discount;
		$userSalePerson = $userData->saleperson;
		$preRenewExpireTime = NULL;
		$preRenewPool = NULL;
		$postRenewPool = NULL;
		$preRenewExpiredStatus = false;
		$packagePrice = 0;
		$franchisePrice = 0;
		$dealerPrice = 0;
		$subdealerPrice = 0;
		$adminProfit = 0;
		$franchiseProfit = 0;
		$dealerProfit = 0;
		$subdealerProfit = 0;
		if (!isset($userData->connectiontype) || empty($userData->connectiontype) || ($userData->connectiontype <= 0)) {
			$this->session->set_flashdata('error', 'No Connection Type Found.');
			redirect('user/all');
		}

		if (!$this->main->getJoinPackagesByPkgID($package)) {
			$this->session->set_flashdata('error', 'No Package Found.');
			redirect('user/all');
		}

		$fpackagePermission = $this->packagesM->profilePackagePermission($package, $userSalePerson);
		if (!isset($fpackagePermission) || !is_array($fpackagePermission) || !$fpackagePermission[0]) {
			$this->session->set_flashdata('error', $fpackagePermission[1]);
			redirect('user/all');
		}

		$packageData = $this->main->getJoinPackagesByPkgID($package);
		$packageID = $packageData[0]->id;
		$packageGroup = $packageData[0]->groupname;
		$packagePool = $packageData[0]->pool;
		$packageExpirePool = $packageData[0]->expirepool;
		$packageQuata = $packageData[0]->dataqt;
		$packageVolume = $packageData[0]->dataqtvol;
		$pkgVolLeftOver = $packageData[0]->left_over_volumes;
		$pkgSessionQt = $packageData[0]->sessionqt;
		$pkgSessionTime = $packageData[0]->sessiontime;
		$pkgSessionLeftOver = $packageData[0]->left_over_sessions;
		$billingSystem = $packageData[0]->billing_type;
		$packageVatType = $packageData[0]->vat_type;
		$packageVat = $packageData[0]->vat;
		$packageOtherFeeType = $packageData[0]->extra_fee_type;
		$packageOtherFee = $packageData[0]->extra_fee;
		$packageAutoPayment = $packageData[0]->autopayment;
		if (isset($expireTime) && !empty($expireTime)) {
			$expireTime = $expireTime;
		}
		else {
			$fixedExpireTimeStatus = $packageData[0]->fixed_expire_time_status;
			$fixedExpireTime = $packageData[0]->fixed_expire_time;
			if (isset($fixedExpireTimeStatus) && isset($fixedExpireTime) && ($fixedExpireTimeStatus == 1) && !empty($fixedExpireTime)) {
				$expireTime = $fixedExpireTime;
			}
		}

		$packageDurationType = $this->packagesM->packageDurationTypes($packageData[0]->duration_type);
		$expirationData = $this->expirationM->expireDate($userData, $packageData, $expireTime, $customExpiryDate);
		$currentExpiration = $expirationData['expiration']['currentExpiration'];
		$newExpirationDate = $expirationData['expiration']['newExpiration'];
		$salePersonData = $this->main->getAdminByAdminID($userSalePerson);

		if (!$salePersonData) {
			$this->session->set_flashdata('error', 'No Reseller Found, Plz, Assign A Reseller To User.');
			redirect('user/all');
		}

		$userResellerRole = $salePersonData[0]->role;
		$fixExCostCalType = 2;
		$packageAccounting = $this->accountingM->packageAccounting($userData, $packageData, $extraServiceFee, $expirationData, $fixExCostCalType, $customExpiryDate);
		if (!isset($packageAccounting) || !is_array($packageAccounting)) {
			$this->session->set_flashdata('error', 'Invalid Package Accounting.');
			redirect('user/all');
		}

		if (!$packageAccounting['status']) {
			$this->session->set_flashdata('error', $packageAccounting['error']);
			redirect('user/all');
		}

		$status = $packageAccounting['status'];
		$packagePrice = $packageAccounting['packagePrice'];
		$extraServiceFee = $packageAccounting['extraServiceFee'];
		$packageOtherFee = $packageAccounting['packageOtherFee'];
		$packageVat = $packageAccounting['packageVat'];
		$discount = $packageAccounting['discount'];
		$packageTotal = $packageAccounting['packageTotal'];
		$franchisePrice = $packageAccounting['franchisePrice'];
		$dealerPrice = $packageAccounting['dealerPrice'];
		$subdealerPrice = $packageAccounting['subdealerPrice'];
		$adminProfit = $packageAccounting['adminProfit'];
		$franchiseProfit = $packageAccounting['franchiseProfit'];
		$dealerProfit = $packageAccounting['dealerProfit'];
		$subdealerProfit = $packageAccounting['subdealerProfit'];
		$salePersonAd = $packageAccounting['salePersonAd'];
		$salePersonFr = $packageAccounting['salePersonFr'];
		$salePersonDr = $packageAccounting['salePersonDr'];
		$salePersonLedBal = $this->main->getLGBLByAdminID($userSalePerson);
		$userLedBal = $this->main->getLGBLByUserID($userID);
		if (($packageAutoPayment == 1) && ($userResellerRole != 1)) {
			if ($salePersonLedBal < $packageTotal) {
				$this->session->set_flashdata('error', 'Insufficient Salesperson Balance for Activation.');
				redirect('user/all');
			}
		}

		if ($billingSystem == 1) {
			if (($salePersonLedBal < $packageTotal) && ($userResellerRole != 1)) {
				$this->session->set_flashdata('error', 'Insufficient Salesperson Balance for Activation.');
				redirect('user/all');
			}
		}
		else if ($packageAutoPayment == 0) {
			if ($userLedBal < $packageTotal) {
				$this->session->set_flashdata('error', 'Insufficient User Balance for Activation.');
				redirect('user/all');
			}
		}
		else if (($salePersonLedBal < $packageTotal) && ($userResellerRole != 1)) {
			$this->session->set_flashdata('error', 'Insufficient Salesperson Balance for Activation.');
			redirect('user/all');
		}

		$this->activationM->checkActivationFrequency($userData);
		$activationResponse = $this->activationM->activation($userData, $packageData, $expirationData, $otherData, 1);
		$activationResSuccessMsg = $activationResponse['successMessages'];
		$activationResErrorMsg = $activationResponse['errorMessages'];
		if (isset($activationResErrorMsg) && is_array($activationResErrorMsg)) {
			for ($x = 0; $x < count($activationResErrorMsg); $x++) {
				array_push($errorMessages, $activationResErrorMsg[$x]);
			}
		}
		if (isset($activationResSuccessMsg) && is_array($activationResSuccessMsg)) {
			for ($x = 0; $x < count($activationResSuccessMsg); $x++) {
				array_push($successMessages, $activationResSuccessMsg[$x]);
			}
		}

		$this->accountingM->autoPayment($userData, $packageData, $packageAccounting, $userLedBal, $salePersonLedBal, $userResellerRole);
		$salePersonLedBal = $this->main->getLGBLByAdminID($userSalePerson);
		$userLedBal = $this->main->getLGBLByUserID($userID);
		$ledgerTrxID = $this->main->insertLedger($userID, -1 * $packageTotal, 5, 1, $userSalePerson, '', 1, true);
		$resellerLedgerRes = $this->accountingM->resellerLedgerCalculation($userData, $packageData, $packageAccounting, $userLedBal, $salePersonLedBal, $userResellerRole);
		$reseller_paid_amount = 0;
		if (isset($resellerLedgerRes['userRestBalance']) && (0 < $resellerLedgerRes['userRestBalance'])) {
			$reseller_paid_amount = $resellerLedgerRes['userRestBalance'];
		}
		else {
			$reseller_paid_amount = 0;
		}

		$this->main->insertInvoice(['id' => $userID, 'ispid' => $userData->ispid, 'balance' => $userLedBal], ['id' => $package], [
			'id'   => $userSalePerson,
			'role' => $userResellerRole,
			0      => ['packagePrice' => $packagePrice, 'adminProfit' => $adminProfit],
			1      => ['franchisePrice' => $franchisePrice, 'franchiseProfit' => $franchiseProfit],
			2      => ['franchisePrice' => $franchisePrice, 'franchiseProfit' => $franchiseProfit, 'dealerPrice' => $dealerPrice, 'dealerProfit' => $dealerProfit],
			3      => ['franchisePrice' => $franchisePrice, 'franchiseProfit' => $franchiseProfit, 'dealerPrice' => $dealerPrice, 'dealerProfit' => $dealerProfit, 'subdealerPrice' => $subdealerPrice, 'subdealerProfit' => $subdealerProfit]
		], ['date' => $newExpirationDate, 'lastExpiration' => $currentExpiration, 'newExpiration' => $newExpirationDate, 'note' => '', 'header_note' => '', 'footer_note' => '', 'ledger_trx_id' => $ledgerTrxID], ['discount' => $discount, 'vat' => $packageOtherFee + $packageVat, 'amount' => $packageTotal, 'reseller_paid_amount' => $reseller_paid_amount], [
			'level' => [$cost1level, $cost2level, $cost3level, $cost4level, $cost5level],
			'value' => [$cost1, $cost2, $cost3, $cost4, $cost5]
		]);
		$usersinfoData['last_expiration_date'] = date('Y-m-d H:i:s', strtotime($currentExpiration));
		$usersinfoData['activation_by'] = $currentRoleID;
		if (isset($userData->activation_date) && !empty($userData->activation_date)) {
			$usersinfoData['activation_date'] = date('Y-m-d H:i:s');
		}
		else {
			$usersinfoData['renew_date'] = date('Y-m-d H:i:s');
		}

		$packageDataQuota = $this->activationM->getPackageDataQuota($packageData, $userData);
		$usersinfoData['is_enabled'] = $packageDataQuota['isVolQtEnabled'];
		$usersinfoData['qt_total'] = $packageDataQuota['volQtTotal'];
		$packageSessionQuota = $this->activationM->getPackageSessionQuota($packageData, $userData);
		$usersinfoData['qt_total_session'] = $packageSessionQuota['sessionQtTotal'];
		$usersinfoData['connectionstatus'] = 1;
		$usersinfoData['status'] = 2;
		$usersinfoData['package'] = $packageID;
		$usersinfoData['c_package'] = $packageID;
		$usersinfoData['is_qt_expired'] = 0;
		$usersinfoData['qt_used'] = 0;
		$usersinfoData['qt_session'] = 0;
		$updateUsersinfoRes = $this->main->singleUpdate('usersinfo', $usersinfoData, ['username' => $userData->username]);
		$this->main->insertActivity('User Activate', $userID);

		if (if_SMSEnable()) {
			if (chkSMSAlert(17) && getSMSAlert(17)) {
				if (getUserSMSStatus($userID) && getUserSalespersonSms($userID, $userData->saleperson)) {
					$mobile = $userData->mobile;
					$smsText = getSMSAlert(17)->template;
					$smsText = getFinalSMSText($smsText, 2, $userID);
					$smsRes = sendSMS($mobile, $smsText);
					$smsData = ['smsAlert' => 17, 'destination' => $mobile, 'message' => $smsText, 'userID' => $userID, 'adminID' => 0];
					$responseSmsDelivery = $this->smsM->insertDelivery($smsRes, $smsData);
				}
			}
		}

		$this->session->set_flashdata('successMessages', $successMessages);
		$this->session->set_flashdata('errorMessages', $errorMessages);

		if ($updateUsersinfoRes) {
			$this->session->set_flashdata('success', 'User Successfully Activated');
		}
		else {
			$this->session->set_flashdata('error', 'Oops! Something Wrong');
		}

		$source_page = $this->input->post('source_page');

		if ($source_page == 'user_profile') {
			redirect('user/profile/' . $userID);
		}
		else {
			redirect('user/all');
		}
	}
}

?>