<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
    $action_data=array();
    $action_data["action_save_jqx"]='#save_form_jqx';
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
    <?php
    if(isset($CI->permissions['column_headers'])&&($CI->permissions['column_headers']==1))
    {

        ?>
        <div class="col-xs-12" style="margin-bottom: 20px;">
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="type_name"><?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="variety_name"><?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?></label>

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

            if (record.profit_percentage<5)
            {
                element.css({ 'background-color': '#FF0000','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            else if (record.profit_percentage<10)
            {
                element.css({ 'background-color': '#0CA2C5','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});

            }
            else if (record.profit_percentage<15)
            {
                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','whiteSpace':'normal'});
            }
            else
            {
                element.css({ 'background-color': '#00FF00','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
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
        var aggregates=function (aggregatedValue, currentValue, column, record)
        {
            var total=0;
            var av=String(aggregatedValue);
            if(!isNaN(parseFloat(av.replace(/,/g,''))))
            {
                total=parseFloat(av.replace(/,/g,''));
            }
            var cv=String(record[column]);
            if(!isNaN(parseFloat(cv.replace(/,/g,''))))
            {
                total=total+parseFloat(cv.replace(/,/g,''));
            }
            //console.log(cv+' '+total);


            /*if(aggregatedValue.length>0)
            {
                if(!isNaN(parseFloat(aggregatedValue.replace(/,/g,''))))
                {
                    total=parseFloat(aggregatedValue.replace(/,/g,''));
                }
            }*/
            /*if(!isNaN(parseFloat(String(currentValue).replace(/,/g,''))))
            {
                console.log('parse');
                total=total+parseFloat(String(currentValue).replace(/,/g,''));
            }*/
            if(total!=0)
            {
                return number_format(total,2);
            }
            else
            {
                return '';
            }
            //return grand_starting_stock;
        };
        var aggregatesrenderer=function (aggregates)
        {
            return '<div style="position: relative; margin: 0px;padding: 5px;width: 100%;height: 100%; overflow: hidden;background-color:#AEC2DD;">' +aggregates['total']+'</div>';

        };

        var url = "<?php echo site_url($CI->controller_url."/index/get_edit_items/");?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'type_name', type: 'string' },
                { name: 'variety_name', type: 'string' },
                { name: 'hom_target', type: 'string' },
                { name: 'tp_last_year', type: 'string' },
                { name: 'tp_automated', type: 'string' },
                { name: 'tp_mgt', type: 'string' },
                { name: 'sales_commission_percentage', type: 'string' },
                { name: 'sales_commission', type: 'string' },
                { name: 'incentive_percentage', type: 'string' },
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
                editable:true,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?>', dataField: 'type_name',width: '80',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer,editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>', dataField: 'variety_name',width: '130',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer,editable:false},
                    {text: 'Target', dataField: 'hom_target',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Last Year TP', dataField: 'tp_last_year',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Automated TP', dataField: 'tp_automated',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Management TP', dataField: 'tp_mgt',align:'center',cellsalign: 'right',width:'150',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:true,columntype:'custom',
                        initeditor: function (row, cellvalue, editor, celltext, pressedkey) {
                            editor.html('<div style="margin: 0px;width: 100%;height: 100%;padding: 5px;"><input type="text" value="'+cellvalue+'" class="jqxgrid_input float_type_positive"><div>');
                        },
                        geteditorvalue: function (row, cellvalue, editor) {
                            return editor.find('input').val();
                        },
                        cellendedit: function (row, column, editor,oldvalue,value)
                        {
                            var row_data = $('#system_jqx_container').jqxGrid('getrowdata', row);
                            var sales_commission_percentage=0;
                            if(!isNaN(parseFloat(row_data['sales_commission_percentage'])))
                            {
                                sales_commission_percentage=parseFloat(row_data['sales_commission_percentage']);
                            }
                            var incentive_percentage=0;
                            if(!isNaN(parseFloat(row_data['incentive_percentage'])))
                            {
                                incentive_percentage=parseFloat(row_data['incentive_percentage']);
                            }
                            calculate_total(row, value, sales_commission_percentage,incentive_percentage);
                        }
                    },
                    {text: 'Commission %', dataField: 'sales_commission_percentage',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:true,columntype:'custom',
                        initeditor: function (row, cellvalue, editor, celltext, pressedkey) {
                            editor.html('<div style="margin: 0px;width: 100%;height: 100%;padding: 5px;"><input type="text" value="'+cellvalue+'" class="jqxgrid_input float_type_positive"><div>');
                        },
                        geteditorvalue: function (row, cellvalue, editor) {
                            return editor.find('input').val();
                        },
                        cellendedit: function (row, column, editor,oldvalue,value)
                        {
                            var row_data = $('#system_jqx_container').jqxGrid('getrowdata', row);
                            var tp_mgt=0;
                            if(!isNaN(parseFloat(row_data['tp_mgt'])))
                            {
                                tp_mgt=parseFloat(row_data['tp_mgt']);
                            }
                            var incentive_percentage=0;
                            if(!isNaN(parseFloat(row_data['incentive_percentage'])))
                            {
                                incentive_percentage=parseFloat(row_data['incentive_percentage']);
                            }
                            calculate_total(row, tp_mgt,value,incentive_percentage);
                        }
                    },
                    {text: 'Commission', dataField: 'sales_commission',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Incentive %', dataField: 'incentive_percentage',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:true,columntype:'custom',
                        initeditor: function (row, cellvalue, editor, celltext, pressedkey) {
                            editor.html('<div style="margin: 0px;width: 100%;height: 100%;padding: 5px;"><input type="text" value="'+cellvalue+'" class="jqxgrid_input float_type_positive"><div>');
                        },
                        geteditorvalue: function (row, cellvalue, editor) {
                            return editor.find('input').val();
                        },
                        cellendedit: function (row, column, editor,oldvalue,value)
                        {
                            var row_data = $('#system_jqx_container').jqxGrid('getrowdata', row);
                            var tp_mgt=0;
                            if(!isNaN(parseFloat(row_data['tp_mgt'])))
                            {
                                tp_mgt=parseFloat(row_data['tp_mgt']);
                            }
                            var sales_commission_percentage=0;
                            if(!isNaN(parseFloat(row_data['sales_commission_percentage'])))
                            {
                                sales_commission_percentage=parseFloat(row_data['sales_commission_percentage']);
                            }
                            calculate_total(row, tp_mgt, sales_commission_percentage,value);
                        }
                    },
                    {text: 'Incentive', dataField: 'incentive',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Net Price', dataField: 'net_price',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'COGS', dataField: 'cogs',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'General', dataField: 'general',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Marketing', dataField: 'marketing',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Finance', dataField: 'finance',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Profit', dataField: 'profit',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Total Net Price', dataField: 'total_net_price',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    {text: 'Total Profit', dataField: 'total_profit',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false,aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    {text: 'Profit %', dataField: 'profit_percentage',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false}

                ]
            });

    });
</script>