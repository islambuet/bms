<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
    $action_data=array();
    if(isset($CI->permissions['add'])&&($CI->permissions['add']==1))
    {
        $action_data["action_new"]=base_url($CI->controller_url."/index/add");
    }
    $action_data["action_refresh"]=base_url($CI->controller_url."/index/list");
    $CI->load->view("action_buttons",$action_data);
?>

<div class="row widget">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="col-xs-12" style="overflow-x: auto;">
        <table class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th><?php echo $CI->lang->line("ID");?></th>
                    <th><?php echo $CI->lang->line("NAME");?></th>
                    <th><?php echo $CI->lang->line("TYPE");?></th>
                    <th><?php echo $CI->lang->line("LABEL_ORDER");?></th>
                    <th><?php echo $CI->lang->line("LABEL_CONTROLLER_NAME");?></th>
                </tr>
            </thead>

            <tbody>
            <?php
                if(sizeof($modules_tasks)>0)
                {
                    foreach($modules_tasks as $module_task)
                    {
                        ?>
                        <tr>
                            <td><?php echo $module_task['module_task']['id']; ?></td>
                            <td><?php echo $module_task['prefix'];?><a href="<?php echo site_url('sys_module_task/index/edit/'.$module_task['module_task']['id']);?>"><?php echo $module_task['module_task']['name']; ?></a></td>
                            <td><?php if($module_task['module_task']['type']=='TASK'){echo $CI->lang->line('TASK');}else{ echo $CI->lang->line('MODULE');} ?></td>
                            <td><?php echo $module_task['module_task']['ordering']; ?></td>
                            <td><?php echo $module_task['module_task']['controller']; ?></td>

                        </tr>
                    <?php
                    }
                }
                else
                {
                    ?>
                    <tr>
                        <td colspan="20" class="text-center alert-danger">
                            <?php echo $CI->lang->line("NO_DATA_FOUND"); ?>
                        </td>
                    </tr>
                    <?php
                }
            ?>

            </tbody>
        </table>
    </div>


</div>
<div class="clearfix"></div>