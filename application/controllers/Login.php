<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Login extends CI_Controller
{
    function __construct()
    {
        parent::__construct();


        // $cond = array('emp_id' => $_SESSION['tkEmpId'], 'user_status' => 'active');
        // $promo = $this->promo_model->selectAll_tcR('promo_user', $cond);

        // if (!isset($_SESSION['tkEmpId']) && !$promo) {
        //     redirect('http://' . $_SERVER['SERVER_ADDR'] . ":" . $_SERVER['SERVER_PORT'] . '/timekeeping');
        // }
    }

    public function index()
    {
        // redirect('http://' . $_SERVER['SERVER_ADDR'] . ":" . $_SERVER['SERVER_PORT'] . '/hrms/promoV2/promo');
        var_dump($_SESSION);
    }
}
