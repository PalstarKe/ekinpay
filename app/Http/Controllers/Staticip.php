<?php

defined('BASEPATH') || exit('No direct script access allowed');

class Staticip extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		isPermitted('mstaticip');
		isKena();
		isLogin();
		ifClient();
	}

	public function index()
	{
		$data['staticips'] = $this->main->singleQuery('usersinfo', ['staticip !=' => '']);
		$this->load->view('themes/legacy/admin_portal/dashboard/header');
		$this->load->view('themes/legacy/admin_portal/staticip/insertstaticip', $data);
		$this->load->view('themes/legacy/admin_portal/dashboard/footer');
	}

	public function insert()
	{
		$data = [];
		$data['ipaddress'] = $this->input->post('address', true);
		$data['updatedon'] = date('Y-m-d H:i:s');
		$insert = $this->db->insert('staticip', $data);
		$this->main->insertActivity('Static IP Insert');

		if ($insert) {
			$this->session->set_flashdata('success', 'Static IP Successfully Created');
			redirect('staticip/staticip');
		}
		else {
			$this->session->set_flashdata('error', 'Oops! Something Wrong');
			redirect('staticip/staticip');
		}
	}

	public function delete($id)
	{
		$insert = $this->db->delete('staticip', ['ipid' => $id]);

		if ($insert) {
			$this->session->set_flashdata('success', 'Static IP Successfully Deleted');
			redirect('staticip/staticip');
		}
		else {
			$this->session->set_flashdata('error', 'Oops! Something Wrong');
			redirect('staticip/staticip');
		}
	}
}

?>