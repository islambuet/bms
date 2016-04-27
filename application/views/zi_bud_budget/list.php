<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();

    $action_data=array();
    if((isset($CI->permissions['add'])&&($CI->permissions['add']==1))||(isset($CI->permissions['edit'])&&($CI->permissions['edit']==1)))
    {
        $action_data["action_edit"]=base_url($CI->controller_url."/index/edit");
    }
    if(isset($CI->permissions['forward'])&&($CI->permissions['forward']==1))
    {
        $action_data["action_forward"]=base_url($CI->controller_url."/index/forward");
    }
    if(isset($CI->permissions['print'])&&($CI->permissions['print']==1))
    {
        $action_data["action_print"]='print';
    }
    if(isset($CI->permissions['download'])&&($CI->permissions['download']==1))
    {
        $action_data["action_csv"]='csv';
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
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="fiscal_year"><?php echo $CI->lang->line('LABEL_FISCAL_YEAR'); ?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="division_name"><?php echo $CI->lang->line('LABEL_DIVISION_NAME'); ?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="zone_name"><?php echo $CI->lang->line('LABEL_ZONE_NAME'); ?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="status_forward"><?php echo $CI->lang->line('LABEL_FORWARDED'); ?></label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="num_total_territories">Number of Territories in Zone</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="num_forwarded_territories">Number of Territories Forwarded</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="num_varieties_ti">#Varieties Budgeted by TI'</label>
            <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="num_varieties_zi">#Varieties Budgeted by ZI</label>
        </div>
        <?php
    }
    ?>
    <div class="col-xs-12" id="system_jqx_container">

    </div>
</div>
<div class="clearfix"></div>
<style type="text/css">
    .jqx-grid-header
    {
        height: 91px !important;
    }
</style>
<script type="text/javascript">
    $(document).ready(function ()
    {
        turn_off_triggers();
        var url = "<?php echo base_url($CI->controller_url.'/get_items');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'fiscal_year', type: 'string' },
                { name: 'division_name', type: 'string' },
                { name: 'zone_name', type: 'string' },
                { name: 'status_forward', type: 'string' },
                { name: 'num_total_territories', type: 'string' },
                { name: 'num_forwarded_territories', type: 'string' },
                { name: 'num_varieties_ti', type: 'string' },
                { name: 'num_varieties_zi', type: 'string' }

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
                pagesize:50,
                pagesizeoptions: ['20', '50', '100', '200','300','500'],
                selectionmode: 'checkbox',
                altrows: true,
                autoheight: true,
                autorowheight: true,
                columnsreorder: true,
                enabletooltips: true,
                columnsheight:'60px',
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_FISCAL_YEAR'); ?>', dataField: 'fiscal_year',width:'100',filtertype: 'list',align:'center'},
                    { text: '<?php echo $CI->lang->line('LABEL_DIVISION_NAME'); ?>', dataField: 'division_name',filtertype: 'list',width:'150',align:'center'},
                    { text: '<?php echo $CI->lang->line('LABEL_ZONE_NAME'); ?>', dataField: 'zone_name',width:'150',align:'center'},
                    { text: '<?php echo $CI->lang->line('LABEL_FORWARDED'); ?>', dataField: 'status_forward',width:'150',filtertype: 'list',align:'center'},
                    { text: 'Number of<br>Territories<br>in Zone', dataField: 'num_total_territories',width:'80',cellsAlign:'right',align:'center'},
                    { text: 'Number of <br>Territories<br>Forwarded', dataField: 'num_forwarded_territories',width:'80',cellsAlign:'right',align:'center'},
                    { text: '#Varieties<br>Budgeted<br>by TI', dataField: 'num_varieties_ti',width:'100',cellsAlign:'right',align:'center'},
                    { text: '#Varieties<br>Budgeted<br>by ZI', dataField: 'num_varieties_zi',width:'100',cellsAlign:'right',align:'center'}

                ]
            });

    });
</script>