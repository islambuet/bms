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

                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="kg_budgeted">Quantity Budgeted</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="kg_actual">Quantity Actual</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="kg_variance">Quantity Variance</label>

                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="pi_budgeted">PI Values Budgeted</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="pi_actual">PI Values Actual</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="pi_variance">PI Values Variance</label>

                <?php
                foreach($direct_costs as $cost)
                {?>
                    <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="<?php echo 'dc_'.$cost['value'].'_budgeted'; ?>"><?php echo $cost['text']; ?> Budgeted</label>
                    <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="<?php echo 'dc_'.$cost['value'].'_actual'; ?>"><?php echo $cost['text']; ?> Actual</label>
                    <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="<?php echo 'dc_'.$cost['value'].'_variance'; ?>"><?php echo $cost['text']; ?> Variance</label>
                    <?php
                }
                ?>
                <?php
                foreach($packing_costs as $cost)
                {?>
                    <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="<?php echo 'pc_'.$cost['value'].'_budgeted'; ?>"><?php echo $cost['text']; ?> Budgeted</label>
                    <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="<?php echo 'pc_'.$cost['value'].'_actual'; ?>"><?php echo $cost['text']; ?> Actual</label>
                    <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="<?php echo 'pc_'.$cost['value'].'_variance'; ?>"><?php echo $cost['text']; ?> Variance</label>
                <?php
                }
                ?>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="cogs_budgeted">COGS Budgeted</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="cogs_actual">COGS Actual</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="cogs_variance">COGS Variance</label>

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
                { name: 'kg_budgeted', type: 'string' },
                { name: 'kg_actual', type: 'string' },
                { name: 'kg_variance', type: 'string' },
                { name: 'pi_budgeted', type: 'string' },
                { name: 'pi_actual', type: 'string' },
                { name: 'pi_variance', type: 'string' },
                <?php
                    foreach($direct_costs as $cost)
                    {?>{ name: '<?php echo 'dc_'.$cost['value'].'_budgeted'; ?>', type: 'string' },
                { name: '<?php echo 'dc_'.$cost['value'].'_actual'; ?>', type: 'string' },
                { name: '<?php echo 'dc_'.$cost['value'].'_variance'; ?>', type: 'string' },
                        <?php
                    }
                ?>
                <?php
                    foreach($packing_costs as $cost)
                    {?>{ name: '<?php echo 'pc_'.$cost['value'].'_budgeted'; ?>', type: 'string' },
                { name: '<?php echo 'pc_'.$cost['value'].'_actual'; ?>', type: 'string' },
                { name: '<?php echo 'pc_'.$cost['value'].'_variance'; ?>', type: 'string' },
                        <?php
                    }
                ?>
                { name: 'cogs_budgeted', type: 'string' },
                { name: 'cogs_actual', type: 'string' },
                { name: 'cogs_variance', type: 'string' }

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
                rowsheight: 35,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_NAME'); ?>', dataField: 'crop_name',width: '80',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?>', dataField: 'type_name',width: '80',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>', dataField: 'variety_name',width: '130',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},

                    { columngroup: 'quantity',text: 'Budgeted', dataField: 'kg_budgeted',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right',aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { columngroup: 'quantity',text: 'Actual', dataField: 'kg_actual',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right',aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { columngroup: 'quantity',text: 'Variance', dataField: 'kg_variance',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right',aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { columngroup: 'pi',text: 'Budgeted', dataField: 'pi_budgeted',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right',aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { columngroup: 'pi',text: 'Actual', dataField: 'pi_actual',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right',aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { columngroup: 'pi',text: 'Variance', dataField: 'pi_variance',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right',aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    <?php
                        foreach($direct_costs as $cost)
                        {?>{ columngroup: '<?php echo 'direct_costs_'.$cost['value']; ?>',text: 'Budgeted', dataField: '<?php echo 'dc_'.$cost['value'].'_budgeted'; ?>',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right',aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { columngroup: '<?php echo 'direct_costs_'.$cost['value']; ?>',text: 'Actual', dataField: '<?php echo 'dc_'.$cost['value'].'_actual'; ?>',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right',aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { columngroup: '<?php echo 'direct_costs_'.$cost['value']; ?>',text: 'Variance', dataField: '<?php echo 'dc_'.$cost['value'].'_variance'; ?>',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right',aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                            <?php
                        }
                    ?>
                    <?php
                        foreach($packing_costs as $cost)
                        {?>{ columngroup: '<?php echo 'packing_costs_'.$cost['value']; ?>',text: 'Budgeted', dataField: '<?php echo 'pc_'.$cost['value'].'_budgeted'; ?>',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right',aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { columngroup: '<?php echo 'packing_costs_'.$cost['value']; ?>',text: 'Actual', dataField: '<?php echo 'pc_'.$cost['value'].'_actual'; ?>',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right',aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { columngroup: '<?php echo 'packing_costs_'.$cost['value']; ?>',text: 'Variance', dataField: '<?php echo 'pc_'.$cost['value'].'_variance'; ?>',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right',aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                            <?php
                        }
                    ?>
                    { columngroup: 'cogs',text: 'Budgeted', dataField: 'cogs_budgeted',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right',aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { columngroup: 'cogs',text: 'Actual', dataField: 'cogs_actual',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right',aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { columngroup: 'cogs',text: 'Variance', dataField: 'cogs_variance',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsalign: 'right',aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer}

                ],
                columngroups:
                    [
                        { text: 'Quantity', align: 'center', name: 'quantity' },
                        { text: 'PI Values', align: 'center', name: 'pi' },
                        { text: 'Direct Costs', align: 'center', name: 'direct_costs' },
                        <?php
                            foreach($direct_costs as $cost)
                            {?>{ text: '<?php echo $cost['text']; ?>', align: 'center',parentgroup:'direct_costs', name: '<?php echo 'direct_costs_'.$cost['value']; ?>' },
                                <?php
                            }
                        ?>
                        { text: 'Packing Costs', align: 'center', name: 'packing_costs' },
                        <?php
                            foreach($packing_costs as $cost)
                            {?>{ text: '<?php echo $cost['text']; ?>', align: 'center',parentgroup:'packing_costs', name: '<?php echo 'packing_costs_'.$cost['value']; ?>' },
                                <?php
                            }
                        ?>
                        { text: 'COGS', align: 'center', name: 'cogs' }
                    ]

            });
    });
</script>