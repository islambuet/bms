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
                        <option value="<?php echo $year['value']?>" <?php if($year['value']==$budget['year0_id']){echo 'selected';} ?>><?php echo $year['text'];?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <div style="" class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $CI->lang->line('LABEL_DIVISION_NAME');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <?php
                if($budget['division_id']>0)
                {
                    $division_name='';
                    foreach($divisions as $division)
                    {
                        if($division['value']==$budget['division_id'])
                        {
                            $division_name=$division['text'];
                        }
                    }
                    ?>
                    <label class="control-label"><?php echo $division_name;;?></label>
                    <input type="hidden" id="division_id" value="<?php echo $budget['division_id'];?>"/>
                <?php
                }
                else
                {
                    ?>
                    <select id="division_id" class="form-control">
                        <option value=""><?php echo $this->lang->line('SELECT');?></option>
                        <?php
                        foreach($divisions as $division)
                        {?>
                            <option value="<?php echo $division['value']?>"><?php echo $division['text'];?></option>
                        <?php
                        }
                        ?>
                    </select>
                <?php
                }
                ?>
            </div>
        </div>

    </div>
    <div id="system_report_container">

    </div>

    <div class="clearfix"></div>


<script type="text/javascript">
    function load_crops()
    {
        var division_id=$('#division_id').val();
        var year0_id=$('#year0_id').val();
        if(division_id>0 && year0_id>0)
        {
            $.ajax({
                url: '<?php echo site_url($CI->controller_url.'/index/list');?>',
                type: 'POST',
                datatype: "JSON",
                data:{division_id:division_id,year0_id:year0_id},
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
        load_crops();
        $(document).on("change","#division_id",function()
        {
            $('#system_report_container').html('');
            load_crops();
        });
        $(document).on("change","#year0_id",function()
        {
            $('#system_report_container').html('');
            load_crops();
        });

    });
</script>
