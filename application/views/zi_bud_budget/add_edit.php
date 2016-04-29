<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$CI = & get_instance();
?>
<form class="form_valid" id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" name="setup_id" value="<?php echo $setup_id; ?>" />
    <input type="hidden" name="crop_id" value="<?php echo $crop_id; ?>" />
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
        var url = "<?php echo base_url($CI->controller_url.'/index/get_budget_form_items');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'crop_type_name', type: 'string' },
                { name: 'variety_id', type: 'string' },
                { name: 'variety_name', type: 'string' },
                    <?php
                        foreach($territories as $territory)
                        {?>{ name: '<?php echo 'territory_quantity_'.$territory['value'];?>', type: 'string' },
                    <?php
                        }
                        for($i=0;$i<=$CI->config->item('num_year_prediction');$i++)
                            {?>{ name: '<?php echo 'year'.$i.'_territory_total_quantity';?>', type: 'string' },
                { name: '<?php echo 'year'.$i.'_budget_quantity';?>', type: 'string' },
                { name: '<?php echo 'year'.$i.'_budget_quantity_editable';?>', type: 'string' },
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
            if (record.variety_name=="Type Total")
            {
                if(column!='crop_type_name')
                {
                    element.css({ 'background-color': '#6CAB44','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }
            }
            else if (record.crop_type_name=="Crop Total")
            {

                element.css({ 'background-color': '#AEC2DD','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});

            }
            else
            {
                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            if(record[column+'_editable'])
            {
                element.html('<div class="jqxgrid_input">'+value+'</div>');
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
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?>',pinned:true, dataField: 'crop_type_name',width:'150',cellsrenderer: cellsrenderer,align:'center',editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>',pinned:true, dataField: 'variety_name',width:'150',cellsrenderer: cellsrenderer,align:'center',editable:false},
                        <?php
                            foreach($territories as $territory)
                            {?>{ columngroup: 'territories',text: '<?php echo $territory['text'];?>', dataField: '<?php echo 'territory_quantity_'.$territory['value'];?>',align:'center',width:'200',cellsrenderer: cellsrenderer,cellsAlign:'right',editable:false},
                    <?php
                        }
                    ?>
                        <?php
                            for($i=0;$i<=$CI->config->item('num_year_prediction');$i++)
                            {?>{ columngroup: '<?php echo 'year'.$i.'_id'; ?>',text: '<?php if($i>0){echo "TI Prediction";}else{echo "TI Budget";} ?>', dataField: '<?php echo 'year'.$i.'_territory_total_quantity';?>',align:'center',width:'150',cellsrenderer: cellsrenderer,cellsAlign:'right',editable:false},
                    {
                        columngroup: '<?php echo 'year'.$i.'_id'; ?>',text: '<?php if($i>0){echo "ZI Prediction";}else{echo "ZI Budget";} ?>', dataField: '<?php echo 'year'.$i.'_budget_quantity';?>',align:'center',width:'150',cellsrenderer: cellsrenderer,cellsAlign:'right',columntype:'custom',
                        cellbeginedit: function (row)
                        {
                            var selectedRowData = $('#system_jqx_container').jqxGrid('getrowdata', row);//only last selected
                            return selectedRowData['<?php echo 'year'.$i.'_budget_quantity_editable';?>'];
                        },
                        initeditor: function (row, cellvalue, editor, celltext, pressedkey) {
                            editor.html('<div style="margin: 0px;width: 100%;height: 100%;padding: 5px;"><input type="text" value="'+cellvalue+'" class="jqxgrid_input integer_type_positive"><div>');
                        },
                        geteditorvalue: function (row, cellvalue, editor) {
                            // return the editor's value.
                            var value=editor.find('input').val();
                            var selectedRowData = $('#system_jqx_container').jqxGrid('getrowdata', row);
                            //console.log(selectedRowData);
                            $('#items_'+selectedRowData['variety_id']+'<?php echo 'year'.$i.'_budget_quantity';?>').remove();
                            $('#save_form').append('<input type="hidden" id="items_'+selectedRowData['variety_id']+'<?php echo 'year'.$i.'_budget_quantity';?>" name="items['+selectedRowData['variety_id']+'][<?php echo 'year'.$i.'_budget_quantity';?>]" value="'+value+'">');

                            return editor.find('input').val();
                        }
                    },
                    <?php
                        }
                    ?>

                ],
                columngroups:
                    [
                        { text: 'Territories', align: 'center', name: 'territories' },
                        { text: '<?php echo $CI->lang->line('LABEL_BUDGETED_YEAR'); ?>', align: 'center', name: 'budgeted_year' },
                        { text: '<?php echo $CI->lang->line('LABEL_NEXT_YEARS'); ?>', align: 'center', name: 'next_years' },
                            <?php
                            for($i=1;$i<=$CI->config->item('num_year_prediction');$i++)
                            {?>{ text: '<?php echo $years['year'.$i.'_id']['text']; ?>', align: 'center',parentgroup:'next_years', name: '<?php echo 'year'.$i.'_id'; ?>' },
                        <?php
                            }
                        ?>
                        { text: '<?php echo $years['year0_id']['text']; ?>', align: 'center',parentgroup:'budgeted_year', name: 'year0_id' }

                    ]
            });

    });
</script>