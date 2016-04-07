<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Sys_module_task_model extends CI_Model
{

    public function __construct() {
        parent::__construct();
    }
    public function get_modules_tasks_table_tree()
    {
        $CI = & get_instance();

        $this->db->from($CI->config->item('table_system_task'));
        //$this->db->order_by('order');
        $this->db->order_by('ordering');
        $results=$this->db->get()->result_array();
        $children=array();
        foreach($results as $result)
        {
            $children[$result['parent']]['ids'][$result['id']]=$result['id'];
            $children[$result['parent']]['modules'][$result['id']]=$result;
        }
        $level0 = $children[0]['modules'];
        $tree=array();
        foreach ($level0 as $module)
        {
            $this->get_sub_modules_tasks_tree($module,"",$tree,$children);
        }
        return $tree;

    }
    public function get_sub_modules_tasks_tree($module,$prefix,&$tree,$children)
    {
        $tree[]=array("prefix"=>$prefix,"module_task"=>$module);
        $subs=array();
        if(isset($children[$module['id']]))
        {
            $subs = $children[$module['id']]['modules'];
        }
        if (sizeof($subs) > 0)
        {
            foreach ($subs as $sub){
                $this->get_sub_modules_tasks_tree($sub,$prefix."- ",$tree,$children);
            }
        }
    }
    public function get_module_task_info($id)
    {
        $CI = & get_instance();
        $this->db->from($CI->config->item('table_system_task'));
        $this->db->where('id',$id);
        $result=$this->db->get()->row_array();
        return $result;
    }
    public function get_modules()
    {
        $CI = & get_instance();
        $this->db->from($CI->config->item('table_system_task'));
        //$this->db->order_by('order');
        $this->db->order_by('ordering');
        $this->db->where('type','Module');
        $results=$this->db->get()->result_array();
        $children=array();
        foreach($results as $result)
        {
            $children[$result['parent']]['ids'][$result['id']]=$result['id'];
            $children[$result['parent']]['modules'][$result['id']]=$result;
        }
        $level0 = $children[0]['modules'];
        $tree=array();
        foreach ($level0 as $module)
        {
            $this->get_sub_modules_tasks_tree($module,"",$tree,$children);
        }
        return $tree;
        //return $this->db->get_where('rnd_task',array("type"=>"MODULE"))->result_array();
    }
}