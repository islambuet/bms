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
                <label class="control-label pull-right">HOM %<span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="incentive[hom]" class="form-control float_type_positive" style="text-align: left;" value="<?php echo $incentive['hom'];?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">ICT %<span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="incentive[ict]" class="form-control float_type_positive" style="text-align: left;" value="<?php echo $incentive['ict'];?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">DI %<span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="incentive[di]" class="form-control float_type_positive" style="text-align: left;" value="<?php echo $incentive['di'];?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">ZI %<span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="incentive[zi]" class="form-control float_type_positive" style="text-align: left;" value="<?php echo $incentive['zi'];?>"/>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right">TI %<span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <input type="text" name="incentive[ti]" class="form-control float_type_positive" style="text-align: left;" value="<?php echo $incentive['ti'];?>"/>
            </div>
        </div>
    </div>
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                Achieve Ratio
            </div>
            <div class="clearfix"></div>
        </div>
        <?php
        for($i=100;$i>=0;$i--)
        {
            ?>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php echo $i; ?> %<span style="color:#FF0000">*</span></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <input type="text" name="achieve_ratio[<?php echo $i; ?>]" class="form-control float_type_positive" style="text-align: left;" value="<?php echo $achieve_ratio[$i];?>"/>
                </div>
            </div>
            <?php
        }
        ?>
    </div>


    <div class="clearfix"></div>
</form>
<script type="text/javascript">

    jQuery(document).ready(function()
    {
        turn_off_triggers();

    });
</script>
