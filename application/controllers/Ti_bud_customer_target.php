<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ti_bud_customer_target extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Ti_bud_customer_target');
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
        if((isset($this->permissions['edit'])&&($this->permissions['edit']==1))||(isset($this->permissions['add'])&&($this->permissions['add']==1)))
        {
            $this->permissions['forward']=1;
        }
        $this->controller_url='ti_bud_customer_target';

    }
    //$id1--division id
    //$id2--year0
    //$id3--crop_id
    public function index($action="search",$id1=0,$id2=0,$id3=0)
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
            $this->get_items($id1,$id2);
        }
        elseif($action=="edit")
        {
            $this->system_edit($id1,$id2,$id3);
        }
        elseif($action=="get_edit_items")
        {
            $this->system_get_edit_items();
        }
        elseif($action=="save")
        {
            $this->system_save();
        }
        elseif($action=="details")
        {
            $this->system_details($id1,$id2,$id3);
        }
        elseif($action=="forward")
        {
            $this->system_forward($id1,$id2,$id3);
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
            $data['budget']=array();
            $data['budget']['year0_id']=$fy_info['budget_year']['value'];
            $data['budget']['division_id']=$this->locations['division_id'];
            $data['budget']['zone_id']=$this->locations['zone_id'];
            $data['budget']['territory_id']=$this->locations['territory_id'];
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

            $data['title']="Assign Customer Target";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("ti_bud_customer_target/search",$data,true));
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
            $data['territory_id']=$this->input->post('territory_id');
            $data['year0_id']=$this->input->post('year0_id');
            $keys=',';
            $keys.="territory_id:'".$data['territory_id']."',";
            $keys.="year0_id:'".$data['year0_id']."',";
            $data['keys']=trim($keys,',');
            $data['title']="Crop List";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("ti_bud_customer_target/list",$data,true));
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
    private function get_items($territory_id,$year0_id)
    {
        $territory_info=Query_helper::get_info($this->config->item('ems_setup_location_territories'),'*',array('id ='.$territory_id),1);
        $this->db->from($this->config->item('ems_setup_classification_crops').' crop');
        $this->db->select('crop.id,crop.name crop_name');

        //$this->db->select('fdi.status_assign status_target_finalize');
        //$this->db->select('fzi.status_assign status_assign');
        $this->db->select('fzi.status_assign status_target_finalize');
        $this->db->select('fti.status_assign status_assign');

        //$this->db->join($this->config->item('table_forward_di').' fdi','fdi.crop_id = crop.id and fdi.year0_id ='.$year0_id.' and division_id ='.$zone_info['division_id'],'LEFT');
        $this->db->join($this->config->item('table_forward_zi').' fzi','fzi.crop_id = crop.id and fzi.year0_id ='.$year0_id.' and zone_id ='.$territory_info['zone_id'],'LEFT');
        $this->db->join($this->config->item('table_forward_ti').' fti','fti.crop_id = crop.id and fti.year0_id ='.$year0_id.' and territory_id ='.$territory_id,'LEFT');

        $this->db->order_by('crop.ordering','ASC');
        $this->db->where('crop.status',$this->config->item('system_status_active'));
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            if(!$item['status_assign'])
            {
                $item['status_assign']=$this->config->item('system_status_no');
            }
            if(!$item['status_target_finalize'])
            {
                $item['status_target_finalize']=$this->config->item('system_status_no');
            }
        }
        $this->jsonReturn($items);
    }

    private function system_edit($territory_id,$year0_id,$crop_id)
    {
        $territory_info=Query_helper::get_info($this->config->item('ems_setup_location_territories'),'*',array('id ='.$territory_id),1);
        if((isset($this->permissions['edit'])&&($this->permissions['edit']==1))||(isset($this->permissions['add'])&&($this->permissions['add']==1)))
        {
            if(($this->input->post('id')))
            {
                $crop_id=$this->input->post('id');
            }
            $info=Query_helper::get_info($this->config->item('table_forward_zi'),'*',array('year0_id ='.$year0_id,'crop_id ='.$crop_id,'zone_id ='.$territory_info['zone_id']),1);
            if($info)
            {
                if($info['status_assign']!==$this->config->item('system_status_yes'))
                {
                    $ajax['status']=false;
                    $ajax['system_message']=$this->lang->line("MSG_TARGET_NOT_FINALIZED");
                    $this->jsonReturn($ajax);
                    die();
                }
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_TARGET_NOT_FINALIZED");
                $this->jsonReturn($ajax);
                die();
            }
            if((!isset($this->permissions['edit'])||($this->permissions['edit']!=1)))
            {
                $info=Query_helper::get_info($this->config->item('table_forward_ti'),'*',array('year0_id ='.$year0_id,'crop_id ='.$crop_id,'territory_id ='.$territory_id),1);

                if($info)
                {
                    if($info['status_assign']===$this->config->item('system_status_yes'))
                    {
                        $ajax['status']=false;
                        $ajax['system_message']=$this->lang->line("MSG_ALREADY_FORWARDED");
                        $this->jsonReturn($ajax);
                        die();
                    }
                }
            }
            $crop=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array('id ='.$crop_id),1);
            $data['years']=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"',' id >='.$year0_id),$this->config->item('num_year_prediction')+1,0,array('id ASC'));
            $data['territory_id']=$territory_id;
            $data['year0_id']=$year0_id;
            $data['crop_id']=$crop_id;
            //customers
            $this->db->from($this->config->item('ems_csetup_customers').' cus');
            $this->db->select('cus.id value');
            $this->db->select('CONCAT(cus.customer_code," - ",cus.name) text');
            $this->db->join($this->config->item('ems_setup_location_districts').' d','d.id = cus.district_id','INNER');
            $this->db->where('d.territory_id',$territory_id);
            $this->db->order_by('cus.ordering','ASC');
            $this->db->where('cus.status',$this->config->item('system_status_active'));
            $data['areas']=$this->db->get()->result_array();//customers

            $keys=',';
            $keys.="territory_id:'".$territory_id."',";
            $keys.="year0_id:'".$year0_id."',";
            $keys.="crop_id:'".$crop_id."',";
            $data['keys']=trim($keys,',');


            $data['title']="Assign Customers Target For ".$crop['text'].'('.$data['years'][0]['text'].')';
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("ti_bud_customer_target/add_edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url."/index/edit/".$territory_id.'/'.$year0_id.'/'.$crop_id);
            $this->jsonReturn($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
        }
    }
    private function system_get_edit_items()
    {
        $items=array();
        $territory_id=$this->input->post('territory_id');
        $year0_id=$this->input->post('year0_id');
        $crop_id=$this->input->post('crop_id');

        //may be need to check if already forwarded
        //may be short out with crops
        $results=Query_helper::get_info($this->config->item('table_ti_bud_ti_bt'),'*',array('year0_id ='.$year0_id,'territory_id ='.$territory_id));
        $incharge_budget_target=array();//DI budget and target
        foreach($results as $result)
        {
            $incharge_budget_target[$result['variety_id']]=$result;
        }
        $this->db->from($this->config->item('ems_csetup_customers').' cus');
        $this->db->select('cus.id value');
        $this->db->select('CONCAT(cus.customer_code," - ",cus.name) text');
        $this->db->join($this->config->item('ems_setup_location_districts').' d','d.id = cus.district_id','INNER');
        $this->db->where('d.territory_id',$territory_id);
        $this->db->order_by('cus.ordering','ASC');
        $this->db->where('cus.status',$this->config->item('system_status_active'));
        $areas=$this->db->get()->result_array();//customers

        //my be short less by crop_id
        $results=Query_helper::get_info($this->config->item('table_ti_bud_customer_bt'),'*',array('territory_id ='.$territory_id,'year0_id ='.$year0_id));
        $area_budget_target=array();//ti budget and target
        foreach($results as $result)
        {
            $area_budget_target[$result['customer_id']][$result['variety_id']]=$result;
        }


        $this->db->from($this->config->item('ems_setup_classification_varieties').' v');
        $this->db->select('v.id,v.name');
        $this->db->select('type.name type_name');
        $this->db->join($this->config->item('ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->where('v.whose','ARM');
        $this->db->where('v.status =',$this->config->item('system_status_active'));
        $this->db->where('type.crop_id',$crop_id);
        $this->db->order_by('type.ordering','ASC');
        $this->db->order_by('v.ordering','ASC');

        $results=$this->db->get()->result_array();

        $count=0;
        $prev_type='';
        foreach($results as $index=>$result)
        {
            $item=array();
            if($index>0)
            {
                if($prev_type!=$result['type_name'])
                {
                    $count=0;
                    $item['type_name']=$result['type_name'];
                    $prev_type=$result['type_name'];
                }
                else
                {
                    $item['type_name']='';
                }
            }
            else
            {
                $prev_type=$result['type_name'];
                $item['type_name']=$result['type_name'];
            }

            $count++;
            $item['sl_no']=$count;
            $item['variety_id']=$result['id'];
            $item['variety_name']=$result['name'];
            if(isset($incharge_budget_target[$item['variety_id']]))
            {

                if(($incharge_budget_target[$item['variety_id']]['year0_budget_quantity']==null) ||($incharge_budget_target[$item['variety_id']]['year0_budget_quantity']==0))
                {
                    $item['year0_budget_quantity']='-';
                }
                else
                {
                    $item['year0_budget_quantity']=$incharge_budget_target[$item['variety_id']]['year0_budget_quantity'];
                }
                if(($incharge_budget_target[$item['variety_id']]['year0_target_quantity']==null) ||($incharge_budget_target[$item['variety_id']]['year0_target_quantity']==0))
                {
                    $item['year0_target_quantity']='-';
                }
                else
                {
                    $item['year0_target_quantity']=$incharge_budget_target[$item['variety_id']]['year0_target_quantity'];
                }
            }
            else
            {
                $item['year0_budget_quantity']='-';
                $item['year0_target_quantity']='-';
            }
            foreach($areas as $area)
            {
                if(isset($area_budget_target[$area['value']][$item['variety_id']]))
                {
                    if(($area_budget_target[$area['value']][$item['variety_id']]['year0_budget_quantity']==null) ||($area_budget_target[$area['value']][$item['variety_id']]['year0_budget_quantity']==0))
                    {
                        $item['year0_budget_quantity_'.$area['value']]='';
                    }
                    else
                    {
                        $item['year0_budget_quantity_'.$area['value']]=$area_budget_target[$area['value']][$item['variety_id']]['year0_budget_quantity'];
                    }
                    if(($area_budget_target[$area['value']][$item['variety_id']]['year0_target_quantity']==null) ||($area_budget_target[$area['value']][$item['variety_id']]['year0_target_quantity']==0))
                    {
                        $item['year0_target_quantity_'.$area['value']]='';
                    }
                    else
                    {
                        $item['year0_target_quantity_'.$area['value']]=$area_budget_target[$area['value']][$item['variety_id']]['year0_target_quantity'];
                    }


                }
                else
                {
                    $item['year0_budget_quantity_'.$area['value']]='-';
                    $item['year0_target_quantity_'.$area['value']]='';
                }
                $item['year0_target_quantity_'.$area['value'].'_editable']=true;
            }
            $items[]=$item;
        }

        $this->jsonReturn($items);

    }
    private function system_save()
    {
        $territory_id=$this->input->post('territory_id');
        $year0_id=$this->input->post('year0_id');
        $crop_id=$this->input->post('crop_id');
        $territory_info=Query_helper::get_info($this->config->item('ems_setup_location_territories'),'*',array('id ='.$territory_id),1);
        $user = User_helper::get_user();
        $time=time();
        if((isset($this->permissions['edit'])&&($this->permissions['edit']==1))||(isset($this->permissions['add'])&&($this->permissions['add']==1)))
        {
            $info=Query_helper::get_info($this->config->item('table_forward_zi'),'*',array('year0_id ='.$year0_id,'crop_id ='.$crop_id,'zone_id ='.$territory_info['zone_id']),1);
            if($info)
            {
                if($info['status_assign']!==$this->config->item('system_status_yes'))
                {
                    $ajax['status']=false;
                    $ajax['system_message']=$this->lang->line("MSG_TARGET_NOT_FINALIZED");
                    $this->jsonReturn($ajax);
                    die();
                }
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_TARGET_NOT_FINALIZED");
                $this->jsonReturn($ajax);
                die();
            }
            if((!isset($this->permissions['edit'])||($this->permissions['edit']!=1)))
            {
                $info=Query_helper::get_info($this->config->item('table_forward_ti'),'*',array('year0_id ='.$year0_id,'crop_id ='.$crop_id,'territory_id ='.$territory_id),1);

                if($info)
                {
                    if($info['status_assign']===$this->config->item('system_status_yes'))
                    {
                        $ajax['status']=false;
                        $ajax['system_message']=$this->lang->line("MSG_ALREADY_FORWARDED");
                        $this->jsonReturn($ajax);
                        die();
                    }
                }
            }
            $items=$this->input->post('items');
            $this->db->trans_start();
            if(sizeof($items)>0)
            {
                $results=Query_helper::get_info($this->config->item('table_ti_bud_customer_bt'),'*',array('territory_id ='.$territory_id,'year0_id ='.$year0_id));
                $area_budget_target=array();//ti budget and target
                foreach($results as $result)
                {
                    $area_budget_target[$result['customer_id']][$result['variety_id']]=$result;
                }
                foreach($items as $customer_id=>$item)
                {
                    foreach($item as $variety_id=> $year0_target_quantity)
                    {
                        $data=array();
                        if(isset($area_budget_target[$customer_id][$variety_id]))
                        {
                            $data['year0_target_quantity']=$year0_target_quantity;
                            $data['user_updated'] = $user->user_id;
                            $data['date_updated'] = $time;
                            $data['user_targeted'] = $user->user_id;
                            $data['date_targeted'] = $time;
                            if($year0_target_quantity!=$area_budget_target[$customer_id][$variety_id]['year0_target_quantity'])
                            {
                                Query_helper::update($this->config->item('table_ti_bud_customer_bt'),$data,array("id = ".$area_budget_target[$customer_id][$variety_id]['id']));
                            }

                        }
                        else
                        {
                            $data['territory_id'] = $territory_id;
                            $data['customer_id'] = $customer_id;
                            $data['year0_id'] = $year0_id;
                            $data['variety_id'] = $variety_id;
                            $data['year0_target_quantity']=$year0_target_quantity;
                            $data['user_created'] = $user->user_id;
                            $data['date_created'] = $time;
                            $data['user_targeted'] = $user->user_id;
                            $data['date_targeted'] = $time;
                            if(!empty($year0_target_quantity))
                            {
                                Query_helper::add($this->config->item('table_ti_bud_customer_bt'),$data);
                            }
                        }


                    }
                }
            }
            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {
                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                $this->system_edit($territory_id,$year0_id,$crop_id);
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
                $this->jsonReturn($ajax);
            }
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
        }

    }
    private function system_details($territory_id,$year0_id,$crop_id)
    {
        $territory_info=Query_helper::get_info($this->config->item('ems_setup_location_territories'),'*',array('id ='.$territory_id),1);
        if(isset($this->permissions['view'])&&($this->permissions['view']==1))
        {
            if(($this->input->post('id')))
            {
                $crop_id=$this->input->post('id');
            }
            $crop=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array('id ='.$crop_id),1);
            $data['years']=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"',' id >='.$year0_id),$this->config->item('num_year_prediction')+1,0,array('id ASC'));
            $data['territory_id']=$territory_id;
            $data['year0_id']=$year0_id;
            $data['crop_id']=$crop_id;
            //Customers
            $this->db->from($this->config->item('ems_csetup_customers').' cus');
            $this->db->select('cus.id value');
            $this->db->select('CONCAT(cus.customer_code," - ",cus.name) text');
            $this->db->join($this->config->item('ems_setup_location_districts').' d','d.id = cus.district_id','INNER');
            $this->db->where('d.territory_id',$territory_id);
            $this->db->order_by('cus.ordering','ASC');
            $this->db->where('cus.status',$this->config->item('system_status_active'));
            $data['areas']=$this->db->get()->result_array();//customers


            $keys=',';
            $keys.="territory_id:'".$territory_id."',";
            $keys.="year0_id:'".$year0_id."',";
            $keys.="crop_id:'".$crop_id."',";
            $data['keys']=trim($keys,',');


            $data['title']="Customers Target For ".$crop['text'].'('.$data['years'][0]['text'].')';
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("ti_bud_customer_target/details",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url."/index/details/".$territory_id.'/'.$year0_id.'/'.$crop_id);
            $this->jsonReturn($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
        }
    }

    private function system_forward($territory_id,$year0_id,$crop_id)
    {
        $territory_info=Query_helper::get_info($this->config->item('ems_setup_location_territories'),'*',array('id ='.$territory_id),1);
        $user = User_helper::get_user();
        $time=time();
        if(($this->input->post('id')))
        {
            $crop_id=$this->input->post('id');
        }
        $info=Query_helper::get_info($this->config->item('table_forward_zi'),'*',array('year0_id ='.$year0_id,'crop_id ='.$crop_id,'zone_id ='.$territory_info['zone_id']),1);
        if($info)
        {
            if($info['status_assign']!==$this->config->item('system_status_yes'))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_TARGET_NOT_FINALIZED");
                $this->jsonReturn($ajax);
                die();
            }
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_TARGET_NOT_FINALIZED");
            $this->jsonReturn($ajax);
            die();
        }
        $info=Query_helper::get_info($this->config->item('table_forward_ti'),'*',array('year0_id ='.$year0_id,'crop_id ='.$crop_id,'territory_id ='.$territory_id),1);
        $this->db->trans_start();
        if($info)
        {
            if($info['status_assign']===$this->config->item('system_status_yes'))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_ALREADY_FORWARDED");
                $this->jsonReturn($ajax);
                die();
            }
            else
            {
                $data=array();
                $data['status_assign']=$this->config->item('system_status_yes');
                $data['user_assigned'] = $user->user_id;
                $data['date_assigned'] = $time;
                $data['user_updated'] = $user->user_id;
                $data['date_updated'] = $time;
                Query_helper::update($this->config->item('table_forward_ti'),$data,array("id = ".$info['id']));
            }
        }
        else
        {
            $data=array();
            $data['status_assign']=$this->config->item('system_status_yes');
            $data['zone_id']=$territory_id;
            $data['year0_id']=$year0_id;
            $data['crop_id']=$crop_id;
            $data['user_created'] = $user->user_id;
            $data['date_created'] = $time;
            $data['user_assigned'] = $user->user_id;
            $data['date_assigned'] = $time;
            Query_helper::add($this->config->item('table_forward_ti'),$data);
        }
        $this->db->trans_complete();   //DB Transaction Handle END
        if ($this->db->trans_status() === TRUE)
        {
            $ajax['status']=true;
            $ajax['system_message']=$this->lang->line("MSG_SUCCESSFULLY_FORWARDED");
            $this->jsonReturn($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("MSG_SAVED_FAIL");
            $this->jsonReturn($ajax);
        }
    }
    private function check_my_editable($customer)
    {
        if(($this->locations['division_id']>0)&&($this->locations['division_id']!=$customer['division_id']))
        {
            return false;
        }
        return true;
    }


}
