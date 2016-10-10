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
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="name">Customer Name</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="customer_code">Customer Code</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="division_name"><?php echo $CI->lang->line('LABEL_DIVISION_NAME');?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="zone_name"><?php echo $CI->lang->line('LABEL_ZONE_NAME');?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="territory_name"><?php echo $CI->lang->line('LABEL_TERRITORY_NAME');?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  value="district_name"><?php echo $CI->lang->line('LABEL_DISTRICT_NAME');?></label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="total_tp">Total Sales (TP)</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="total">Total Sales (NET)</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="total_po">Total # PO</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  value="opening_balance_tp">Opening Balance TP</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  value="opening_balance_net">Opening Balance Net</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  value="total_payment">Total Payment</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="balance_tp">Current Balance TP</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="balance_net">Current Balance NET</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="payment_percentage_tp">Payment % TP</label>
                <label class="checkbox-inline"><input type="checkbox" class="system_jqx_column"  checked value="payment_percentage_net">Payment % NET</label>
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

        var url = "<?php echo base_url($CI->controller_url.'/index/get_items_customer');?>";

        // prepare the data
        var source =
        {
            dataType: "json",
            dataFields: [
                { name: 'id', type: 'int' },
                { name: 'name', type: 'string' },
                { name: 'customer_code', type: 'string' },
                { name: 'division_name', type: 'string' },
                { name: 'zone_name', type: 'string' },
                { name: 'territory_name', type: 'string' },
                { name: 'district_name', type: 'string' },
                { name: 'total_tp', type: 'string'},
                { name: 'total', type: 'string'},
                { name: 'total_po', type: 'string'},

                { name: 'opening_balance_tp', type: 'string' },
                { name: 'opening_balance_net', type: 'string' },

                { name: 'total_payment', type: 'string' },

                { name: 'balance_tp', type: 'string' },
                { name: 'balance_net', type: 'string' },
                { name: 'payment_percentage_tp', type: 'string' },
                { name: 'payment_percentage_net', type: 'string' }
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

            if (record.name=="Total")
            {

                element.css({ 'background-color': grand_total_color,'margin': '0px','width': '100%', 'height': '100%',padding:'5px','line-height':'25px'});
            }
            else
            {
                element.css({'margin': '0px','width': '100%', 'height': '100%',padding:'5px','whiteSpace':'normal'});
            }
            return element[0].outerHTML;

        };
        var tooltiprenderer = function (element) {
            $(element).jqxTooltip({position: 'mouse', content: $(element).text() });
        };
        var aggregates=function (total, column, element, record)
        {
            if(record.name=="Total")
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
                height:'250px',
                source: dataAdapter,
                columnsresize: true,
                columnsreorder: true,
                altrows: true,
                enabletooltips: true,
                rowsheight: 35,
                columns: [
                    {
                        text: '<?php echo $CI->lang->line('LABEL_SL_NO'); ?>',datafield: '',pinned:true,width:'50', columntype: 'number',cellsalign: 'right',sortable:false,filterable:false,
                        cellsrenderer: function(row, column, value, defaultHtml, columnSettings, record)
                        {
                            var element = $(defaultHtml);
                            element.html(value+1);
                            return element[0].outerHTML;
                        }
                    },
                    { text: 'Customer Name', dataField: 'name',width: '200',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: 'Customer code', dataField: 'customer_code',width: '60',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_DIVISION_NAME');?>', dataField: 'division_name',width: '100',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_ZONE_NAME');?>', dataField: 'zone_name',width: '100',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_TERRITORY_NAME');?>', dataField: 'territory_name',width: '100',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: '<?php echo $CI->lang->line('LABEL_DISTRICT_NAME');?>',hidden:true, dataField: 'district_name',width: '100',cellsrenderer: cellsrenderer,pinned:true,rendered: tooltiprenderer},
                    { text: 'Total Sales (TP)', dataField: 'total_tp',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsAlign:'right',align:'center',aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { text: 'Total Sales (NET)', dataField: 'total',width: '130',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsAlign:'right',align:'center',aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { text: 'Total # PO', dataField: 'total_po',width: '60',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,cellsAlign:'right',align:'center',aggregates: [{ 'total':aggregates}],aggregatesrenderer:aggregatesrenderer},
                    { columngroup: 'opening_balance',text: 'TP',hidden:true,dataField: 'opening_balance_tp',width:'150',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,align:'center',cellsAlign:'right'},
                    { columngroup: 'opening_balance',text: 'NET',hidden:true,dataField: 'opening_balance_net',width:'150',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,align:'center',cellsAlign:'right'},
                    { text: 'Total Payment',hidden:true, dataField: 'total_payment',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,align:'center',cellsalign: 'right',width:'150'},
                    { columngroup: 'balance',text: 'TP', dataField: 'balance_tp',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,align:'center',cellsalign: 'right',width:'150'},
                    { columngroup: 'balance',text: 'NET', dataField: 'balance_net',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,align:'center',cellsalign: 'right',width:'150'},
                    { columngroup: 'payment_percentage',text: 'TP', dataField: 'payment_percentage_tp',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,align:'center',cellsalign: 'right',width:'80'},
                    { columngroup: 'payment_percentage',text: 'NET', dataField: 'payment_percentage_net',cellsrenderer: cellsrenderer,rendered: tooltiprenderer,align:'center',cellsalign: 'right',width:'80'}
                ],
                columngroups:
                    [
                        { text: 'Opening Balance', align: 'center', name: 'opening_balance' },
                        { text: 'Current Balance', align: 'center', name: 'balance' },
                        { text: 'Payment %', align: 'center', name: 'payment_percentage' }
                    ]

            });
    });
</script>