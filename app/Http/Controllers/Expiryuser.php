<?php
namespace App\Http\Controllers;
defined('BASEPATH') || exit('No direct script access allowed');
use Illuminate\Http\Request;

class Expiryuser extends Controller
{
	public $settings;

	public function __construct()
	{
		parent::__construct();
		$this->settings = settings()[0];
	}

	public function index()
	{
		$this->checkUserExpiry();
	}

	public function checkUserExpiry()
	{
		if ($this->settings->auto_clear_log == 1) {
			$this->main->singleInsert('log', ['data' => 'Cron Job', 'msg' => 'Cron Job Expiry User SMS', 'datetime' => date('Y-m-d H:i:s')]);
		}

		$response = $this->daysSMSNotification();
	}

	public function daysSMSNotification()
	{
		$sendUserIds = [];
		$days = 7;
		$expiryUserResult = $this->smsNotificationQuery($days);

		if ($expiryUserResult) {
			foreach ($expiryUserResult as $row) {
				$userID = $row->id;
				$mobile = $row->mobile;
				$salespersonID = $row->saleperson;
				$expirationDate = $row->expiration;
				$userPackageID = $row->package;
				if (isset($mobile) && !empty($mobile)) {
					if (isset($expirationDate) && !empty($expirationDate) && strtotime($expirationDate)) {
						$expirationDate = strtotime(date('Y-m-d', strtotime($expirationDate)));
						$expiryAfter1Days = strtotime(date('Y-m-d 23:59:59', strtotime('+ 1 days')));
						$expiryAfter3Days = strtotime(date('Y-m-d 23:59:59', strtotime('+ 3 days')));
						$expiryAfter7Days = strtotime(date('Y-m-d 23:59:59', strtotime('+ 7 days')));
						$today = strtotime(date('Y-m-d'));
						if (($today < $expirationDate) && ($expirationDate <= $expiryAfter1Days)) {
							$alertID = 19;
							$smsDeliveryObj = $this->main->getSMSDelivered($userID, 19);
						}
						else if (($today < $expirationDate) && ($expirationDate <= $expiryAfter3Days)) {
							$alertID = 18;
							$smsDeliveryObj = $this->main->getSMSDelivered($userID, 18);
						}
						else if (($today < $expirationDate) && ($expirationDate <= $expiryAfter7Days)) {
							$alertID = 15;
							$smsDeliveryObj = $this->main->getSMSDelivered($userID, 15);
						}

						$packageData = $this->main->getGMPackagesByID($userPackageID);
						if ($packageData && is_array($packageData) && ($packageData[0]->autorenew == 1)) {
							$userData = $this->main->getUserinfoByID($userID)[0];
							$userBalance = $this->main->getLGBLByUserID($userID);
							$packageData = $this->main->getGMPackagesByID($userPackageID);
							$expirationData = $this->expirationM->expireDate($userData, $packageData, NULL, NULL);
							$packageAccounting = $this->accountingM->packageAccounting($userData, $packageData, NULL, $expirationData, NULL, NULL);
							if (isset($packageAccounting) && is_array($packageAccounting) && $packageAccounting['status']) {
								$packageTotal = $packageAccounting['packageTotal'];

								if (round($packageTotal) < round($userBalance)) {
									$smsDeliveryObj = true;
								}
							}
						}
						if (isset($smsDeliveryObj) && isset($alertID) && !$smsDeliveryObj) {
							$smsResonse = $this->sendExpirySMS($alertID, $userID, $salespersonID, $mobile);
							if (isset($smsResonse) && $smsResonse) {
								array_push($sendUserIds, $userID);
							}
						}
					}
				}
			}

			return $sendUserIds;
		}

		return false;
	}

	public function smsNotificationQuery($days)
	{
		$sqlQuery = 'SELECT usersinfo.id, usersinfo.username, usersinfo.mobile, usersinfo.phone, usersinfo.status, usersinfo.saleperson, usersinfo.package, usersinfo.smsstatus, radcheck.value as expiration FROM radcheck RIGHT JOIN usersinfo ON radcheck.username = usersinfo.username WHERE radcheck.attribute=\'Expiration\' AND usersinfo.status = 2 AND usersinfo.smsstatus = 1 AND DATE_SUB(STR_TO_DATE(radcheck.value, \'%d %b %Y\'), INTERVAL ' . $days . ' DAY) <= DATE_FORMAT(NOW(),\'%Y-%m-%d\') AND STR_TO_DATE(radcheck.value, \'%d %b %Y %H:%i:%s\') > NOW()';
		$query = $this->db->query($sqlQuery);

		if (0 < $query->num_rows()) {
			return $query->result();
		}

		return false;
	}

	public function sendExpirySMS(int $alertID, int $userID, int $salespersonID, $mobile)
	{
		if (if_SMSEnable()) {
			$smsAlertData = getSMSAlert($alertID);
			if (chkSMSAlert($alertID) && $smsAlertData) {
				$smsDeliveredObj = $this->main->getSMSDelivered($userID, $alertID);

				if (!$smsDeliveredObj) {
					if (getUserSMSStatus($userID) && getUserSalespersonSms($userID)) {
						$smsText = $smsAlertData->template;
						$smsText = getFinalSMSText($smsText, 2, $userID);
						$smsRes = sendSMS($mobile, $smsText);
						$smsData = ['smsAlert' => $alertID, 'destination' => $mobile, 'message' => $smsText, 'userID' => $userID, 'adminID' => $salespersonID];
						$responseSmsDelivery = $this->smsM->insertDelivery($smsRes, $smsData);
						return $smsRes['status'];
					}
				}
			}
		}

		return false;
	}
}

?>