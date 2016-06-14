<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mgt_purchase_bud extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Mgt_purchase_bud');
        $this->controller_url='mgt_purchase_bud';
    }

    public function index($action="search",$id1=0,$id2=0)
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
            $this->get_items($id1);
        }
        elseif($action=="edit")
        {
            $this->system_edit($id1,$id2);
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
            $data['title']="Purchase Budget";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("mgt_purchase_bud/search",$data,true));
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
            $data['year0_id']=$this->input->post('year0_id');
            $data['title']="Variety List";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("mgt_purchase_bud/list",$data,true));
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
    private function get_items($year0_id)
    {
        $this->db->from($this->config->item('ems_setup_classification_varieties').' v');
        $this->db->select('v.id,v.name variety_name,v.status,v.ordering,v.whose,v.stock_id');
        $this->db->select('crop.name crop_name');
        $this->db->select('type.name type_name');
        $this->db->select('pb.quantity_total quantity_total');
        $this->db->join($this->config->item('ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->join($this->config->item('ems_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');
        $this->db->join($this->config->item('table_mgt_purchase_budget').' pb','pb.variety_id = v.id and year0_id ='.$year0_id,'LEFT');
        $this->db->where('v.whose','ARM');
        $this->db->order_by('crop.ordering');
        $this->db->order_by('type.ordering');
        $this->db->order_by('v.ordering');


        $this->db->where('v.status',$this->config->item('system_status_active'));
        $items=$this->db->get()->result_array();
        $this->jsonReturn($items);
    }

    private function system_edit($year0_id,$variety_id)
    {
        if(isset($this->permissions['edit'])&&($this->permissions['edit']==1))
        {
            if(($this->input->post('id')))
            {
                $variety_id=$this->input->post('id');
            }
            //initialize with previous data
            $data=Query_helper::get_info($this->config->item('table_mgt_purchase_budget'),'*',array('year0_id ='.$year0_id,'variety_id ='.$variety_id),1);
            $result=Query_helper::get_info($this->config->item('table_hom_bud_hom_bt'),'*',array('year0_id ='.$year0_id,'variety_id ='.$variety_id),1);
            if($result)
            {
                $data['hom_budget']=$result['year0_budget_quantity'];
            }
            else
            {
                $data['hom_budget']=0;
            }
            $current_stock=System_helper::get_stocks(0,0,$variety_id);
            if($current_stock)
            {
                $data['current_stock']=number_format($current_stock[$variety_id]['current_stock']/1000,3,'.','');
            }
            else
            {
                $data['current_stock']=0;
            }
            $result=Query_helper::get_info($this->config->item('table_hom_bud_variance'),'*',array('year0_id ='.$year0_id,'variety_id ='.$variety_id),1);
            if($result)
            {
                $data['final_variance']=$result['year0_variance_quantity'];
            }
            else
            {
                $data['final_variance']=0;
            }
            $data['quantity_needed']=$data['hom_budget']-$data['final_variance'];
            if($data['quantity_needed']<0)
            {
                $data['quantity_needed']=0;
            }
            $data['currencies']=Query_helper::get_info($this->config->item('table_setup_currency'),array('id value','name text'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));
            //get already saved items

            //$data['quantity_purchased']=0;
            /*$data['quantity_1']=0;
            $data['quantity_2']=0;
            $data['quantity_3']=0;
            $data['quantity_4']=0;
            $data['quantity_5']=0;
            $data['quantity_6']=0;
            $data['quantity_7']=0;
            $data['quantity_8']=0;
            $data['quantity_9']=0;
            $data['quantity_10']=0;
            $data['quantity_11']=0;
            $data['quantity_12']=0;*/

            //additional infos
            //currency rates
            $rates=Query_helper::get_info($this->config->item('table_mgt_currency_rate'),'*',array('status !="'.$this->config->item('system_status_delete').'"','fiscal_year_id ='.$year0_id));
            $data['currency_rates']=array();
            foreach($rates as $rate)
            {
                $data['currency_rates'][$rate['currency_id']]=$rate['rate'];
            }
            //total direct costs
            $result=$results=Query_helper::get_info($this->config->item('table_mgt_direct_cost_percentage'),array('SUM(percentage) total_percentage'),array('status !="'.$this->config->item('system_status_delete').'"','fiscal_year_id ='.$year0_id),1);
            if($result)
            {
                if(strlen($result['total_percentage'])>0)
                {
                    $data['direct_costs_percentage']=number_format($result['total_percentage']/100,5,'.','');
                }
                else
                {
                    $data['direct_costs_percentage']=0;
                }

            }
            else
            {
                $data['direct_costs_percentage']=0;
            }
            $result=Query_helper::get_info($this->config->item('table_mgt_packing_cost_kg'),array('SUM(cost) total_cost'),array('year0_id ='.$year0_id,'variety_id ='.$variety_id),1);
            if($result)
            {
                if(strlen($result['total_cost'])>0)
                {
                    $data['packing_cost']=$result['total_cost'];
                }
                else
                {
                    $data['packing_cost']=0;
                }

            }
            else
            {
                $data['packing_cost']=0;
            }

            $variety=Query_helper::get_info($this->config->item('ems_setup_classification_varieties'),array('id value','name text','name_import','principal_id'),array('id ='.$variety_id),1);
            $year=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text'),array('id ='.$year0_id),1);
            $data['year0_id']=$year0_id;
            $data['variety_id']=$variety_id;
            $data['variety_info']=$variety;
            $data['variety_info']['principal_name']='-';
            if($variety['principal_id']>0)
            {
                $result=Query_helper::get_info($this->config->item('ems_basic_setup_principal'),array('name text'),array('id ='.$variety['principal_id']),1);
                $data['variety_info']['principal_name']=$result['text'];
            }
            $data['title']="Purchase Budget For ".$variety['text'].'('.$year['text'].')';
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("mgt_purchase_bud/add_edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url."/index/edit/".$year0_id.'/'.$variety_id);
            $this->jsonReturn($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
        }
    }
    private function system_save()
    {
        $user = User_helper::get_user();
        $time=time();
        $year0_id=$this->input->post('year0_id');
        $variety_id=$this->input->post('variety_id');
        if(!(isset($this->permissions['edit'])&&($this->permissions['edit']==1)))
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
            die();
        }
        if(!$this->check_validation())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->jsonReturn($ajax);
        }
        else
        {

            $data=$this->input->post('purchase');
            $data['quantity_total']=0;
            for($i=1;$i<13;$i++)
            {
                if(($data['quantity_'.$i])>0)
                {
                    $data['quantity_total']+=$data['quantity_'.$i];
                }
                else
                {
                    $data['quantity_'.$i]=0;
                }
            }
            if($data['quantity_total']>0)
            {
                $this->db->trans_start();
                $result=Query_helper::get_info($this->config->item('table_mgt_purchase_budget'),'*',array('year0_id ='.$year0_id,'variety_id ='.$variety_id),1);
                if($result)
                {
                    $data['user_updated'] = $user->user_id;
                    $data['date_updated'] = $time;
                    Query_helper::update($this->config->item('table_mgt_purchase_budget'),$data,array("id = ".$result['id']));
                }
                else
                {
                    $data['year0_id']=$year0_id;
                    $data['variety_id']=$variety_id;
                    $data['user_created'] = $user->user_id;
                    $data['date_created'] = $time;
                    Query_helper::add($this->config->item('table_mgt_purchase_budget'),$data);
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
                $ajax['system_message']="Purchase Quantity Cannot be 0";
                $this->jsonReturn($ajax);
            }
        }
    }
    private function check_validation()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('purchase[currency_id]','Currency','required');
        $this->form_validation->set_rules('purchase[unit_price]','Unit Price','required');

        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }
}
