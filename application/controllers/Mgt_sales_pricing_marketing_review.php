<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mgt_sales_pricing_marketing_review extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Mgt_sales_pricing_marketing_review');
        $this->controller_url='mgt_sales_pricing_marketing_review';
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
            $data['title']="Marketing Pricing Details Search";
            $ajax['status']=true;
            $fy_info=System_helper::get_fiscal_years();
            $data['fiscal_years']=$fy_info['years'];
            $data['year0_id']=$fy_info['budget_year']['value'];

            $data['crops']=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array(),0,0,array('ordering ASC'));


            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("mgt_sales_pricing_marketing_review/search",$data,true));
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
            $data['title']="Marketing Pricing Details";
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("mgt_sales_pricing_marketing_review/list",$data,true));

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

        //getting full budget and target
        $results=Query_helper::get_info($this->config->item('table_hom_bud_hom_bt'),'*',array('year0_id ='.$year0_id));
        $incharge_budget_target=array();//hom budget and target
        foreach($results as $result)
        {
            $incharge_budget_target[$result['variety_id']]=$result;
        }
        //getting full budget and target end
        $currency_rates=array();
        $rates=Query_helper::get_info($this->config->item('table_mgt_currency_rate'),'*',array('status !="'.$this->config->item('system_status_delete').'"','fiscal_year_id ='.$year0_id));
        foreach($rates as $rate)
        {
            $currency_rates[$rate['currency_id']]=$rate['rate'];
        }

        $direct_costs_percentage=0;
        $result=$results=Query_helper::get_info($this->config->item('table_mgt_direct_cost_percentage'),array('SUM(percentage) total_percentage'),array('status !="'.$this->config->item('system_status_delete').'"','fiscal_year_id ='.$year0_id),1);
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

        $indirect_costs=$results=Query_helper::get_info($this->config->item('table_mgt_indirect_cost_setup'),'*',array('status !="'.$this->config->item('system_status_delete').'"','year0_id ='.$year0_id),1);

        //getting cogs
        $results=Query_helper::get_info($this->config->item('table_mgt_purchase_budget'),'*',array('year0_id ='.$year0_id));
        $cogs=array();//hom budget and target
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
            $cogs[$result['variety_id']]=$result;
        }
        //getting cogs end
        //get varities
        $crop_id=$this->input->post('crop_id');
        $crop_type_id=$this->input->post('crop_type_id');
        $variety_id=$this->input->post('variety_id');

        $this->db->from($this->config->item('table_mgt_sales_pricing').' sp');
        $this->db->select('sp.*');
        $this->db->select('v.name variety_name');
        $this->db->select('type.name type_name');
        $this->db->select('crop.name crop_name');
        $this->db->join($this->config->item('ems_setup_classification_varieties').' v','v.id = sp.variety_id','INNER');
        $this->db->join($this->config->item('ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->join($this->config->item('ems_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');
        $this->db->where('v.whose','ARM');
        $this->db->where('v.status =',$this->config->item('system_status_active'));
        $this->db->where('sp.year0_id',$year0_id);
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
        if($results)
        {



            $grand_row=$crop_row=$type_row=array();
            $grand_row['crop_name']=$crop_row['crop_name']=$type_row['crop_name']='';
            $grand_row['type_name']=$crop_row['type_name']=$type_row['type_name']='';
            $grand_row['variety_name']=$crop_row['variety_name']=$type_row['variety_name']='';
            $type_row['variety_name']='Total Type';
            $crop_row['type_name']='Total Crop';
            $grand_row['crop_name']='Grand Total';
            $grand_row['hom_target']=$crop_row['hom_target']=$type_row['hom_target']='0';
            $grand_row['tp_last_year']=$crop_row['tp_last_year']=$type_row['tp_last_year']='0';
            $grand_row['tp_management']=$crop_row['tp_management']=$type_row['tp_management']='0';
            $grand_row['tp_hom']=$crop_row['tp_hom']=$type_row['tp_hom']='0';
            $grand_row['commission_hom']=$crop_row['commission_hom']=$type_row['commission_hom']='0';
            $grand_row['sales_commission']=$crop_row['sales_commission']=$type_row['sales_commission']='0';
            $grand_row['incentive_hom']=$crop_row['incentive_hom']=$type_row['incentive_hom']='0';
            $grand_row['incentive']=$crop_row['incentive']=$type_row['incentive']='0';
            $grand_row['net_price']=$crop_row['net_price']=$type_row['net_price']='0';
            $grand_row['cogs']=$crop_row['cogs']=$type_row['cogs']='0';
            $grand_row['general']=$crop_row['general']=$type_row['general']='0';
            $grand_row['marketing']=$crop_row['marketing']=$type_row['marketing']='0';
            $grand_row['finance']=$crop_row['finance']=$type_row['finance']='0';
            $grand_row['profit']=$crop_row['profit']=$type_row['profit']='0';
            $grand_row['total_profit']=$crop_row['total_profit']=$type_row['total_profit']='0';
            $grand_row['total_net_price']=$crop_row['total_net_price']=$type_row['total_net_price']='0';
            $grand_row['profit_percentage']=$crop_row['profit_percentage']=$type_row['profit_percentage']='0';

            $prev_crop_name='';
            $prev_crop_type_name='';
            foreach($results as $index=>$result)
            {
                $item=array();
                if($index>0)
                {
                    if($prev_crop_name!=$result['crop_name'])
                    {
                        if($type_row['total_net_price']!=0)
                        {
                            $type_row['profit_percentage']=$type_row['total_profit']*100/$type_row['total_net_price'];
                        }
                        else
                        {
                            $type_row['profit_percentage']=0;
                        }

                        $items[]=$this->get_report_row($type_row);
                        $type_row['hom_target']=0;
                        $type_row['total_profit']=0;
                        $type_row['total_net_price']=0;
                        if($crop_row['total_net_price']!=0)
                        {
                            $crop_row['profit_percentage']=$crop_row['total_profit']*100/$crop_row['total_net_price'];
                        }
                        else
                        {
                            $crop_row['profit_percentage']=0;
                        }
                        $items[]=$this->get_report_row($crop_row);
                        $crop_row['hom_target']=0;
                        $crop_row['total_profit']=0;
                        $crop_row['total_net_price']=0;

                        $item['crop_name']=$result['crop_name'];
                        $prev_crop_name=$result['crop_name'];

                        $item['type_name']=$result['type_name'];
                        $prev_crop_type_name=$result['type_name'];
                    }
                    elseif($prev_crop_type_name!=$result['type_name'])
                    {
                        if($type_row['total_net_price']!=0)
                        {
                            $type_row['profit_percentage']=$type_row['total_profit']*100/$type_row['total_net_price'];
                        }
                        else
                        {
                            $type_row['profit_percentage']=0;
                        }
                        $items[]=$this->get_report_row($type_row);
                        $type_row['hom_target']=0;
                        $type_row['total_profit']=0;
                        $type_row['total_net_price']=0;

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

                if(isset($incharge_budget_target[$result['variety_id']]))
                {
                    $item['hom_target']=$incharge_budget_target[$result['variety_id']]['year0_target_quantity'];
                }
                else
                {
                    $item['hom_target']=0;
                }
                $item['tp_last_year']=0;
                $item['tp_management']=$result['tp_management'];
                if($result['user_created_hom']>0)
                {
                    $item['tp_hom']=$result['tp_hom'];
                    $item['commission_hom']=$result['commission_hom'];
                    $item['incentive_hom']=$result['incentive_hom'];
                }
                else
                {
                    $item['tp_hom']=0;
                    $item['commission_hom']=$result['commission_management'];
                    $item['incentive_hom']=$result['incentive_management'];
                }
                $item['sales_commission']=$item['tp_hom']*$item['commission_hom']/100;
                $item['incentive']=$item['tp_hom']*$item['incentive_hom']/100;
                $item['net_price']=$item['tp_hom']-$item['sales_commission']-$item['incentive'];
                if(isset($cogs[$result['variety_id']]))
                {
                    $item['cogs']=$cogs[$result['variety_id']]['cogs'];
                }
                else
                {
                    $item['cogs']=0;
                }

                if($indirect_costs)
                {
                    $item['general']=$item['cogs']*$indirect_costs['general']/100;
                    $item['marketing']=$item['cogs']*$indirect_costs['marketing']/100;
                    $item['finance']=$item['cogs']*$indirect_costs['finance']/100;
                }
                else
                {
                    $item['general']=0;
                    $item['marketing']=0;
                    $item['finance']=0;
                }
                if($item['net_price']==0)
                {
                    $item['profit']=0;
                    $item['profit_percentage']=0;
                }
                else
                {

                    $item['profit']=$item['net_price']-$item['cogs']-$item['general']-$item['marketing']-$item['finance'];
                    $item['profit_percentage']=$item['profit']*100/$item['net_price'];
                }
                $item['total_profit']=$item['profit']*$item['hom_target'];
                $item['total_net_price']=$item['net_price']*$item['hom_target'];

                $type_row['hom_target']+=$item['hom_target'];
                $crop_row['hom_target']+=$item['hom_target'];
                $grand_row['hom_target']+=$item['hom_target'];
                $type_row['total_profit']+=$item['total_profit'];
                $crop_row['total_profit']+=$item['total_profit'];
                $grand_row['total_profit']+=$item['total_profit'];
                $type_row['total_net_price']+=$item['total_net_price'];
                $crop_row['total_net_price']+=$item['total_net_price'];
                $grand_row['total_net_price']+=$item['total_net_price'];

                $items[]=$this->get_report_row($item);
            }
            if($type_row['total_net_price']!=0)
            {
                $type_row['profit_percentage']=$type_row['total_profit']*100/$type_row['total_net_price'];
            }
            else
            {
                $type_row['profit_percentage']=0;
            }
            $items[]=$this->get_report_row($type_row);
            if($crop_row['total_net_price']!=0)
            {
                $crop_row['profit_percentage']=$crop_row['total_profit']*100/$crop_row['total_net_price'];
            }
            else
            {
                $crop_row['profit_percentage']=0;
            }
            $items[]=$this->get_report_row($crop_row);
            if($grand_row['total_net_price']!=0)
            {
                $grand_row['profit_percentage']=$grand_row['total_profit']*100/$grand_row['total_net_price'];
            }
            else
            {
                $grand_row['profit_percentage']=0;
            }
            $items[]=$this->get_report_row($grand_row);
        }
        $this->jsonReturn($items);
    }
    private function get_report_row($item)
    {
        $row=array();
        $row['crop_name']=$item['crop_name'];
        $row['type_name']=$item['type_name'];
        $row['variety_name']=$item['variety_name'];
        if($item['hom_target']!=0)
        {
            $row['hom_target']=$item['hom_target'];
        }
        else
        {
            $row['hom_target']='';
        }
        if($item['tp_last_year']!=0)
        {
            $row['tp_last_year']=$item['tp_last_year'];
        }
        else
        {
            $row['tp_last_year']='';
        }
        if($item['tp_management']!=0)
        {
            $row['tp_management']=number_format($item['tp_management'],2);
        }
        else
        {
            $row['tp_management']='';
        }
        if($item['tp_hom']!=0)
        {
            $row['tp_hom']=$item['tp_hom'];
        }
        else
        {
            $row['tp_hom']='';
        }
        if($item['commission_hom']!=0)
        {
            $row['commission_hom']=$item['commission_hom'];
        }
        else
        {
            $row['commission_hom']='';
        }
        if($item['sales_commission']!=0)
        {
            $row['sales_commission']=number_format($item['sales_commission'],2);
        }
        else
        {
            $row['sales_commission']='';
        }
        if($item['incentive_hom']!=0)
        {
            $row['incentive_hom']=$item['incentive_hom'];
        }
        else
        {
            $row['incentive_hom']='';
        }
        if($item['incentive']!=0)
        {
            $row['incentive']=number_format($item['incentive'],2);
        }
        else
        {
            $row['incentive']='';
        }
        if($item['net_price']!=0)
        {
            $row['net_price']=number_format($item['net_price'],2);
        }
        else
        {
            $row['net_price']='';
        }
        if($item['cogs']!=0)
        {
            $row['cogs']=number_format($item['cogs'],2);
        }
        else
        {
            $row['cogs']='';
        }
        if($item['general']!=0)
        {
            $row['general']=number_format($item['general'],2);
        }
        else
        {
            $row['general']='';
        }
        if($item['marketing']!=0)
        {
            $row['marketing']=number_format($item['marketing'],2);
        }
        else
        {
            $row['marketing']='';
        }
        if($item['finance']!=0)
        {
            $row['finance']=number_format($item['finance'],2);
        }
        else
        {
            $row['finance']='';
        }
        if($item['profit']!=0)
        {
            $row['profit']=number_format($item['profit'],2);
        }
        else
        {
            $row['profit']='';
        }
        /*if($item['net_price']!=0)
        {
            $row['net_price']=number_format($item['net_price'],2);
        }
        else
        {
            $row['net_price']='';
        }
        if($item['sales_commission']!=0)
        {
            $row['sales_commission']=number_format($item['sales_commission'],2);
        }
        else
        {
            $row['sales_commission']='';
        }
        if($item['incentive']!=0)
        {
            $row['incentive']=number_format($item['incentive'],2);
        }
        else
        {
            $row['incentive']='';
        }
        if($item['trade_price']!=0)
        {
            $row['trade_price']=number_format($item['trade_price'],2);
        }
        else
        {
            $row['trade_price']='';
        }*/
        if($item['total_profit']!=0)
        {
            $row['total_profit']=number_format($item['total_profit'],2);
        }
        else
        {
            $row['total_profit']='';
        }
        if($item['total_net_price']!=0)
        {
            $row['total_net_price']=number_format($item['total_net_price'],2);
        }
        else
        {
            $row['total_net_price']='';
        }
        if($item['profit_percentage']!=0)
        {
            $row['profit_percentage']=number_format($item['profit_percentage'],2);
        }
        else
        {
            $row['profit_percentage']='';
        }

        return $row;

    }

}
