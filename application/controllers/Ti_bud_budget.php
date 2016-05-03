<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ti_bud_budget extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Ti_bud_budget');
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
        $this->controller_url='ti_bud_budget';

    }

    public function index($action="search",$id1=0,$id2=0,$id3=0)
    {
        if($action=="search")
        {
            $this->system_search();
        }
        elseif($action=="list")
        {
            $this->system_list();
        }
        elseif($action=="get_items")
        {
            $this->get_items($id1,$id2);
        }
        elseif($action=="edit")
        {
            $this->system_edit($id1,$id2,$id3);
        }
        elseif($action=="get_edit_items")
        {
            $this->system_get_edit_items();
        }
        elseif($action=="save")
        {
            $this->system_save();
        }
        elseif($action=="details")
        {
            $this->system_details($id1,$id2,$id3);
        }
        elseif($action=="get_details_form")
        {
            $this->system_get_details_form();
        }
        elseif($action=="get_details_items")
        {
            $this->system_get_details_items();
        }
        else
        {
            $this->system_list();
        }
    }
    private function system_search()
    {
        if(isset($this->permissions['view'])&&($this->permissions['view']==1))
        {

            $fy_info=System_helper::get_fiscal_years();
            $data['years']=$fy_info['years'];
            $data['budget']=array();
            $data['budget']['year0_id']=$fy_info['budget_year']['value'];
            $data['budget']['division_id']=$this->locations['division_id'];
            $data['budget']['zone_id']=$this->locations['zone_id'];
            $data['budget']['territory_id']=$this->locations['territory_id'];

            $data['divisions']=Query_helper::get_info($this->config->item('ems_setup_location_divisions'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));
            $data['zones']=array();
            $data['territories']=array();
            if($this->locations['division_id']>0)
            {
                $data['zones']=Query_helper::get_info($this->config->item('ems_setup_location_zones'),array('id value','name text'),array('division_id ='.$this->locations['division_id']));
                if($this->locations['zone_id']>0)
                {
                    $data['territories']=Query_helper::get_info($this->config->item('ems_setup_location_territories'),array('id value','name text'),array('zone_id ='.$this->locations['zone_id']));

                }
            }

            $data['title']="Search";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("ti_bud_budget/search",$data,true));
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
    private function system_list()
    {
        if(isset($this->permissions['view'])&&($this->permissions['view']==1))
        {
            $data['territory_id']=$this->input->post('territory_id');
            $data['year0_id']=$this->input->post('year0_id');
            $keys=',';
            $keys.="territory_id:'".$data['territory_id']."',";
            $keys.="year0_id:'".$data['year0_id']."',";
            $data['keys']=trim($keys,',');
            $data['title']="Crop List";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("ti_bud_budget/list",$data,true));
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
    private function get_items($territory_id,$year0_id)
    {

        $this->db->from($this->config->item('ems_setup_classification_crops').' crop');
        $this->db->select('crop.id,crop.name crop_name');
        $this->db->select('fti.status_forward');
        $this->db->join($this->config->item('table_forward_ti').' fti','fti.crop_id = crop.id and year0_id ='.$year0_id.' and territory_id ='.$territory_id,'LEFT');
        //$this->db->where('d.territory_id',$territory_id);

        $this->db->order_by('crop.ordering','ASC');
        $this->db->where('crop.status',$this->config->item('system_status_active'));
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            if(!$item['status_forward'])
            {
                $item['status_forward']=$this->config->item('system_status_no');
            }
        }
        $this->jsonReturn($items);
    }

    private function system_edit($territory_id,$year0_id,$crop_id)
    {
        if((isset($this->permissions['edit'])&&($this->permissions['edit']==1))||(isset($this->permissions['add'])&&($this->permissions['add']==1)))
        {
            if(($this->input->post('id')))
            {
                $crop_id=$this->input->post('id');
            }
            if((!isset($this->permissions['edit'])||($this->permissions['edit']!=1)))
            {
                //echo 'check if already forwarded';
                //if forwarded dont allow
            }
            $crop=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array('id ='.$crop_id),1);
            $data['years']=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"',' id >='.$year0_id),$this->config->item('num_year_prediction')+1,0,array('id ASC'));
            $data['territory_id']=$territory_id;
            $data['year0_id']=$year0_id;
            $data['crop_id']=$crop_id;
            $this->db->from($this->config->item('ems_csetup_customers').' cus');
            $this->db->select('cus.id value');
            $this->db->select('CONCAT(cus.customer_code," - ",cus.name) text');
            $this->db->join($this->config->item('ems_setup_location_districts').' d','d.id = cus.district_id','INNER');
            $this->db->where('d.territory_id',$territory_id);
            $this->db->order_by('cus.ordering','ASC');
            $this->db->where('cus.status',$this->config->item('system_status_active'));
            $data['areas']=$this->db->get()->result_array();//customers

            $keys=',';
            $keys.="territory_id:'".$territory_id."',";
            $keys.="year0_id:'".$year0_id."',";
            $keys.="crop_id:'".$crop_id."',";
            $data['keys']=trim($keys,',');


            $data['title']="TI Budget For ".$crop['text'].'('.$data['years'][0]['text'].')';
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("ti_bud_budget/add_edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url."/index/edit/".$territory_id.'/'.$year0_id.'/'.$crop_id);
            $this->jsonReturn($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
        }
    }
    private function system_get_edit_items()
    {
        $territory_id=$this->input->post('territory_id');
        $year0_id=$this->input->post('year0_id');
        $crop_id=$this->input->post('crop_id');
        $results=Query_helper::get_info($this->config->item('table_ti_bud_ti_bt'),'*',array('territory_id ='.$territory_id,'year0_id ='.$year0_id));
        $old_items=array();//ti budget
        foreach($results as $result)
        {
            $old_items[$result['variety_id']]=$result;
        }
        $this->db->from($this->config->item('ems_csetup_customers').' cus');
        $this->db->select('cus.id value');
        $this->db->join($this->config->item('ems_setup_location_districts').' d','d.id = cus.district_id','INNER');
        $this->db->where('d.territory_id',$territory_id);
        $this->db->order_by('cus.ordering','ASC');
        $this->db->where('cus.status',$this->config->item('system_status_active'));
        $areas=$this->db->get()->result_array();//customers

        $this->db->from($this->config->item('table_ti_bud_customer_bt').' cbt');
        $this->db->select('cbt.*');
        $this->db->where('cbt.territory_id',$territory_id);
        $this->db->where('cbt.year0_id',$year0_id);
        $results=$this->db->get()->result_array();
        $prev_area_items=array();//customer budget

        foreach($results as $result)
        {
            $prev_area_items[$result['variety_id']][$result['customer_id']]=$result;
        }

        $this->db->from($this->config->item('ems_setup_classification_varieties').' v');
        $this->db->select('v.id,v.name');
        $this->db->select('type.name type_name');
        $this->db->join($this->config->item('ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->where('v.whose','ARM');
        $this->db->where('v.status =',$this->config->item('system_status_active'));
        $this->db->where('type.crop_id',$crop_id);
        $this->db->order_by('type.ordering','ASC');
        $this->db->order_by('v.ordering','ASC');

        $results=$this->db->get()->result_array();


        $total_types=array();
        $total_crop=array();
        foreach($areas as $area)
        {
            $total_types['area'][$area['value']]=0;
            $total_crop['area'][$area['value']]=0;
        }
        for($i=0;$i<=$this->config->item('num_year_prediction');$i++)
        {
            $total_types['year'.$i.'_area_total_quantity']=0;
            $total_types['year'.$i.'_budget_quantity']=0;
            $total_crop['year'.$i.'_area_total_quantity']=0;
            $total_crop['year'.$i.'_budget_quantity']=0;
        }
        $count=0;
        $items=array();
        $prev_type='';
        foreach($results as $index=>$result)
        {
            $item=array();
            if($index>0)
            {
                if($prev_type!=$result['type_name'])
                {
                    $total_item=array();
                    $total_item['sl_no']='';
                    $total_item['type_name']='';
                    $total_item['variety_name']='Total Type';
                    $total_item['variety_id']='';
                    $items[]=$this->get_edit_row($total_item,$total_types);
                    foreach($areas as $area)
                    {
                        $total_types['area'][$area['value']]=0;
                    }
                    for($i=0;$i<=$this->config->item('num_year_prediction');$i++)
                    {
                        $total_types['year'.$i.'_area_total_quantity']=0;;
                    }
                    $count=0;
                    $item['type_name']=$result['type_name'];
                    $prev_type=$result['type_name'];
                }
                else
                {
                    $item['type_name']='';
                }
            }
            else
            {
                $prev_type=$result['type_name'];
                $item['type_name']=$result['type_name'];
            }

            $count++;
            $item['sl_no']=$count;
            $item['variety_id']=$result['id'];
            $item['variety_name']=$result['name'];

            $row_quantity=array();
            for($i=0;$i<=$this->config->item('num_year_prediction');$i++)
            {
                $row_quantity['year'.$i.'_area_total_quantity']=0;
                $row_quantity['year'.$i.'_budget_quantity']=0;
            }
            foreach($areas as $area)
            {
                if(isset($prev_area_items[$result['id']][$area['value']]))
                {
                    if($prev_area_items[$result['id']][$area['value']]['year0_budget_quantity']>0)
                    {
                        $row_quantity['area'][$area['value']]=$prev_area_items[$result['id']][$area['value']]['year0_budget_quantity'];
                        $total_types['area'][$area['value']]+=$prev_area_items[$result['id']][$area['value']]['year0_budget_quantity'];
                        $total_crop['area'][$area['value']]+=$prev_area_items[$result['id']][$area['value']]['year0_budget_quantity'];
                    }
                    for($i=0;$i<=$this->config->item('num_year_prediction');$i++)
                    {
                        if($prev_area_items[$result['id']][$area['value']]['year'.$i.'_budget_quantity']>0)
                        {
                            $row_quantity['year'.$i.'_area_total_quantity']+=$prev_area_items[$result['id']][$area['value']]['year'.$i.'_budget_quantity'];
                            $total_types['year'.$i.'_area_total_quantity']+=$prev_area_items[$result['id']][$area['value']]['year'.$i.'_budget_quantity'];
                            $total_crop['year'.$i.'_area_total_quantity']+=$prev_area_items[$result['id']][$area['value']]['year'.$i.'_budget_quantity'];
                        }
                    }
                }
                else
                {
                    $row_quantity['area'][$area['value']]=0;
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

            $items[]=$this->get_edit_row($item,$row_quantity);

        }
        $total_item=array();
        $total_item['sl_no']='';
        $total_item['type_name']='';
        $total_item['variety_name']='Total Type';
        $total_item['variety_id']='';
        $items[]=$this->get_edit_row($total_item,$total_types);
        $total_item=array();
        $total_item['sl_no']='';
        $total_item['type_name']='Total Crop';
        $total_item['variety_name']='';
        $total_item['variety_id']='';
        $items[]=$this->get_edit_row($total_item,$total_crop);

        $this->jsonReturn($items);

    }
    private function get_edit_row($item,$row_quantity)
    {

        $row=array();
        $row['sl_no']=$item['sl_no'];
        $row['type_name']=$item['type_name'];
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
            }
            else
            {
                $row['year'.$i.'_area_total_quantity']='';
            }
            if($row_quantity['year'.$i.'_budget_quantity']>0)
            {
                $row['year'.$i.'_budget_quantity']=$row_quantity['year'.$i.'_budget_quantity'];
            }
            else
            {
                $row['year'.$i.'_budget_quantity']='';
            }
            if(isset($row_quantity['year'.$i.'_budget_quantity_editable']))
            {
                $row['year'.$i.'_budget_quantity_editable']=$row_quantity['year'.$i.'_budget_quantity_editable'];
            }
            else
            {
                $row['year'.$i.'_budget_quantity_editable']=false;
            }


        }

        return $row;

    }
    private function system_save()
    {
        $territory_id=$this->input->post('territory_id');
        $year0_id=$this->input->post('year0_id');
        $crop_id=$this->input->post('crop_id');

        $user = User_helper::get_user();
        $time=time();
        //check forward status if has only add permission but not edit permission
        $items=$this->input->post('items');
        $this->db->trans_start();
        if(sizeof($items)>0)
        {
            $results=Query_helper::get_info($this->config->item('table_ti_bud_ti_bt'),'*',array('territory_id ='.$territory_id,'year0_id ='.$year0_id));
            $old_items=array();//ti budget
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

                    $data['territory_id']=$territory_id;
                    $data['year0_id']=$year0_id;
                    $data['variety_id']=$variety_id;
                    $data['user_budgeted'] = $user->user_id;
                    $data['date_budgeted'] = $time;
                    if(isset($old_items[$variety_id]))
                    {
                        $data['user_updated'] = $user->user_id;
                        $data['date_updated'] = $time;
                        Query_helper::update($this->config->item('table_ti_bud_ti_bt'),$data,array("id = ".$old_items[$variety_id]['id']));
                    }
                    else
                    {
                        $data['user_created'] = $user->user_id;
                        $data['date_created'] = $time;
                        Query_helper::add($this->config->item('table_ti_bud_ti_bt'),$data);
                    }
                }
            }
        }

        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
            $this->system_edit($territory_id,$year0_id,$crop_id);
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
        $this->db->from($this->config->item('table_ti_budget').' tb');
        $this->db->select('tb.*');
        $this->db->select('t.id territory_id,t.zone_id zone_id');
        $this->db->join($this->config->item('ems_setup_location_territories').' t','t.id = tb.territory_id','INNER');
        $this->db->where('tb.id',$id);
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
        $zone_setup=Query_helper::get_info($this->config->item('table_zi_budget'),'*',array('zone_id ='.$setup['zone_id'],'year0_id ='.$setup['year0_id']),1);
        if($zone_setup)
        {
            if($zone_setup['status_forward']===$this->config->item('system_status_yes'))
            {
                $ajax['status']=false;
                $ajax['system_message']='ZI already Forwarded.You cannot forward more';
                $this->jsonReturn($ajax);
                die();
            }
        }
        else
        {

            $data=array();
            $data['zone_id']=$setup['zone_id'];
            for($i=0;$i<=$this->config->item('num_year_prediction');$i++)
            {
                $data['year'.$i.'_id']=$setup['year'.$i.'_id'];
            }
            $data['user_created'] = $user->user_id;
            $data['date_created'] = $time;
            Query_helper::add($this->config->item('table_zi_budget'),$data);
        }
        $data=array();
        $data['status_forward']=$this->config->item('system_status_yes');
        $data['user_updated'] = $user->user_id;
        $data['date_updated'] = $time;
        Query_helper::update($this->config->item('table_ti_budget'),$data,array("id = ".$id));
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
        if(($this->locations['zone_id']>0)&&($this->locations['zone_id']!=$customer['zone_id']))
        {
            return false;
        }
        if(($this->locations['territory_id']>0)&&($this->locations['territory_id']!=$customer['territory_id']))
        {
            return false;
        }
        return true;
    }


}
