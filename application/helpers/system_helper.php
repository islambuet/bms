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
    /*public static function pagination_config($base_url, $total_rows, $segment)
    {
        $CI =& get_instance();

        $config["base_url"] = $base_url;
        $config["total_rows"] = $total_rows;
        $config["per_page"] = $CI->config->item('view_per_page');
        $config['num_links'] = $CI->config->item('links_per_page');
        $config['use_page_numbers'] = true;
        $config['cur_tag_open'] = '<li class="active"><a href="#">';
        $config['cur_tag_close'] = '</a></li>';

        $config['first_tag_open'] = '<li>';
        $config['first_tag_close'] = '</li>';

        $config['prev_tag_open'] = '<li>';
        $config['prev_tag_close'] = '</li>';

        $config['full_tag_open'] = '<ul class="pagination">';
        $config['full_tag_close'] = '</ul>';

        $config['num_tag_open'] = '<li>';
        $config['num_tag_close'] = '</li>';
        $config['next_tag_open'] = '<li>';
        $config['next_tag_close'] = '</li>';
        $config['last_tag_open'] = '<li>';
        $config['last_tag_close'] = '</li>';
        $config['uri_segment'] = $segment;
        return $config;
    }


    public static function get_pdf($html)
    {
        include(FCPATH."mpdf60/mpdf.php");
        $mpdf=new mPDF();
        $mpdf->SetDisplayMode('fullpage');

        $mpdf->WriteHTML($html);
        $mpdf->Output();
        exit;

    }*/


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
    public static function get_bonus_info($variety_id,$pack_size_id,$quantity)
    {
        $CI =& get_instance();
        $CI->db->from($CI->config->item('table_setup_classification_variety_bonus_details').' vbd');
        $CI->db->select('vbd.*');
        $CI->db->select('bonus_pack.name bonus_pack_size_name');
        $CI->db->join($CI->config->item('table_setup_classification_variety_bonus').' vb','vb.id = vbd.bonus_id','INNER');
        $CI->db->join($CI->config->item('table_setup_classification_vpack_size').' bonus_pack','bonus_pack.id = vbd.bonus_pack_size_id','INNER');
        $CI->db->where("vb.variety_id",$variety_id);
        $CI->db->where("vb.pack_size_id",$pack_size_id);
        $CI->db->where("vbd.revision",1);
        $CI->db->order_by('vbd.quantity_min DESC');
        $results=$CI->db->get()->result_array();
        $info=array();
        if($results)
        {
            foreach($results as $result)
            {
                if($result['quantity_min']<=$quantity)
                {
                    $info['bonus_details_id']=$result['id'];
                    $info['bonus_id']=$result['bonus_id'];
                    $info['quantity_min']=$result['quantity_min'];
                    $info['bonus_pack_size_id']=$result['bonus_pack_size_id'];
                    $info['bonus_pack_size_name']=$result['bonus_pack_size_name'];
                    $info['quantity_bonus']=$result['quantity_bonus'];
                    $info['total_weight']=$result['quantity_bonus']*$result['bonus_pack_size_name']/1000;
                    break;
                }

            }
        }
        if(!$info)
        {

            $info['bonus_details_id']=0;
            $info['bonus_id']=0;
            $info['quantity_min']=0;
            $info['bonus_pack_size_id']=0;
            $info['bonus_pack_size_name']='N/A';
            $info['quantity_bonus']=0;
            $info['total_weight']=0;

        }
        return $info;
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

}