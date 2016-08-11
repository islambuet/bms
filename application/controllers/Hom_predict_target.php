<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Hom_predict_target extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public $locations;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Hom_predict_target');
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
        $this->controller_url='hom_predict_target';

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
        elseif($action=="get_detail_items")
        {
            $this->system_get_edit_items('details');
        }
        elseif($action=="save")
        {
            $this->system_save();
        }
        /*elseif($action=="details")
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
            $data['budget']['division_id']=$this->locations['division_id'];
            $data['budget']['zone_id']=$this->locations['zone_id'];
            $data['title']="Predict 3Years target";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("hom_predict_target/search",$data,true));
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
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("hom_predict_target/list",$data,true));
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
        $this->db->select('fhom.status_target_finalize');
        $this->db->select('fhom.status_prediction_finalize');
        $this->db->join($this->config->item('table_forward_hom').' fhom','fhom.crop_id = crop.id and year0_id ='.$year0_id,'LEFT');
        //$this->db->where('d.territory_id',$territory_id);

        $this->db->order_by('crop.ordering','ASC');
        $this->db->where('crop.status',$this->config->item('system_status_active'));
        $items=$this->db->get()->result_array();
        foreach($items as &$item)
        {
            if(!$item['status_target_finalize'])
            {
                $item['status_target_finalize']=$this->config->item('system_status_no');
            }
            if(!$item['status_prediction_finalize'])
            {
                $item['status_prediction_finalize']=$this->config->item('system_status_no');
            }
        }
        $this->jsonReturn($items);
    }
    //if edit permission he can edit whenever he want
    //if add permission he can edit until forward/finalize

    private function system_edit($year0_id,$crop_id)
    {
        if((isset($this->permissions['edit'])&&($this->permissions['edit']==1))||(isset($this->permissions['add'])&&($this->permissions['add']==1)))
        {
            if(($this->input->post('id')))
            {
                $crop_id=$this->input->post('id');
            }
            $info=Query_helper::get_info($this->config->item('table_forward_hom'),'*',array('year0_id ='.$year0_id,'crop_id ='.$crop_id),1);
            if(!$info)
            {
                /*$ajax['status']=false;
                $ajax['system_message']="Please finalize this year's target first";
                $this->jsonReturn($ajax);
                die();*/
            }
            else
            {
                /*if($info['status_target_finalize']!=$this->config->item('system_status_yes'))
                {
                    $ajax['status']=false;
                    $ajax['system_message']="Please finalize this year's target first";
                    $this->jsonReturn($ajax);
                    die();
                }*/

            }
            if((!isset($this->permissions['edit'])||($this->permissions['edit']!=1)))
            {
                if($info['status_prediction_finalize']===$this->config->item('system_status_yes'))
                {
                    $ajax['status']=false;
                    $ajax['system_message']=$this->lang->line("MSG_ALREADY_PREDICTION_FINALIZED");
                    $this->jsonReturn($ajax);
                    die();
                }
            }
            $crop=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array('id ='.$crop_id),1);
            $data['years']=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"',' id >='.$year0_id),$this->config->item('num_year_prediction')+1,0,array('id ASC'));
            $data['year0_id']=$year0_id;
            $data['crop_id']=$crop_id;
            $keys=',';
            $keys.="year0_id:'".$year0_id."',";
            $keys.="crop_id:'".$crop_id."',";
            $data['keys']=trim($keys,',');


            $data['title']="Predict 3Years target For ".$crop['text'].'('.$data['years'][0]['text'].')';
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("hom_predict_target/add_edit",$data,true));
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
    private function system_get_edit_items($task_purpose='edit')
    {
        $items=array();
        $year0_id=$this->input->post('year0_id');
        $crop_id=$this->input->post('crop_id');
        $year_2_id=0;
        $year_1_id=0;

        $prev_years=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"',' id <'.$year0_id),2,0,array('id DESC'));
        if(sizeof($prev_years)>0)
        {
            $year_1_id=$prev_years[0]['value'];
        }
        if(sizeof($prev_years)>1)
        {
            $year_2_id=$prev_years[1]['value'];
        }


        $this->db->from($this->config->item('table_hom_bud_hom_bt').' bt');
        $this->db->select('bt.*');
        $this->db->select('v.name variety_name');
        $this->db->select('type.name type_name');
        $this->db->join($this->config->item('ems_setup_classification_varieties').' v','v.id = bt.variety_id','INNER');
        $this->db->join($this->config->item('ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->where('bt.year0_id',$year0_id);
        $this->db->where('type.crop_id',$crop_id);
        $this->db->order_by('type.ordering','ASC');
        $this->db->order_by('v.ordering','ASC');
        $results=$this->db->get()->result_array();

        $crop_row=$type_row=array();
        $crop_row['crop_name']=$type_row['crop_name']='';
        $crop_row['type_name']=$type_row['type_name']='';
        $crop_row['variety_name']=$type_row['variety_name']='';
        $crop_row['variety_id']=$type_row['variety_id']='';
        $type_row['variety_name']='Total Type';
        $crop_row['type_name']='Total Crop';
        $crop_row['variety_id']=$type_row['variety_id']='';
        $crop_row['year0_budget_quantity']=$type_row['year0_budget_quantity']=0;
        $crop_row['year0_target_quantity']=$type_row['year0_target_quantity']=0;
        $crop_row['year1_target_quantity']=$type_row['year1_target_quantity']=0;
        $crop_row['year2_target_quantity']=$type_row['year2_target_quantity']=0;
        $crop_row['year3_target_quantity']=$type_row['year3_target_quantity']=0;
        $prev_type='';
        foreach($results as $index=>$result)
        {
            $item=array();
            if($index>0)
            {
                if($prev_type!=$result['type_name'])
                {

                    $items[]=$this->get_item_row($type_row);
                    $type_row['year0_budget_quantity']=0;
                    $type_row['year0_target_quantity']=0;
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
            $item['year0_budget_quantity']=$result['year0_budget_quantity'];
            if($item['year0_budget_quantity']>0)
            {
                $type_row['year0_budget_quantity']+=$item['year0_budget_quantity'];
                $crop_row['year0_budget_quantity']+=$item['year0_budget_quantity'];
            }
            $item['year0_target_quantity']=$result['year0_target_quantity'];
            if($item['year0_target_quantity']>0)
            {
                $type_row['year0_target_quantity']+=$item['year0_target_quantity'];
                $crop_row['year0_target_quantity']+=$item['year0_target_quantity'];
            }
            $item['year1_target_quantity']=$result['year1_target_quantity'];
            $item['year2_target_quantity']=$result['year2_target_quantity'];
            $item['year3_target_quantity']=$result['year3_target_quantity'];
            $items[]=$this->get_item_row($item);
        }
        $items[]=$this->get_item_row($type_row);
        $items[]=$this->get_item_row($crop_row);

        /*$item=array();
        $item['type_name']='test';
        $item['variety_id']='1';
        $item['variety_name']='test';
        $item['year0_budget_quantity']='100';
        $item['year0_target_quantity']='90';
        $item['year1_2_target_quantity']='40';
        $item['year1_1_target_quantity']='90';
        $item['year1_target_quantity']='100';
        $item['year1_target_quantity_editable']=true;
        $item['year2_1_target_quantity']='90';
        $item['year2_target_quantity']='150';
        $item['year2_target_quantity_editable']=true;
        $item['year3_target_quantity']='300';
        $item['year3_target_quantity_editable']='1';
        $items[]=$item;*/
        /*$results=Query_helper::get_info($this->config->item('table_hom_bud_hom_bt'),'*',array('year0_id ='.$year0_id));
        $budgets=array();//hom budget and target
        foreach($results as $result)
        {
            $budgets[$result['variety_id']]=$result;
        }
        $results=Query_helper::get_info($this->config->item('table_hom_bud_variance'),'*',array('year0_id ='.$year0_id));//can filter by crop id to increase runtime
        $final_variances=array();//hom variance
        foreach($results as $result)
        {
            $final_variances[$result['variety_id']]=$result;
        }
        $results=Query_helper::get_info($this->config->item('table_variety_min_stock'),'*',array('revision =1'));//only for this crop could be done
        $min_stocks=array();//min stock
        foreach($results as $result)
        {
            $min_stocks[$result['variety_id']]=$result['quantity'];
        }

        $results=Query_helper::get_info($this->config->item('table_mgt_purchase_budget'),'*',array('year0_id ='.$year0_id));//can filter by crop id to increase runtime
        $purchases=array();//mgt purchase
        foreach($results as $result)
        {
            $purchases[$result['variety_id']]=$result;
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


        $total_types=0;
        $total_crop=0;
        $count=0;

        $prev_type='';
        foreach($results as $index=>$result)
        {
            $item=array();
            if($index>0)
            {
                if($prev_type!=$result['type_name'])
                {
                    $total_item=array();
                    $total_item['sl_no']='';
                    $total_item['type_name']='';
                    $total_item['variety_name']='Total Type';
                    $total_item['variety_id']='';
                    if($total_types>0)
                    {
                        $total_item['year0_budget_quantity']=$total_types;
                    }
                    else
                    {
                        $total_item['year0_budget_quantity']='';
                    }

                    $total_item['year0_purchase_quantity']='';
                    $total_item['pq_fv']='';
                    $total_item['pq_fv_min']='';
                    $total_item['year0_target_quantity']='';
                    $total_item['year0_target_quantity_editable']=false;
                    $items[]=$total_item;
                    $total_types=0;
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
            if((isset($budgets[$result['id']]['year0_budget_quantity']))&&(($budgets[$result['id']]['year0_budget_quantity'])>0))
            {
                $item['year0_budget_quantity']=$budgets[$result['id']]['year0_budget_quantity'];
                $total_types+=$budgets[$result['id']]['year0_budget_quantity'];
                $total_crop+=$budgets[$result['id']]['year0_budget_quantity'];
            }
            else
            {
                $item['year0_budget_quantity']='-';
            }
            $item['year0_variance_quantity']=0;
            if(isset($final_variances[$item['variety_id']]))
            {
                $item['year0_variance_quantity']=$final_variances[$item['variety_id']]['year0_variance_quantity'];
            }
            $item['min_stock']=0;
            if(isset($min_stocks[$item['variety_id']]))
            {
                $item['min_stock']=$min_stocks[$item['variety_id']];
            }

            $item['quantity_needed']=$item['year0_budget_quantity']-$item['year0_variance_quantity'];
            $item['year0_purchase_quantity']=0;
            if(isset($purchases[$item['variety_id']]))
            {
                $item['year0_purchase_quantity']=$purchases[$item['variety_id']]['quantity_total'];
            }
            $item['pq_fv']=$item['year0_purchase_quantity']+$item['year0_variance_quantity'];
            $item['pq_fv_min']=$item['year0_purchase_quantity']+$item['year0_variance_quantity']+$item['min_stock'];
            if($item['year0_variance_quantity']==0)
            {
                $item['year0_variance_quantity']='-';
            }
            if($item['min_stock']==0)
            {
                $item['min_stock']='-';
            }
            if($item['quantity_needed']<=0)
            {
                $item['quantity_needed']='-';
            }
            if($item['year0_purchase_quantity']==0)
            {
                $item['year0_purchase_quantity']='-';
            }
            if($item['pq_fv']==0)
            {
                $item['pq_fv']='-';
            }
            if($item['pq_fv_min']==0)
            {
                $item['pq_fv_min']='-';
            }
            if(isset($budgets[$item['variety_id']]))
            {
                if($budgets[$item['variety_id']]['year0_target_quantity']!=0)
                {
                    $item['year0_target_quantity']=$budgets[$item['variety_id']]['year0_target_quantity'];
                }
                else
                {
                    if($task_purpose=='edit')
                    {
                        $item['year0_target_quantity']='';
                    }
                    else
                    {
                        $item['year0_target_quantity']='-';
                    }
                }

            }
            else
            {
                if($task_purpose=='edit')
                {
                    $item['year0_target_quantity']='';
                }
                else
                {
                    $item['year0_target_quantity']='-';
                }
            }



            $item['year0_target_quantity_editable']=true;
            $items[]=$item;

        }
        $total_item=array();
        $total_item['sl_no']='';
        $total_item['type_name']='';
        $total_item['variety_name']='Total Type';
        $total_item['variety_id']='';
        if($total_types>0)
        {
            $total_item['year0_budget_quantity']=$total_types;
        }
        else
        {
            $total_item['year0_budget_quantity']='';
        }
        $total_item['year0_purchase_quantity']='';
        $total_item['pq_fv']='';
        $total_item['pq_fv_min']='';
        $total_item['year0_target_quantity']='';
        $total_item['year0_target_quantity_editable']=false;
        $items[]=$total_item;
        $total_item=array();

        $total_item['sl_no']='';
        $total_item['type_name']='Total Crop';
        $total_item['variety_name']='';
        $total_item['variety_id']='';
        if($total_crop>0)
        {
            $total_item['year0_budget_quantity']=$total_crop;
        }
        else
        {
            $total_item['year0_budget_quantity']='';
        }
        $total_item['year0_purchase_quantity']='';
        $total_item['pq_fv']='';
        $total_item['pq_fv_min']='';
        $total_item['year0_target_quantity']='';
        $total_item['year0_target_quantity_editable']=false;
        $items[]=$total_item;
        */
        $this->jsonReturn($items);

    }
    private function get_item_row($item)
    {
        $row=array();
        $row['type_name']=$item['type_name'];
        $row['variety_id']=$item['variety_id'];
        $row['variety_name']=$item['variety_name'];
        if($item['year0_budget_quantity']!=0)
        {
            $row['year0_budget_quantity']=$item['year0_budget_quantity'];
        }
        else
        {
            $row['year0_budget_quantity']='';
        }
        if($item['year0_target_quantity']!=0)
        {
            $row['year0_target_quantity']=$item['year0_target_quantity'];
        }
        else
        {
            $row['year0_target_quantity']='';
        }
        for($i=1;$i<4;$i++)
        {
            if($item['year'.$i.'_target_quantity']!=0)
            {
                $row['year'.$i.'_target_quantity']=$item['year'.$i.'_target_quantity'];
                if(isset($this->permissions['edit'])&&($this->permissions['edit']==1))
                {
                    $row['year'.$i.'_target_quantity_editable']=true;
                }
                else
                {
                    $row['year'.$i.'_target_quantity_editable']=false;
                }
            }
            else
            {
                $row['year'.$i.'_target_quantity']='';
                if($row['variety_name']=='Total Type' || $row['type_name']=='Total Crop')
                {
                    $row['year'.$i.'_target_quantity_editable']=false;
                }
                else
                {
                    $row['year'.$i.'_target_quantity_editable']=true;
                }

            }
        }



        return $row;
    }

    private function system_save()
    {
        $year0_id=$this->input->post('year0_id');
        $crop_id=$this->input->post('crop_id');
        $info=Query_helper::get_info($this->config->item('table_forward_hom'),'*',array('year0_id ='.$year0_id,'crop_id ='.$crop_id),1);
        if(!$info)
        {
            /*$ajax['status']=false;
            $ajax['system_message']="Please finalize this year's target first";
            $this->jsonReturn($ajax);
            die();*/
        }
        else
        {
            /*if($info['status_target_finalize']!=$this->config->item('system_status_yes'))
            {
                $ajax['status']=false;
                $ajax['system_message']="Please finalize this year's target first";
                $this->jsonReturn($ajax);
                die();
            }*/

        }
        if((!isset($this->permissions['edit'])||($this->permissions['edit']!=1)))
        {
            if($info['status_prediction_finalize']===$this->config->item('system_status_yes'))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_ALREADY_PREDICTION_FINALIZED");
                $this->jsonReturn($ajax);
                die();
            }
        }
        $user = User_helper::get_user();
        $time=time();
        //check forward status if has only add permission but not edit permission
        $items=$this->input->post('items');
        
        $this->db->trans_start();
        if(sizeof($items)>0)
        {
            $results=Query_helper::get_info($this->config->item('table_hom_bud_hom_bt'),'*',array('year0_id ='.$year0_id));
            $old_items=array();//hom budget
            foreach($results as $result)
            {
                $old_items[$result['variety_id']]=$result;
            }

            foreach($items as $variety_id=>$data)
            {
                if(isset($old_items[$variety_id]))
                {
                    $data['user_updated'] = $user->user_id;
                    $data['date_updated'] = $time;
                    $data['user_predicted'] = $user->user_id;
                    $data['date_predicted'] = $time;
                    Query_helper::update($this->config->item('table_hom_bud_hom_bt'),$data,array("id = ".$old_items[$variety_id]['id']));
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


            $keys=',';
            $keys.="year0_id:'".$year0_id."',";
            $keys.="crop_id:'".$crop_id."',";
            $data['keys']=trim($keys,',');


            $data['title']="Variance Finalize For ".$crop['text'].'('.$data['years'][0]['text'].')';
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("hom_bud_target_finalize/details",$data,true));
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
        $info=Query_helper::get_info($this->config->item('table_forward_hom'),'*',array('year0_id ='.$year0_id,'crop_id ='.$crop_id),1);
        $this->db->trans_start();
        if($info)
        {
            if($info['status_target_finalize']===$this->config->item('system_status_yes'))
            {
                $ajax['status']=false;
                $ajax['system_message']=$this->lang->line("MSG_ALREADY_FORWARDED");
                $this->jsonReturn($ajax);
                die();
            }
            else
            {
                $data=array();
                $data['status_target_finalize']=$this->config->item('system_status_yes');
                $data['user_updated'] = $user->user_id;
                $data['date_updated'] = $time;
                $data['user_target_finalized'] = $user->user_id;
                $data['date_target_finalized'] = $time;
                Query_helper::update($this->config->item('table_forward_hom'),$data,array("id = ".$info['id']));
            }
        }
        else
        {
            $data=array();
            $data['status_target_finalize']=$this->config->item('system_status_yes');
            $data['year0_id']=$year0_id;
            $data['crop_id']=$crop_id;
            $data['user_created'] = $user->user_id;
            $data['date_created'] = $time;
            $data['user_target_finalized'] = $user->user_id;
            $data['date_target_finalized'] = $time;
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
