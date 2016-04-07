<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sys_assign_user_group extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Sys_assign_user_group');
        $this->controller_url='sys_assign_user_group';

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
            $data['title']="List of Users";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("sys_assign_user_group/list",$data,true));
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


    private function system_edit($id)
    {
        if(isset($this->permissions['edit'])&&($this->permissions['edit']==1))
        {
            if(($this->input->post('id')))
            {
                $user_id=$this->input->post('id');
            }
            else
            {
                $user_id=$id;
            }

            $db_login=$this->load->database('armalik_login',TRUE);

            $db_login->from($this->config->item('table_setup_user_info'));
            $db_login->select('name,user_id');
            $db_login->where('revision',1);
            $db_login->where('user_id',$user_id);

            $data['user_info']=$db_login->get()->row_array();
            $data['title']="Assign User(".$data['user_info']['name'].') to a Group';
            $data['user_info']['user_group']=0;
            $group_info=Query_helper::get_info($this->config->item('table_system_assigned_group'),array('user_group'),array('revision =1','user_id ='.$user_id),1);
            if($group_info)
            {
                $data['user_info']['user_group']=$group_info['user_group'];
            }

            $data['user_groups']=Query_helper::get_info($this->config->item('table_system_user_group'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"','id !=1'));

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("sys_assign_user_group/add_edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit/'.$user_id);
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
        $id = $this->input->post("id");
        $user = User_helper::get_user();
        if(!(isset($this->permissions['add'])&&($this->permissions['add']==1)))
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
            $time=time();

            $this->db->trans_start();  //DB Transaction Handle START

            $this->db->where('user_id',$id);
            $this->db->set('revision', 'revision+1', FALSE);
            $this->db->update($this->config->item('table_system_assigned_group'));

            $data['user_id']=$id;
            $data['user_group']=$this->input->post('user_group');
            $data['user_created'] = $user->user_id;
            $data['date_created'] = $time;
            $data['revision'] = 1;

            if($data['user_group']!=0)
            {
                Query_helper::add($this->config->item('table_system_assigned_group'),$data);
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
        $user_group = $this->input->post("user_group");
        if($user_group==1)
        {
            $this->message='Try again';
            return false;
        }
        return true;
    }
    public function get_items()
    {
        $user = User_helper::get_user();
        $db_login=$this->load->database('armalik_login',TRUE);

        $db_login->from($this->config->item('table_setup_user').' user');
        $db_login->select('user.id,user.employee_id,user.user_name,user.status');
        $db_login->select('user_info.name,user_info.ordering');
        $db_login->select('designation.name designation_name');
        //$db_login->select('ug.name group_name');
        $db_login->join($this->config->item('table_setup_user_info').' user_info','user.id = user_info.user_id','INNER');
        $db_login->join($this->config->item('table_setup_users_other_sites').' uos','uos.user_id = user.id','INNER');
        $db_login->join($this->config->item('table_system_other_sites').' os','os.id = uos.site_id','INNER');

        $db_login->join($this->config->item('table_setup_designation').' designation','designation.id = user_info.designation','LEFT');

        $db_login->where('user_info.revision',1);
        $db_login->where('uos.revision',1);
        $db_login->where('os.short_name',$this->config->item('system_site_short_name'));
        $db_login->order_by('user_info.ordering','ASC');
        if($user->user_group!=1)
        {
            $db_login->where('user_info.user_group !=',1);
        }
        $items=$db_login->get()->result_array();


        $this->db->from($this->config->item('table_system_assigned_group').' ag');
        $this->db->select('ag.user_id');
        $this->db->select('ug.name group_name');
        $this->db->join($this->config->item('table_system_user_group').' ug','ug.id = ag.user_group','INNER');
        $this->db->where('ag.revision',1);
        $results=$this->db->get()->result_array();
        $groups=array();
        foreach($results as $result)
        {
            $groups[$result['user_id']]['group_name']=$result['group_name'];
        }
        foreach($items as &$item)
        {
            if(isset($groups[$item['id']]['group_name']))
            {
                $item['group_name']=$groups[$item['id']]['group_name'];
            }
            else
            {
                $item['group_name']='Not Assigned';
            }
        }




        //$items=Query_helper::get_info($this->config->item('table_setup_user'),array('id','name','status','ordering'),array('status !="'.$this->config->item('system_status_delete').'"'));
        $this->jsonReturn($items);

    }

}
