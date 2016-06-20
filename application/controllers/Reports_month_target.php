<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reports_month_target extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Reports_month_target');
        $this->locations=User_helper::get_locations();
        if(!is_array($this->locations))
        {
            if($this->locations=='wrong')
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line('MSG_LOCATION_INVALID');
                $this->jsonReturn($ajax);
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line('MSG_LOCATION_NOT_ASSIGNED');
                $this->jsonReturn($ajax);
            }

        }
        $this->controller_url='reports_month_target';
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
        else
        {
            $this->system_search();
        }
    }
    private function system_search()
    {
        if(isset($this->permissions['view'])&&($this->permissions['view']==1))
        {
            $data['title']="Month wise Target Report";
            $ajax['status']=true;
            $data['divisions']=Query_helper::get_info($this->config->item('ems_setup_location_divisions'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));
            $data['zones']=array();
            $data['territories']=array();
            if($this->locations['division_id']>0)
            {
                $data['zones']=Query_helper::get_info($this->config->item('ems_setup_location_zones'),array('id value','name text'),array('division_id ='.$this->locations['division_id']));
                if($this->locations['zone_id']>0)
                {
                    $data['territories']=Query_helper::get_info($this->config->item('ems_setup_location_territories'),array('id value','name text'),array('zone_id ='.$this->locations['zone_id']));
                }
            }
            $fy_info=System_helper::get_fiscal_years();
            $data['fiscal_years']=$fy_info['years'];
            $data['year0_id']=$fy_info['budget_year']['value']-1;//current fiscal year

            $data['crops']=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array(),0,0,array('ordering ASC'));


            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("reports_month_target/search",$data,true));
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
            if(!($reports['month_start']>0))
            {
                $ajax['status']=false;
                $ajax['system_message']='Please Select Starting Month';
                $this->jsonReturn($ajax);
            }
            if(!($reports['month_end']>0))
            {
                $ajax['status']=false;
                $ajax['system_message']='Please Select End month';
                $this->jsonReturn($ajax);
            }
            if(!($reports['crop_id']>0))
            {
                $ajax['status']=false;
                $ajax['system_message']='Please Select a Crop';
                $this->jsonReturn($ajax);
            }
            if(!($reports['crop_type_id']>0))
            {
                $ajax['status']=false;
                $ajax['system_message']='Please Select a Crop Type';
                $this->jsonReturn($ajax);
            }
            /* month validation check spliting month may-june*/
            if($reports['month_start']<6 && $reports['month_end']>5)
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid Starting and End month';
                $this->jsonReturn($ajax);
            }
            if($reports['month_start']>5 && $reports['month_end']>5 && $reports['month_end']<$reports['month_start'])
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid Starting and End month';
                $this->jsonReturn($ajax);
            }
            if($reports['month_start']<6 && $reports['month_end']<6 && $reports['month_end']<$reports['month_start'])
            {
                $ajax['status']=false;
                $ajax['system_message']='Invalid Starting and End month';
                $this->jsonReturn($ajax);
            }
            $month_end=$reports['month_end']<$reports['month_start']?$reports['month_end']+12:$reports['month_end'];

            $data['months']=array();
            for($i=$reports['month_start'];$i<=$month_end;$i++)
            {
                if($i%12)
                {
                    $data['months'][]=$i%12;
                }
                else
                {
                    $data['months'][]=12;
                }

            }

            /* month validation check spliting month may-june*/

            $keys=',';

            foreach($reports as $elem=>$value)
            {
                $keys.=$elem.":'".$value."',";
            }

            $data['keys']=trim($keys,',');


            $ajax['status']=true;
            $data['title']="Month wise Target Report";
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("reports_month_target/list",$data,true));

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
    public function get_items()
    {
        $items=array();
        $this->jsonReturn($items);
    }
}
