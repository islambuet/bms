<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Zi_bud_budget extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Zi_bud_budget');
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
        $this->controller_url='zi_bud_budget';

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
            $data['title']="Customer Sales Predicted List";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("zi_bud_budget/list",$data,true));
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
            $this->db->from($this->config->item('table_ti_budget').' tb');
            $this->db->select('tb.id,tb.status_forward');

            $this->db->select('t.id territory_id,t.name territory_name');
            $this->db->select('zone.id zone_id,zone.name zone_name');
            $this->db->select('division.id division_id,division.name division_name');
            $this->db->join($this->config->item('ems_setup_location_territories').' t','t.id = tb.territory_id','INNER');
            $this->db->join($this->config->item('ems_setup_location_zones').' zone','zone.id = t.zone_id','INNER');
            $this->db->join($this->config->item('ems_setup_location_divisions').' division','division.id = zone.division_id','INNER');
            $this->db->where('tb.id',$id);
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
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("ti_bud_budget/search",$data,true));
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
        $setup=Query_helper::get_info($this->config->item('table_ti_budget'),'*',array('id ='.$data['setup_id']),1);
        $results=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('id ASC'));
        $fiscal_years=array();
        foreach($results as $result)
        {
            $fiscal_years[$result['value']]=$result;
        }
        $years=array();
        $years['fiscal_year_id']=$fiscal_years[$setup['fiscal_year_id']];
        for($i=1;$i<=$this->config->item('num_year_prediction');$i++)
        {
            $years['year'.$i.'_id']=$fiscal_years[$setup['year'.$i.'_id']];
        }
        $this->db->from($this->config->item('ems_csetup_customers').' cus');
        $this->db->select('cus.id value,CONCAT(cus.customer_code," - ",cus.name) text');
        $this->db->join($this->config->item('ems_setup_location_districts').' d','d.id = cus.district_id','INNER');
        $this->db->where('d.territory_id',$setup['territory_id']);
        $this->db->order_by('cus.ordering');
        $data['customers']=$this->db->get()->result_array();
        $keys=',';
        $keys.="setup_id:'".$data['setup_id']."',";
        $keys.="crop_id:'".$data['crop_id']."',";
        $data['keys']=trim($keys,',');
        $data['years']=$years;
        $data['title']="TI Budget";
        $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("ti_bud_budget/add_edit",$data,true));
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
        $results=Query_helper::get_info($this->config->item('table_ti_bud_budget_target'),'*',array('setup_id ='.$setup_id));
        $old_items=array();
        foreach($results as $result)
        {
            $old_items[$result['variety_id']]=$result;
        }

        $setup=Query_helper::get_info($this->config->item('table_ti_budget'),'*',array('id ='.$setup_id),1);
        $this->db->from($this->config->item('ems_csetup_customers').' cus');
        $this->db->select('cus.id');
        $this->db->join($this->config->item('ems_setup_location_districts').' d','d.id = cus.district_id','INNER');
        $this->db->where('d.territory_id',$setup['territory_id']);
        $this->db->order_by('cus.ordering');
        $customers=$this->db->get()->result_array();

        $total_types=array();
        $total_crop=array();
        foreach($customers as $customer)
        {
            $total_types['customer'][$customer['id']]=0;
            $total_crop['customer'][$customer['id']]=0;
        }
        $total_types['budget_quantity']='';
        $total_crop['budget_quantity']='';
        $total_types['customer_total_quantity']=0;
        $total_crop['customer_total_quantity']=0;

        for($i=1;$i<=$this->config->item('num_year_prediction');$i++)
        {
            $total_types['year'.$i.'_customer_total_quantity']=0;
            $total_types['year'.$i.'_budget_quantity']='';
            $total_crop['year'.$i.'_customer_total_quantity']=0;
            $total_crop['year'.$i.'_budget_quantity']='';
        }

        $results=Query_helper::get_info($this->config->item('table_ti_bud_customer_sales_target'),'*',array('setup_id ='.$setup_id));
        $old_customer_items=array();

        foreach($results as $result)
        {
            $old_customer_items[$result['variety_id']][$result['customer_id']]=$result;
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
                    $items[]=$this->get_form_row($total_item,$total_types);
                    foreach($customers as $customer)
                    {
                        $total_types['customer'][$customer['id']]=0;
                    }
                    $total_types['customer_total_quantity']=0;
                    for($i=1;$i<=$this->config->item('num_year_prediction');$i++)
                    {
                        $total_types['year'.$i.'_customer_total_quantity']=0;;
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
            $row_quantity=array();
            $row_quantity['customer_total_quantity']=0;
            $row_quantity['budget_quantity']='';
            for($i=1;$i<=$this->config->item('num_year_prediction');$i++)
            {
                $row_quantity['year'.$i.'_customer_total_quantity']=0;
                $row_quantity['year'.$i.'_budget_quantity']='';
            }

            foreach($customers as $customer)
            {
                if(isset($old_customer_items[$result['id']][$customer['id']]))
                {
                    if($old_customer_items[$result['id']][$customer['id']]['budget_quantity']>0)
                    {
                        $row_quantity['customer'][$customer['id']]=$old_customer_items[$result['id']][$customer['id']]['budget_quantity'];
                        $total_types['customer'][$customer['id']]+=$old_customer_items[$result['id']][$customer['id']]['budget_quantity'];
                        $total_crop['customer'][$customer['id']]+=$old_customer_items[$result['id']][$customer['id']]['budget_quantity'];

                        $row_quantity['customer_total_quantity']+=$old_customer_items[$result['id']][$customer['id']]['budget_quantity'];
                        $total_types['customer_total_quantity']+=$old_customer_items[$result['id']][$customer['id']]['budget_quantity'];
                        $total_crop['customer_total_quantity']+=$old_customer_items[$result['id']][$customer['id']]['budget_quantity'];
                        //also input field
                    }
                    for($i=1;$i<=$this->config->item('num_year_prediction');$i++)
                    {
                        if($old_customer_items[$result['id']][$customer['id']]['year'.$i.'_budget_quantity']>0)
                        {
                            $row_quantity['year'.$i.'_customer_total_quantity']+=$old_customer_items[$result['id']][$customer['id']]['year'.$i.'_budget_quantity'];
                            $total_types['year'.$i.'_customer_total_quantity']+=$old_customer_items[$result['id']][$customer['id']]['year'.$i.'_budget_quantity'];
                            $total_crop['year'.$i.'_customer_total_quantity']+=$old_customer_items[$result['id']][$customer['id']]['year'.$i.'_budget_quantity'];
                        }
                    }
                }
                else
                {
                    $row_quantity['customer'][$customer['id']]=0;
                }

            }
            $quantity='';
            $editable=false;
            if((isset($old_items[$result['id']]['budget_quantity']))&&(($old_items[$result['id']]['budget_quantity'])>0))
            {
                $quantity=$old_items[$result['id']]['budget_quantity'];
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
            if($editable)
            {
                $row_quantity['budget_quantity']='<input type="text" name="items['.$result['id'].'][budget_quantity]"  class="jqxgrid_input integer_type_positive" value="'.$quantity.'"/>';
            }
            else
            {
                $row_quantity['budget_quantity']=$quantity;
            }
            for($i=1;$i<=$this->config->item('num_year_prediction');$i++)
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
                if($editable)
                {
                    $row_quantity['year'.$i.'_budget_quantity']='<input type="text" name="items['.$result['id'].'][year'.$i.'_budget_quantity]"  class="jqxgrid_input integer_type_positive" value="'.$quantity.'"/>';
                }
                else
                {
                    $row_quantity['year'.$i.'_budget_quantity']=$quantity;
                }
            }
            $items[]=$this->get_form_row($item,$row_quantity);
        }
        $total_item=array();
        $total_item['crop_type_name']='';
        $total_item['variety_name']='Type Total';
        $items[]=$this->get_form_row($total_item,$total_types);
        $total_item=array();
        $total_item['crop_type_name']='Crop Total';
        $total_item['variety_name']='';
        $items[]=$this->get_form_row($total_item,$total_crop);

        $this->jsonReturn($items);

    }
    private function get_form_row($item,$row_quantity)
    {

        $row=array();
        $row['crop_type_name']=$item['crop_type_name'];
        $row['variety_name']=$item['variety_name'];

        foreach($row_quantity['customer'] as $id=>$quantity)
        {
            if($quantity>0)
            {
                $row['customer_quantity_'.$id]=$quantity;
            }
            else
            {
                $row['customer_quantity_'.$id]='';
            }

        }
        if($row_quantity['customer_total_quantity']>0)
        {
            $row['customer_total_quantity']=$row_quantity['customer_total_quantity'];
            $row['budget_quantity']=$row_quantity['budget_quantity'];
        }
        else
        {
            $row['customer_total_quantity']='';
            $row['budget_quantity']='';
        }

        for($i=1;$i<=$this->config->item('num_year_prediction');$i++)
        {
            if($row_quantity['year'.$i.'_customer_total_quantity']>0)
            {
                $row['year'.$i.'_customer_total_quantity']=$row_quantity['year'.$i.'_customer_total_quantity'];
                $row['year'.$i.'_budget_quantity']=$row_quantity['year'.$i.'_budget_quantity'];
            }
            else
            {
                $row['year'.$i.'_customer_total_quantity']='';
                $row['year'.$i.'_budget_quantity']='';
            }

        }

        return $row;

    }
    private function system_save()
    {
        $user = User_helper::get_user();
        $time=time();
        $setup_id=$this->input->post('setup_id');
        $setup=Query_helper::get_info($this->config->item('table_ti_budget'),'*',array('id ='.$setup_id),1);
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
            $results=Query_helper::get_info($this->config->item('table_ti_bud_budget_target'),'*',array('setup_id ='.$setup_id));
            $old_items=array();

            foreach($results as $result)
            {
                $old_items[$result['variety_id']]=$result;
            }

            foreach($items as $variety_id=>$item)
            {
                $data=array();
                if((isset($item['budget_quantity']))&&($item['budget_quantity']>0))
                {
                    $data['budget_quantity']=$item['budget_quantity'];

                }
                for($i=1;$i<=$this->config->item('num_year_prediction');$i++)
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
                        Query_helper::update($this->config->item('table_ti_bud_budget_target'),$data,array("id = ".$old_items[$variety_id]['id']));
                    }
                    else
                    {
                        $data['user_created'] = $user->user_id;
                        $data['date_created'] = $time;
                        Query_helper::add($this->config->item('table_ti_bud_budget_target'),$data);
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
        $zone_setup=Query_helper::get_info($this->config->item('table_zi_budget'),'*',array('zone_id ='.$setup['zone_id'],'fiscal_year_id ='.$setup['fiscal_year_id']),1);
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
            $data['fiscal_year_id']=$setup['fiscal_year_id'];
            for($i=1;$i<=$this->config->item('num_year_prediction');$i++)
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
    public function get_items()
    {
        $items=array();
        $this->db->from($this->config->item('table_zi_budget').' zb');
        $this->db->select('zb.id,zb.status_forward');
        $this->db->select('zone.name zone_name');
        $this->db->select('division.name division_name');
        $this->db->select('fy.name fiscal_year');
        $this->db->select('COUNT(DISTINCT t.id) num_total_territories');
        $this->db->select('COUNT(DISTINCT tb.id) num_forwarded_territories');
        $this->db->select('COUNT(DISTINCT tbt.variety_id) num_varieties_ti');
        $this->db->select('COUNT(DISTINCT zbt.variety_id) num_varieties_zi');

        $this->db->join($this->config->item('ems_setup_location_zones').' zone','zone.id = zb.zone_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_divisions').' division','division.id = zone.division_id','INNER');

        $this->db->join($this->config->item('ems_basic_setup_fiscal_year').' fy','fy.id = zb.fiscal_year_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_territories').' t','zone.id = t.zone_id','INNER');

        $this->db->join($this->config->item('ems_setup_location_territories').' t1','zone.id = t1.zone_id','INNER');
        $this->db->join($this->config->item('table_ti_budget').' tb','tb.territory_id = t1.id','INNER');
        $this->db->where('tb.status_forward',$this->config->item('system_status_yes'));

        $this->db->join($this->config->item('table_ti_bud_budget_target').' tbt','tbt.setup_id = tb.id','INNER');
        $this->db->join($this->config->item('table_zi_bud_budget_target').' zbt','zbt.setup_id = zb.id','LEFT');

        if($this->locations['division_id']>0)
        {
            $this->db->where('division.id',$this->locations['division_id']);
            if($this->locations['zone_id']>0)
            {
                $this->db->where('zone.id',$this->locations['zone_id']);
            }
        }
        $this->db->group_by(array('zb.id'));
        $items=$this->db->get()->result_array();
        $this->jsonReturn($items);
    }

}
