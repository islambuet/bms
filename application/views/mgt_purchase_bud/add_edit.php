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
                <label class="control-label pull-right">Variety Name</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $variety_info['text'];?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Import Name</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $variety_info['name_import'];?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Principal Name</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $variety_info['principal_name'];?></label>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Purchase Quantity(kg)</label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label" id="quantity_purchased"></label>
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
                    $quantity=0;
                    $variable='quantity_'.(($i%12)+1);
                    if((isset($$variable))&& ($$variable>0))
                    {
                        $quantity=$$variable;
                    }
                    if($quantity==0)
                    {
                        $quantity='';
                    }
                    ?>
                    <div class="col-xs-1">
                        <label class="control-label pull-right"><?php echo date("M", mktime(0, 0, 0,  ($i%12)+1,1, 2000));?></label>
                    </div>
                    <div class="col-xs-2">
                        <input id="quantity_<?php echo ($i%12)+1;?>" name="purchase[quantity_<?php echo ($i%12)+1;?>]" type="text" class="form-control float_type_positive quantity_month" style="float: left;margin-bottom: 5px;" value="<?php echo $quantity;  ?>">
                    </div>

                    <?php
                }
                ?>
                </div>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">Price/KG</label>
            </div>
            <div class="col-xs-2">
                <input id="price" name="purchase[unit_price]" type="text" class="form-control float_type_positive" style="float: left;" value="<?php if(isset($unit_price)){echo $unit_price;} ?>">
            </div>
            <div class="col-xs-2">
                <select id="currency_id" name="purchase[currency_id]" class="form-control">
                    <?php
                    foreach($currencies as $currency)
                    {?>
                        <option value="<?php echo $currency['value']?>" <?php if(isset($currency_id)&&($currency['value']==$currency_id)){ echo "selected";}?>><?php echo $currency['text'];?></option>
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
    <?php
        foreach($currencies as $currency)
        {
            $value=0;
            if(isset($currency_rates[$currency['value']]))
            {
                $value=$currency_rates[$currency['value']];
            }
        ?>
        var currency_<?php echo $currency['value'];?>=<?php echo $value;?>;
        <?php
        }
    ?>
    var direct_costs_percentage=<?php echo $direct_costs_percentage; ?>;
    var packing_cost=<?php echo $packing_cost; ?>;
    function calculate_total()
    {
        var quantity_purchased=0;
        var total_cogs=0;
        var cogs=0;
        $("#quantity_purchased").html("-");
        $("#cogs").html("-");
        $("#total_cogs").html("-");

        $(".quantity_month").each( function( index, element )
        {
            var month_quantity=parseFloat($(this).val());
            if(month_quantity>0)
            {
                quantity_purchased+=month_quantity;
            }
        });
        if(quantity_purchased>0)
        {
            $("#quantity_purchased").html(number_format(quantity_purchased,3,'.',''));
        }
        var price=parseFloat($("#price").val());
        var currency_id=$("#currency_id").val();
        if(price>0)
        {
            var unit_price=price*window['currency_'+currency_id];
            var total_unit_price=unit_price+unit_price*direct_costs_percentage+packing_cost;
            $("#cogs").html(number_format(total_unit_price,2));
            if(quantity_purchased>0)
            {
                $("#total_cogs").html(number_format(total_unit_price*quantity_purchased,2));
            }

        }

    }
    jQuery(document).ready(function()
    {
        turn_off_triggers();
        $(document).off("change", ".quantity_month");
        $(document).off("change", "#price");
        $(document).off("change", "#currency_id");
        calculate_total();
        $(document).on("change",".quantity_month",function(){
            calculate_total();
        });
        $(document).on("change","#price",function(){
            calculate_total();
        });
        $(document).on("change","#currency_id",function(){
            calculate_total();
        });


    });
</script>
