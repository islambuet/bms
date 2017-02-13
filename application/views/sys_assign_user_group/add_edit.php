<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$CI=& get_instance();
$action_data=array();
$action_data['action_back']=site_url($CI->controller_url);
$action_data['action_save']='#save_form';
$action_data['action_clear']='#save_form';
$CI->load->view('action_buttons',$action_data);
?>
<form id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save'); ?>" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $item['user_id']; ?>" />
    <input type="hidden" id="system_save_new_status" name="system_save_new_status" value="0" />
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label for="user_group" class="control-label pull-right"><?php echo $CI->lang->line('LABEL_USER_GROUP');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select id="user_group" name="user_group" class="form-control">
                    <option value="0"><?php echo $this->lang->line('SELECT');?></option>
                    <?php
                    foreach($user_groups as $user_group)
                    {?>
                        <option value="<?php echo $user_group['value']?>" <?php if($user_group['value']==$item['user_group']){ echo "selected";} ?>><?php echo $user_group['text'];?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </div>

    </div>

    <div class="clearfix"></div>
</form>
