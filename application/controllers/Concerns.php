<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Concerns extends CI_Controller
{
    public $userName;
    public $userId;
    public $hrCode;
    function __construct()
    {
        parent::__construct();

        $cond   = array('empId' => $_SESSION['tkEmpId'], 'status' => 'active');
        $user   = $this->promo_model->select_rowArray('tk', 'user', $cond);

        $cond   = array('empId' => $_SESSION['tkEmpId'], 'order_by' => '1', 'status' => '1');
        $access = $this->promo_model->select_rowArray('tk', 'setup_user_menu', $cond);

        if (!isset($_SESSION['tkEmpId']) && !$user && !$access) {
            redirect('http://' . $_SERVER['SERVER_ADDR'] . ":" . $_SERVER['SERVER_PORT'] . '/timekeeping');
        } else {
            $this->userId   = $_SESSION['tkEmpId'];
            $this->hrCode   = $_SESSION['hrCode'];
            $this->userName = $_SESSION['usr'];
        }
    }

    public function concerns_searchPromo()
    {
        echo '<ul class="list-group">';
        $input  = $this->input->post(NULL, TRUE);
        $string = $input['string'];
        $query  = $this->promo_model->concerns_searchPromo($string);
        if ($query) {
            foreach ($query as  $row) {
                $name       = ucwords(strtolower($row['name']));
                $empId      = $row['emp_id'];
                $recordNo   = $row['record_no'];

                $onclick = 'onclick="selectPromo(\'' . $name . '\',\'' . $empId . '\',\'' . $recordNo . '\')"';
                echo '<a href="javascript:;" class="list-group-item" ' . $onclick . '>[' . $empId . '] ' . $name . '</a>';
            }
        } else {
            echo '<span class="list-group-item">No Results Found!</span>';
        }
        echo '</ul>';
    }

    public function ot_list()
    {
        $input  = $this->input->post(NULL, TRUE);
        $empId  = $input['empId'];
        $stores = $this->promo_model->tk_stores();

        $data   = [];
        $cond   = "emp_id ='$empId' ORDER BY ot_date DESC";
        $query  = $this->promo_model->select_resultArray('tk', 'ot_table_for_roving', $cond);
        foreach ($query as $row) {
            $id         = $row['id'];
            $ot_date    = date('M. d, Y', strtotime($row['ot_date']));
            $order      = '<span style="display:none;">' . $row['ot_date'] . '</span>';
            $ot_left    = $row['ot_left'];
            $store      = $row['store'];

            $action = '<select class="form-control form-control-sm" id="' . $id . '" onchange="changeStore(this.value,\'' . $id . '\',\'' . $store . '\')">';
            $action .= '<option value="">---</option>';
            foreach ($stores as $key => $bu) {
                $selected = ($store == $key) ? 'selected' : '';
                $action .= '<option value="' . $key . '" ' . $selected . '>' . $bu . '</option>';
            }
            $action .= '</select>';

            if ($row['status'] == '1st_level') {
                $status = 'First Level Approval';
            } else if ($row['status'] == '2nd_level') {
                $status = 'Request for Payment';
            } else {
                $status = 'Cover-Up Authorization';
            }

            $col    = [];
            $col[]  = $order . $ot_date;
            $col[]  = $ot_left;
            $col[]  = $action;
            $col[]  = $status;
            $data[] = $col;
        }

        echo json_encode(['data' => $data]);
    }

    public function change_store()
    {
        $input  = $this->input->post(NULL, TRUE);
        $id     = $input['id'];
        $store  = $input['store'];

        $tk_bu  = $this->promo_model->tk_bu();
        $buacr  = array_search($store, $tk_bu);

        $cond   = ['bunit_acronym' => $buacr];
        $bu     = $this->promo_model->select_rowArray('pis', 'locate_promo_business_unit', $cond);
        $code   = $bu['cc'] . ',' . $bu['bc'];

        $cond   = ['id' => $id];
        $data   = ['store' => $store, 'store_code' => $code];
        $update = $this->promo_model->update('tk', 'ot_table_for_roving', $cond, $data);
        if ($update) {
            echo json_encode(['message' => 'success', 'feedback' => 'Store has been changed.']);
        } else {
            echo json_encode(['message' => 'failed']);
        }
    }

    public function hrAccount_list()
    {
        $data   = [];
        $query  = $this->promo_model->selectAll('tk', 'promo_hr_start');
        foreach ($query as $row) {
            $id     = $row['phr_id'];
            $empId  = $row['empId'];
            $cond   = ['emp_id' => $empId];
            $emp    = $this->promo_model->select_rowArray('pis', 'employee3', $cond);
            $name   = $emp['name'];
            $pos    = $emp['position'];

            $status = '<span class="badge bg-warning">' . $row['status'] . '</span>';
            if ($row['status'] == 'active') {
                $status = '<span class="badge bg-success">' . $row['status'] . '</span>';
            }

            $upd = ($row['status'] == 'active') ? 'deactivate' : 'activate';
            $cls = ($row['status'] == 'active') ? 'text-warning' : 'text-secondary';

            $action = '<a href="javascript:;" onclick="hrAccount(\'' . $id . '\',\'' . $upd . '\')"><i class="fa fa-lightbulb text-md ' . $cls . ' mr-2"></i></a>';
            $action .= '<a href="javascript:;" onclick="hrAccount(\'' . $id . '\',\'delete\')"><i class="fa fa-trash text-md text-danger"></i></a>';

            $col    = [];
            $col[]  = $empId;
            $col[]  = $name;
            $col[]  = $pos;
            $col[]  = $status;
            $col[]  = $action;
            $data[] = $col;
        }

        echo json_encode(['data' => $data]);
    }

    public function hrAccount()
    {
        $input  = $this->input->post(NULL, TRUE);
        $action = $input['action'];
        if ($action == 'add') {
            $empId = $input['empId'];
            $cond = ['empId' => $empId];
            $check = $this->promo_model->select_rowArray('tk', 'promo_hr_start', $cond);
            if (!$check) {
                $fb = 'HR Account has been added.';
                $data = ['empId' => $empId];
                $hracc = $this->promo_model->insert('tk', 'promo_hr_start', $data);
            } else {
                echo json_encode(['message' => 'User already exist.']);
                return;
            }
        } else if ($action == 'delete') {
            $fb = 'HR Account has been deleted.';
            $cond = ['phr_id' => $input['id']];
            $hracc = $this->promo_model->delete('tk', 'promo_hr_start', $cond);
        } else {
            $fb = 'HR Account status has been updated.';
            $status = ($action == 'activate') ? 'active' : 'inactive';
            $cond = ['phr_id' => $input['id']];
            $data = ['status' => $status];
            $hracc = $this->promo_model->update('tk', 'promo_hr_start', $cond, $data);
        }

        if ($hracc) {
            echo json_encode(['message' => 'success', 'feedback' => $fb]);
        } else {
            echo json_encode(['message' => 'failed']);
        }
    }

    public function promoReqList()
    {
        $data = [];
        $query = $this->promo_model->selectAll('tk', 'promo_setup_user_mode');
        foreach ($query as  $user) {
            $id     = $user['sumId'];
            $empId  = $user['empId'];
            $status = $user['status'];

            $cond   = ['emp_id' => $empId];
            $row    = $this->promo_model->select_rowArray('pis', 'employee3', $cond);
            $sstat  = $row['sub_status'];
            $name   = ucwords(strtolower($row['name']));
            $pos    = $row['position'];
            $cc     = $row['company_code'];
            $bc     = $row['bunit_code'];
            $dc     = $row['dept_code'];

            $cond   = ['company_code' => $cc, 'bunit_code' => $bc];
            $bunit  = $this->promo_model->select_rowArray('pis', 'locate_business_unit', $cond)['business_unit'];
            $cond   = ['company_code' => $cc, 'bunit_code' => $bc, 'dept_code' => $dc];
            $dept   = $this->promo_model->select_rowArray('pis', 'locate_department', $cond)['dept_name'];

            if ($sstat == 'Cleared' && $status != 'inactive') {
                $cond   = ['sumId' => $id];
                $data   = ['status' => 'inactive'];
                $update = $this->promo_model->update('tk', 'promo_setup_user_mode', $cond, $data);
            }

            $class = ($status == 'active') ? 'text-warning' : 'text-secondary';
            $action = '<i class="fa fa-trash text-secondary mr-2"></i>';
            $action .= '<i class="fa fa-lightbulb text-secondary"></i>';

            if ($this->userId != $empId) {
                $action = '<a href="javascript:;" onclick="promoReqAccess(\'' . $id . '\',this.id)" title="Delete" id="delete"><i class="fa fa-trash text-danger mr-2"></i></a>';
                if ($sstat != 'Cleared') {
                    $action .= '<a href="javascript:;" onclick="promoReqAccess(\'' . $id . '\',this.id)" title="Update" id="update"><i class="' . $class . '"></i></a>';
                }
            }

            $col    = [];
            $col[]  = $empId;
            $col[]  = $name;
            $col[]  = $pos;
            // $col[]  = $bunit;
            $col[]  = $dept;
            $col[]  = $action;
            $data[] = $col;
        }

        echo json_encode(['data' => $data]);
    }

    public function promoReqAccess()
    {
        $input  = $this->input->post(NULL, TRUE);
        $action = $input['func'];
        $id     = $input['id'];
        $cond   = ['sumId' => $id];
        if ($action == 'delete') {
            $promo = $this->promo_model->delete('tk', 'promo_setup_user_mode', $cond);
        } else if ($action == 'update') {
            $chk    = $this->promo_model->select_rowArray('tk', 'promo_setup_user_mode', $cond);
            $data   = array('status' => ($chk['status'] == 'active') ? 'inactive' : 'active');
            $promo  = $this->promo_model->update('tk', 'promo_setup_user_mode', $cond, $data);
        } else if ($action == 'add') {
            $cond   = ['emp_id' => $id];
            $row    = $this->promo_model->select_rowArray('pis', 'employee3', $cond);
            if ($row) {
                if ($row['sub_status'] == 'Cleared') {
                    echo json_encode(['message' => 'cleared']);
                    return;
                }

                $cond = ['empId' => $id];
                $chk = $this->promo_model->select_rowArray('tk', 'promo_setup_user_mode', $cond);
                if (!$chk) {
                    $data = ['empId' => $id, 'status' => 'active'];
                    $promo = $this->promo_model->insert('tk', 'promo_setup_user_mode', $data);
                } else {
                    echo json_encode(['message' => 'exist']);
                    return;
                }
            } else {
                echo json_encode(['message' => 'mismatch']);
                return;
            }
        }

        if ($promo) {
            echo json_encode(['message' => 'success']);
        } else {
            echo json_encode(['message' => 'failed']);
        }
    }

    public function setupSupList()
    {
        $data = [];
        $query = $this->promo_model->selectAll('tk', 'promo_sup_start');
        foreach ($query as  $sup) {
            $id     = $sup['pss_id'];
            $empId  = $sup['empId'];
            $status = $sup['status'];

            $cond   = ['emp_id' => $empId];
            $row    = $this->promo_model->select_rowArray('pis', 'employee3', $cond);
            $sstat  = $row['sub_status'];
            $name   = ucwords(strtolower($row['name']));
            $pos    = $row['position'];
            $cc     = $row['company_code'];
            $bc     = $row['bunit_code'];
            $dc     = $row['dept_code'];

            $cond   = ['company_code' => $cc, 'bunit_code' => $bc];
            $bunit  = $this->promo_model->select_rowArray('pis', 'locate_business_unit', $cond)['business_unit'];
            $cond   = ['company_code' => $cc, 'bunit_code' => $bc, 'dept_code' => $dc];
            $dept   = $this->promo_model->select_rowArray('pis', 'locate_department', $cond)['dept_name'];

            $action = '<a href="javascript:;" onclick="setupListAction(\'' . $id . '\',this.id)" title="Delete" id="delete"><i class="fa fa-trash text-danger mr-2"></i></a>';

            if ($sstat == 'Cleared' && $status != 'inactive') {
                $cond   = array('pss_id' => $id);
                $data   = array('status' => 'inactive');
                $update = $this->promo_model->update('tk', 'promo_sup_start', $cond, $data);
            }

            if ($row['current_status'] == 'blacklisted') {
                $cond   = array('pss_id' => $id);
                $data   = array('status' => 'inactive');
                $update = $this->promo_model->update('tk', 'promo_sup_start', $cond, $data);
            }

            if ($status == 'active') {
                $class = 'fa fa-lightbulb text-warning mr-2';
            } else {
                $class = 'fa fa-lightbulb text-secondary mr-2';
            }

            if ($sstat != 'Cleared') {
                $action .= '<a href="javascript:;" onclick="setupListAction(\'' . $id . '\',this.id)" title="Update account status" id="update"><i class="' . $class . '"></i></a>';
                $action .= '<a href="javascript:;" onclick="cutOffSelection(\'' . $empId . '\')" title="Update Cutoff" id="cutoff"><i class="fa fa-calendar text-primary"></i></a>';
            }

            $col    = [];
            $col[]  = $empId;
            $col[]  = $name;
            $col[]  = $pos;
            $col[]  = $bunit;
            $col[]  = $dept;
            $col[]  = $action;
            $data[] = $col;
        }

        echo json_encode(['data' => $data]);
    }

    public function cutOffSelection()
    {
        $input  = $this->input->post(NULL, TRUE);
        $empId  = $input['id'];

        $cond   = array('empId' => $empId);
        $chk    = $this->promo_model->select_rowArray('tk', 'promo_sup_start', $cond);
        if ($chk) {
            $cond   = array('emp_id' => $empId);
            $row    = $this->promo_model->select_rowArray('pis', 'employee3', $cond);
            $name   = ucwords(strtolower($row['name']));

            echo '<div class="row">';
            echo '<div class="col-md-12">';
            echo '<h5 class="text-theme">[' . $empId . '] ' . $name . '</td>';
            echo '</div>';

            echo '<label class="mt-3 mb-2 text-secondary"><b>Cutoff List</b></label>';
            echo '<div class="col-md-12">';
            echo '<div class="form-check">
            		<input class="form-check-input" name="selectAll" onclick="selectAll2(this.id,\'' . $empId . '\')" type="checkbox" id="selectAll">
            		<label class="form-check-label" for="selectAll"><h5 class="" style="width: 150px;">Select All</h5></label>';
            echo '</div>';
            echo '</div>';
            $query = $this->promo_model->selectAll('tk', 'promo_schedule');
            foreach ($query as $key => $c) {

                if ($c['endFC'] == '') {
                    $endFC = "Last";
                } else {
                    $endFC = $c['endFC'];
                }

                $statCut    = $c['statCut'];
                $val        = $empId . '|' . $statCut;

                $chk    = '';
                $class  = 'badge bg-secondary ' . $statCut . '';
                $cond   = array('superior' => $empId, 'statCut' => $statCut);
                $check  = $this->promo_model->select_rowArray('tk', 'promo_sup_cut', $cond);
                if ($check) {
                    $chk    = 'checked';
                    $class  = 'badge bg-primary ' . $statCut . '';
                }

                $cutOff = '<h5 class="" style="width: 150px;">' . $c['startFC'] . '-' . $endFC . ' / ' . $c['startSC'] . '-' . $c['endSC'] . '</h5>';

                echo '<div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input selCutoff" name="statCut[]" onclick="checkboxColor(this.id)" value="' . $val . '" type="checkbox" id="' . $statCut . '" ' . $chk . '>
                            <label class="form-check-label" for="' . $statCut . '">' . $cutOff . '</label>
                        </div>';
                echo '</div>';
            }
            echo '</div>';
        } else {
            echo '<div class="row">';
            echo '<label class="mt-3 mb-2 text-secondary"><b>Cutoff List</b></label>';
            echo '<div class="col-md-12">';
            echo '<div class="form-check">
            		<input class="form-check-input" name="selectAll" onclick="selectAll(this.id)" type="checkbox" id="selectAll">
            		<label class="form-check-label" for="selectAll"><h5 class="" style="width: 150px;">Select All</h5></label>';
            echo '</div>';
            echo '</div>';


            $promoSched = $this->promo_model->selectAll('tk', 'promo_schedule');
            foreach ($promoSched as $key => $c) {

                if ($c['endFC'] == '') {
                    $endFC = "Last";
                } else {
                    $endFC = $c['endFC'];
                }

                $statCut    = $c['statCut'];

                $val    = $empId . '|' . $statCut;

                $cond   = "superior='$empId' AND statCut='$statCut'";
                $check  = $this->promo_model->select_rowArray('tk', 'promo_sup_cut', $cond);

                $chk    = '';
                $class  = 'badge bg-secondary ' . $statCut . '';
                if (count($check) != 0) {
                    $chk    = 'checked';
                    $class  = 'badge bg-primary ' . $statCut . '';
                }

                $cutOff = '<h5 class="" style="width: 150px;">' . $c['startFC'] . '-' . $endFC . ' / ' . $c['startSC'] . '-' . $c['endSC'] . '</h5>';

                echo '<div class="col-md-6">
                        <div class="form-check">
            		    	<input class="form-check-input selCutoff" name="statCut[]" onclick="checkboxColor(this.id)" value="' . $statCut . '" type="checkbox" id="' . $statCut . '">
            		    	<label class="form-check-label" for="' . $statCut . '">' . $cutOff . '</label>
            		    </div>';
                echo '</div>';
            }
            echo '</div>';
        }
    }

    public function setupUpdates()
    {
        $input  = $this->input->post(NULL, TRUE);
        $action = $input['action'];
        $id     = $input['id'];

        if ($action == 'delete') {
            $cond   = ['pss_id' => $id];
            $empId  = $this->promo_model->select_rowArray('tk', 'promo_sup_start', $cond)['empId'];
            $cond   = ['superior' => $empId];
            $setup  = $this->promo_model->delete('tk', 'promo_sup_cut', $cond);
            $setup  = $this->promo_model->delete('tk', 'promo_sup_start', ['pss_id' => $id]);
        } else if ($action == 'update') {
            $cond   = ['pss_id' => $id];
            $status = $this->promo_model->select_rowArray('tk', 'promo_sup_start', $cond)['status'];
            $data   = ['status' => ($status == 'active') ? 'inactive' : 'active'];
            $setup = $this->promo_model->update('tk', 'promo_sup_start', $cond, $data);
        } else if ($action == 'cutoff') {
            $val        = explode('|', $id);
            $superior   = $val[0];
            $statCut    = $val[1];

            $data   = ['statCut' => $statCut, 'superior' => $superior];
            $chk    = $this->promo_model->select_rowArray('tk', 'promo_sup_cut', $data);
            if ($chk) {
                $setup = $this->promo_model->delete('tk', 'promo_sup_cut', $data);
            } else {
                $setup = $this->promo_model->insert('tk', 'promo_sup_cut', $data);
            }
        } else if ($action == 'add') {
            $superior = '';
            foreach ($id as $key => $row) {
                if ($key == 0) {
                    $superior = $row;
                    $data = ['empId' => $superior, 'date_started' => date('Y-m-d'), 'status' => 'active'];
                    $setup = $this->promo_model->insert('tk', 'promo_sup_start', $data);
                } else {
                    $statCut = $row;
                    $data = ['statCut' => $statCut, 'superior' => $superior];
                    $setup = $this->promo_model->insert('tk', 'promo_sup_cut', $data);
                }
            }
        } else {
            $i = 0;
            foreach ($id as $key => $row) {
                $i++;
                $val        = explode('|', $row);
                $superior   = $val[0];
                $statCut    = $val[1];

                if ($i == 1) {
                    $cond   = ['superior' => $superior];
                    $delete = $this->promo_model->delete('tk', 'promo_sup_cut', $cond);
                }

                $data   = ['statCut' => $statCut, 'superior' => $superior];
                $setup  = $this->promo_model->insert('tk', 'promo_sup_cut', $data);
            }
        }

        if ($setup) {
            echo json_encode(['message' => 'success']);
        } else {
            echo json_encode(['message' => 'failed']);
        }
    }
}
