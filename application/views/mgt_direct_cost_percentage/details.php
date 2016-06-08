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
                    <th style="width: 150px;">Direct Cost</th>
                    <th>Percentage</th>
                </tr>
                </thead>
                <tbody>
                    <?php
                    foreach($items as $item)
                    {
                        ?>
                        <tr>
                            <td>
                                <?php echo $item['name']; ?>
                            </td>
                            <td>
                                <?php if(isset($percentages[$item['id']])){echo $percentages[$item['id']]['percentage'];}else{echo 'NOT SET';}?>
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
