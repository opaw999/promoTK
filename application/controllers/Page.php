<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Page extends CI_Controller
{
    public $hrCode;
    public $userId;
    function __construct()
    {
        parent::__construct();

        if (isset($_SESSION['audit_guest'])) {
            $_SESSION['tkEmpId'] = $_SESSION['emp_id'];
            $_SESSION['usr'] = 'audit';
        }

        $cond   = array('emp_id' => $_SESSION['tkEmpId']);
        $cc     = $this->promo_model->select_rowArray('pis', 'employee3', $cond)['company_code'];
        $_SESSION['hrCode'] = ($cc != '07') ? 'asc' : 'cebo';
        $this->hrCode = $_SESSION['hrCode'];

        $cond   = array('empId' => $_SESSION['tkEmpId'], 'status' => 'active');
        $user   = $this->promo_model->select_rowArray('tk', 'user', $cond);

        $cond   = array('empId' => $_SESSION['tkEmpId'], 'order_by' => '1', 'status' => '1');
        $access = $this->promo_model->select_rowArray('tk', 'setup_user_menu', $cond);


        if (!isset($_SESSION['tkEmpId']) && !$user && !$access && !isset($_SESSION['audit_guest'])) {
            redirect('http://' . $_SERVER['SERVER_ADDR'] . ":" . $_SERVER['SERVER_PORT'] . '/timekeeping');
        } else {
            $this->userId = $_SESSION['tkEmpId'];
        }
    }

    public function menu($menu = 'dtr', $file = 'per_cutoff')
    {
        if (!file_exists(APPPATH . "views/page/$menu/$file.php")) {
            show_404();
        }

        if (!file_exists(APPPATH . "views/page/$menu/{$menu}_js.php")) {
            show_404();
        }

        $cond   = ['empId' => $this->userId, 'menu' => $menu];
        $roles  = $this->promo_model->select_rowArray('tk', 'promo_access_roles', $cond);
        if (!$roles) {
            redirect('http://' . $_SERVER['SERVER_ADDR'] . ":" . $_SERVER['SERVER_PORT'] . '/timekeeping');
        } else {

            $s = [];
            if ($roles['submenu']) {
                $s = explode('|', $roles['submenu']);
            }
            if ($menu != 'user' && $menu != 'duty') {
                if (!in_array($file, $s)) {
                    redirect('http://' . $_SERVER['SERVER_ADDR'] . ":" . $_SERVER['SERVER_PORT'] . '/timekeeping');
                }
            }
        }

        $data['menu']       = $menu;
        $data['file']       = $file;

        $data['username']   = $_SESSION['usr'];
        $data['userId']     = $this->userId;
        $data['user']       = $this->promo_model->user_info();

        $cond               = ['empId' => $this->userId];
        $data['account']    = $this->promo_model->select_rowArray('tk', 'user', $cond);

        $data['stores']     = $this->promo_model->locate_promo_bu($this->hrCode);
        $data['tk_stores']  = $this->promo_model->tk_stores();
        $data['tk_bu']      = $this->promo_model->tk_bu();


        $cutoffs = ['per_cutoff', 'cutoff', 'app_dis', 'cutoff_process', 'change_shift', 'off_duty', 'process_per_cutoff'];
        if (in_array($file, $cutoffs)) {
            $data['cutoffs'] = $this->promo_model->selectAll('tk', 'promo_schedule');
        }

        if ($menu == 'viewreq') {
            $data['cutoffs'] = $this->promo_model->selectAll('tk', 'promo_schedule');
        }

        if ($file == 'export_import' || $file == 'portal_export') {
            $data['promo_hr_start'] = $this->promo_model->promo_hr_start();
        }

        if ($file == 'logbox') {
            $data['stores'] = $this->promo_model->locate_promo_bu($this->hrCode);
        }

        $data['eoa'] = $this->promo_model->pendingReq('e_o_authorization', 't1.empId=t2.empId');
        $data['eca'] = $this->promo_model->pendingReq('coverup_auth', 't1.empId=t2.empId');
        $data['dwa'] = $this->promo_model->pendingReq('d_w_adjustment', 't1.empId=t2.empId');
        $data['rla'] = $this->promo_model->pendingReq('r_l_absence', 't1.empId=t2.empId');

        if (isset($_SESSION['audit_guest'])) {
            $cond = ['statcut' => '11'];
            $data['cutoffs'] = $this->promo_model->select_resultArray('tk', 'promo_schedule', $cond);
            $this->load->view('guest/header', $data);
            $this->load->view('guest/menu', $data);
            $this->load->view('page/' . $menu . '/' . $file, $data);
            $this->load->view('guest/script');
            $this->load->view('page/' . $menu . '/' . $menu . '_js', $data);
        } else {
            $this->load->view('template/header', $data);
            $this->load->view('template/menu', $data);
            $this->load->view('page/' . $menu . '/' . $file, $data);
            $this->load->view('template/script');
            $this->load->view('page/' . $menu . '/' . $menu . '_js', $data);
        }
    }

    public function logout()
    {
        $this->nativesession->sess_destroy();
        redirect('http://' . $_SERVER['SERVER_ADDR'] . ':' . $_SERVER['SERVER_PORT'] . '/timekeeping');
    }
}
