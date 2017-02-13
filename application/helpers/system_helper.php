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
    public static function upload_file($save_dir='images',$allowed_types='gif|jpg|png')
    {
        $CI= & get_instance();
        $CI->load->library('upload');
        $config=array();
        $config['upload_path']=FCPATH.$save_dir;
        $config['allowed_types']=$allowed_types;
        $config['max_size']=$CI->config->item('max_file_size');
        $config['overwrite']=false;
        $config['remove_spaces']=true;

        $uploaded_files=array();
        foreach ($_FILES as $key=>$value)
        {
            if(strlen($value['name'])>0)
            {
                $CI->upload->initialize($config);
                if($CI->upload->do_upload($key))
                {
                    $uploaded_files[$key]=array('status'=>true,'info'=>$CI->upload->data());
                }
                else
                {
                    $uploaded_files[$key]=array('status'=>false,'message'=>$value['name'].': '.$CI->upload->display_errors());
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
        $CI->db->insert($CI->config->item('table_system_history_hack'), $data);
    }
    public static function get_users_info($user_ids=array())
    {
        $CI=& get_instance();
        $CI->db->from($CI->config->item('system_db_login').'.'.$CI->config->item('table_login_setup_user_info'));
        if(sizeof($user_ids)>0)
        {
            $CI->db->where_in('user_id',$user_ids);
        }
        $CI->db->where('revision',1);
        $results=$CI->db->get()->result_array();
        $users=array();
        foreach($results as $result)
        {
            $users[$result['user_id']]=$result;
        }
        return $users;

    }
    /*public static function get_fiscal_years()
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
    public static function get_stocks($crop_id=0,$type_id=0,$variety_id=0)
    {
        $CI = & get_instance();
        $stocks=array();
        //+get stock in
        $CI->db->from($CI->config->item('ems_stockin_varieties').' sinv');
        $CI->db->select('sinv.variety_id');
        $CI->db->select('SUM(sinv.quantity*pack.name) stock_in');

        $CI->db->join($CI->config->item('ems_setup_classification_vpack_size').' pack','pack.id = sinv.pack_size_id','INNER');
        $CI->db->join($CI->config->item('ems_setup_classification_varieties').' v','v.id = sinv.variety_id','INNER');
        $CI->db->join($CI->config->item('ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        if($crop_id>0)
        {
            $CI->db->where('type.crop_id',$crop_id);
        }
        if($type_id>0)
        {
            $CI->db->where('type.id',$type_id);
        }
        if($variety_id>0)
        {
            $CI->db->where('v.id',$variety_id);
        }
        $CI->db->group_by('sinv.variety_id');
        $results=$CI->db->get()->result_array();
        foreach($results as $result)
        {
            $stocks[$result['variety_id']]['stock_in']=$result['stock_in'];
            $stocks[$result['variety_id']]['excess']=0;
            $stocks[$result['variety_id']]['stock_out']=0;
            $stocks[$result['variety_id']]['sales']=0;
            $stocks[$result['variety_id']]['current_stock']=$result['stock_in'];
        }

        //+excess Inventory
        $CI->db->from($CI->config->item('ems_stockin_excess_inventory').' sinv');
        $CI->db->select('sinv.variety_id');
        $CI->db->select('SUM(sinv.quantity*pack.name) stock_in');

        $CI->db->join($CI->config->item('ems_setup_classification_vpack_size').' pack','pack.id = sinv.pack_size_id','INNER');
        $CI->db->join($CI->config->item('ems_setup_classification_varieties').' v','v.id = sinv.variety_id','INNER');
        $CI->db->join($CI->config->item('ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        if($crop_id>0)
        {
            $CI->db->where('type.crop_id',$crop_id);
        }
        if($type_id>0)
        {
            $CI->db->where('type.id',$type_id);
        }
        if($variety_id>0)
        {
            $CI->db->where('v.id',$variety_id);
        }
        $CI->db->group_by('sinv.variety_id');
        $results=$CI->db->get()->result_array();
        foreach($results as $result)
        {
            $stocks[$result['variety_id']]['excess']=$result['stock_in'];
            $stocks[$result['variety_id']]['current_stock']+=$result['stock_in'];
        }
        //-stock out all
        $CI->db->from($CI->config->item('ems_stockout').' sinv');
        $CI->db->select('sinv.variety_id');
        $CI->db->select('SUM(sinv.quantity*pack.name) stock_out');

        $CI->db->join($CI->config->item('ems_setup_classification_vpack_size').' pack','pack.id = sinv.pack_size_id','INNER');
        $CI->db->join($CI->config->item('ems_setup_classification_varieties').' v','v.id = sinv.variety_id','INNER');
        $CI->db->join($CI->config->item('ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        if($crop_id>0)
        {
            $CI->db->where('type.crop_id',$crop_id);
        }
        if($type_id>0)
        {
            $CI->db->where('type.id',$type_id);
        }
        if($variety_id>0)
        {
            $CI->db->where('v.id',$variety_id);
        }
        $CI->db->group_by('sinv.variety_id');
        $results=$CI->db->get()->result_array();
        foreach($results as $result)
        {
            $stocks[$result['variety_id']]['stock_out']=$result['stock_out'];
            $stocks[$result['variety_id']]['current_stock']-=$result['stock_out'];
        }
        //-sales and sales return

        $CI->db->from($CI->config->item('ems_sales_po_details').' spd');
        $CI->db->select('variety_id');
        $CI->db->select('SUM((spd.quantity-spd.quantity_return)*spd.pack_size) sales');
        $CI->db->join($CI->config->item('ems_sales_po').' sp','sp.id =spd.sales_po_id','INNER');

        $CI->db->join($CI->config->item('ems_setup_classification_varieties').' v','v.id = spd.variety_id','INNER');
        $CI->db->join($CI->config->item('ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');

        $CI->db->where('sp.status_approved',$CI->config->item('system_status_po_approval_approved'));
        $CI->db->where('spd.revision',1);

        if($crop_id>0)
        {
            $CI->db->where('type.crop_id',$crop_id);
        }
        if($type_id>0)
        {
            $CI->db->where('type.id',$type_id);
        }
        if($variety_id>0)
        {
            $CI->db->where('v.id',$variety_id);
        }
        $CI->db->group_by('variety_id');
        $results=$CI->db->get()->result_array();

        foreach($results as $result)
        {
            $stocks[$result['variety_id']]['sales']=$result['sales'];
            $stocks[$result['variety_id']]['current_stock']-=$result['sales'];
        }
        //-sales bonus and bonus return
        $CI->db->from($CI->config->item('ems_sales_po_details').' spd');
        $CI->db->select('variety_id');
        $CI->db->select('SUM((quantity_bonus-quantity_bonus_return)*bonus_pack_size) sales');
        $CI->db->join($CI->config->item('ems_sales_po').' sp','sp.id =spd.sales_po_id','INNER');

        $CI->db->join($CI->config->item('ems_setup_classification_varieties').' v','v.id = spd.variety_id','INNER');
        $CI->db->join($CI->config->item('ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');

        $CI->db->where('bonus_details_id >',0);
        $CI->db->where('sp.status_approved',$CI->config->item('system_status_po_approval_approved'));
        $CI->db->where('spd.revision',1);
        if($crop_id>0)
        {
            $CI->db->where('type.crop_id',$crop_id);
        }
        if($type_id>0)
        {
            $CI->db->where('type.id',$type_id);
        }
        if($variety_id>0)
        {
            $CI->db->where('v.id',$variety_id);
        }
        $CI->db->group_by('variety_id');
        $results=$CI->db->get()->result_array();

        foreach($results as $result)
        {
            $stocks[$result['variety_id']]['sales']+=$result['sales'];
            $stocks[$result['variety_id']]['current_stock']-=$result['sales'];
        }
        return $stocks;

    }*/

}