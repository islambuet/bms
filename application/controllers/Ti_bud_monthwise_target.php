<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ti_bud_monthwise_target extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Ti_bud_monthwise_target');
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
        $this->controller_url='ti_bud_monthwise_target';

    }
    //$id1--territory id
    //$id2--year0
    //$id3--type_id
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

            $data['title']="Assign Month wise Target";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("ti_bud_monthwise_target/search",$data,true));
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
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("ti_bud_monthwise_target/list",$data,true));
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
        $this->db->from($this->config->item('ems_setup_classification_crop_types').' type');
        $this->db->select('type.id,type.name type_name');
        $this->db->select('crop.name crop_name');

        $this->db->select('fzi.status_assign status_target_finalize');
        $this->db->select('fti.status_assign status_assign');

        $this->db->join($this->config->item('ems_setup_classification_crops').' crop','crop.id = type.crop_id','INNER');

        //$this->db->join($this->config->item('table_forward_di').' fdi','fdi.crop_id = crop.id and fdi.year0_id ='.$year0_id.' and division_id ='.$zone_info['division_id'],'LEFT');
        $this->db->join($this->config->item('table_forward_zi').' fzi','fzi.crop_id = crop.id and fzi.year0_id ='.$year0_id.' and zone_id ='.$territory_info['zone_id'],'LEFT');
        $this->db->join($this->config->item('table_forward_ti_month_target').' fti','fti.type_id = type.id and fti.year0_id ='.$year0_id.' and territory_id ='.$territory_id,'LEFT');

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

    private function system_edit($territory_id,$year0_id,$type_id)
    {
        $territory_info=Query_helper::get_info($this->config->item('ems_setup_location_territories'),'*',array('id ='.$territory_id),1);

        if((isset($this->permissions['edit'])&&($this->permissions['edit']==1))||(isset($this->permissions['add'])&&($this->permissions['add']==1)))
        {
            if(($this->input->post('id')))
            {
                $type_id=$this->input->post('id');
            }
            $type_info=Query_helper::get_info($this->config->item('ems_setup_classification_crop_types'),'*',array('id ='.$type_id),1);
            $info=Query_helper::get_info($this->config->item('table_forward_zi'),'*',array('year0_id ='.$year0_id,'crop_id ='.$type_info['crop_id'],'zone_id ='.$territory_info['zone_id']),1);
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
                $info=Query_helper::get_info($this->config->item('table_forward_ti_month_target'),'*',array('year0_id ='.$year0_id,'type_id ='.$type_id,'territory_id ='.$territory_id),1);

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

            $data['years']=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"',' id >='.$year0_id),$this->config->item('num_year_prediction')+1,0,array('id ASC'));
            $data['territory_id']=$territory_id;
            $data['year0_id']=$year0_id;
            $data['type_id']=$type_id;

            $keys=',';
            $keys.="territory_id:'".$territory_id."',";
            $keys.="year0_id:'".$year0_id."',";
            $keys.="type_id:'".$type_id."',";
            $data['keys']=trim($keys,',');


            $data['title']="Assign Month wise Target For ".$type_info['name'].'('.$data['years'][0]['text'].')';
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("ti_bud_monthwise_target/add_edit",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url."/index/edit/".$territory_id.'/'.$year0_id.'/'.$type_id);
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
        $type_id=$this->input->post('type_id');


        $pick_months=array();
        $result=Query_helper::get_info($this->config->item('ems_setup_classification_variety_time'),'*',array('crop_type_id ='.$type_id,'territory_id ='.$territory_id,'revision =1'),1);
        if($result)
        {
            for($i=1;$i<13;$i++)
            {
                if($result['month_'.$i]>0)
                {
                    $pick_months[]=$i;
                }
            }
        }

        //may be need to check if already forwarded
        //may be short out with crops
        $results=Query_helper::get_info($this->config->item('table_ti_bud_ti_bt'),'*',array('year0_id ='.$year0_id,'territory_id ='.$territory_id));
        $incharge_budget_target=array();//DI budget and target
        foreach($results as $result)
        {
            $incharge_budget_target[$result['variety_id']]=$result;
        }
        $results=Query_helper::get_info($this->config->item('table_ti_bud_month_bt'),'*',array('territory_id ='.$territory_id,'year0_id ='.$year0_id));
        $month_budget_target=array();//ti budget and target
        foreach($results as $result)
        {
            $month_budget_target[$result['variety_id']]=$result;
        }
        $this->db->from($this->config->item('ems_setup_classification_varieties').' v');
        $this->db->select('v.id,v.name');
        $this->db->where('v.whose','ARM');
        $this->db->where('v.status =',$this->config->item('system_status_active'));
        $this->db->where('v.crop_type_id',$type_id);
        $this->db->order_by('v.ordering','ASC');

        $results=$this->db->get()->result_array();

        $count=0;

        foreach($results as $index=>$result)
        {
            $item=array();
            $count++;
            $item['sl_no']=$count;
            $item['variety_id']=$result['id'];
            $item['variety_name']=$result['name'];
            if(isset($incharge_budget_target[$item['variety_id']]))
            {

                /*if(($incharge_budget_target[$item['variety_id']]['year0_budget_quantity']==null) ||($incharge_budget_target[$item['variety_id']]['year0_budget_quantity']==0))
                {
                    $item['year0_budget_quantity']='-';
                }
                else
                {
                    $item['year0_budget_quantity']=$incharge_budget_target[$item['variety_id']]['year0_budget_quantity'];
                }*/
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
            if(isset($month_budget_target[$item['variety_id']]))
            {
                for($i=1;$i<13;$i++)
                {
                    if($month_budget_target[$item['variety_id']]['target_quantity_'.$i])
                    {
                        $item['target_quantity_'.$i]=$month_budget_target[$item['variety_id']]['target_quantity_'.$i];
                    }
                    else
                    {
                        $item['target_quantity_'.$i]='';
                    }

                }
            }
            else
            {
                for($i=1;$i<13;$i++)
                {
                    $item['target_quantity_'.$i]='';
                }
            }
            for($i=1;$i<13;$i++)
            {
                $item['target_quantity_'.$i.'_editable']=true;
                if(in_array($i,$pick_months))
                {
                    $item['target_quantity_'.$i.'_pick_month']=true;
                }
                else
                {
                    $item['target_quantity_'.$i.'_pick_month']=false;
                }
            }
            $items[]=$item;
        }

        $this->jsonReturn($items);

    }
    private function system_save()
    {
        $territory_id=$this->input->post('territory_id');
        $year0_id=$this->input->post('year0_id');
        $type_id=$this->input->post('type_id');
        $territory_info=Query_helper::get_info($this->config->item('ems_setup_location_territories'),'*',array('id ='.$territory_id),1);
        $user = User_helper::get_user();
        $time=time();
        if((isset($this->permissions['edit'])&&($this->permissions['edit']==1))||(isset($this->permissions['add'])&&($this->permissions['add']==1)))
        {
            $type_info=Query_helper::get_info($this->config->item('ems_setup_classification_crop_types'),'*',array('id ='.$type_id),1);
            $info=Query_helper::get_info($this->config->item('table_forward_zi'),'*',array('year0_id ='.$year0_id,'crop_id ='.$type_info['crop_id'],'zone_id ='.$territory_info['zone_id']),1);
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
                $info=Query_helper::get_info($this->config->item('table_forward_ti_month_target'),'*',array('year0_id ='.$year0_id,'type_id ='.$type_id,'territory_id ='.$territory_id),1);

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
                $results=Query_helper::get_info($this->config->item('table_ti_bud_month_bt'),'*',array('territory_id ='.$territory_id,'year0_id ='.$year0_id));
                $month_budget_target=array();//ti budget and target
                foreach($results as $result)
                {
                    $month_budget_target[$result['variety_id']]=$result;
                }
                foreach($items as $variety_id=>$months)
                {
                    $data=array();

                    foreach($months as $month_id=> $quantity)
                    {
                        if(strlen(trim($quantity))==0)
                        {
                            $data['target_quantity_'.$month_id]=0;
                        }
                        else
                        {
                            $data['target_quantity_'.$month_id]=$quantity;
                        }
                    }
                    if(isset($month_budget_target[$variety_id]))
                    {
                        $data['user_updated'] = $user->user_id;
                        $data['date_updated'] = $time;
                        $data['user_targeted'] = $user->user_id;
                        $data['date_targeted'] = $time;
                        Query_helper::update($this->config->item('table_ti_bud_month_bt'),$data,array("id = ".$month_budget_target[$variety_id]['id']));
                    }
                    else
                    {
                        $data['territory_id'] = $territory_id;
                        $data['year0_id'] = $year0_id;
                        $data['variety_id'] = $variety_id;
                        $data['user_created'] = $user->user_id;
                        $data['date_created'] = $time;
                        $data['user_targeted'] = $user->user_id;
                        $data['date_targeted'] = $time;
                        Query_helper::add($this->config->item('table_ti_bud_month_bt'),$data);
                    }

                }
            }
            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {
                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                $this->system_edit($territory_id,$year0_id,$type_id);
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
    private function system_details($territory_id,$year0_id,$type_id)
    {
        $territory_info=Query_helper::get_info($this->config->item('ems_setup_location_territories'),'*',array('id ='.$territory_id),1);
        if(isset($this->permissions['view'])&&($this->permissions['view']==1))
        {
            if(($this->input->post('id')))
            {
                $type_id=$this->input->post('id');
            }
            $type_info=Query_helper::get_info($this->config->item('ems_setup_classification_crop_types'),'*',array('id ='.$type_id),1);
            $data['years']=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"',' id >='.$year0_id),$this->config->item('num_year_prediction')+1,0,array('id ASC'));
            $data['territory_id']=$territory_id;
            $data['year0_id']=$year0_id;
            $data['type_id']=$type_id;

            $keys=',';
            $keys.="territory_id:'".$territory_id."',";
            $keys.="year0_id:'".$year0_id."',";
            $keys.="type_id:'".$type_id."',";
            $data['keys']=trim($keys,',');


            $data['title']="Month Wise Target For ".$type_info['name'].'('.$data['years'][0]['text'].')';
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("ti_bud_monthwise_target/details",$data,true));
            if($this->message)
            {
                $ajax['system_message']=$this->message;
            }
            $ajax['system_page_url']=site_url($this->controller_url."/index/details/".$territory_id.'/'.$year0_id.'/'.$type_id);
            $this->jsonReturn($ajax);
        }
        else
        {
            $ajax['status']=false;
            $ajax['system_message']=$this->lang->line("YOU_DONT_HAVE_ACCESS");
            $this->jsonReturn($ajax);
        }
    }

    private function system_forward($territory_id,$year0_id,$type_id)
    {
        $territory_info=Query_helper::get_info($this->config->item('ems_setup_location_territories'),'*',array('id ='.$territory_id),1);
        $user = User_helper::get_user();
        $time=time();
        if(($this->input->post('id')))
        {
            $type_id=$this->input->post('id');
        }
        $type_info=Query_helper::get_info($this->config->item('ems_setup_classification_crop_types'),'*',array('id ='.$type_id),1);
        $info=Query_helper::get_info($this->config->item('table_forward_zi'),'*',array('year0_id ='.$year0_id,'crop_id ='.$type_info['crop_id'],'zone_id ='.$territory_info['zone_id']),1);
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
        $info=Query_helper::get_info($this->config->item('table_forward_ti_month_target'),'*',array('year0_id ='.$year0_id,'type_id ='.$type_id,'territory_id ='.$territory_id),1);
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
                Query_helper::update($this->config->item('table_forward_ti_month_target'),$data,array("id = ".$info['id']));
            }
        }
        else
        {
            $data=array();
            $data['status_assign']=$this->config->item('system_status_yes');
            $data['territory_id']=$territory_id;
            $data['year0_id']=$year0_id;
            $data['type_id']=$type_id;
            $data['user_created'] = $user->user_id;
            $data['date_created'] = $time;
            $data['user_assigned'] = $user->user_id;
            $data['date_assigned'] = $time;
            Query_helper::add($this->config->item('table_forward_ti_month_target'),$data);
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
