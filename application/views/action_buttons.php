<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');?>
<?php
    $CI = & get_instance();

?>
<div class="row widget hidden-print" style="padding-bottom: 0px;">

    <?php
    if(isset($action_new))
    {
        ?>
        <div class="action_button">
            <a class="btn" href="<?php echo $action_new; ?>"><?php echo $CI->lang->line("ACTION_NEW"); ?></a>
        </div>
        <?php
    }
    ?>
    <?php
    if(isset($action_edit))
    {
        ?>
        <div class="action_button">
            <button id="button_action_edit" class="btn button_action_single" data-action-link="<?php echo $action_edit; ?>"><?php echo $CI->lang->line("ACTION_EDIT"); ?></button>
        </div>
        <?php
    }
    ?>
    <?php
    if(isset($action_edit_get))
    {
        ?>
        <div class="action_button">
            <a class="btn" id="button_action_edit_get" href="<?php echo $action_edit_get; ?>"><?php echo $CI->lang->line("ACTION_EDIT"); ?></a>
        </div>
    <?php
    }
    ?>
    <?php
    if(isset($action_forward))
    {
        ?>
        <div class="action_button">
            <button id="button_action_request" class="btn button_action_single" data-action-link="<?php echo $action_forward; ?>"><?php echo $CI->lang->line("ACTION_FORWARD"); ?></button>
        </div>
    <?php
    }
    ?>
    <?php
    if(isset($action_forward_get))
    {
        ?>
        <div class="action_button">
            <button id="button_action_request_get" class="btn" data-action-link="<?php echo $action_forward_get; ?>"><?php echo $CI->lang->line("ACTION_FORWARD"); ?></button>
        </div>
    <?php
    }
    ?>
    <?php
    if(isset($action_details))
    {
        ?>
        <div class="action_button">
                <button id="button_action_details" class="btn button_action_single" data-action-link="<?php echo $action_details; ?>"><?php echo $CI->lang->line("ACTION_DETAILS"); ?></button>
            </div>
            <?php
        }
    ?>
    <?php
    if(isset($action_details_get))
    {
        ?>
        <div class="action_button">
            <a class="btn" id="button_action_details_get" href="<?php echo $action_details_get; ?>"><?php echo $CI->lang->line("ACTION_DETAILS"); ?></a>
        </div>
    <?php
    }
    ?>
    <?php
    if(isset($action_delete))
    {
        ?>
        <div class="action_button">
            <button id="button_action_delete" class="btn button_action_multiple" data-action-link="<?php echo $action_delete; ?>"><?php echo $CI->lang->line("ACTION_DELETE"); ?></button>
        </div>
    <?php
    }
    ?>
    <?php
    if(isset($action_back))
    {
        ?>
        <div class="action_button">
            <a class="btn" href="<?php echo $action_back; ?>"><?php echo $CI->lang->line("ACTION_BACK"); ?></a>
        </div>
        <?php
    }
    ?>
    <?php
    if(isset($action_report))
    {
        ?>
        <div class="action_button">
            <button id="button_action_report" class="btn" data-form="<?php echo $action_report; ?>"><?php echo $CI->lang->line("ACTION_REPORT"); ?></button>
        </div>
    <?php
    }
    ?>
    <?php
    if(isset($action_save))
    {
        ?>
        <div class="action_button">
            <button id="button_action_save" class="btn" data-form="<?php echo $action_save; ?>"><?php echo $CI->lang->line("ACTION_SAVE"); ?></button>
        </div>
        <?php
    }
    ?>
    <?php
    if(isset($action_save_jqx))
    {
        ?>
        <div class="action_button">
            <button id="button_action_save_jqx" class="btn" data-form="<?php echo $action_save_jqx; ?>"><?php echo $CI->lang->line("ACTION_SAVE"); ?></button>
        </div>
    <?php
    }
    ?>
    <?php
    if(isset($action_save_new))
    {
        ?>
        <div class="action_button">
                <button id="button_action_save_new" class="btn" data-form="<?php echo $action_save_new; ?>"><?php echo $CI->lang->line("ACTION_SAVE_NEW"); ?></button>
            </div>
            <?php
        }
    ?>
    <?php
    if(isset($action_clear))
    {
        ?>
        <div class="action_button">
            <button id="button_action_clear" class="btn" data-form="<?php echo $action_clear; ?>"><?php echo $CI->lang->line("ACTION_CLEAR"); ?></button>
        </div>
    <?php
    }
    ?>
    <?php
    if(isset($action_refresh))
    {
        ?>
        <div class="action_button">
            <a class="btn" href="<?php echo $action_refresh; ?>"><?php echo $CI->lang->line("ACTION_REFRESH"); ?></a>
        </div>
    <?php
    }
    ?>
    <?php
    if(isset($action_print))
    {
        ?>
        <div class="action_button">
            <button id="button_action_print" class="btn" data-title="<?php echo $action_print; ?>"><?php echo $CI->lang->line("LABEL_ACTION4"); ?></button>
        </div>
    <?php
    }
    ?>
    <?php
    if(isset($action_print_page))
    {
        ?>
        <div class="action_button">
            <button onClick="window.print()" class="btn" data-title="<?php echo $action_print_page; ?>"><?php echo $CI->lang->line("LABEL_ACTION4"); ?></button>
        </div>
    <?php
    }
    ?>
    <?php
    if(isset($action_download))
    {
        ?>
        <div class="action_button">
            <button id="button_action_download" class="btn" data-title="<?php echo $action_download; ?>"><?php echo $CI->lang->line("LABEL_ACTION5"); ?></button>
        </div>
    <?php
    }
    ?>
</div>
<div class="clearfix"></div>