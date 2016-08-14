<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
    $action_data=array();
    $action_data["action_back"]=base_url($CI->controller_url);
    $action_data["action_save_jqx"]='#save_form_jqx';
    if(isset($CI->permissions['view'])&&($CI->permissions['view']==1))
    {
        $action_data["action_details_get"]=site_url($CI->controller_url."/index/details/".$year0_id.'/'.$crop_id);
    }
    $CI->load->view("action_buttons",$action_data);
?>
<form class="form_valid" id="save_form_jqx" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" name="year0_id" value="<?php echo $year0_id; ?>" />
    <input type="hidden" name="crop_id" value="<?php echo $crop_id; ?>" />
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
                    for($i=1;$i<=$CI->config->item('num_year_prediction');$i++)
                    {
                       foreach($areas as $area)
                       {
                       ?>
                            if(data[i]['<?php echo 'year'.$i.'_target_quantity_'.$area['value'].'_editable';?>'])
                            {
                                $('#save_form_jqx  #jqx_inputs').append('<input type="hidden" name="items[<?php echo $area['value']; ?>]['+data[i]['variety_id']+'][<?php echo 'year'.$i.'_target_quantity';?>]" value="'+data[i]['<?php echo 'year'.$i.'_target_quantity_'.$area['value'];?>']+'">');
                            }
                        <?php
                       }
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
                { name: 'type_name', type: 'string' },
                { name: 'variety_name', type: 'string' },
                <?php
                    for($i=0;$i<=$CI->config->item('num_year_prediction');$i++)
                    {?>{ name: '<?php echo 'year'.$i.'_target_quantity_hom';?>', type: 'string' },
                        <?php
                        foreach($areas as $area)
                            {?>{ name: '<?php echo 'year'.$i.'_target_quantity_'.$area['value'];?>', type: 'string' },
                            { name: '<?php echo 'year'.$i.'_target_quantity_'.$area['value'].'_editable';?>', type: 'string' },
                                <?php
                            }
                    }
                ?>
                { name: 'variety_id', type: 'string' }
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
                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            if(record[column+'_editable'])
            {
                element.html('<div class="jqxgrid_input">'+value+'</div>');
            }
            <?php
            for($i=0;$i<=$CI->config->item('num_year_prediction');$i++)
            {
                ?>
                if(column=='<?php echo 'year'.$i.'_variance';?>')
                {
                    var variance=0;
                    if(!isNaN(parseFloat(record['<?php echo 'year'.$i.'_target_quantity_hom';?>'])))
                    {
                        variance=parseFloat(record['<?php echo 'year'.$i.'_target_quantity_hom';?>']);
                    }
                    <?php
                        foreach($areas as $area)
                        {
                        ?>
                    if(!isNaN(parseFloat(record['<?php echo 'year'.$i.'_target_quantity_'.$area['value'];?>'])))
                    {
                        variance-=parseFloat(record['<?php echo 'year'.$i.'_target_quantity_'.$area['value'];?>']);
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
                <?php
            }
            ?>

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
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?>',pinned:true, dataField: 'type_name',width:'100',cellsrenderer: cellsrenderer,align:'center',editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>',pinned:true, dataField: 'variety_name',width:'150',cellsrenderer: cellsrenderer,align:'center',editable:false},
                    <?php
                        for($i=0;$i<=$CI->config->item('num_year_prediction');$i++)
                        {?>{ columngroup: '<?php echo 'year'.$i.'_id'; ?>',text: 'HOM',dataField: '<?php echo 'year'.$i.'_target_quantity_hom';?>',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                            <?php
                            foreach($areas as $area)
                            {?>{ columngroup: '<?php echo 'year'.$i.'_id'; ?>',text: '<?php echo $area['text']; ?>',dataField: '<?php echo 'year'.$i.'_target_quantity_'.$area['value'];?>',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:true,columntype:'custom',
                                    cellbeginedit: function (row)
                                    {
                                        var selectedRowData = $('#system_jqx_container').jqxGrid('getrowdata', row);
                                        return selectedRowData['<?php echo 'year'.$i.'_target_quantity_'.$area['value'].'_editable';?>'];
                                    },
                                    initeditor: function (row, cellvalue, editor, celltext, pressedkey) {

                                        editor.html('<div style="margin: 0px;width: 100%;height: 100%;padding: 5px;"><input type="text" value="'+cellvalue+'" class="jqxgrid_input integer_type_positive"><div>');
                                    },
                                    geteditorvalue: function (row, cellvalue, editor)
                                    {
                                        return editor.find('input').val();
                                    }
                                },
                                <?php
                            }
                            ?>
                            { columngroup: '<?php echo 'year'.$i.'_id'; ?>',text: 'Variance',dataField: '<?php echo 'year'.$i.'_variance';?>',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                        <?php
                        }
                    ?>
                ],
                columngroups:
                    [
                        { text: '<?php echo $CI->lang->line('LABEL_BUDGETED_YEAR'); ?>', align: 'center', name: 'budgeted_year' },
                        { text: '<?php echo $CI->lang->line('LABEL_NEXT_YEARS'); ?>', align: 'center', name: 'next_years' },
                            <?php
                            for($i=1;$i<=$CI->config->item('num_year_prediction');$i++)
                            {?>{ text: '<?php echo $years[$i]['text']; ?>', align: 'center',parentgroup:'next_years', name: '<?php echo 'year'.$i.'_id'; ?>' },
                        <?php
                            }
                        ?>
                        { text: '<?php echo $years[0]['text']; ?>', align: 'center',parentgroup:'budgeted_year', name: 'year0_id' }

                    ]
            });

    });
</script>