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
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">HOM Budget</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo 0;?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Current Stock</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo 0;?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Final Variance</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo 0;?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Quantity Needed</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo 0;?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Purchase Quantity(kg)</label>
            </div>
            <div class="col-sm-4 col-xs-8">
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">JUNE</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" class="form-control float_type_all" style="float: left;">
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">JULY</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" class="form-control float_type_all" style="float: left;">
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">AUGUST</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" class="form-control float_type_all" style="float: left;">
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Purchase price</label>
            </div>
            <div class="col-xs-2">
                <input type="text" class="form-control float_type_all" style="float: left;">
            </div>
            <div class="col-xs-2">
                <select class="form-control">
                    <option>USD</option>
                    <option>EURO</option>
                </select>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">COGS</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo 0;?></label>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript">

    jQuery(document).ready(function()
    {
        turn_off_triggers();

    });
</script>
