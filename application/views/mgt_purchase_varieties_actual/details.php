<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
    $action_data=array();
    $action_data["action_back"]=base_url($CI->controller_url);
    if(isset($CI->permissions['edit'])&&($CI->permissions['edit']==1))
    {
        $action_data["action_edit_get"]=site_url($CI->controller_url."/index/edit/".$consignment['id']);
    }
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
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_FISCAL_YEAR');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $consignment['fiscal_year_name'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_MONTH_PURCHASE');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo date("M", mktime(0, 0, 0,  $consignment['month'],1, 2000));?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_DATE_PURCHASE');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo System_helper::display_date($consignment['date_purchase']);?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_CONSIGNMENT_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $consignment['name'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_CURRENCY_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $consignment['currency_name'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_CURRENCY_RATE');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $consignment['rate'];?></label>
            </div>
        </div>
        <?php
        foreach($direct_cost_items as $item)
        {
            ?>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php  echo $item['text'];?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php if(isset($direct_costs[$item['value']])){echo $direct_costs[$item['value']]['cost'];}?></label>
                </div>
            </div>
        <?php
        }
        ?>
    </div>

    <div class="clearfix"></div>
<script type="text/javascript">

    jQuery(document).ready(function()
    {
        turn_off_triggers();

    });
</script>
