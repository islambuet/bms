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

    }

}
