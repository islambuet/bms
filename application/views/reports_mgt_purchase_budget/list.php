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
        $(document).off("click", ".pop_up");
        $(document).on("click", ".pop_up", function(event)
        {
            var left=((($(window).width() - 550) / 2) +$(window).scrollLeft());
            var top=((($(window).height() - 550) / 2) +$(window).scrollTop());

            $("#popup_window").jqxWindow({position: { x: left, y: top  }});
            /*var row=$(this).attr('data-item-no');
            var row_info = $("#system_jqx_container").jqxGrid('getrowdata', row);
            var html='';
            html+='<div style="line-height: 1.8;">';
            html+='<div><b>Crop Name:</b> '+row_info.details['crop_name']+'<div>';
            html+='<div><b>Crop Type:</b> '+row_info.details['crop_type_name']+'<div>';
            html+='<div><b>Variety Name:</b> '+row_info.details['variety_name']+'<div>';
            html+='<div><b>Characteristics:</b><div>';
            html+='<div>'+row_info.details['characteristics']+'<div>';
            html+='<div><b>Cultivation Period:</b> '+row_info.details['cultivation_period']+'<div>';
            html+='<div><b>Compare With Other Variety:</b><div>';
            html+='<div>'+row_info.details['comparison']+'<div>';
            html+='<div><b>Remarks:</b> '+row_info.details['remarks']+'<div>';
            html+='<div><b>Picture:</b> <div>';
            html+='<div><img src="'+row_info.details['picture']+'" style="max-width: 100%;"></div>';

            html+='</div>';
            $('#popup_content').html(html);*/
            $('#popup_content').html('shaiful');
            $("#popup_window").jqxWindow('open');


        });

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
                { name: 'variety_import_name', type: 'string' },
                { name: 'quantity_total', type: 'string' }
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
            if(column=='details_button' && record.variety_name!='Total Type'&& record.variety_name!='')
            {
                element.html('<div><button class="btn btn-primary pop_up" data-item-no="'+row+'">Details</button></div>');
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
                rowsheight: 45,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_NAME'); ?>', dataField: 'crop_name',width: '80',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?>', dataField: 'type_name',width: '80',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>', dataField: 'variety_name',width: '130',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: 'Import Name', dataField: 'variety_import_name',width: '130',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: 'Purchase Quantity', dataField: 'quantity_total',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsAlign:'right'},
                    { text: 'Details', dataField: 'details_button',width: '100',cellsrenderer: cellsrenderer,rendered: tooltiprenderer}
                ]

            });
    });
</script>