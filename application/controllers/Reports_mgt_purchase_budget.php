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
        foreach($results as $result)
        {
            $variety_items=$this->get_variety_row($result,$months,$currency_rates);
            foreach($variety_items as $item)
            {
                $items[]=$this->get_report_row($item);
            }
        }


        $this->jsonReturn($items);
    }
    private function get_variety_row($item,$months,$currency_rates)
    {
        $items=array();
        foreach($months as $month)
        {
            if($item['quantity_'.$month]>0)
            {
                $info=array();
                $info['crop_name']=$item['crop_name'];
                $info['type_name']=$item['type_name'];
                $info['principal_name']=$item['principal_name'];
                $info['variety_name']=$item['variety_name'];
                $info['variety_import_name']=$item['variety_import_name'];
                $info['month']=date("M", mktime(0, 0, 0,$month,1, 2000));
                $info['quantity']=$item['quantity_'.$month];
                $info['currency_name']=$item['currency_name'];
                if(isset($currency_rates[$item['currency_id']]))
                {
                    $info['currency_rate']=$currency_rates[$item['currency_id']];
                }
                else
                {
                    $info['currency_rate']=0;
                }
                $info['unit_price']=$item['unit_price'];

                $items[]=$info;
            }
        }
        return $items;
    }
    private function get_report_row($item)
    {
        $info=array();
        $info['crop_name']=$item['crop_name'];
        $info['type_name']=$item['type_name'];
        $info['principal_name']=$item['principal_name'];
        $info['variety_name']=$item['variety_name'];
        $info['variety_import_name']=$item['variety_import_name'];
        $info['month']=$item['month'];
        if($item['quantity']!=0)
        {
            $info['quantity']=number_format($item['quantity'],3,'.','');
        }
        else
        {
            $info['quantity']='';
        }
        $info['currency_name']=$item['currency_name'];
        $info['currency_rate']=$item['currency_rate'];
        if($item['unit_price']!=0)
        {
            $info['unit_price']=number_format($item['unit_price'],2);
        }
        else
        {
            $info['unit_price']='';
        }

        /*if($item['sales_net']!=0)
        {
            $info['sales_net']=number_format($item['sales_net'],2);
        }
        else
        {
            $info['sales_net']='';
        }*/
        return $info;
    }

}
