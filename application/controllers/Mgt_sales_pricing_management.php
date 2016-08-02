<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mgt_sales_pricing_management extends Root_Controller
{
    private  $message;
    public $permissions;
    public $controller_url;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
        $this->permissions=User_helper::get_permission('Mgt_sales_pricing_management');
        $this->controller_url='mgt_sales_pricing_management';

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
            $data['title']="Pricing Management Search";
            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_content","html"=>$this->load->view("mgt_sales_pricing_management/search",$data,true));
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


            $data['title']="Pricing Management For ".$crop['text'].'('.$data['years'][0]['text'].')';

            $ajax['status']=true;
            $ajax['system_content'][]=array("id"=>"#system_report_container","html"=>$this->load->view("mgt_sales_pricing_management/add_edit",$data,true));
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
            $item['variety_name']=$result['variety_name'];
//            { name: 'hom_target', type: 'string' },
//            { name: 'tp_last_year', type: 'string' },
//            { name: 'tp_automated', type: 'string' },
//            { name: 'tp_mgt', type: 'string' },
//            { name: 'sales_commission_percentage', type: 'string' },
//            { name: 'sales_commission', type: 'string' },
//            { name: 'incentive_percentage', type: 'string' },
//            { name: 'incentive', type: 'string' },
//            { name: 'net_price', type: 'string' },
//            { name: 'cogs', type: 'string' },
//            { name: 'general', type: 'string' },
//            { name: 'marketing', type: 'string' },
//            { name: 'finance', type: 'string' },
//            { name: 'profit', type: 'string' },
//            { name: 'total_net_price', type: 'string' },
//            { name: 'total_profit', type: 'string' },
//            { name: 'profit_percentage', type: 'string' }
            $item['hom_target']=0;
            $item['tp_last_year']=0;
            $item['tp_automated']=0;
            $item['tp_mgt']=0;
            $item['sales_commission_percentage']=0;
            $item['sales_commission']=0;
            $item['incentive_percentage']=0;
            $item['incentive']=0;
            $item['net_price']=0;
            $item['cogs']=0;
            $item['general']=0;
            $item['marketing']=0;
            $item['finance']=0;
            $item['profit']=0;
            $item['total_net_price']=0;
            $item['total_profit']=0;
            $item['profit_percentage']=0;

            $items[]=$this->get_report_row($item);

        }

        $this->jsonReturn($items);

    }
    private function get_report_row($item)
    {
        $row=array();
        $row['type_name']=$item['type_name'];
        $row['variety_name']=$item['variety_name'];
        $row['variety_name']=$item['variety_name'];
        if($item['hom_target']!=0)
        {
            $row['hom_target']=$item['hom_target'];
        }
        else
        {
            $row['hom_target']='';
        }
        $row['tp_last_year']=$item['tp_last_year'];
        $row['tp_automated']=$item['tp_automated'];
        $row['tp_mgt']=$item['tp_mgt'];
        $row['sales_commission_percentage']=$item['sales_commission_percentage'];
        $row['sales_commission']=$item['sales_commission'];
        $row['incentive_percentage']=$item['incentive_percentage'];
        $row['incentive']=$item['incentive'];
        $row['net_price']=$item['net_price'];
        $row['profit']=$item['profit'];
        $row['total_net_price']=$item['total_net_price'];
        $row['total_profit']=$item['total_profit'];
        $row['profit_percentage']=$item['profit_percentage'];
        if($item['cogs']!=0)
        {
            $row['cogs']=number_format($item['cogs'],2);
        }
        else
        {
            $row['cogs']='';
        }
        if($item['general']!=0)
        {
            $row['general']=number_format($item['general'],2);
        }
        else
        {
            $row['general']='';
        }
        if($item['marketing']!=0)
        {
            $row['marketing']=number_format($item['marketing'],2);
        }
        else
        {
            $row['marketing']='';
        }
        if($item['finance']!=0)
        {
            $row['finance']=number_format($item['finance'],2);
        }
        else
        {
            $row['finance']='';
        }
        /*if($item['profit']!=0)
        {
            $row['profit']=number_format($item['profit'],2);
        }
        else
        {
            $row['profit']='';
        }
        if($item['net_price']!=0)
        {
            $row['net_price']=number_format($item['net_price'],2);
        }
        else
        {
            $row['net_price']='';
        }
        if($item['sales_commission']!=0)
        {
            $row['sales_commission']=number_format($item['sales_commission'],2);
        }
        else
        {
            $row['sales_commission']='';
        }
        if($item['incentive']!=0)
        {
            $row['incentive']=number_format($item['incentive'],2);
        }
        else
        {
            $row['incentive']='';
        }
        if($item['trade_price']!=0)
        {
            $row['trade_price']=number_format($item['trade_price'],2);
        }
        else
        {
            $row['trade_price']='';
        }
        if($item['total_profit']!=0)
        {
            $row['total_profit']=number_format($item['total_profit'],2);
        }
        else
        {
            $row['total_profit']='';
        }
        if($item['total_net_price']!=0)
        {
            $row['total_net_price']=number_format($item['total_net_price'],2);
        }
        else
        {
            $row['total_net_price']='';
        }

        if($item['profit_percentage']!=0)
        {
            $row['profit_percentage']=number_format($item['profit_percentage'],2);
        }
        else
        {
            $row['profit_percentage']='';
        }*/

        return $row;

    }
    private function system_save()
    {
        $year0_id=$this->input->post('year0_id');
        $crop_id=$this->input->post('crop_id');
        if((!isset($this->permissions['edit'])||($this->permissions['edit']!=1)))
        {
            $info=Query_helper::get_info($this->config->item('table_forward_hom'),'*',array('year0_id ='.$year0_id,'crop_id ='.$crop_id),1);

            if($info)
            {
                if($info['status_target_finalize']===$this->config->item('system_status_yes'))
                {
                    $ajax['status']=false;
                    $ajax['system_message']=$this->lang->line("MSG_ALREADY_FINALIZED");
                    $this->jsonReturn($ajax);
                    die();
                }
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
            $budgets=array();//hom budget
            foreach($results as $result)
            {
                $budgets[$result['variety_id']]=$result;
            }

            foreach($items as $variety_id=>$quantity)
            {
                $data=array();
                {
                    if(strlen($quantity)==0)
                    {
                        $data['year0_target_quantity']=0;
                    }
                    else
                    {
                        $data['year0_target_quantity']=$quantity;
                    }

                    if(isset($budgets[$variety_id]))
                    {
                        $data['user_updated'] = $user->user_id;
                        $data['date_updated'] = $time;
                        $data['user_budgeted'] = $user->user_id;
                        $data['date_budgeted'] = $time;
                        if($quantity!=$budgets[$variety_id]['year0_target_quantity'])
                        {
                            Query_helper::update($this->config->item('table_hom_bud_hom_bt'),$data,array("id = ".$budgets[$variety_id]['id']));
                        }
                    }
                    else
                    {
                        $data['year0_id']=$year0_id;
                        $data['variety_id']=$variety_id;
                        $data['user_created'] = $user->user_id;
                        $data['date_created'] = $time;
                        $data['user_budgeted'] = $user->user_id;
                        $data['date_budgeted'] = $time;
                        if($quantity!=0)
                        {
                            Query_helper::add($this->config->item('table_hom_bud_hom_bt'),$data);
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

}
