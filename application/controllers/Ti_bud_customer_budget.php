<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ti_bud_customer_budget extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Ti_bud_customer_budget');
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
        $this->controller_url='ti_bud_customer_budget';

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
        elseif($action=="get_edit_form")
        {
            $this->system_get_edit_form();
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

            $data['title']="Customer Budget";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("ti_bud_customer_budget/search",$data,true));
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
            $data['title']="Customer List";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("ti_bud_customer_budget/list",$data,true));
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
    private function system_edit($territory_id,$year0_id,$customer_id)
    {
        if((isset($this->permissions['edit'])&&($this->permissions['edit']==1))||(isset($this->permissions['add'])&&($this->permissions['add']==1)))
        {
            if(($this->input->post('id')))
            {
                $customer_id=$this->input->post('id');
            }
            $this->db->from($this->config->item('ems_csetup_customers').' cus');
            $this->db->select('cus.id');
            $this->db->select('CONCAT(cus.customer_code," - ",cus.name) customer_name');
            $this->db->select('d.name district_name');
            $this->db->select('t.id territory_id,t.name territory_name');
            $this->db->select('zone.id zone_id,zone.name zone_name');
            $this->db->select('division.id division_id,division.name division_name');

            $this->db->join($this->config->item('ems_setup_location_districts').' d','d.id = cus.district_id','INNER');
            $this->db->join($this->config->item('ems_setup_location_territories').' t','t.id = d.territory_id','INNER');
            $this->db->join($this->config->item('ems_setup_location_zones').' zone','zone.id = t.zone_id','INNER');
            $this->db->join($this->config->item('ems_setup_location_divisions').' division','division.id = zone.division_id','INNER');

            $this->db->where('d.territory_id',$territory_id);
            $this->db->where('cus.id',$customer_id);
            $data['customer']=$this->db->get()->row_array();
            if(!$data['customer'])
            {
                System_helper::invalid_try($this->config->item('system_edit_not_exists'),$customer_id);
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->jsonReturn($ajax);
            }
            if(!$this->check_my_editable($data['customer']))
            {
                System_helper::invalid_try($this->config->item('system_edit_others'),$customer_id);
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->jsonReturn($ajax);
            }
            $data['title']="Customer Budget";
            $data['crops']=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array());
            $data['year0']=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text','date_start','date_end'),array('id ='.$year0_id),1);
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("ti_bud_customer_budget/search_edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url."/index/edit/".$territory_id.'/'.$year0_id.'/'.$customer_id);
            $this->jsonReturn($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
        }
    }
    private function system_get_edit_form()
    {
        $territory_id=$this->input->post('territory_id');
        $year0_id=$this->input->post('year0_id');
        $customer_id=$this->input->post('customer_id');
        $crop_id=$this->input->post('crop_id');
        $crop_type_id=$this->input->post('crop_type_id');
        $user = User_helper::get_user();
        $time=time();

        if((!isset($this->permissions['edit'])||($this->permissions['edit']!=1)))
        {
            $info=Query_helper::get_info($this->config->item('table_forward_ti'),'*',array('territory_id ='.$territory_id,'year0_id ='.$year0_id,'crop_id ='.$crop_id),1);

            if($info)
            {
                if($info['status_forward']===$this->config->item('system_status_yes'))
                {
                    $ajax['status']=false;
                    $ajax['system_message']=$this->lang->line("MSG_ALREADY_FORWARDED");
                    $this->jsonReturn($ajax);
                    die();
                }
            }
        }

        //echo $territory_id.' '.$year0_id.' '.$customer_id.' '.$crop_type_id;
        $years=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"',' id >='.$year0_id),$this->config->item('num_year_prediction')+1,0,array('id ASC'));

        $keys=',';
        $keys.="territory_id:'".$territory_id."',";
        $keys.="year0_id:'".$year0_id."',";
        $keys.="customer_id:'".$customer_id."',";
        $keys.="crop_type_id:'".$crop_type_id."',";
        $data['keys']=trim($keys,',');
        $data['years']=$years;
        $data['territory_id']=$territory_id;
        $data['year0_id']=$year0_id;
        $data['customer_id']=$customer_id;
        $data['title']="Customer Budget";
        $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("ti_bud_customer_budget/add_edit",$data,true));
        if($this->message)
        {
            $ajax['system_message']=$this->message;
        }
        $this->jsonReturn($ajax);

    }
    private function system_get_edit_items()
    {
        $territory_id=$this->input->post('territory_id');
        $year0_id=$this->input->post('year0_id');
        $customer_id=$this->input->post('customer_id');
        $crop_type_id=$this->input->post('crop_type_id');

        $results=Query_helper::get_info($this->config->item('table_ti_bud_customer_bt'),'*',array('territory_id ='.$territory_id,'year0_id ='.$year0_id,'customer_id ='.$customer_id));
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
            $item['variety_id']=$result['id'];
            $item['variety_name']=$result['name'];

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
                $item['year'.$i.'_budget_quantity']=$quantity;
                $item['year'.$i.'_budget_quantity_editable']=$editable;
            }

            $items[]=$item;
        }
        $this->jsonReturn($items);


    }
    private function system_save()
    {
        $user = User_helper::get_user();
        $time=time();
        $territory_id=$this->input->post('territory_id');
        $year0_id=$this->input->post('year0_id');
        $customer_id=$this->input->post('customer_id');
        $items=$this->input->post('items');
        $this->db->trans_start();
        if(sizeof($items)>0)
        {
            $results=Query_helper::get_info($this->config->item('table_ti_bud_customer_bt'),'*',array('territory_id ='.$territory_id,'year0_id ='.$year0_id,'customer_id ='.$customer_id));
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
                    $data['territory_id']=$territory_id;
                    $data['year0_id']=$year0_id;
                    $data['customer_id']=$customer_id;
                    $data['variety_id']=$variety_id;
                    $data['user_budgeted'] = $user->user_id;
                    $data['date_budgeted'] = $time;
                    if(isset($old_items[$variety_id]))
                    {
                        $data['user_updated'] = $user->user_id;
                        $data['date_updated'] = $time;
                        Query_helper::update($this->config->item('table_ti_bud_customer_bt'),$data,array("id = ".$old_items[$variety_id]['id']));
                    }
                    else
                    {
                        $data['user_created'] = $user->user_id;
                        $data['date_created'] = $time;
                        Query_helper::add($this->config->item('table_ti_bud_customer_bt'),$data);
                    }
                }
            }
        }

        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
            $this->system_edit($territory_id,$year0_id,$customer_id);
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
    private function get_items($territory_id,$year0_id)
    {

        $this->db->from($this->config->item('ems_csetup_customers').' cus');
        $this->db->select('cus.id');
        $this->db->select('CONCAT(cus.customer_code," - ",cus.name) customer_name');
        $this->db->select('d.name district_name');
        $this->db->join($this->config->item('ems_setup_location_districts').' d','d.id = cus.district_id','INNER');
        $this->db->where('d.territory_id',$territory_id);

        $this->db->order_by('cus.ordering','ASC');
        $this->db->where('cus.status',$this->config->item('system_status_active'));
        $items=$this->db->get()->result_array();
        $this->jsonReturn($items);
    }
    private function system_details($territory_id,$year0_id,$customer_id)
    {
        if(isset($this->permissions['view'])&&($this->permissions['view']==1))
        {
            if(($this->input->post('id')))
            {
                $customer_id=$this->input->post('id');
            }
            $this->db->from($this->config->item('ems_csetup_customers').' cus');
            $this->db->select('cus.id');
            $this->db->select('CONCAT(cus.customer_code," - ",cus.name) customer_name');
            $this->db->select('d.name district_name');
            $this->db->select('t.id territory_id,t.name territory_name');
            $this->db->select('zone.id zone_id,zone.name zone_name');
            $this->db->select('division.id division_id,division.name division_name');

            $this->db->join($this->config->item('ems_setup_location_districts').' d','d.id = cus.district_id','INNER');
            $this->db->join($this->config->item('ems_setup_location_territories').' t','t.id = d.territory_id','INNER');
            $this->db->join($this->config->item('ems_setup_location_zones').' zone','zone.id = t.zone_id','INNER');
            $this->db->join($this->config->item('ems_setup_location_divisions').' division','division.id = zone.division_id','INNER');

            $this->db->where('d.territory_id',$territory_id);
            $this->db->where('cus.id',$customer_id);
            $data['customer']=$this->db->get()->row_array();
            if(!$data['customer'])
            {
                System_helper::invalid_try($this->config->item('system_edit_not_exists'),$customer_id);
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->jsonReturn($ajax);
            }
            if(!$this->check_my_editable($data['customer']))
            {
                System_helper::invalid_try($this->config->item('system_edit_others'),$customer_id);
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->jsonReturn($ajax);
            }
            $data['title']="Customer Budget";
            $data['crops']=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array());
            $data['year0']=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text','date_start','date_end'),array('id ='.$year0_id),1);
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("ti_bud_customer_budget/search_details",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url."/index/details/".$territory_id.'/'.$year0_id.'/'.$customer_id);
            $this->jsonReturn($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
        }
    }
    private function system_get_details_form()
    {
        $territory_id=$this->input->post('territory_id');
        $year0_id=$this->input->post('year0_id');
        $customer_id=$this->input->post('customer_id');
        $crop_id=$this->input->post('crop_id');
        $user = User_helper::get_user();
        $time=time();
        $years=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"',' id >='.$year0_id),$this->config->item('num_year_prediction')+1,0,array('id ASC'));

        $keys=',';
        $keys.="territory_id:'".$territory_id."',";
        $keys.="year0_id:'".$year0_id."',";
        $keys.="customer_id:'".$customer_id."',";
        $keys.="crop_id:'".$crop_id."',";
        $data['keys']=trim($keys,',');
        $data['years']=$years;
        $data['territory_id']=$territory_id;
        $data['year0_id']=$year0_id;
        $data['customer_id']=$customer_id;
        $data['title']="Customer Budget";
        $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("ti_bud_customer_budget/details",$data,true));
        if($this->message)
        {
            $ajax['system_message']=$this->message;
        }
        $this->jsonReturn($ajax);

    }
    private function system_get_details_items()
    {
        $territory_id=$this->input->post('territory_id');
        $year0_id=$this->input->post('year0_id');
        $customer_id=$this->input->post('customer_id');
        $crop_id=$this->input->post('crop_id');

        $results=Query_helper::get_info($this->config->item('table_ti_bud_customer_bt'),'*',array('territory_id ='.$territory_id,'year0_id ='.$year0_id,'customer_id ='.$customer_id));
        $old_items=array();

        foreach($results as $result)
        {
            $old_items[$result['variety_id']]=$result;
        }
        $this->db->from($this->config->item('ems_setup_classification_varieties').' v');
        $this->db->select('v.id,v.name');
        $this->db->select('crop.name crop_name');
        $this->db->select('type.name type_name');
        $this->db->join($this->config->item('ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->join($this->config->item('ems_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');
        $this->db->where('v.status !=',$this->config->item('system_status_delete'));
        $this->db->where('v.whose','ARM');
        $this->db->where('crop.id',$crop_id);
        $this->db->order_by('type.ordering','ASC');
        $this->db->order_by('v.ordering','ASC');
        $results=$this->db->get()->result_array();
        $items=array();
        $total_types=array();
        $total_crop=array();
        $count=0;
        for($i=0;$i<=$this->config->item('num_year_prediction');$i++)
        {
            $total_types['year'.$i]=0;
            $total_crop['year'.$i]=0;
        }
        $prev_type='';
        foreach($results as $index=>$result)
        {
            $item=array();
            if($index>0)
            {
                if($prev_type!=$result['type_name'])
                {
                    $total_item=array();
                    $total_item['type_name']='';
                    $total_item['variety_name']='Total Type';
                    $total_item['variety_id']='';

                    for($i=0;$i<=$this->config->item('num_year_prediction');$i++)
                    {
                        if($total_types['year'.$i]>0)
                        {
                            $total_item['year'.$i.'_budget_quantity']=$total_types['year'.$i];
                        }
                        else
                        {
                            $total_item['year'.$i.'_budget_quantity']='';
                        }

                        $total_types['year'.$i]=0;
                    }
                    $items[]=$total_item;
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

            for($i=0;$i<=$this->config->item('num_year_prediction');$i++)
            {
                $quantity='';
                if((isset($old_items[$result['id']]['year'.$i.'_budget_quantity']))&&(($old_items[$result['id']]['year'.$i.'_budget_quantity'])>0))
                {
                    $quantity=$old_items[$result['id']]['year'.$i.'_budget_quantity'];
                    $total_types['year'.$i]+=$old_items[$result['id']]['year'.$i.'_budget_quantity'];
                    $total_crop['year'.$i]+=$old_items[$result['id']]['year'.$i.'_budget_quantity'];
                }
                $item['year'.$i.'_budget_quantity']=$quantity;
            }

            $items[]=$item;
        }
        $total_item=array();
        $total_item['type_name']='';
        $total_item['variety_name']='Total Type';
        $total_item['variety_id']='';

        for($i=0;$i<=$this->config->item('num_year_prediction');$i++)
        {
            if($total_types['year'.$i]>0)
            {
                $total_item['year'.$i.'_budget_quantity']=$total_types['year'.$i];
            }
            else
            {
                $total_item['year'.$i.'_budget_quantity']='';
            }
        }
        $items[]=$total_item;
        $total_item=array();
        $total_item['type_name']='Total Crop';
        $total_item['variety_name']='';
        $total_item['variety_id']='';

        for($i=0;$i<=$this->config->item('num_year_prediction');$i++)
        {
            if($total_crop['year'.$i]>0)
            {
                $total_item['year'.$i.'_budget_quantity']=$total_crop['year'.$i];
            }
            else
            {
                $total_item['year'.$i.'_budget_quantity']='';
            }
        }
        $items[]=$total_item;
        $this->jsonReturn($items);


    }

}
