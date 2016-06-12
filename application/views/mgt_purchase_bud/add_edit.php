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
                <label class="control-label"><?php if($hom_budget==0){echo '-';}else{echo $hom_budget;}?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Current Stock</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php if($current_stock==0){echo '-';}else{echo $current_stock;}?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Final Variance</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php if($final_variance==0){echo '-';}else{echo $final_variance;}?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Quantity Needed</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php if($quantity_needed==0){echo '-';}else{echo $quantity_needed;}?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Purchase Quantity(kg)</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label" id="quantity_purchased"><?php if($quantity_purchased==0){echo '-';}else{echo $quantity_purchased;}?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Months</label>
            </div>
            <div class="col-xs-8">
                <div class="row">
                <?php
                for($i=5;$i<17;$i++)
                {
                    ?>
                    <div class="col-xs-1">
                        <label class="control-label pull-right"><?php echo date("M", mktime(0, 0, 0,  ($i%12)+1,1, 2000));?></label>
                    </div>
                    <div class="col-xs-2">
                        <input id="quantity_<?php echo ($i%12)+1;?>" name="purchase[quantity_<?php echo ($i%12)+1;?>]" type="text" class="form-control float_type_all quantity_month" style="float: left;margin-bottom: 5px;">
                    </div>

                    <?php
                }
                ?>
                </div>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Purchase price</label>
            </div>
            <div class="col-xs-2">
                <input id="price" name="purchase[name]" type="text" class="form-control float_type_all" style="float: left;">
            </div>
            <div class="col-xs-2">
                <select id="currency_id" name="purchase[currency_id]" class="form-control">
                    <?php
                    foreach($currencies as $currency)
                    {?>
                        <option value="<?php echo $currency['value']?>" <?php //if($arm_bank['value']==$payment['arm_bank_id']){ echo "selected";}?>><?php echo $currency['text'];?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">COGS</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label id="cogs" class="control-label"></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Total COGS</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label id="total_cogs" class="control-label"></label>
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
