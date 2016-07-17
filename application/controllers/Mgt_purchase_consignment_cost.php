<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mgt_purchase_consignment_cost extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Mgt_purchase_consignment_cost');
        $this->controller_url='mgt_purchase_consignment_cost';
        //$this->load->model("sys_user_role_model");
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
        else
        {
            $this->system_list($id);
        }
    }

    private function system_list()
    {

        if(isset($this->permissions['view'])&&($this->permissions['view']==1))
        {
            $data['title']="Consignment List(Cost Setup)";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("mgt_purchase_consignment_cost/list",$data,true));
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
        //$this->db->select('classifications.id id,classifications.name classification_name');
        $this->db->select('consignments.*');
        $this->db->select('fy.name fiscal_year_name');
        $this->db->select('principal.name principal_name');
        $this->db->join($this->config->item('ems_basic_setup_fiscal_year').' fy','fy.id = consignments.year0_id','INNER');
        $this->db->join($this->config->item('ems_basic_setup_principal').' principal','principal.id = consignments.principal_id','INNER');
        //$this->db->where('classifications.status !=',$this->config->item('system_status_delete'));
        $this->db->order_by('consignments.year0_id','DESC');
        $this->db->order_by('consignments.id','DESC');
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

            $data['title']="Edit Cost (".$data['consignment']['name'].')';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("mgt_purchase_consignment_cost/add_edit",$data,true));
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


            $this->db->trans_start();  //DB Transaction Handle START
            $this->db->where('consignment_id',$id);
            $this->db->set('revision', 'revision+1', FALSE);
            $this->db->update($this->config->item('table_mgt_purchase_consignment_costs'));
            $items=$this->input->post('items');
            foreach($items as $item_id=>$cost)
            {
                $data=array();
                $data['consignment_id']=$id;
                $data['item_id']=$item_id;
                $data['cost']=$cost;
                $data['revision']=1;
                $data['user_created'] = $user->user_id;
                $data['date_created'] = $time;
                Query_helper::add($this->config->item('table_mgt_purchase_consignment_costs'),$data);
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
        return true;
    }


}
