<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
    $action_data=array();
    $action_data["action_back"]=base_url($CI->controller_url);
    $CI->load->view("action_buttons",$action_data);
?>
<div class="row widget">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_GENERAL_EXPENSE');?> %</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php if(isset($indirect_cost['general'])){echo $indirect_cost['general'];}?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_MARKETING_EXPENSE');?> %</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php if(isset($indirect_cost['marketing'])){echo $indirect_cost['marketing'];}?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_FINANCE_EXPENSE');?> %</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php if(isset($indirect_cost['finance'])){echo $indirect_cost['finance'];}?></label>
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
            <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_PROFIT');?> %</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php if(isset($indirect_cost['profit'])){echo $indirect_cost['profit'];}?></label>
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
            <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_SALES_COMMISSION');?> %</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php if(isset($indirect_cost['sales_commission'])){echo $indirect_cost['sales_commission'];}?></label>
        </div>
    </div>
    <div class="row show-grid">
        <div class="col-xs-4">
            <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_INCENTIVE');?> %</label>
        </div>
        <div class="col-sm-4 col-xs-8">
            <label class="control-label"><?php if(isset($indirect_cost['incentive'])){echo $indirect_cost['incentive'];}?></label>
        </div>
    </div>
</div>
    <div class="clearfix"></div>

<script type="text/javascript">

    jQuery(document).ready(function()
    {
        turn_off_triggers();

    });
</script>
