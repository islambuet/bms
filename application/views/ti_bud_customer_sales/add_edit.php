<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
    $CI = & get_instance();
    $union_ids=array();

    $remarks='';
    if($survey)
    {
        if($survey['union_ids'])
        {
            $union_ids=json_decode($survey['union_ids'],true);
        }
        $remarks=$survey['remarks'];
    }

?>
<form class="form_valid" id="save_form" action="<?php echo site_url($CI->controller_url.'/index/save');?>" method="post">
    <input type="hidden" name="year" value="<?php echo $year; ?>" />
    <input type="hidden" name="crop_type_id" value="<?php echo $crop_type_id; ?>" />
    <input type="hidden" name="upazilla_id" value="<?php echo $upazilla_id; ?>" />
    <input type="hidden" id="system_save_new_status" name="system_save_new_status" value="0" />
    <div class="row widget">
        <div class="widget-header">
            <div class="title">
                <?php echo $title; ?>
            </div>
            <div class="clearfix"></div>
        </div>
        <div class="col-xs-12" style="margin-bottom: 20px;">
            <?php
            foreach($unions as $union)
            {
                ?>
                <label class="checkbox-inline" style="font-size:12px;padding: 5px 5px 5px 25px;background-color: #0C865B; color: #fff; "><input type="checkbox" name="unions[]" value="<?php echo $union['value']; ?>" <?php if(in_array($union['value'],$union_ids)){echo 'checked';} ?>><?php echo $union['text']; ?></label>
                <?php
            }
            ?>
        </div>
        <div class="col-xs-12" style="overflow-x: auto;">
            <table class="table table-hover table-bordered">
                <thead>
                    <tr>
                        <th colspan="50">ARM Variety</th>
                    </tr>
                    <tr>
                        <th style="width: 100px;">
                            Variety
                        </th>
                        <?php
                        for($i=1;$i<=$max_customers_number;$i++)
                        {
                            ?>
                            <th style="width: 100px;">
                                Individual Sales Quantity
                            </th>
                            <th style="width: 100px;">
                                Market Size
                            </th>
                            <?php
                        }
                        ?>
                        <th style="width: 100px;">
                            Assumed Market Size
                        </th>
                    </tr>
                    <tr>
                        <th></th>
                        <?php
                        for($i=1;$i<=$max_customers_number;$i++)
                        {
                            ?>
                            <th colspan="2">
                                <?php
                                $editable=false;
                                if(isset($customers[$i]['name'])&&strlen($customers[$i]['name'])>0)
                                {
                                    if(isset($CI->permissions['edit'])&&($CI->permissions['edit']==1))
                                    {
                                        $editable=true;
                                    }
                                    else
                                    {
                                        $editable=false;
                                    }

                                }
                                else
                                {
                                    $editable=true;
                                }
                                if($editable)
                                {
                                    ?>
                                    <input type="text" name="customers[<?php echo $i;?>]" class="form-control" value="<?php if(isset($customers[$i])){echo $customers[$i]['name']; } ?>">
                                    <?php
                                }
                                else
                                {
                                    ?>
                                    <label><?php echo $customers[$i]['name']; ?></label>
                                    <?php
                                }
                                ?>

                            </th>
                        <?php
                        }
                        ?>
                        <th>
                            &nbsp;
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach($varieties_arm as $variety)
                    {
                        ?>
                        <tr>
                            <td>
                                <?php echo $variety['name']; ?>
                            </td>
                            <?php
                            for($i=1;$i<=$max_customers_number;$i++)
                            {
                                $editable_sales=false;
                                $editable_market=false;
                                $sales='';
                                $market='';
                                if(isset($survey_customer_survey[$variety['id']][$i]))
                                {
                                    if($survey_customer_survey[$variety['id']][$i]['weight_sales']>0)
                                    {
                                        $sales=$survey_customer_survey[$variety['id']][$i]['weight_sales'];
                                        if(isset($CI->permissions['edit'])&&($CI->permissions['edit']==1))
                                        {
                                            $editable_sales=true;
                                        }
                                        else
                                        {
                                            $editable_sales=false;
                                        }
                                    }
                                    else
                                    {
                                        $editable_sales=true;
                                    }
                                    if($survey_customer_survey[$variety['id']][$i]['weight_market']>0)
                                    {
                                        $market=$survey_customer_survey[$variety['id']][$i]['weight_market'];
                                        if(isset($CI->permissions['edit'])&&($CI->permissions['edit']==1))
                                        {
                                            $editable_market=true;
                                        }
                                        else
                                        {
                                            $editable_market=false;
                                        }
                                    }
                                    else
                                    {
                                        $editable_market=true;
                                    }
                                }
                                else
                                {
                                    $editable_sales=true;
                                    $editable_market=true;
                                }
                                ?>
                                <td>
                                    <?php
                                    if($editable_sales)
                                    {
                                        ?>
                                            <input type="text" class="form-control integer_type_positive text-right" name="varieties[<?php echo $variety['id']; ?>][<?php echo $i;?>][weight_sales]" value="<?php echo $sales;?>">
                                        <?php
                                    }
                                    else
                                    {
                                        ?>
                                        <label class="text-right form-control" style="background-color: #F5F5F5;"><?php echo $sales;?></label>
                                        <?php
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if($editable_market)
                                    {
                                        ?>
                                            <input type="text" class="form-control integer_type_positive text-right" name="varieties[<?php echo $variety['id']; ?>][<?php echo $i;?>][weight_market]" value="<?php echo $market;?>">
                                        <?php
                                    }
                                    else
                                    {
                                        ?>
                                        <label class="text-right form-control" style="background-color: #F5F5F5;"><?php echo $market;?></label>
                                        <?php
                                    }
                                    ?>
                                </td>
                                <?php
                            }
                            ?>
                            <td>
                                <?php
                                $editable=false;
                                if(isset($survey_quantity_survey[$variety['id']]['weight_assumed'])&&($survey_quantity_survey[$variety['id']]['weight_assumed']>0))
                                {
                                    if(isset($CI->permissions['edit'])&&($CI->permissions['edit']==1))
                                    {
                                        $editable=true;
                                    }
                                    else
                                    {
                                        $editable=false;
                                    }

                                }
                                else
                                {
                                    $editable=true;
                                }
                                if($editable)
                                {
                                    ?>
                                    <input type="text" name="weight_assumed[<?php echo $variety['id'];?>]" class="form-control integer_type_positive text-right" value="<?php if(isset($survey_quantity_survey[$variety['id']]['weight_assumed'])){echo $survey_quantity_survey[$variety['id']]['weight_assumed']; } ?>">
                                <?php
                                }
                                else
                                {
                                    ?>
                                    <label class="text-right form-control" style="background-color: #F5F5F5;"><?php echo $survey_quantity_survey[$variety['id']]['weight_assumed'];?></label>
                                <?php
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                    <!--check variety-->
                    <tr>
                        <th colspan="21">
                            Competitor Variety
                        </th>
                    </tr>
                    <?php
                    foreach($varieties_competitor as $variety)
                    {
                        ?>
                        <tr>
                            <td>
                                <?php echo $variety['name']; ?>
                            </td>
                            <?php
                            for($i=1;$i<=$max_customers_number;$i++)
                            {
                                $editable_sales=false;
                                $editable_market=false;
                                $sales='';
                                $market='';
                                if(isset($survey_customer_survey[$variety['id']][$i]))
                                {
                                    if($survey_customer_survey[$variety['id']][$i]['weight_sales']>0)
                                    {
                                        $sales=$survey_customer_survey[$variety['id']][$i]['weight_sales'];
                                        if(isset($CI->permissions['edit'])&&($CI->permissions['edit']==1))
                                        {
                                            $editable_sales=true;
                                        }
                                        else
                                        {
                                            $editable_sales=false;
                                        }
                                    }
                                    else
                                    {
                                        $editable_sales=true;
                                    }
                                    if($survey_customer_survey[$variety['id']][$i]['weight_market']>0)
                                    {
                                        $market=$survey_customer_survey[$variety['id']][$i]['weight_market'];
                                        if(isset($CI->permissions['edit'])&&($CI->permissions['edit']==1))
                                        {
                                            $editable_market=true;
                                        }
                                        else
                                        {
                                            $editable_market=false;
                                        }
                                    }
                                    else
                                    {
                                        $editable_market=true;
                                    }
                                }
                                else
                                {
                                    $editable_sales=true;
                                    $editable_market=true;
                                }
                                ?>
                                <td>
                                    <?php
                                    if($editable_sales)
                                    {
                                        ?>
                                        <input type="text" class="form-control integer_type_positive text-right" name="varieties[<?php echo $variety['id']; ?>][<?php echo $i;?>][weight_sales]" value="<?php echo $sales;?>">
                                    <?php
                                    }
                                    else
                                    {
                                        ?>
                                        <label class="text-right form-control" style="background-color: #F5F5F5;"><?php echo $sales;?></label>
                                    <?php
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if($editable_market)
                                    {
                                        ?>
                                        <input type="text" class="form-control integer_type_positive text-right" name="varieties[<?php echo $variety['id']; ?>][<?php echo $i;?>][weight_market]" value="<?php echo $market;?>">
                                    <?php
                                    }
                                    else
                                    {
                                        ?>
                                        <label class="text-right form-control" style="background-color: #F5F5F5;"><?php echo $market;?></label>
                                    <?php
                                    }
                                    ?>
                                </td>
                            <?php
                            }
                            ?>
                            <td>
                                <?php
                                $editable=false;
                                if(isset($survey_quantity_survey[$variety['id']]['weight_assumed'])&&($survey_quantity_survey[$variety['id']]['weight_assumed']>0))
                                {
                                    if(isset($CI->permissions['edit'])&&($CI->permissions['edit']==1))
                                    {
                                        $editable=true;
                                    }
                                    else
                                    {
                                        $editable=false;
                                    }

                                }
                                else
                                {
                                    $editable=true;
                                }
                                if($editable)
                                {
                                    ?>
                                    <input type="text" name="weight_assumed[<?php echo $variety['id'];?>]" class="form-control integer_type_positive text-right" value="<?php if(isset($survey_quantity_survey[$variety['id']]['weight_assumed'])){echo $survey_quantity_survey[$variety['id']]['weight_assumed']; } ?>">
                                <?php
                                }
                                else
                                {
                                    ?>
                                    <label class="text-right form-control" style="background-color: #F5F5F5;"><?php echo $survey_quantity_survey[$variety['id']]['weight_assumed'];?></label>
                                <?php
                                }
                                ?>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                    <tr>
                        <th colspan="21">
                            Others variety
                        </th>
                    </tr>
                    <tr>
                        <td>
                            Others
                        </td>
                        <?php
                        for($i=1;$i<=$max_customers_number;$i++)
                        {
                            $editable_sales=false;
                            $editable_market=false;
                            $sales='';
                            $market='';
                            if(isset($survey_customer_survey[0][$i]))
                            {
                                if($survey_customer_survey[0][$i]['weight_sales']>0)
                                {
                                    $sales=$survey_customer_survey[0][$i]['weight_sales'];
                                    if(isset($CI->permissions['edit'])&&($CI->permissions['edit']==1))
                                    {
                                        $editable_sales=true;
                                    }
                                    else
                                    {
                                        $editable_sales=false;
                                    }
                                }
                                else
                                {
                                    $editable_sales=true;
                                }
                                if($survey_customer_survey[0][$i]['weight_market']>0)
                                {
                                    $market=$survey_customer_survey[0][$i]['weight_market'];
                                    if(isset($CI->permissions['edit'])&&($CI->permissions['edit']==1))
                                    {
                                        $editable_market=true;
                                    }
                                    else
                                    {
                                        $editable_market=false;
                                    }
                                }
                                else
                                {
                                    $editable_market=true;
                                }
                            }
                            else
                            {
                                $editable_sales=true;
                                $editable_market=true;
                            }
                            ?>
                            <td>
                                <?php
                                if($editable_sales)
                                {
                                    ?>
                                    <input type="text" class="form-control integer_type_positive text-right" name="varieties[0][<?php echo $i;?>][weight_sales]" value="<?php echo $sales;?>">
                                <?php
                                }
                                else
                                {
                                    ?>
                                    <label class="text-right form-control" style="background-color: #F5F5F5;"><?php echo $sales;?></label>
                                <?php
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if($editable_market)
                                {
                                    ?>
                                    <input type="text" class="form-control integer_type_positive text-right" name="varieties[0][<?php echo $i;?>][weight_market]" value="<?php echo $market;?>">
                                <?php
                                }
                                else
                                {
                                    ?>
                                    <label class="text-right form-control" style="background-color: #F5F5F5;"><?php echo $market;?></label>
                                <?php
                                }
                                ?>
                            </td>
                        <?php
                        }
                        ?>
                        <td>
                            <?php
                            $editable=false;
                            if(isset($survey_quantity_survey[0]['weight_assumed'])&&($survey_quantity_survey[0]['weight_assumed']>0))
                            {
                                if(isset($CI->permissions['edit'])&&($CI->permissions['edit']==1))
                                {
                                    $editable=true;
                                }
                                else
                                {
                                    $editable=false;
                                }

                            }
                            else
                            {
                                $editable=true;
                            }
                            if($editable)
                            {
                                ?>
                                <input type="text" name="weight_assumed[0]" class="form-control integer_type_positive text-right" value="<?php if(isset($survey_quantity_survey[0]['weight_assumed'])){echo $survey_quantity_survey[0]['weight_assumed']; } ?>">
                            <?php
                            }
                            else
                            {
                                ?>
                                <label class="text-right form-control" style="background-color: #F5F5F5;"><?php echo $survey_quantity_survey[0]['weight_assumed'];?></label>
                            <?php
                            }
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="row show-grid">
            <div class="col-xs-4">
                <label class="control-label pull-right"><?php echo $this->lang->line('LABEL_REMARKS');?></label>
            </div>
            <div class="col-sm-4 col-xs-8">
                <textarea class="form-control" id="remarks" name="remarks"><?php echo $remarks; ?></textarea>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>
</form>
