<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sys_assign_user_area extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Sys_assign_user_area');
        $this->controller_url='sys_assign_user_area';

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
            $data['title']="List of Users To assign area";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("sys_assign_user_area/list",$data,true));
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
            if(!$data['user_info'])
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->jsonReturn($ajax);
                die();
            }
            $data['title']="Assign (".$data['user_info']['name'].') to an Area';

            $this->db->from($this->config->item('table_system_assigned_area').' aa');
            $this->db->select('aa.*');
            $this->db->select('union.name union_name');
            $this->db->select('u.name upazilla_name');
            $this->db->select('d.name district_name');
            $this->db->select('t.name territory_name');
            $this->db->select('zone.name zone_name');
            $this->db->select('division.name division_name');
            $this->db->join($this->config->item('ems_setup_location_unions').' union','union.id = aa.union_id','LEFT');
            $this->db->join($this->config->item('ems_setup_location_upazillas').' u','u.id = aa.upazilla_id','LEFT');
            $this->db->join($this->config->item('ems_setup_location_districts').' d','d.id = aa.district_id','LEFT');
            $this->db->join($this->config->item('ems_setup_location_territories').' t','t.id = aa.territory_id','LEFT');
            $this->db->join($this->config->item('ems_setup_location_zones').' zone','zone.id = aa.zone_id','LEFT');
            $this->db->join($this->config->item('ems_setup_location_divisions').' division','division.id = aa.division_id','LEFT');
            $this->db->where('aa.revision',1);
            $this->db->where('aa.user_id',$user_id);
            $data['assigned_area']=$this->db->get()->row_array();
            if($data['assigned_area'])
            {
                $this->db->from($this->config->item('table_system_assigned_area').' aa');
                if($data['assigned_area']['division_id']>0)
                {
                    $this->db->join($this->config->item('ems_setup_location_divisions').' division','division.id = aa.division_id','INNER');
                }
                if($data['assigned_area']['zone_id']>0)
                {
                    $this->db->join($this->config->item('ems_setup_location_zones').' zone','zone.division_id = division.id','INNER');
                    $this->db->where('zone.id',$data['assigned_area']['zone_id']);
                }
                if($data['assigned_area']['territory_id']>0)
                {
                    $this->db->join($this->config->item('ems_setup_location_territories').' t','t.zone_id = zone.id','INNER');
                    $this->db->where('t.id',$data['assigned_area']['territory_id']);
                }
                if($data['assigned_area']['district_id']>0)
                {
                    $this->db->join($this->config->item('ems_setup_location_districts').' d','d.territory_id = t.id','INNER');
                    $this->db->where('d.id',$data['assigned_area']['district_id']);
                }
                if($data['assigned_area']['upazilla_id']>0)
                {
                    $this->db->join($this->config->item('ems_setup_location_upazillas').' u','u.district_id = d.id','INNER');
                    $this->db->where('u.id',$data['assigned_area']['upazilla_id']);
                }
                if($data['assigned_area']['union_id']>0)
                {
                    $this->db->join($this->config->item('ems_setup_location_unions').' union','union.upazilla_id = u.id','INNER');
                    $this->db->where('union.id',$data['assigned_area']['union_id']);
                }
                $this->db->where('aa.revision',1);
                $this->db->where('aa.user_id',$user_id);
                $info=$this->db->get()->row_array();
                if(!$info)
                {
                    $data['message']="Relation between assigned area is not correct.Please re-assign this user.";
                }
            }



            $data['divisions']=Query_helper::get_info($this->config->item('ems_setup_location_divisions'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("sys_assign_user_area/add_edit",$data,true));
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
            $this->db->update($this->config->item('table_system_assigned_area'));

            $data=$this->input->post('area');
            $data['user_id']=$id;
            $data['user_created'] = $user->user_id;
            $data['date_created'] = $time;
            $data['revision'] = 1;

            Query_helper::add($this->config->item('table_system_assigned_area'),$data);

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
        $this->load->library('form_validation');
        $data=$this->input->post('area');
        if($data['union_id']>0)
        {
            $this->form_validation->set_rules('area[upazilla_id]',$this->lang->line('LABEL_UPAZILLA_NAME'),'required|is_natural_no_zero');
        }
        if($data['upazilla_id']>0)
        {
            $this->form_validation->set_rules('area[district_id]',$this->lang->line('LABEL_DISTRICT_NAME'),'required|is_natural_no_zero');
        }
        if($data['district_id']>0)
        {
            $this->form_validation->set_rules('area[territory_id]',$this->lang->line('LABEL_TERRITORY_NAME'),'required|is_natural_no_zero');
        }
        if($data['territory_id']>0)
        {
            $this->form_validation->set_rules('area[zone_id]',$this->lang->line('LABEL_ZONE_NAME'),'required|is_natural_no_zero');
        }
        if($data['zone_id']>0)
        {
            $this->form_validation->set_rules('area[division_id]',$this->lang->line('LABEL_DIVISION_NAME'),'required|is_natural_no_zero');
        }
        $this->form_validation->set_rules('id',$this->lang->line('LABEL_USER_NAME'),'required|is_natural_no_zero');

        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
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


        $this->db->from($this->config->item('table_system_assigned_area').' aa');
        $this->db->select('aa.user_id');
        $this->db->select('union.name union_name');
        $this->db->select('u.name upazilla_name');
        $this->db->select('d.name district_name');
        $this->db->select('t.name territory_name');
        $this->db->select('zone.name zone_name');
        $this->db->select('division.name division_name');
        $this->db->join($this->config->item('ems_setup_location_unions').' union','union.id = aa.union_id','LEFT');
        $this->db->join($this->config->item('ems_setup_location_upazillas').' u','u.id = aa.upazilla_id','LEFT');
        $this->db->join($this->config->item('ems_setup_location_districts').' d','d.id = aa.district_id','LEFT');
        $this->db->join($this->config->item('ems_setup_location_territories').' t','t.id = aa.territory_id','LEFT');
        $this->db->join($this->config->item('ems_setup_location_zones').' zone','zone.id = aa.zone_id','LEFT');
        $this->db->join($this->config->item('ems_setup_location_divisions').' division','division.id = aa.division_id','LEFT');
        $this->db->where('aa.revision',1);
        $results=$this->db->get()->result_array();
        $areas=array();
        foreach($results as $result)
        {
            $areas[$result['user_id']]=$result;
        }
        foreach($items as &$item)
        {
            if(isset($areas[$item['id']]))
            {
                if($areas[$item['id']]['union_name'])
                {
                    $item['union_name']=$areas[$item['id']]['union_name'];
                }
                else
                {
                    $item['union_name']='ALL';
                }
                if($areas[$item['id']]['upazilla_name'])
                {
                    $item['upazilla_name']=$areas[$item['id']]['upazilla_name'];
                }
                else
                {
                    $item['upazilla_name']='ALL';
                }
                if($areas[$item['id']]['district_name'])
                {
                    $item['district_name']=$areas[$item['id']]['district_name'];
                }
                else
                {
                    $item['district_name']='ALL';
                }
                if($areas[$item['id']]['territory_name'])
                {
                    $item['territory_name']=$areas[$item['id']]['territory_name'];
                }
                else
                {
                    $item['territory_name']='ALL';
                }
                if($areas[$item['id']]['zone_name'])
                {
                    $item['zone_name']=$areas[$item['id']]['zone_name'];
                }
                else
                {
                    $item['zone_name']='ALL';
                }
                if($areas[$item['id']]['division_name'])
                {
                    $item['division_name']=$areas[$item['id']]['division_name'];
                }
                else
                {
                    $item['division_name']='ALL';
                }

            }
            else
            {
                $item['union_name']='Not Assigned';
                $item['upazilla_name']='Not Assigned';
                $item['district_name']='Not Assigned';
                $item['territory_name']='Not Assigned';
                $item['zone_name']='Not Assigned';
                $item['division_name']='Not Assigned';
            }
        }
        $this->jsonReturn($items);

    }

}
