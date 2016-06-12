<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mgt_packing_material_cost extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Mgt_packing_material_cost');
        $this->controller_url='mgt_packing_material_cost';
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
            $data['title']="Packing Material Cost Setup";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("mgt_packing_material_cost/search",$data,true));
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
            $data['packing_items']=Query_helper::get_info($this->config->item('table_setup_packing_material_items'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC'));
            $data['title']="Variety List";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("mgt_packing_material_cost/list",$data,true));
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
        $this->db->join($this->config->item('ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->join($this->config->item('ems_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');
        $this->db->where('v.whose','ARM');
        $this->db->order_by('crop.ordering');
        $this->db->order_by('type.ordering');
        $this->db->order_by('v.ordering');
        $this->db->where('v.status',$this->config->item('system_status_active'));
        $items=$this->db->get()->result_array();
        $packing_items=Query_helper::get_info($this->config->item('table_setup_packing_material_items'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));

        $results=Query_helper::get_info($this->config->item('table_mgt_packing_cost_kg'),array('variety_id variety_id','packing_item_id packing_item_id','cost cost'),array('year0_id ='.$year0_id));
        $prev_packing_cost=array();
        foreach($results as $result)
        {
            $prev_packing_cost[$result['variety_id']][$result['packing_item_id']]=$result;
        }
        foreach($items as &$item)
        {
            foreach($packing_items as &$pack_item)
            {
                if(isset($prev_packing_cost[$item['id']][$pack_item['value']]))
                {
                    $item['item_'.$pack_item['value']]=number_format($prev_packing_cost[$item['id']][$pack_item['value']]['cost'],2);
                }
                else
                {
                    $item['item_'.$pack_item['value']]='';
                }
            }
        }

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
            $results=Query_helper::get_info($this->config->item('table_mgt_packing_cost_kg'),array('id value','packing_item_id packing_item_id','cost cost'),array('year0_id ='.$year0_id,'variety_id ='.$variety_id));
            $data['prev_packing_cost']=array();
            foreach($results as $result)
            {
                $data['prev_packing_cost'][$result['packing_item_id']]=$result;
            }
            $data['packing_items']=Query_helper::get_info($this->config->item('table_setup_packing_material_items'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC'));
            $variety=Query_helper::get_info($this->config->item('ems_setup_classification_varieties'),array('id value','name text'),array('id ='.$variety_id),1);
            $year=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text'),array('id ='.$year0_id),1);
            $data['year0_id']=$year0_id;
            $data['variety_id']=$variety_id;
            $data['title']="Packing Price For ".$variety['text'].'('.$year['text'].')';
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("mgt_packing_material_cost/add_edit",$data,true));
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

        $items=$this->input->post('items');
        $this->db->trans_start();
        if(sizeof($items)>0)
        {
            $results=Query_helper::get_info($this->config->item('table_mgt_packing_cost_kg'),array('id value','packing_item_id packing_item_id','cost cost'),array('year0_id ='.$year0_id,'variety_id ='.$variety_id));
            $prev_packing_cost=array();
            foreach($results as $result)
            {
                $prev_packing_cost[$result['packing_item_id']]=$result;
            }

            foreach($items as $packing_item_id=>$cost)
            {
                $data=array();
                {
                    $data['year0_id']=$year0_id;
                    $data['variety_id']=$variety_id;
                    if(strlen($cost==0))
                    {
                        $data['cost']=0;
                    }
                    else
                    {
                        $data['cost']=$cost;
                    }
                    if(isset($prev_packing_cost[$packing_item_id]))
                    {
                        $data['user_updated'] = $user->user_id;
                        $data['date_updated'] = $time;
                        Query_helper::update($this->config->item('table_mgt_packing_cost_kg'),$data,array("id = ".$prev_packing_cost[$packing_item_id]['value']));
                    }
                    else
                    {
                        $data['packing_item_id'] = $packing_item_id;
                        $data['user_created'] = $user->user_id;
                        $data['date_created'] = $time;
                        Query_helper::add($this->config->item('table_mgt_packing_cost_kg'),$data);
                    }
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
}
