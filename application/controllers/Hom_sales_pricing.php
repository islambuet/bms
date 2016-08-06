<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Hom_sales_pricing extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Hom_sales_pricing');
        $this->controller_url='hom_sales_pricing';

    }

    public function index($action="search",$id1=0,$id2=0,$id3=0)
    {
        if($action=="search")
        {
            $this->system_search();
        }
        elseif($action=="edit")
        {
            $this->system_edit();
        }
        elseif($action=="get_edit_items")
        {
            $this->system_get_edit_items();
        }
        elseif($action=="save")
        {
            $this->system_save();
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
            $data['crops']=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array(),0,0,array('ordering ASC'));
            $data['title']="Pricing Marketing Search";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("hom_sales_pricing/search",$data,true));
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

    private function system_edit()
    {
        if(isset($this->permissions['edit'])&&($this->permissions['edit']==1))
        {
            $year0_id=$this->input->post('year0_id');
            $crop_id=$this->input->post('crop_id');

            $crop=Query_helper::get_info($this->config->item('ems_setup_classification_crops'),array('id value','name text'),array('id ='.$crop_id),1);
            $data['years']=Query_helper::get_info($this->config->item('ems_basic_setup_fiscal_year'),array('id value','name text'),array('status ="'.$this->config->item('system_status_active').'"',' id >='.$year0_id),$this->config->item('num_year_prediction')+1,0,array('id ASC'));
            $data['year0_id']=$year0_id;
            $data['crop_id']=$crop_id;
            $keys=',';
            $keys.="year0_id:'".$year0_id."',";
            $keys.="crop_id:'".$crop_id."',";
            $data['keys']=trim($keys,',');


            $data['title']="Pricing Marketing For ".$crop['text'].'('.$data['years'][0]['text'].')';

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("hom_sales_pricing/add_edit",$data,true));
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
    private function system_get_edit_items()
    {
        $items=array();
        $year0_id=$this->input->post('year0_id');
        $crop_id=$this->input->post('crop_id');

        $this->db->from($this->config->item('table_mgt_sales_pricing').' sp');
        $this->db->select('sp.*');
        $this->db->select('v.name variety_name');
        $this->db->select('type.name type_name');
        $this->db->join($this->config->item('ems_setup_classification_varieties').' v','v.id = sp.variety_id','INNER');
        $this->db->join($this->config->item('ems_setup_classification_crop_types').' type','type.id = v.crop_type_id','INNER');
        $this->db->where('v.whose','ARM');
        $this->db->where('v.status =',$this->config->item('system_status_active'));
        $this->db->where('type.crop_id',$crop_id);
        $this->db->where('sp.year0_id',$year0_id);
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
            $item['tp_last_year']=0;
            $item['tp_management']=$result['tp_management'];
            if($result['user_created_hom']>0)
            {
                $item['tp_hom']=$result['tp_hom'];
                $item['commission_hom']=$result['commission_hom'];
                $item['incentive_hom']=$result['incentive_hom'];
            }
            else
            {
                $item['tp_hom']=0;
                $item['commission_hom']=$result['commission_management'];
                $item['incentive_hom']=$result['incentive_management'];
            }
            $item['sales_commission']=$item['tp_hom']*$item['commission_hom']/100;
            $item['incentive']=$item['tp_hom']*$item['incentive_hom']/100;
            $item['net_price']=$item['tp_hom']-$item['sales_commission']-$item['incentive'];

            $items[]=$this->get_report_row($item);

        }

        $this->jsonReturn($items);

    }
    private function get_report_row($item)
    {
        $row=array();
        $row['type_name']=$item['type_name'];
        $row['variety_id']=$item['variety_id'];
        $row['variety_name']=$item['variety_name'];
        if($item['tp_last_year']!=0)
        {
            $row['tp_last_year']=$item['tp_last_year'];
        }
        else
        {
            $row['tp_last_year']='';
        }
        if($item['tp_management']!=0)
        {
            $row['tp_management']=number_format($item['tp_management'],2);
        }
        else
        {
            $row['tp_management']='';
        }
        if($item['tp_hom']!=0)
        {
            $row['tp_hom']=$item['tp_hom'];
        }
        else
        {
            $row['tp_hom']='';
        }
        if($item['commission_hom']!=0)
        {
            $row['commission_hom']=$item['commission_hom'];
        }
        else
        {
            $row['commission_hom']='';
        }
        if($item['sales_commission']!=0)
        {
            $row['sales_commission']=number_format($item['sales_commission'],2);
        }
        else
        {
            $row['sales_commission']='';
        }
        if($item['incentive_hom']!=0)
        {
            $row['incentive_hom']=$item['incentive_hom'];
        }
        else
        {
            $row['incentive_hom']='';
        }
        if($item['incentive']!=0)
        {
            $row['incentive']=number_format($item['incentive'],2);
        }
        else
        {
            $row['incentive']='';
        }
        if($item['net_price']!=0)
        {
            $row['net_price']=number_format($item['net_price'],2);
        }
        else
        {
            $row['net_price']='';
        }
        return $row;

    }
    private function system_save()
    {
        if(isset($this->permissions['edit'])&&($this->permissions['edit']==1))
        {
            $year0_id=$this->input->post('year0_id');
            $crop_id=$this->input->post('crop_id');
            $user = User_helper::get_user();
            $time=time();

            $items=$this->input->post('items');
            $this->db->trans_start();
            if(sizeof($items)>0)
            {
                $sales_pricing=array();
                $results=Query_helper::get_info($this->config->item('table_mgt_sales_pricing'),'*',array('year0_id ='.$year0_id));
                foreach($results as $result)
                {
                    $sales_pricing[$result['variety_id']]=$result;
                }

                foreach($items as $variety_id=>$data)
                {
                    if(strlen(trim($data['tp_hom']))==0)
                    {
                        $data['tp_hom']=0;
                    }
                    if(strlen(trim($data['commission_hom']))==0)
                    {
                        $data['commission_hom']=0;
                    }
                    if(isset($sales_pricing[$variety_id]))
                    {
                        $data['incentive_hom']=$sales_pricing[$variety_id]['incentive_management'];
                        $data['user_updated'] = $user->user_id;
                        $data['date_updated'] = $time;
                        if($sales_pricing[$variety_id]['user_created_hom']>0)
                        {
                            $data['user_updated_hom'] = $user->user_id;
                            $data['date_updated_hom'] = $time;
                        }
                        else
                        {
                            $data['user_created_hom'] = $user->user_id;
                            $data['date_created_hom'] = $time;
                        }

                        Query_helper::update($this->config->item('table_mgt_sales_pricing'),$data,array("id = ".$sales_pricing[$variety_id]['id']));
                    }
                }
            }

            $this->db->trans_complete();   //DB Transaction Handle END
            if ($this->db->trans_status() === TRUE)
            {
                $this->message=$this->lang->line("MSG_SAVED_SUCCESS");
                $this->system_search();
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

}
