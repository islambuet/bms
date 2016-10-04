<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Analysis_sales_order extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Analysis_sales_order');
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
        $this->controller_url='analysis_sales_order';
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
        elseif($action=="get_items_product")
        {
            $this->get_items_product();
        }
        elseif($action=="get_items_location")
        {
            $this->get_items_location();
        }
        elseif($action=="get_items_customer")
        {
            $this->get_items_customer();
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
            $data['title']="Sales Ordering Analysis";
            $ajax['status']=true;

            $fy_info=System_helper::get_fiscal_years();
            $data['fiscal_years']=$fy_info['years'];
            $data['year0_id']=$fy_info['budget_year']['value']-1;//current fiscal year

            $data['crops']=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array(),0,0,array('ordering ASC'));
            $data['divisions']=Query_helper::get_info($this->config->item('ems_setup_location_divisions'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));
            $data['zones']=array();
            $data['territories']=array();
            $data['districts']=array();
            $data['customers']=array();
            if($this->locations['division_id']>0)
            {
                $data['zones']=Query_helper::get_info($this->config->item('ems_setup_location_zones'),array('id value','name text'),array('division_id ='.$this->locations['division_id']));
                if($this->locations['zone_id']>0)
                {
                    $data['territories']=Query_helper::get_info($this->config->item('ems_setup_location_territories'),array('id value','name text'),array('zone_id ='.$this->locations['zone_id']));
                    if($this->locations['territory_id']>0)
                    {
                        $data['districts']=Query_helper::get_info($this->config->item('ems_setup_location_districts'),array('id value','name text'),array('territory_id ='.$this->locations['territory_id']));
                        if($this->locations['district_id']>0)
                        {
                            $data['customers']=Query_helper::get_info($this->config->item('ems_csetup_customers'),array('id value','name text'),array('district_id ='.$this->locations['district_id'],'status ="'.$this->config->item('system_status_active').'"'));
                        }
                    }
                }
            }

            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("analysis_sales_order/search",$data,true));
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
            $data['months']=array();
            for($i=1;$i<13;$i++)
            {
                if((isset($months[$i]))&&$months[$i]>0)
                {
                    $data['months'][]=$i;
                    $keys.="month_".$i.":'1',";
                }
                else
                {
                    $keys.="month_".$i.":'0',";
                }
            }

            $data['keys']=trim($keys,',');


            $ajax['status']=true;
            $analysis_type=$this->input->post('analysis_type');
            if($analysis_type=='product')
            {
                $data['title']="Product-wise Sales Ordering";
                $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("analysis_sales_order/list_product",$data,true));
            }
            elseif($analysis_type=='location')
            {
                $data['title']="Location-wise Sales Ordering";
                $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("analysis_sales_order/list_location",$data,true));
            }
            elseif($analysis_type=='customer')
            {
                $data['title']="Customer-wise Sales Ordering";
                $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("analysis_sales_order/list_customer",$data,true));
            }
            else
            {
                $data['title']="Product-wise Sales Ordering";
                $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("analysis_sales_order/list_product",$data,true));
            }


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
    private function sorting_compare($a, $b)
    {
        if ($a['sales'] == $b['sales'])
        {
            return 0;
        }
        return ($a['sales'] > $b['sales']) ? -1 : 1;
    }
    private function get_items_product()
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
        $district_id=$this->input->post('district_id');
        $customer_id=$this->input->post('customer_id');

        $months=array();

        for($i=1;$i<13;$i++)
        {
            if($this->input->post('month_'.$i)==1)
            {
                $months[]=$i;
            }
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
        if($division_id>0)
        {
            $this->db->where('zone.division_id',$division_id);
            if($zone_id>0)
            {
                $this->db->where('zone.id',$zone_id);
                if($territory_id>0)
                {
                    $this->db->where('t.id',$territory_id);
                    if($district_id>0)
                    {
                        $this->db->where('d.id',$district_id);
                        if($customer_id>0)
                        {
                            $this->db->where('cus.id',$customer_id);
                        }
                    }
                }
            }
        }
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            $m=date('n',$result['date_approved']);
            if(in_array($m,$months))
            {
                if(isset($sales_total[$result['variety_id']][$m]))
                {
                    //$sales_total[$result['variety_id']][$m]['quantity']+=$result['pack_size']*$result['quantity'];//minus sales return,discard bonus
                    $sales_total[$result['variety_id']][$m]['net_sales']+=$result['variety_price_net']*$result['quantity'];//minus sales return,discard bonus
                }
                else
                {
                    //$sales_total[$result['variety_id']][$m]['quantity']=$result['pack_size']*$result['quantity'];//minus sales return,discard bonus
                    $sales_total[$result['variety_id']][$m]['net_sales']=$result['variety_price_net']*$result['quantity'];//minus sales return,discard bonus

                }
            }

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
        foreach($results as $result)
        {
            $item=array();
            $item['crop_name']=$result['crop_name'];
            $item['type_name']=$result['type_name'];
            $item['variety_name']=$result['variety_name'];
            $item['total']=0;
            foreach($months as $month)
            {
                if(isset($sales_total[$result['variety_id']][$month]['net_sales']))
                {
                    $item['total']+=$sales_total[$result['variety_id']][$month]['net_sales'];
                }
            }
            $items[]=$this->get_report_row_product($item);
        }
        usort($items, array( $this, 'sorting_compare' ));
        $this->jsonReturn($items);

    }
    private function get_report_row_product($item)
    {
        $info=array();
        $info['crop_name']=$item['crop_name'];
        $info['type_name']=$item['type_name'];
        $info['variety_name']=$item['variety_name'];
        if($item['total']!=0)
        {
            $info['total']=number_format($item['total'],2);
            $info['sales']=$item['total'];
        }
        else
        {
            $info['total']='';
            $info['sales']=0;
        }
        return $info;
    }
    private function get_items_location()
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
        $district_id=$this->input->post('district_id');
        $customer_id=$this->input->post('customer_id');

        $type='division_id';
        $locations=array();
        if($customer_id>0)
        {
            $type='customer_id';
            $locations=Query_helper::get_info($this->config->item('ems_csetup_customers'),array('id value','name text'),array('id ='.$customer_id,'status ="'.$this->config->item('system_status_active').'"'));
        }
        else if($district_id>0)
        {
            $type='customer_id';
            $locations=Query_helper::get_info($this->config->item('ems_csetup_customers'),array('id value','name text'),array('district_id ='.$district_id,'status ="'.$this->config->item('system_status_active').'"'));
        }
        else if($territory_id>0)
        {
            $type='district_id';
            $locations=Query_helper::get_info($this->config->item('ems_setup_location_districts'),array('id value','name text'),array('territory_id ='.$territory_id,'status ="'.$this->config->item('system_status_active').'"'));
        }
        else if($zone_id>0)
        {
            $type='territory_id';
            $locations=Query_helper::get_info($this->config->item('ems_setup_location_territories'),array('id value','name text'),array('zone_id ='.$zone_id,'status ="'.$this->config->item('system_status_active').'"'));
        }
        else if($division_id>0)
        {
            $type='zone_id';
            $locations=Query_helper::get_info($this->config->item('ems_setup_location_zones'),array('id value','name text'),array('division_id ='.$division_id,'status ="'.$this->config->item('system_status_active').'"'));
        }
        else
        {
            $type='division_id';
            $locations=Query_helper::get_info($this->config->item('ems_setup_location_divisions'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));
        }

        $months=array();

        for($i=1;$i<13;$i++)
        {
            if($this->input->post('month_'.$i)==1)
            {
                $months[]=$i;
            }
        }

        //total sales
        $sales_total=array();
        $this->db->from($this->config->item('ems_sales_po_details').' pod');

        $this->db->select('pod.*');
        $this->db->select('po.date_approved');
        $this->db->select('zone.division_id division_id');
        $this->db->select('zone.id zone_id');
        $this->db->select('t.id territory_id');
        $this->db->select('d.id district_id');
        $this->db->select('cus.id customer_id');

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
        if($division_id>0)
        {
            $this->db->where('zone.division_id',$division_id);
            if($zone_id>0)
            {
                $this->db->where('zone.id',$zone_id);
                if($territory_id>0)
                {
                    $this->db->where('t.id',$territory_id);
                    if($district_id>0)
                    {
                        $this->db->where('d.id',$district_id);
                        if($customer_id>0)
                        {
                            $this->db->where('cus.id',$customer_id);
                        }
                    }
                }
            }
        }
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            $m=date('n',$result['date_approved']);
            if(in_array($m,$months))
            {
                if(isset($sales_total[$result[$type]][$m]))
                {
                    //$sales_total[$result[$type]][$m]['quantity']+=$result['pack_size']*$result['quantity'];//minus sales return,discard bonus
                    $sales_total[$result[$type]][$m]['net_sales']+=$result['variety_price_net']*$result['quantity'];//minus sales return,discard bonus
                }
                else
                {
                    //$sales_total[$result[$type]][$m]['quantity']=$result['pack_size']*$result['quantity'];//minus sales return,discard bonus
                    $sales_total[$result[$type]][$m]['net_sales']=$result['variety_price_net']*$result['quantity'];//minus sales return,discard bonus

                }
            }

        }
        foreach($locations as $location)
        {
            $item=array();
            $item['name']=$location['text'];
            $item['total']=0;
            foreach($months as $month)
            {
                if(isset($sales_total[$location['value']][$month]['net_sales']))
                {
                    $item['total']+=$sales_total[$location['value']][$month]['net_sales'];
                }
            }
            $items[]=$this->get_report_row_location($item);
        }

        usort($items, array( $this, 'sorting_compare' ));
        $this->jsonReturn($items);
    }
    private function get_report_row_location($item)
    {
        $info=array();
        $info['name']=$item['name'];
        if($item['total']!=0)
        {
            $info['total']=number_format($item['total'],2);
            $info['sales']=$item['total'];
        }
        else
        {
            $info['total']='';
            $info['sales']=0;
        }
        return $info;
    }
    private function get_items_customer()
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
        $district_id=$this->input->post('district_id');
        $customer_id=$this->input->post('customer_id');

        $months=array();

        for($i=1;$i<13;$i++)
        {
            if($this->input->post('month_'.$i)==1)
            {
                $months[]=$i;
            }
        }

        //total sales

        $this->db->from($this->config->item('ems_sales_po_details').' pod');

        $this->db->select('pod.*');
        $this->db->select('po.date_approved');
        $this->db->select('zone.division_id division_id');
        $this->db->select('zone.id zone_id');
        $this->db->select('t.id territory_id');
        $this->db->select('d.id district_id');
        $this->db->select('cus.id customer_id');

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
        if($division_id>0)
        {
            $this->db->where('zone.division_id',$division_id);
            if($zone_id>0)
            {
                $this->db->where('zone.id',$zone_id);
                if($territory_id>0)
                {
                    $this->db->where('t.id',$territory_id);
                    if($district_id>0)
                    {
                        $this->db->where('d.id',$district_id);
                        if($customer_id>0)
                        {
                            $this->db->where('cus.id',$customer_id);
                        }
                    }
                }
            }
        }
        $results=$this->db->get()->result_array();
        $sales_total=array();
        $customer_po_ids=array();
        foreach($results as $result)
        {
            $m=date('n',$result['date_approved']);
            if(in_array($m,$months))
            {
                if(isset($sales_total[$result['customer_id']][$m]))
                {
                    //$sales_total[$result[$type]][$m]['quantity']+=$result['pack_size']*$result['quantity'];//minus sales return,discard bonus
                    $sales_total[$result['customer_id']][$m]['net_sales']+=$result['variety_price_net']*$result['quantity'];//minus sales return,discard bonus
                }
                else
                {
                    //$sales_total[$result[$type]][$m]['quantity']=$result['pack_size']*$result['quantity'];//minus sales return,discard bonus
                    $sales_total[$result['customer_id']][$m]['net_sales']=$result['variety_price_net']*$result['quantity'];//minus sales return,discard bonus
                }
                $customer_po_ids[$result['customer_id']][$result['sales_po_id']]=$result['sales_po_id'];
            }

        }
        $arm_banks=Query_helper::get_info($this->config->item('ems_basic_setup_arm_bank'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));

        $this->db->from($this->config->item('ems_csetup_customers').' cus');
        $this->db->select('cus.id customer_id,cus.name customer_name,cus.customer_code customer_code');
        $this->db->select('division.name division_name');
        $this->db->select('zone.name zone_name');
        $this->db->select('t.name territory_name');
        $this->db->select('d.name district_name');

        $this->db->join($this->config->item('ems_setup_location_districts').' d','d.id = cus.district_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_territories').' t','t.id = d.territory_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_zones').' zone','zone.id = t.zone_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_divisions').' division','division.id = zone.division_id','INNER');
        $this->db->where('cus.status !=',$this->config->item('system_status_delete'));
        if($division_id>0)
        {
            $this->db->where('division.id',$division_id);
            if($zone_id>0)
            {
                $this->db->where('zone.id',$zone_id);
                if($territory_id>0)
                {
                    $this->db->where('t.id',$territory_id);
                    if($district_id>0)
                    {
                        $this->db->where('d.id',$district_id);
                        if($customer_id>0)
                        {
                            $this->db->where('cus.id',$customer_id);
                        }
                    }
                }
            }
        }
        $results=$this->db->get()->result_array();
        $area_initial=array();
        //setting 0
        foreach($results as $area)
        {
            $area_initial[$area['customer_id']]['name']=$area['customer_name'];
            $area_initial[$area['customer_id']]['customer_code']=$area['customer_code'];
            $area_initial[$area['customer_id']]['division_name']=$area['division_name'];
            $area_initial[$area['customer_id']]['zone_name']=$area['zone_name'];
            $area_initial[$area['customer_id']]['territory_name']=$area['territory_name'];
            $area_initial[$area['customer_id']]['district_name']=$area['district_name'];

            $area_initial[$area['customer_id']]['opening_balance_tp']=0;
            $area_initial[$area['customer_id']]['opening_balance_net']=0;
            $area_initial[$area['customer_id']]['sales_tp']=0;
            $area_initial[$area['customer_id']]['sales_net']=0;
            foreach($arm_banks as $arm_bank)
            {
                $area_initial[$area['customer_id']]['payment_'.$arm_bank['value']]=0;
            }

            $area_initial[$area['customer_id']]['total_payment']=0;
            $area_initial[$area['customer_id']]['adjust_tp']=0;
            $area_initial[$area['customer_id']]['adjust_net']=0;

            //from previous calculation
            $area_initial[$area['customer_id']]['total']=0;
            foreach($months as $month)
            {
                if(isset($sales_total[$area['customer_id']][$month]['net_sales']))
                {
                    $area_initial[$area['customer_id']]['total']+=$sales_total[$area['customer_id']][$month]['net_sales'];
                }
            }
            $area_initial[$area['customer_id']]['total_po']=0;
            if(isset($customer_po_ids[$area['customer_id']]))
            {
                $area_initial[$area['customer_id']]['total_po']=sizeof($customer_po_ids[$area['customer_id']]);
            }
        }
        //party balance report copy from ems
        $location_type='customer_id';
        if($year_info['date_start']>0)
        {
            $this->db->from($this->config->item('ems_csetup_balance_adjust').' ba');
            $this->db->select('SUM(ba.amount_tp) amount_tp');
            $this->db->select('SUM(ba.amount_net) amount_net');
            $this->db->select('ba.customer_id customer_id');
            $this->db->select('ba.date_adjust date_adjust');
            $this->db->select('d.id district_id');
            $this->db->select('t.id territory_id');
            $this->db->select('zone.id zone_id');
            $this->db->select('zone.division_id division_id');
            $this->db->where('ba.status',$this->config->item('system_status_active'));
            $this->db->join($this->config->item('ems_csetup_customers').' cus','cus.id = ba.customer_id','INNER');
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
                        if($district_id>0)
                        {
                            $this->db->where('d.id',$district_id);
                        }
                    }
                }
            }
            $this->db->where('ba.date_adjust <',$year_info['date_start']);
            $group_array[]=$location_type;
            $this->db->group_by($group_array);
            $results=$this->db->get()->result_array();
            if($results)
            {
                foreach($results as $result)
                {

                    $area_initial[$result[$location_type]]['opening_balance_tp']-=$result['amount_tp'];
                    $area_initial[$result[$location_type]]['opening_balance_net']-=$result['amount_net'];
                }
            }
        }
        //other adjustment
        $this->db->from($this->config->item('ems_csetup_balance_adjust').' ba');
        $this->db->select('SUM(ba.amount_tp) amount_tp');
        $this->db->select('SUM(ba.amount_net) amount_net');
        $this->db->select('ba.customer_id customer_id');
        $this->db->select('ba.date_adjust date_adjust');
        $this->db->select('d.id district_id');
        $this->db->select('t.id territory_id');
        $this->db->select('zone.id zone_id');
        $this->db->select('zone.division_id division_id');
        $this->db->where('ba.status',$this->config->item('system_status_active'));
        $this->db->join($this->config->item('ems_csetup_customers').' cus','cus.id = ba.customer_id','INNER');
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
                    if($district_id>0)
                    {
                        $this->db->where('d.id',$district_id);
                    }
                }
            }
        }
        $this->db->where('ba.date_adjust >=',$year_info['date_start']);
        $this->db->where('ba.date_adjust <=',$year_info['date_end']);
        $group_array[]=$location_type;
        $this->db->group_by($group_array);
        $results=$this->db->get()->result_array();
        if($results)
        {
            foreach($results as $result)
            {

                $area_initial[$result[$location_type]]['adjust_tp']+=$result['amount_tp'];
                $area_initial[$result[$location_type]]['adjust_net']+=$result['amount_net'];
            }
        }

        //sales in opening balance
        if($year_info['date_start']>0)
        {
            $this->db->from($this->config->item('ems_sales_po_details').' pod');
            $this->db->select('SUM(quantity*variety_price) total_sales_tp');
            $this->db->select('SUM(quantity*variety_price_net) total_sales_net');

            $this->db->select('cus.id customer_id,cus.name customer_name');
            $this->db->select('d.id district_id');
            $this->db->select('t.id territory_id');
            $this->db->select('zone.id zone_id');
            $this->db->select('zone.division_id division_id');

            $this->db->join($this->config->item('ems_sales_po').' po','po.id = pod.sales_po_id','INNER');
            $this->db->join($this->config->item('ems_csetup_customers').' cus','cus.id = po.customer_id','INNER');
            $this->db->join($this->config->item('ems_setup_location_districts').' d','d.id = cus.district_id','INNER');
            $this->db->join($this->config->item('ems_setup_location_territories').' t','t.id = d.territory_id','INNER');
            $this->db->join($this->config->item('ems_setup_location_zones').' zone','zone.id = t.zone_id','INNER');
            $this->db->where('pod.revision',1);
            $this->db->where('po.status_approved',$this->config->item('system_status_po_approval_approved'));
            if($division_id>0)
            {
                $this->db->where('zone.division_id',$division_id);
                if($zone_id>0)
                {
                    $this->db->where('zone.id',$zone_id);
                    if($territory_id>0)
                    {
                        $this->db->where('t.id',$territory_id);
                        if($district_id>0)
                        {
                            $this->db->where('d.id',$district_id);
                        }
                    }
                }
            }

            $this->db->where('po.date_approved <',$year_info['date_start']);

            $group_array[]=$location_type;
            $this->db->group_by($group_array);
            $results=$this->db->get()->result_array();
            foreach($results as $result)
            {
                $area_initial[$result[$location_type]]['opening_balance_tp']+=$result['total_sales_tp'];
                $area_initial[$result[$location_type]]['opening_balance_net']+=$result['total_sales_net'];
            }
        }
        //sales in sales
        $this->db->from($this->config->item('ems_sales_po_details').' pod');
        $this->db->select('SUM(quantity*variety_price) total_sales_tp');
        $this->db->select('SUM(quantity*variety_price_net) total_sales_net');

        $this->db->select('cus.id customer_id,cus.name customer_name');
        $this->db->select('d.id district_id');
        $this->db->select('t.id territory_id');
        $this->db->select('zone.id zone_id');
        $this->db->select('zone.division_id division_id');

        $this->db->join($this->config->item('ems_sales_po').' po','po.id = pod.sales_po_id','INNER');
        $this->db->join($this->config->item('ems_csetup_customers').' cus','cus.id = po.customer_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_districts').' d','d.id = cus.district_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_territories').' t','t.id = d.territory_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_zones').' zone','zone.id = t.zone_id','INNER');
        $this->db->where('pod.revision',1);
        $this->db->where('po.status_approved',$this->config->item('system_status_po_approval_approved'));
        if($division_id>0)
        {
            $this->db->where('zone.division_id',$division_id);
            if($zone_id>0)
            {
                $this->db->where('zone.id',$zone_id);
                if($territory_id>0)
                {
                    $this->db->where('t.id',$territory_id);
                    if($district_id>0)
                    {
                        $this->db->where('d.id',$district_id);
                    }
                }
            }
        }

        $this->db->where('po.date_approved >=',$year_info['date_start']);
        $this->db->where('po.date_approved <=',$year_info['date_end']);

        $group_array[]=$location_type;
        $this->db->group_by($group_array);
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            $area_initial[$result[$location_type]]['sales_tp']+=$result['total_sales_tp'];
            $area_initial[$result[$location_type]]['sales_net']+=$result['total_sales_net'];
        }

        //payment opening balance
        if($year_info['date_start']>0)
        {
            $this->db->from($this->config->item('ems_payment_payment').' p');
            $this->db->select('SUM(p.amount) amount');
            $this->db->select('p.date_payment_receive,p.customer_id');
            $this->db->select('d.id district_id');
            $this->db->select('t.id territory_id');
            $this->db->select('zone.id zone_id');
            $this->db->select('zone.division_id division_id');
            $this->db->where('p.status',$this->config->item('system_status_active'));
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
                        if($district_id>0)
                        {
                            $this->db->where('d.id',$district_id);
                        }
                    }
                }
            }
            $this->db->where('p.date_payment_receive <',$year_info['date_start']);
            $group_array[]=$location_type;
            $this->db->group_by($group_array);
            $results=$this->db->get()->result_array();
            if($results)
            {
                foreach($results as $result)
                {
                    $area_initial[$result[$location_type]]['opening_balance_tp']-=$result['amount'];
                    $area_initial[$result[$location_type]]['opening_balance_net']-=$result['amount'];
                }
            }

        }
        //payment
        $this->db->from($this->config->item('ems_payment_payment').' p');
        $this->db->select('p.amount,p.date_payment_receive,p.arm_bank_id,p.customer_id');
        $this->db->select('d.id district_id');
        $this->db->select('t.id territory_id');
        $this->db->select('zone.id zone_id');
        $this->db->select('zone.division_id division_id');
        $this->db->where('p.status',$this->config->item('system_status_active'));
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
                    if($district_id>0)
                    {
                        $this->db->where('d.id',$district_id);
                    }
                }
            }
        }

        $this->db->where('p.date_payment_receive >=',$year_info['date_start']);
        $this->db->where('p.date_payment_receive <=',$year_info['date_end']);

        $results=$this->db->get()->result_array();
        if($results)
        {
            foreach($results as $result)
            {
                $area_initial[$result[$location_type]]['payment_'.$result['arm_bank_id']]+=$result['amount'];
            }
        }
        //sales return in opening balance
        if($year_info['date_start']>0)
        {

            $this->db->from($this->config->item('ems_sales_po_details').' pod');
            $this->db->select('SUM(quantity_return*variety_price) total_sales_tp');
            $this->db->select('SUM(quantity_return*variety_price_net) total_sales_net');

            $this->db->select('cus.id customer_id,cus.name customer_name');
            $this->db->select('d.id district_id');
            $this->db->select('t.id territory_id');
            $this->db->select('zone.id zone_id');
            $this->db->select('zone.division_id division_id');

            $this->db->join($this->config->item('ems_sales_po').' po','po.id = pod.sales_po_id','INNER');
            $this->db->join($this->config->item('ems_csetup_customers').' cus','cus.id = po.customer_id','INNER');
            $this->db->join($this->config->item('ems_setup_location_districts').' d','d.id = cus.district_id','INNER');
            $this->db->join($this->config->item('ems_setup_location_territories').' t','t.id = d.territory_id','INNER');
            $this->db->join($this->config->item('ems_setup_location_zones').' zone','zone.id = t.zone_id','INNER');
            $this->db->where('pod.revision',1);
            $this->db->where('po.status_approved',$this->config->item('system_status_po_approval_approved'));
            if($division_id>0)
            {
                $this->db->where('zone.division_id',$division_id);
                if($zone_id>0)
                {
                    $this->db->where('zone.id',$zone_id);
                    if($territory_id>0)
                    {
                        $this->db->where('t.id',$territory_id);
                        if($district_id>0)
                        {
                            $this->db->where('d.id',$district_id);
                        }
                    }
                }
            }
            $this->db->where('pod.date_return >',0);
            $this->db->where('pod.date_return <',$year_info['date_start']);
            $group_array[]=$location_type;
            $this->db->group_by($group_array);
            $results=$this->db->get()->result_array();
            foreach($results as $result)
            {
                $area_initial[$result[$location_type]]['opening_balance_tp']-=$result['total_sales_tp'];
                $area_initial[$result[$location_type]]['opening_balance_net']-=$result['total_sales_net'];

            }
        }
        //sales return in sales
        $this->db->from($this->config->item('ems_sales_po_details').' pod');
        $this->db->select('SUM(quantity_return*variety_price) total_sales_tp');
        $this->db->select('SUM(quantity_return*variety_price_net) total_sales_net');

        $this->db->select('cus.id customer_id,cus.name customer_name');
        $this->db->select('d.id district_id');
        $this->db->select('t.id territory_id');
        $this->db->select('zone.id zone_id');
        $this->db->select('zone.division_id division_id');

        $this->db->join($this->config->item('ems_sales_po').' po','po.id = pod.sales_po_id','INNER');
        $this->db->join($this->config->item('ems_csetup_customers').' cus','cus.id = po.customer_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_districts').' d','d.id = cus.district_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_territories').' t','t.id = d.territory_id','INNER');
        $this->db->join($this->config->item('ems_setup_location_zones').' zone','zone.id = t.zone_id','INNER');
        $this->db->where('pod.revision',1);
        $this->db->where('po.status_approved',$this->config->item('system_status_po_approval_approved'));
        if($division_id>0)
        {
            $this->db->where('zone.division_id',$division_id);
            if($zone_id>0)
            {
                $this->db->where('zone.id',$zone_id);
                if($territory_id>0)
                {
                    $this->db->where('t.id',$territory_id);
                    if($district_id>0)
                    {
                        $this->db->where('d.id',$district_id);
                    }
                }
            }
        }
        $this->db->where('pod.date_return >',0);
        $this->db->where('pod.date_return >=',$year_info['date_start']);
        $this->db->where('pod.date_return <=',$year_info['date_end']);
        $group_array[]=$location_type;
        $this->db->group_by($group_array);
        $results=$this->db->get()->result_array();
        foreach($results as $result)
        {
            $area_initial[$result[$location_type]]['sales_tp']-=$result['total_sales_tp'];
            $area_initial[$result[$location_type]]['sales_net']-=$result['total_sales_net'];

        }
        foreach($area_initial as $area)
        {
            //bank sum
            foreach($arm_banks as $arm_bank)
            {
                $area['total_payment']+=($area['payment_'.$arm_bank['value']]);

            }
            //opening balance+sales-total_payment-adjustment
            $area['balance_tp']=$area['opening_balance_tp']+$area['sales_tp']-$area['total_payment']-$area['adjust_tp'];
            $area['balance_net']=$area['opening_balance_net']+$area['sales_net']-$area['total_payment']-$area['adjust_net'];

            //for printing purpose
            $items[]=$this->get_report_row_customer($area);

        }

        usort($items, array( $this, 'sorting_compare' ));
        $this->jsonReturn($items);
    }
    private function get_report_row_customer($item)
    {
        $info=array();
        $info['name']=$item['name'];
        $info['customer_code']=$item['customer_code'];
        $info['division_name']=$item['division_name'];
        $info['zone_name']=$item['zone_name'];
        $info['territory_name']=$item['territory_name'];
        $info['district_name']=$item['district_name'];
        if($item['total']!=0)
        {
            $info['total']=number_format($item['total'],2);
            $info['sales']=$item['total'];
        }
        else
        {
            $info['total']='';
            $info['sales']=0;
        }
        if($item['total_po']!=0)
        {
            $info['total_po']=$item['total_po'];
        }
        else
        {
            $info['total_po']='';
        }
        if($item['opening_balance_tp']!=0)
        {
            $info['opening_balance_tp']=number_format($item['opening_balance_tp'],2);
        }
        else
        {
            $info['opening_balance_tp']='';
        }
        if($item['opening_balance_net']!=0)
        {
            $info['opening_balance_net']=number_format($item['opening_balance_net'],2);
        }
        else
        {
            $info['opening_balance_net']='';
        }
        if($item['total_payment']!=0)
        {
            $info['total_payment']=number_format($item['total_payment'],2);
        }
        else
        {
            $info['total_payment']='';
        }
        if($item['balance_tp']!=0)
        {
            $info['balance_tp']=number_format($item['balance_tp'],2);
        }
        else
        {
            $info['balance_tp']='';
        }
        if($item['balance_net']!=0)
        {
            $info['balance_net']=number_format($item['balance_net'],2);
        }
        else
        {
            $info['balance_net']='';
        }

        if(($item['sales_tp'])!=0)
        {
            $info['payment_percentage_tp']=number_format(($item['total_payment']-$item['opening_balance_tp'])*100/($item['sales_tp']),2);
        }
        else
        {
            $info['payment_percentage_tp']='-';
        }
        if(($item['sales_net'])!=0)
        {
            $info['payment_percentage_net']=number_format(($item['total_payment']-$item['opening_balance_net'])*100/($item['sales_net']),2);
        }
        else
        {
            $info['payment_percentage_net']='-';
        }
        return $info;
    }

}
