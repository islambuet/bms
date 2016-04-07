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
        $db_login=$this->load->database('armalik_login',TRUE);
        $db_login->from($this->config->item('table_other_sites_visit'));
        $db_login->where('auth_key',$auth_code);
        $db_login->where('status',$this->config->item('system_status_active'));
        $info=$db_login->get()->row_array();
        if($info)
        {
            $db_login->where('id',$info['id']);
            $db_login->set('status', $this->config->item('system_status_inactive'));
            $db_login->update($this->config->item('table_other_sites_visit'));

            $this->session->set_userdata("user_id", $info['user_id']);

        }
        redirect(site_url());

    }
}
