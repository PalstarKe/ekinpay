<?php
namespace App\Http\Controllers;
defined('BASEPATH') || exit('No direct script access allowed');
use Illuminate\Http\Request;

class Expireduser extends Controller
{
	public $settings;

	public function __construct()
	{
		parent::__construct();
		$this->settings = settings()[0];
	}

	public function index()
	{
		$this->userExpired();
		$this->fixWrongPool();
	}

	public function userExpired()
	{
		if ($this->settings->auto_clear_log == 1) {
			$this->main->singleInsert('log', ['data' => 'Cron Job', 'msg' => 'Cron Job Expired User SMS', 'datetime' => date('Y-m-d H:i:s')]);
		}

		$sqlQuery = 'SELECT usersinfo.id, usersinfo.username, usersinfo.mobile, usersinfo.phone, usersinfo.status, usersinfo.saleperson, usersinfo.smsstatus, usersinfo.package, radcheck.value as expiration FROM radcheck RIGHT JOIN usersinfo ON radcheck.username = usersinfo.username WHERE radcheck.attribute=\'Expiration\' AND STR_TO_DATE(radcheck.value, \'%d %b %Y %H:%i:%s\') < NOW() AND usersinfo.status = 2';
		$expiredUsersObj = $this->db->query($sqlQuery);

		if (0 < $expiredUsersObj->num_rows()) {
			foreach ($expiredUsersObj->result() as $row) {
				$userID = $row->id;
				$username = $row->username;
				$mobile = $row->mobile;
				$expiration = $row->expiration;
				if (isset($expiration) && !empty($expiration)) {
					if (strtotime($expiration) < strtotime(date('Y-m-d H:i:s'))) {
						$packageData = $this->main->getGMPackagesByID($row->package);

						if ($packageData) {
							$packageExpirePool = $packageData[0]->expirepool;
							if (isset($expiration) && isset($packageExpirePool) && !empty($packageExpirePool) && !empty($expiration)) {
								$dataRadReply['username'] = $row->username;
								$dataRadReply['attribute'] = 'Framed-Pool';
								$dataRadReply['op'] = ':=';
								$dataRadReply['value'] = $packageExpirePool;
								$radReplyObj = $this->main->singleQuery('radreply', ['username' => $row->username, 'attribute' => 'Framed-Pool']);

								if ($radReplyObj) {
									$this->main->singleUpdate('radreply', $dataRadReply, ['username' => $row->username]);
								}
								else {
									$this->main->singleInsert('radreply', $dataRadReply);
								}
							}
						}
					}

					$todayDate = strtotime(date('Y-m-d', strtotime('today')));
					$yesterdayDate = strtotime(date('Y-m-d', strtotime('yesterday')));
					$userExpireDate = strtotime(date('Y-m-d', strtotime($expiration)));
					if (($yesterdayDate <= $userExpireDate) && ($userExpireDate <= $todayDate)) {
						if (if_SMSEnable()) {
							$smsAlertData = getSMSAlert(16);
							if (chkSMSAlert(16) && $smsAlertData) {
								if (getUserSMSStatus($userID) && getUserSalespersonSms($userID) && ($row->status == 2)) {
									$smsDeliveredObj = $this->main->getSMSDelivered($userID, 16);

									if (!$smsDeliveredObj) {
										$smsText = $smsAlertData->template;
										$smsText = getFinalSMSText($smsText, 2, $userID);
										$smsRes = sendSMS($mobile, $smsText);
										$smsData = ['smsAlert' => 16, 'destination' => $mobile, 'message' => $smsText, 'userID' => $userID, 'adminID' => $row->saleperson];
										$responseSmsDelivery = $this->smsM->insertDelivery($smsRes, $smsData);
									}
								}
							}
						}
					}
				}
			}
		}
	}

	public function fixWrongPool()
	{
		if ($this->settings->auto_clear_log == 1) {
			$this->main->singleInsert('log', ['data' => 'Cron Job', 'msg' => 'Cron Job Fixing Wrong Pool Auto', 'datetime' => date('Y-m-d H:i:s')]);
		}

		$sqlQuery = 'SELECT usersinfo.id as user_id, radreply.username, radreply.value as pool_name, radcheck.value as expiration, packages.name as package_name, packages.id as package_id, usersinfo.package as user_package, packages.pool as package_pool, packages.expirepool as package_expire_pool FROM radcheck RIGHT JOIN radreply ON radcheck.username = radreply.username JOIN usersinfo ON usersinfo.username = radreply.username JOIN packages ON packages.id = usersinfo.package WHERE radcheck.attribute = \'Expiration\' AND radreply.attribute = \'Framed-Pool\' AND radcheck.attribute=\'Expiration\' AND STR_TO_DATE(radcheck.value, \'%d %b %Y %H:%i:%s\') >= NOW() AND radreply.value = packages.expirepool';
		$wrongPoolObj = $this->db->query($sqlQuery);

		if (0 < $wrongPoolObj->num_rows()) {
			foreach ($wrongPoolObj->result() as $row) {
				$username = $row->username;
				$this->db->reset_query();
				$radReplyDeleteRes = $this->main->singleDelete('radreply', ['username' => $row->username, 'attribute' => 'Framed-Pool']);
				$this->db->reset_query();
				$dataRadReply['username'] = $row->username;
				$dataRadReply['attribute'] = 'Framed-Pool';
				$dataRadReply['op'] = ':=';
				$dataRadReply['value'] = $row->package_pool;
				$radReplyInsertRes = $this->main->singleInsert('radreply', $dataRadReply);
				$this->db->reset_query();
				$radacctData = getRadacctByUsername($username);
				if ((is_object($radacctData) || is_array($radacctData)) && $radacctData) {
					if ($this->settings->disconnecttype != 0) {
						try {
							$disconnectRes = $this->radiusM->kickOutUsers($username);
						}
						catch (Exception $e) {
						}
					}
				}
			}
		}
	}

	public function fixPolicy()
	{
		if ($this->settings->auto_clear_log == 1) {
			$this->main->singleInsert('log', ['data' => 'Cron Job', 'msg' => 'Cron Job Fixing Policy Auto', 'datetime' => date('Y-m-d H:i:s')]);
		}

		$sqlQuery = 'DELETE FROM radusergroup WHERE id NOT IN ( SELECT * FROM (SELECT MAX(id) FROM radusergroup GROUP BY username) AS subquery)';
		$policyObj = $this->db->query($sqlQuery);
	}
}

?>