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
            $data['principals']=Query_helper::get_info($this->config->item('ems_basic_setup_principal'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));

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
        $principal_id=$this->input->post('principal_id');

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
        }
        else
        {
            $info['np_kg_budgeted']='';
        }
        if($item['np_kg_actual']!=0)
        {
            $info['np_kg_actual']=number_format($item['np_kg_actual'],2);
        }
        else
        {
            $info['np_kg_actual']='';

        }
        if(($item['np_kg_budgeted']-$item['np_kg_actual'])!=0)
        {
            $info['np_kg_variance']=number_format(($item['np_kg_budgeted']-$item['np_kg_actual']),2);
        }
        else
        {
            $info['np_kg_variance']='';
        }
        if($item['np_total_budgeted']!=0)
        {
            $info['np_total_budgeted']=number_format($item['np_total_budgeted'],2);

        }
        else
        {
            $info['np_total_budgeted']='';
        }

        if($item['np_total_actual']!=0)
        {
            $info['np_total_actual']=number_format($item['np_total_actual'],2);

        }
        else
        {
            $info['np_total_actual']='';
        }
        if(($item['np_total_budgeted']-$item['np_total_actual'])!=0)
        {
            $info['np_total_variance']=number_format(($item['np_total_budgeted']-$item['np_total_actual']),2);
        }
        else
        {
            $info['np_total_variance']='';
        }
        
        return $info;
    }

}
