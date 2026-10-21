<?php
defined('BASEPATH') or exit('No direct script access allowed');

class User extends CI_Controller
{
    public $userName;
    public $userId;
    public $hrCode;
    function __construct()
    {
        parent::__construct();

        $cond   = ['empId' => $_SESSION['tkEmpId'], 'status' => 'active'];
        $user   = $this->promo_model->select_rowArray('tk', 'user', $cond);

        $cond   = ['empId' => $_SESSION['tkEmpId'], 'order_by' => '1', 'status' => '1'];
        $access = $this->promo_model->select_rowArray('tk', 'setup_user_menu', $cond);

        if (!isset($_SESSION['tkEmpId']) && !$user && !$access) {
            redirect('http://' . $_SERVER['SERVER_ADDR'] . ":" . $_SERVER['SERVER_PORT'] . '/timekeeping');
        } else {
            $this->userId   = $_SESSION['tkEmpId'];
            $this->hrCode   = $_SESSION['hrCode'];
            $this->userName = $_SESSION['usr'];
        }
    }

    public function accessroles_table()
    {
        $data = [];
        $query = $this->promo_model->selectAll('tk', 'user');
        foreach ($query as $key => $row) {
            $empId  = $row['empId'];
            $status = '<span class="badge bg-success">' . $row['status'] . '</span>';
            if ($row['status'] != 'active') {
                $status = '<span class="badge bg-warning">' . $row['status'] . '</span>';
            }
            $name   = $this->promo_model->getName_employee3($empId);
            $pos    = $this->promo_model->select_rowArray('pis', 'employee3', ['emp_id' => $empId])['position'];
            $access = '<a href="javascript:;" onclick="accessrolesForm(\'' . $empId . '\')"><i class="fa fa-lock text-primary"></i></a>';

            $cond = ['empId' => $empId, 'order_by' => '1', 'status' => '1'];
            $check = $this->promo_model->select_rowArray('tk', 'setup_user_menu', $cond);
            if ($check) {
                $col    = [];
                $col[]  = $empId;
                $col[]  = $name;
                $col[]  = $pos;
                $col[]  = $status;
                $col[]  = $access;
                $data[] = $col;
            }
        }

        echo json_encode(['data' => $data]);
    }

    public function accessrolesForm()
    {
        $input  = $this->input->post(NULL, TRUE);
        $empId  = $input['empId'];
        $name   = $this->promo_model->getName_employee3($empId);

        echo '<div class="row">';
        echo '<div class="col-md-12">';
        echo '<h4>[' . $empId . '] ' . $name . '</h4>';
        echo '</div>';
        echo '<div class="col-md-12">';

        echo '<table class="table table-bordered table-hover table-sm">
                <tr>
                    <th width="15%">Main Menu</th>
                    <th class="text-center">Sub Menus</th>
                </tr>';

        $cond = ['status' => 'active'];
        $query = $this->promo_model->select_resultArray('tk', 'promo_menu', $cond);
        foreach ($query as $key => $row) {
            $mid = $row['menu_id'];
            $mname = $row['menu_name'];
            $mm = $row['menu'];

            $cond   = ['empId' => $empId, 'menu' => $mm];
            $check  = $this->promo_model->select_rowArray('tk', 'promo_access_roles', $cond);

            echo '<tr>
                    <td>
                        <div class="form-check form-check-inline ml-2">
                            <input class="form-check-input" type="checkbox" name="' . $mm . '" onclick="mainMenu(\'' . $empId . '\',\'' . $mm . '\')" ' . ($check ? 'checked' : '') . '>
                            <label class="form-check-label">' . $mname . '</label>
                        </div>
                    </td>';

            echo    '<td>';
            $cond = ['status' => 'active', 'menu_id' => $mid];
            $query = $this->promo_model->select_resultArray('tk', 'promo_submenu', $cond);
            foreach ($query as $key => $row) {

                $sname = $row['submenu_name'];
                $sm = $row['submenu'];

                $s = [];
                if ($check) {
                    $s = explode('|', $check['submenu']);
                }

                echo    '<div class="form-check form-check-inline ml-2">
                            <input class="form-check-input ' . $mm . '" type="checkbox" name="' . $sm . '" onclick="subMenu(\'' . $empId . '\',\'' . $mm . '\',\'' . $sm . '\')" ' . (in_array($sm, $s) ? 'checked' : '') . '>
                            <label class="form-check-label">' . $sname . '</label>
                        </div>';
            }
            echo    '</td>';
            echo '</tr>';
        }

        echo '</table>';
        echo '</div>';
        echo '</div>';
    }

    public function mainMenu()
    {
        $input      = $this->input->post(NULL, TRUE);
        $mm         = $input['mm'];
        $empId      = $input['empId'];
        $process    = $input['process'];
        if ($process == 'activate') {
            $sm = '';
            if ($mm == 'add') {
                $sm = 'cutoff|manual_ot|holiday';
            } else if ($mm == 'setup') {
                $sm = 'app_dis|cutoff_process|enable_addon|manual_inputs';
            } else if ($mm == 'view') {
                $sm = 'masterfile|date_assigned|holidays';
            } else if ($mm == 'process') {
                $sm = 'single|logbox|biometric|change_shift|off_duty|process_per_cutoff';
            } else if ($mm == 'dtr') {
                $sm = 'per_cutoff|per_employee|earlyCo';
            } else if ($mm == 'viewreq') {
                $sm = 'otAuth|otPayment|cuAuth|doShort|loa|dwa|utAuth|doLong';
            } else if ($mm == 'concerns') {
                $sm = 'ot_roving|hr_account|portal_export|promoReqList|setupList';
            }

            $data = ['empId' => $empId, 'menu' => $mm, 'submenu' => $sm];
            $insert = $this->promo_model->insert('tk', 'promo_access_roles', $data);
        } else {
            $cond = ['empId' => $empId, 'menu' => $mm];
            $delete = $this->promo_model->delete('tk', 'promo_access_roles', $cond);
        }
    }

    public function subMenu()
    {
        $input      = $this->input->post(NULL, TRUE);
        $mm         = $input['mm'];
        $sm         = $input['sm'];
        $empId      = $input['empId'];
        $process    = $input['process'];

        $cond = ['empId' => $empId, 'menu' => $mm];
        $check = $this->promo_model->select_rowArray('tk', 'promo_access_roles', $cond);
        if ($check) {
            if ($process == 'activate') {
                $m = [];
                if ($check['submenu']) {
                    $m = explode('|', $check['submenu']);
                }
                $m[] = $sm;
                $menu = implode('|', $m);
            } else {
                $m = [];
                if ($check['submenu']) {
                    $m = explode('|', $check['submenu']);
                }
                $m = array_diff($m, [$sm]);
                $menu = implode('|', $m);
            }

            $cond = ['empId' => $empId, 'menu' => $mm];
            $data   = ['submenu' => $menu];
            $update = $this->promo_model->update('tk', 'promo_access_roles', $cond, $data);
        } else {
            $data = ['empId' => $empId, 'menu' => $mm, 'submenu' => $sm];
            $insert = $this->promo_model->insert('tk', 'promo_access_roles', $data);
        }
    }
}
