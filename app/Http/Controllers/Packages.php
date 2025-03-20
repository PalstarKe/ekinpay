<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Packages extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		isKena();
		isLogin();
		ifClient();
		isPermitted('package_module', [11, 12, 13], false);
		$langFiles = ['global/global', 'packages/packages'];
		translation($langFiles);
	}

	public function index()
	{
		$this->all();
	}

	public function all()
	{
		isPermitted('package_all_module');
		$data['packages'] = $this->main->getJoinAdminPackages();
		$data['groups'] = $this->main->getRadgroupreply();
		$this->load->view('themes/legacy/admin_portal/dashboard/header');
		$this->load->view('themes/legacy/admin_portal/packages/all', $data);
		$this->load->view('themes/legacy/admin_portal/dashboard/footer');
	}

	public function insert()
	{
		isPermitted('package_add_new');
		$groupname = $this->input->post('group', true);
		$groupCheckObj = $this->main->singleQuery('radgroupcheck', ['groupname' => $groupname]);
		$groupReplyObj = $this->main->singleQuery('radgroupreply', ['groupname' => $groupname]);
		if (!$groupReplyObj && !$groupCheckObj) {
			$this->session->set_flashdata('error', 'Package Bandwidth Policy Not Found, Please Select Another Policy.');
			redirect('package/all');
		}

		$this->db->reset_query();
		$adminID = $this->session->user_id;
		$adminData = $this->main->getAdminByAdminID($adminID);

		if ($adminData) {
			if (($adminData[0]->role != 1) && ($adminData[0]->adminid != 2)) {
				$this->session->set_flashdata('error', 'You Can\'t Add Package As You Are Not Primary Admin Of The System.');
				redirect('package/all');
			}
		}

		$this->db->reset_query();
		$data = [];
		$data['usertype'] = $this->input->post('usertype', true);
		$data['groupname'] = $this->input->post('group', true);
		$data['name'] = $this->input->post('name', true);
		$data['description'] = $this->input->post('description', true);
		$data['invoice_description'] = $this->input->post('invoice_description', true);
		$bandwidth = $this->input->post('bandwidth', true);
		$bandwidthType = $this->input->post('bandwidth_type', true);
		if (isset($bandwidth) && !empty($bandwidth)) {
			$data['bandwidth'] = (int) $bandwidth;
		}
		if (isset($bandwidthType) && !empty($bandwidthType)) {
			$data['bandwidth_type'] = (int) $bandwidthType;
		}

		$extraFeeType = $this->input->post('extra_fee_type', true);
		$extraFee = $this->input->post('extra_fee', true);
		if (isset($extraFeeType) && !empty($extraFeeType)) {
			$data['extra_fee_type'] = (int) $extraFeeType;
		}
		if (isset($extraFee) && !empty($extraFee)) {
			$data['extra_fee'] = (float) $extraFee;
		}

		$vatType = $this->input->post('vat_type', true);
		$vat = $this->input->post('vat', true);
		if (isset($vatType) && !empty($vatType)) {
			$data['vat_type'] = (int) $vatType;
		}
		if (isset($vat) && !empty($vat)) {
			$data['vat'] = (float) $vat;
		}

		$data['billing_type'] = $this->input->post('billing_type', true);
		$data['duration_type'] = $this->input->post('duration_type', true);
		$data['duration'] = $this->input->post('duration', true);
		$data['pool'] = $this->input->post('pool', true);
		$data['expirepool'] = $this->input->post('expirepool', true);
		$data['fixed_expire_day_status'] = ($this->input->post('fixed_expire_day_status', true) == 'on' ? 1 : 0);
		$data['fixed_expire_day_accounting_status'] = ($this->input->post('fixed_expire_day_accounting_status', true) == 'on' ? 1 : 0);
		$data['fixed_expire_day_accounting_type'] = $this->input->post('fixed_expire_day_accounting_type', true);
		$fixedExpireDay = $this->input->post('fixed_expire_day', true);
		if (isset($fixedExpireDay) && !empty($fixedExpireDay)) {
			$data['fixed_expire_day'] = $fixedExpireDay;
		}

		$data['fixed_expire_time_status'] = ($this->input->post('fixed_expire_time_status', true) == 'on' ? 1 : 0);
		$data['fixed_expire_time'] = $this->input->post('fixed_expire_time', true);
		$data['autopayment'] = ($this->input->post('autopayment', true) == 'on' ? 1 : 0);
		$data['leftoverdays'] = ($this->input->post('leftoverdays', true) == 'on' ? 1 : 0);
		$data['left_over_volumes'] = ($this->input->post('left_over_volumes', true) == 'on' ? 1 : 0);
		$data['left_over_sessions'] = ($this->input->post('left_over_sessions', true) == 'on' ? 1 : 0);
		$data['autorenew'] = ($this->input->post('autorenew', true) == 'on' ? 1 : 0);
		$data['user_self_activation'] = ($this->input->post('user_self_activation', true) == 'on' ? 1 : 0);
		$data['dataqt'] = ($this->input->post('dataqt', true) == 'on' ? 1 : 0);

		if (!!$this->input->post('dataqtvol', true)) {
			$dataqtvol = preg_replace('/[^0-9.]+/', '', $this->input->post('dataqtvol', true));
			$data['dataqtvol'] = (int) ($dataqtvol * 1024 * 1024) * 1024;
		}
		else {
			$data['dataqtvol'] = 0;
		}

		$data['fupqt'] = ($this->input->post('fupqt', true) == 'on' ? 1 : 0);

		if (!!$this->input->post('fupqtvol', true)) {
			$dataqtvol = preg_replace('/[^0-9.]+/', '', $this->input->post('dataqtvol', true));
			$data['fupqtvol'] = (int) ($this->input->post('fupqtvol', true) * 1024 * 1024) * 1024;
		}
		else {
			$data['fupqtvol'] = 0;
		}

		$data['data_quota_exceed_status'] = ($this->input->post('data_quota_exceed_status', true) == 'on' ? 1 : 0);
		$data['data_quota_exceed_type'] = $this->input->post('data_quota_exceed_type', true);
		$data['session_quota_exceed_status'] = ($this->input->post('session_quota_exceed_status', true) == 'on' ? 1 : 0);
		$data['session_quota_exceed_type'] = $this->input->post('session_quota_exceed_type', true);
		$data['session_fup_limit_status'] = ($this->input->post('session_fup_limit_status', true) == 'on' ? 1 : 0);
		$data['session_fup_bw_limit'] = $this->input->post('session_fup_bw_limit', true);
		$data['fupqtbwlimit'] = $this->input->post('fupqtbwlimit', true);
		$data['sessionqt'] = ($this->input->post('sessionqt', true) == 'on' ? 1 : 0);
		$sessionTime = $this->input->post('sessiontime', true);
		if (isset($sessionTime) && !empty($sessionTime)) {
			$data['sessiontime'] = $sessionTime;
		}

		$data['dynamicbw'] = ($this->input->post('dynamicbw', true) == 'on' ? 1 : 0);
		$data['apply_users'] = ($this->input->post('apply_users', true) == 'on' ? 1 : 0);
		$data['apply_resellers'] = ($this->input->post('apply_resellers', true) == 'on' ? 1 : 0);

		if ($this->input->post('dynamicbw', true) == 1) {
			$data['dbstarttime1'] = $this->input->post('dbstarttime1', true);
			$data['dbendtime1'] = $this->input->post('dbendtime1', true);
			$data['dblimit1'] = $this->input->post('dblimit1', true);
			$data['dbstarttime2'] = $this->input->post('dbstarttime2', true);
			$data['dbendtime2'] = $this->input->post('dbendtime2', true);
			$data['dblimit2'] = $this->input->post('dblimit2', true);
			$data['dbstarttime3'] = $this->input->post('dbstarttime3', true);
			$data['dbendtime3'] = $this->input->post('dbendtime3', true);
			$data['dblimit3'] = $this->input->post('dblimit3', true);
			$data['dbstarttime4'] = $this->input->post('dbstarttime4', true);
			$data['dbendtime4'] = $this->input->post('dbendtime4', true);
			$data['dblimit4'] = $this->input->post('dblimit4', true);
			$data['dbstarttime5'] = $this->input->post('dbstarttime5', true);
			$data['dbendtime5'] = $this->input->post('dbendtime5', true);
			$data['dblimit5'] = $this->input->post('dblimit5', true);
			$data['dbstarttime6'] = $this->input->post('dbstarttime6', true);
			$data['dbendtime6'] = $this->input->post('dbendtime6', true);
			$data['dblimit6'] = $this->input->post('dblimit6', true);
			$data['dbstarttime7'] = $this->input->post('dbstarttime7', true);
			$data['dbendtime7'] = $this->input->post('dbendtime7', true);
			$data['dblimit7'] = $this->input->post('dblimit7', true);
			$data['dbstarttime8'] = $this->input->post('dbstarttime8', true);
			$data['dbendtime8'] = $this->input->post('dbendtime8', true);
			$data['dblimit8'] = $this->input->post('dblimit8', true);
			$data['dbstarttime9'] = $this->input->post('dbstarttime9', true);
			$data['dbendtime9'] = $this->input->post('dbendtime9', true);
			$data['dblimit9'] = $this->input->post('dblimit9', true);
			$data['dbstarttime10'] = $this->input->post('dbstarttime10', true);
			$data['dbendtime10'] = $this->input->post('dbendtime10', true);
			$data['dblimit10'] = $this->input->post('dblimit10', true);
			$data['dbstarttime11'] = $this->input->post('dbstarttime11', true);
			$data['dbendtime11'] = $this->input->post('dbendtime11', true);
			$data['dblimit11'] = $this->input->post('dblimit11', true);
			$data['dbstarttime12'] = $this->input->post('dbstarttime12', true);
			$data['dbendtime12'] = $this->input->post('dbendtime12', true);
			$data['dblimit12'] = $this->input->post('dblimit12', true);
		}

		$packageInsertRes = $this->db->insert('packages', $data);
		$latestPackageID = $this->db->insert_id();
		$this->db->reset_query();
		$accountingData['adminid'] = $this->session->user_id;
		$accountingData['fadminid'] = 0;
		$accountingData['pkgid'] = $latestPackageID;
		$accountingData['price'] = $this->input->post('price', true);
		$accountingData['aprofit'] = $this->input->post('aprofit', true);
		$accountingData['fprofit'] = 0;
		$accountingData['dprofit'] = 0;
		$accountingData['sprofit'] = 0;
		$extraFeeType = $this->input->post('extra_fee_type', true);
		$extraFee = $this->input->post('extra_fee', true);
		if (isset($extraFeeType) && !empty($extraFeeType)) {
			$accountingData['extra_fee_type'] = (int) $extraFeeType;
		}
		if (isset($extraFee) && !empty($extraFee)) {
			$accountingData['extra_fee'] = (float) $extraFee;
		}

		$vatType = $this->input->post('vat_type', true);
		$vat = $this->input->post('vat', true);
		if (isset($vatType) && !empty($vatType)) {
			$accountingData['vat_type'] = (int) $vatType;
		}
		if (isset($vat) && !empty($vat)) {
			$accountingData['vat'] = (float) $vat;
		}

		$accPackageInsertRes = $this->db->insert('f_packages', $accountingData);
		$this->main->insertActivity('New Package Insert');
		if ($packageInsertRes && $accPackageInsertRes) {
			$this->session->set_flashdata('success', 'Packages Successfully Created');
			redirect('package/all');
		}
		else {
			$this->session->set_flashdata('error', 'Oops! Something Wrong');
			redirect('package/all');
		}
	}

	public function edit($id)
	{
		isPermitted('package_edit');
		$packageQuery = $this->main->singleQuery('packages', ['id' => $id]);

		if (!$packageQuery) {
			$this->session->set_flashdata('error', 'Oops! Package Not Found');
			redirect('package/all');
		}

		$data['packages'] = $this->main->getJoinPackagesByID($id);
		$data['groups'] = $this->main->getRadgroupreply();
		$this->load->view('themes/legacy/admin_portal/dashboard/header');
		$this->load->view('themes/legacy/admin_portal/packages/edit', $data);
		$this->load->view('themes/legacy/admin_portal/dashboard/footer');
	}

	public function update()
	{
		isPermitted('package_edit');
		$data = [];
		$id = $this->input->post('id', true);
		$packageQuery = $this->main->singleQuery('packages', ['id' => $id]);

		if (!$packageQuery) {
			$this->session->set_flashdata('error', 'Oops! Package Not Found');
			redirect('package/all');
		}

		$groupname = $this->input->post('group', true);
		$groupCheckObj = $this->main->singleQuery('radgroupcheck', ['groupname' => $groupname]);
		$groupReplyObj = $this->main->singleQuery('radgroupreply', ['groupname' => $groupname]);
		if (!$groupReplyObj && !$groupCheckObj) {
			$this->session->set_flashdata('error', 'Oops! Policy Not Found');
			redirect('package/all');
		}

		$packageData = $this->main->getGMPackagesByID($id);

		if ($packageData) {
			$packagePool = $packageData[0]->pool;
			$packageExpirePool = $packageData[0]->expirepool;
		}

		$data['usertype'] = $this->input->post('usertype', true);
		$data['groupname'] = $this->input->post('group', true);
		$data['name'] = $this->input->post('name', true);
		$data['description'] = $this->input->post('description', true);
		$data['invoice_description'] = $this->input->post('invoice_description', true);
		$bandwidth = $this->input->post('bandwidth', true);
		$bandwidthType = $this->input->post('bandwidth_type', true);
		if (isset($bandwidth) && !empty($bandwidth)) {
			$data['bandwidth'] = (int) $bandwidth;
		}
		if (isset($bandwidthType) && !empty($bandwidthType)) {
			$data['bandwidth_type'] = (int) $bandwidthType;
		}

		$extraFeeType = $this->input->post('extra_fee_type', true);
		$extraFee = $this->input->post('extra_fee', true);
		if (isset($extraFeeType) && !empty($extraFeeType)) {
			$data['extra_fee_type'] = (int) $extraFeeType;
		}
		if (isset($extraFee) && (0 <= $extraFee)) {
			$data['extra_fee'] = (float) $extraFee;
		}

		$vatType = $this->input->post('vat_type', true);
		$vat = $this->input->post('vat', true);
		if (isset($vatType) && !empty($vatType)) {
			$data['vat_type'] = (int) $vatType;
		}
		if (isset($vat) && (0 <= $vat)) {
			$data['vat'] = (float) $vat;
		}

		$data['billing_type'] = $this->input->post('billing_type', true);
		$data['duration_type'] = $this->input->post('duration_type', true);
		$data['duration'] = $this->input->post('duration', true);
		$data['pool'] = $this->input->post('pool', true);
		$data['expirepool'] = $this->input->post('expirepool', true);
		$data['fixed_expire_day_status'] = ($this->input->post('fixed_expire_day_status', true) == 'on' ? 1 : 0);
		$data['fixed_expire_day_accounting_status'] = ($this->input->post('fixed_expire_day_accounting_status', true) == 'on' ? 1 : 0);
		$data['fixed_expire_day_accounting_type'] = $this->input->post('fixed_expire_day_accounting_type', true);
		$fixedExpireDay = $this->input->post('fixed_expire_day', true);
		if (isset($fixedExpireDay) && !empty($fixedExpireDay)) {
			$data['fixed_expire_day'] = $fixedExpireDay;
		}

		$data['fixed_expire_time_status'] = ($this->input->post('fixed_expire_time_status', true) == 'on' ? 1 : 0);
		$data['fixed_expire_time'] = $this->input->post('fixed_expire_time', true);
		$data['autopayment'] = ($this->input->post('autopayment', true) == 'on' ? 1 : 0);
		$data['leftoverdays'] = ($this->input->post('leftoverdays', true) == 'on' ? 1 : 0);
		$data['left_over_volumes'] = ($this->input->post('left_over_volumes', true) == 'on' ? 1 : 0);
		$data['left_over_sessions'] = ($this->input->post('left_over_sessions', true) == 'on' ? 1 : 0);
		$data['autorenew'] = ($this->input->post('autorenew', true) == 'on' ? 1 : 0);
		$data['user_self_activation'] = ($this->input->post('user_self_activation', true) == 'on' ? 1 : 0);
		$data['dataqt'] = ($this->input->post('dataqt', true) == 'on' ? 1 : 0);

		if (!!$this->input->post('dataqtvol', true)) {
			$dataqtvol = preg_replace('/[^0-9.]+/', '', $this->input->post('dataqtvol', true));
			$data['dataqtvol'] = (int) ($dataqtvol * 1024 * 1024) * 1024;
		}
		else {
			$data['dataqtvol'] = 0;
		}

		$data['fupqt'] = ($this->input->post('fupqt', true) == 'on' ? 1 : 0);

		if (!!$this->input->post('fupqtvol', true)) {
			$dataqtvol = preg_replace('/[^0-9.]+/', '', $this->input->post('dataqtvol', true));
			$data['fupqtvol'] = (int) ($this->input->post('fupqtvol', true) * 1024 * 1024) * 1024;
		}
		else {
			$data['fupqtvol'] = 0;
		}

		$data['data_quota_exceed_status'] = ($this->input->post('data_quota_exceed_status', true) == 'on' ? 1 : 0);
		$data['data_quota_exceed_type'] = $this->input->post('data_quota_exceed_type', true);
		$data['session_quota_exceed_status'] = ($this->input->post('session_quota_exceed_status', true) == 'on' ? 1 : 0);
		$data['session_quota_exceed_type'] = $this->input->post('session_quota_exceed_type', true);
		$data['session_fup_limit_status'] = ($this->input->post('session_fup_limit_status', true) == 'on' ? 1 : 0);
		$data['session_fup_bw_limit'] = $this->input->post('session_fup_bw_limit', true);
		$data['fupqtbwlimit'] = $this->input->post('fupqtbwlimit', true);
		$data['sessionqt'] = ($this->input->post('sessionqt', true) == 'on' ? 1 : 0);
		$sessionTime = $this->input->post('sessiontime', true);
		if (isset($sessionTime) && !empty($sessionTime)) {
			$data['sessiontime'] = $sessionTime;
		}

		$data['dynamicbw'] = ($this->input->post('dynamicbw', true) == 'on' ? 1 : 0);
		$data['apply_users'] = ($this->input->post('apply_users', true) == 'on' ? 1 : 0);
		$data['apply_resellers'] = ($this->input->post('apply_resellers', true) == 'on' ? 1 : 0);

		if ($this->input->post('dynamicbw', true) == 'on') {
			$data['dbstarttime1'] = $this->input->post('dbstarttime1', true);
			$data['dbendtime1'] = $this->input->post('dbendtime1', true);
			$data['dblimit1'] = $this->input->post('dblimit1', true);
			$data['dbstarttime2'] = $this->input->post('dbstarttime2', true);
			$data['dbendtime2'] = $this->input->post('dbendtime2', true);
			$data['dblimit2'] = $this->input->post('dblimit2', true);
			$data['dbstarttime3'] = $this->input->post('dbstarttime3', true);
			$data['dbendtime3'] = $this->input->post('dbendtime3', true);
			$data['dblimit3'] = $this->input->post('dblimit3', true);
			$data['dbstarttime4'] = $this->input->post('dbstarttime4', true);
			$data['dbendtime4'] = $this->input->post('dbendtime4', true);
			$data['dblimit4'] = $this->input->post('dblimit4', true);
			$data['dbstarttime5'] = $this->input->post('dbstarttime5', true);
			$data['dbendtime5'] = $this->input->post('dbendtime5', true);
			$data['dblimit5'] = $this->input->post('dblimit5', true);
			$data['dbstarttime6'] = $this->input->post('dbstarttime6', true);
			$data['dbendtime6'] = $this->input->post('dbendtime6', true);
			$data['dblimit6'] = $this->input->post('dblimit6', true);
			$data['dbstarttime7'] = $this->input->post('dbstarttime7', true);
			$data['dbendtime7'] = $this->input->post('dbendtime7', true);
			$data['dblimit7'] = $this->input->post('dblimit7', true);
			$data['dbstarttime8'] = $this->input->post('dbstarttime8', true);
			$data['dbendtime8'] = $this->input->post('dbendtime8', true);
			$data['dblimit8'] = $this->input->post('dblimit8', true);
			$data['dbstarttime9'] = $this->input->post('dbstarttime9', true);
			$data['dbendtime9'] = $this->input->post('dbendtime9', true);
			$data['dblimit9'] = $this->input->post('dblimit9', true);
			$data['dbstarttime10'] = $this->input->post('dbstarttime10', true);
			$data['dbendtime10'] = $this->input->post('dbendtime10', true);
			$data['dblimit10'] = $this->input->post('dblimit10', true);
			$data['dbstarttime11'] = $this->input->post('dbstarttime11', true);
			$data['dbendtime11'] = $this->input->post('dbendtime11', true);
			$data['dblimit11'] = $this->input->post('dblimit11', true);
			$data['dbstarttime12'] = $this->input->post('dbstarttime12', true);
			$data['dbendtime12'] = $this->input->post('dbendtime12', true);
			$data['dblimit12'] = $this->input->post('dblimit12', true);
		}

		$this->db->where('id', $id);
		$packUpdateRes = $this->db->update('packages', $data);
		$this->db->reset_query();
		$fpackagesData = getJoinPackageByID($id);
		$accountingData['adminid'] = $fpackagesData->adminid;
		$accountingData['fadminid'] = 0;
		$accountingData['pkgid'] = $id;
		$accountingData['price'] = $this->input->post('price', true);
		$accountingData['aprofit'] = $this->input->post('aprofit', true);
		$accountingData['fprofit'] = 0;
		$accountingData['dprofit'] = 0;
		$accountingData['sprofit'] = 0;
		$extraFeeType = $this->input->post('extra_fee_type', true);
		$extraFee = $this->input->post('extra_fee', true);
		if (isset($extraFeeType) && !empty($extraFeeType)) {
			$accountingData['extra_fee_type'] = (int) $extraFeeType;
		}
		if (isset($extraFee) && (0 <= $extraFee)) {
			$accountingData['extra_fee'] = (float) $extraFee;
		}

		$vatType = $this->input->post('vat_type', true);
		$vat = $this->input->post('vat', true);
		if (isset($vatType) && !empty($vatType)) {
			$accountingData['vat_type'] = (int) $vatType;
		}
		if (isset($vat) && (0 <= $vat)) {
			$accountingData['vat'] = (float) $vat;
		}

		$this->db->where('adminid', $fpackagesData->adminid);
		$this->db->where('fpkgid', $fpackagesData->fpkgid);
		$accPackUpdateRes = $this->db->update('f_packages', $accountingData);
		$this->db->reset_query();
		$this->main->insertActivity('Package Update');
		if ($packUpdateRes && $accPackUpdateRes) {
			if ($data['apply_users'] == 1) {
				$packagePool = $data['pool'];
				$packageExpirePool = $data['expirepool'];
				$packageDataQuota = $data['dataqt'];
				$packageTotalVolume = $data['dataqtvol'];
				$packageSessionQuota = $data['sessionqt'];
				$packageSessionTime = $data['sessiontime'];
				$this->db->select('username');
				$this->db->where('package', $id);
				$usersObj = $this->db->get('usersinfo');
				$this->db->reset_query();

				if (0 < $usersObj->num_rows()) {
					foreach ($usersObj->result() as $userData) {
						$radCheckObj = $this->main->singleQuery('radcheck', ['username' => $userData->username, 'attribute' => 'Expiration']);

						if ($radCheckObj) {
							$expiryTime = $radCheckObj[0]->value;

							if (strtotime($expiryTime) < time()) {
								if (isset($packageExpirePool) && !empty($packageExpirePool)) {
									$packageRadReplyPool = $packageExpirePool;
								}
							}
							else if (time() < strtotime($expiryTime)) {
								if (isset($packagePool) && !empty($packagePool)) {
									$packageRadReplyPool = $packagePool;
								}
							}
						}
						else if (isset($packagePool) && !empty($packagePool)) {
							$packageRadReplyPool = $packagePool;
						}

						$radReplyDeleteRes = $this->main->singleDelete('radreply', ['username' => $userData->username, 'attribute' => 'Framed-Pool']);
						$this->db->reset_query();
						if (isset($packageRadReplyPool) && !empty($packageRadReplyPool)) {
							$dataRadReply['username'] = $userData->username;
							$dataRadReply['attribute'] = 'Framed-Pool';
							$dataRadReply['op'] = ':=';
							$dataRadReply['value'] = $packageRadReplyPool;
							$radReplyInsertRes = $this->main->singleInsert('radreply', $dataRadReply);
							$this->db->reset_query();
						}
						if (isset($data['groupname']) && !empty($data['groupname'])) {
							$groupData['username'] = $userData->username;
							$groupData['groupname'] = $data['groupname'];
							$groupData['priority'] = 1;
							$radusergroupRes = $this->main->singleDelete('radusergroup', ['username' => $userData->username]);
							$insertGroupRes = $this->main->singleInsert('radusergroup', $groupData);
						}
						if ((0 < $packageDataQuota) && (0 < $packageTotalVolume)) {
							$userDataUpdate = [];
							$userDataUpdate['is_enabled'] = $packageDataQuota;
							$userDataUpdate['qt_total'] = $packageTotalVolume;
							$userDataUpdateRes = $this->main->singleUpdate('usersinfo', $userDataUpdate, ['username' => $userData->username]);
							$this->db->reset_query();
						}
						if ((0 < $packageSessionQuota) && (0 < $packageSessionTime)) {
							$userSessTimeUpdate = [];
							$userSessTimeUpdate['qt_total_session'] = $packageSessionTime;
							$userSessTimeUpdateRes = $this->main->singleUpdate('usersinfo', $userSessTimeUpdate, ['username' => $userData->username]);
							$this->db->reset_query();
						}
					}
				}
			}

			if ($data['apply_resellers'] == 1) {
			}

			$this->session->set_flashdata('success', 'Package Successfully Updated');
		}
		else {
			$this->session->set_flashdata('error', 'Oops! Something Wrong');
		}

		redirect('package/edit/' . $id);
	}

	public function delete($id)
	{
		isPermitted('package_delete');
		$packageQuery = $this->main->singleQuery('packages', ['id' => $id]);

		if (!$packageQuery) {
			$this->session->set_flashdata('error', 'Oops! Package Not Found');
			redirect('package/all');
		}

		$this->db->delete('packages', ['id' => $id]);
		$this->db->reset_query();
		$this->db->delete('f_packages', ['pkgid' => $id]);
		$this->db->reset_query();
		$this->main->insertActivity('Package Delete');
		redirect('package/all');
	}
}

?>