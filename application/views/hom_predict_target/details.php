<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
    $action_data=array();
    $action_data["action_back"]=base_url($CI->controller_url);
    if((isset($this->permissions['edit'])&&($this->permissions['edit']==1))||(isset($this->permissions['add'])&&($this->permissions['add']==1)))
    {
        $action_data["action_edit_get"]=site_url($CI->controller_url."/index/edit/".$year0_id.'/'.$crop_id);
    }
    if(isset($CI->permissions['forward'])&&($CI->permissions['forward']==1))
    {
        $action_data["action_forward_get"]=site_url($CI->controller_url."/index/forward/".$year0_id.'/'.$crop_id);
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
        turn_off_triggers();
        var url = "<?php echo site_url($CI->controller_url.'/index/get_detail_items');?>";

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
                { name: 'year1_2_target_quantity', type: 'string' },
                { name: 'year1_1_target_quantity', type: 'string' },
                { name: 'year1_target_quantity', type: 'string' },
                { name: 'year1_target_quantity_editable', type: 'string' },
                { name: 'year2_1_target_quantity', type: 'string' },
                { name: 'year2_target_quantity', type: 'string' },
                { name: 'year2_target_quantity_editable', type: 'string' },
                { name: 'year3_target_quantity', type: 'string' },
                { name: 'year3_target_quantity_editable', type: 'string' }

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
                    { text: '<?php echo $CI->lang->line('LABEL_CROP_TYPE'); ?>',pinned:true, dataField: 'type_name',width:'100',cellsrenderer: cellsrenderer,align:'center'},
                    { text: '<?php echo $CI->lang->line('LABEL_VARIETY_NAME'); ?>',pinned:true, dataField: 'variety_name',width:'150',cellsrenderer: cellsrenderer,align:'center'},
                    { columngroup: 'year0_id',text: 'BUD',dataField: 'year0_budget_quantity',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'},
                    { columngroup: 'year0_id',text: 'Target',dataField: 'year0_target_quantity',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'},
                    { columngroup: 'year1_id',text: '2yr ago',dataField: 'year1_2_target_quantity',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'},
                    { columngroup: 'year1_id',text: '1yr ago',dataField: 'year1_1_target_quantity',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'},
                    { columngroup: 'year1_id',text: 'Target',dataField: 'year1_target_quantity',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'},
                    { columngroup: 'year2_id',text: '1yr ago',dataField: 'year2_1_target_quantity',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'},
                    { columngroup: 'year2_id',text: 'Target',dataField: 'year2_target_quantity',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'},
                    { columngroup: 'year3_id',text: 'Target',dataField: 'year3_target_quantity',width:'100',cellsrenderer: cellsrenderer,align:'center',cellsAlign:'right'}
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