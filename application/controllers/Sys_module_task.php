<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sys_module_task extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Sys_module_task');
        $this->controller_url='sys_module_task';
        $this->load->model("sys_module_task_model");
    }

    public function index($action="list",$id=0)
    {
        if($action=="list")
        {
            $this->system_list($id);
        }
        elseif($action=="add")
        {
            $this->system_add();
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
        $data['title']="Module and Task";
        if(isset($this->permissions['view'])&&($this->permissions['view']==1))
        {
            $data['modules_tasks']=$this->sys_module_task_model->get_modules_tasks_table_tree();

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("sys_module_task/list",$data,true));
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
        /**/

    }

    private function system_add()
    {
        if(isset($this->permissions['add'])&&($this->permissions['add']==1))
        {

            $data['title']="Create New Module/Task";
            $data["module_task"] = Array(
                'id' => 0,
                'name' => '',
                'type' => '',
                'parent' => 0,
                'controller' => '',
                'ordering' => 99,
                'icon' => 'menu.png',
                'status' => $this->config->item('system_status_active')
            );
            $ajax['system_page_url']=site_url($this->controller_url."/index/add");
            //$data['crops'] = System_helper::get_ordered_crops();
            $data["modules"] = $this->sys_module_task_model->get_modules();
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("sys_module_task/add_edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
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

            $data['module_task'] = $this->sys_module_task_model->get_module_task_info($id);
            $data['title']='Edit '.$data['module_task']['name'];
            $ajax['system_page_url']=site_url($this->controller_url."/index/edit/".$id);
            //$data['crops'] = System_helper::get_ordered_crops();
            $data["modules"] = $this->sys_module_task_model->get_modules();
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("sys_module_task/add_edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
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
        $data=$this->input->post('task');
        $user = User_helper::get_user();
        if($id>0)
        {
            if(!(isset($this->permissions['edit'])&&($this->permissions['edit']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->jsonReturn($ajax);
                die();
            }
        }
        else
        {
            if(!(isset($this->permissions['add'])&&($this->permissions['add']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->jsonReturn($ajax);
                die();

            }
        }
        if(!$this->check_validation())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->jsonReturn($ajax);
        }
        else
        {
            $this->db->trans_start();  //DB Transaction Handle START
            if($id>0)
            {
                $data['user_updated'] = $user->user_id;
                $data['date_updated'] = time();

                Query_helper::update($this->config->item('table_system_task'),$data,array("id = ".$id));

            }
            else
            {

                $data['user_created'] = $user->user_id;
                $data['date_created'] = time();
                Query_helper::add($this->config->item('table_system_task'),$data);
            }
            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {
                $save_and_new=$this->input->post('system_save_new_status');
                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                if($save_and_new==1)
                {
                    $this->system_add();
                }
                else
                {
                    $this->system_list();
                }
            }
            else
            {
                $ajax['status']=false;
                $ajax['desk_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->jsonReturn($ajax);
            }
        }
    }
    private function check_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('task[name]',$this->lang->line('LABEL_NAME'),'required');
        $this->form_validation->set_rules('task[type]',$this->lang->line('LABEL_TYPE'),'required');
        $data=$this->input->post('task');
        if($data['type']=='TASK')
        {
            $this->form_validation->set_rules('task[controller]',$this->lang->line('LABEL_CONTROLLER_NAME'),'required');
        }



        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }

}
