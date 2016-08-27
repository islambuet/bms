<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Test extends CI_Controller
{
    private  $message;
    public function __construct()
    {
        parent::__construct();
        $this->message="";
    }
    public function index()
    {
        $a=5;
        $b=3;
        $c=$a&&$b;
        if($c===true)
        {
            echo 'yes';
        }
        else
        {
            echo 'no';
        }
        var_dump($c);

    }

    //location setup

}
