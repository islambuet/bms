<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mgt_purchase_varieties_actual extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Mgt_purchase_varieties_actual');
        $this->controller_url='mgt_purchase_varieties_actual';
    }

    public function index($action="list",$id=0)
    {
        if($action=="list")
        {
            $this->system_list($id);
        }
        elseif($action=="get_items")
        {
            $this->get_items();
        }
        elseif($action=="edit")
        {
            $this->system_edit($id);
        }
        elseif($action=="save")
        {
            $this->system_save();
        }
        elseif($action=="details")
        {
            $this->system_details($id);
        }

        elseif($action=="get_varieties")
        {
            $this->get_varieties($id);
        }
        else
        {
            $this->system_list($id);
        }
    }

    private function system_list()
    {

        if(isset($this->permissions['view'])&&($this->permissions['view']==1))
        {
            $data['title']="Varieties in Consignment";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("mgt_purchase_varieties_actual/list",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url);
            $this->jsonReturn($ajax);
        }
        else
        {
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
        }
    }
    private function get_items()
    {
        $this->db->from($this->config->item('table_mgt_purchase_consignments').' consignments');
        $this->db->select('consignments.*');
        $this->db->select('fy.name fiscal_year_name');
        $this->db->select('principal.name principal_name');
        $this->db->select('Count(cv.variety_id) num_varieties');
        $this->db->select('SUM(cv.quantity) total_quantity');
        $this->db->join($this->config->item('ems_basic_setup_fiscal_year').' fy','fy.id = consignments.year0_id','INNER');
        $this->db->join($this->config->item('ems_basic_setup_principal').' principal','principal.id = consignments.principal_id','INNER');
        $this->db->join($this->config->item('table_mgt_purchase_consignment_varieties').' cv','consignments.id = cv.consignment_id and cv.revision =1','LEFT');

        $this->db->order_by('consignments.year0_id','DESC');
        $this->db->order_by('consignments.id','DESC');
        $this->db->group_by('consignments.id');
        $results=$this->db->get()->result_array();
        $items=array();
        foreach($results as $result)
        {
            $item=array();
            $item['id']=$result['id'];
            $item['fiscal_year_name']=$result['fiscal_year_name'];
            $item['principal_name']=$result['principal_name'];
            $item['name']=$result['name'];
            $item['month']=date("M", mktime(0, 0, 0,  $result['month'],1, 2000));
            $item['date_purchase']=System_helper::display_date($result['date_purchase']);
            $item['num_varieties']=$result['num_varieties'];
            $item['total_quantity']=number_format($result['total_quantity'],3,'.','');
            $items[]=$item;
        }
        $this->jsonReturn($items);

    }
    private function system_edit($id)
    {
        if(isset($this->permissions['edit'])&&($this->permissions['edit']==1))
        {
            if(($this->input->post('id')))
            {
                $consignment_id=$this->input->post('id');
            }
            else
            {
                $consignment_id=$id;
            }
            $this->db->from($this->config->item('table_mgt_purchase_consignments').' consignments');
            $this->db->select('consignments.*');
            $this->db->select('fy.name fiscal_year_name');
            $this->db->select('principal.name principal_name');
            $this->db->select('cur.name currency_name');
            $this->db->join($this->config->item('ems_basic_setup_fiscal_year').' fy','fy.id = consignments.year0_id','INNER');
            $this->db->join($this->config->item('table_setup_currency').' cur','cur.id = consignments.currency_id','INNER');
            $this->db->join($this->config->item('ems_basic_setup_principal').' principal','principal.id = consignments.principal_id','INNER');
            $this->db->where('consignments.id',$consignment_id);

            $data['consignment']=$this->db->get()->row_array();
            $data['direct_cost_items']=Query_helper::get_info($this->config->item('table_setup_direct_cost_items'),array('id value','name text'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));
            $data['direct_costs']=array();
            $results=Query_helper::get_info($this->config->item('table_mgt_purchase_consignment_costs'),array('item_id','cost'),array('consignment_id ='.$consignment_id,'revision =1'));
            foreach($results as $result)
            {
                $data['direct_costs'][$result['item_id']]=$result;
            }
            $data['crops']=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));

            $this->db->from($this->config->item('table_mgt_purchase_consignment_varieties').' cv');
            $this->db->select('cv.*');
            $this->db->select('v.name_import variety_name');
            $this->db->select('crop_type.name crop_type_name');
            $this->db->select('crop.name crop_name');
            $this->db->join($this->config->item('ems_setup_classification_varieties').' v','v.id =cv.variety_id','INNER');
            $this->db->join($this->config->item('ems_setup_classification_crop_types').' crop_type','crop_type.id =v.crop_type_id','INNER');
            $this->db->join($this->config->item('ems_setup_classification_crops').' crop','crop.id =crop_type.crop_id','INNER');
            $this->db->where('cv.consignment_id',$consignment_id);
            $this->db->where('cv.revision',1);
            $data['varieties']=$this->db->get()->result_array();
            $data['title']="Edit Varieties in Consignment (".$data['consignment']['name'].')';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("mgt_purchase_varieties_actual/add_edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit/'.$consignment_id);
            $this->jsonReturn($ajax);
        }
        else
        {
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
        }
    }

    private function system_details($id)
    {
        if(isset($this->permissions['edit'])&&($this->permissions['edit']==1))
        {
            if(($this->input->post('id')))
            {
                $consignment_id=$this->input->post('id');
            }
            else
            {
                $consignment_id=$id;
            }
            $this->db->from($this->config->item('table_mgt_purchase_consignments').' consignments');
            $this->db->select('consignments.*');
            $this->db->select('fy.name fiscal_year_name');
            $this->db->select('principal.name principal_name');
            $this->db->select('cur.name currency_name');
            $this->db->join($this->config->item('ems_basic_setup_fiscal_year').' fy','fy.id = consignments.year0_id','INNER');
            $this->db->join($this->config->item('table_setup_currency').' cur','cur.id = consignments.currency_id','INNER');
            $this->db->join($this->config->item('ems_basic_setup_principal').' principal','principal.id = consignments.principal_id','INNER');
            $this->db->where('consignments.id',$consignment_id);

            $data['consignment']=$this->db->get()->row_array();
            $data['direct_cost_items']=Query_helper::get_info($this->config->item('table_setup_direct_cost_items'),array('id value','name text'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));
            $data['direct_costs']=array();
            $results=Query_helper::get_info($this->config->item('table_mgt_purchase_consignment_costs'),array('item_id','cost'),array('consignment_id ='.$consignment_id,'revision =1'));
            foreach($results as $result)
            {
                $data['direct_costs'][$result['item_id']]=$result;
            }
            $data['packing_items']=Query_helper::get_info($this->config->item('table_setup_packing_material_items'),array('id value','name text'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));
            $data['title']="Details of Consignment (".$data['consignment']['name'].')';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("mgt_purchase_varieties_actual/details",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/details/'.$consignment_id);
            $this->jsonReturn($ajax);
        }
        else
        {
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
        }
    }
    private function get_varieties($id)
    {
        if($this->input->post('consignment_id')>0)
        {
            $consignment_id=$this->input->post('consignment_id');
        }
        else
        {
            $consignment_id=$id;
        }
        $this->db->from($this->config->item('table_mgt_purchase_consignment_varieties').' cv');
        $this->db->select('cv.*');
        $this->db->select('v.name_import variety_name');
        $this->db->select('crop_type.name crop_type_name');
        $this->db->select('crop.name crop_name');
        $this->db->join($this->config->item('ems_setup_classification_varieties').' v','v.id =cv.variety_id','INNER');
        $this->db->join($this->config->item('ems_setup_classification_crop_types').' crop_type','crop_type.id =v.crop_type_id','INNER');
        $this->db->join($this->config->item('ems_setup_classification_crops').' crop','crop.id =crop_type.crop_id','INNER');
        $this->db->where('cv.consignment_id',$consignment_id);
        $this->db->where('cv.revision',1);
        $items=array();
        $varieties=$this->db->get()->result_array();
        if(sizeof($varieties)>0)
        {
            $this->db->from($this->config->item('table_mgt_purchase_consignments').' consignments');
            $this->db->select('consignments.*');
            $this->db->select('fy.name fiscal_year_name');
            $this->db->select('principal.name principal_name');
            $this->db->select('cur.name currency_name');
            $this->db->join($this->config->item('ems_basic_setup_fiscal_year').' fy','fy.id = consignments.year0_id','INNER');
            $this->db->join($this->config->item('table_setup_currency').' cur','cur.id = consignments.currency_id','INNER');
            $this->db->join($this->config->item('ems_basic_setup_principal').' principal','principal.id = consignments.principal_id','INNER');
            $this->db->where('consignments.id',$consignment_id);

            $consignment=$this->db->get()->row_array();
            $direct_cost_items=Query_helper::get_info($this->config->item('table_setup_direct_cost_items'),array('id value','name text'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));
            $direct_costs=array();
            $results=Query_helper::get_info($this->config->item('table_mgt_purchase_consignment_costs'),array('item_id','cost'),array('consignment_id ='.$consignment_id,'revision =1'));
            foreach($results as $result)
            {
                $direct_costs[$result['item_id']]=$result;
            }
            $packing_items=Query_helper::get_info($this->config->item('table_setup_packing_material_items'),array('id value','name text'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));
            $packing_costs=array();
            $results=Query_helper::get_info($this->config->item('table_mgt_packing_cost_kg'),'*',array('year0_id ='.$consignment['year0_id']));
            foreach($results as $result)
            {
                $packing_costs[$result['variety_id']][$result['packing_item_id']]=$result['cost'];
            }
            $total_weight=0;
            foreach($varieties as $result)
            {
                $total_weight+=$result['quantity']*$result['price'];
            }
            foreach($varieties as $result)
            {
                $total=0;
                $item=array();
                $item['crop_name']=$result['crop_name'];
                $item['crop_type_name']=$result['crop_type_name'];
                $item['variety_name']=$result['variety_name'];
                $item['quantity']=$result['quantity'];
                $item['price']=$result['price'];
                $total+=$result['price']*$result['quantity']*$consignment['rate'];
                foreach($direct_cost_items as $dc_items)
                {
                    $item['dc_'.$dc_items['value']]='';
                    if(($total_weight>0)&& isset($direct_costs[$dc_items['value']]))
                    {
                        $item['dc_'.$dc_items['value']]=number_format(($direct_costs[$dc_items['value']]['cost']*$result['quantity']*$result['price']/$total_weight),2);
                        $total+=($direct_costs[$dc_items['value']]['cost']*$result['quantity']*$result['price']/$total_weight);
                    }
                }
                foreach($packing_items as $pack_item)
                {
                    $item['pack_'.$pack_item['value']]='';
                    if((isset($packing_costs[$result['variety_id']][$pack_item['value']]))&&(($packing_costs[$result['variety_id']][$pack_item['value']])>0))
                    {
                        //$item['pack_'.$pack_item['value']]=number_format(($direct_costs[$dc_items['value']]['cost']*$result['quantity']*$result['price']/$total_weight),2);
                        $item['pack_'.$pack_item['value']]=number_format($packing_costs[$result['variety_id']][$pack_item['value']]*$result['quantity'],2);
                        $total+=$packing_costs[$result['variety_id']][$pack_item['value']]*$result['quantity'];
                    }
                }
                $item['total_cogs']=number_format($total,2);
                $item['cogs']=number_format($total/$result['quantity'],2);
                $items[]=$item;
            }
        }

        $this->jsonReturn($items);

    }


    private function system_save()
    {
        $user=User_helper::get_user();
        $time=time();
        $id = $this->input->post("id");

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
            $varieties=$this->input->post('varieties');

            $this->db->trans_start();  //DB Transaction Handle START

            $this->db->where('consignment_id',$id);
            $this->db->set('revision', 'revision+1', FALSE);
            $this->db->update($this->config->item('table_mgt_purchase_consignment_varieties'));

            foreach($varieties as $data)
            {
                $data['consignment_id']=$id;
                $data['revision']=1;
                $data['user_created'] = $user->user_id;
                $data['date_created'] = $time;
                Query_helper::add($this->config->item('table_mgt_purchase_consignment_varieties'),$data);
            }

            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {
                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                $this->system_list();
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->jsonReturn($ajax);
            }
        }
    }
    private function check_validation()
    {
        $varieties=$this->input->post('varieties');
        if(!(sizeof($varieties)>0))
        {
            $this->message='Minimum One Variety Required';
            return false;
        }
        else
        {
            foreach($varieties as $po)
            {
                if(!(($po['variety_id']>0)&& isset($po['price'])&& isset($po['quantity'])))
                {
                    $this->message='Unfinished Variety Entry';
                    return false;
                }
            }
        }
        return true;
    }


}
