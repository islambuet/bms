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
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="crop_name"><?php echo $CI->lang->line('LABEL_CROP_NAME'); ?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="type_name"><?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="variety_name"><?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="hom_target">Target</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="tp_last_year">Last Year TP</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="tp_management">Management TP</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="tp_hom">HOM TP</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="commission_hom">Commission %</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="sales_commission">Commission</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="incentive_hom">Incentive %</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="incentive">Incentive</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="net_price">Net Price</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="cogs">COGS</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="general">General</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="marketing">Marketing</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="finance">Finance</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="profit">Profit</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="total_profit">Total Profit</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="total_net_price">Total Net Price</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="profit_percentage">Profit %</label>

        </div>
        <?php
    }
    ?>
    <div class="col-xs-12" id="system_jqx_container">

    </div>
</div>
<div class="clearfix"></div>
<script type="text/javascript">
    function calculate_total(row, tp_mgt, sales_commission_percentage,incentive_percentage)
    {
        var row_data = $('#system_jqx_container').jqxGrid('getrowdata', row);


        var hom_target=0;
        if(!isNaN(parseFloat(row_data.hom_target.replace(/,/g,''))))
        {
            hom_target=parseFloat(row_data.hom_target.replace(/,/g,''));
        }
        var sales_commission=tp_mgt*sales_commission_percentage/100;
        var incentive=tp_mgt*incentive_percentage/100;

        if(sales_commission!=0)
        {
            $("#system_jqx_container").jqxGrid('setcellvalue', row, 'sales_commission', number_format(sales_commission,2));
        }
        else
        {
            $("#system_jqx_container").jqxGrid('setcellvalue', row, 'sales_commission', '');
        }
        if(incentive!=0)
        {
            $("#system_jqx_container").jqxGrid('setcellvalue', row, 'incentive', number_format(incentive,2));
        }
        else
        {
            $("#system_jqx_container").jqxGrid('setcellvalue', row, 'incentive', '');
        }
        var net_price=tp_mgt-sales_commission-incentive;
        if(net_price!=0)
        {
            $("#system_jqx_container").jqxGrid('setcellvalue', row, 'net_price', number_format(net_price,2));
            $("#system_jqx_container").jqxGrid('setcellvalue', row, 'total_net_price', number_format(net_price*hom_target,2));
        }
        else
        {
            $("#system_jqx_container").jqxGrid('setcellvalue', row, 'net_price', '');
            $("#system_jqx_container").jqxGrid('setcellvalue', row, 'total_net_price', '');
        }
        var cogs=0;
        if(!isNaN(parseFloat(row_data.cogs.replace(/,/g,''))))
        {
            cogs=parseFloat(row_data.cogs.replace(/,/g,''));
        }
        var general=0;
        if(!isNaN(parseFloat(row_data.general.replace(/,/g,''))))
        {
            general=parseFloat(row_data.general.replace(/,/g,''));
        }
        var marketing=0;
        if(!isNaN(parseFloat(row_data.marketing.replace(/,/g,''))))
        {
            marketing=parseFloat(row_data.marketing.replace(/,/g,''));
        }
        var finance=0;
        if(!isNaN(parseFloat(row_data.finance.replace(/,/g,''))))
        {
            finance=parseFloat(row_data.finance.replace(/,/g,''));
        }
        var profit=net_price-cogs-general-marketing-finance;
        if(profit!=0)
        {
            $("#system_jqx_container").jqxGrid('setcellvalue', row, 'profit', number_format(profit,2));
            $("#system_jqx_container").jqxGrid('setcellvalue', row, 'total_profit', number_format(profit*hom_target,2));
        }
        else
        {
            $("#system_jqx_container").jqxGrid('setcellvalue', row, 'profit', '');
            $("#system_jqx_container").jqxGrid('setcellvalue', row, 'total_profit', '');
        }
        if(net_price!=0)
        {
            $("#system_jqx_container").jqxGrid('setcellvalue', row, 'profit_percentage', number_format(profit*100/net_price,2));
        }
        else
        {
            $("#system_jqx_container").jqxGrid('setcellvalue', row, 'profit_percentage', '');

        }

    }
    $(document).ready(function ()
    {
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            // console.log(defaultHtml);

            if (record.profit_percentage<0)
            {
                element.css({ 'background-color': '#FE4638','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            else if (record.profit_percentage<5)
            {
                element.css({ 'background-color': '#FEE3B4','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            else if (record.profit_percentage<10)
            {
                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','whiteSpace':'normal'});
            }
            else if (record.profit_percentage<15)
            {
                element.css({ 'background-color': '#65ACFB','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            else
            {
                element.css({ 'background-color': '#88E87E','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            if (record.variety_name=="Total Type")
            {
                if(!((column=='crop_name')||(column=='type_name')))
                {
                    element.css({ 'color': '#0000FF','font-weight': 'bold'});
                }
            }
            else if (record.type_name=="Total Crop")
            {
                if((column!='crop_name'))
                {
                    element.css({ 'color': '#0000FF','font-weight': 'bold'});

                }

            }
            else if (record.crop_name=="Grand Total")
            {
                element.css({ 'color': '#0000FF','font-weight': 'bold'});
            }
            if(column=='tp_mgt' ||column=='sales_commission_percentage' ||column=='incentive_percentage')
            {
                element.html('<div class="jqxgrid_input">'+value+'</div>');
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
            return '<div style="position: relative; margin: 0px;padding: 5px;width: 100%;height: 100%; overflow: hidden;background-color:#AEC2DD;">' +aggregates['total']+'</div>';

        };

        var url = "<?php echo site_url($CI->controller_url."/index/get_items/");?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'crop_name', type: 'string' },
                { name: 'type_name', type: 'string' },
                { name: 'variety_id', type: 'string' },
                { name: 'variety_name', type: 'string' },
                { name: 'hom_target', type: 'string' },
                { name: 'tp_last_year', type: 'string' },
                { name: 'tp_management', type: 'string' },
                { name: 'tp_hom', type: 'string' },
                { name: 'commission_hom', type: 'string' },
                { name: 'sales_commission', type: 'string' },
                { name: 'incentive_hom', type: 'string' },
                { name: 'incentive', type: 'string' },
                { name: 'net_price', type: 'string' },
                { name: 'cogs', type: 'string' },
                { name: 'general', type: 'string' },
                { name: 'marketing', type: 'string' },
                { name: 'finance', type: 'string' },
                { name: 'profit', type: 'string' },
                { name: 'total_net_price', type: 'string' },
                { name: 'total_profit', type: 'string' },
                { name: 'profit_percentage', type: 'string' }

            ],
            id: 'id',
            url: url,
            type: 'POST',
            data:{<?php echo $keys; ?>}
        };
        var dataAdapter = new $.jqx.dataAdapter(source);

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
                showstatusbar: true,
                showaggregates: true,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_NAME'); ?>', dataField: 'crop_name',width: '100',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?>', dataField: 'type_name',width: '100',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>', dataField: 'variety_name',width: '130',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    {text: 'Target', dataField: 'hom_target',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'Last Year TP', dataField: 'tp_last_year',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'Management TP', dataField: 'tp_management',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'Hom TP', dataField: 'tp_hom',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'Commission %', dataField: 'commission_hom',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'Commission', dataField: 'sales_commission',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'Incentive %', dataField: 'incentive_hom',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'Incentive', dataField: 'incentive',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'Net Price', dataField: 'net_price',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'COGS', dataField: 'cogs',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'General', dataField: 'general',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'Marketing', dataField: 'marketing',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'Finance', dataField: 'finance',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'Profit', dataField: 'profit',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    {text: 'Total Profit', dataField: 'total_profit',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    {text: 'Total Net Price', dataField: 'total_net_price',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    {text: 'Profit %', dataField: 'profit_percentage',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer}

                ]
            });

    });
</script>