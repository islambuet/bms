<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
    $action_data=array();
    $action_data["action_back"]=base_url($CI->controller_url);
    $action_data["action_save"]='#save_form';
    if(isset($CI->permissions['view'])&&($CI->permissions['view']==1))
    {
        $action_data["action_details_get"]=site_url($CI->controller_url."/index/details/".$consignment['id']);
    }
    $CI->load->view("action_buttons",$action_data);
?>
<form class="form_valid" id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" id="id" name="id" value="<?php echo $consignment['id']; ?>" />
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
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_PRINCIPAL_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $consignment['principal_name'];?></label>
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
        <div class="widget-header">
            <div class="title">
                Varieties
            </div>
            <div class="clearfix"></div>
        </div>
        <div style="overflow-x: auto;" class="row show-grid" id="order_items_container">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th style="min-width: 150px;"><?php echo $CI->lang->line('LABEL_CROP_NAME'); ?></th>
                    <th style="min-width: 150px;"><?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?></th>
                    <th style="min-width: 150px;"><?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?></th>
                    <th style="min-width: 150px;"><?php echo $CI->lang->line('LABEL_QUANTITY'); ?></th>
                    <th style="min-width: 150px;">PI Value</th>

                </tr>
                </thead>
                <tbody>
                <?php

                foreach($varieties as $index=>$variety)
                {
                    ?>
                    <tr>
                        <td>
                            <label><?php echo $variety['crop_name']; ?></label>
                        </td>
                        <td>
                            <label><?php echo $variety['crop_type_name']; ?></label>
                        </td>
                        <td>
                            <label><?php echo $variety['variety_name']; ?></label>
                            <input type="hidden" class="variety_id" id="variety_id_<?php echo $index+1;?>" name="varieties[<?php echo $index+1;?>][variety_id]" value="<?php echo $variety['variety_id']; ?>" />
                        </td>
                        <td class="text-right">
                            <input type="text" value="<?php echo $variety['quantity']; ?>" class="form-control text-right quantity" id="quantity_<?php echo $index+1;?>" data-current-id="<?php echo $index+1;?>" name="varieties[<?php echo $index+1;?>][quantity]">
                        </td>
                        <td class="text-right">
                            <input type="text" value="<?php echo $variety['price']; ?>" class="form-control text-right price" id="price_<?php echo $index+1;?>" data-current-id="<?php echo $index+1;?>" name="varieties[<?php echo $index+1;?>][price]">
                        </td>
                        <td>
                            <button class="btn btn-danger system_button_add_delete" type="button"><?php echo $CI->lang->line('DELETE'); ?></button>
                        </td>
                    </tr>
                <?php
                }
                ?>
                </tbody>
            </table>

        </div>
        <div class="row show-grid">
            <div class="col-xs-4">

            </div>
            <div class="col-xs-4">
                <button type="button" class="btn btn-warning system_button_add_more" data-current-id="<?php echo sizeof($varieties);?>"><?php echo $CI->lang->line('LABEL_ADD_MORE');?></button>
            </div>
            <div class="col-xs-4">

            </div>
        </div>
    </div>

    <div class="clearfix"></div>
</form>
<div id="system_content_add_more" style="display: none;">
    <table>
        <tbody>
        <tr>
            <td>
                <select class="form-control crop_id">
                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                    <?php
                    foreach($crops as $crop)
                    {?>
                        <option value="<?php echo $crop['value']?>"><?php echo $crop['text'];?></option>
                    <?php
                    }
                    ?>
                </select>
            </td>
            <td>
                <div style="display: none;" class="crop_type_id_container">
                    <select class="form-control crop_type_id">
                        <option value=""><?php echo $this->lang->line('SELECT');?></option>
                    </select>
                </div>
            </td>
            <td>
                <div style="display: none;" class="variety_id_container">
                    <select class="form-control variety_id">
                        <option value=""><?php echo $this->lang->line('SELECT');?></option>
                    </select>
                </div>
            </td>
            <td class="text-right">
                <input type="text"class="form-control float_type_positive text-right quantity" value=""/>
            </td>
            <td class="text-right">
                <input type="text"class="form-control float_type_positive text-right price" value=""/>
            </td>

            <td><button type="button" class="btn btn-danger system_button_add_delete"><?php echo $CI->lang->line('DELETE'); ?></button></td>
        </tr>
        </tbody>
    </table>
</div>
<script type="text/javascript">

    jQuery(document).ready(function()
    {
        turn_off_triggers();
        $(document).on("click", ".system_button_add_more", function(event)
        {
            var current_id=parseInt($(this).attr('data-current-id'));
            current_id=current_id+1;
            $(this).attr('data-current-id',current_id);
            var content_id='#system_content_add_more table tbody';
            $(content_id+' .crop_id').attr('id','crop_id_'+current_id);
            $(content_id+' .crop_id').attr('data-current-id',current_id);

            $(content_id+' .crop_type_id').attr('id','crop_type_id_'+current_id);
            $(content_id+' .crop_type_id').attr('data-current-id',current_id);
            $(content_id+' .crop_type_id_container').attr('id','crop_type_id_container_'+current_id);

            $(content_id+' .variety_id').attr('id','variety_id_'+current_id);
            $(content_id+' .variety_id').attr('data-current-id',current_id);
            $(content_id+' .variety_id').attr('name','varieties['+current_id+'][variety_id]');
            $(content_id+' .variety_id_container').attr('id','variety_id_container_'+current_id);

            $(content_id+' .quantity').attr('id','quantity_'+current_id);
            $(content_id+' .quantity').attr('data-current-id',current_id);
            $(content_id+' .quantity').attr('name','varieties['+current_id+'][quantity]');

            $(content_id+' .price').attr('id','price_'+current_id);
            $(content_id+' .price').attr('data-current-id',current_id);
            $(content_id+' .price').attr('name','varieties['+current_id+'][price]');
            var html=$(content_id).html();
            $("#order_items_container tbody").append(html);
            $(content_id+' .crop_id').removeAttr('id');
            $(content_id+' .crop_type_id').removeAttr('id');
            $(content_id+' .crop_type_id_container').removeAttr('id');
            $(content_id+' .variety_id').removeAttr('id');
            $(content_id+' .variety_id_container').removeAttr('id');
            $(content_id+' .quantity').removeAttr('id');
            $(content_id+' .price').removeAttr('id');

        });
        $(document).on("change",".crop_id",function()
        {

            var active_id=parseInt($(this).attr('data-current-id'));
            $("#crop_type_id_"+active_id).val("");
            $("#variety_id_"+active_id).val("");
            $("#quantity_"+active_id).val("");
            $("#price_"+active_id).val("");

            var crop_id=$('#crop_id_'+active_id).val();
            if(crop_id>0)
            {
                $('#crop_type_id_container_'+active_id).show();
                $('#variety_id_container_'+active_id).hide();

                $.ajax({
                    url: base_url+"common_controller/get_dropdown_croptypes_by_cropid/",
                    type: 'POST',
                    datatype: "JSON",
                    data:{crop_id:crop_id,html_container_id:'#crop_type_id_'+active_id},
                    success: function (data, status)
                    {

                    },
                    error: function (xhr, desc, err)
                    {
                        console.log("error");

                    }
                });
            }
            else
            {
                $('#crop_type_id_container_'+active_id).hide();
                $('#variety_id_container_'+active_id).hide();
            }
        });
        $(document).on("change",".crop_type_id",function()
        {
            var active_id=parseInt($(this).attr('data-current-id'));

            $("#variety_id_"+active_id).val("");
            $("#quantity_"+active_id).val("");
            $("#price_"+active_id).val("");
            var crop_type_id=$('#crop_type_id_'+active_id).val();
            if(crop_type_id>0)
            {
                $('#variety_id_container_'+active_id).show();

                $.ajax({
                    url: base_url+"common_controller/get_dropdown_armvarieties_by_croptypeid/",
                    type: 'POST',
                    datatype: "JSON",
                    data:{crop_type_id:crop_type_id,html_container_id:'#variety_id_'+active_id},
                    success: function (data, status)
                    {

                    },
                    error: function (xhr, desc, err)
                    {
                        console.log("error");

                    }
                });
            }
            else
            {
                $('#variety_id_container_'+active_id).hide();
            }
        });
        $(document).on("change",".variety_id",function()
        {
            var active_id=parseInt($(this).attr('data-current-id'));
            $("#quantity_"+active_id).val("");
            $("#price_"+active_id).val("");
        });
        $(document).on("click", ".system_button_add_delete", function(event)
        {
            $(this).closest('tr').remove();
        });

    });
</script>
