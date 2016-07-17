<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mgt_purchase_consignment extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Mgt_purchase_consignment');
        $this->controller_url='mgt_purchase_consignment';
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
        elseif($action=="add")
        {
            $this->system_add();
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
            $data['title']="Consignment List";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("mgt_purchase_consignment/list",$data,true));
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

    private function system_add()
    {
        if(isset($this->permissions['add'])&&($this->permissions['add']==1))
        {
            $fy_info=System_helper::get_fiscal_years();
            $data['fiscal_years']=$fy_info['years'];


            $data['title']="Create New Consignment";
            $data['consignment']['id']=0;
            $data['consignment']['year0_id']=$fy_info['budget_year']['value']-1;//current fiscal year
            $data['consignment']['name']='';
            $data['consignment']['date_purchase']=time();
            $data['consignment']['currency_id']=0;
            $data['consignment']['principal_id']=0;
            $data['consignment']['rate']='';
            $data['consignment']['lc_number']='';

            $data['currencies']=Query_helper::get_info($this->config->item('table_setup_currency'),array('id value','name text'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));
            $data['principals']=Query_helper::get_info($this->config->item('ems_basic_setup_principal'),array('id value','name text'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("mgt_purchase_consignment/add_edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/add');
            $this->jsonReturn($ajax);
        }
        else
        {
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
        }
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

            $data['consignment']=Query_helper::get_info($this->config->item('table_mgt_purchase_consignments'),'*',array('id ='.$consignment_id),1);
            $fy_info=System_helper::get_fiscal_years();
            $data['fiscal_years']=$fy_info['years'];
            $data['currencies']=Query_helper::get_info($this->config->item('table_setup_currency'),array('id value','name text'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));
            $data['principals']=Query_helper::get_info($this->config->item('ems_basic_setup_principal'),array('id value','name text'),array('status !="'.$this->config->item('system_status_delete').'"'),0,0,array('ordering'));

            $data['title']="Edit consignment (".$data['consignment']['name'].')';
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("mgt_purchase_consignment/add_edit",$data,true));
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
        if($id>0)
        {
            if(!(isset($this->permissions['edit'])&&($this->permissions['edit']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->jsonReturn($ajax);
                die();
            }
        }
        else
        {
            if(!(isset($this->permissions['add'])&&($this->permissions['add']==1)))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->jsonReturn($ajax);
                die();

            }
        }
        if(!$this->check_validation())
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->message;
            $this->jsonReturn($ajax);
        }
        else
        {
            $consignment = $this->input->post('consignment');
            $consignment['date_purchase']=System_helper::get_time($consignment['date_purchase']);

            $this->db->trans_start();  //DB Transaction Handle START
            if($id>0)
            {
                $consignment['user_updated'] = $user->user_id;
                $consignment['date_updated'] = $time;
                Query_helper::update($this->config->item('table_mgt_purchase_consignments'),$consignment,array("id = ".$id));
            }
            else
            {
                $consignment['user_created'] = $user->user_id;
                $consignment['date_created'] = $time;
                Query_helper::add($this->config->item('table_mgt_purchase_consignments'),$consignment);
            }
            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {
                $save_and_new=$this->input->post('system_save_new_status');
                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                if($save_and_new==1)
                {
                    $this->system_add();
                }
                else
                {
                    $this->system_list();
                }
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
        $this->load->library('form_validation');
        $this->form_validation->set_rules('consignment[year0_id]',$this->lang->line('LABEL_FISCAL_YEAR'),'required');
        $this->form_validation->set_rules('consignment[month]',$this->lang->line('LABEL_MONTH_PURCHASE'),'required');
        $this->form_validation->set_rules('consignment[date_purchase]',$this->lang->line('LABEL_DATE_PURCHASE'),'required');
        $this->form_validation->set_rules('consignment[currency_id]',$this->lang->line('LABEL_CURRENCY_NAME'),'required');
        $this->form_validation->set_rules('consignment[principal_id]',$this->lang->line('LABEL_PRINCIPAL_NAME'),'required');
        $this->form_validation->set_rules('consignment[name]',$this->lang->line('LABEL_CONSIGNMENT_NAME'),'required');
        $this->form_validation->set_rules('consignment[lc_number]',$this->lang->line('LABEL_LC_NUMBER'),'required');

        if($this->form_validation->run() == FALSE)
        {
            $this->message=validation_errors();
            return false;
        }
        return true;
    }


}
