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
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="crop_type_name"><?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="variety_name"><?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="hom_target">Target</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="cogs">COGS</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="general">General</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="marketing">Marketing</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="finance">Finance</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="profit">Profit</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="net_price">Net Price</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="sales_commission">Sales Commission</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="incentive">Incentive</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="trade_price">Trade Price</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="total_profit">Total Profit</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="total_net_price">Total Net Price</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="profit_percentage">Profit %</label>
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
        var grand_total_color='#AEC2DD';

        var url = "<?php echo base_url($CI->controller_url.'/index/get_items');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'crop_name', type: 'string' },
                { name: 'crop_type_name', type: 'string' },
                { name: 'variety_name', type: 'string' },
                { name: 'hom_target', type: 'string' },
                { name: 'cogs', type: 'string' },
                { name: 'general', type: 'string' },
                { name: 'marketing', type: 'string' },
                { name: 'finance', type: 'string' },
                { name: 'profit', type: 'string' },
                { name: 'net_price', type: 'string' },
                { name: 'sales_commission', type: 'string' },
                { name: 'incentive', type: 'string' },
                { name: 'trade_price', type: 'string' },
                { name: 'total_profit', type: 'string' },
                { name: 'total_net_price', type: 'string' },
                { name: 'profit_percentage', type: 'string' }
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

            if (record.variety_name=="Total Type")
            {
                if(!((column=='crop_name')||(column=='crop_type_name')))
                {
                    element.css({ 'background-color': '#6CAB44','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }
            }
            else if (record.crop_type_name=="Total Crop")
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
                rowsheight: 35,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_NAME'); ?>', dataField: 'crop_name',width: '80',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?>', dataField: 'crop_type_name',width: '80',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>', dataField: 'variety_name',width: '130',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    {text: 'Target', dataField: 'hom_target',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'COGS', dataField: 'cogs',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'General', dataField: 'general',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'Marketing', dataField: 'marketing',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'Finance', dataField: 'finance',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'Profit', dataField: 'profit',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'Net Price', dataField: 'net_price',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'Commission', dataField: 'sales_commission',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'Incentive', dataField: 'incentive',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'Trade Price', dataField: 'trade_price',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'Total Profit', dataField: 'total_profit',align:'center',cellsalign: 'right',width:'100',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'Total Net Price', dataField: 'total_net_price',align:'center',cellsalign: 'right',width:'130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'Profit %', dataField: 'profit_percentage',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer}

                ]

            });
    });
</script>