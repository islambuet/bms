<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
    $action_data=array();
    if(isset($CI->permissions['edit'])&&($CI->permissions['edit']==1))
    {
        $action_data["action_edit"]=base_url($CI->controller_url."/index/edit");
    }
    if(isset($CI->permissions['view'])&&($CI->permissions['view']==1))
    {
        $action_data["action_details"]=base_url($CI->controller_url."/index/details");
    }
    if(isset($CI->permissions['print'])&&($CI->permissions['print']==1))
    {
        $action_data["action_print"]='print';
    }
    if(isset($CI->permissions['download'])&&($CI->permissions['download']==1))
    {
        $action_data["action_csv"]='download';
    }
    $action_data["action_refresh"]=base_url($CI->controller_url."/index/list");
    $CI->load->view("action_buttons",$action_data);
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
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="fiscal_year_name"><?php echo $CI->lang->line('LABEL_FISCAL_YEAR'); ?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="principal_name"><?php echo $CI->lang->line('LABEL_PRINCIPAL_NAME'); ?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="name"><?php echo $CI->lang->line('LABEL_CONSIGNMENT_NAME'); ?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="month"><?php echo $CI->lang->line('LABEL_MONTH_PURCHASE'); ?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="date_purchase"><?php echo $CI->lang->line('LABEL_DATE_PURCHASE'); ?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="num_varieties">Num Varieties</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="total_quantity">Total Quantity</label>
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
        turn_off_triggers();
        var url = "<?php echo base_url($CI->controller_url.'/index/get_items');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'fiscal_year_name', type: 'string' },
                { name: 'principal_name', type: 'string' },
                { name: 'name', type: 'string' },
                { name: 'month', type: 'string' },
                { name: 'date_purchase', type: 'string' },
                { name: 'num_varieties', type: 'string' },
                { name: 'total_quantity', type: 'string' }
            ],
            id: 'id',
            url: url
        };

        var dataAdapter = new $.jqx.dataAdapter(source);
        // create jqxgrid.
        $("#system_jqx_container").jqxGrid(
            {
                width: '100%',
                source: dataAdapter,
                pageable: true,
                filterable: true,
                sortable: true,
                showfilterrow: true,
                columnsresize: true,
                pagesize:20,
                pagesizeoptions: ['20', '50', '100', '200','300','500'],
                selectionmode: 'singlerow',
                altrows: true,
                autoheight: true,
                autorowheight: true,
                columnsreorder: true,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_FISCAL_YEAR'); ?>', dataField: 'fiscal_year_name',width:'100',filtertype: 'list'},
                    { text: '<?php echo $CI->lang->line('LABEL_PRINCIPAL_NAME'); ?>', dataField: 'principal_name',width:'200',filtertype: 'list'},
                    { text: '<?php echo $CI->lang->line('LABEL_CONSIGNMENT_NAME'); ?>', dataField: 'name',width:'150'},
                    { text: '<?php echo $CI->lang->line('LABEL_MONTH_PURCHASE'); ?>', dataField: 'month',width:'150'},
                    { text: '<?php echo $CI->lang->line('LABEL_DATE_PURCHASE'); ?>', dataField: 'date_purchase',width:'150'},
                    { text: 'Num Varieties', dataField: 'num_varieties',width:'150',cellsalign: 'right'},
                    { text: 'Total Quantity', dataField: 'total_quantity'}
                ]
            });
    });
</script>