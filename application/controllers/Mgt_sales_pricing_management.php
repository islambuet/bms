<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mgt_sales_pricing_management extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Mgt_sales_pricing_management');
        $this->controller_url='mgt_sales_pricing_management';

    }

    public function index($action="search",$id1=0,$id2=0,$id3=0)
    {
        if($action=="search")
        {
            $this->system_search();
        }
        elseif($action=="edit")
        {
            $this->system_edit();
        }
        elseif($action=="get_edit_items")
        {
            $this->system_get_edit_items();
        }
        elseif($action=="save")
        {
            $this->system_save();
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

            $fy_info=System_helper::get_fiscal_years();
            $data['years']=$fy_info['years'];
            $data['year0_id']=$fy_info['budget_year']['value'];
            $data['crops']=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array(),0,0,array('ordering ASC'));
            $data['title']="Pricing Management Search";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("mgt_sales_pricing_management/search",$data,true));
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

    private function system_edit()
    {
        if(isset($this->permissions['edit'])&&($this->permissions['edit']==1))
        {
            $year0_id=$this->input->post('year0_id');
            $crop_id=$this->input->post('crop_id');

            $crop=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array('id ='.$crop_id),1);
            $data['years']=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"',' id >='.$year0_id),$this->config->item('num_year_prediction')+1,0,array('id ASC'));
            $data['year0_id']=$year0_id;
            $data['crop_id']=$crop_id;
            $keys=',';
            $keys.="year0_id:'".$year0_id."',";
            $keys.="crop_id:'".$crop_id."',";
            $data['keys']=trim($keys,',');


            $data['title']="Pricing Management For ".$crop['text'].'('.$data['years'][0]['text'].')';

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("mgt_sales_pricing_management/add_edit",$data,true));
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
    private function system_get_edit_items()
    {
        $items=array();
        $year0_id=$this->input->post('year0_id');
        $crop_id=$this->input->post('crop_id');

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

        $indirect_costs=Query_helper::get_info($this->config->item('table_mgt_indirect_cost_setup'),'*',array('status !="'.$this->config->item('system_status_delete').'"','year0_id ='.$year0_id),1);

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
        //getting previous saved by management
        //short by crop if needed
        $sales_pricing=array();
        $results=Query_helper::get_info($this->config->item('table_mgt_sales_pricing'),'*',array('year0_id ='.$year0_id));
        foreach($results as $result)
        {
            $sales_pricing[$result['variety_id']]=$result;
        }


        $this->db->from($this->config->item('ems_setup_classification_varieties').' v');
        $this->db->select('v.id variety_id,v.name variety_name');
        $this->db->select('type.name type_name');
        $this->db->join($this->config->item('ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->where('v.whose','ARM');
        $this->db->where('v.status =',$this->config->item('system_status_active'));
        $this->db->where('type.crop_id',$crop_id);
        $this->db->order_by('type.ordering','ASC');
        $this->db->order_by('v.ordering','ASC');

        $results=$this->db->get()->result_array();


        $prev_type='';
        foreach($results as $index=>$result)
        {
            $item=array();
            if($index>0)
            {
                if($prev_type!=$result['type_name'])
                {
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
            $item['variety_id']=$result['variety_id'];
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
                $automated_profit=$item['cogs']*$indirect_costs['profit']/100;
                $automated_net_price=$item['cogs']+$item['general']+$item['marketing']+$item['finance']+$automated_profit;
                $automated_sales_commission=$automated_net_price*$indirect_costs['sales_commission']/100;
                $automated_incentive=$automated_net_price*$indirect_costs['incentive']/100;
                //$item['trade_price']=$item['net_price']+$item['sales_commission']+$item['incentive'];
                $item['tp_automated']=$automated_net_price+$automated_sales_commission+$automated_incentive;

            }
            else
            {
                $item['general']=0;
                $item['marketing']=0;
                $item['finance']=0;
                //$item['profit']=0;
                //$item['net_price']=$item['cogs'];
                //$item['sales_commission']=0;
                //$item['incentive']=0;
                $item['tp_automated']=$item['cogs'];
            }
            if(isset($sales_pricing[$result['variety_id']]))
            {
                if(is_null($sales_pricing[$result['variety_id']]['tp_management']))
                {
                    $item['tp_mgt']=0;
                }
                else
                {
                    $item['tp_mgt']=$sales_pricing[$result['variety_id']]['tp_management'];
                }
                if(is_null($sales_pricing[$result['variety_id']]['commission_management']))
                {
                    $item['sales_commission_percentage']=0;
                }
                else
                {
                    $item['sales_commission_percentage']=$sales_pricing[$result['variety_id']]['commission_management'];
                }
                if(is_null($sales_pricing[$result['variety_id']]['incentive_management']))
                {
                    $item['incentive_percentage']=0;
                }
                else
                {
                    $item['incentive_percentage']=$sales_pricing[$result['variety_id']]['incentive_management'];
                }
            }
            else
            {
                $item['tp_mgt']=0;
                if($indirect_costs)
                {
                    $item['sales_commission_percentage']=$indirect_costs['sales_commission'];
                    $item['incentive_percentage']=$indirect_costs['incentive'];
                }
                else
                {
                    $item['sales_commission_percentage']=0;
                    $item['incentive_percentage']=0;

                }

            }
            $item['sales_commission']=$item['tp_mgt']*$item['sales_commission_percentage']/100;
            $item['incentive']=$item['tp_mgt']*$item['incentive_percentage']/100;
            $item['net_price']=$item['tp_mgt']-$item['sales_commission']-$item['incentive'];
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
            $item['total_net_price']=$item['hom_target']*$item['net_price'];
            $item['total_profit']=$item['hom_target']*$item['profit'];





            $items[]=$this->get_report_row($item);

        }

        $this->jsonReturn($items);

    }
    private function get_report_row($item)
    {
        $row=array();
        $row['type_name']=$item['type_name'];
        $row['variety_id']=$item['variety_id'];
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
        if($item['tp_automated']!=0)
        {
            $row['tp_automated']=number_format($item['tp_automated'],2);
        }
        else
        {
            $row['tp_automated']='';
        }
        if($item['tp_mgt']!=0)
        {
            $row['tp_mgt']=$item['tp_mgt'];
        }
        else
        {
            $row['tp_mgt']='';
        }
        if($item['sales_commission_percentage']!=0)
        {
            $row['sales_commission_percentage']=$item['sales_commission_percentage'];
        }
        else
        {
            $row['sales_commission_percentage']='';
        }
        if($item['sales_commission']!=0)
        {
            $row['sales_commission']=number_format($item['sales_commission'],2);
        }
        else
        {
            $row['sales_commission']='';
        }
        if($item['incentive_percentage']!=0)
        {
            $row['incentive_percentage']=$item['incentive_percentage'];
        }
        else
        {
            $row['incentive_percentage']='';
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
        if($item['total_net_price']!=0)
        {
            $row['total_net_price']=number_format($item['total_net_price'],2);
        }
        else
        {
            $row['total_net_price']='';
        }
        if($item['total_profit']!=0)
        {
            $row['total_profit']=number_format($item['total_profit'],2);
        }
        else
        {
            $row['total_profit']='';
        }
        if($item['profit_percentage']!=0)
        {
            $row['profit_percentage']=number_format($item['profit_percentage'],2);
        }
        else
        {
            $row['profit_percentage']=0;
        }
        return $row;

    }
    private function system_save()
    {
        if(isset($this->permissions['edit'])&&($this->permissions['edit']==1))
        {
            $year0_id=$this->input->post('year0_id');
            $crop_id=$this->input->post('crop_id');
            $user = User_helper::get_user();
            $time=time();

            $items=$this->input->post('items');
            $this->db->trans_start();
            if(sizeof($items)>0)
            {
                $sales_pricing=array();
                $results=Query_helper::get_info($this->config->item('table_mgt_sales_pricing'),'*',array('year0_id ='.$year0_id));
                foreach($results as $result)
                {
                    $sales_pricing[$result['variety_id']]=$result;
                }

                foreach($items as $variety_id=>$data)
                {
                    if(strlen(trim($data['tp_management']))==0)
                    {
                        $data['tp_management']=0;
                    }
                    if(strlen(trim($data['commission_management']))==0)
                    {
                        $data['commission_management']=0;
                    }
                    if(strlen(trim($data['incentive_management']))==0)
                    {
                        $data['incentive_management']=0;
                    }
                    if(isset($sales_pricing[$variety_id]))
                    {
                        $data['user_updated'] = $user->user_id;
                        $data['date_updated'] = $time;
                        $data['user_update_management'] = $user->user_id;
                        $data['date_updated_management'] = $time;
                        Query_helper::update($this->config->item('table_mgt_sales_pricing'),$data,array("id = ".$sales_pricing[$variety_id]['id']));
                    }
                    else
                    {
                        $data['year0_id']=$year0_id;
                        $data['variety_id']=$variety_id;
                        $data['user_created'] = $user->user_id;
                        $data['date_created'] = $time;
                        $data['user_created_management'] = $user->user_id;
                        $data['date_created_management'] = $time;
                        Query_helper::add($this->config->item('table_mgt_sales_pricing'),$data);
                    }

                }
            }

            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {
                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                $this->system_search();
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->jsonReturn($ajax);
            }
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
        }

    }

}
