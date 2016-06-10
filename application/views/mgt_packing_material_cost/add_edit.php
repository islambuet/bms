<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
    $action_data=array();
    $action_data["action_back"]=base_url($CI->controller_url);
    $action_data["action_save"]='#save_form';
    $CI->load->view("action_buttons",$action_data);
?>
<form class="form_valid" id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" name="year0_id" value="<?php echo $year0_id; ?>" />
    <input type="hidden" name="variety_id" value="<?php echo $variety_id; ?>" />
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <?php
        foreach($packing_items as $item)
        {
            ?>
            <div style="" class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $item['text'];?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <input type="text" name="items[<?php echo $item['value'];?>]" class="form-control float_type_all" style="text-align: left;" value="<?php if(isset($prev_packing_cost[$item['value']])){echo $prev_packing_cost[$item['value']]['cost'];};?>"/>
                </div>
            </div>
        <?php
        }
        ?>
    </div>
</form>
<script type="text/javascript">

    jQuery(document).ready(function()
    {
        turn_off_triggers();

    });
</script>
