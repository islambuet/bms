<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
    $action_data=array();
    $action_data["action_back"]=base_url($CI->controller_url);
    if((isset($this->permissions['edit'])&&($this->permissions['edit']==1))||(isset($this->permissions['add'])&&($this->permissions['add']==1)))
    {
        $action_data["action_edit_get"]=site_url($CI->controller_url."/index/edit/".$territory_id.'/'.$year0_id.'/'.$type_id);
    }
    if(isset($CI->permissions['forward'])&&($CI->permissions['forward']==1))
    {
        $action_data["action_forward_get"]=site_url($CI->controller_url."/index/forward/".$territory_id.'/'.$year0_id.'/'.$type_id);
    }
    $CI->load->view("action_buttons",$action_data);
?>
<div class="row widget">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="col-xs-12" id="system_jqx_container">

    </div>
</div>

<script type="text/javascript">
    $(document).ready(function ()
    {
        var url = "<?php echo site_url($CI->controller_url.'/index/get_edit_items');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'variety_id', type: 'string' },
                { name: 'variety_name', type: 'string' },
                { name: 'year0_target_quantity', type: 'string' },
                <?php
                    for($i=1;$i<13;$i++)
                    {?>
                { name: '<?php echo 'target_quantity_'.$i;?>', type: 'string' },
                { name: '<?php echo 'target_quantity_'.$i.'_editable';?>', type: 'string' },
                { name: '<?php echo 'target_quantity_'.$i.'_pick_month';?>', type: 'string' },
                <?php
                    }
            ?>
                { name: 'sl_no', type: 'int' }
            ],
            id: 'id',
            url: url,
            type: 'POST',
            data:{<?php echo $keys; ?>}
        };
        var dataAdapter = new $.jqx.dataAdapter(source);
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            if (record.variety_name=="Total Type")
            {
                if(!((column=='sl_no')||(column=='type_name')))
                {
                    element.css({ 'background-color': '#6CAB44','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }
            }
            else if (record.type_name=="Total Crop")
            {
                if((column!='sl_no'))
                {
                    element.css({ 'background-color': '#0CA2C5','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});

                }
            }
            else if((column=='allocation_variance'))
            {
                var variance=0;
                if(!isNaN(parseFloat(record['year0_target_quantity'])))
                {
                    variance=parseFloat(record['year0_target_quantity']);
                }
                <?php
                    for($i=1;$i<13;$i++)
                    {
                        ?>
                if(!isNaN(parseFloat(record['<?php echo 'target_quantity_'.$i;?>'])))
                {
                    variance-=parseFloat(record['<?php echo 'target_quantity_'.$i;?>']);
                }
                <?php
            }
            ?>

                if(variance==0)
                {
                    element.html('');
                    element.css({ 'background-color': '#00FF00','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }
                else
                {
                    element.html(variance);
                    element.css({ 'background-color': '#FF0000','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }

            }
            else
            {
                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            if(record[column+'_pick_month'])
            {
                element.css({ 'background-color': '#00FF00'});
            }
            return element[0].outerHTML;

        };
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                height:'300',
                source: dataAdapter,
                columnsresize: true,
                columnsreorder: true,
                altrows: true,
                rowsheight: 35,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_SL_NO'); ?>',pinned:true, dataField: 'sl_no',width:'50',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?>',pinned:true, dataField: 'type_name',width:'60',cellsrenderer: cellsrenderer,align:'center',editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>',pinned:true, dataField: 'variety_name',width:'150',cellsrenderer: cellsrenderer,align:'center',editable:false},
                    { text: 'Total Target',dataField: 'year0_target_quantity',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                        <?php
                            for($i=1;$i<13;$i++)
                            {?>{text: '<?php echo date("M", mktime(0, 0, 0,  $i,1, 2000));?>',dataField: '<?php echo 'target_quantity_'.$i;?>',width:'110',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'},
                    <?php
                }
            ?>
                    { text: 'Variance', dataField: 'allocation_variance',cellsrenderer: cellsrenderer,align:'center',editable:false}
                ]
            });

    });
</script>