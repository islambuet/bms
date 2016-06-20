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
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="sl_no"><?php echo $CI->lang->line('LABEL_SL_NO'); ?></label>
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

        var url = "<?php echo base_url($CI->controller_url.'/get_items');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'crop_name', type: 'string' },
                { name: 'crop_type_name', type: 'string' },
                { name: 'variety_name', type: 'string' },
                    <?php
                        foreach($months as $month)
                        {?>{ name: '<?php echo 'target_'.$month;?>', type: 'string' },
                { name: '<?php echo 'achieve_'.$month;?>', type: 'string' },
                { name: '<?php echo 'variance_'.$month;?>', type: 'string' },
                { name: '<?php echo 'net_'.$month;?>', type: 'string' },
                <?php
                    }
                ?>
                { name: 'target_total', type: 'string' },
                { name: 'achieve_total', type: 'string' },
                { name: 'variance_total', type: 'string' },
                { name: 'net_total', type: 'string' }
            ],
            id: 'id',
            url: url,
            type: 'POST',
            data:{<?php echo $keys; ?>}
        };
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            if (record.areas=="Total")
            {
                element.css({ 'background-color': grand_total_color,'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            else
            {
                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','whiteSpace':'normal'});
            }
            if(column=='sl')
            {
                if (record.areas!="Total")
                {
                    element.html(value+1);
                }

            }
            return element[0].outerHTML;

        };
        var tooltiprenderer = function (element) {
            $(element).jqxTooltip({position: 'mouse', content: $(element).text() });
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
                rowsheight: 35,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_NAME'); ?>', dataField: 'crop_name',width: '100',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?>', dataField: 'crop_type_name',width: '100',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>', dataField: 'variety_name',width: '100',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                        <?php
                            foreach($months as $month)
                            {?>{ columngroup: 'month_<?php echo $month; ?>',text: 'Target', dataField: 'target_<?php echo $month;?>',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    { columngroup: 'month_<?php echo $month; ?>',text: 'Achieved', dataField: 'achieve_<?php echo $month;?>',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    { columngroup: 'month_<?php echo $month; ?>',text: 'Variance', dataField: 'variance_<?php echo $month;?>',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    { columngroup: 'month_<?php echo $month; ?>',text: 'Net Price', dataField: 'net_<?php echo $month;?>',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    <?php
                        }
                    ?>
                    { columngroup: 'total',text: 'Target', dataField: 'target_total',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    { columngroup: 'total',text: 'Achieved', dataField: 'achieve_total',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    { columngroup: 'total',text: 'Variance', dataField: 'variance_total',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    { columngroup: 'total',text: 'Net Price', dataField: 'net_total',align:'center',cellsalign: 'right',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer}
                ],
                columngroups:
                [
                        <?php
                                foreach($months as $month)
                                {?>{ text: '<?php echo date("M", mktime(0, 0, 0,  $month,1, 2000));?>', align: 'center', name: 'month_<?php echo $month; ?>' },
                    <?php
                        }
                    ?>
                    { text: 'Total', align: 'center', name: 'total' }
                ]

            });
    });
</script>