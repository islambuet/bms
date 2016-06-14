<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
    $action_data=array();
    $action_data["action_back"]=base_url($CI->controller_url);
    if((isset($this->permissions['edit'])&&($this->permissions['edit']==1))||(isset($this->permissions['add'])&&($this->permissions['add']==1)))
    {
        $action_data["action_edit_get"]=site_url($CI->controller_url."/index/edit/".$year0_id.'/'.$crop_id);
    }
    if(isset($CI->permissions['forward'])&&($CI->permissions['forward']==1))
    {
        $action_data["action_forward_get"]=site_url($CI->controller_url."/index/forward/".$year0_id.'/'.$crop_id);
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
        turn_off_triggers();
        var url = "<?php echo site_url($CI->controller_url.'/index/get_detail_items');?>";

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
                { name: 'year0_variance_quantity', type: 'string' },
                { name: 'min_stock', type: 'string' },
                { name: 'quantity_needed', type: 'string' },
                { name: 'year0_purchase_quantity', type: 'string' },
                { name: 'pq_fv', type: 'string' },
                { name: 'pq_fv_min', type: 'string' },
                { name: 'year0_target_quantity', type: 'string' },
                { name: 'year0_target_quantity_editable', type: 'string' }

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
                    { text: 'HOM BUD',dataField: 'year0_budget_quantity',width:'100',pinned:true,cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    { text: 'Final Variance',dataField: 'year0_variance_quantity',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    { text: 'Min Stock',dataField: 'min_stock',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    { text: 'Quantity Needed',dataField: 'quantity_needed',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    { text: 'Purchase Quantity',dataField: 'year0_purchase_quantity',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    { text: 'PQ+FV',dataField: 'pq_fv',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    { text: 'PQ+FV+MIN Stock',dataField: 'pq_fv_min',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false},
                    { text: 'Target Quantity',dataField: 'year0_target_quantity',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right',editable:false}                ]
            });

    });
</script>