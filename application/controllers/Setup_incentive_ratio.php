<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Setup_incentive_ratio extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Setup_incentive_ratio');
        $this->controller_url='setup_incentive_ratio';
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
            $data['title']="Incentive Ratio Setup List";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("setup_incentive_ratio/list",$data,true));
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
        $this->db->select('ir.hom,ir.ict,ir.di,ir.zi,ir.ti');
        $this->db->join($this->config->item('table_setup_incentive_ratio').' ir','ir.year0_id = fy.id and ir.status ="'.$this->config->item('system_status_active').'"','LEFT');
        $this->db->where('fy.status !=',$this->config->item('system_status_delete'));
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
                $year0_id=$this->input->post('id');
            }
            else
            {
                $year0_id=$id;
            }
            $data['incentive']['hom']=0;
            $data['incentive']['ict']=0;
            $data['incentive']['di']=0;
            $data['incentive']['zi']=0;
            $data['incentive']['ti']=0;
            for($i=0;$i<101;$i++)
            {
                $data['achieve_ratio'][$i]=0;
            }
            $info=Query_helper::get_info($this->config->item('table_setup_incentive_ratio'),'*',array('status !="'.$this->config->item('system_status_delete').'"','year0_id ='.$year0_id),1);
            if($info)
            {
                $data['incentive']['hom']=$info['hom'];
                $data['incentive']['ict']=$info['ict'];
                $data['incentive']['di']=$info['di'];
                $data['incentive']['zi']=$info['zi'];
                $data['incentive']['ti']=$info['ti'];
                $ratio=json_decode($info['achieve_ratio'],true);
                if($ratio)
                {
                    foreach($ratio as $i=>$r)
                    {
                        $data['achieve_ratio'][$i]=$r;
                    }
                }
            }

            $data['year0_id']=$year0_id;
            $year=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text'),array('id ='.$year0_id),1);
            $data['title']="Edit Incentive Ratio (".$year['text'].')';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("setup_incentive_ratio/add_edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit/'.$year0_id);
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
                $year0_id=$this->input->post('id');
            }
            else
            {
                $year0_id=$id;
            }
            $data['incentive']['hom']=0;
            $data['incentive']['ict']=0;
            $data['incentive']['di']=0;
            $data['incentive']['zi']=0;
            $data['incentive']['ti']=0;
            for($i=0;$i<101;$i++)
            {
                $data['achieve_ratio'][$i]=0;
            }
            $info=Query_helper::get_info($this->config->item('table_setup_incentive_ratio'),'*',array('status !="'.$this->config->item('system_status_delete').'"','year0_id ='.$year0_id),1);
            if($info)
            {
                $data['incentive']['hom']=$info['hom'];
                $data['incentive']['ict']=$info['ict'];
                $data['incentive']['di']=$info['di'];
                $data['incentive']['zi']=$info['zi'];
                $data['incentive']['ti']=$info['ti'];
                $ratio=json_decode($info['achieve_ratio'],true);
                if($ratio)
                {
                    foreach($ratio as $i=>$r)
                    {
                        $data['achieve_ratio'][$i]=$r;
                    }
                }
            }

            $data['year0_id']=$year0_id;
            $year=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text'),array('id ='.$year0_id),1);
            $data['title']="Incentive Ratio (".$year['text'].')';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("setup_incentive_ratio/details",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/details/'.$year0_id);
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
        $year0_id = $this->input->post("id");
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
            $setup=Query_helper::get_info($this->config->item('table_setup_incentive_ratio'),'*',array('status !="'.$this->config->item('system_status_delete').'"','year0_id ='.$year0_id),1);
            $data=$this->input->post('incentive');
            $ratio=array();
            for($i=0;$i<101;$i++)
            {
                $ratio[$i]=0;
            }
            foreach($this->input->post('achieve_ratio') as $i=>$ar)
            {
                $ratio[$i]=$ar;
            }
            $data['achieve_ratio']=json_encode($ratio);

            $this->db->trans_start();  //DB Transaction Handle START
            if($setup)
            {
                $data['user_updated'] = $user->user_id;
                $data['date_updated'] = $time;
                Query_helper::update($this->config->item('table_setup_incentive_ratio'),$data,array("id = ".$setup['id']));
            }
            else
            {
                $data['year0_id']=$year0_id;
                $data['user_created'] = $user->user_id;
                $data['date_created'] = $time;
                Query_helper::add($this->config->item('table_setup_incentive_ratio'),$data);
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
