<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
    $action_data=array();
    $action_data["action_back"]=base_url($CI->controller_url);
    $action_data["action_save"]='#save_form';
    $action_data["action_clear"]='#save_form';
    $CI->load->view("action_buttons",$action_data);
?>
<form class="form_valid" id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $year0_id; ?>" />
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_GENERAL_EXPENSE');?> %<span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="indirect_cost[general]" class="form-control" value="<?php if(isset($indirect_cost['general'])){echo $indirect_cost['general'];}?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_MARKETING_EXPENSE');?> %<span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="indirect_cost[marketing]" class="form-control" value="<?php if(isset($indirect_cost['marketing'])){echo $indirect_cost['marketing'];}?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_FINANCE_EXPENSE');?> %<span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="indirect_cost[finance]" class="form-control" value="<?php if(isset($indirect_cost['finance'])){echo $indirect_cost['finance'];}?>"/>
            </div>
        </div>
    </div>
    <div class="row widget">
        <div class="widget-header">
            <div class="title">

            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_PROFIT');?> %<span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="indirect_cost[profit]" class="form-control" value="<?php if(isset($indirect_cost['profit'])){echo $indirect_cost['profit'];}?>"/>
            </div>
        </div>
    </div>
    <div class="row widget">
        <div class="widget-header">
            <div class="title">

            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_SALES_COMMISSION');?> %<span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="indirect_cost[sales_commission]" class="form-control" value="<?php if(isset($indirect_cost['sales_commission'])){echo $indirect_cost['sales_commission'];}?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_INCENTIVE');?> %<span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="indirect_cost[incentive]" class="form-control" value="<?php if(isset($indirect_cost['incentive'])){echo $indirect_cost['incentive'];}?>"/>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>
</form>
<script type="text/javascript">

    jQuery(document).ready(function()
    {
        turn_off_triggers();

    });
</script>
