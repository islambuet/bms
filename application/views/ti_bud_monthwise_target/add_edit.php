<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
    $action_data=array();
    $action_data["action_back"]=base_url($CI->controller_url);
    $action_data["action_save_jqx"]='#save_form_jqx';
    if(isset($CI->permissions['view'])&&($CI->permissions['view']==1))
    {
        $action_data["action_details_get"]=site_url($CI->controller_url."/index/details/".$territory_id.'/'.$year0_id.'/'.$type_id);
    }
    $CI->load->view("action_buttons",$action_data);
?>
<form class="form_valid" id="save_form_jqx" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" name="territory_id" value="<?php echo $territory_id; ?>" />
    <input type="hidden" name="year0_id" value="<?php echo $year0_id; ?>" />
    <input type="hidden" name="type_id" value="<?php echo $type_id; ?>" />
    <div id="jqx_inputs">
    </div>
</form>
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
        turn_off_triggers();
        $(document).on("click", "#button_action_save_jqx", function(event)
        {
            $("#system_loading").show();
            $('#save_form_jqx #jqx_inputs').html('');
            var data=$('#system_jqx_container').jqxGrid('getrows');
            for(var i=0;i<data.length;i++)
            {
            <?php
                for($i=1;$i<13;$i++)
                {?>
                if(data[i]['target_quantity_<?php echo $i; ?>_editable'])
                {
                    $('#save_form_jqx  #jqx_inputs').append('<input type="hidden" id="items_'+data[i]['variety_id']+'_target_quantity_<?php echo $i; ?>" name="items['+data[i]['variety_id']+'][<?php echo $i; ?>]" value="'+data[i]['target_quantity_<?php echo $i; ?>']+'">');
                }
                <?php
                }
            ?>
            }
            $("#save_form_jqx").submit();

        });
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
            if(record[column+'_editable'])
            {
                element.html('<div class="jqxgrid_input">'+value+'</div>');
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
                editable:true,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_SL_NO'); ?>',pinned:true, dataField: 'sl_no',width:'50',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?>',pinned:true, dataField: 'type_name',width:'60',cellsrenderer: cellsrenderer,align:'center',editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>',pinned:true, dataField: 'variety_name',width:'150',cellsrenderer: cellsrenderer,align:'center',editable:false},
                    { text: 'Total Target',dataField: 'year0_target_quantity',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    <?php
                        for($i=1;$i<13;$i++)
                        {?>{text: '<?php echo date("M", mktime(0, 0, 0,  $i,1, 2000));?>',dataField: '<?php echo 'target_quantity_'.$i;?>',width:'110',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:true,columntype:'custom',
                            cellbeginedit: function (row)
                            {
                                var selectedRowData = $('#system_jqx_container').jqxGrid('getrowdata', row);
                                return selectedRowData['target_quantity_<?php echo $i; ?>_editable'];
                            },
                            initeditor: function (row, cellvalue, editor, celltext, pressedkey) {

                                editor.html('<div style="margin: 0px;width: 100%;height: 100%;padding: 5px;"><input type="text" value="'+cellvalue+'" class="jqxgrid_input integer_type_positive"><div>');
                            },
                            geteditorvalue: function (row, cellvalue, editor) {
                                // return the editor's value.
                                var value=editor.find('input').val();
                                var selectedRowData = $('#system_jqx_container').jqxGrid('getrowdata', row);
                                return editor.find('input').val();
                            }
                        },
                            <?php
                        }
                    ?>
                    { text: 'Variance', dataField: 'allocation_variance',cellsrenderer: cellsrenderer,align:'center',editable:false}
                ]
            });

    });
</script>