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
            }

        }
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
        foreach($results as $result)
        {
            $item=array();
            $item['name']=$result['customer_name'];
            $item['customer_code']=$result['customer_code'];
            $item['division_name']=$result['division_name'];
            $item['zone_name']=$result['zone_name'];
            $item['territory_name']=$result['territory_name'];
            $item['district_name']=$result['district_name'];
            $item['total']=0;
            foreach($months as $month)
            {
                if(isset($sales_total[$result['customer_id']][$month]['net_sales']))
                {
                    $item['total']+=$sales_total[$result['customer_id']][$month]['net_sales'];
                }
            }
            $items[]=$this->get_report_row_customer($item);
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
        return $info;
    }

}