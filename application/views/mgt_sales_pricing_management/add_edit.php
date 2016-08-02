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
    $(document).ready(function ()
    {
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
            if(column=='tp_mgt' ||column=='sales_commission_percentage' ||column=='incentive_percentage')
            {
                element.html('<div class="jqxgrid_input">'+value+'</div>');
            }

            return element[0].outerHTML;

        };
        var tooltiprenderer = function (element) {
            $(element).jqxTooltip({position: 'mouse', content: $(element).text() });
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
                editable:true,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?>', dataField: 'type_name',width: '80',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer,editable:false},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>', dataField: 'variety_name',width: '130',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer,editable:false},
                    {text: 'Target', dataField: 'hom_target',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Last Year TP', dataField: 'tp_last_year',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Automated TP', dataField: 'tp_automated',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Management TP', dataField: 'tp_mgt',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Commission %', dataField: 'sales_commission_percentage',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Commission', dataField: 'sales_commission',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Incentive %', dataField: 'incentive_percentage',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Incentive', dataField: 'incentive',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Net Price', dataField: 'net_price',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'COGS', dataField: 'cogs',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'General', dataField: 'general',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Marketing', dataField: 'marketing',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Finance', dataField: 'finance',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Profit', dataField: 'profit',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Total Net Price', dataField: 'total_net_price',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Total Profit', dataField: 'total_profit',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false},
                    {text: 'Profit %', dataField: 'profit_percentage',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,editable:false}

                ]
            });

    });
</script>