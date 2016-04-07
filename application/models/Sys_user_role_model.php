<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Sys_user_role_model extends CI_Model
{
    public function __construct() {
        parent::__construct();
    }
    public function get_roles_count()
    {
        $CI = & get_instance();
        $user = User_helper::get_user();
        $this->db->from($CI->config->item('table_system_user_group').' ug');
        $this->db->select('ug.id,ug.name');
        $this->db->where('ug.status',$CI->config->item('system_status_active'));
        if($user->user_group!=1)
        {
            $this->db->where('ug.id !=1');
        }
        $user_groups=$this->db->get()->result_array();

        $this->db->from($CI->config->item('table_system_user_group_role').' ugr');
        $this->db->select('Count(ugr.id) total_task',false);
        $this->db->select('user_group_id');
        $this->db->where('ugr.revision',1);
        $this->db->where('ugr.view',1);
        $this->db->group_by('user_group_id');
        $results=$this->db->get()->result_array();
        $total_roles=array();
        foreach($results as $result)
        {
            $total_roles[$result['user_group_id']]['total_task']=$result['total_task'];
        }
        foreach($user_groups as &$groups)
        {
            if(isset($total_roles[$groups['id']]['total_task']))
            {
                $groups['total_task']=$total_roles[$groups['id']]['total_task'];
            }
            else
            {
                $groups['total_task']=0;
            }
        }
        return $user_groups;
    }

    public function get_role_status($user_group_id)
    {
        $CI = & get_instance();
        $this->db->from($CI->config->item('table_system_user_group_role').' ugr');
        $this->db->select('ugr.view,ugr.add,ugr.edit,ugr.delete,ugr.print,ugr.download,ugr.column_headers,ugr.task_id');
        $this->db->where('ugr.user_group_id',$user_group_id);
        $this->db->where('ugr.revision',1);
        $results=$this->db->get()->result_array();
        $roles=array();
        $roles['view']=array();
        $roles['add']=array();
        $roles['edit']=array();
        $roles['delete']=array();
        $roles['print']=array();
        $roles['download']=array();
        $roles['column_headers']=array();
        foreach($results as $result)
        {
            if($result['view'])
            {
                $roles['view'][]=$result['task_id'];
            }
            if($result['add'])
            {
                $roles['add'][]=$result['task_id'];
            }
            if($result['edit'])
            {
                $roles['edit'][]=$result['task_id'];
            }
            if($result['delete'])
            {
                $roles['delete'][]=$result['task_id'];
            }
            if($result['print'])
            {
                $roles['print'][]=$result['task_id'];
            }
            if($result['download'])
            {
                $roles['download'][]=$result['task_id'];
            }
            if($result['column_headers'])
            {
                $roles['column_headers'][]=$result['task_id'];
            }
        }

        return $roles;
    }
}