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
        $year0_id=$this->input->post('year0_id');
        $year_info=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text','date_start','date_end'),array('id ='.$year0_id),1);


        $crop_id=$this->input->post('crop_id');
        $crop_type_id=$this->input->post('crop_type_id');
        $variety_id=$this->input->post('variety_id');

        $division_id=$this->input->post('division_id');
        $zone_id=$this->input->post('zone_id');
        $territory_id=$this->input->post('territory_id');

        $month_start=$this->input->post('month_start');
        if($month_start<6)
        {
            $date_start=mktime(0,0,0,$month_start,1,date('Y',$year_info['date_end']));
        }
        else
        {
            $date_start=mktime(0,0,0,$month_start,1,date('Y',$year_info['date_start']));
        }

        $month_end=$this->input->post('month_end');
        if($month_end==12)
        {
            $date_end=mktime(0,0,0,1,1,date('Y',$year_info['date_end']))-1;
        }
        else if($month_end<6)
        {
            $date_end=mktime(0,0,0,$month_end+1,1,date('Y',$year_info['date_end']))-1;
        }
        else
        {
            $date_end=mktime(0,0,0,$month_end+1,1,date('Y',$year_info['date_start']))-1;
        }
        if($month_end<$month_start)
        {
            $month_end+=12;
        }


        //month total
        $month_total=array();
        $this->db->from($this->config->item('table_ti_bud_month_bt').' timbt');
        $this->db->select('timbt.variety_id');
        for($month=$month_start;$month<=$month_end;$month++)
        {
            if($month%12)
            {
                $this->db->select('SUM(target_quantity_'.($month%12).') target_quantity_'.($month%12));
            }
            else
            {
                $this->db->select('SUM(target_quantity_12) target_quantity_12');
            }
        }
        $this->db->join($this->config->item('ems_setup_location_territories').' t','t.id = timbt.territory_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_zones').' zone','zone.id = t.zone_id','INNER');

        $this->db->join($this->config->item('ems_setup_classification_varieties').' v','v.id = timbt.variety_id','INNER');
        $this->db->join($this->config->item('table_forward_ti_month_target').' ftimt','ftimt.territory_id = timbt.territory_id and ftimt.year0_id=timbt.year0_id and ftimt.type_id=v.crop_type_id','INNER');

        $this->db->where('timbt.year0_id',$year0_id);
        $this->db->where('ftimt.status_assign',$this->config->item('system_status_yes'));
        if($division_id>0)
        {
            $this->db->where('zone.division_id',$division_id);
            if($zone_id>0)
            {
                $this->db->where('zone.id',$zone_id);
                if($territory_id>0)
                {
                    $this->db->where('t.id',$territory_id);
                }
            }
        }
        $this->db->group_by('timbt.variety_id');
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            $month_total[$result['variety_id']]=$result;
        }
        //total sales
        $sales_total=array();
        $this->db->from($this->config->item('ems_sales_po_details').' pod');

        $this->db->select('pod.*');
        $this->db->select('po.date_approved');

        //$this->db->select('(pod.pack_size * pod.quantity) sales_quantity');
        //$this->db->select('(pod.bonus_pack_size * pod.quantity_bonus) bonus_quantity');
        //$this->db->select('(pod.variety_price_net * pod.quantity) net_sales');

        $this->db->join($this->config->item('ems_sales_po').' po','po.id = pod.sales_po_id','INNER');
        $this->db->join($this->config->item('ems_csetup_customers').' cus','cus.id = po.customer_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_districts').' d','d.id = cus.district_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_territories').' t','t.id = d.territory_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_zones').' zone','zone.id = t.zone_id','INNER');

        $this->db->join($this->config->item('ems_setup_classification_varieties').' v','v.id =pod.variety_id','INNER');

        $this->db->where('pod.revision',1);
        $this->db->where('po.status_approved',$this->config->item('system_status_po_approval_approved'));
        $this->db->where('po.date_approved >=',$date_start);
        $this->db->where('po.date_approved <=',$date_end);
        $this->db->where('v.crop_type_id',$crop_type_id);
        if($variety_id>0)
        {
            $this->db->where('pod.variety_id',$variety_id);
        }
        if($division_id>0)
        {
            $this->db->where('zone.division_id',$division_id);
            if($zone_id>0)
            {
                $this->db->where('zone.id',$zone_id);
                if($territory_id>0)
                {
                    $this->db->where('t.id',$territory_id);
                }
            }
        }
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            $m=date('n',$result['date_approved']);
            if(isset($sales_total[$result['variety_id']][$m]))
            {
                $sales_total[$result['variety_id']][$m]['quantity']+=$result['pack_size']*$result['quantity'];//minus sales return,discard bonus
                $sales_total[$result['variety_id']][$m]['net_sales']+=$result['variety_price_net']*$result['quantity'];//minus sales return,discard bonus
            }
            else
            {
                $sales_total[$result['variety_id']][$m]['quantity']=$result['pack_size']*$result['quantity'];//minus sales return,discard bonus
                $sales_total[$result['variety_id']][$m]['net_sales']=$result['variety_price_net']*$result['quantity'];//minus sales return,discard bonus

            }
        }
        //variety list
        $this->db->from($this->config->item('ems_setup_classification_varieties').' v');
        $this->db->select('v.id variety_id,v.name variety_name');
        $this->db->select('type.name crop_type_name');
        $this->db->select('crop.name crop_name');
        $this->db->join($this->config->item('ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->join($this->config->item('ems_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');
        $this->db->where('v.whose','ARM');
        $this->db->where('v.status =',$this->config->item('system_status_active'));
        $this->db->where('type.id',$crop_type_id);
        if($variety_id>0)
        {
            $this->db->where('v.id',$variety_id);
        }
        $this->db->order_by('type.ordering','ASC');
        $this->db->order_by('v.ordering','ASC');
        $results=$this->db->get()->result_array();
        $count=0;
        $crop_total=array();
        $crop_total['crop_name']='';
        $crop_total['crop_type_name']='';
        $crop_total['variety_name']='Total';
        for($month=$month_start;$month<=$month_end;$month++)
        {
            if($month%12)
            {
                $m=$month%12;
            }
            else
            {
                $m=12;
            }
            $crop_total['target_'.$m]=0;
            $crop_total['achieve_'.$m]=0;
            $crop_total['net_'.$m]=0;
        }
        $crop_total['target_total']=0;
        $crop_total['achieve_total']=0;
        $crop_total['net_total']=0;
        foreach($results as $index=>$result)
        {
            $item=array();
            if($count==0)
            {
                $item['crop_name']=$result['crop_name'];
                $item['crop_type_name']=$result['crop_type_name'];
            }
            else
            {
                $item['crop_name']='';
                $item['crop_type_name']='';
            }
            $count++;
            $item['variety_name']=$result['variety_name'];
            $item['target_total']=0;//initialization
            $item['achieve_total']=0;//initialization
            $item['variance_total']=0;//initialization
            $item['net_total']=0;//initialization
            for($month=$month_start;$month<=$month_end;$month++)
            {
                if($month%12)
                {
                    $m=$month%12;
                }
                else
                {
                    $m=12;
                }
                if((isset($month_total[$result['variety_id']]['target_quantity_'.$m]))&&($month_total[$result['variety_id']]['target_quantity_'.$m]!=null))
                {
                    $item['target_'.$m]=$month_total[$result['variety_id']]['target_quantity_'.$m];
                    $item['target_total']+=$item['target_'.$m];
                    $crop_total['target_'.$m]+=$item['target_'.$m];
                    $crop_total['target_total']+=$item['target_'.$m];
                }
                else
                {
                    $item['target_'.$m]=0;
                }
                if((isset($sales_total[$result['variety_id']][$m]))&&($sales_total[$result['variety_id']][$m]['quantity']!=null))
                {
                    $item['achieve_'.$m]=$sales_total[$result['variety_id']][$m]['quantity']/1000;
                    $item['achieve_total']+=$item['achieve_'.$m];
                    $crop_total['achieve_'.$m]+=$item['achieve_'.$m];
                    $crop_total['achieve_total']+=$item['achieve_'.$m];

                    $item['net_'.$m]=$sales_total[$result['variety_id']][$m]['net_sales'];
                    $item['net_total']+=$item['net_'.$m];
                    $crop_total['net_'.$m]+=$item['net_'.$m];
                    $crop_total['net_total']+=$item['net_'.$m];

                }
                else
                {
                    $item['achieve_'.$m]=0;
                    $item['net_'.$m]=0;

                }
            }
            $items[]=$this->get_report_row($item,$month_start,$month_end);

            //$item['sl_no']=$count;
        }
        $items[]=$this->get_report_row($crop_total,$month_start,$month_end);
        $this->jsonReturn($items);
    }
    private function get_report_row($item,$month_start,$month_end)
    {
        $info=array();
        $info['crop_name']=$item['crop_name'];
        $info['crop_type_name']=$item['crop_type_name'];
        $info['variety_name']=$item['variety_name'];
        for($month=$month_start;$month<=$month_end;$month++)
        {

            if($month%12)
            {
                $m=$month%12;
            }
            else
            {
                $m=12;
            }
            if(isset($item['target_'.$m]) && $item['target_'.$m]!=0)
            {
                $info['target_'.$m]=number_format($item['target_'.$m],3,'.','');
                $info['variance_'.$m]=$item['target_'.$m];
            }
            else
            {
                $info['target_'.$m]='';
                $info['variance_'.$m]=0;
            }
            if(isset($item['achieve_'.$m]) && $item['achieve_'.$m]!=0)
            {
                $info['achieve_'.$m]=number_format($item['achieve_'.$m],3,'.','');
                $info['variance_'.$m]-=$item['achieve_'.$m];
            }
            else
            {
                $info['achieve_'.$m]='';
            }
            if($info['variance_'.$m]==0)
            {
                $info['variance_'.$m]='';
            }
            else
            {
                $info['variance_'.$m]=number_format($info['variance_'.$m],3,'.','');
            }
            if(isset($item['net_'.$m]) && $item['net_'.$m]!=0)
            {
                $info['net_'.$m]=number_format($item['net_'.$m],2);
            }
            else
            {
                $info['net_'.$m]='';
            }
        }
        if(isset($item['target_total']) && $item['target_total']!=0)
        {
            $info['target_total']=number_format($item['target_total'],3,'.','');
            $info['variance_total']=$item['target_total'];
        }
        else
        {
            $info['target_total']='';
            $info['variance_total']=0;
        }
        if(isset($item['achieve_total']) && $item['achieve_total']!=0)
        {
            $info['achieve_total']=number_format($item['achieve_total'],3,'.','');
            $info['variance_total']-=$item['achieve_total'];
        }
        else
        {
            $info['achieve_total']='';
        }
        if($info['variance_total']==0)
        {
            $info['variance_total']='';
        }
        else
        {
            $info['variance_total']=number_format($info['variance_total'],3,'.','');
        }
        if(isset($item['net_total']) && $item['net_total']!=0)
        {
            $info['net_total']=number_format($item['net_total'],2);
        }
        else
        {
            $info['net_total']='';
        }
        return $info;
    }

}
