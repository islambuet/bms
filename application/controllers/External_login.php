<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class External_login extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{

	}
    public function login($auth_code)
    {
        $this->db->from($this->config->item('system_db_login').'.'.$this->config->item('table_login_other_sites_visit'));
        $this->db->where('auth_key',$auth_code);
        $this->db->where('status',$this->config->item('system_status_active'));
        $info=$this->db->get()->row_array();
        if($info)
        {
            $this->db->where('id',$info['id']);
            $this->db->set('status',$this->config->item('system_status_inactive'));
            $this->db->update($this->config->item('system_db_login').'.'.$this->config->item('table_login_other_sites_visit'));
            $this->session->set_userdata('user_id',$info['user_id']);
        }
        redirect(site_url());

    }
}
