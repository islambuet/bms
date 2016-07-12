<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
    $action_data=array();
    $action_data["action_back"]=base_url($CI->controller_url);
    if(isset($CI->permissions['edit'])&&($CI->permissions['edit']==1))
    {
        $action_data["action_edit_get"]=site_url($CI->controller_url."/index/edit/".$consignment['id']);
    }
    $CI->load->view("action_buttons",$action_data);
?>

    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_FISCAL_YEAR');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $consignment['fiscal_year_name'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_PRINCIPAL_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $consignment['principal_name'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_MONTH_PURCHASE');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo date("M", mktime(0, 0, 0,  $consignment['month'],1, 2000));?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_DATE_PURCHASE');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo System_helper::display_date($consignment['date_purchase']);?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_CONSIGNMENT_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $consignment['name'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_CURRENCY_NAME');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $consignment['currency_name'];?></label>
            </div>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_CURRENCY_RATE');?><span style="color:#FF0000">*</span></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <label class="control-label"><?php echo $consignment['rate'];?></label>
            </div>
        </div>
        <?php
        foreach($direct_cost_items as $item)
        {
            ?>
            <div class="row show-grid">
                <div class="col-xs-4">
                    <label class="control-label pull-right"><?php  echo $item['text'];?></label>
                </div>
                <div class="col-sm-4 col-xs-8">
                    <label class="control-label"><?php if(isset($direct_costs[$item['value']])){echo $direct_costs[$item['value']]['cost'];}?></label>
                </div>
            </div>
        <?php
        }
        ?>
        <div class="widget-header">
            <div class="title">
                Varieties
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="col-xs-12" id="system_jqx_container">

        </div>
    </div>

    <div class="clearfix"></div>
<script type="text/javascript">
    $(document).ready(function ()
    {
        turn_off_triggers();
        //var grand_total_color='#AEC2DD';
        var grand_total_color='#AEC2DD';

        var url = "<?php echo base_url($CI->controller_url.'/index/get_varieties');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'crop_name', type: 'string' },
                { name: 'crop_type_name', type: 'string' },
                { name: 'variety_name', type: 'string' },
                { name: 'quantity', type: 'string' },
                { name: 'price', type: 'string' },
                    <?php
                        foreach($direct_cost_items as $item)
                        {?>{ name: '<?php echo 'dc_'.$item['value'];?>', type: 'string' },
                <?php
                    }
                    foreach($packing_items as $item)
                        {?>{ name: '<?php echo 'pack_'.$item['value'];?>', type: 'string' },
                <?php
                    }
                ?>

                { name: 'total_cogs', type: 'string' },
                { name: 'cogs', type: 'string' }
            ],
            id: 'id',
            url: url,
            type: 'POST',
            data:{consignment_id:'<?php echo $consignment['id']; ?>'}
        };
        var cellsrenderer = function(row, column, value, defaultHtml, columnSettings, record)
        {
            var element = $(defaultHtml);
            // console.log(defaultHtml);

            if (record.crop_type_name=="Total Crop")
            {
                if(column!='crop_name')
                {
                    element.css({ 'background-color': '#6CAB44','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }
            }
            else if (record.crop_name=="Grand Total")
            {

                element.css({ 'background-color': grand_total_color,'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});

            }
            else
            {
                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
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
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_NAME'); ?>', dataField: 'crop_name',align:'center',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,pinned:true},
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?>', dataField: 'crop_type_name',align:'center',width:'80',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,pinned:true},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>', dataField: 'variety_name',align:'center',width:'100',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,pinned:true},
                    { text: '<?php echo $CI->lang->line('LABEL_QUANTITY'); ?>', dataField: 'quantity',align:'center',width:'100',cellsalign: 'right',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    { text: 'PI Value', dataField: 'price',align:'center',width:'100',cellsalign: 'right',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                        <?php
                            foreach($direct_cost_items as $item)
                            {?>{ columngroup: 'direct_cost',text: '<?php echo $item['text']; ?>', dataField: '<?php echo 'dc_'.$item['value'];?>',align:'center',cellsalign: 'right',width:'100',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    <?php
                        }
                        foreach($packing_items as $item)
                        {?>{ columngroup: 'packing_cost',text: '<?php echo $item['text']; ?>', dataField: '<?php echo 'pack_'.$item['value'];?>',align:'center',cellsalign: 'right',width:'100',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    <?php
                        }
                        ?>
                    { text: 'Total COGS', dataField: 'total_cogs',align:'center',cellsalign: 'right',width:'100',cellsrenderer: cellsrenderer,rendered: tooltiprenderer},
                    { text: 'COGS', dataField: 'cogs',align:'center',cellsalign: 'right',width:'150',cellsrenderer: cellsrenderer,rendered: tooltiprenderer}
                ],
                columngroups:
                    [
                        { text: 'Direct Cost', align: 'center', name: 'direct_cost' },
                        { text: 'Packing Material Cost', align: 'center', name: 'packing_cost' }
                    ]


            });
    });
</script>
<script type="text/javascript">

    jQuery(document).ready(function()
    {


    });
</script>
