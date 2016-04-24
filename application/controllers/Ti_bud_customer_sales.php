<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ti_bud_customer_sales extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Ti_bud_customer_sales');
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
        $this->controller_url='ti_bud_customer_sales';

    }

    public function index($action="list",$id=0)
    {
        if($action=="list")
        {
            $this->system_list();
        }
        elseif($action=="add")
        {
            $this->system_add();
        }
        elseif($action=="get_customer_varieties")
        {
            $this->get_customer_varieties();
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
            $this->system_list();
        }
    }

    private function system_list()
    {
        if(isset($this->permissions['view'])&&($this->permissions['view']==1))
        {
            $data['title']="Customer Sales Predicted List";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("ti_bud_customer_sales/list",$data,true));
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

    private function system_add()
    {
        if((isset($this->permissions['add'])&&($this->permissions['add']==1))||(isset($this->permissions['edit'])&&($this->permissions['edit']==1)))
        {
            $data['budget']=array();
            $data['fiscal_year_id']=0;
            $data['fiscal_year_name']='';
            $time=time();
            $data['fiscal_years']=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text','date_start','date_end'),array('status ="'.$this->config->item('system_status_active').'"'));
            //deciding current year is in a fiscal year
            foreach($data['fiscal_years'] as $year)
            {
                if($year['date_start']<=$time && $year['date_end']>=$time)
                {
                    $data['budget']['fiscal_year_id']=$year['value'];
                    $data['budget']['fiscal_year_name']=$year['text'];
                    break;
                }

            }
            if(!($data['budget']['fiscal_year_id']>0))
            {
                $ajax['status']=false;
                $ajax['system_message']="Current Date is not in any Fiscal Year.";
                $this->jsonReturn($ajax);
            }


            $data['title']="New Customer Sales Prediction";

            $data['budget']['division_id']=$this->locations['division_id'];
            $data['budget']['zone_id']=$this->locations['zone_id'];
            $data['budget']['territory_id']=$this->locations['territory_id'];
            $data['budget']['district_id']=$this->locations['district_id'];
            $data['budget']['customer_id']='';

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
            $data['crops']=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array());


            $ajax['system_page_url']=site_url($this->controller_url."/index/add");

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("ti_bud_customer_sales/search",$data,true));
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
    private function system_edit($id)
    {
        if((isset($this->permissions['edit'])&&($this->permissions['edit']==1))||(isset($this->permissions['add'])&&($this->permissions['add']==1)))
        {
            if(($this->input->post('id')))
            {
                $survey_id=$this->input->post('id');
            }
            else
            {
                $survey_id=$id;
            }
            $this->db->from($this->config->item('table_survey_primary').' sp');
            $this->db->select('sp.*');
            $this->db->select('d.id district_id');
            $this->db->select('t.id territory_id');
            $this->db->select('zone.id zone_id');
            $this->db->select('zone.division_id division_id');
            $this->db->join($this->config->item('table_setup_location_upazillas').' upz','upz.id = sp.upazilla_id','INNER');
            $this->db->join($this->config->item('table_setup_location_districts').' d','d.id = upz.district_id','INNER');
            $this->db->join($this->config->item('table_setup_location_territories').' t','t.id = d.territory_id','INNER');
            $this->db->join($this->config->item('table_setup_location_zones').' zone','zone.id = t.zone_id','INNER');
            $this->db->where('sp.id',$survey_id);

            $data['survey']=$this->db->get()->row_array();
            if(!$data['survey'])
            {
                System_helper::invalid_try($this->config->item('system_edit_not_exists'),$survey_id);
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->jsonReturn($ajax);
            }
            if(!$this->check_my_editable($data['survey']))
            {
                System_helper::invalid_try($this->config->item('system_edit_others'),$survey_id);
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
                $this->jsonReturn($ajax);
            }
            $data['title']="Market Survey";
            $data['divisions']=Query_helper::get_info($this->config->item('table_setup_location_divisions'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));
            $data['zones']=Query_helper::get_info($this->config->item('table_setup_location_zones'),array('id value','name text'),array('division_id ='.$data['survey']['division_id']));
            $data['territories']=Query_helper::get_info($this->config->item('table_setup_location_territories'),array('id value','name text'),array('zone_id ='.$data['survey']['zone_id']));
            $data['districts']=Query_helper::get_info($this->config->item('table_setup_location_districts'),array('id value','name text'),array('territory_id ='.$data['survey']['territory_id']));
            $data['upazillas']=Query_helper::get_info($this->config->item('table_setup_location_upazillas'),array('id value','name text'),array('district_id ='.$data['survey']['district_id']));

            $data['crops']=Query_helper::get_info($this->config->item('table_setup_classification_crops'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'));

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("survey_primary_market/search",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url.'/index/edit/'.$survey_id);
            $this->jsonReturn($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
        }
    }
    private function get_customer_varieties()
    {
        echo '<PRE>';
        print_r($this->input->post());
        echo '</PRE>';
        die();
        $data['year']=$this->input->post('year');
        $data['crop_type_id']=$this->input->post('crop_type_id');
        $data['upazilla_id']=$this->input->post('upazilla_id');
        $data['unions']=Query_helper::get_info($this->config->item('table_setup_location_unions'),array('id value','name text'),array('upazilla_id ='.$data['upazilla_id'],'status ="'.$this->config->item('system_status_active').'"'));
        $data['varieties_arm']=Query_helper::get_info($this->config->item('table_setup_classification_varieties'),'*',array('crop_type_id ='.$data['crop_type_id'],'status ="'.$this->config->item('system_status_active').'"','whose ="ARM"'));
        $data['varieties_competitor']=Query_helper::get_info($this->config->item('table_setup_classification_varieties'),'*',array('crop_type_id ='.$data['crop_type_id'],'status ="'.$this->config->item('system_status_active').'"','whose ="Competitor"'));
        $data['max_customers_number']=$this->config->item('system_msurvey_customers_num');
        $data['survey']=Query_helper::get_info($this->config->item('table_survey_primary'),'*',array('year ='.$data['year'],'crop_type_id ='.$data['crop_type_id'],'upazilla_id ='.$data['upazilla_id'],'status ="'.$this->config->item('system_status_active').'"'),1);
        $data['survey_customer_survey']=array();
        $data['survey_quantity_survey']=array();
        $data['customers']=array();
        $customers=Query_helper::get_info($this->config->item('table_survey_primary_customers'),'*',array('year ='.$data['year'],'upazilla_id ='.$data['upazilla_id']));
        foreach($customers as $customer)
        {
            $data['customers'][$customer['customer_no']]=$customer;
        }
        if($data['survey'])
        {
            $data['title']="Edit Survey";


            $customer_survey=Query_helper::get_info($this->config->item('table_survey_primary_customer_survey'),'*',array('survey_id ='.$data['survey']['id']));
            foreach($customer_survey as $survey)
            {
                $data['survey_customer_survey'][$survey['variety_id']][$survey['customer_no']]=$survey;
            }
            $quantity_survey=Query_helper::get_info($this->config->item('table_survey_primary_quantity_survey'),'*',array('survey_id ='.$data['survey']['id']));
            foreach($quantity_survey as $survey)
            {
                $data['survey_quantity_survey'][$survey['variety_id']]=$survey;
            }
        }
        else
        {
            $data['title']="New Survey";//edit Survey
        }

        //get info
        //if exits check my editable

        $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("survey_primary_market/add_edit",$data,true));
        if($this->message)
        {
            $ajax['system_message']=$this->message;
        }
        $this->jsonReturn($ajax);

    }
    private function system_save()
    {
        $user = User_helper::get_user();
        if(!((isset($this->permissions['edit'])&&($this->permissions['edit']==1))||(isset($this->permissions['add'])&&($this->permissions['add']==1))))
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
            $time=time();
            $data=array();
            $year=$this->input->post('year');
            $crop_type_id=$this->input->post('crop_type_id');
            $upazilla_id=$this->input->post('upazilla_id');
            $survey=Query_helper::get_info($this->config->item('table_survey_primary'),'*',array('year ='.$year,'crop_type_id ='.$crop_type_id,'upazilla_id ='.$upazilla_id,'status ="'.$this->config->item('system_status_active').'"'),1);
            if($survey)
            {
                $data['user_updated'] = $user->user_id;
                $data['date_updated'] = $time;
            }
            else
            {
                $data['user_created'] = $user->user_id;
                $data['date_created'] = $time;
            }


            $unions=$this->input->post('unions');
            if(sizeof($unions)>0)
            {
                $data['union_ids']=json_encode($unions);
            }


            $data['remarks']=$this->input->post('remarks');

            $this->db->trans_start();  //DB Transaction Handle START
            if($survey)
            {
                $survey_id=$survey['id'];
                Query_helper::update($this->config->item('table_survey_primary'),$data,array("id = ".$survey['id']));

            }
            else
            {
                $data['year']=$year;
                $data['crop_type_id']=$crop_type_id;
                $data['upazilla_id']=$upazilla_id;
                $survey_id=Query_helper::add($this->config->item('table_survey_primary'),$data);
                if($survey_id===false)
                {
                    $this->db->trans_complete();
                    $ajax['status']=false;
                    $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                    $this->jsonReturn($ajax);
                    die();
                }
            }
            $survey_customers=array();
            $customers=Query_helper::get_info($this->config->item('table_survey_primary_customers'),'*',array('year ='.$year,'upazilla_id ='.$upazilla_id));
            foreach($customers as $customer)
            {
                $survey_customers[$customer['customer_no']]=$customer;
            }
            $customers=$this->input->post('customers');
            if(sizeof($customers)>0)
            {
                foreach($customers as $i=>$customer)
                {
                    if(strlen($customer)>0)
                    {
                        $data=array();
                        $data['name']=$customer;
                        if(isset($survey_customers[$i]))
                        {
                            $data['user_updated'] = $user->user_id;
                            $data['date_updated'] = $time;
                            Query_helper::update($this->config->item('table_survey_primary_customers'),$data,array("id = ".$survey_customers[$i]['id']));
                        }
                        else
                        {
                            $data['year'] = $year;
                            $data['upazilla_id'] = $upazilla_id;
                            $data['customer_no'] = $i;
                            $data['user_created'] = $user->user_id;
                            $data['date_created'] = $time;
                            Query_helper::add($this->config->item('table_survey_primary_customers'),$data);
                        }
                    }

                }
            }
            $survey_customer_survey=array();
            $customer_survey=Query_helper::get_info($this->config->item('table_survey_primary_customer_survey'),'*',array('survey_id ='.$survey_id));
            foreach($customer_survey as $survey)
            {
                $survey_customer_survey[$survey['variety_id']][$survey['customer_no']]=$survey;
            }
            $varieties=$this->input->post('varieties');
            if(sizeof($varieties)>0)
            {
                foreach($varieties as $variety_id=>$variety)
                {
                    foreach($variety as $i=>$customer)
                    {
                        $data=array();
                        if(isset($customer['weight_sales'])&&$customer['weight_sales']>0)
                        {
                            $data['weight_sales']=$customer['weight_sales'];
                        }
                        if(isset($customer['weight_market'])&&$customer['weight_market']>0)
                        {
                            $data['weight_market']=$customer['weight_market'];
                        }
                        if($data)
                        {
                            if(isset($survey_customer_survey[$variety_id][$i]))
                            {
                                $data['user_updated'] = $user->user_id;
                                $data['date_updated'] = $time;
                                Query_helper::update($this->config->item('table_survey_primary_customer_survey'),$data,array("id = ".$survey_customer_survey[$variety_id][$i]['id']));
                            }
                            else
                            {
                                $data['survey_id'] = $survey_id;
                                $data['variety_id'] = $variety_id;
                                $data['customer_no'] = $i;
                                $data['user_created'] = $user->user_id;
                                $data['date_created'] = $time;
                                Query_helper::add($this->config->item('table_survey_primary_customer_survey'),$data);
                            }

                        }
                    }
                }
            }
            $survey_quantity_survey=array();
            $quantity_survey=Query_helper::get_info($this->config->item('table_survey_primary_quantity_survey'),'*',array('survey_id ='.$survey_id));
            foreach($quantity_survey as $survey)
            {
                $survey_quantity_survey[$survey['variety_id']]=$survey;
            }

            $weights_assumed=$this->input->post('weight_assumed');
            if(sizeof($weights_assumed)>0)
            {
                foreach($weights_assumed as $variety_id=>$weight_assumed)
                {
                    if($weight_assumed>0)
                    {
                        $data=array();
                        $data['weight_assumed']=$weight_assumed;
                        if(isset($survey_quantity_survey[$variety_id]))
                        {
                            $data['user_updated'] = $user->user_id;
                            $data['date_updated'] = $time;
                            Query_helper::update($this->config->item('table_survey_primary_quantity_survey'),$data,array("id = ".$survey_quantity_survey[$variety_id]['id']));

                        }
                        else
                        {
                            $data['survey_id'] = $survey_id;
                            $data['variety_id'] = $variety_id;
                            $data['user_created'] = $user->user_id;
                            $data['date_created'] = $time;
                            Query_helper::add($this->config->item('table_survey_primary_quantity_survey'),$data);
                        }
                    }


                }
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
    private function check_my_editable($customer)
    {
        if(($this->locations['division_id']>0)&&($this->locations['division_id']!=$customer['division_id']))
        {
            return false;
        }
        if(($this->locations['zone_id']>0)&&($this->locations['zone_id']!=$customer['zone_id']))
        {
            return false;
        }
        if(($this->locations['territory_id']>0)&&($this->locations['territory_id']!=$customer['territory_id']))
        {
            return false;
        }
        if(($this->locations['district_id']>0)&&($this->locations['district_id']!=$customer['district_id']))
        {
            return false;
        }
        return true;
    }

    private function check_validation()
    {
        return true;
    }

    public function get_items()
    {
        /*$this->db->from($this->config->item('table_survey_primary').' sp');

        $this->db->select('COUNT(sp.crop_type_id) num_types');
        $this->db->select('COUNT(Distinct  types.crop_id) num_crops');
        $this->db->select('sp.*');

        $this->db->select('upz.name upazilla_name');
        $this->db->select('d.name district_name');
        $this->db->select('t.name territory_name');
        $this->db->select('zone.name zone_name');
        $this->db->select('division.name division_name');
        $this->db->join($this->config->item('table_setup_location_upazillas').' upz','upz.id = sp.upazilla_id','INNER');
        $this->db->join($this->config->item('table_setup_location_districts').' d','d.id = upz.district_id','INNER');
        $this->db->join($this->config->item('table_setup_location_territories').' t','t.id = d.territory_id','INNER');
        $this->db->join($this->config->item('table_setup_location_zones').' zone','zone.id = t.zone_id','INNER');
        $this->db->join($this->config->item('table_setup_location_divisions').' division','division.id = zone.division_id','INNER');

        $this->db->join($this->config->item('table_setup_classification_crop_types').' types','types.id = sp.crop_type_id','INNER');
        if($this->locations['division_id']>0)
        {
            $this->db->where('division.id',$this->locations['division_id']);
            if($this->locations['zone_id']>0)
            {
                $this->db->where('zone.id',$this->locations['zone_id']);
                if($this->locations['territory_id']>0)
                {
                    $this->db->where('t.id',$this->locations['territory_id']);
                    if($this->locations['district_id']>0)
                    {
                        $this->db->where('d.id',$this->locations['district_id']);
                        if($this->locations['upazilla_id']>0)
                        {
                            $this->db->where('upz.id',$this->locations['upazilla_id']);
                        }
                    }
                }
            }
        }
        $this->db->group_by(array('sp.year','sp.upazilla_id'));
        $this->db->order_by('sp.year','DESC');
        $this->db->order_by('sp.id','DESC');
        $items=$this->db->get()->result_array();*/
        $items=array();
        $this->jsonReturn($items);
    }

}
