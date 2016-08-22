<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Reports_incentive extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Reports_incentive');
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
        $this->controller_url='reports_incentive';
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
            $data['title']="Incentive Report";
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


            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("reports_incentive/search",$data,true));
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
            $data['title']="Incentive Report";
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("reports_incentive/list",$data,true));

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

        $division_id=$this->input->post('division_id');
        $zone_id=$this->input->post('zone_id');
        $territory_id=$this->input->post('territory_id');




        //month total
        $month_total=array();
        $this->db->from($this->config->item('table_ti_bud_month_bt').' timbt');
        $this->db->select('timbt.variety_id');
        for($month=1;$month<=12;$month++)
        {
            $this->db->select('SUM(target_quantity_'.($month).') target_quantity_'.($month));
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

        $this->db->join($this->config->item('ems_sales_po').' po','po.id = pod.sales_po_id','INNER');
        $this->db->join($this->config->item('ems_csetup_customers').' cus','cus.id = po.customer_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_districts').' d','d.id = cus.district_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_territories').' t','t.id = d.territory_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_zones').' zone','zone.id = t.zone_id','INNER');

        $this->db->join($this->config->item('ems_setup_classification_varieties').' v','v.id =pod.variety_id','INNER');
        $this->db->join($this->config->item('ems_setup_classification_crop_types').' type','type.id =v.crop_type_id','INNER');

        $this->db->where('pod.revision',1);
        $this->db->where('po.status_approved',$this->config->item('system_status_po_approval_approved'));
        $this->db->where('po.date_approved >=',$year_info['date_start']);
        $this->db->where('po.date_approved <=',$year_info['date_end']);
        if($crop_id>0)
        {
            $this->db->where('type.crop_id',$crop_id);
            if($crop_type_id>0)
            {
                $this->db->where('type.id',$crop_type_id);
                if($variety_id>0)
                {
                    $this->db->where('pod.variety_id',$variety_id);
                }
            }
        }
        //$this->db->where('v.crop_type_id',$crop_type_id);
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
            if(isset($sales_total[$result['variety_id']]))
            {
                $sales_total[$result['variety_id']]['quantity']+=$result['pack_size']*$result['quantity'];//minus sales return,discard bonus
                $sales_total[$result['variety_id']]['net_sales']+=$result['variety_price_net']*$result['quantity'];//minus sales return,discard bonus
            }
            else
            {
                $sales_total[$result['variety_id']]['quantity']=$result['pack_size']*$result['quantity'];//minus sales return,discard bonus
                $sales_total[$result['variety_id']]['net_sales']=$result['variety_price_net']*$result['quantity'];//minus sales return,discard bonus

            }
        }
        //variety price
        $variety_prices=array();
        $results=Query_helper::get_info($this->config->item('ems_setup_classification_variety_price_kg'),'*',array('year0_id ='.$year0_id));
        foreach($results as $result)
        {
            $variety_prices[$result['variety_id']]=$result['price_net'];
        }

        //variety list
        $this->db->from($this->config->item('ems_setup_classification_varieties').' v');
        $this->db->select('v.id variety_id,v.name variety_name');
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
        $type_row['variety_name']='Total Type';
        $crop_row['type_name']='Total Crop';
        $grand_row['crop_name']='Grand Total';
        $grand_row['target_kg']=$crop_row['target_kg']=$type_row['target_kg']=0;
        $grand_row['sales_kg']=$crop_row['sales_kg']=$type_row['sales_kg']=0;

        $grand_row['target_net']=$crop_row['target_net']=$type_row['target_net']=0;
        $grand_row['sales_net']=$crop_row['sales_net']=$type_row['sales_net']=0;
        $grand_row['sales_percentage']=$crop_row['sales_percentage']=$type_row['sales_percentage']=0;
        $grand_row['incentive_achievable']=$crop_row['incentive_achievable']=$type_row['incentive_achievable']=0;
        $grand_row['payment']=$crop_row['payment']=$type_row['payment']=0;
        $grand_row['incentive_achieved']=$crop_row['incentive_achieved']=$type_row['incentive_achieved']=0;

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
                    $type_row['target_kg']=0;
                    $type_row['target_net']=0;
                    $type_row['sales_kg']=0;
                    $type_row['sales_net']=0;

                    $items[]=$this->get_report_row($crop_row);
                    $crop_row['target_kg']=0;
                    $crop_row['target_net']=0;
                    $crop_row['sales_kg']=0;
                    $crop_row['sales_net']=0;

                    $item['crop_name']=$result['crop_name'];
                    $prev_crop_name=$result['crop_name'];

                    $item['type_name']=$result['type_name'];
                    $prev_crop_type_name=$result['type_name'];
                }
                elseif($prev_crop_type_name!=$result['type_name'])
                {
                    $items[]=$this->get_report_row($type_row);
                    $type_row['target_kg']=0;
                    $type_row['target_net']=0;
                    $type_row['sales_kg']=0;
                    $type_row['sales_net']=0;

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

            $item['target_kg']=0;

            for($m=1;$m<=12;$m++)
            {
                if((isset($month_total[$result['variety_id']]['target_quantity_'.$m]))&&($month_total[$result['variety_id']]['target_quantity_'.$m]!=null))
                {
                    $item['target_kg']+=$month_total[$result['variety_id']]['target_quantity_'.$m];
                }
            }
            $type_row['target_kg']+=$item['target_kg'];
            $crop_row['target_kg']+=$item['target_kg'];
            $grand_row['target_kg']+=$item['target_kg'];

            $item['target_net']=0;//get net price for kg
            if((isset($variety_prices[$result['variety_id']]))&&($variety_prices[$result['variety_id']]>0))
            {
                $item['target_net']=$variety_prices[$result['variety_id']]*$item['target_kg'];
            }
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
            $grand_row['sales_net']+=$item['sales_net'];
            $item['sales_percentage']=0;
            $item['incentive_achievable']=0;
            $item['payment']=0;
            $item['incentive_achieved']=0;

            $items[]=$this->get_report_row($item);
        }
        $items[]=$this->get_report_row($type_row);
        $items[]=$this->get_report_row($crop_row);
        if($grand_row['target_net']!=0)
        {
            $grand_row['sales_percentage']=ceil($grand_row['sales_net']*100/$grand_row['target_net']);
            if($grand_row['sales_percentage']>100)
            {
                $grand_row['sales_percentage']=100;
            }
        }
        //$grand_row['sales_percentage']=95;
        $incentive_ratio=0;
        $result=Query_helper::get_info($this->config->item('table_mgt_indirect_cost_setup'),'*',array('status !="'.$this->config->item('system_status_delete').'"','year0_id ='.$year0_id),1);
        if($result)
        {
            $incentive_ratio=$result['incentive']/100;
        }

        $result=Query_helper::get_info($this->config->item('table_setup_incentive_ratio'),'*',array('status !="'.$this->config->item('system_status_delete').'"','year0_id ='.$year0_id),1);
        if($result)
        {
            if($territory_id>0)
            {
                $incentive_ratio*=$result['ti']/100;
            }
            elseif($zone_id>0)
            {
                $incentive_ratio*=$result['zi']/100;
            }
            elseif($division_id>0)
            {
                if($this->input->post('di_ict')=='ict')
                {
                    $incentive_ratio*=$result['ict']/100;
                }
                else
                {
                    $incentive_ratio*=$result['di']/100;
                }

            }
            else
            {
                $incentive_ratio*=$result['hom']/100;
            }
            $achieve_ratio=json_decode($result['achieve_ratio'],true);
            if(isset($achieve_ratio[$grand_row['sales_percentage']]))
            {
                $incentive_ratio*=$achieve_ratio[$grand_row['sales_percentage']]/100;
            }

        }
        $grand_row['incentive_achievable']=number_format($grand_row['sales_net']*$incentive_ratio,2);
        //payment
        $payment_amount=0;
        $this->db->from($this->config->item('ems_payment_payment').' p');
        $this->db->select('SUM(p.amount) total_payment');
        $this->db->where('p.status',$this->config->item('system_status_active'));
        $this->db->where('p.date_payment_receive >=',$year_info['date_start']);
        $this->db->where('p.date_payment_receive <=',$year_info['date_end']);
        $this->db->join($this->config->item('ems_csetup_customers').' cus','cus.id = p.customer_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_districts').' d','d.id = cus.district_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_territories').' t','t.id = d.territory_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_zones').' zone','zone.id = t.zone_id','INNER');
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
        $result=$this->db->get()->row_array();
        if($result)
        {
            $payment_amount=$result['total_payment'];
        }

        $grand_row['payment']=number_format($payment_amount,2);
        $grand_row['incentive_achieved']=number_format($payment_amount*$incentive_ratio,2);
        $items[]=$this->get_report_row($grand_row);
        $this->jsonReturn($items);
    }
    private function get_report_row($item)
    {
        $info=array();
        $info['crop_name']=$item['crop_name'];
        $info['type_name']=$item['type_name'];
        $info['variety_name']=$item['variety_name'];
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
        if($item['sales_percentage']!=0)
        {
            $info['sales_percentage']=$item['sales_percentage'];
        }
        else
        {
            $info['sales_percentage']='';
        }
        if($item['incentive_achievable']!=0)
        {
            $info['incentive_achievable']=$item['incentive_achievable'];
        }
        else
        {
            $info['incentive_achievable']='';
        }
        if($item['payment']!=0)
        {
            $info['payment']=$item['payment'];
        }
        else
        {
            $info['payment']='';
        }
        if($item['incentive_achieved']!=0)
        {
            $info['incentive_achieved']=$item['incentive_achieved'];
        }
        else
        {
            $info['incentive_achieved']='';
        }
        return $info;
    }

}
