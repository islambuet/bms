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
        <div class="col-xs-12" style="overflow-x: auto;">
            <table class="table table-hover table-bordered">
                <thead>
                <tr>
                    <th style="width: 100px;">Currency</th>
                    <th>Rate</th>
                </tr>
                </thead>
                <tbody>
                    <?php
                    foreach($currencies as $currency)
                    {
                        ?>
                        <tr>
                            <td>
                                <?php echo $currency['name']; ?>
                            </td>
                            <td>
                                <?php if(isset($rates[$currency['id']])){echo $rates[$currency['id']];}else{echo 'NOT SET';}?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
                </table>
            </div>
    </div>

    <div class="clearfix"></div>

<script type="text/javascript">

    jQuery(document).ready(function()
    {
        turn_off_triggers();

    });
</script>
