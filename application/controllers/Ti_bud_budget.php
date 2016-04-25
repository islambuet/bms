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
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("ti_bud_budget/list",$data,true));
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
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("ti_bud_customer_budget/search",$data,true));
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
    public function get_items()
    {
        $items=array();
        //$this->db->from($this->config->item('table_ti_bud_customer_sales_target').' csst');
        $this->db->from($this->config->item('table_ti_budget').' tb');
        $this->db->select('tb.id,tb.status_forward');
        $this->db->select('t.name territory_name');
        $this->db->select('zone.name zone_name');
        $this->db->select('division.name division_name');
        $this->db->select('fy.name fiscal_year');
        $this->db->select('COUNT(DISTINCT csst.customer_id) num_budgeted_customers');
        $this->db->select('COUNT(DISTINCT csst.variety_id) num_varieties_customer');
        $this->db->select('COUNT(DISTINCT cus.id) num_total_customers');
        $this->db->select('COUNT(DISTINCT tbt.variety_id) num_varieties_ti');

        $this->db->join($this->config->item('ems_setup_location_territories').' t','t.id = tb.territory_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_zones').' zone','zone.id = t.zone_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_divisions').' division','division.id = zone.division_id','INNER');

        $this->db->join($this->config->item('ems_basic_setup_fiscal_year').' fy','fy.id = tb.fiscal_year_id','INNER');
        $this->db->join($this->config->item('table_ti_bud_customer_sales_target').' csst','csst.setup_id = tb.id','INNER');

        $this->db->join($this->config->item('ems_setup_location_districts').' d','t.id = d.territory_id','INNER');
        $this->db->join($this->config->item('ems_csetup_customers').' cus','cus.district_id = d.id','INNER');

        $this->db->join($this->config->item('table_ti_bud_budget_target').' tbt','tbt.setup_id = tb.id','LEFT');

        if($this->locations['division_id']>0)
        {
            $this->db->where('division.id',$this->locations['division_id']);
            if($this->locations['zone_id']>0)
            {
                $this->db->where('zone.id',$this->locations['zone_id']);
                if($this->locations['territory_id']>0)
                {
                    $this->db->where('t.id',$this->locations['territory_id']);
                }
            }
        }
        /*$this->db->select('csst.customer_id,csst.id');

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
        $this->db->order_by('csst.id','DESC');*/
        $this->db->group_by(array('tb.id'));
        $items=$this->db->get()->result_array();
        $this->jsonReturn($items);
    }

}
