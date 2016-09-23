<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
    $action_data=array();
    if(isset($CI->permissions['print'])&&($CI->permissions['print']==1))
    {
        $action_data["action_print"]='print';
    }
    if(isset($CI->permissions['download'])&&($CI->permissions['download']==1))
    {
        $action_data["action_csv"]='csv';
    }
    if(sizeof($action_data)>0)
    {
        $CI->load->view("action_buttons",$action_data);
    }

?>

<div class="row widget">
    <div class="widget-header">
        <div class="title">
            <?php echo $title; ?>
        </div>
        <div class="clearfix"></div>
    </div>
    <?php
    if(isset($CI->permissions['column_headers'])&&($CI->permissions['column_headers']==1))
    {

        ?>
        <div class="col-xs-12" style="margin-bottom: 20px;">
            <div class="col-xs-12" style="margin-bottom: 20px;">
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="crop_name"><?php echo $CI->lang->line('LABEL_CROP_NAME'); ?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="type_name"><?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="variety_name"><?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="price_final">Final Price</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="target_kg">Target (Kg)</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="sales_kg">Sales (Kg)</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="variance_kg">Variance (Kg)</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="cogs_budgeted">COGS Budgeted</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="cogs_actual">COGS Actual</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="cogs_variance">COGS Variance</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="np_kg_budgeted">NP/KG Budgeted</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="np_kg_actual">NP/KG Actual</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="np_kg_variance">NP/KG Variance</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="np_total_budgeted">NP Total Budgeted</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="np_total_actual">NP Total Actual</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="np_total_variance">NP Total Variance</label>

            </div>
        </div>
    <?php
    }
    ?>
    <div class="col-xs-12" id="system_jqx_container">

    </div>
</div>
<div class="clearfix"></div>
<script type="text/javascript">
    $(document).ready(function ()
    {

        //var grand_total_color='#AEC2DD';
        var grand_total_color='#AEC2DD';

        var url = "<?php echo base_url($CI->controller_url.'/index/get_items');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'crop_name', type: 'string' },
                { name: 'type_name', type: 'string' },
                { name: 'variety_name', type: 'string' },
                { name: 'price_final', type: 'string' },
                { name: 'target_kg', type: 'string' },
                { name: 'sales_kg', type: 'string' },
                { name: 'variance_kg', type: 'string' },
                { name: 'cogs_budgeted', type: 'string' },
                { name: 'cogs_actual', type: 'string' },
                { name: 'cogs_variance', type: 'string' },
                { name: 'np_kg_budgeted', type: 'string' },
                { name: 'np_kg_actual', type: 'string' },
                { name: 'np_kg_variance', type: 'string' },
                { name: 'np_total_budgeted', type: 'string' },
                { name: 'np_total_actual', type: 'string' },
                { name: 'np_total_variance', type: 'string' }
            ],
            id: 'id',
            url: url,
            type: 'POST',
            data:{<?php echo $keys; ?>}
        };
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            // console.log(defaultHtml);

            if((column=='target_net')&&(record.target_net==""))
            {
                element.css({ 'background-color': '#FF0000','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            else if (record.variety_name=="Total Type")
            {
                if(!((column=='crop_name')||(column=='type_name')))
                {
                    element.css({ 'background-color': '#6CAB44','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }
            }
            else if (record.type_name=="Total Crop")
            {
                if((column!='crop_name'))
                {
                    element.css({ 'background-color': '#0CA2C5','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});

                }
            }
            else if (record.crop_name=="Grand Total")
            {

                element.css({ 'background-color': grand_total_color,'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            else
            {
                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','whiteSpace':'normal'});
            }

            return element[0].outerHTML;

        };
        var tooltiprenderer = function (element) {
            $(element).jqxTooltip({position: 'mouse', content: $(element).text() });
        };
        var aggregates=function (total, column, element, record)
        {
            if(record.crop_name=="Grand Total")
            {
                //console.log(element);
                return record[element];

            }
            return total;
            //return grand_starting_stock;
        };
        var aggregatesrenderer=function (aggregates)
        {
            return '<div style="position: relative; margin: 0px;padding: 5px;width: 100%;height: 100%; overflow: hidden;background-color:'+grand_total_color+';">' +aggregates['total']+'</div>';

        };
        var dataAdapter = new $.jqx.dataAdapter(source);
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                height:'350px',
                source: dataAdapter,
                columnsresize: true,
                columnsreorder: true,
                altrows: true,
                enabletooltips: true,
                showaggregates: true,
                showstatusbar: true,
                rowsheight: 25,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_NAME'); ?>', dataField: 'crop_name',width: '80',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?>', dataField: 'type_name',width: '80',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>', dataField: 'variety_name',width: '130',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { columngroup: 'quantity',text: 'Final Price', dataField: 'price_final',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right'},
                    { columngroup: 'quantity',text: 'Target (Kg)', dataField: 'target_kg',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right'},
                    { columngroup: 'quantity',text: 'Sales (Kg)', dataField: 'sales_kg',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right'},
                    { columngroup: 'quantity',text: 'Variance (Kg)', dataField: 'variance_kg',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right'},
                    { columngroup: 'cogs',text: 'Budgeted', dataField: 'cogs_budgeted',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right'},
                    { columngroup: 'cogs',text: 'Actual', dataField: 'cogs_actual',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right'},
                    { columngroup: 'cogs',text: 'Variance', dataField: 'cogs_variance',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right'},
                    { columngroup: 'np_kg',text: 'Budgeted', dataField: 'np_kg_budgeted',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right'},
                    { columngroup: 'np_kg',text: 'Actual', dataField: 'np_kg_actual',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right'},
                    { columngroup: 'np_kg',text: 'Variance', dataField: 'np_kg_variance',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right'},
                    { columngroup: 'np_total',text: 'Budgeted', dataField: 'np_total_budgeted',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right'},
                    { columngroup: 'np_total',text: 'Actual', dataField: 'np_total_actual',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right'},
                    { columngroup: 'np_total',text: 'Variance', dataField: 'np_total_variance',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right'}
                ],
                columngroups:
                    [
                        { text: 'Quantity', align: 'center', name: 'quantity' },
                        { text: 'COGS', align: 'center', name: 'cogs' },
                        { text: 'NP/KG', align: 'center', name: 'np_kg' },
                        { text: 'NP Total', align: 'center', name: 'np_total' }
                    ]

            });
    });
</script>