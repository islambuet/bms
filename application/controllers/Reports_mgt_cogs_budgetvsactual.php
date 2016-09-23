<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reports_mgt_cogs_budgetvsactual extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Reports_mgt_cogs_budgetvsactual');
        $this->controller_url='reports_mgt_cogs_budgetvsactual';
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
            $data['title']="Budgeted vs Actual Pricing Report";
            $ajax['status']=true;
            $fy_info=System_helper::get_fiscal_years();
            $data['fiscal_years']=$fy_info['years'];
            $data['year0_id']=$fy_info['budget_year']['value']-1;//current fiscal year

            $data['crops']=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array(),0,0,array('ordering ASC'));


            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("reports_mgt_cogs_budgetvsactual/search",$data,true));
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
            $keys=',';

            foreach($reports as $elem=>$value)
            {
                $keys.=$elem.":'".$value."',";
            }

            $data['keys']=trim($keys,',');


            $ajax['status']=true;
            $data['title']="Budget And Target Report";
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("reports_mgt_cogs_budgetvsactual/list",$data,true));

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
        //final pricing
        $final_pricing=array();
        $results=Query_helper::get_info($this->config->item('table_mgt_sales_pricing'),'*',array('year0_id ='.$year0_id));
        foreach($results as $result)
        {
            $final_pricing[$result['variety_id']]=$result;
        }
        //final pricing finish


        //getting full budget and target
        $results=Query_helper::get_info($this->config->item('table_hom_bud_hom_bt'),'*',array('year0_id ='.$year0_id));
        $incharge_budget_target=array();//hom budget and target
        foreach($results as $result)
        {
            $incharge_budget_target[$result['variety_id']]=$result;
        }
        //getting full budget and target end
        //total sales
        $sales_total=array();
        $this->db->from($this->config->item('ems_sales_po_details').' pod');

        $this->db->select('pod.*');
        $this->db->select('po.date_approved');

        $this->db->join($this->config->item('ems_sales_po').' po','po.id = pod.sales_po_id','INNER');

        $this->db->join($this->config->item('ems_setup_classification_varieties').' v','v.id =pod.variety_id','INNER');
        $this->db->join($this->config->item('ems_setup_classification_crop_types').' type','type.id =v.crop_type_id','INNER');

        $this->db->where('pod.revision',1);
        $this->db->where('po.status_approved',$this->config->item('system_status_po_approval_approved'));
        $this->db->where('po.date_approved >=',$year_info['date_start']);
        $this->db->where('po.date_approved <=',$year_info['date_end']);
        if($crop_id>0)
        {
            $this->db->where('type.crop_id',$crop_id);
            if($crop_type_id>0)
            {
                $this->db->where('type.id',$crop_type_id);
                if($variety_id>0)
                {
                    $this->db->where('pod.variety_id',$variety_id);
                }
            }
        }

        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            if(isset($sales_total[$result['variety_id']]))
            {
                $sales_total[$result['variety_id']]['quantity']+=$result['pack_size']*$result['quantity'];//minus sales return,discard bonus
                //$sales_total[$result['variety_id']]['net_sales']+=$result['variety_price_net']*$result['quantity'];//minus sales return,discard bonus
            }
            else
            {
                $sales_total[$result['variety_id']]['quantity']=$result['pack_size']*$result['quantity'];//minus sales return,discard bonus
                //$sales_total[$result['variety_id']]['net_sales']=$result['variety_price_net']*$result['quantity'];//minus sales return,discard bonus

            }
        }
        //total sales finish
        $currency_rates=array();
        $rates=Query_helper::get_info($this->config->item('table_mgt_currency_rate'),'*',array('status !="'.$this->config->item('system_status_delete').'"','fiscal_year_id ='.$year0_id));
        foreach($rates as $rate)
        {
            $currency_rates[$rate['currency_id']]=$rate['rate'];
        }
        $direct_costs_percentage=0;
        $result=Query_helper::get_info($this->config->item('table_mgt_direct_cost_percentage'),array('SUM(percentage) total_percentage'),array('status !="'.$this->config->item('system_status_delete').'"','fiscal_year_id ='.$year0_id),1);
        if($result)
        {
            if(strlen($result['total_percentage'])>0)
            {
                $direct_costs_percentage=number_format($result['total_percentage']/100,5,'.','');
            }
        }
        $packing_cost=array();
        $this->db->from($this->config->item('table_mgt_packing_cost_kg').' pc');
        $this->db->select('SUM(pc.cost) total_cost');
        $this->db->where('pc.year0_id',$year0_id);
        $this->db->group_by('pc.variety_id');
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            $packing_cost[$result['variety_id']]=$result['total_cost'];
        }


        //getting budgeted cogs
        $results=Query_helper::get_info($this->config->item('table_mgt_purchase_budget'),'*',array('year0_id ='.$year0_id));
        $cogs_budgeted=array();//hom budget and target
        foreach($results as $result)
        {
            //$cogs_price=$result['quantity_total'];
            $result['cogs']=0;
            if(isset($currency_rates[$result['currency_id']]))
            {
                $result['cogs']=$currency_rates[$result['currency_id']]*$result['unit_price'];
            }
            $result['cogs']=$result['cogs']+$result['cogs']*$direct_costs_percentage;
            if(isset($packing_cost[$result['variety_id']]))
            {
                $result['cogs']+=$packing_cost[$result['variety_id']];
            }
            $cogs_budgeted[$result['variety_id']]=$result;
        }
        //getting budgeted cogs finish
        //actual cogs calculation
        //consignment total direct cost calculation
        $consignments=array();
        $consignment_ids=array();
        $this->db->from($this->config->item('table_mgt_purchase_consignment_costs').' cost');
        $this->db->select('SUM(cost.cost) total_cost');
        $this->db->select('cost.consignment_id');
        $this->db->select('con.rate');
        $this->db->join($this->config->item('table_mgt_purchase_consignments').' con','con.id = cost.consignment_id','INNER');
        $this->db->where('cost.revision',1);
        $this->db->where('con.year0_id',$year0_id);
        $this->db->where('con.status',$this->config->item('system_status_active'));
        $this->db->group_by('cost.consignment_id');
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            $consignment_ids[]=$result['consignment_id'];
            $consignments[$result['consignment_id']]['direct_cost']=$result['total_cost'];
            $consignments[$result['consignment_id']]['rate']=$result['rate'];
        }
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
        $cogs_actual=array();
        foreach($consignment_varieties as $con_id=>$varieties)
        {
            $total_weight=0;
            foreach($varieties as $result)
            {
                $total_weight+=$result['quantity']*$result['price'];
            }
            foreach($varieties as $variety_id=>$result)
            {
                $total=0;
                $total+=$result['price']*$result['quantity']*$consignments[$con_id]['rate'];
                if(($total_weight>0))
                {
                    $total+=($consignments[$con_id]['direct_cost']*$result['quantity']*$result['price']/$total_weight);

                }
                if(isset($packing_costs[$variety_id]))
                {
                    $total+=($result['quantity']*$packing_costs[$variety_id]);

                }
                if(isset($cogs_actual[$variety_id]))
                {
                    $cogs_actual[$variety_id]['total_cogs']+=$total;
                    $cogs_actual[$variety_id]['quantity']+=$result['quantity'];
                }
                else
                {
                    $cogs_actual[$variety_id]['total_cogs']=$total;
                    $cogs_actual[$variety_id]['quantity']=$result['quantity'];
                }
            }

        }
        $indirect_cost_percentage=0;
        $result=Query_helper::get_info($this->config->item('table_mgt_indirect_cost_setup'),'*',array('status !="'.$this->config->item('system_status_delete').'"','year0_id ='.$year0_id),1);
        if($result)
        {
            $indirect_cost_percentage=$result['general']+$result['marketing']+$result['finance'];
        }
        //actual cogs calculation finish
        //variety list
        $this->db->from($this->config->item('ems_setup_classification_varieties').' v');
        $this->db->select('v.id variety_id,v.name variety_name');
        $this->db->select('type.name type_name');
        $this->db->select('crop.name crop_name');
        $this->db->join($this->config->item('ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->join($this->config->item('ems_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');
        $this->db->where('v.whose','ARM');
        $this->db->where('v.status =',$this->config->item('system_status_active'));
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
        $this->db->order_by('crop.ordering','ASC');
        $this->db->order_by('type.ordering','ASC');
        $this->db->order_by('v.ordering','ASC');
        $results=$this->db->get()->result_array();
        $prev_crop_name='';
        $prev_crop_type_name='';
        foreach($results as $index=>$result)
        {
            $item=array();
            if($index>0)
            {
                if($prev_crop_name!=$result['crop_name'])
                {
                    $item['crop_name']=$result['crop_name'];
                    $prev_crop_name=$result['crop_name'];

                    $item['type_name']=$result['type_name'];
                    $prev_crop_type_name=$result['type_name'];
                }
                elseif($prev_crop_type_name!=$result['type_name'])
                {
                    $item['crop_name']='';
                    $item['type_name']=$result['type_name'];
                    $prev_crop_type_name=$result['type_name'];
                }
                else
                {
                    $item['crop_name']='';
                    $item['type_name']='';
                }
            }
            else
            {
                $item['crop_name']=$result['crop_name'];
                $prev_crop_name=$result['crop_name'];
                $item['type_name']=$result['type_name'];
                $prev_crop_type_name=$result['type_name'];
            }
            $item['variety_name']=$result['variety_name'];

            if((isset($final_pricing[$result['variety_id']]))&&($final_pricing[$result['variety_id']]['user_created_final']>0))
            {
                $tp_final=$final_pricing[$result['variety_id']]['tp_final'];
                $sales_commission=$tp_final*$final_pricing[$result['variety_id']]['commission_final']/100;
                $incentive=$tp_final*$final_pricing[$result['variety_id']]['incentive_final']/100;
                $item['price_final']=$tp_final-$sales_commission-$incentive;

            }
            else
            {
                $item['price_final']=0;
            }

            if(isset($incharge_budget_target[$result['variety_id']]))
            {
                $item['target_kg']=$incharge_budget_target[$result['variety_id']]['year0_target_quantity'];
            }
            else
            {
                $item['target_kg']=0;
            }
            $item['sales_kg']=0;
            if((isset($sales_total[$result['variety_id']]))&&($sales_total[$result['variety_id']]['quantity']!=null))
            {
                $item['sales_kg']=$sales_total[$result['variety_id']]['quantity']/1000;
            }
            if(isset($cogs_budgeted[$result['variety_id']]))
            {
                $item['cogs_budgeted']=$cogs_budgeted[$result['variety_id']]['cogs'];
            }
            else
            {
                $item['cogs_budgeted']=0;
            }
            if(isset($cogs_actual[$result['variety_id']]))
            {
                $item['cogs_actual']=$cogs_actual[$result['variety_id']]['total_cogs']/$cogs_actual[$result['variety_id']]['quantity'];
            }
            else
            {
                $item['cogs_actual']=0;
            }
            $item['np_kg_budgeted']=$item['price_final']-$item['cogs_budgeted']-$item['cogs_budgeted']*$indirect_cost_percentage/100;
            $item['np_kg_actual']=$item['price_final']-$item['cogs_actual']-$item['cogs_actual']*$indirect_cost_percentage/100;

            $items[]=$this->get_report_row($item);
        }


        $this->jsonReturn($items);
    }
    private function get_report_row($item)
    {
        $info=array();
        $info['crop_name']=$item['crop_name'];
        $info['type_name']=$item['type_name'];
        $info['variety_name']=$item['variety_name'];
        if($item['price_final']!=0)
        {
            $info['price_final']=number_format($item['price_final'],2);
        }
        else
        {
            $info['price_final']='';
        }
        if($item['target_kg']!=0)
        {
            $info['target_kg']=number_format($item['target_kg'],3,'.','');
        }
        else
        {
            $info['target_kg']='';
        }
        if($item['sales_kg']!=0)
        {
            $info['sales_kg']=number_format($item['sales_kg'],3,'.','');
        }
        else
        {
            $info['sales_kg']='';
        }
        if(($item['target_kg']-$item['sales_kg'])!=0)
        {
            $info['variance_kg']=number_format(($item['target_kg']-$item['sales_kg']),3,'.','');
        }
        else
        {
            $info['variance_kg']='';
        }
        if($item['cogs_budgeted']!=0)
        {
            $info['cogs_budgeted']=number_format($item['cogs_budgeted'],2);
        }
        else
        {
            $info['cogs_budgeted']='';
        }
        if($item['cogs_actual']!=0)
        {
            $info['cogs_actual']=number_format($item['cogs_actual'],2);
        }
        else
        {
            $info['cogs_actual']='';
        }
        if(($item['cogs_budgeted']-$item['cogs_actual'])!=0)
        {
            $info['cogs_variance']=number_format(($item['cogs_budgeted']-$item['cogs_actual']),2);
        }
        else
        {
            $info['cogs_variance']='';
        }
        if($item['np_kg_budgeted']!=0)
        {
            $info['np_kg_budgeted']=number_format($item['np_kg_budgeted'],2);
            if($item['sales_kg']!=0)
            {
                $info['np_total_budgeted']=number_format($item['np_kg_budgeted']*$item['sales_kg'],2);
            }
            else
            {
                $info['np_total_budgeted']='';
            }
        }
        else
        {
            $info['np_kg_budgeted']='';
            $info['np_total_budgeted']='';
        }
        if($item['np_kg_actual']!=0)
        {
            $info['np_kg_actual']=number_format($item['np_kg_actual'],2);
            if($item['sales_kg']!=0)
            {
                $info['np_total_actual']=number_format($item['np_kg_actual']*$item['sales_kg'],2);
            }
            else
            {
                $info['np_total_actual']='';
            }
        }
        else
        {
            $info['np_kg_actual']='';
            $info['np_total_actual']='';
        }
        if(($item['np_kg_budgeted']-$item['np_kg_actual'])!=0)
        {
            $info['np_kg_variance']=number_format(($item['np_kg_budgeted']-$item['np_kg_actual']),2);
            if($item['sales_kg']!=0)
            {
                $info['np_total_variance']=number_format(($item['np_kg_budgeted']-$item['np_kg_actual'])*$item['sales_kg'],2);
            }
            else
            {
                $info['np_total_variance']='';
            }
        }
        else
        {
            $info['np_kg_variance']='';
            $info['np_total_variance']='';
        }
        
        return $info;
    }

}
