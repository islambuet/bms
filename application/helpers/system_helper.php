<?php
class System_helper
{
    public static function display_date($time)
    {
        if(is_numeric($time))
        {
            return date('d-M-Y',$time);
        }
        else
        {
            return '';
        }
    }
    public static function display_date_time($time)
    {
        if(is_numeric($time))
        {
            return date('d-M-Y h:i:s A',$time);
        }
        else
        {
            return '';
        }
    }
    public static function get_time($str)
    {
        $time=strtotime($str);
        if($time===false)
        {
            return 0;
        }
        else
        {
            return $time;
        }
    }

    public static function upload_file($save_dir="images")
    {
        $CI = & get_instance();
        $CI->load->library('upload');
        $config=array();
        $config['upload_path'] = FCPATH.$save_dir;
        $config['allowed_types'] = 'gif|jpg|png';
        $config['max_size'] = $CI->config->item("max_file_size");
        $config['overwrite'] = false;
        $config['remove_spaces'] = true;

        $uploaded_files=array();
        foreach ($_FILES as $key => $value)
        {
            if(strlen($value['name'])>0)
            {
                $CI->upload->initialize($config);
                if (!$CI->upload->do_upload($key))
                {
                    $uploaded_files[$key]=array("status"=>false,"message"=>$value['name'].': '.$CI->upload->display_errors());
                }
                else
                {
                    $uploaded_files[$key]=array("status"=>true,"info"=>$CI->upload->data());
                }

            }
        }

        return $uploaded_files;
    }
    public static function invalid_try($action='',$action_id='',$other_info='')
    {
        $CI =& get_instance();
        $user = User_helper::get_user();
        $time=time();
        $data=array();
        $data['user_id']=$user->user_id;
        $data['controller']=$CI->router->class;
        $data['action']=$action;
        $data['action_id']=$action_id;
        $data['other_info']=$other_info;
        $data['date_created']=$time;
        $data['date_created_string']=System_helper::display_date($time);
        $CI->db->insert('bms_history_hack', $data);
    }
    public static function get_users_info($user_ids)
    {
        $CI =& get_instance();
        $db_login=$CI->load->database('armalik_login',TRUE);
        $db_login->from($CI->config->item('table_setup_user_info').' user_info');
        if(sizeof($user_ids)>0)
        {
            $db_login->where_in('user_id',$user_ids);
        }
        $db_login->where('revision',1);
        $results=$db_login->get()->result_array();
        $users=array();
        foreach($results as $result)
        {
            $users[$result['user_id']]=$result;
        }
        return $users;

    }
    public static function get_fiscal_years()
    {
        $CI =& get_instance();
        $results=Query_helper::get_info($CI->config->item('ems_basic_setup_fiscal_year'),array('id value','name text','date_start','date_end'),array('status ="'.$CI->config->item('system_status_active').'"'),0,0,array('id ASC'));
        $fiscal_years=array();
        $time=time();
        if(sizeof($results)>$CI->config->item('num_year_prediction'))
        {
            $budget_year=$results[0];
            for($i=0;$i<(sizeof($results)-$CI->config->item('num_year_prediction'));$i++)
            {
                $fiscal_years[]=$results[$i];
                if($results[$i]['date_start']<=$time && $results[$i]['date_end']>=$time)
                {
                    $budget_year=$results[$i+1];
                }
            }
            return array('budget_year'=>$budget_year,'years'=>$fiscal_years);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$CI->lang->line('MSG_SETUP_MORE_FISCAL_YEAR');
            $CI->jsonReturn($ajax);
            return null;
        }
    }

}