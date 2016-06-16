<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
    $action_data=array();
    $action_data["action_back"]=base_url($CI->controller_url);
    if((isset($this->permissions['edit'])&&($this->permissions['edit']==1))||(isset($this->permissions['add'])&&($this->permissions['add']==1)))
    {
        $action_data["action_edit_get"]=site_url($CI->controller_url."/index/edit/".$division_id.'/'.$year0_id.'/'.$crop_id);
    }
    if(isset($CI->permissions['forward'])&&($CI->permissions['forward']==1))
    {
        $action_data["action_forward_get"]=site_url($CI->controller_url."/index/forward/".$division_id.'/'.$year0_id.'/'.$crop_id);
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
    <div class="col-xs-12" id="system_jqx_container">

    </div>
</div>

<script type="text/javascript">
    $(document).ready(function ()
    {
        var url = "<?php echo site_url($CI->controller_url.'/index/get_edit_items');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'type_name', type: 'string' },
                { name: 'variety_id', type: 'string' },
                { name: 'variety_name', type: 'string' },
                { name: 'year0_budget_quantity', type: 'string' },
                { name: 'year0_target_quantity', type: 'string' },
                    <?php
                        foreach($areas as $area)
                        {?>{ name: '<?php echo 'year0_budget_quantity_'.$area['value'];?>', type: 'string' },
                { name: '<?php echo 'year0_target_quantity_'.$area['value'];?>', type: 'string' },
                { name: '<?php echo 'year0_target_quantity_'.$area['value'].'_editable';?>', type: 'string' },
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
            if (record.variety_name=="Total Type")
            {
                if(!((column=='sl_no')||(column=='type_name')))
                {
                    element.css({ 'background-color': '#6CAB44','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }
            }
            else if (record.type_name=="Total Crop")
            {
                if((column!='sl_no'))
                {
                    element.css({ 'background-color': '#0CA2C5','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});

                }
            }
            else if((column=='allocation_variance'))
            {
                var variance=0;
                if(!isNaN(parseFloat(record['year0_target_quantity'])))
                {
                    variance=parseFloat(record['year0_target_quantity']);
                }
                <?php
                    foreach($areas as $area)
                    {
                        ?>
                if(!isNaN(parseFloat(record['<?php echo 'year0_target_quantity_'.$area['value'];?>'])))
                {
                    variance-=parseFloat(record['<?php echo 'year0_target_quantity_'.$area['value'];?>']);
                }
                <?php
            }
            ?>

                if(variance==0)
                {
                    element.html('');
                    element.css({ 'background-color': '#00FF00','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }
                else
                {
                    element.html(variance);
                    element.css({ 'background-color': '#FF0000','margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
                }
                //$("#system_jqx_container").jqxGrid('setcellvalue', row, "allocation_variance", variance);
                //console.log(selectedRowData['year0_target_quantity']);

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
                height:'300',
                source: dataAdapter,
                columnsresize: true,
                columnsreorder: true,
                altrows: true,
                rowsheight: 35,
                columns: [
                    { text: '<?php echo $CI->lang->line('LABEL_SL_NO'); ?>',pinned:true, dataField: 'sl_no',width:'50',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'},
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?>',pinned:true, dataField: 'type_name',width:'60',cellsrenderer: cellsrenderer,align:'center'},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>',pinned:true, dataField: 'variety_name',width:'150',cellsrenderer: cellsrenderer,align:'center'},
                    { columngroup: 'incharge',text: 'Budget',dataField: 'year0_budget_quantity',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'},
                    { columngroup: 'incharge',text: 'Target',dataField: 'year0_target_quantity',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'},
                        <?php
                            foreach($areas as $area)
                            {?>{ columngroup: '<?php echo 'area_'.$area['value'];?>',text: 'Budget',dataField: '<?php echo 'year0_budget_quantity_'.$area['value'];?>',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'},
                    { columngroup: '<?php echo 'area_'.$area['value'];?>',text: 'Target',dataField: '<?php echo 'year0_target_quantity_'.$area['value'];?>',width:'110',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'},
                    <?php
                }
            ?>
                    { text: 'Variance', dataField: 'allocation_variance',cellsrenderer: cellsrenderer,align:'center'}
                ],
                columngroups:
                    [
                        { text: 'DI',align: 'center',name:'incharge'},
                            <?php
                                foreach($areas as $area)
                                {?>{ text: '<?php echo $area['text'];?>',align:'center',name:'<?php echo 'area_'.$area['value'];?>' },
                        <?php
                            }
                    ?>

                    ]
            });

    });
</script>