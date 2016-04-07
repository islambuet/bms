<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
$user=User_helper::get_user();
//$menu=User_helper::get_html_menu();
//echo '<PRE>';
//print_r($menu);
//echo '</PRE>';
if($user)
{
    ?>
    <div class="navbar navbar-default" role="navigation">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
            </div>
            <div class="collapse navbar-collapse navbar-ex1-collapse">
                <ul class="nav navbar-nav">
                    <li class="menu-item"><a href="<?php echo site_url(); ?>">Dashboard</a></li>
<!--                    <li class="menu-item dropdown">-->
<!--                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">System settings<b class="caret"></b></a>-->
<!--                        <ul class="dropdown-menu">-->
<!--                            <li class="menu-item dropdown dropdown-submenu">-->
<!--                                <a href="#" class="dropdown-toggle" data-toggle="dropdown">System settings</a>-->
<!--                                <ul class="dropdown-menu">-->
<!--                                    <li class="menu-item ">-->
<!--                                        <a href="#">System settings</a>-->
<!--                                    </li>-->
<!--                                    <li class="menu-item dropdown dropdown-submenu">-->
<!--                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown">Level 2</a>-->
<!--                                        <ul class="dropdown-menu">-->
<!--                                            <li>-->
<!--                                                <a href="#">Link 3</a>-->
<!--                                            </li>-->
<!--                                        </ul>-->
<!--                                    </li>-->
<!--                                </ul>-->
<!--                            </li>-->
<!--                        </ul>-->
<!--                    </li>-->
                    <?php
                        $menu=User_helper::get_html_menu();
                        echo $menu;
                    ?>
                    <li class="menu-item"><a href="<?php echo site_url('home/logout'); ?>">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
    <?php
}
?>
<script type="text/javascript">

    jQuery(document).ready(function()
    {
        ///$(document).on("click", ".dropdown-submenu", function(event)
        $( ".dropdown-submenu" ).click(function(event)
        {
            var target = $( event.target );
            // stop bootstrap.js to hide the parents
            if(target.attr('class')=='dropdown-toggle')
            {
                event.preventDefault();
                event.stopPropagation();
            }
            // hide the open children
            $( this ).find(".dropdown-submenu").removeClass('open');
            // add 'open' class to all parents with class 'dropdown-submenu'
            $( this ).parents(".dropdown-submenu").addClass('open');
            // this is also open (or was)
            $( this ).toggleClass('open');
        });
    });
</script>