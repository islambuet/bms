<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reports_mgt_purchase_actual extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Reports_mgt_purchase_actual');
        $this->controller_url='reports_mgt_purchase_actual';
    }

    public function index($action="search",$id=0)
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
            $this->get_items();
        }
        else
        {
            $this->system_search();
        }
    }
    private function system_search()
    {
        if(isset($this->permissions['view'])&&($this->permissions['view']==1))
        {
            $data['title']="Actual Purchase Report";
            $ajax['status']=true;

            $fy_info=System_helper::get_fiscal_years();
            $data['fiscal_years']=$fy_info['years'];
            $data['year0_id']=$fy_info['budget_year']['value']-1;//current fiscal year

            $data['crops']=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array(),0,0,array('ordering ASC'));
            $data['principals']=Query_helper::get_info($this->config->item('ems_basic_setup_principal'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));

            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("reports_mgt_purchase_actual/search",$data,true));
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
            $reports=$this->input->post('report');
            if(!($reports['year0_id']>0))
            {
                $ajax['status']=false;
                $ajax['system_message']='Please Select a Fiscal Year';
                $this->jsonReturn($ajax);
            }
            $months=$this->input->post('months');
            if(!(sizeof($months)>0))
            {
                $ajax['status']=false;
                $ajax['system_message']='Please Select at Least one month';
                $this->jsonReturn($ajax);
            }
            $keys=',';

            foreach($reports as $elem=>$value)
            {
                $keys.=$elem.":'".$value."',";
            }
            for($i=1;$i<13;$i++)
            {
                if((isset($months[$i]))&&$months[$i]>0)
                {
                    $keys.="month_".$i.":'1',";
                }
                else
                {
                    $keys.="month_".$i.":'0',";
                }
            }

            $data['keys']=trim($keys,',');
            $data['direct_costs']=Query_helper::get_info($this->config->item('table_setup_direct_cost_items'),array('id value,name text'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));
            $data['packing_costs']=Query_helper::get_info($this->config->item('table_setup_packing_material_items'),array('id value,name text'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));


            $ajax['status']=true;
            $data['title']="Actual Purchase Report";

            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("reports_mgt_purchase_actual/list",$data,true));

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
    private function get_items()
    {
        $items=array();
        $year0_id=$this->input->post('year0_id');
        $year_info=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text','date_start','date_end'),array('id ='.$year0_id),1);

        $crop_id=$this->input->post('crop_id');
        $crop_type_id=$this->input->post('crop_type_id');
        $variety_id=$this->input->post('variety_id');
        $principal_id=$this->input->post('principal_id');
        $months=array();
        for($i=1;$i<13;$i++)
        {
            if($this->input->post('month_'.$i)==1)
            {
                $months[]=$i;
            }
        }
        //consignment info
        $this->db->from($this->config->item('table_mgt_purchase_consignments').' con');
        $this->db->select('con.*');
        $this->db->select('c.name currency_name');
        $this->db->select('p.id principal_id,p.name principal_name');

        $this->db->join($this->config->item('table_setup_currency').' c','c.id = con.currency_id','INNER');
        $this->db->join($this->config->item('ems_basic_setup_principal').' p','p.id = con.principal_id','LEFT');
        if($principal_id>0)
        {
            $this->db->where('p.id',$principal_id);
        }
        $this->db->where('con.year0_id',$year0_id);
        $this->db->where_in('con.month',$months);
        $this->db->where_in('con.status',$this->config->item('system_status_active'));
        $results=$this->db->get()->result_array();
        $consignments=array();
        $consignment_ids=array();
        foreach($results as $result)
        {
            $consignment_ids[]=$result['id'];
            $consignments[$result['id']]['consignment_name']=$result['name'];
            $consignments[$result['id']]['currency_name']=$result['currency_name'];
            $consignments[$result['id']]['month']=$result['month'];
            $consignments[$result['id']]['principal_name']=$result['principal_name'];
            $consignments[$result['id']]['rate']=$result['rate'];
            $consignments[$result['id']]['lc_number']=$result['lc_number'];
            $consignments[$result['id']]['direct_cost']=0;
        }
        //consignment info
        //consignment total direct cost calculation
        $this->db->from($this->config->item('table_mgt_purchase_consignment_costs').' cost');
        $this->db->select('SUM(cost.cost) total_cost');
        $this->db->select('cost.consignment_id');
        $this->db->where('cost.revision',1);
        $this->db->where_in('cost.consignment_id',$consignment_ids);
        $this->db->group_by('cost.consignment_id');
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            $consignments[$result['consignment_id']]['direct_cost']=$result['total_cost'];
        }
        //consignment total direct cost calculation
        //variety packing cost calculation
        $this->db->from($this->config->item('table_mgt_packing_cost_kg').' pack_cost');
        $this->db->select('SUM(pack_cost.cost) total_cost');
        $this->db->select('pack_cost.variety_id');
        $this->db->where('pack_cost.year0_id',$year0_id);
        $this->db->group_by('pack_cost.variety_id');
        $results=$this->db->get()->result_array();
        $packing_costs=array();
        foreach($results as $result)
        {
            $packing_costs[$result['variety_id']]=$result['total_cost'];
        }
        //variety packing cost calculation

        //consignment varieties
        $this->db->from($this->config->item('table_mgt_purchase_consignment_varieties').' cv');
        $this->db->select('cv.*');
        $this->db->where_in('cv.consignment_id',$consignment_ids);
        $this->db->where('cv.revision',1);
        $results=$this->db->get()->result_array();
        $consignment_varieties=array();
        foreach($results as $result)
        {
            $info=array();
            $info['quantity']=$result['quantity'];
            $info['price']=$result['price'];
            $consignment_varieties[$result['consignment_id']][$result['variety_id']]=$info;
        }
        $purchase_varieties=array();
        $variety_ids=array();
        foreach($consignment_varieties as $con_id=>$varieties)
        {
            $total_weight=0;
            foreach($varieties as $result)
            {
                $total_weight+=$result['quantity']*$result['price'];
            }
            foreach($varieties as $variety_id=>$result)
            {
                $variety_ids[]=$variety_id;
                $info=array();
                $info['principal_name']=$consignments[$con_id]['principal_name'];
                $info['months_val']=$consignments[$con_id]['month'];
                $info['months']=date("M", mktime(0, 0, 0,$consignments[$con_id]['month'],1, 2000));
                $info['quantity']=$result['quantity'];
                $info['currency_name']=$consignments[$con_id]['currency_name'];
                $info['currency_rate']=$consignments[$con_id]['rate'];
                $info['unit_price']=$result['price'];
                $info['direct_cost']=0;
                $info['packing_cost']=0;
                $info['pi_values']=$result['quantity']*$consignments[$con_id]['rate']*$result['price'];
                $info['cogs']=0;
                $info['total_cogs']=0;
                $total=0;
                $total+=$result['price']*$result['quantity']*$consignments[$con_id]['rate'];
                if(($total_weight>0))
                {
                    $info['direct_cost']=($consignments[$con_id]['direct_cost']*$result['quantity']*$result['price']/$total_weight);
                    $total+=$info['direct_cost'];
                }
                if(isset($packing_costs[$variety_id]))
                {
                    $info['packing_cost']=$result['quantity']*$packing_costs[$variety_id];
                    $total+=$info['packing_cost'];
                }
                $info['total_cogs']=$total;
                $info['cogs']=$total/$result['quantity'];
                $purchase_varieties[$variety_id][$con_id]=$info;
            }

        }
        $this->db->from($this->config->item('ems_setup_classification_varieties').' v');
        $this->db->select('v.id variety_id,v.name variety_name,v.name_import variety_import_name');
        $this->db->select('type.id type_id,type.name type_name');
        $this->db->select('crop.id crop_id,crop.name crop_name');
        $this->db->join($this->config->item('ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->join($this->config->item('ems_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');
        if($crop_id>0)
        {
            $this->db->where('crop.id',$crop_id);
            if($crop_type_id>0)
            {
                $this->db->where('type.id',$crop_type_id);
                if($variety_id>0)
                {
                    $this->db->where('v.id',$variety_id);
                }
            }
        }
        $this->db->where_in('v.id',$variety_ids);
        $this->db->order_by('crop.ordering','ASC');
        $this->db->order_by('type.ordering','ASC');
        $this->db->order_by('v.ordering','ASC');
        $results=$this->db->get()->result_array();
        $grand_row=array();
        $grand_row['crop_name']='Total';
        $grand_row['type_name']='';
        $grand_row['principal_name']='';
        $grand_row['variety_name']='';
        $grand_row['variety_import_name']='';
        $grand_row['months']='';
        $grand_row['quantity']=0;
        $grand_row['currency_name']='';
        $grand_row['currency_rate']=0;
        $grand_row['unit_price']=0;
        $grand_row['direct_cost']=0;
        $grand_row['packing_cost']=0;
        $grand_row['pi_values']=0;
        $grand_row['cogs']=0;
        $grand_row['total_cogs']=0;
        $prev_crop_name='';
        $prev_crop_type_name='';
        foreach($results as $result)
        {
            if(!isset($purchase_varieties[$result['variety_id']]))
            {
                continue;
            }
            if(sizeof($items)>0)
            {
                if($prev_crop_name!=$result['crop_name'])
                {
                    $current_crop_name=$result['crop_name'];
                    $prev_crop_name=$result['crop_name'];

                    $current_type_name=$result['type_name'];
                    $prev_crop_type_name=$result['type_name'];
                }
                elseif($prev_crop_type_name!=$result['type_name'])
                {
                    $current_crop_name='';
                    $current_type_name=$result['type_name'];
                    $prev_crop_type_name=$result['type_name'];
                }
                else
                {
                    $current_crop_name='';
                    $current_type_name='';
                }
            }
            else
            {
                $current_crop_name=$result['crop_name'];
                $prev_crop_name=$result['crop_name'];
                $current_type_name=$result['type_name'];
                $prev_crop_type_name=$result['type_name'];
            }
            $variety_total=array();
            $variety_total['crop_name']='';
            $variety_total['type_name']='';
            $variety_total['principal_name']='';
            $variety_total['variety_name']='Total';
            $variety_total['variety_import_name']='';
            $variety_total['months']='';
            $variety_total['quantity']=0;
            $variety_total['currency_name']='';
            $variety_total['currency_rate']=0;
            $variety_total['unit_price']=0;
            $variety_total['direct_cost']=0;
            $variety_total['packing_cost']=0;
            $variety_total['pi_values']=0;
            $variety_total['cogs']=0;
            $variety_total['total_cogs']=0;
            $index=0;
            foreach($purchase_varieties[$result['variety_id']] as $variety_id=>$cons)
            {
                $item=array();
                if($index==0)
                {
                    $item['crop_name']=$current_crop_name;
                    $item['type_name']=$current_type_name;
                    $item['variety_name']=$result['variety_name'];
                    $item['principal_name']=$cons['principal_name'];
                    $item['variety_import_name']=$result['variety_import_name'];
                }
                else
                {
                    $item['crop_name']='';
                    $item['type_name']='';
                    $item['variety_name']='';
                    $item['principal_name']='';
                    $item['variety_import_name']='';
                }
                $index++;
                $item['months']=$cons['months'];
                $item['quantity']=$cons['quantity'];
                $variety_total['quantity']+=$cons['quantity'];
                $grand_row['quantity']+=$cons['quantity'];
                $item['currency_name']=$cons['currency_name'];
                $item['currency_rate']=$cons['currency_rate'];
                $item['unit_price']=$cons['unit_price'];

                $item['direct_cost']=$cons['direct_cost'];
                $variety_total['direct_cost']+=$cons['direct_cost'];
                $grand_row['direct_cost']+=$cons['direct_cost'];

                $item['packing_cost']=$cons['packing_cost'];
                $variety_total['packing_cost']+=$cons['packing_cost'];
                $grand_row['packing_cost']+=$cons['packing_cost'];

                $item['pi_values']=$cons['pi_values'];
                $variety_total['pi_values']+=$cons['pi_values'];
                $grand_row['pi_values']+=$cons['pi_values'];

                $item['cogs']=$cons['cogs'];

                $item['total_cogs']=$cons['total_cogs'];
                $variety_total['total_cogs']+=$cons['total_cogs'];
                $grand_row['total_cogs']+=$cons['total_cogs'];
                $items[]=$this->get_report_row($item);
            }
            $variety_total['cogs']=$variety_total['total_cogs']/$variety_total['quantity'];
            if(sizeof($purchase_varieties[$result['variety_id']])>1)
            {
                $items[]=$this->get_report_row($variety_total);
            }

        }
        $items[]=$this->get_report_row($grand_row);


        $this->jsonReturn($items);



    }
    private function get_report_row($item)
    {
        $info=array();
        $grand_row=array();
        $grand_row['crop_name']='Total';
        $grand_row['type_name']='';
        $grand_row['principal_name']='';
        $grand_row['variety_name']='';
        $grand_row['variety_import_name']='';
        $grand_row['months']='';
        $grand_row['quantity']=0;
        $grand_row['currency_name']='';
        $grand_row['currency_rate']=0;
        $grand_row['unit_price']=0;
        $grand_row['direct_cost']=0;
        $grand_row['packing_cost']=0;
        $grand_row['pi_values']=0;
        $grand_row['cogs']=0;
        $grand_row['total_cogs']=0;
        $info=$grand_row;
        $info['crop_name']=$item['crop_name'];
        $info['type_name']=$item['type_name'];
        $info['principal_name']=$item['principal_name'];
        $info['variety_name']=$item['variety_name'];
        $info['variety_import_name']=$item['variety_import_name'];
        $info['months']=$item['months'];

        if($item['quantity']!=0)
        {
            $info['quantity']=number_format($item['quantity'],3,'.','');
        }
        else
        {
            $info['quantity']='';
        }
        $info['currency_name']=$item['currency_name'];
        if($item['currency_rate']!=0)
        {
            $info['currency_rate']=number_format($item['currency_rate'],2);
        }
        else
        {
            $info['currency_rate']='';
        }
        if($item['unit_price']!=0)
        {
            $info['unit_price']=number_format($item['unit_price'],2);
        }
        else
        {
            $info['unit_price']='';
        }
        if($item['direct_cost']!=0)
        {
            $info['direct_cost']=number_format($item['direct_cost'],2);
        }
        else
        {
            $info['direct_cost']='';
        }
        if($item['packing_cost']!=0)
        {
            $info['packing_cost']=number_format($item['packing_cost'],2);
        }
        else
        {
            $info['packing_cost']='';
        }
        if($item['pi_values']!=0)
        {
            $info['pi_values']=number_format($item['pi_values'],2);
        }
        else
        {
            $info['pi_values']='';
        }
        if($item['cogs']!=0)
        {
            $info['cogs']=number_format($item['cogs'],2);
        }
        else
        {
            $info['cogs']='';
        }
        if($item['total_cogs']!=0)
        {
            $info['total_cogs']=number_format($item['total_cogs'],2);
        }
        else
        {
            $info['total_cogs']='';
        }
        return $info;
    }

}
