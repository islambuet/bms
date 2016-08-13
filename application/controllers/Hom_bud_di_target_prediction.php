<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Hom_bud_di_target_prediction extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Hom_bud_di_target_prediction');
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
        $this->controller_url='hom_bud_di_target_prediction';

    }

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
            $this->get_items($id1);
        }
        elseif($action=="edit")
        {
            $this->system_edit($id1,$id2);
        }
        elseif($action=="get_edit_items")
        {
            $this->system_get_edit_items();
        }
        /*elseif($action=="save")
        {
            $this->system_save();
        }
        elseif($action=="details")
        {
            $this->system_details($id1,$id2);
        }
        elseif($action=="forward")
        {
            $this->system_forward($id1,$id2);
        }*/
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
            //$data['budget']['division_id']=$this->locations['division_id'];
            //$data['budget']['zone_id']=$this->locations['zone_id'];
            $data['title']="Assign 3Years Target Search";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("hom_bud_di_target_prediction/search",$data,true));
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
            $data['year0_id']=$this->input->post('year0_id');
            $keys=',';
            $keys.="year0_id:'".$data['year0_id']."',";
            $data['keys']=trim($keys,',');
            $data['title']="Crop List";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("Hom_bud_di_target_prediction/list",$data,true));
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
    private function get_items($year0_id)
    {

        $this->db->from($this->config->item('ems_setup_classification_crops').' crop');
        $this->db->select('crop.id,crop.name crop_name');
        $this->db->select('fhom.status_prediction_assign,fhom.status_prediction_finalize');
        $this->db->join($this->config->item('table_forward_hom').' fhom','fhom.crop_id = crop.id and year0_id ='.$year0_id,'LEFT');
        //$this->db->where('d.territory_id',$territory_id);

        $this->db->order_by('crop.ordering','ASC');
        $this->db->where('crop.status',$this->config->item('system_status_active'));
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            if(!$item['status_prediction_assign'])
            {
                $item['status_prediction_assign']=$this->config->item('system_status_no');
            }
            if(!$item['status_prediction_finalize'])
            {
                $item['status_prediction_finalize']=$this->config->item('system_status_no');
            }
        }
        $this->jsonReturn($items);
    }

    private function system_edit($year0_id,$crop_id)
    {
        if((isset($this->permissions['edit'])&&($this->permissions['edit']==1))||(isset($this->permissions['add'])&&($this->permissions['add']==1)))
        {
            if(($this->input->post('id')))
            {
                $crop_id=$this->input->post('id');
            }
            //only for HOM target finalized is in same table
            $info=Query_helper::get_info($this->config->item('table_forward_hom'),'*',array('year0_id ='.$year0_id,'crop_id ='.$crop_id),1);
            if($info)
            {
                if($info['status_prediction_finalize']!=$this->config->item('system_status_yes'))
                {
                    $ajax['status']=false;
                    $ajax['system_message']=$this->lang->line("MSG_PREDICTION_NOT_FINALIZED");
                    $this->jsonReturn($ajax);
                    die();
                }
            }
            else
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_PREDICTION_NOT_FINALIZED");
                $this->jsonReturn($ajax);
                die();
            }
            if((!isset($this->permissions['edit'])||($this->permissions['edit']!=1)))
            {
                //$info=Query_helper::get_info($this->config->item('table_forward_hom'),'*',array('year0_id ='.$year0_id,'crop_id ='.$crop_id),1);

                if($info)
                {
                    if($info['status_prediction_assign']==$this->config->item('system_status_yes'))
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
            $data['year0_id']=$year0_id;
            $data['crop_id']=$crop_id;
            //divisions
            $data['areas']=Query_helper::get_info($this->config->item('ems_setup_location_divisions'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC'));

            $keys=',';
            $keys.="year0_id:'".$year0_id."',";
            $keys.="crop_id:'".$crop_id."',";
            $data['keys']=trim($keys,',');


            $data['title']="Assign DI 3Years Target For ".$crop['text'].'('.$data['years'][0]['text'].')';
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("hom_bud_di_target_prediction/add_edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url."/index/edit/".$year0_id.'/'.$crop_id);
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
        $year0_id=$this->input->post('year0_id');
        $crop_id=$this->input->post('crop_id');


        $results=Query_helper::get_info($this->config->item('table_hom_bud_hom_bt'),'*',array('year0_id ='.$year0_id));
        $incharge_budget_target=array();//hom budget and target
        foreach($results as $result)
        {
            $incharge_budget_target[$result['variety_id']]=$result;
        }

        $areas=Query_helper::get_info($this->config->item('ems_setup_location_divisions'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC'));

        //my be short less by crop_id
        $results=Query_helper::get_info($this->config->item('table_di_bud_di_bt'),'*',array('year0_id ='.$year0_id));
        $area_budget_target=array();//di budget and target
        foreach($results as $result)
        {
            $area_budget_target[$result['division_id']][$result['variety_id']]=$result;
        }

        $this->db->from($this->config->item('ems_setup_classification_varieties').' v');
        $this->db->select('v.id variety_id,v.name variety_name');
        $this->db->select('type.name type_name');
        $this->db->join($this->config->item('ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->where('v.whose','ARM');
        $this->db->where('v.status =',$this->config->item('system_status_active'));
        $this->db->where('type.crop_id',$crop_id);
        $this->db->order_by('type.ordering','ASC');
        $this->db->order_by('v.ordering','ASC');

        $results=$this->db->get()->result_array();

        $prev_type='';
        foreach($results as $index=>$result)
        {
            $item=array();
            if($index>0)
            {
                if($prev_type!=$result['type_name'])
                {
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
            $item['variety_id']=$result['variety_id'];
            $item['variety_name']=$result['variety_name'];
            for($i=0;$i<=$this->config->item('num_year_prediction');$i++)
            {
                if(isset($incharge_budget_target[$result['variety_id']]))
                {
                    $item['year'.$i.'_target_quantity_hom']=$incharge_budget_target[$result['variety_id']]['year'.$i.'_target_quantity'];
                }
                else
                {
                    $item['year'.$i.'_target_quantity_hom']=0;
                }
                foreach($areas as $area)
                {
                    if(isset($area_budget_target[$area['value']][$result['variety_id']]))
                    {
                        $item['year'.$i.'_target_quantity_'.$area['value']]=$area_budget_target[$area['value']][$result['variety_id']]['year'.$i.'_target_quantity'];
                    }
                    else
                    {
                        $item['year'.$i.'_target_quantity_'.$area['value']]=0;
                    }
                }
            }
            $items[]=$this->get_item_row($item,$areas);
        }
        $this->jsonReturn($items);
    }
    private function get_item_row($item,$areas)
    {
        $row=array();
        $row['type_name']=$item['type_name'];
        $row['variety_id']=$item['variety_id'];
        $row['variety_name']=$item['variety_name'];
        for($i=0;$i<=$this->config->item('num_year_prediction');$i++)
        {
            if($item['year'.$i.'_target_quantity_hom']!=0)
            {
                $row['year'.$i.'_target_quantity_hom']=$item['year'.$i.'_target_quantity_hom'];
            }
            else
            {
                $row['year'.$i.'_target_quantity_hom']='';
            }
            foreach($areas as $area)
            {
                if($item['year'.$i.'_target_quantity_'.$area['value']]!=0)
                {
                    $row['year'.$i.'_target_quantity_'.$area['value']]=$item['year'.$i.'_target_quantity_'.$area['value']];
                    if($i==0)
                    {
                        $row['year'.$i.'_target_quantity_'.$area['value'].'_editable']=false;
                    }
                    else
                    {
                        if(isset($this->permissions['edit'])&&($this->permissions['edit']==1))
                        {
                            $row['year'.$i.'_target_quantity_'.$area['value'].'_editable']=true;
                        }
                        else
                        {
                            $row['year'.$i.'_target_quantity_'.$area['value'].'_editable']=false;
                        }
                    }
                }
                else
                {
                    $row['year'.$i.'_target_quantity_'.$area['value']]='';
                    if($i==0)
                    {
                        $row['year'.$i.'_target_quantity_'.$area['value'].'_editable']=false;
                    }
                    else
                    {
                        $row['year'.$i.'_target_quantity_'.$area['value'].'_editable']=true;
                    }
                }
            }

        }
        return $row;
    }
    private function system_save()
    {
        $year0_id=$this->input->post('year0_id');
        $crop_id=$this->input->post('crop_id');
        $user = User_helper::get_user();
        $time=time();
        if((isset($this->permissions['edit'])&&($this->permissions['edit']==1))||(isset($this->permissions['add'])&&($this->permissions['add']==1)))
        {
            //only for HOM target finalized is in same table
            $info=Query_helper::get_info($this->config->item('table_forward_hom'),'*',array('year0_id ='.$year0_id,'crop_id ='.$crop_id),1);
            if($info)
            {
                if($info['status_target_finalize']!==$this->config->item('system_status_yes'))
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
                //$info=Query_helper::get_info($this->config->item('table_forward_hom'),'*',array('year0_id ='.$year0_id,'crop_id ='.$crop_id),1);

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
                $results=Query_helper::get_info($this->config->item('table_di_bud_di_bt'),'*',array('year0_id ='.$year0_id));
                $area_budget_target=array();//di bud and target
                foreach($results as $result)
                {
                    $area_budget_target[$result['division_id']][$result['variety_id']]=$result;
                }

                foreach($items as $division_id=>$item)
                {
                    foreach($item as $variety_id=> $year0_target_quantity)
                    {
                        $data=array();
                        if(isset($area_budget_target[$division_id][$variety_id]))
                        {
                            $data['year0_target_quantity']=$year0_target_quantity;
                            $data['user_updated'] = $user->user_id;
                            $data['date_updated'] = $time;
                            $data['user_targeted'] = $user->user_id;
                            $data['date_targeted'] = $time;
                            if($year0_target_quantity!=$area_budget_target[$division_id][$variety_id]['year0_target_quantity'])
                            {
                                Query_helper::update($this->config->item('table_di_bud_di_bt'),$data,array("id = ".$area_budget_target[$division_id][$variety_id]['id']));
                            }

                        }
                        else
                        {
                            $data['division_id'] = $division_id;
                            $data['year0_id'] = $year0_id;
                            $data['variety_id'] = $variety_id;
                            $data['year0_target_quantity']=$year0_target_quantity;
                            $data['user_created'] = $user->user_id;
                            $data['date_created'] = $time;
                            $data['user_targeted'] = $user->user_id;
                            $data['date_targeted'] = $time;
                            if(!empty($year0_target_quantity))
                            {
                                Query_helper::add($this->config->item('table_di_bud_di_bt'),$data);
                            }
                        }


                    }
                }
            }
            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {
                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                $this->system_edit($year0_id,$crop_id);
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
    private function system_details($year0_id,$crop_id)
    {
        if(isset($this->permissions['view'])&&($this->permissions['view']==1))
        {
            if(($this->input->post('id')))
            {
                $crop_id=$this->input->post('id');
            }
            $crop=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array('id ='.$crop_id),1);
            $data['years']=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"',' id >='.$year0_id),$this->config->item('num_year_prediction')+1,0,array('id ASC'));
            $data['year0_id']=$year0_id;
            $data['crop_id']=$crop_id;
            //divisions
            $data['areas']=Query_helper::get_info($this->config->item('ems_setup_location_divisions'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"'),0,0,array('ordering ASC'));


            $keys=',';
            $keys.="year0_id:'".$year0_id."',";
            $keys.="crop_id:'".$crop_id."',";
            $data['keys']=trim($keys,',');


            $data['title']="DI Target For ".$crop['text'].'('.$data['years'][0]['text'].')';
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("hom_bud_di_target/details",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url."/index/details/".$year0_id.'/'.$crop_id);
            $this->jsonReturn($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
        }
    }

    private function system_forward($year0_id,$crop_id)
    {
        $user = User_helper::get_user();
        $time=time();
        if(($this->input->post('id')))
        {
            $crop_id=$this->input->post('id');
        }
        //only for HOM target finalized is in same table
        $info=Query_helper::get_info($this->config->item('table_forward_hom'),'*',array('year0_id ='.$year0_id,'crop_id ='.$crop_id),1);
        if($info)
        {
            if($info['status_target_finalize']!==$this->config->item('system_status_yes'))
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

        $info=Query_helper::get_info($this->config->item('table_forward_hom'),'*',array('year0_id ='.$year0_id,'crop_id ='.$crop_id),1);
        $this->db->trans_start();
        if($info)
        {
            if($info['status_target_finalize']!==$this->config->item('system_status_yes'))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_TARGET_NOT_FINALIZED");
                $this->jsonReturn($ajax);
                die();
            }
            else if($info['status_assign']===$this->config->item('system_status_yes'))
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
                Query_helper::update($this->config->item('table_forward_hom'),$data,array("id = ".$info['id']));
            }
        }
        else
        {
            $data=array();
            $data['status_assign']=$this->config->item('system_status_yes');
            $data['year0_id']=$year0_id;
            $data['crop_id']=$crop_id;
            $data['user_created'] = $user->user_id;
            $data['date_created'] = $time;
            $data['user_assigned'] = $user->user_id;
            $data['date_assigned'] = $time;
            Query_helper::add($this->config->item('table_forward_hom'),$data);
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
