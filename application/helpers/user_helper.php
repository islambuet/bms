<?php

class User_helper
{
    public static $logged_user = null;
    function __construct($id)
    {
        $CI = & get_instance();
        $db_login=$CI->load->database('armalik_login',TRUE);
        $db_login->from($CI->config->item('table_setup_user_info').' user_info');
        $db_login->where('user_id',$id);
        $db_login->where('revision',1);

        $user=$db_login->get()->row();
        if ($user)
        {
            foreach ($user as $key => $value)
            {
                $this->$key = $value;
            }
        }
        $this->user_group=0;
        $assigned_group=$CI->db->get_where($CI->config->item('table_system_assigned_group'), array('user_id' => $id,'revision'=>1))->row();
        if($assigned_group)
        {
            $this->user_group=$assigned_group->user_group;
        }
    }
    public static function login($username, $password)
    {
        //also need to check if it has access to ems
        $CI = & get_instance();
        $db_login=$CI->load->database('armalik_login',TRUE);
        $db_login->from($CI->config->item('table_setup_user').' user');
        $db_login->select('user.id');
        $db_login->join($CI->config->item('table_setup_users_other_sites').' uos','uos.user_id =user.id','INNER');
        $db_login->join($CI->config->item('table_system_other_sites').' os','uos.site_id =os.id','INNER');
        $db_login->where('uos.revision',1);
        $db_login->where('os.short_name',$CI->config->item('system_site_short_name'));

        $db_login->where('user.user_name',$username);
        $db_login->where('user.password',md5($password));
        $db_login->where('user.status',$CI->config->item('system_status_active'));

        $user=$db_login->get()->row();

        if ($user)
        {
            $CI->session->set_userdata("user_id", $user->id);
            return TRUE;
        }
        else
        {
            return FALSE;
        }
    }



    public static function get_user()
    {
        $CI = & get_instance();
        if (User_helper::$logged_user) {
            return User_helper::$logged_user;
        }
        else
        {
            if($CI->session->userdata("user_id")!="")
            {
                $db_login=$CI->load->database('armalik_login',TRUE);
                $db_login->from($CI->config->item('table_setup_user').' user');
                $db_login->select('user.id');
                $db_login->join($CI->config->item('table_setup_users_other_sites').' uos','uos.user_id =user.id','INNER');
                $db_login->join($CI->config->item('table_system_other_sites').' os','uos.site_id =os.id','INNER');
                $db_login->where('uos.revision',1);
                $db_login->where('os.short_name',$CI->config->item('system_site_short_name'));

                $db_login->where('user.id',$CI->session->userdata('user_id'));
                $db_login->where('user.status',$CI->config->item('system_status_active'));
                $user=$db_login->get()->row();

                if($user)
                {
                    User_helper::$logged_user = new User_helper($CI->session->userdata('user_id'));
                    return User_helper::$logged_user;
                }
                return null;
            }
            else
            {
                return null;
            }

        }
    }
    public static function get_html_menu()
    {
        $user=User_helper::get_user();
        $CI = & get_instance();
        $CI->db->order_by('ordering');
        $tasks=$CI->db->get($CI->config->item('table_system_task'))->result_array();

        $roles=Query_helper::get_info($CI->config->item('table_system_user_group_role'),'*',array('revision =1','view =1','user_group_id ='.$user->user_group));
        $role_data=array();
        foreach($roles as $role)
        {
            $role_data[]=$role['task_id'];

        }
        $menu_data=array();
        foreach($tasks as $task)
        {
            if($task['type']=='TASK')
            {
                if(in_array($task['id'],$role_data))
                {
                    $menu_data['items'][$task['id']]=$task;
                    $menu_data['children'][$task['parent']][]=$task['id'];
                }
            }
            else
            {
                $menu_data['items'][$task['id']]=$task;
                $menu_data['children'][$task['parent']][]=$task['id'];
            }


        }

        $html='';
        if(isset($menu_data['children'][0]))
        {
            foreach($menu_data['children'][0] as $child)
            {
                $html.=User_helper::get_html_submenu($child,$menu_data,1);
            }
        }
        return $html;



        //return User_helper::get_html_submenu(0,$menu_data,1);

    }
    public static function get_html_submenu($parent,$menu_data,$level)
    {
        if(isset($menu_data['children'][$parent]))
        {
            $sub_html='';
            foreach($menu_data['children'][$parent] as $child)
            {
                $sub_html.=User_helper::get_html_submenu($child,$menu_data,$level+1);

            }
            $html='';
            if($sub_html)
            {
                if($level==1)
                {
                    $html.='<li class="menu-item dropdown">';
                    $html.='<a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$menu_data['items'][$parent]['name'].'<b class="caret"></b></a>';
                }
                else
                {
                    $html.='<li class="menu-item dropdown dropdown-submenu">';
                    $html.='<a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$menu_data['items'][$parent]['name'].'</a>';
                }

                $html.='<ul class="dropdown-menu">';
                $html.=$sub_html;
                $html.='</ul></li>';
            }

            return $html;

        }
        else
        {
            if($menu_data['items'][$parent]['type']=='TASK')
            {
                return '<li><a href="'.site_url($menu_data['items'][$parent]['controller']).'">'.$menu_data['items'][$parent]['name'].'</a></li>';
            }
            else
            {
                return '';
            }

        }
    }
    public static function get_permission($controller_name)
    {
        $CI = & get_instance();
        $user=User_helper::get_user();
        $CI->db->from($CI->config->item('table_system_user_group_role').' ugr');
        $CI->db->select('ugr.*');

        $CI->db->join($CI->config->item('table_system_task').' task','task.id = ugr.task_id','INNER');
        $CI->db->like("controller",$controller_name,"after");
        $CI->db->where("user_group_id",$user->user_group);
        $CI->db->where("revision",1);
        $result=$CI->db->get()->row_array();
        return $result;
    }
    public static function get_locations()
    {
        $CI = & get_instance();
        $user=User_helper::get_user();
        $CI->db->from($CI->config->item('table_system_assigned_area').' aa');
        $CI->db->select('aa.*');
        $CI->db->select('union.name union_name');
        $CI->db->select('u.name upazilla_name');
        $CI->db->select('d.name district_name');
        $CI->db->select('t.name territory_name');
        $CI->db->select('zone.name zone_name');
        $CI->db->select('division.name division_name');
        $CI->db->join($CI->config->item('table_setup_location_unions').' union','union.id = aa.union_id','LEFT');
        $CI->db->join($CI->config->item('table_setup_location_upazillas').' u','u.id = aa.upazilla_id','LEFT');
        $CI->db->join($CI->config->item('table_setup_location_districts').' d','d.id = aa.district_id','LEFT');
        $CI->db->join($CI->config->item('table_setup_location_territories').' t','t.id = aa.territory_id','LEFT');
        $CI->db->join($CI->config->item('table_setup_location_zones').' zone','zone.id = aa.zone_id','LEFT');
        $CI->db->join($CI->config->item('table_setup_location_divisions').' division','division.id = aa.division_id','LEFT');
        $CI->db->where('aa.revision',1);
        $CI->db->where('aa.user_id',$user->user_id);
        $assigned_area=$CI->db->get()->row_array();

        if($assigned_area)
        {
            $CI->db->from($CI->config->item('table_system_assigned_area').' aa');
            if($assigned_area['division_id']>0)
            {
                $CI->db->join($CI->config->item('table_setup_location_divisions').' division','division.id = aa.division_id','INNER');
            }
            if($assigned_area['zone_id']>0)
            {
                $CI->db->join($CI->config->item('table_setup_location_zones').' zone','zone.division_id = division.id','INNER');
                $CI->db->where('zone.id',$assigned_area['zone_id']);
            }
            if($assigned_area['territory_id']>0)
            {
                $CI->db->join($CI->config->item('table_setup_location_territories').' t','t.zone_id = zone.id','INNER');
                $CI->db->where('t.id',$assigned_area['territory_id']);
            }
            if($assigned_area['district_id']>0)
            {
                $CI->db->join($CI->config->item('table_setup_location_districts').' d','d.territory_id = t.id','INNER');
                $CI->db->where('d.id',$assigned_area['district_id']);
            }
            if($assigned_area['upazilla_id']>0)
            {
                $CI->db->join($CI->config->item('table_setup_location_upazillas').' u','u.district_id = d.id','INNER');
                $CI->db->where('u.id',$assigned_area['upazilla_id']);
            }
            if($assigned_area['union_id']>0)
            {
                $CI->db->join($CI->config->item('table_setup_location_unions').' union','union.upazilla_id = u.id','INNER');
                $CI->db->where('union.id',$assigned_area['union_id']);
            }
            $CI->db->where('aa.revision',1);
            $CI->db->where('aa.user_id',$user->user_id);
            $info=$CI->db->get()->row_array();
            if(!$info)
            {
                return 'wrong';
            }
        }
        return $assigned_area;
    }
}