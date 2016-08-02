<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
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
                <select id="year0_id" class="form-control">
                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                    <?php
                    foreach($years as $year)
                    {?>
                        <option value="<?php echo $year['value']?>"><?php echo $year['text'];?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_CROP_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <select id="crop_id" class="form-control">
                    <option value=""><?php echo $this->lang->line('SELECT');?></option>
                    <?php
                    foreach($crops as $crop)
                    {?>
                        <option value="<?php echo $crop['value']?>"><?php echo $crop['text'];?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>
    <div id="system_report_container">

    </div>

    <div class="clearfix"></div>


<script type="text/javascript">
    function load_crops()
    {
        var year0_id=$('#year0_id').val();
        var crop_id=$('#crop_id').val();
        if(year0_id>0 && crop_id>0)
        {
            $.ajax({
                url: '<?php echo site_url($CI->controller_url.'/index/edit');?>',
                type: 'POST',
                datatype: "JSON",
                data:{year0_id:year0_id,crop_id:crop_id},
                success: function (data, status)
                {

                },
                error: function (xhr, desc, err)
                {
                    console.log("error");

                }
            });
        }
    }
    jQuery(document).ready(function()
    {
        turn_off_triggers();
        $(document).on("click", "#button_action_save_jqx", function(event)
        {
            console.log('hi');
            /*$("#system_loading").show();
            var data=$('#system_jqx_container').jqxGrid('getrows');
            for(var i=0;i<data.length;i++)
            {
                if(data[i]['year0_target_quantity_editable'])
                {
                    $('#save_form_jqx').append('<input type="hidden" id="items_'+data[i]['variety_id']+'_year0_target_quantity" name="items['+data[i]['variety_id']+']" value="'+data[i]['year0_target_quantity']+'">');
                }
            }
            $("#save_form_jqx").submit();*/

        });
        load_crops();
        $(document).on("change","#year0_id",function()
        {
            $('#system_report_container').html('');
            load_crops();
        });
        $(document).on("change","#crop_id",function()
        {
            $('#system_report_container').html('');
            load_crops();
        });

    });
</script>
