<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sys_user_role extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;

    public function __construct()
    {
        parent::__construct();
        $user = User_helper::get_user();
        $this->message="";
        $this->permissions=User_helper::get_permission('Sys_user_role');
        if($user->user_group==1)
        {
            $this->permissions['view']=1;
            $this->permissions['edit']=1;
        }
        $this->controller_url='sys_user_role';
        $this->load->model("sys_user_role_model");
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

    public function system_list()
    {
        if(isset($this->permissions['view'])&&($this->permissions['view']==1))
        {
            $data['title']="User Role";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("sys_user_role/list",$data,true));
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

    public function system_edit($id)
    {
        if(isset($this->permissions['edit'])&&($this->permissions['edit']==1))
        {
            if(($this->input->post('id')))
            {
                $group_id=$this->input->post('id');
            }
            else
            {
                $group_id=$id;
            }
            $this->load->model("sys_module_task_model");

            $data['modules_tasks']=$this->sys_module_task_model->get_modules_tasks_table_tree();
            $data['role_status']=$this->sys_user_role_model->get_role_status($group_id);
            $data['title']="Edit User Role";
            $data['group_id']=$group_id;
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("sys_user_role/add_edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit/'.$group_id);
            $this->jsonReturn($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
        }
    }

    public function system_save()
    {
        $group_id = $this->input->post("id");
        $user = User_helper::get_user();
        if(!(isset($this->permissions['edit'])&&($this->permissions['edit']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
            die();
        }

        $tasks=$this->input->post('tasks');

        $time=time();
        $this->db->trans_start();  //DB Transaction Handle START

        $this->db->where('user_group_id',$group_id);
        $this->db->set('revision', 'revision+1', FALSE);
        $this->db->update($this->config->item('table_system_user_group_role'));
        if(is_array($tasks))
        {
            foreach($tasks as $task_id=>$task)
            {

                $data=array();
                if(isset($task['view'])&& ($task['view']==1))
                {
                    $data['view']=1;
                }
                else
                {
                    $data['view']=0;
                }
                if(isset($task['add'])&& ($task['add']==1))
                {
                    $data['add']=1;
                }
                else
                {
                    $data['add']=0;
                }
                if(isset($task['edit'])&& ($task['edit']==1))
                {
                    $data['edit']=1;
                }
                else
                {
                    $data['edit']=0;
                }
                if(isset($task['delete'])&& ($task['delete']==1))
                {
                    $data['delete']=1;
                }
                else
                {
                    $data['delete']=0;
                }
                if(isset($task['print'])&& ($task['print']==1))
                {
                    $data['print']=1;
                }
                else
                {
                    $data['print']=0;
                }
                if(isset($task['download'])&& ($task['download']==1))
                {
                    $data['download']=1;
                }
                else
                {
                    $data['download']=0;
                }
                if(isset($task['column_headers'])&& ($task['column_headers']==1))
                {
                    $data['column_headers']=1;
                }
                else
                {
                    $data['column_headers']=0;
                }
                if(($data['add'])||($data['edit'])||($data['delete'])||($data['print'])||($data['download'])||($data['column_headers']))
                {
                    $data['view']=1;
                }
                $data['task_id']=$task_id;
                $data['user_group_id']=$group_id;
                $data['user_created'] = $user->user_id;
                $data['date_created'] =$time;

                Query_helper::add($this->config->item('table_system_user_group_role'),$data);

            }
        }

        $this->db->trans_complete();   //DB Transaction Handle END

        if ($this->db->trans_status() === TRUE)
        {
            $this->message=$this->lang->line("MSG_ROLE_ASSIGN_SUCCESS");
            $this->system_list();
        }
        else
        {
            $ajax['status']=false;
            $ajax['desk_message']=$this->lang->line("MSG_ROLE_ASSIGN_FAIL");
            $this->jsonReturn($ajax);
        }
    }
    public function get_items()
    {
        //$items=Query_helper::get_info($this->config->item('table_system_user_group'),array('id','name','status','ordering'),array('status !="'.$this->config->item('system_status_delete').'"'));
        $items=$this->sys_user_role_model->get_roles_count();
        $this->jsonReturn($items);

    }

}
