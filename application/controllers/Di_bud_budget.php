<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Di_bud_budget extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Di_bud_budget');
        $this->locations=User_helper::get_locations();
        if(!is_array($this->locations))
        {
            if($this->locations=='wrong')
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line('MSG_LOCATION_INVALID');
                $this->jsonReturn($ajax);
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line('MSG_LOCATION_NOT_ASSIGNED');
                $this->jsonReturn($ajax);
            }

        }
        if((isset($this->permissions['edit'])&&($this->permissions['edit']==1))||(isset($this->permissions['add'])&&($this->permissions['add']==1)))
        {
            $this->permissions['forward']=1;
        }
        $this->controller_url='di_bud_budget';

    }

    public function index($action="list",$id=0)
    {
        if($action=="list")
        {
            $this->system_list();
        }
        elseif($action=="edit")
        {
            $this->system_edit($id);
        }
        elseif($action=="get_budget_form")
        {
            $this->system_get_budget_form();
        }
        elseif($action=="get_budget_form_items")
        {
            $this->system_get_budget_form_items();
        }
        elseif($action=="save")
        {
            $this->system_save();
        }
        elseif($action=="forward")
        {
            $this->system_forward($id);
        }
        else
        {
            $this->system_list();
        }
    }

    private function system_list()
    {
        if(isset($this->permissions['view'])&&($this->permissions['view']==1))
        {
            $data['title']="Budget List";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("di_bud_budget/list",$data,true));
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
        if((isset($this->permissions['edit'])&&($this->permissions['edit']==1))||(isset($this->permissions['add'])&&($this->permissions['add']==1)))
        {
            if(($this->input->post('id')))
            {
                $id=$this->input->post('id');
            }
            $this->db->from($this->config->item('table_di_budget').' db');
            $this->db->select('db.id,db.status_forward');
            //$this->db->select('zone.id zone_id,zone.name zone_name');
            $this->db->select('division.id division_id,division.name division_name');

            //$this->db->join($this->config->item('ems_setup_location_zones').' zone','zone.id = zb.zone_id','INNER');
            $this->db->join($this->config->item('ems_setup_location_divisions').' division','division.id = db.division_id','INNER');
            $this->db->where('db.id',$id);
            $data['budget']=$this->db->get()->row_array();
            if(!$data['budget'])
            {
                System_helper::invalid_try($this->config->item('system_edit_not_exists'),$id);
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->jsonReturn($ajax);
            }
            if($data['budget']['status_forward']===$this->config->item('system_status_yes'))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_ALREADY_FORWARDED");
                $this->jsonReturn($ajax);
                die();
            }
            if(!$this->check_my_editable($data['budget']))
            {
                System_helper::invalid_try($this->config->item('system_edit_others'),$id);
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->jsonReturn($ajax);
            }
            $data['title']="Search";
            $data['crops']=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array());
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("di_bud_budget/search",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit/'.$id);
            $this->jsonReturn($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
        }
    }
    private function system_get_budget_form()
    {
        $user = User_helper::get_user();
        $time=time();


        $data['crop_id']=$this->input->post('crop_id');
        $data['setup_id']=$this->input->post('setup_id');
        $setup=Query_helper::get_info($this->config->item('table_di_budget'),'*',array('id ='.$data['setup_id']),1);
        $results=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('id ASC'));
        $fiscal_years=array();
        foreach($results as $result)
        {
            $fiscal_years[$result['value']]=$result;
        }
        $years=array();
        for($i=0;$i<=$this->config->item('num_year_prediction');$i++)
        {
            $years['year'.$i.'_id']=$fiscal_years[$setup['year'.$i.'_id']];
        }
        //zones
        $data['areas']=Query_helper::get_info($this->config->item('ems_setup_location_zones'),array('id value','name text'),array('division_id ='.$setup['division_id']),0,0,array('ordering'));
        $keys=',';
        $keys.="setup_id:'".$data['setup_id']."',";
        $keys.="crop_id:'".$data['crop_id']."',";
        $data['keys']=trim($keys,',');
        $data['years']=$years;
        $data['title']="DI Budget";
        $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("di_bud_budget/add_edit",$data,true));
        if($this->message)
        {
            $ajax['system_message']=$this->message;
        }
        $this->jsonReturn($ajax);

    }
    private function system_get_budget_form_items()
    {
        $crop_id=$this->input->post('crop_id');
        $setup_id=$this->input->post('setup_id');
        $results=Query_helper::get_info($this->config->item('table_di_bud_budget_target'),'*',array('setup_id ='.$setup_id));
        $old_items=array();
        foreach($results as $result)
        {
            $old_items[$result['variety_id']]=$result;
        }

        $setup=Query_helper::get_info($this->config->item('table_di_budget'),'*',array('id ='.$setup_id),1);

        //zones
        $areas=Query_helper::get_info($this->config->item('ems_setup_location_zones'),array('id','name text'),array('division_id ='.$setup['division_id']),0,0,array('ordering'));
        $total_types=array();
        $total_crop=array();
        foreach($areas as $area)
        {
            $total_types['area'][$area['id']]=0;
            $total_crop['area'][$area['id']]=0;
        }
        for($i=0;$i<=$this->config->item('num_year_prediction');$i++)
        {
            $total_types['year'.$i.'_area_total_quantity']=0;
            $total_types['year'.$i.'_budget_quantity']='';
            $total_crop['year'.$i.'_area_total_quantity']=0;
            $total_crop['year'.$i.'_budget_quantity']='';
        }

        $this->db->from($this->config->item('table_zi_bud_budget_target').' zbt');
        $this->db->select('zbt.*');
        $this->db->select('zb.zone_id');
        $this->db->join($this->config->item('table_zi_budget').' zb','zb.id = zbt.setup_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_zones').' zone','zone.id = zb.zone_id','INNER');
        $this->db->where('zb.status_forward',$this->config->item('system_status_yes'));
        $this->db->where('zone.division_id',$setup['division_id']);
        $this->db->where('zb.year0_id',$setup['year0_id']);
        $results=$this->db->get()->result_array();
        $old_area_items=array();

        foreach($results as $result)
        {
            $old_area_items[$result['variety_id']][$result['zone_id']]=$result;
        }


        $this->db->from($this->config->item('ems_setup_classification_varieties').' v');
        $this->db->select('v.id,v.name');
        $this->db->select('type.name crop_type_name');
        $this->db->join($this->config->item('ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->where('v.whose','ARM');
        $this->db->where('v.status =',$this->config->item('system_status_active'));
        $this->db->where('type.crop_id',$crop_id);
        $this->db->order_by('type.ordering','ASC');
        $this->db->order_by('v.ordering','ASC');

        $results=$this->db->get()->result_array();

        $items=array();
        $prev_type='';
        foreach($results as $i=>$result)
        {
            $item=array();
            if($i>0)
            {
                if($prev_type!=$result['crop_type_name'])
                {
                    $total_item=array();
                    $total_item['crop_type_name']='';
                    $total_item['variety_name']='Type Total';
                    $total_item['variety_id']='';
                    $items[]=$this->get_form_row($total_item,$total_types);
                    foreach($areas as $area)
                    {
                        $total_types['area'][$area['id']]=0;
                    }
                    for($i=0;$i<=$this->config->item('num_year_prediction');$i++)
                    {
                        $total_types['year'.$i.'_area_total_quantity']=0;;
                    }

                    $item['crop_type_name']=$result['crop_type_name'];
                    $prev_type=$result['crop_type_name'];
                }
                else
                {
                    $item['crop_type_name']='';
                }
            }
            else
            {
                $prev_type=$result['crop_type_name'];
                $item['crop_type_name']=$result['crop_type_name'];
            }
            $item['variety_name']=$result['name'];
            $item['variety_id']=$result['id'];
            $row_quantity=array();
            for($i=0;$i<=$this->config->item('num_year_prediction');$i++)
            {
                $row_quantity['year'.$i.'_area_total_quantity']=0;
                $row_quantity['year'.$i.'_budget_quantity']='';
            }
            foreach($areas as $area)
            {
                if(isset($old_area_items[$result['id']][$area['id']]))
                {
                    if($old_area_items[$result['id']][$area['id']]['year0_budget_quantity']>0)
                    {
                        $row_quantity['area'][$area['id']]=$old_area_items[$result['id']][$area['id']]['year0_budget_quantity'];
                        $total_types['area'][$area['id']]+=$old_area_items[$result['id']][$area['id']]['year0_budget_quantity'];
                        $total_crop['area'][$area['id']]+=$old_area_items[$result['id']][$area['id']]['year0_budget_quantity'];
                    }
                    for($i=0;$i<=$this->config->item('num_year_prediction');$i++)
                    {
                        if($old_area_items[$result['id']][$area['id']]['year'.$i.'_budget_quantity']>0)
                        {
                            $row_quantity['year'.$i.'_area_total_quantity']+=$old_area_items[$result['id']][$area['id']]['year'.$i.'_budget_quantity'];
                            $total_types['year'.$i.'_area_total_quantity']+=$old_area_items[$result['id']][$area['id']]['year'.$i.'_budget_quantity'];
                            $total_crop['year'.$i.'_area_total_quantity']+=$old_area_items[$result['id']][$area['id']]['year'.$i.'_budget_quantity'];
                        }
                    }
                }
                else
                {
                    $row_quantity['area'][$area['id']]=0;
                }

            }
            for($i=0;$i<=$this->config->item('num_year_prediction');$i++)
            {
                $quantity='';
                $editable=false;
                if((isset($old_items[$result['id']]['year'.$i.'_budget_quantity']))&&(($old_items[$result['id']]['year'.$i.'_budget_quantity'])>0))
                {
                    $quantity=$old_items[$result['id']]['year'.$i.'_budget_quantity'];
                    if(isset($this->permissions['edit'])&&($this->permissions['edit']==1))
                    {
                        $editable=true;
                    }
                    else
                    {
                        $editable=false;
                    }
                }
                else
                {
                    $editable=true;
                }
                $row_quantity['year'.$i.'_budget_quantity']=$quantity;
                $row_quantity['year'.$i.'_budget_quantity_editable']=$editable;
            }
            $items[]=$this->get_form_row($item,$row_quantity);
        }
        $total_item=array();
        $total_item['crop_type_name']='';
        $total_item['variety_name']='Type Total';
        $total_item['variety_id']='';
        $items[]=$this->get_form_row($total_item,$total_types);
        $total_item=array();
        $total_item['crop_type_name']='Crop Total';
        $total_item['variety_name']='';
        $total_item['variety_id']='';
        $items[]=$this->get_form_row($total_item,$total_crop);

        $this->jsonReturn($items);

    }
    private function get_form_row($item,$row_quantity)
    {

        $row=array();
        $row['crop_type_name']=$item['crop_type_name'];
        $row['variety_name']=$item['variety_name'];
        $row['variety_id']=$item['variety_id'];

        foreach($row_quantity['area'] as $id=>$quantity)
        {
            if($quantity>0)
            {
                $row['area_quantity_'.$id]=$quantity;
            }
            else
            {
                $row['area_quantity_'.$id]='';
            }

        }
        for($i=0;$i<=$this->config->item('num_year_prediction');$i++)
        {
            if($row_quantity['year'.$i.'_area_total_quantity']>0)
            {
                $row['year'.$i.'_area_total_quantity']=$row_quantity['year'.$i.'_area_total_quantity'];
                $row['year'.$i.'_budget_quantity']=$row_quantity['year'.$i.'_budget_quantity'];
                if($item['variety_id']>0)
                {
                    $row['year'.$i.'_budget_quantity_editable']=$row_quantity['year'.$i.'_budget_quantity_editable'];
                }
                else
                {
                    $row['year'.$i.'_budget_quantity_editable']=false;
                }

            }
            else
            {
                $row['year'.$i.'_area_total_quantity']='';
                $row['year'.$i.'_budget_quantity']='';
                $row['year'.$i.'_budget_quantity_editable']=false;
            }

        }

        return $row;

    }
    private function system_save()
    {
        $user = User_helper::get_user();
        $time=time();
        $setup_id=$this->input->post('setup_id');
        $setup=Query_helper::get_info($this->config->item('table_di_budget'),'*',array('id ='.$setup_id),1);
        if($setup)
        {
            if($setup['status_forward']===$this->config->item('system_status_yes'))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_ALREADY_FORWARDED");
                $this->jsonReturn($ajax);
                die();
            }
        }
        $items=$this->input->post('items');
        $this->db->trans_start();
        if(sizeof($items)>0)
        {
            $results=Query_helper::get_info($this->config->item('table_di_bud_budget_target'),'*',array('setup_id ='.$setup_id));
            $old_items=array();

            foreach($results as $result)
            {
                $old_items[$result['variety_id']]=$result;
            }

            foreach($items as $variety_id=>$item)
            {
                $data=array();
                for($i=0;$i<=$this->config->item('num_year_prediction');$i++)
                {
                    if((isset($item['year'.$i.'_budget_quantity']))&&($item['year'.$i.'_budget_quantity']>0))
                    {
                        $data['year'.$i.'_budget_quantity']=$item['year'.$i.'_budget_quantity'];
                    }
                }
                if($data)
                {
                    $data['setup_id']=$setup_id;
                    $data['variety_id']=$variety_id;
                    if(isset($old_items[$variety_id]))
                    {
                        $data['user_updated'] = $user->user_id;
                        $data['date_updated'] = $time;
                        Query_helper::update($this->config->item('table_di_bud_budget_target'),$data,array("id = ".$old_items[$variety_id]['id']));
                    }
                    else
                    {
                        $data['user_created'] = $user->user_id;
                        $data['date_created'] = $time;
                        Query_helper::add($this->config->item('table_di_bud_budget_target'),$data);
                    }
                }
            }
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
    private function system_forward()
    {
        $user = User_helper::get_user();
        $time=time();
        if(($this->input->post('id')))
        {
            $id=$this->input->post('id');
        }
        $this->db->from($this->config->item('table_di_budget').' db');
        $this->db->select('db.*');
        $this->db->where('db.id',$id);
        $setup=$this->db->get()->row_array();
        if($setup)
        {
            if($setup['status_forward']===$this->config->item('system_status_yes'))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_ALREADY_FORWARDED");
                $this->jsonReturn($ajax);
                die();
            }
        }
        else
        {
            System_helper::invalid_try('Invalid forward',$id);
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
        }
        $this->db->trans_start();
        $hom_setup=Query_helper::get_info($this->config->item('table_hom_budget'),'*',array('year0_id ='.$setup['year0_id']),1);
        if($hom_setup)
        {
            if($hom_setup['status_forward']===$this->config->item('system_status_yes'))
            {
                $ajax['status']=false;
                $ajax['system_message']='HOM already Forwarded.You cannot forward more';
                $this->jsonReturn($ajax);
                die();
            }
        }
        else
        {

            $data=array();
            for($i=0;$i<=$this->config->item('num_year_prediction');$i++)
            {
                $data['year'.$i.'_id']=$setup['year'.$i.'_id'];
            }
            $data['user_created'] = $user->user_id;
            $data['date_created'] = $time;
            Query_helper::add($this->config->item('table_hom_budget'),$data);
        }
        $data=array();
        $data['status_forward']=$this->config->item('system_status_yes');
        $data['user_updated'] = $user->user_id;
        $data['date_updated'] = $time;
        Query_helper::update($this->config->item('table_di_budget'),$data,array("id = ".$id));
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $this->message='Forwarded Successfully';
            $this->system_list();
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
            $this->jsonReturn($ajax);
        }
    }
    private function check_my_editable($customer)
    {
        if(($this->locations['division_id']>0)&&($this->locations['division_id']!=$customer['division_id']))
        {
            return false;
        }
        return true;
    }
    public function get_items()
    {
        $items=array();
        $this->db->from($this->config->item('table_di_budget').' db');
        $this->db->select('db.id,db.status_forward');
        $this->db->select('division.name division_name');
        $this->db->select('fy.name fiscal_year');
        $this->db->select('COUNT(DISTINCT zone.id) num_total_zones');
        $this->db->select('COUNT(DISTINCT zb.id) num_forwarded_zones');
        $this->db->select('COUNT(DISTINCT zbt.variety_id) num_varieties_zi');
        $this->db->select('COUNT(DISTINCT dbt.variety_id) num_varieties_di');

        //$this->db->join($this->config->item('ems_setup_location_zones').' zone','zone.id = zb.zone_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_divisions').' division','division.id = db.division_id','INNER');

        $this->db->join($this->config->item('ems_basic_setup_fiscal_year').' fy','fy.id = db.year0_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_zones').' zone','division.id = zone.division_id','INNER');

        $this->db->join($this->config->item('ems_setup_location_zones').' z1','division.id = z1.division_id','INNER');
        $this->db->join($this->config->item('table_zi_budget').' zb','zb.zone_id = z1.id and zb.year0_id = db.year0_id','INNER');
        $this->db->where('zb.status_forward',$this->config->item('system_status_yes'));

        $this->db->join($this->config->item('table_zi_bud_budget_target').' zbt','zbt.setup_id = zb.id','INNER');
        $this->db->join($this->config->item('table_di_bud_budget_target').' dbt','dbt.setup_id = db.id','LEFT');

        if($this->locations['division_id']>0)
        {
            $this->db->where('division.id',$this->locations['division_id']);
        }
        $this->db->group_by(array('db.id'));
        $this->db->order_by('db.id DESC');
        $items=$this->db->get()->result_array();
        $this->jsonReturn($items);
    }

}
