<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mgt_direct_cost_percentage extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Mgt_direct_cost_percentage');
        $this->controller_url='mgt_direct_cost_percentage';
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
            $data['title']="Direct Cost Percentage(fiscal year lists)";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("mgt_direct_cost_percentage/list",$data,true));
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
        $this->db->select('COUNT(dcp.id) num_item_setup');
        $this->db->join($this->config->item('table_mgt_direct_cost_percentage').' dcp','dcp.fiscal_year_id = fy.id and dcp.status ="'.$this->config->item('system_status_active').'"','LEFT');
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
            $results=Query_helper::get_info($this->config->item('table_mgt_direct_cost_percentage'),'*',array('status !="'.$this->config->item('system_status_delete').'"','fiscal_year_id ='.$fiscal_year_id));
            $data['percentages']=array();
            foreach($results as $result)
            {
                $data['percentages'][$result['item_id']]=$result;
            }
            $data['items']=Query_helper::get_info($this->config->item('table_setup_direct_cost_items'),'*',array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));
            $data['fiscal_year_id']=$fiscal_year_id;
            $year=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text'),array('id ='.$fiscal_year_id),1);
            $data['title']="Edit Direct Cost Item Percentage(".$year['text'].')';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("mgt_direct_cost_percentage/add_edit",$data,true));
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
            $results=Query_helper::get_info($this->config->item('table_mgt_direct_cost_percentage'),'*',array('status !="'.$this->config->item('system_status_delete').'"','fiscal_year_id ='.$fiscal_year_id));
            $data['percentages']=array();
            foreach($results as $result)
            {
                $data['percentages'][$result['item_id']]=$result;
            }
            $data['items']=Query_helper::get_info($this->config->item('table_setup_direct_cost_items'),'*',array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));
            $data['fiscal_year_id']=$fiscal_year_id;
            $year=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text'),array('id ='.$fiscal_year_id),1);
            $data['title']="Edit Direct Cost Item Percentage(".$year['text'].')';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("mgt_direct_cost_percentage/details",$data,true));
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
            $results=Query_helper::get_info($this->config->item('table_mgt_direct_cost_percentage'),'*',array('status !="'.$this->config->item('system_status_delete').'"','fiscal_year_id ='.$fiscal_year_id));
            $old_percentages=array();
            foreach($results as $result)
            {
                $old_percentages[$result['item_id']]=$result;
            }
            $items=$this->input->post('items');

            $this->db->trans_start();  //DB Transaction Handle START
            if(sizeof($items)>0)
            {
                foreach($items as $item_id=>$percentage)
                {
                    $data=array();
                    $data['item_id']=$item_id;
                    if(strlen($percentage)==0)
                    {
                        $data['percentage']=0;
                    }
                    else
                    {
                        $data['percentage']=$percentage;
                    }
                    if(isset($old_percentages[$item_id]))
                    {
                        $data['user_updated'] = $user->user_id;
                        $data['date_updated'] = $time;
                        Query_helper::update($this->config->item('table_mgt_direct_cost_percentage'),$data,array("id = ".$old_percentages[$item_id]['id']));
                    }
                    else
                    {
                        $data['fiscal_year_id']=$fiscal_year_id;
                        $data['user_created'] = $user->user_id;
                        $data['date_created'] = $time;
                        Query_helper::add($this->config->item('table_mgt_direct_cost_percentage'),$data);
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
