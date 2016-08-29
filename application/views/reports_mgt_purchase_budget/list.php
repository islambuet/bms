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
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="variety_import_name">Import Name</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="quantity_total">Purchase Quantity</label>
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
                { name: 'principal_name', type: 'string' },
                { name: 'variety_name', type: 'string' },
                { name: 'variety_import_name', type: 'string' },
                { name: 'month', type: 'string' },
                { name: 'quantity', type: 'string'},
                { name: 'currency_name', type: 'string'},
                { name: 'currency_rate', type: 'string'},
                { name: 'unit_price', type: 'string'},
                <?php
                    foreach($direct_costs as $cost)
                    {?>{ name: '<?php echo 'dc_'.$cost['value'];?>', type: 'string' },
                        <?php
                    }
                    foreach($packing_costs as $cost)
                    {?>{ name: '<?php echo 'pc_'.$cost['value'];?>', type: 'string' },
                        <?php
                    }
                ?>
                { name: 'bank', type: 'string'},
                { name: 'cogs', type: 'string'},
                { name: 'total_cogs', type: 'string'}
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
                    { text: '<?php echo $CI->lang->line('LABEL_PRINCIPAL_NAME'); ?>', dataField: 'principal_name',width: '130',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>', dataField: 'variety_name',width: '130',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: 'Import Name', dataField: 'variety_import_name',width: '130',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: 'Month', dataField: 'month',width: '40',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: 'Quantity', dataField: 'quantity',width: '100',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsAlign:'right'},
                    { text: 'Currency', dataField: 'currency_name',width: '50',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsAlign:'right'},
                    { text: 'C Rate', dataField: 'currency_rate',width: '60',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsAlign:'right'},
                    { text: 'Price/Kg', dataField: 'unit_price',width: '100',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsAlign:'right'},
                    <?php
                        foreach($direct_costs as $cost)
                        {?>{ text: '<?php echo $cost['text']; ?>', dataField: '<?php echo 'pc_'.$cost['value'];?>',width: '100',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsAlign:'right'},
                                <?php
                        }
                        foreach($packing_costs as $cost)
                        {?>{ text: '<?php echo $cost['text']; ?>', dataField: '<?php echo 'dc_'.$cost['value'];?>',width: '100',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsAlign:'right'},
                            <?php
                        }
                    ?>
                    { text: 'Bank', dataField: 'bank',width: '100',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsAlign:'right'},
                    { text: 'COGS', dataField: 'cogs',width: '100',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsAlign:'right'},
                    { text: 'Total Cogs', dataField: 'total_cogs',width: '100',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsAlign:'right'}
                ]

            });
    });
</script>