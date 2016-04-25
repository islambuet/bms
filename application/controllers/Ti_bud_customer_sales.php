<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ti_bud_customer_sales extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Ti_bud_customer_sales');
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
        $this->controller_url='ti_bud_customer_sales';

    }

    public function index($action="list",$id=0)
    {
        if($action=="list")
        {
            $this->system_list();
        }
        elseif($action=="add")
        {
            $this->system_add();
        }
        elseif($action=="get_customer_details")
        {
            $this->get_customer_details();
        }
        elseif($action=="get_customer_items")
        {
            $this->get_customer_items();
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
            $this->system_list();
        }
    }

    private function system_list()
    {
        if(isset($this->permissions['view'])&&($this->permissions['view']==1))
        {
            $data['title']="Customer Sales Predicted List";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("ti_bud_customer_sales/list",$data,true));
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

    private function system_add()
    {
        if((isset($this->permissions['add'])&&($this->permissions['add']==1))||(isset($this->permissions['edit'])&&($this->permissions['edit']==1)))
        {
            $data['budget']=array();
            $data['fiscal_year_id']=0;
            $data['fiscal_year_name']='';
            $time=time();
            $data['fiscal_years']=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text','date_start','date_end'),array('status ="'.$this->config->item('system_status_active').'"'));
            //deciding current year is in a fiscal year
            foreach($data['fiscal_years'] as $year)
            {
                if($year['date_start']<=$time && $year['date_end']>=$time)
                {
                    $data['budget']['fiscal_year_id']=$year['value'];
                    $data['budget']['fiscal_year_name']=$year['text'];
                    break;
                }

            }
            if(!($data['budget']['fiscal_year_id']>0))
            {
                $ajax['status']=false;
                $ajax['system_message']="Current Date is not in any Fiscal Year.";
                $this->jsonReturn($ajax);
            }


            $data['title']="Search";

            $data['budget']['division_id']=$this->locations['division_id'];
            $data['budget']['zone_id']=$this->locations['zone_id'];
            $data['budget']['territory_id']=$this->locations['territory_id'];
            $data['budget']['district_id']=$this->locations['district_id'];
            $data['budget']['customer_id']='';

            $data['divisions']=Query_helper::get_info($this->config->item('ems_setup_location_divisions'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));
            $data['zones']=array();
            $data['territories']=array();
            $data['districts']=array();
            $data['customers']=array();
            if($this->locations['division_id']>0)
            {
                $data['zones']=Query_helper::get_info($this->config->item('ems_setup_location_zones'),array('id value','name text'),array('division_id ='.$this->locations['division_id']));
                if($this->locations['zone_id']>0)
                {
                    $data['territories']=Query_helper::get_info($this->config->item('ems_setup_location_territories'),array('id value','name text'),array('zone_id ='.$this->locations['zone_id']));
                    if($this->locations['territory_id']>0)
                    {
                        $data['districts']=Query_helper::get_info($this->config->item('ems_setup_location_districts'),array('id value','name text'),array('territory_id ='.$this->locations['territory_id']));
                        if($this->locations['district_id']>0)
                        {
                            $data['customers']=Query_helper::get_info($this->config->item('ems_csetup_customers'),array('id value','CONCAT(customer_code," - ",name) text'),array('district_id ='.$this->locations['district_id'],'status ="'.$this->config->item('system_status_active').'"'));
                        }
                    }
                }
            }
            $data['crops']=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array());


            $ajax['system_page_url']=site_url($this->controller_url."/index/add");

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("ti_bud_customer_sales/search",$data,true));
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
        if((isset($this->permissions['edit'])&&($this->permissions['edit']==1))||(isset($this->permissions['add'])&&($this->permissions['add']==1)))
        {
            if(($this->input->post('id')))
            {
                $id=$this->input->post('id');
            }
            $this->db->from($this->config->item('table_ti_bud_customer_sales_target').' csst');
            $this->db->select('csst.customer_id');
            $this->db->select('tb.fiscal_year_id');

            $this->db->select('d.id district_id');
            $this->db->select('t.id territory_id');
            $this->db->select('zone.id zone_id');
            $this->db->select('division.id division_id');
            $this->db->select('fy.name fiscal_year_name');

            $this->db->join($this->config->item('ems_csetup_customers').' cus','cus.id = csst.customer_id','INNER');
            $this->db->join($this->config->item('ems_setup_location_districts').' d','d.id = cus.district_id','INNER');

            $this->db->join($this->config->item('table_ti_budget').' tb','tb.id = csst.setup_id','INNER');

            $this->db->join($this->config->item('ems_setup_location_territories').' t','t.id = tb.territory_id','INNER');
            $this->db->join($this->config->item('ems_setup_location_zones').' zone','zone.id = t.zone_id','INNER');
            $this->db->join($this->config->item('ems_setup_location_divisions').' division','division.id = zone.division_id','INNER');
            $this->db->join($this->config->item('ems_basic_setup_fiscal_year').' fy','fy.id = tb.fiscal_year_id','INNER');
            $this->db->where('csst.id',$id);

            $data['budget']=$this->db->get()->row_array();
            if(!$data['budget'])
            {
                System_helper::invalid_try($this->config->item('system_edit_not_exists'),$id);
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->jsonReturn($ajax);
            }
            if(!$this->check_my_editable($data['budget']))
            {
                System_helper::invalid_try($this->config->item('system_edit_others'),$id);
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->jsonReturn($ajax);
            }
            $data['title']="Search";
            $data['divisions']=Query_helper::get_info($this->config->item('ems_setup_location_divisions'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));
            $data['zones']=Query_helper::get_info($this->config->item('ems_setup_location_zones'),array('id value','name text'),array('division_id ='.$data['budget']['division_id']));
            $data['territories']=Query_helper::get_info($this->config->item('ems_setup_location_territories'),array('id value','name text'),array('zone_id ='.$data['budget']['zone_id']));
            $data['districts']=Query_helper::get_info($this->config->item('ems_setup_location_districts'),array('id value','name text'),array('territory_id ='.$data['budget']['territory_id']));
            $data['customers']=Query_helper::get_info($this->config->item('ems_csetup_customers'),array('id value','CONCAT(customer_code," - ",name) text'),array('district_id ='.$data['budget']['district_id'],'status ="'.$this->config->item('system_status_active').'"'));

            $data['crops']=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array());


            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("ti_bud_customer_sales/search",$data,true));
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
    private function get_customer_details()
    {
        $user = User_helper::get_user();
        $time=time();
        $results=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('id DESC'));
        $fiscal_years=array();
        foreach($results as $result)
        {
            $fiscal_years[$result['value']]=$result;
        }

        $budget_search=$this->input->post('budget');
        $years=array();
        $years['fiscal_year_id']=$fiscal_years[$budget_search['fiscal_year_id']];
        $setup_id=0;
        $setup=Query_helper::get_info($this->config->item('table_ti_budget'),'*',array('territory_id ='.$budget_search['territory_id'],'fiscal_year_id ='.$budget_search['fiscal_year_id']),1);
        if($setup)
        {
            if($setup['status_forward']===$this->config->item('system_status_yes'))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_ALREADY_FORWARDED");
                $this->jsonReturn($ajax);
                die();
            }
            $setup_id=$setup['id'];
            for($i=1;$i<=$this->config->item('num_year_prediction');$i++)
            {
                $years['year'.$i.'_id']=$fiscal_years[$setup['year'.$i.'_id']];
            }
        }
        else
        {
            $i=1;
            $data=array();
            $data['fiscal_year_id']=$years['fiscal_year_id']['value'];
            foreach($fiscal_years as $result)
            {
                if(sizeof($years)<=$this->config->item('num_year_prediction'))
                {
                    if($result['value']>$years['fiscal_year_id']['value'])
                    {
                        $years['year'.$i.'_id']=$result;
                        $data['year'.$i.'_id']=$result['value'];
                        $i++;
                    }
                }
            }
            if(sizeof($years)<=$this->config->item('num_year_prediction'))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line('MSG_SETUP_MORE_FISCAL_YEAR');
                $this->jsonReturn($ajax);
            }
            $data['territory_id'] = $budget_search['territory_id'];
            $data['user_created'] = $user->user_id;
            $data['date_created'] = $time;
            $setup_id=Query_helper::add($this->config->item('table_ti_budget'),$data);
            if($setup_id===false)
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->jsonReturn($ajax);
                die();
            }
        }
        $keys=',';
        $keys.="setup_id:'".$setup_id."',";
        $keys.="customer_id:'".$budget_search['customer_id']."',";
        $keys.="crop_type_id:'".$budget_search['crop_type_id']."',";
        $data['keys']=trim($keys,',');
        $data['years']=$years;
        $data['customer_id']=$budget_search['customer_id'];
        $data['setup_id']=$setup_id;
        $data['title']="Customer Sales Budget";
        $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("ti_bud_customer_sales/add_edit",$data,true));
        if($this->message)
        {
            $ajax['system_message']=$this->message;
        }
        $this->jsonReturn($ajax);

    }
    private function get_customer_items()
    {
        $setup_id=$this->input->post('setup_id');
        $customer_id=$this->input->post('customer_id');
        $crop_type_id=$this->input->post('crop_type_id');

        $results=Query_helper::get_info($this->config->item('table_ti_bud_customer_sales_target'),'*',array('setup_id ='.$setup_id,'customer_id ='.$customer_id));
        $old_items=array();

        foreach($results as $result)
        {
            $old_items[$result['variety_id']]=$result;
        }

        $results=Query_helper::get_info($this->config->item('ems_setup_classification_varieties'),array('id','name'),array('crop_type_id ='.$crop_type_id,'status ="'.$this->config->item('system_status_active').'"','whose ="ARM"'),0,0,array('ordering ASC'));
        $items=array();
        $count=0;
        foreach($results as $result)
        {
            $count++;
            $item=array();
            $item['sl_no']=$count;
            $item['variety_name']=$result['name'];
            $quantity='';
            $editable=false;
            if((isset($old_items[$result['id']]['sale_quantity']))&&(($old_items[$result['id']]['sale_quantity'])>0))
            {
                $quantity=$old_items[$result['id']]['sale_quantity'];
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
                $item['sale_quantity']='<input type="text" name="items['.$result['id'].'][sale_quantity]"  class="jqxgrid_input integer_type_positive" value="'.$quantity.'"/>';
            }
            else
            {
                $item['sale_quantity']=$quantity;
            }

            for($i=1;$i<=$this->config->item('num_year_prediction');$i++)
            {
                $quantity='';
                $editable=false;
                if((isset($old_items[$result['id']]['year'.$i.'_sale_quantity']))&&(($old_items[$result['id']]['year'.$i.'_sale_quantity'])>0))
                {
                    $quantity=$old_items[$result['id']]['year'.$i.'_sale_quantity'];
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
                    $item['year'.$i.'_sale_quantity']='<input type="text" name="items['.$result['id'].'][year'.$i.'_sale_quantity]"  class="jqxgrid_input integer_type_positive" value="'.$quantity.'"/>';
                }
                else
                {
                    $item['year'.$i.'_sale_quantity']=$quantity;
                }
            }

            $items[]=$item;
        }
        $this->jsonReturn($items);


    }
    private function system_save()
    {
        $user = User_helper::get_user();
        $time=time();
        $setup_id=$this->input->post('setup_id');
        $customer_id=$this->input->post('customer_id');
        $items=$this->input->post('items');
        $this->db->trans_start();
        if(sizeof($items)>0)
        {
            $results=Query_helper::get_info($this->config->item('table_ti_bud_customer_sales_target'),'*',array('setup_id ='.$setup_id,'customer_id ='.$customer_id));
            $old_items=array();

            foreach($results as $result)
            {
                $old_items[$result['variety_id']]=$result;
            }

            foreach($items as $variety_id=>$item)
            {
                $data=array();
                if((isset($item['sale_quantity']))&&($item['sale_quantity']>0))
                {
                    $data['sale_quantity']=$item['sale_quantity'];

                }
                for($i=1;$i<=$this->config->item('num_year_prediction');$i++)
                {
                    if((isset($item['year'.$i.'_sale_quantity']))&&($item['year'.$i.'_sale_quantity']>0))
                    {
                        $data['year'.$i.'_sale_quantity']=$item['year'.$i.'_sale_quantity'];
                    }
                }
                if($data)
                {
                    $data['customer_id']=$customer_id;
                    $data['setup_id']=$setup_id;
                    $data['variety_id']=$variety_id;
                    if(isset($old_items[$variety_id]))
                    {
                        $data['user_updated'] = $user->user_id;
                        $data['date_updated'] = $time;
                        Query_helper::update($this->config->item('table_ti_bud_customer_sales_target'),$data,array("id = ".$old_items[$variety_id]['id']));
                    }
                    else
                    {
                        $data['user_created'] = $user->user_id;
                        $data['date_created'] = $time;
                        Query_helper::add($this->config->item('table_ti_bud_customer_sales_target'),$data);
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
        if(($this->locations['district_id']>0)&&($this->locations['district_id']!=$customer['district_id']))
        {
            return false;
        }
        return true;
    }
    public function get_items()
    {
        $items=array();
        $this->db->from($this->config->item('table_ti_bud_customer_sales_target').' csst');
        $this->db->select('csst.customer_id,csst.id');

        $this->db->select('COUNT(csst.variety_id) num_varieties');

        $this->db->select('COUNT(DISTINCT types.id) num_types');
        $this->db->select('COUNT(Distinct types.crop_id) num_crops');


        $this->db->select('cus.name customer_name');
        $this->db->select('d.name district_name');
        $this->db->select('t.name territory_name');
        $this->db->select('zone.name zone_name');
        $this->db->select('division.name division_name');
        $this->db->select('fy.name fiscal_year');
        $this->db->join($this->config->item('ems_csetup_customers').' cus','cus.id = csst.customer_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_districts').' d','d.id = cus.district_id','INNER');

        $this->db->join($this->config->item('table_ti_budget').' tb','tb.id = csst.setup_id','INNER');

        $this->db->join($this->config->item('ems_setup_location_territories').' t','t.id = tb.territory_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_zones').' zone','zone.id = t.zone_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_divisions').' division','division.id = zone.division_id','INNER');

        $this->db->join($this->config->item('ems_basic_setup_fiscal_year').' fy','fy.id = tb.fiscal_year_id','INNER');

        $this->db->join($this->config->item('ems_setup_classification_varieties').' v','v.id = csst.variety_id','INNER');
        $this->db->join($this->config->item('ems_setup_classification_crop_types').' types','types.id = v.crop_type_id','INNER');
        if($this->locations['division_id']>0)
        {
            $this->db->where('division.id',$this->locations['division_id']);
            if($this->locations['zone_id']>0)
            {
                $this->db->where('zone.id',$this->locations['zone_id']);
                if($this->locations['territory_id']>0)
                {
                    $this->db->where('t.id',$this->locations['territory_id']);
                    if($this->locations['district_id']>0)
                    {
                        $this->db->where('d.id',$this->locations['district_id']);
                    }
                }
            }
        }
        $this->db->group_by(array('csst.customer_id','fy.id'));
        $this->db->order_by('fy.id','DESC');
        $items=$this->db->get()->result_array();
        $this->jsonReturn($items);
    }

}
