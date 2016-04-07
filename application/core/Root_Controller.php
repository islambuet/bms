<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

abstract class Root_Controller extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if ($this->input->is_ajax_request())
        {
            $user=User_helper::get_user();
            if(!$user)
            {
                if($this->router->class!="home")
                {
                    $this->login_page("Time out");
                }
            }
        }
        else
        {
            echo $this->load->view("main",'',true);
            die();
        }
    }
    public function jsonReturn($array)
    {
        header('Content-type: application/json');
        echo json_encode($array);
        exit();
    }
    public function login_page($message="")
    {
        $ajax['status']=true;
        $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("login","",true));
        $ajax['system_content'][]=array("id"=>"#system_menus","html"=>'');
        if($message)
        {
            $ajax['system_message']=$message;
        }
        $ajax['system_page_url']=base_url()."home/login";
        $this->jsonReturn($ajax);
    }
    public function dashboard_page($message="")
    {
        $ajax['status']=true;
        $data['test']='asdf';
        //$this->load->model("root_model");
        //$data['modules']=$this->root_model->get_modules();
        /*if($module_id)
        {
            $this->load->model("dashboard_model");
            $data['tasks']=$this->dashboard_model->get_tasks($module_id);
        }
        else
        {
            $data['tasks']=array();
        }*/

        $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("dashboard",$data,true));
        $ajax['system_content'][]=array("id"=>"#system_menus","html"=>$this->load->view("menu",array(),true));
        if($message)
        {
            $ajax['system_message']=$message;
        }
        $ajax['system_page_url']=base_url()."home";
        $this->jsonReturn($ajax);
    }
}
