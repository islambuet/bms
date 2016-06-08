<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mgt_currency_rate extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Mgt_currency_rate');
        $this->controller_url='mgt_currency_rate';
        //$this->load->model("sys_module_task_model");
    }

    public function index($action="list",$id=0)
    {
        if($action=="list")
        {
            $this->system_list($id);
        }
        elseif($action=="edit")
        {
            $this->system_edit($id);
        }
        elseif($action=="details")
        {
            $this->system_details($id);
        }
        elseif($action=="save")
        {
            $this->system_save();
        }
        else
        {
            $this->system_list($id);
        }
    }

    private function system_list()
    {
        if(isset($this->permissions['view'])&&($this->permissions['view']==1))
        {
            $data['title']="Currency Rate(fiscal year lists)";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("mgt_currency_rate/list",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url);
            $this->jsonReturn($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
        }

    }
    public function get_items()
    {
        $this->db->from($this->config->item('ems_basic_setup_fiscal_year').' fy');
        $this->db->select('fy.id,fy.name,fy.ordering');
        $this->db->select('COUNT(cr.id) num_currency_setup');
        $this->db->join($this->config->item('table_mgt_currency_rate').' cr','cr.fiscal_year_id = fy.id and cr.status ="'.$this->config->item('system_status_active').'"','LEFT');
        $this->db->where('fy.status !=',$this->config->item('system_status_delete'));
        $this->db->group_by('fy.id');
        $this->db->order_by('fy.id','ASC');
        $items=$this->db->get()->result_array();
        $this->jsonReturn($items);
    }
    private function system_edit($id)
    {
        if(isset($this->permissions['edit'])&&($this->permissions['edit']==1))
        {
            if(($this->input->post('id')))
            {
                $fiscal_year_id=$this->input->post('id');
            }
            else
            {
                $fiscal_year_id=$id;
            }
            $rates=Query_helper::get_info($this->config->item('table_mgt_currency_rate'),'*',array('status !="'.$this->config->item('system_status_delete').'"'),'fiscal_year_id ='.$fiscal_year_id);
            $data['rates']=array();
            foreach($rates as $rate)
            {
                $data['rates'][$rate['currency_id']]=$rate['rate'];
            }
            $data['currencies']=Query_helper::get_info($this->config->item('table_setup_currency'),'*',array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));
            $data['fiscal_year_id']=$fiscal_year_id;
            $data['title']="Edit Currency Rate";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("mgt_currency_rate/add_edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit/'.$fiscal_year_id);
            $this->jsonReturn($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
        }
    }
    private function system_details($id)
    {
        if(isset($this->permissions['view'])&&($this->permissions['view']==1))
        {
            if(($this->input->post('id')))
            {
                $fiscal_year_id=$this->input->post('id');
            }
            else
            {
                $fiscal_year_id=$id;
            }
            $rates=Query_helper::get_info($this->config->item('table_mgt_currency_rate'),'*',array('status !="'.$this->config->item('system_status_delete').'"'),'fiscal_year_id ='.$fiscal_year_id);
            $data['rates']=array();
            foreach($rates as $rate)
            {
                $data['rates'][$rate['currency_id']]=$rate['rate'];
            }
            $data['currencies']=Query_helper::get_info($this->config->item('table_setup_currency'),'*',array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));
            $data['fiscal_year_id']=$fiscal_year_id;
            $data['title']="Edit Currency Rate";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("mgt_currency_rate/details",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/details/'.$fiscal_year_id);
            $this->jsonReturn($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
        }
    }

    private function system_save()
    {
        $fiscal_year_id = $this->input->post("id");
        $user = User_helper::get_user();
        $time=time();
        if(!(isset($this->permissions['edit'])&&($this->permissions['edit']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
            die();
        }
        if(!$this->check_validation())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->jsonReturn($ajax);
        }
        else
        {
            $results=Query_helper::get_info($this->config->item('table_mgt_currency_rate'),'*',array('status !="'.$this->config->item('system_status_delete').'"'),'fiscal_year_id ='.$fiscal_year_id);
            $old_rates=array();
            foreach($results as $result)
            {
                $old_rates[$result['currency_id']]=$result;
            }

            $currencies=$this->input->post('currencies');

            $this->db->trans_start();  //DB Transaction Handle START
            if(sizeof($currencies)>0)
            {
                foreach($currencies as $currency_id=>$rate)
                {
                    $data=array();
                    $data['currency_id']=$currency_id;
                    if(strlen($rate)==0)
                    {
                        $data['rate']=0;
                    }
                    else
                    {
                        $data['rate']=$rate;
                    }
                    if(isset($old_rates[$currency_id]))
                    {
                        $data['user_updated'] = $user->user_id;
                        $data['date_updated'] = $time;
                        Query_helper::update($this->config->item('table_mgt_currency_rate'),$data,array("id = ".$old_rates['id']));
                    }
                    else
                    {
                        $data['fiscal_year_id']=$fiscal_year_id;
                        $data['user_created'] = $user->user_id;
                        $data['date_created'] = $time;
                        Query_helper::add($this->config->item('table_mgt_currency_rate'),$data);
                    }
                }
            }

            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {

                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                $this->system_list();
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->jsonReturn($ajax);
            }
        }
    }
    private function check_validation()
    {
        return true;
    }


}
