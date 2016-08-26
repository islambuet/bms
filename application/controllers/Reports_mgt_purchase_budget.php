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
            $keys=',';

            foreach($reports as $elem=>$value)
            {
                $keys.=$elem.":'".$value."',";
            }

            $data['keys']=trim($keys,',');


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

        $purchase_budget=array();
        $this->db->from($this->config->item('table_mgt_purchase_budget').' purchase_budget');
        $this->db->select('purchase_budget.*');
        $this->db->where('purchase_budget.year0_id',$year0_id);
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            $purchase_budget[$result['variety_id']]=$result;
        }


        //variety list
        $this->db->from($this->config->item('ems_setup_classification_varieties').' v');
        $this->db->select('v.id variety_id,v.name variety_name,v.name_import variety_import_name');
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

        $grand_row=$crop_row=$type_row=array();
        $grand_row['crop_name']=$crop_row['crop_name']=$type_row['crop_name']='';
        $grand_row['type_name']=$crop_row['type_name']=$type_row['type_name']='';
        $grand_row['variety_name']=$crop_row['variety_name']=$type_row['variety_name']='';
        $grand_row['variety_import_name']=$crop_row['variety_import_name']=$type_row['variety_import_name']='';
        $type_row['variety_name']='Total Type';
        $crop_row['type_name']='Total Crop';
        $grand_row['crop_name']='Grand Total';
        $grand_row['quantity_total']=$crop_row['quantity_total']=$type_row['quantity_total']=0;

        $prev_crop_name='';
        $prev_crop_type_name='';

        foreach($results as $index=>$result)
        {
            $item=array();
            if($index>0)
            {
                if($prev_crop_name!=$result['crop_name'])
                {
                    $items[]=$this->get_report_row($type_row);
                    $type_row['quantity_total']=0;


                    $items[]=$this->get_report_row($crop_row);
                    $crop_row['quantity_total']=0;

                    $item['crop_name']=$result['crop_name'];
                    $prev_crop_name=$result['crop_name'];

                    $item['type_name']=$result['type_name'];
                    $prev_crop_type_name=$result['type_name'];
                }
                elseif($prev_crop_type_name!=$result['type_name'])
                {
                    $items[]=$this->get_report_row($type_row);
                    $type_row['quantity_total']=0;


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
            $item['variety_import_name']=$result['variety_import_name'];
            $item['quantity_total']=0;
            if(isset($purchase_budget[$result['variety_id']]))
            {
                if($purchase_budget[$result['variety_id']]['quantity_total']>0)
                {
                    $item['quantity_total']=$purchase_budget[$result['variety_id']]['quantity_total'];
                }
            }
            $type_row['quantity_total']+=$item['quantity_total'];
            $crop_row['quantity_total']+=$item['quantity_total'];
            $grand_row['quantity_total']+=$item['quantity_total'];

            /*$item['budget_kg']=0;
            $item['target_kg']=0;
            if(isset($bud_target[$result['variety_id']]))
            {
                if($bud_target[$result['variety_id']]['year0_budget_quantity']>0)
                {
                    $item['budget_kg']=$bud_target[$result['variety_id']]['year0_budget_quantity'];
                }
                if($bud_target[$result['variety_id']]['year0_target_quantity']>0)
                {
                    $item['target_kg']=$bud_target[$result['variety_id']]['year0_target_quantity'];
                }
            }

            $type_row['budget_kg']+=$item['budget_kg'];
            $crop_row['budget_kg']+=$item['budget_kg'];
            $grand_row['budget_kg']+=$item['budget_kg'];

            $type_row['target_kg']+=$item['target_kg'];
            $crop_row['target_kg']+=$item['target_kg'];
            $grand_row['target_kg']+=$item['target_kg'];

            $item['budget_net']=0;//get net price for kg
            $item['target_net']=0;//get net price for kg

            if((isset($variety_prices[$result['variety_id']]))&&($variety_prices[$result['variety_id']]>0))
            {
                $item['budget_net']=$variety_prices[$result['variety_id']]*$item['budget_kg'];
                $item['target_net']=$variety_prices[$result['variety_id']]*$item['target_kg'];

            }
            $type_row['budget_net']+=$item['budget_net'];
            $crop_row['budget_net']+=$item['budget_net'];
            $grand_row['budget_net']+=$item['budget_net'];

            $type_row['target_net']+=$item['target_net'];
            $crop_row['target_net']+=$item['target_net'];
            $grand_row['target_net']+=$item['target_net'];

            $item['sales_kg']=0;
            $item['sales_net']=0;
            if((isset($sales_total[$result['variety_id']]))&&($sales_total[$result['variety_id']]['quantity']!=null))
            {
                $item['sales_kg']=$sales_total[$result['variety_id']]['quantity']/1000;
                $item['sales_net']=$sales_total[$result['variety_id']]['net_sales'];
            }
            else
            {
                $item['sales_kg']=0;
                $item['sales_net']=0;
            }
            $type_row['sales_kg']+=$item['sales_kg'];
            $crop_row['sales_kg']+=$item['sales_kg'];
            $grand_row['sales_kg']+=$item['sales_kg'];
            $type_row['sales_net']+=$item['sales_net'];
            $crop_row['sales_net']+=$item['sales_net'];
            $grand_row['sales_net']+=$item['sales_net'];*/

            $items[]=$this->get_report_row($item);
        }
        $items[]=$this->get_report_row($type_row);
        $items[]=$this->get_report_row($crop_row);
        $items[]=$this->get_report_row($grand_row);
        $this->jsonReturn($items);
    }
    private function get_report_row($item)
    {
        $info=array();
        $info['crop_name']=$item['crop_name'];
        $info['type_name']=$item['type_name'];
        $info['variety_name']=$item['variety_name'];
        $info['variety_import_name']=$item['variety_import_name'];
        if($item['quantity_total']!=0)
        {
            $info['quantity_total']=number_format($item['quantity_total'],3,'.','');
        }
        else
        {
            $info['quantity_total']='';
        }
        /*if($item['target_kg']!=0)
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
        if($item['budget_net']!=0)
        {
            $info['budget_net']=number_format($item['budget_net'],2);
        }
        else
        {
            $info['budget_net']='';
        }
        if($item['target_net']!=0)
        {
            $info['target_net']=number_format($item['target_net'],2);
        }
        else
        {
            $info['target_net']='';
        }
        if($item['sales_net']!=0)
        {
            $info['sales_net']=number_format($item['sales_net'],2);
        }
        else
        {
            $info['sales_net']='';
        }
        if(($item['target_net']-$item['sales_net'])!=0)
        {
            $info['variance_net']=number_format(($item['target_net']-$item['sales_net']),3,'.','');
        }
        else
        {
            $info['variance_net']='';
        }*/
        return $info;
    }

}
