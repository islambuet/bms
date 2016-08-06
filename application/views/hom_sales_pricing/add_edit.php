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
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="tp_last_year">Last Year TP</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="tp_management">Management TP</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="tp_hom">HOM TP</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="commission_hom">Commission %</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="sales_commission">Commission</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="incentive_hom">Incentive %</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="incentive">Incentive</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="net_price">Net Price</label>
        </div>
        <?php
    }
    ?>
    <div class="col-xs-12" id="system_jqx_container">

    </div>
</div>
<div class="clearfix"></div>
<script type="text/javascript">
    function calculate_total(row, tp_hom, sales_commission_percentage,incentive_percentage)
    {
        var row_data = $('#system_jqx_container').jqxGrid('getrowdata', row);

        var sales_commission=tp_hom*sales_commission_percentage/100;
        var incentive=tp_hom*incentive_percentage/100;

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
        var net_price=tp_hom-sales_commission-incentive;
        if(net_price!=0)
        {
            $("#system_jqx_container").jqxGrid('setcellvalue', row, 'net_price', number_format(net_price,2));
        }
        else
        {
            $("#system_jqx_container").jqxGrid('setcellvalue', row, 'net_price', '');
        }


    }
    $(document).ready(function ()
    {
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            // console.log(defaultHtml);

            element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','whiteSpace':'normal'});
            if(column=='tp_hom' ||column=='commission_hom')
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
                { name: 'variety_id', type: 'string' },
                { name: 'variety_name', type: 'string' },
                { name: 'tp_last_year', type: 'string' },
                { name: 'tp_management', type: 'string' },
                { name: 'tp_hom', type: 'string' },
                { name: 'commission_hom', type: 'string' },
                { name: 'sales_commission', type: 'string' },
                { name: 'incentive_hom', type: 'string' },
                { name: 'incentive', type: 'string' },
                { name: 'net_price', type: 'string' }
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
                    {text: 'Last Year TP', dataField: 'tp_last_year',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Management TP', dataField: 'tp_management',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'HOM TP', dataField: 'tp_hom',align:'center',cellsalign: 'right',width:'150',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:true,columntype:'custom',
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
                            if(!isNaN(parseFloat(row_data['commission_hom'])))
                            {
                                sales_commission_percentage=parseFloat(row_data['commission_hom']);
                            }
                            var incentive_percentage=0;
                            if(!isNaN(parseFloat(row_data['incentive_hom'])))
                            {
                                incentive_percentage=parseFloat(row_data['incentive_hom']);
                            }
                            calculate_total(row, value, sales_commission_percentage,incentive_percentage);
                        }
                    },
                    {text: 'Commission %', dataField: 'commission_hom',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:true,columntype:'custom',
                        initeditor: function (row, cellvalue, editor, celltext, pressedkey) {
                            editor.html('<div style="margin: 0px;width: 100%;height: 100%;padding: 5px;"><input type="text" value="'+cellvalue+'" class="jqxgrid_input float_type_positive"><div>');
                        },
                        geteditorvalue: function (row, cellvalue, editor) {
                            return editor.find('input').val();
                        },
                        cellendedit: function (row, column, editor,oldvalue,value)
                        {
                            var row_data = $('#system_jqx_container').jqxGrid('getrowdata', row);
                            var tp_hom=0;
                            if(!isNaN(parseFloat(row_data['tp_hom'])))
                            {
                                tp_hom=parseFloat(row_data['tp_hom']);
                            }
                            var incentive_percentage=0;
                            if(!isNaN(parseFloat(row_data['incentive_hom'])))
                            {
                                incentive_percentage=parseFloat(row_data['incentive_hom']);
                            }
                            calculate_total(row, tp_hom,value,incentive_percentage);
                        }
                    },
                    {text: 'Commission', dataField: 'sales_commission',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Incentive %', dataField: 'incentive_hom',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Incentive', dataField: 'incentive',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Net Price', dataField: 'net_price',align:'center',cellsalign: 'right',width:'110',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false}

                ]
            });

    });
</script>