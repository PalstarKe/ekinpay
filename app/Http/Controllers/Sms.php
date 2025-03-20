<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Sms extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		isKena();
		isLogin();
		ifClient();
		isPermitted('sms_module', [11, 12, 13], false);
		$langFiles = ['global/global', 'sms/sms'];
		translation($langFiles);
	}

	public function index()
	{
		isPermitted('sms_bulk_module');
		isPermitted('sms_alert_module');
		$data['smsalerts'] = $this->main->getAllSmsAlerts();
		$data['activeTempAlerts'] = $this->smsM->getActiveTemplates();
		$data['users'] = $this->main->getAllUsersinfo();
		$data['packages'] = $this->main->getAllGMPackages();
		$data['areas'] = $this->main->getTypesArea();
		$data['subareas'] = $this->main->getTypesSubArea();
		$data['cities'] = $this->main->getTypesCity();
		$data['admins'] = $this->main->getOnlyAdmins();
		$data['salespersons'] = $this->main->getSalePersonAdmins();
		$data['nases'] = $this->main->singleQuery('nas');
		$this->load->view('themes/legacy/admin_portal/dashboard/header');
		$this->load->view('themes/legacy/admin_portal/sms/sms_alert', $data);
		$this->load->view('themes/legacy/admin_portal/dashboard/footer');
	}

	public function smsAlert()
	{
		$this->index();
	}

	public function smsAlertUpdate()
	{
		isPermitted('sms_edit');
		$smsID = $this->input->post('smsalertid', true);
		$data['status'] = ($this->input->post('smsalertstatus', true) == 'on' ? 1 : 0);
		$data['template'] = $this->input->post('smsalerttemplate', true);
		$this->db->where('smsid', $smsID);
		$update = $this->db->update('smsalerts', $data);

		if ($update) {
			$this->session->set_flashdata('success', 'SMS Alert Successfully Updated');
		}
		else {
			$this->session->set_flashdata('error', 'Oops! SMS Alert Update failed');
		}

		redirect('sms/sms-alert');
	}

	public function sendBulkSms()
	{
		$fail = [];
		$success = [];

		if (!if_SMSEnable()) {
			$this->session->set_flashdata('error', 'Oops! System SMS Status Disable.');
			redirect('home');
		}

		$filterType = $this->input->post('filterType', true);
		$filterExpiring = $this->input->post('filterExpiring', true);
		$filterPackage = $this->input->post('filterPackage', true);
		$filterSalesperson = $this->input->post('filterSalesperson', true);
		$filterCity = $this->input->post('filterCity', true);
		$filterArea = $this->input->post('filterArea', true);
		$filterSubarea = $this->input->post('filterSubarea', true);
		$filterNas = $this->input->post('filterNas', true);
		$filterJoinFrom = $this->input->post('filterJoinDateFrom', true);
		$filterJoinTo = $this->input->post('filterJoinDateTo', true);
		$filterActivationFrom = $this->input->post('filterActivationDateFrom', true);
		$filterActivationTo = $this->input->post('filterActivationDateTo', true);
		$filterTextType = $this->input->post('filterTextType', true);
		$filterNumberType = $this->input->post('filterNumberType', true);
		$filterNumber = $this->input->post('filterNumber', true);
		$filterTemplateID = $this->input->post('filterTemplate', true);
		$filterMessage = strip_tags($this->input->post('filterMessage', true));

		if (checkFranchise()) {
			$users = $this->franchiseM->getUsersByFranchise();
		}
		else if (checkDealer()) {
			$users = $this->main->getUsersByDealer();
		}
		else if (checkSubdealer()) {
			$users = $this->main->getUsersBySubdealer();
		}
		else {
			$users = $this->main->getAllUsersinfo();
		}

		if ($users) {
			$userIDs = [];
			$userNames = [];

			foreach ($users as $user) {
				$userIDs[] = $user->id;
				$userNames[] = $user->username;
			}

			$filterData = [];
			$filterData['filterType'] = $filterType;
			$filterData['filterPackage'] = $filterPackage;
			$filterData['filterSalesperson'] = $filterSalesperson;
			$filterData['filterExpiring'] = $filterExpiring;
			$filterData['filterCity'] = $filterCity;
			$filterData['filterArea'] = $filterArea;
			$filterData['filterSubarea'] = $filterSubarea;
			$filterData['userIDs'] = $userIDs;
			$filterData['filterNas'] = $filterNas;
			$filterData['filterJoinFrom'] = $filterJoinFrom;
			$filterData['filterJoinTo'] = $filterJoinTo;
			$filterData['filterActivationFrom'] = $filterActivationFrom;
			$filterData['filterActivationTo'] = $filterActivationTo;
			$filterData['filterForSMSOnly'] = 1;
			$requestData = [];
			$selectingString = 'id, username, password, phone, mobile, email, nic, address, status, smsstatus, package, discount, saleperson, joindate, activation_date';

			if ($filterType == 1) {
				$response = $this->userM->generalUsersFilterModal($filterData, $requestData, $selectingString);
			}
			else if ($filterType == 2) {
				$filterData['usernames'] = $userNames;
				$response = $this->userM->activeUsersFilterModal($filterData, $requestData, $selectingString);
			}
			else if ($filterType == 4) {
				$filterData['usernames'] = $userNames;
				$response = $this->userM->disabledUsersFilterModal($filterData, $requestData, $selectingString);
			}
			else if ($filterType == 3) {
				$response = $this->userM->expiredUsersFilterModal($filterData, $requestData, $selectingString);
			}
			if (isset($response) && is_array($response)) {
				if ($response['query']) {
					$query = $response['query']->result();
				}
				else {
					$query = false;
				}
			}
			else {
				$query = false;
			}
		}
		if (isset($filterNumberType) && !empty($filterNumberType) && ($filterNumberType == 1)) {
			if (isset($query) && is_array($query)) {
				foreach ($query as $userData) {
					if (isset($filterTextType) && !empty($filterTextType) && ($filterTextType == 1)) {
						$smsTemplateData = getSMSAlert($filterTemplateID);

						if ($smsTemplateData) {
							$smsText = $smsTemplateData->template;
							$userCurrentBalance = getLGBLByUserID($userData->id);

							if ($userData->status == 1) {
								$status = 'Pending';
							}
							else if ($userData->status == 2) {
								$status = 'Active';
							}
							else {
								$status = 'Disable';
							}
							if (($filterTemplateID == 3) || ($filterTemplateID == 8)) {
								$smsText = str_replace('{password}', $userData->password, $smsText);
							}

							if ($filterTemplateID == 11) {
								$smsText = str_replace('{amount}', $userCurrentBalance, $smsText);
							}

							if ($filterTemplateID == 12) {
								$smsText = str_replace('{status}', $status, $smsText);
							}

							$smsText = getFinalSMSText($smsText, 2, $userData->id);
						}
					}
					else {
						$smsText = $filterMessage;
					}

					$destinationNumber = $userData->mobile;
					$smsResponse = sendSMS($destinationNumber, $smsText);

					if ($smsResponse['status']) {
						$success[] = $smsResponse['status'];
					}
					else {
						$fail[] = $smsResponse['status'];
					}

					$smsDeliveryData = ['smsAlert' => $filterTemplateID, 'destination' => $destinationNumber, 'message' => $smsText, 'userID' => $userData->id, 'adminID' => 0];
					$responseSmsDelivery = $this->smsM->insertDelivery($smsResponse, $smsDeliveryData);
				}

				$this->session->set_flashdata('success', count($success) . ' SMS Successfully Send & ' . count($fail) . ' SMS Failed.');
				redirect('sms/sms-alert');
			}
			else {
				$this->session->set_flashdata('error', 'Oops! User Not Found!');
				redirect('home');
			}
		}
		else if (isset($filterNumber) && isset($filterMessage) && !empty($filterNumber) && !empty($filterMessage)) {
			$customNumbers = explode(',', $filterNumber);
			if (isset($customNumbers) && is_array($customNumbers) && (0 < count($customNumbers))) {
				for ($x = 0; $x < count($customNumbers); $x++) {
					$destinationNumber = $customNumbers[$x];
					$smsResponse = sendSMS($destinationNumber, $filterMessage);

					if ($smsResponse['status']) {
						$success[] = $smsResponse['status'];
					}
					else {
						$fail[] = $smsResponse['status'];
					}

					$smsDeliveryData = ['smsAlert' => 0, 'destination' => $destinationNumber, 'message' => $filterMessage, 'userID' => 0, 'adminID' => 0];
					$responseSmsDelivery = $this->smsM->insertDelivery($smsResponse, $smsDeliveryData);
				}

				$this->session->set_flashdata('success', count($success) . ' SMS Successfully Send & ' . count($fail) . ' SMS Failed.');
				redirect('sms/sms-alert');
			}
			else {
				$this->session->set_flashdata('error', 'Oops! Invalid Mobile Number.');
				redirect('home');
			}
		}
		else {
			$this->session->set_flashdata('error', 'Oops! Invalid Mobile Number Or Message.');
			redirect('home');
		}

		redirect('sms/sms-alert');
	}

	public function smsDelivery()
	{
		isPermitted('sms_delivery_module');
		$this->load->view('themes/legacy/admin_portal/dashboard/header');
		$this->load->view('themes/legacy/admin_portal/sms/sms_delivery');
		$this->load->view('themes/legacy/admin_portal/dashboard/footer');
	}

	public function smsDeliveryDelete($id)
	{
		isPermitted('sms_delivery_delete');
		$res = $this->main->singleDelete('smsdelivered', ['id' => $id]);

		if ($res) {
			$this->session->set_flashdata('success', 'SMS Delivered Log Successfully Deleted');
		}
		else {
			$this->session->set_flashdata('error', 'Oops! Something Went Wrong');
		}

		redirect('sms/sms-delivered');
	}

	public function smsDeliveryAjax()
	{
		$requestData = $_REQUEST;
		if (!isset($requestData) || empty($requestData)) {
			$this->session->set_flashdata('error', 'Forbidden! You Can\'t Direct Access.');
			redirect('home');
		}

		$from = $requestData['from'];
		$to = $requestData['to'];
		$columns = ['id', 'smsalert', 'datetime', 'destination', 'message', 'datetime', 'adminid', 'userid'];

		if (!empty($requestData['search']['value'])) {
			$this->db->where('datetime >=', date('Y-m-d 00:00:00', strtotime($from)));
			$this->db->where('datetime <=', date('Y-m-d 23:59:59', strtotime($to)));
			$totalData = $this->db->from('smsdelivered')->count_all_results();
			$this->db->flush_cache();
			$this->db->group_start();
			$this->db->like('id', $requestData['search']['value']);
			$this->db->or_like('smsalert', $requestData['search']['value']);
			$this->db->or_like('destination', $requestData['search']['value']);
			$this->db->or_like('message', $requestData['search']['value']);
			$this->db->or_like('datetime', $requestData['search']['value']);
			$this->db->or_like('adminid', $requestData['search']['value']);
			$this->db->or_like('userid', $requestData['search']['value']);
			$this->db->or_like('sms_api_response', $requestData['search']['value']);
			$this->db->group_end();
			$this->db->where('datetime >=', date('Y-m-d 00:00:00', strtotime($from)));
			$this->db->where('datetime <=', date('Y-m-d 23:59:59', strtotime($to)));
			$totalFiltered = $this->db->from('smsdelivered')->count_all_results();
			$this->db->flush_cache();
			$this->db->order_by($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
			$this->db->limit($requestData['length'], $requestData['start']);
			$this->db->group_start();
			$this->db->like('id', $requestData['search']['value']);
			$this->db->or_like('smsalert', $requestData['search']['value']);
			$this->db->or_like('destination', $requestData['search']['value']);
			$this->db->or_like('message', $requestData['search']['value']);
			$this->db->or_like('datetime', $requestData['search']['value']);
			$this->db->or_like('adminid', $requestData['search']['value']);
			$this->db->or_like('userid', $requestData['search']['value']);
			$this->db->or_like('sms_api_response', $requestData['search']['value']);
			$this->db->group_end();
			$this->db->where('datetime >=', date('Y-m-d 00:00:00', strtotime($from)));
			$this->db->where('datetime <=', date('Y-m-d 23:59:59', strtotime($to)));
			$query = $this->db->get('smsdelivered')->result();
			$this->db->flush_cache();
		}
		else {
			if (isset($from) && isset($to) && !empty($from) && !empty($to)) {
				$this->db->where('datetime >=', date('Y-m-d 00:00:00', strtotime($from)));
				$this->db->where('datetime <=', date('Y-m-d 23:59:59', strtotime($to)));
			}
			else {
				$this->db->where('datetime >=', date('Y-m-d 00:00:00', strtotime('yesterday')));
			}

			$totalQuery = $this->db->from('smsdelivered')->count_all_results();
			$this->db->flush_cache();
			$totalData = $totalQuery;
			$totalFiltered = $totalData;
			$this->db->order_by($columns[$requestData['order'][0]['column']], $requestData['order'][0]['dir']);
			$this->db->limit($requestData['length'], $requestData['start']);
			if (isset($from) && isset($to) && !empty($from) && !empty($to)) {
				$this->db->where('datetime >=', date('Y-m-d 00:00:00', strtotime($from)));
				$this->db->where('datetime <=', date('Y-m-d 23:59:59', strtotime($to)));
			}
			else {
				$this->db->where('datetime >=', date('Y-m-d 00:00:00', strtotime('yesterday')));
			}

			$query = $this->db->get('smsdelivered')->result();
			$this->db->flush_cache();
		}
		if (isset($query) && is_array($query)) {
			$data = [];

			foreach ($query as $row) {
				$nestedData = [];
				$nestedData[] = $row->id;

				if ($row->smsalert == 1) {
					$nestedData[] = 'Admin/Reseller Login';
				}
				else if ($row->smsalert == 2) {
					$nestedData[] = 'User Login';
				}
				else if ($row->smsalert == 3) {
					$nestedData[] = 'Portal Login';
				}
				else if ($row->smsalert == 8) {
					$nestedData[] = 'New User';
				}
				else if ($row->smsalert == 10) {
					$nestedData[] = 'Reseller Balance';
				}
				else if ($row->smsalert == 11) {
					$nestedData[] = 'User Balance';
				}
				else if ($row->smsalert == 12) {
					$nestedData[] = 'User Status';
				}
				else if ($row->smsalert == 15) {
					$nestedData[] = 'Expiring (7 Days)';
				}
				else if ($row->smsalert == 16) {
					$nestedData[] = 'User Expired';
				}
				else if ($row->smsalert == 16) {
					$nestedData[] = 'User Expired';
				}
				else if ($row->smsalert == 17) {
					$nestedData[] = 'User Activate/Renew';
				}
				else if ($row->smsalert == 18) {
					$nestedData[] = 'Expiring (3 Days)';
				}
				else if ($row->smsalert == 19) {
					$nestedData[] = 'Expiring (1 Day)';
				}
				else {
					$nestedData[] = 'Bulk/Manual/Panel';
				}

				$nestedData[] = $row->datetime;
				$nestedData[] = $row->destination;
				$nestedData[] = $row->message;
				$nestedData[] = $row->sms_api_response;

				if (getAdminByID($row->adminid)) {
					$nestedData[] = '<a data-toggle="tooltip" title="View Profile" href="' . base_url() . 'admin/profile/' . $row->adminid . '" target="_blank">' . "\n" . '                                        <span class="label label-success">' . getAdminByID($row->adminid)->username . '</span>' . "\n" . '                                    </a>';
				}
				else {
					$nestedData[] = '<a data-toggle="tooltip" title="View Profile" href="#" target="_blank">' . "\n" . '                                        <span class="label label-default">N/A</span>' . "\n" . '                                    </a>';
				}

				if (getUserInfo($row->userid)) {
					$nestedData[] = '<a data-toggle="tooltip" title="View Profile" href="' . base_url() . 'user/profile/' . $row->userid . '" target="_blank">' . "\n" . '                                        <span class="label label-success">' . getUserInfo($row->userid)->username . '</span>' . "\n" . '                                    </a>';
				}
				else {
					$nestedData[] = '<a data-toggle="tooltip" title="View Profile" href="#" target="_blank">' . "\n" . '                                        <span class="label label-default">N/A</span>' . "\n" . '                                    </a>';
				}

				$nestedData[] = '<a class="delete" href="' . base_url() . 'sms/sms_delivery_delete/' . $row->id . '"><span data-toggle="tooltip" title="Delete" class="label label-danger"><i class="fas fa-trash-alt"></i></span></a>';
				$data[] = $nestedData;
			}

			$json_data = ['draw' => (int) $requestData['draw'], 'recordsTotal' => (int) $totalData, 'recordsFiltered' => (int) $totalFiltered, 'data' => $data];
			echo json_encode($json_data);
		}
		else {
			$data = [];
			$json_data = ['draw' => (int) $requestData['draw'], 'recordsTotal' => (int) 0, 'recordsFiltered' => (int) 0, 'data' => $data];
			echo json_encode($json_data);
		}
	}
}

?>