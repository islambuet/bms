<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
    $action_data=array();
    $action_data["action_back"]=base_url($CI->controller_url);
    if((isset($this->permissions['edit'])&&($this->permissions['edit']==1))||(isset($this->permissions['add'])&&($this->permissions['add']==1)))
    {
        $action_data["action_edit_get"]=site_url($CI->controller_url."/index/edit/".$zone_id.'/'.$year0_id.'/'.$crop_id);
    }
    if(isset($CI->permissions['forward'])&&($CI->permissions['forward']==1))
    {
        $action_data["action_forward_get"]=site_url($CI->controller_url."/index/forward/".$zone_id.'/'.$year0_id.'/'.$crop_id);
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
                    <?php
                        foreach($areas as $area)
                        {?>{ name: '<?php echo 'area_quantity_'.$area['value'];?>', type: 'string' },
                    <?php
                        }
                        for($i=0;$i<=$CI->config->item('num_year_prediction');$i++)
                            {?>{ name: '<?php echo 'year'.$i.'_area_total_quantity';?>', type: 'string' },
                { name: '<?php echo 'year'.$i.'_budget_quantity';?>', type: 'string' },
                { name: '<?php echo 'year'.$i.'_budget_quantity_editable';?>', type: 'string' },
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
                    { text: '<?php echo $CI->lang->line('LABEL_SL_NO'); ?>',pinned:true, dataField: 'sl_no',width:'50',cellsrenderer: cellsrenderer,align:'center'},
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?>',pinned:true, dataField: 'type_name',width:'100',cellsrenderer: cellsrenderer,align:'center'},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>',pinned:true, dataField: 'variety_name',width:'150',cellsrenderer: cellsrenderer,align:'center'},
                        <?php
                            foreach($areas as $area)
                            {?>{ columngroup: 'area',text: '<?php echo $area['text'];?>', dataField: '<?php echo 'area_quantity_'.$area['value'];?>',align:'center',width:'200',cellsrenderer: cellsrenderer,cellsAlign:'right'},
                    <?php
                        }
                    ?>
                        <?php
                            for($i=0;$i<=$CI->config->item('num_year_prediction');$i++)
                            {?>{ columngroup: '<?php echo 'year'.$i.'_id'; ?>',text: '<?php if($i>0){echo "Customer Prediction";}else{echo "Customer Budget";} ?>', dataField: '<?php echo 'year'.$i.'_area_total_quantity';?>',align:'center',width:'150',cellsrenderer: cellsrenderer,cellsAlign:'right',editable:false},
                    {
                        columngroup: '<?php echo 'year'.$i.'_id'; ?>',text: '<?php if($i>0){echo "TI Prediction";}else{echo "TI Budget";} ?>', dataField: '<?php echo 'year'.$i.'_budget_quantity';?>',align:'center',width:'150',cellsrenderer: cellsrenderer,cellsAlign:'right',columntype:'custom'},
                    <?php
                        }
                    ?>

                ],
                columngroups:
                    [
                        { text: 'Customers', align: 'center', name: 'area' },
                        { text: '<?php echo $CI->lang->line('LABEL_BUDGETED_YEAR'); ?>', align: 'center', name: 'budgeted_year' },
                        { text: '<?php echo $CI->lang->line('LABEL_NEXT_YEARS'); ?>', align: 'center', name: 'next_years' },
                            <?php
                            for($i=1;$i<=$CI->config->item('num_year_prediction');$i++)
                            {?>{ text: '<?php echo $years[$i]['text']; ?>', align: 'center',parentgroup:'next_years', name: '<?php echo 'year'.$i.'_id'; ?>' },
                        <?php
                            }
                        ?>
                        { text: '<?php echo $years[0]['text']; ?>', align: 'center',parentgroup:'budgeted_year', name: 'year0_id' }

                    ]
            });

    });
</script>