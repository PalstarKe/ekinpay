<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

defined('BASEPATH') || exit('No direct script access allowed');

if (!function_exists('sendSMS')) {
	function sendSMS($destination, $message)
	{
		$ci = loadingInstance();

		if (settings()[0]->smsgateway == 1) {
			$account_sid = settings()[0]->twilio_sid;
			$auth_token = settings()[0]->twilio_token;
			$twilio_number = settings()[0]->twilio_sender;
			$message = str_replace('{phone}', $destination, $message);
			$message = str_replace('{message}', $message, $message);

			try {
				$client = new Twilio\Rest\Client($account_sid, $auth_token);
				$messageObj = $client->messages->create($destination, ['from' => $twilio_number, 'body' => strip_tags($message)]);
				if (isset($messageObj->status) && !empty($messageObj->status)) {
					return ['status' => true, 'message' => $messageObj->status];
				}

				return ['status' => false, 'message' => $messageObj->error_code . '|' . $messageObj->error_message];
			}
			catch (Exception $e) {
				return ['status' => false, 'message' => 'Failed | Unknown'];
			}
		}
		else if (settings()[0]->smsgateway == 2) {
			try {
				$client = new Nexmo\Client(new Nexmo\Client\Credentials\Basic(settings()[0]->nexmo_api, settings()[0]->nexmo_secret));
				$messageObj = $client->message()->send(['to' => $destination, 'from' => settings()[0]->nexmo_from, 'text' => strip_tags($message)]);
				if (isset($messageObj) && !empty($messageObj)) {
					if ($message->getStatus() == 0) {
						return ['status' => true, 'message' => 'Successfully Send'];
					}

					return ['status' => false, 'message' => $message->getStatus()];
				}

				return ['status' => false, 'message' => 'Failed | Unknown'];
			}
			catch (Exception $e) {
				return ['status' => false, 'message' => 'Failed | Unknown'];
			}
		}
		else if (settings()[0]->smsgateway == 12) {
			if (strpos($message, '{template:') !== false) {
				$tempArrMSG = explode('{template:', $message);
				if (isset($tempArrMSG) && is_array($tempArrMSG) && array_key_exists(1, $tempArrMSG)) {
					$tempArrMSG = explode('}', $tempArrMSG[1]);
					if (isset($tempArrMSG) && is_array($tempArrMSG) && array_key_exists(0, $tempArrMSG)) {
						$tempArrMSG = str_replace('{', '', $tempArrMSG[0]);
						$tempArrMSG = str_replace('}', '', $tempArrMSG);
						$tempArrMSG = str_replace(':', '', $tempArrMSG);
						$templateID = str_replace('template', '', $tempArrMSG);
					}
				}

				$message = str_replace('{template:' . $templateID . '}', '', $message);
			}

			$apiUrl = settings()[0]->smsurl;
			$apiUrl = str_replace('{phone}', $destination, $apiUrl);
			$apiUrl = str_replace('{message}', $message, $apiUrl);
			if (isset($templateID) && !empty($templateID)) {
				$apiUrl = str_replace('{template}', $templateID, $apiUrl);
			}

			try {
				$client = new GuzzleHttp\Client();
				$response = $client->request('GET', $apiUrl, ['connect_timeout' => 30, 'timeout' => 30]);
				$responseString = $response->getBody()->getContents();
				if (isset($responseString) && !empty($responseString)) {
					return ['status' => true, 'message' => $responseString];
				}
				else {
					return ['status' => false, 'message' => $responseString];
				}
			}
			catch (GuzzleHttp\Exception\TransferException $e) {
				return ['status' => false, 'message' => $e->getMessage()];
			}
		}
		else {
			return ['status' => false, 'message' => 'API Not Found | Failed | Unknown'];
		}
	}
}

if (!function_exists('if_SMSEnable')) {
	function if_SMSEnable()
	{
		$ci = loadingInstance();

		if (settings()[0]->smsstatus == 1) {
			return true;
		}

		return false;
	}
}

if (!function_exists('chkSMSAlert')) {
	function chkSMSAlert($id)
	{
		$ci = loadingInstance();
		$query = $ci->db->get_where('smsalerts', ['smsid' => $id]);

		if (0 < $query->num_rows()) {
			if ($query->result()[0]->status == 1) {
				return true;
			}
		}

		return false;
	}
}

if (!function_exists('getSMSAlert')) {
	function getSMSAlert($id)
	{
		$ci = loadingInstance();
		$query = $ci->db->get_where('smsalerts', ['smsid' => $id]);

		if (0 < $query->num_rows()) {
			return $query->result()[0];
		}

		return false;
	}
}

if (!function_exists('getFinalSMSText')) {
	function getFinalSMSText($smsText, $type, $userID)
	{
		$ci = loadingInstance();
		$expirytime = '';

		if ($type == 1) {
			$query = $ci->db->get_where('admin', ['adminid' => $userID]);
		}
		else if ($type == 2) {
			$query = $ci->db->get_where('usersinfo', ['id' => $userID]);
		}
		else {
			return false;
		}
		if (isset($query) && (0 < $query->num_rows())) {
			$queryData = $query->result()[0];
			$username = $queryData->username;
			$phone = $queryData->phone;
			$name = $queryData->name;

			if ($type == 1) {
				$id = $queryData->adminid;
				$currentBalance = number_format(getLBByAdminID($userID), 2);
			}
			else if ($type == 2) {
				$id = $queryData->id;

				if (getRadcheckByUsername($username)) {
					$expirytime = getRadcheckByUsername($username)->value;
					if (isset($expirytime) && !empty($expirytime)) {
						$expirytime = date('Y-m-d H:i:s', strtotime($expirytime));
					}
					else {
						$expirytime = 'N/A';
					}
				}
				else {
					$expirytime = 'N/A';
				}

				$currentBalance = number_format(getLGBLByUserID($userID), 2);
			}

			$smsText = str_replace('{id}', $id, $smsText);
			$smsText = str_replace('{username}', $username, $smsText);
			$smsText = str_replace('{name}', $name, $smsText);
			$smsText = str_replace('{phone}', $phone, $smsText);
			$smsText = str_replace('{datetime}', date('Y-m-d H:i:s'), $smsText);
			$smsText = str_replace('{company}', settings()[0]->name, $smsText);
			$smsText = str_replace('{slogan}', settings()[0]->slogan, $smsText);
			$smsText = str_replace('{expirytime}', $expirytime, $smsText);
			$smsText = str_replace('{balance}', $currentBalance, $smsText);
			$smsText = str_replace('{currency}', settings()[0]->currency, $smsText);
			return $smsText;
		}
		else {
			return false;
		}
	}
}

if (!function_exists('getSmsText')) {
	function getSmsText($type)
	{
		$ci = loadingInstance();
		$query = $ci->db->get_where('smstext', ['type' => $type]);

		if (0 < $query->num_rows()) {
			return $query->result()[0]->smstxt;
		}

		return false;
	}
}

if (!function_exists('getUserSMSStatus')) {
	function getUserSMSStatus($userID)
	{
		$ci = loadingInstance();
		$query = $ci->db->get_where('usersinfo', ['id' => $userID]);

		if (0 < $query->num_rows()) {
			if ($query->result()[0]->smsstatus == 1) {
				return true;
			}
		}

		return false;
	}
}

if (!function_exists('getUserSalespersonSms')) {
	function getUserSalespersonSms($userID, $salespersonID = NULL)
	{
		$ci = loadingInstance();
		if ((isset($userID) && !empty($userID)) || (isset($salespersonID) && !empty($salespersonID))) {
			if (isset($salespersonID) && !empty($salespersonID)) {
				$salespersonQuery = $ci->db->get_where('admin', ['adminid' => $salespersonID]);
				$ci->db->reset_query();

				if (0 < $salespersonQuery->num_rows()) {
					$salespersonRole = $salespersonQuery->result()[0]->role;
					$salespersonSmsStatus = $salespersonQuery->result()[0]->resl_user_sms_permission;
					if (($salespersonRole == 11) || ($salespersonRole == 12) || ($salespersonRole == 13)) {
						if (isset($salespersonSmsStatus) && ($salespersonSmsStatus == 1)) {
							return true;
						}
					}
					else {
						return true;
					}
				}
			}
			else {
				$userQuery = $ci->db->get_where('usersinfo', ['id' => $userID]);
				$ci->db->reset_query();

				if (0 < $userQuery->num_rows()) {
					$salespersonID = $userQuery->result()[0]->saleperson;
					if (isset($salespersonID) && !empty($salespersonID)) {
						$salespersonQuery = $ci->db->get_where('admin', ['adminid' => $salespersonID]);
						$ci->db->reset_query();

						if (0 < $salespersonQuery->num_rows()) {
							$salespersonRole = $salespersonQuery->result()[0]->role;
							$salespersonSmsStatus = $salespersonQuery->result()[0]->resl_user_sms_permission;
							if (($salespersonRole == 11) || ($salespersonRole == 12) || ($salespersonRole == 13)) {
								if (isset($salespersonSmsStatus) && ($salespersonSmsStatus == 1)) {
									return true;
								}
							}
							else {
								return true;
							}
						}
					}
				}
			}
		}

		return false;
	}
}
