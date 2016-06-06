<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
    $action_data=array();
    $action_data["action_back"]=base_url($CI->controller_url);
    //$action_data["action_save"]='#save_form';
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
        /*$(document).on("click", "#button_action_save_jqx", function(event)
        {
            $("#system_loading").show();
            var data=$('#system_jqx_container').jqxGrid('getrows');
            for(var i=0;i<data.length;i++)
            {
                if(data[i]['variance_editable'])
                {
                    $('#save_form_jqx').append('<input type="hidden" id="items_'+data[i]['variety_id']+'_variance" name="items['+data[i]['variety_id']+']" value="'+data[i]['variance']+'">');
                }
            }
            $("#save_form_jqx").submit();

        });*/
        var url = "<?php echo site_url($CI->controller_url.'/index/get_edit_items');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'sl_no', type: 'int' },
                { name: 'type_name', type: 'string' },
                { name: 'variety_id', type: 'string' },
                { name: 'variety_name', type: 'string' },
                { name: 'year0_budget_quantity', type: 'string' },
                { name: 'cur_stock', type: 'string' },
                { name: 'min_stock', type: 'string' },
                { name: 'cur_variance', type: 'string' },
                { name: 'variance', type: 'string' },
                { name: 'quantity_needed', type: 'string' },
                { name: 'final_quantity', type: 'string' },
                { name: 'final_quantity_editable', type: 'string' }

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
                    { text: '<?php echo $CI->lang->line('LABEL_SL_NO'); ?>',pinned:true, dataField: 'sl_no',width:'50',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?>',pinned:true, dataField: 'type_name',width:'60',cellsrenderer: cellsrenderer,align:'center',editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>',pinned:true, dataField: 'variety_name',width:'150',cellsrenderer: cellsrenderer,align:'center',editable:false},
                    { text: 'HOM BUD',dataField: 'year0_budget_quantity',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    { text: 'Cur Stock',dataField: 'cur_stock',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    { text: 'Min Stock',dataField: 'min_stock',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    { text: 'Cur Variance',dataField: 'cur_variance',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    { text: 'Final Variance',dataField: 'variance',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    { text: 'Quantity Needed',dataField: 'quantity_needed',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    { text: 'Final Quantity',dataField: 'final_quantity',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:true,columntype:'custom',
                        cellbeginedit: function (row)
                        {
                            var selectedRowData = $('#system_jqx_container').jqxGrid('getrowdata', row);
                            return selectedRowData['final_quantity_editable'];
                        },
                        initeditor: function (row, cellvalue, editor, celltext, pressedkey) {

                            editor.html('<div style="margin: 0px;width: 100%;height: 100%;padding: 5px;"><input type="text" value="'+cellvalue+'" class="jqxgrid_input integer_type_all"><div>');
                        },
                        geteditorvalue: function (row, cellvalue, editor) {
                            // return the editor's value.
                            var value=editor.find('input').val();
                            var selectedRowData = $('#system_jqx_container').jqxGrid('getrowdata', row);

                            //$('#items_'+selectedRowData['variety_id']+'_variance').remove();
                            //$('#save_form').append('<input type="hidden" id="items_'+selectedRowData['variety_id']+'_variance" name="items['+selectedRowData['variety_id']+'][variance]" value="'+value+'">');
                            //$('#items_'+selectedRowData['variety_id']+'_variance').val(value);

                            return editor.find('input').val();
                        }
                    }
                ]
            });

    });
</script>