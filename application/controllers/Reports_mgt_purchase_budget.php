<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reports_mgt_purchase_budget extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Reports_mgt_purchase_budget');
        $this->controller_url='reports_mgt_purchase_budget';
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
            $data['title']="Purchase Budget Report";
            $ajax['status']=true;

            $fy_info=System_helper::get_fiscal_years();
            $data['fiscal_years']=$fy_info['years'];
            $data['year0_id']=$fy_info['budget_year']['value']-1;//current fiscal year

            $data['crops']=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array(),0,0,array('ordering ASC'));
            $data['principals']=Query_helper::get_info($this->config->item('ems_basic_setup_principal'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));

            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("reports_mgt_purchase_budget/search",$data,true));
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
            $data['title']="Purchase Budget Report";

            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("reports_mgt_purchase_budget/list",$data,true));

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
        $where='';
        for($i=1;$i<13;$i++)
        {
            if($this->input->post('month_'.$i)==1)
            {
                $months[]=$i;
                $where.=' or quantity_'.$i.' > 0';
            }
        }
        $where='('.substr($where,4).')';

        //currency rates
        $results=Query_helper::get_info($this->config->item('table_mgt_currency_rate'),'*',array('status !="'.$this->config->item('system_status_delete').'"','fiscal_year_id ='.$year0_id));
        $currency_rates=array();
        foreach($results as $result)
        {
            $currency_rates[$result['currency_id']]=$result['rate'];
        }
        $result=Query_helper::get_info($this->config->item('table_mgt_direct_cost_percentage'),array('SUM(percentage) total_percentage'),array('status !="'.$this->config->item('system_status_delete').'"','fiscal_year_id ='.$year0_id),1);
        $direct_costs_percentage=0;
        if($result)
        {
            if(strlen($result['total_percentage'])>0)
            {
                $direct_costs_percentage=number_format($result['total_percentage']/100,5,'.','');
            }
        }
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


        //variety list from purchase table
        $this->db->from($this->config->item('table_mgt_purchase_budget').' purchase_budget');
        $this->db->select('purchase_budget.*');
        $this->db->select('v.name variety_name,v.name_import variety_import_name');
        $this->db->select('type.id type_id,type.name type_name');
        $this->db->select('crop.id crop_id,crop.name crop_name');
        $this->db->select('c.name currency_name');
        $this->db->select('p.id principal_id,p.name principal_name');
        $this->db->join($this->config->item('ems_setup_classification_varieties').' v','v.id = purchase_budget.variety_id','INNER');
        $this->db->join($this->config->item('ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->join($this->config->item('ems_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');

        $this->db->join($this->config->item('table_setup_currency').' c','c.id = purchase_budget.currency_id','INNER');
        $this->db->join($this->config->item('ems_basic_setup_principal').' p','p.id = v.principal_id','LEFT');
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
        if($principal_id>0)
        {
            $this->db->where('p.id',$principal_id);
        }
        $this->db->where('purchase_budget.year0_id',$year0_id);
        $this->db->where($where);
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
            $item['principal_name']=$result['principal_name'];
            $item['variety_name']=$result['variety_name'];
            $item['variety_import_name']=$result['variety_import_name'];
            $item['months']=',';
            $item['currency_name']=$result['currency_name'];
            $item['quantity']=0;
            foreach($months as $month)
            {
                if($result['quantity_'.$month]>0)
                {
                    $item['quantity']+=$result['quantity_'.$month];
                    $item['months'].=date("M", mktime(0, 0, 0,$month,1, 2000)).',';
                }
            }
            $item['months']=trim($item['months'],',');
            $grand_row['quantity']+=$item['quantity'];
            $item['currency_rate']=0;
            if(isset($currency_rates[$result['currency_id']]))
            {
                $item['currency_rate']=$currency_rates[$result['currency_id']];
            }
            else
            {
                $item['currency_rate']=0;
            }
            $item['unit_price']=$result['unit_price'];


            $item['pi_values']=$item['quantity']*$item['currency_rate']*$item['unit_price'];
            $item['cogs']=$item['currency_rate']*$item['unit_price'];//unit price in bdt
            $item['direct_cost']=$item['quantity']*$item['cogs']*$direct_costs_percentage;//total direct cost
            $item['cogs']=$item['cogs']+$item['cogs']*$direct_costs_percentage;//unit price + unit direct cost

            $item['packing_cost']=0;
            if(isset($packing_costs[$result['variety_id']]))
            {
                $item['packing_cost']=$item['quantity']*$packing_costs[$result['variety_id']];
                $item['cogs']=$item['cogs']+$packing_costs[$result['variety_id']];
            }

            $item['total_cogs']=$item['quantity']*$item['cogs'];

            $grand_row['direct_cost']+=$item['direct_cost'];
            $grand_row['packing_cost']+=$item['packing_cost'];

            $grand_row['pi_values']+=$item['pi_values'];
            $grand_row['total_cogs']+=$item['total_cogs'];

            $items[]=$this->get_report_row($item);
        }
        $items[]=$this->get_report_row($grand_row);
        $this->jsonReturn($items);


        $this->jsonReturn($items);
    }
    private function get_report_row($item)
    {
        $info=array();
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
