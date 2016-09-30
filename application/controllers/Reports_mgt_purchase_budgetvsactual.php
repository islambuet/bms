<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reports_mgt_purchase_budgetvsactual extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Reports_mgt_purchase_budgetvsactual');
        $this->controller_url='reports_mgt_purchase_budgetvsactual';
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
            $data['title']="Budgeted vs Actual Purchase Report";
            $ajax['status']=true;
            $fy_info=System_helper::get_fiscal_years();
            $data['fiscal_years']=$fy_info['years'];
            $data['year0_id']=$fy_info['budget_year']['value']-1;//current fiscal year

            $data['crops']=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array(),0,0,array('ordering ASC'));
            //$data['principals']=Query_helper::get_info($this->config->item('ems_basic_setup_principal'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));

            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("reports_mgt_purchase_budgetvsactual/search",$data,true));
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
            $data['direct_costs']=Query_helper::get_info($this->config->item('table_setup_direct_cost_items'),array('id value,name text'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));
            $data['packing_costs']=Query_helper::get_info($this->config->item('table_setup_packing_material_items'),array('id value,name text'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));

            $ajax['status']=true;
            $data['title']="Budget v/s actual Purchase Report";
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("reports_mgt_purchase_budgetvsactual/list",$data,true));

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
        //$principal_id=$this->input->post('principal_id');
        $direct_costs_items=Query_helper::get_info($this->config->item('table_setup_direct_cost_items'),array('id value,name text'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));
        $packing_costs_items=Query_helper::get_info($this->config->item('table_setup_packing_material_items'),array('id value,name text'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));

        $results=Query_helper::get_info($this->config->item('table_mgt_direct_cost_percentage'),'*',array('status !="'.$this->config->item('system_status_delete').'"','fiscal_year_id ='.$year0_id));
        $direct_costs_percentage_budgeted=array();
        foreach($results as $result)
        {
            $direct_costs_percentage_budgeted[$result['item_id']]=$result['percentage'];
        }

        $this->db->from($this->config->item('table_mgt_packing_cost_kg').' pack_cost');
        $this->db->select('pack_cost.*');
        $this->db->where('pack_cost.year0_id',$year0_id);
        $this->db->group_by('pack_cost.variety_id');
        $results=$this->db->get()->result_array();
        $packing_costs=array();
        foreach($results as $result)
        {
            $packing_costs[$result['variety_id']][$result['packing_item_id']]=$result['cost'];
        }

        $results=Query_helper::get_info($this->config->item('table_mgt_currency_rate'),'*',array('status !="'.$this->config->item('system_status_delete').'"','fiscal_year_id ='.$year0_id));
        $currency_rates_budgeted=array();
        foreach($results as $result)
        {
            $currency_rates_budgeted[$result['currency_id']]=$result['rate'];
        }
        $this->db->from($this->config->item('table_mgt_purchase_budget').' purchase_budget');
        $this->db->select('purchase_budget.*');
        $this->db->where('purchase_budget.year0_id',$year0_id);
        $results=$this->db->get()->result_array();
        $purchase_budgeted=array();
        foreach($results as $result)
        {
            $quantity_total=0;
            for($i=1;$i<13;$i++)
            {
                if(($result['quantity_'.$i])>0)
                {
                    $quantity_total+=$result['quantity_'.$i];
                }
            }
            $purchase_budgeted[$result['variety_id']]['kg_budgeted']=$quantity_total;
            $purchase_budgeted[$result['variety_id']]['unit_price']=$result['unit_price'];
            $purchase_budgeted[$result['variety_id']]['currency_rate']=$currency_rates_budgeted[$result['currency_id']];

            //$purchase_budgeted[$result['variety_id']]['pi_budgeted']=$quantity_total*$result['unit_price']*$currency_rates_budgeted[$result['currency_id']];

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

        $this->db->order_by('crop.ordering','ASC');
        $this->db->order_by('type.ordering','ASC');
        $this->db->order_by('v.ordering','ASC');
        $results=$this->db->get()->result_array();
        $grand_row=array();
        $crop_row=array();
        $type_row=array();
        $grand_row['crop_name']='Grand Total';
        $crop_row['crop_name']=$type_row['crop_name']='';
        $crop_row['type_name']='Total Crop';
        $grand_row['type_name']=$type_row['type_name']='';
        $type_row['variety_name']='Total Type';
        $grand_row['variety_name']=$crop_row['variety_name']='';
        $grand_row['kg_budgeted']=$crop_row['kg_budgeted']=$type_row['kg_budgeted']=0;
        $grand_row['pi_budgeted']=$crop_row['pi_budgeted']=$type_row['pi_budgeted']=0;
        foreach($direct_costs_items as $dc)
        {
            $grand_row['dc_'.$dc['value'].'_budgeted']=$crop_row['dc_'.$dc['value'].'_budgeted']=$type_row['dc_'.$dc['value'].'_budgeted']=0;
        }
        foreach($packing_costs_items as $pc)
        {
            $grand_row['pc_'.$pc['value'].'_budgeted']=$crop_row['pc_'.$pc['value'].'_budgeted']=$type_row['pc_'.$pc['value'].'_budgeted']=0;
        }
        $grand_row['cogs_budgeted']=$crop_row['cogs_budgeted']=$type_row['cogs_budgeted']=0;

        $prev_crop_name='';
        $prev_crop_type_name='';
        foreach($results as $index=>$result)
        {
            $item=array();
            if($index>0)
            {
                if($prev_crop_name!=$result['crop_name'])
                {
                    $items[]=$this->get_report_row($crop_row,$direct_costs_items,$packing_costs_items);
                    $type_row['kg_budgeted']=0;
                    $type_row['pi_budgeted']=0;



                    $crop_row['kg_budgeted']=0;
                    $crop_row['pi_budgeted']=0;
                    foreach($direct_costs_items as $dc)
                    {
                        $type_row['dc_'.$dc['value'].'_budgeted']=0;
                        $crop_row['dc_'.$dc['value'].'_budgeted']=0;
                    }
                    foreach($packing_costs_items as $pc)
                    {
                        $type_row['pc_'.$pc['value'].'_budgeted']=0;
                        $crop_row['pc_'.$pc['value'].'_budgeted']=0;
                    }
                    $type_row['cogs_budgeted']=0;
                    $crop_row['cogs_budgeted']=0;

                    $item['crop_name']=$result['crop_name'];
                    $prev_crop_name=$result['crop_name'];

                    $item['type_name']=$result['type_name'];
                    $prev_crop_type_name=$result['type_name'];
                }
                elseif($prev_crop_type_name!=$result['type_name'])
                {
                    $items[]=$this->get_report_row($type_row,$direct_costs_items,$packing_costs_items);
                    $type_row['kg_budgeted']=0;
                    $type_row['pi_budgeted']=0;
                    foreach($direct_costs_items as $dc)
                    {
                        $type_row['dc_'.$dc['value'].'_budgeted']=0;
                    }
                    foreach($packing_costs_items as $pc)
                    {
                        $type_row['pc_'.$pc['value'].'_budgeted']=0;
                    }
                    $type_row['cogs_budgeted']=0;

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
            if(isset($purchase_budgeted[$result['variety_id']]))
            {
                $item['kg_budgeted']=$purchase_budgeted[$result['variety_id']]['kg_budgeted'];
                $type_row['kg_budgeted']+=$item['kg_budgeted'];
                $crop_row['kg_budgeted']+=$item['kg_budgeted'];
                $grand_row['kg_budgeted']+=$item['kg_budgeted'];

                $item['pi_budgeted']=$item['kg_budgeted']*$purchase_budgeted[$result['variety_id']]['unit_price']*$purchase_budgeted[$result['variety_id']]['currency_rate'];
                $type_row['pi_budgeted']+=$item['pi_budgeted'];
                $crop_row['pi_budgeted']+=$item['pi_budgeted'];
                $grand_row['pi_budgeted']+=$item['pi_budgeted'];
                $item['cogs_budgeted']=$purchase_budgeted[$result['variety_id']]['unit_price']*$purchase_budgeted[$result['variety_id']]['currency_rate'];;
            }
            else
            {
                $item['kg_budgeted']=0;
                $item['pi_budgeted']=0;
                $item['cogs_budgeted']=0;
            }
            $cogs_dc_budgeted=0;
            foreach($direct_costs_items as $dc)
            {
                if(isset($direct_costs_percentage_budgeted[$dc['value']]))
                {
                    $item['dc_'.$dc['value'].'_budgeted']=($direct_costs_percentage_budgeted[$dc['value']])*$item['pi_budgeted']/100;
                    $type_row['dc_'.$dc['value'].'_budgeted']+=$item['dc_'.$dc['value'].'_budgeted'];
                    $crop_row['dc_'.$dc['value'].'_budgeted']+=$item['dc_'.$dc['value'].'_budgeted'];
                    $grand_row['dc_'.$dc['value'].'_budgeted']+=$item['dc_'.$dc['value'].'_budgeted'];
                    $cogs_dc_budgeted+=$item['cogs_budgeted']*($direct_costs_percentage_budgeted[$dc['value']])/100;
                }
                else
                {
                    $item['dc_'.$dc['value'].'_budgeted']=0;
                }
            }
            $item['cogs_budgeted']+=$cogs_dc_budgeted;
            $cogs_pc_budgeted=0;
            foreach($packing_costs_items as $pc)
            {
                if(isset($packing_costs[$result['variety_id']][$pc['value']]))
                {
                    $item['pc_'.$pc['value'].'_budgeted']=$item['kg_budgeted']*$packing_costs[$result['variety_id']][$pc['value']];
                    $type_row['pc_'.$pc['value'].'_budgeted']+=$item['pc_'.$pc['value'].'_budgeted'];
                    $crop_row['pc_'.$pc['value'].'_budgeted']+=$item['pc_'.$pc['value'].'_budgeted'];
                    $grand_row['pc_'.$pc['value'].'_budgeted']+=$item['pc_'.$pc['value'].'_budgeted'];
                    $cogs_pc_budgeted+=$packing_costs[$result['variety_id']][$pc['value']];
                }
                else
                {
                    $item['pc_'.$pc['value'].'_budgeted']=0;
                }
            }
            $item['cogs_budgeted']+=$cogs_pc_budgeted;

            $items[]=$this->get_report_row($item,$direct_costs_items,$packing_costs_items);
        }
        $items[]=$this->get_report_row($type_row,$direct_costs_items,$packing_costs_items);
        $items[]=$this->get_report_row($crop_row,$direct_costs_items,$packing_costs_items);
        $items[]=$this->get_report_row($grand_row,$direct_costs_items,$packing_costs_items);


        $this->jsonReturn($items);
    }
    private function get_report_row($item,$direct_costs_items,$packing_costs_items)
    {
        $info=array();
        $info['crop_name']=$item['crop_name'];
        $info['type_name']=$item['type_name'];
        $info['variety_name']=$item['variety_name'];
        if($item['kg_budgeted']!=0)
        {
            $info['kg_budgeted']=number_format($item['kg_budgeted'],3,'.','');
        }
        else
        {
            $info['kg_budgeted']='';
        }
        if($item['pi_budgeted']!=0)
        {
            $info['pi_budgeted']=number_format($item['pi_budgeted'],2);
        }
        else
        {
            $info['pi_budgeted']='';
        }
        foreach($direct_costs_items as $dc)
        {
            if($item['dc_'.$dc['value'].'_budgeted']!=0)
            {
                $info['dc_'.$dc['value'].'_budgeted']=number_format($item['dc_'.$dc['value'].'_budgeted'],2);
            }
            else
            {
                $info['dc_'.$dc['value'].'_budgeted']='';
            }
        }
        foreach($packing_costs_items as $pc)
        {
            if($item['pc_'.$pc['value'].'_budgeted']!=0)
            {
                $info['pc_'.$pc['value'].'_budgeted']=number_format($item['pc_'.$pc['value'].'_budgeted'],2);
            }
            else
            {
                $info['pc_'.$pc['value'].'_budgeted']='';
            }
        }
        if($item['cogs_budgeted']!=0)
        {
            $info['cogs_budgeted']=number_format($item['cogs_budgeted'],2);
        }
        else
        {
            $info['cogs_budgeted']='';
        }
        return $info;
    }

}
