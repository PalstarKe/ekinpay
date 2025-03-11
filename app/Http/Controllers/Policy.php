<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Policy extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		isKena();
		isLogin();
		ifClient();
		isPermitted('bandwidth_module', [11, 12, 13], false);
		$langFiles = ['global/global', 'policy/policy'];
		translation($langFiles);
	}

	public function index()
	{
		isPermitted('bandwidth_policy_module');
		$data = [];
		$radGroupReplyRes = [];
		$radGroupCheckRes = [];
		$radGroupReplyRes = $this->main->singleQuery('radgroupreply');
		$radGroupCheckRes = $this->main->singleQuery('radgroupcheck');
		if (!empty($radGroupReplyRes) && !empty($radGroupCheckRes)) {
			$data['policies'] = array_merge($radGroupReplyRes, $radGroupCheckRes);
		} else if (!empty($radGroupReplyRes) && empty($radGroupCheckRes)) {
			$data['policies'] = $radGroupReplyRes;
		} else if (empty($radGroupReplyRes) && !empty($radGroupCheckRes)) {
			$data['policies'] = $radGroupCheckRes;
		}

		$this->load->view('themes/legacy/admin_portal/dashboard/header');
		$this->load->view('themes/legacy/admin_portal/policy/all', $data);
		$this->load->view('themes/legacy/admin_portal/dashboard/footer');
	}

	public function insert()
	{
		isPermitted('bandwidth_add_new');
		$data = [];
		$responseUpdate = [];
		$responseInsert = [];
		$this->form_validation->set_rules('groupname', 'Group Name', 'trim|required|callback_is_groupname_unique_chk');

		if ($this->form_validation->run()) {
			$groupname = preg_replace('/\\s+/', '', $this->input->post('groupname', true));
			$groupname = removeSpecialChar($groupname);

			for ($x = 1; $x < 31; $x++) {
				$attribute_list = $this->input->post('attribute_list_' . $x, true);
				$attribute_name = $this->input->post('attribute_name_' . $x, true);
				$attribute_op = $this->input->post('attribute_op_' . $x, true);
				$attribute_type = $this->input->post('attribute_type_' . $x, true);
				$attribute_value = $this->input->post('attribute_value_' . $x, true);
				$attribute = preg_replace('/\\s/', '', $attribute_list);
				$op = preg_replace('/\\s/', '', $attribute_op);
				$value = $attribute_value;
				$type = $attribute_type;
				$data['groupname'] = $groupname;

				if ($attribute == 'others') {
					$data['attribute'] = $attribute_name;
				} else {
					$data['attribute'] = $attribute;
				}

				$data['op'] = $op;
				$data['value'] = $value;

				if ($type == 1) {
					$table = 'radgroupreply';
				} else {
					$table = 'radgroupcheck';
				}
				if (!empty($groupname) && !empty($attribute) && !empty($op) && !empty($value)) {
					$response = $this->main->singleQuery($table, ['groupname' => $groupname, 'attribute' => $attribute]);

					if ($response) {
						$responseUpdate[] = $this->main->singleUpdate($table, $data, ['groupname' => $groupname, 'attribute' => $attribute]);
						continue;
					}

					$responseInsert[] = $this->main->singleInsert($table, $data);
				}
			}
			if ((0 < count($responseInsert)) || (0 < count($responseUpdate))) {
				$this->main->insertActivity('Group Policy Added');
				$this->session->set_flashdata('success', 'Policy Created With ' . (count($responseInsert) + count($responseUpdate)) . ' Attributes');
				redirect('policy/');
			} else {
				$this->session->set_flashdata('error', 'Oops! Something Wrong');
				redirect('policy/');
			}
		} else {
			$this->session->set_flashdata('error', preg_replace('/\\s+/', ' ', strip_tags(validation_errors())));
			redirect('policy/');
		}
	}

	public function update()
	{
		isPermitted('bandwidth_edit');
		$data = [];
		$radgroupcheckInsert = 0;
		$radgroupreplyInsert = 0;
		$groupName = '';
		$this->form_validation->set_rules('groupname', 'Group Name', 'trim|required');

		if ($this->form_validation->run()) {
			$id = $this->input->post('id', true);
			$groupIDObj = $this->main->singleQuery('radgroupreply', ['id' => $id]);

			if ($groupIDObj) {
				$groupname = $groupIDObj[0]->groupname;
				$groupreplyavps = $this->input->post('groupreplyavps', true);
				$groupreplyavps = trim(preg_replace('/\\s+/', ' ', $groupreplyavps));
				$groupcheckavps = $this->input->post('groupcheckavps', true);
				$groupcheckavps = trim(preg_replace('/\\s+/', ' ', $groupcheckavps));
				$groupcheckavpsArr = explode('###', $groupcheckavps);
				$groupreplyavpsArr = explode('###', $groupreplyavps);

				for ($x = 0; $x < count($groupcheckavpsArr); $x++) {
					if (isset($groupcheckavpsArr[$x]) && !empty($groupcheckavpsArr[$x])) {
						$groupCheckSubArr = explode('}', $groupcheckavpsArr[$x]);

						if (3 <= count($groupCheckSubArr)) {
							$attribute = str_replace('{', '', $groupCheckSubArr[0]);
							$attribute = preg_replace('/\\s/', '', $attribute);
							$op = str_replace('{', '', $groupCheckSubArr[1]);
							$op = preg_replace('/\\s/', '', $op);
							$value = str_replace('{', '', $groupCheckSubArr[2]);
							$data['groupname'] = $groupname;
							$data['attribute'] = $attribute;
							$data['op'] = $op;
							$data['value'] = $value;
							$groupReplyObj = $this->main->singleQuery('radgroupcheck', ['groupname' => $groupname, 'attribute' => $attribute]);

							if ($groupReplyObj) {
								$radgroupcheckInsert = $this->main->singleUpdate('radgroupcheck', $data, ['groupname' => $groupname, 'attribute' => $attribute]);
							} else {
								$radgroupcheckInsert = $this->main->singleInsert('radgroupcheck', $data);
							}

							if ($radgroupcheckInsert) {
								$radgroupcheckInsert++;
							}
						}
					}
				}

				for ($y = 0; $y < count($groupreplyavpsArr); $y++) {
					if (isset($groupreplyavpsArr[$y]) && !empty($groupreplyavpsArr[$y])) {
						$groupReplySubArr = explode('}', $groupreplyavpsArr[$y]);

						if (3 <= count($groupReplySubArr)) {
							$attribute = str_replace('{', '', $groupReplySubArr[0]);
							$attribute = preg_replace('/\\s/', '', $attribute);
							$op = str_replace('{', '', $groupReplySubArr[1]);
							$op = preg_replace('/\\s/', '', $op);
							$value = str_replace('{', '', $groupReplySubArr[2]);
							$data2['groupname'] = $groupname;
							$data2['attribute'] = $attribute;
							$data2['op'] = $op;
							$data2['value'] = $value;
							$groupReplyObj = $this->main->singleQuery('radgroupreply', ['groupname' => $groupname, 'attribute' => $attribute]);

							if ($groupReplyObj) {
								$radgroupreplyInsert = $this->main->singleUpdate('radgroupreply', $data2, ['groupname' => $groupname, 'attribute' => $attribute]);
							} else {
								$radgroupreplyInsert = $this->main->singleInsert('radgroupreply', $data2);
							}

							if ($radgroupreplyInsert) {
								$radgroupreplyInsert++;
							}
						}
					}
				}
			}
			if ((0 < $radgroupreplyInsert) || (0 < $radgroupcheckInsert)) {
				$this->main->insertActivity('Group Policy Updated');
				$this->session->set_flashdata('success', 'Policy Updated Successfully');
				redirect('policy/');
			} else {
				$this->session->set_flashdata('error', 'Oops! Something Wrong');
				redirect('policy/');
			}
		} else {
			$this->session->set_flashdata('error', preg_replace('/\\s+/', ' ', strip_tags(validation_errors())));
			redirect('policy/');
		}
	}

	public function is_groupname_unique_chk($groupname)
	{
		$groupname = removeSpecialChar(preg_replace('/\\s+/', '', $groupname));
		$resGroupName = $this->main->singleQuery('radgroupcheck', ['groupname' => $groupname]);

		if ($resGroupName) {
			$this->form_validation->set_message('is_groupname_unique_chk', 'Group Name Already Used');
			return false;
		}

		return true;
	}
}
