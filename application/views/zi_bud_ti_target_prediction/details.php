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
                { name: 'variety_name', type: 'string' },
                <?php
                    for($i=0;$i<=$CI->config->item('num_year_prediction');$i++)
                    {?>{ name: '<?php echo 'year'.$i.'_target_quantity_hom';?>', type: 'string' },
                        <?php
                        foreach($areas as $area)
                        {?>{ name: '<?php echo 'year'.$i.'_target_quantity_'.$area['value'];?>', type: 'string' },
                        { name: '<?php echo 'year'.$i.'_target_quantity_'.$area['value'].'_editable';?>', type: 'string' },
                        <?php
                        }
                    }
                ?>
                { name: 'variety_id', type: 'string' }
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
            element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            <?php
            for($i=0;$i<=$CI->config->item('num_year_prediction');$i++)
            {
                    ?>
                if(column=='<?php echo 'year'.$i.'_variance';?>')
                {
                    var variance=0;
                    if(!isNaN(parseFloat(record['<?php echo 'year'.$i.'_target_quantity_hom';?>'])))
                    {
                        variance=parseFloat(record['<?php echo 'year'.$i.'_target_quantity_hom';?>']);
                    }
                    <?php
                        foreach($areas as $area)
                        {
                        ?>
                    if(!isNaN(parseFloat(record['<?php echo 'year'.$i.'_target_quantity_'.$area['value'];?>'])))
                    {
                        variance-=parseFloat(record['<?php echo 'year'.$i.'_target_quantity_'.$area['value'];?>']);
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
                }
                <?php
            }
            ?>

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
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?>',pinned:true, dataField: 'type_name',width:'100',cellsrenderer: cellsrenderer,align:'center'},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>',pinned:true, dataField: 'variety_name',width:'150',cellsrenderer: cellsrenderer,align:'center'},
                        <?php
                            for($i=0;$i<=$CI->config->item('num_year_prediction');$i++)
                            {?>{ columngroup: '<?php echo 'year'.$i.'_id'; ?>',text: 'ZI',dataField: '<?php echo 'year'.$i.'_target_quantity_hom';?>',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'},
                        <?php
                        foreach($areas as $area)
                        {?>{ columngroup: '<?php echo 'year'.$i.'_id'; ?>',text: '<?php echo $area['text']; ?>',dataField: '<?php echo 'year'.$i.'_target_quantity_'.$area['value'];?>',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'},
                    <?php
                }
                ?>
                    { columngroup: '<?php echo 'year'.$i.'_id'; ?>',text: 'Variance',dataField: '<?php echo 'year'.$i.'_variance';?>',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'},
                    <?php
                    }
                ?>
                ],
                columngroups:
                    [
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