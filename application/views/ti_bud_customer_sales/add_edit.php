<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
?>
<form class="form_valid" id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" name="setup_id" value="<?php echo $setup_id; ?>" />
    <input type="hidden" name="customer_id" value="<?php echo $customer_id; ?>" />
    <input type="hidden" id="system_save_new_status" name="system_save_new_status" value="0" />
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
        var url = "<?php echo base_url($CI->controller_url.'/index/get_customer_items');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'variety_name', type: 'string' },
                { name: 'sale_quantity', type: 'string' },
                    <?php
                        for($i=1;$i<=$CI->config->item('num_year_prediction');$i++)
                        {?>{ name: '<?php echo 'year'.$i.'_sale_quantity';?>', type: 'string' },
                <?php
                    }
                ?>
                { name: 'sl_no', type: 'int' }
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
            // console.log(defaultHtml);
                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            return element[0].outerHTML;

        };
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                autoheight: true,
                source: dataAdapter,
                columnsresize: true,
                columnsreorder: true,
                altrows: true,
                rowsheight: 35,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_SL_NO'); ?>',pinned:true, dataField: 'sl_no',width:'50',cellsrenderer: cellsrenderer,align:'center'},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>',pinned:true, dataField: 'variety_name',width:'150',cellsrenderer: cellsrenderer,align:'center'},
                    { text: '<?php echo $years['fiscal_year_id']['text']; ?>', dataField: 'sale_quantity',align:'center',width:'150',cellsrenderer: cellsrenderer,cellsAlign:'right'},
                    <?php
                        for($i=1;$i<=$CI->config->item('num_year_prediction');$i++)
                        {?>{ text: '<?php echo $years['year'.$i.'_id']['text']; ?>', dataField: '<?php echo 'year'.$i.'_sale_quantity';?>',align:'center',width:'150',cellsrenderer: cellsrenderer,cellsAlign:'right'},
                    <?php
                        }
                    ?>

                ]
            });

    });
</script>