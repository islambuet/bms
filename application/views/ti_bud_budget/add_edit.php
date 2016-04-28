<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
?>
<form class="form_valid" id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" name="setup_id" value="<?php echo $setup_id; ?>" />
    <input type="hidden" name="crop_id" value="<?php echo $crop_id; ?>" />
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

    <div class="clearfix"></div>
</form>
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
                { name: 'variety_name', type: 'string' },
                    <?php
                        foreach($customers as $customer)
                        {?>{ name: '<?php echo 'customer_quantity_'.$customer['value'];?>', type: 'string' },
                <?php
                    }
                    for($i=1;$i<=$CI->config->item('num_year_prediction');$i++)
                        {?>{ name: '<?php echo 'year'.$i.'_customer_total_quantity';?>', type: 'string' },
                { name: '<?php echo 'year'.$i.'_budget_quantity';?>', type: 'string' },
                <?php
                    }
                ?>
                { name: 'sl_no', type: 'int' },
                { name: 'customer_total_quantity', type: 'string' },
                { name: 'budget_quantity', type: 'string' }
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
            return element[0].outerHTML;

        };
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                autoHeight:true,
                source: dataAdapter,
                columnsresize: true,
                columnsreorder: true,
                altrows: true,
                rowsheight: 35,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?>',pinned:true, dataField: 'crop_type_name',width:'150',cellsrenderer: cellsrenderer,align:'center'},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>',pinned:true, dataField: 'variety_name',width:'150',cellsrenderer: cellsrenderer,align:'center'},
                    <?php
                        foreach($customers as $customer)
                        {?>{ columngroup: 'customers',text: '<?php echo $customer['text'];?>', dataField: '<?php echo 'customer_quantity_'.$customer['value'];?>',align:'center',width:'200',cellsrenderer: cellsrenderer,cellsAlign:'right'},
                    <?php
                        }
                    ?>
                    { columngroup: 'fiscal_year_id',text: 'Customer Total', dataField: 'customer_total_quantity',align:'center',width:'150',cellsrenderer: cellsrenderer,cellsAlign:'right'},
                    { columngroup: 'fiscal_year_id',text: 'TI Budget', dataField: 'budget_quantity',align:'center',width:'150',cellsrenderer: cellsrenderer,cellsAlign:'right'},
                        <?php
                            for($i=1;$i<=$CI->config->item('num_year_prediction');$i++)
                            {?>{ columngroup: '<?php echo 'year'.$i.'_id'; ?>',text: 'Customer Prediction', dataField: '<?php echo 'year'.$i.'_customer_total_quantity';?>',align:'center',width:'150',cellsrenderer: cellsrenderer,cellsAlign:'right'},
                    { columngroup: '<?php echo 'year'.$i.'_id'; ?>',text: 'TI Prediction', dataField: '<?php echo 'year'.$i.'_budget_quantity';?>',align:'center',width:'150',cellsrenderer: cellsrenderer,cellsAlign:'right'},
                    <?php
                        }
                    ?>

                ],
                columngroups:
                    [
                        { text: 'Customers', align: 'center', name: 'customers' },
                        { text: '<?php echo $CI->lang->line('LABEL_BUDGETED_YEAR'); ?>', align: 'center', name: 'budgeted_year' },
                        { text: '<?php echo $CI->lang->line('LABEL_NEXT_YEARS'); ?>', align: 'center', name: 'next_years' },
                            <?php
                            for($i=1;$i<=$CI->config->item('num_year_prediction');$i++)
                            {?>{ text: '<?php echo $years['year'.$i.'_id']['text']; ?>', align: 'center',parentgroup:'next_years', name: '<?php echo 'year'.$i.'_id'; ?>' },
                        <?php
                            }
                        ?>
                        { text: '<?php echo $years['fiscal_year_id']['text']; ?>', align: 'center',parentgroup:'budgeted_year', name: 'fiscal_year_id' }

                    ]
            });

    });
</script>