<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Setup extends CI_Controller
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

    public function  appdis_list()
    {
        $input      = $this->input->post(NULL, TRUE);
        $statCut    = $input['statCut'];

        $data   = [];
        $cond   = "p.superior=e.emp_id AND statCut='$statCut' AND current_status='Active' GROUP BY superior";
        $query  = $this->promo_model->select_resultArray('tk', 'promo_sup_cut as p,pis.employee3 as e', $cond);
        foreach ($query as $key => $row) {

            $empId = $row['superior'];

            $cond = ['supEmpId' => $empId, 'statCut' => $statCut];
            $check = $this->promo_model->select_rowArray('tk', 'promo_setup_switch_sup', $cond);
            if (!$check) {

                $data = ['supEmpId' => $empId, 'statCut' => $statCut, 'status' => 'on'];
                $insert = $this->promo_model->insert('tk', 'promo_setup_switch_sup', $data);
            }

            $cc = $row['company_code'];
            $bc = $row['bunit_code'];
            $dc = $row['dept_code'];
            $name = $row['name'];

            $cond = ['company_code' => $cc, 'bunit_code' => $bc];
            $bu = $this->promo_model->select_rowArray('pis', 'locate_business_unit', $cond)['business_unit'];

            $cond = ['company_code' => $cc, 'bunit_code' => $bc, 'dept_code' => $dc];
            $dept = $this->promo_model->select_rowArray('pis', 'locate_department', $cond)['dept_name'];

            $cond = ['company_code' => $cc, 'bunit_code' => $bc, 'dept_code' => $dc];
            $dept = $this->promo_model->select_rowArray('pis', 'locate_department', $cond)['dept_name'];

            $cond = ['supEmpId' => $empId, 'statCut' => $statCut];
            $psss = $this->promo_model->select_rowArray('tk', 'promo_setup_switch_sup', $cond);
            $id = $psss['pssId'];

            $checked = '';
            if ($psss['status'] == 'on') {
                $checked = 'checked';
            }

            $action = '<input type="checkbox" class="per_status" id="' . $id . '" onchange="switch_status(this.id,\'' . $statCut . '\')" ' . $checked . '>';

            $col    = [];
            $col[]  = $empId;
            $col[]  = $name;
            $col[]  = $bu;
            $col[]  = $dept;
            $col[]  = $action;
            $data[] = $col;
        }

        $checked = false;
        $cond = ['statCut' => $statCut, 'status' => 'on'];
        $check = $this->promo_model->select_rowArray('tk', 'promo_setup_switch_sup', $cond);
        if ($check) {
            $checked = true;
        }

        echo json_encode(['data' => $data, 'checked' => $checked]);
    }

    public function appdis_update()
    {
        $input      = $this->input->post(NULL, TRUE);
        $id         = $input['id'];
        $statCut    = $input['statCut'];
        $status     = ($input['state'] == 'true') ? 'on' : 'off';

        if ($id == 'all') {
            $cond   = ['statCut' => $statCut];
            $data   = ['status' => $status];
            $update = $this->promo_model->update('tk', 'promo_setup_switch_sup', $cond, $data);
        } else {
            $cond   = ['pssId' => $id];
            $data   = ['status' => $status];
            $update = $this->promo_model->update('tk', 'promo_setup_switch_sup', $cond, $data);
        }

        if ($update) {

            $checked = false;
            $cond = ['statCut' => $statCut, 'status' => 'on'];
            $check = $this->promo_model->select_rowArray('tk', 'promo_setup_switch_sup', $cond);
            if ($check) {
                $checked = true;
            }

            echo json_encode(['message' => 'success', 'feedback' => "Supervisor has been turned $status.", 'checked' => $checked]);
        } else {
            echo json_encode(['message' => 'failed']);
        }
    }

    public function exportuser_list()
    {
        $data = [];
        $query = $this->promo_model->selectAll('tk', 'promo_user_exportimport');
        foreach ($query as $key => $row) {
            $empId  = $row['emp_id'];
            $cond   = ['emp_id' => $empId];
            $emp    = $this->promo_model->select_rowArray('pis', 'employee3', $cond);
            $name = $emp['name'];
            $position = $emp['position'];

            $status = '<span class="badge bg-warning">' . $emp['current_status'] . '</span>';
            if ($emp['current_status'] == 'Active') {
                $status = '<span class="badge bg-success">' . $emp['current_status'] . '</span>';
            }

            $action = '<a href="javascript:;" onclick="portalAccess(\'' . $empId . '\',\'delete\')"><i class="fa fa-trash text-md text-danger"></i></a>';

            $col    = [];
            $col[]  = $empId;
            $col[]  = $name;
            $col[]  = $position;
            $col[]  = $status;
            $col[]  = $action;
            $data[] = $col;
        }
        echo json_encode(['data' => $data]);
    }

    public function portalAccess()
    {
        $input  = $this->input->post(NULL, TRUE);
        $action = $input['action'];
        $empId  = $input['empId'];
        $act    = ($action == 'delete') ? 'deleted' : 'added';
        if ($action == 'delete') {
            $cond = ['emp_id' => $empId];
            $portal = $this->promo_model->delete('tk', 'promo_user_exportimport', $cond);
        } else {
            $cond = ['emp_id' => $empId];
            $row = $this->promo_model->select_rowArray('pis', 'employee3', $cond);
            if ($row) {
                if ($row['sub_status'] == 'Cleared') {
                    echo json_encode(['message' => 'cleared']);
                    return;
                }
                $check = $this->promo_model->select_rowArray('tk', 'promo_user_exportimport', $cond);
                if (!$check) {
                    $data = ['emp_id' => $empId];
                    $portal = $this->promo_model->insert('tk', 'promo_user_exportimport', $data);
                } else {
                    echo json_encode(['message' => 'User already exist.']);
                    return;
                }
            }
        }

        if ($portal) {
            echo json_encode(['message' => 'success', 'feedback' => "Export user has been $act."]);
        } else {
            echo json_encode(['message' => 'failed']);
        }
    }

    public function setup_cutoff()
    {
        $input      = $this->input->post(NULL, TRUE);
        $statCut    = $input['statCut'];
        $dateFrom   = $input['dateFrom_' . $statCut];
        $dateTo     = $input['dateTo_' . $statCut];

        $cond   = ['statCut' => $statCut];
        $delete = $this->promo_model->delete('tk', 'setup_cutoff_pro', $cond);
        $data = [
            'dateFrom'  => $dateFrom,
            'dateTo'    => $dateTo,
            'statCut'   => $statCut,
            'reqBy'     => $this->userId,
            'dateReq'   => date('Y-m-d'),
            'timeReq'   => date('H:i:s'),
        ];

        $insert = $this->promo_model->insert('tk', 'setup_cutoff_pro', $data);
        if ($insert) {
            echo json_encode(['message' => 'success', 'feedback' => 'Cutoff has been setup.']);
        } else {
            echo json_encode(['message' => 'failed']);
        }
    }

    public function setup_searchPromo()
    {
        echo '<ul class="list-group">';
        $input  = $this->input->post(NULL, TRUE);
        $string = $input['string'];
        $query  = $this->promo_model->setup_searchPromo($string);
        if ($query) {
            foreach ($query as  $row) {
                $name       = ucwords(strtolower($row['name']));
                $empId      = $row['emp_id'];
                $recordNo   = $row['record_no'];
                $statCut    = $row['statCut'];

                $allowId = $row['allowId'];
                if ($allowId == "Biometric ID") {
                    $bioM = $row['bioMetricId'];
                }

                if ($allowId == "Logbox ID") {
                    $bioM = $row['barcodeId'];
                }

                $onclick = 'onclick="selectPromo(\'' . $name . '\',\'' . $empId . '\',\'' . $recordNo . '\',\'' . $statCut . '\',\'' . $bioM . '\')"';
                echo '<a href="javascript:;" class="list-group-item" ' . $onclick . '>[' . $empId . '] ' . $name . '</a>';
            }
        } else {
            echo '<span class="list-group-item">No Results Found!</span>';
        }
        echo '</ul>';
    }

    public function getCutoffdate()
    {
        $input      = $this->input->post(NULL, TRUE);
        $statCut    = $input['statCut'];
        $bioM       = $input['bioM'];
        $start      = '';
        $end        = '';

        $query = $this->promo_model->promoCutoffs($bioM, $statCut);
        foreach ($query as $row) {

            $cut   = $row['cut'];
            $ndf    = date('Y-m-d', strtotime($row['dateFrom']));
            $ndt    = date('Y-m-d', strtotime($row['dateTo']));

            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));

            if ($cut == 1) {
                $start .= '<option value="' . $ndf . '" >' . $df . '</option>';
                $end .= '<option value="' . $ndt . '" >' . $dt . '</option>';
            }

            if ($cut == 2) {
                $end .= '<option value="' . $ndt . '" >' . $dt . '</option>';
                $start .= '<option value="' . $ndf . '" >' . $df . '</option>';
            }
        }

        echo json_encode(['start' => $start, 'end' => $end]);
    }

    public function perday_previous_allow()
    {
        $data = [];
        $query = $this->promo_model->selectAll('tk', 'perday_previous_allow');
        foreach ($query as $key => $row) {
            $pId    = $row['pId'];
            $empId  = $row['empId'];
            $df     = date('Y-m-d', strtotime($row['dateFrom']));
            $dt     = date('Y-m-d', strtotime($row['dateTo']));

            $func   = 'activate';
            $fabulb = '<i class="fa fa-lightbulb text-secondary"></i>';
            $order  = '<span style="display:none;">' . strtotime($row['dateFrom']) . '</span>';
            $status = '<span class="badge bg-warning">' . $row['status'] . '</span>';
            if ($row['status'] == 'active') {
                $func   = 'deactivate';
                $fabulb = '<i class="fa fa-lightbulb text-warning"></i>';
                $status = '<span class="badge bg-success">' . $row['status'] . '</span>';
            }

            $cond   = ['emp_id' => $empId];
            $emp    = $this->promo_model->select_rowArray('pis', 'employee3', $cond);
            $name   = $emp['name'];

            $test = '<a href="javascript:;" onclick="update_previous(\'' . $pId . '\',\'delete\')"><i class="fa fa-trash text-danger"></i></a>';
            $action = '<a href="javascript:;" onclick="update_previous(\'' . $pId . '\',\'' . $func . '\')">' . $fabulb . '</a>';

            $col    = [];
            $col[]  = $empId;
            $col[]  = $name;
            $col[]  = $order . $df;
            $col[]  = $dt;
            $col[]  = $status;
            $col[]  = $action;
            $data[] = $col;
        }
        echo json_encode(['data' => $data]);
    }

    public function enable_previous()
    {
        $input      = $this->input->post(NULL, TRUE);
        $empId      = $input['empId'];
        $dateFrom   = $input['dateFrom'];
        $dateTo     = $input['dateTo'];

        $cond = ['empId' => $empId, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo];
        $check = $this->promo_model->select_rowArray('tk', 'perday_previous_allow', $cond);
        if (!$check) {
            $data = [
                'empId'         => $empId,
                'dateFrom'      => $dateFrom,
                'dateTo'        => $dateTo,
                'date_added'    => date('Y-m-d'),
                'time_added'    => date('H:i:s'),
                'req_by'        => $this->userId,
                'status'        => 'active',
            ];
            $insert = $this->promo_model->insert('tk', 'perday_previous_allow', $data);
            if ($insert) {
                echo json_encode(['message' => 'success', 'feedback' => 'Previous cutoff has been enabled.']);
            } else {
                echo json_encode(['message' => 'failed']);
            }
        } else {
            echo json_encode(['message' => 'Previous cutoff already exist.']);
            return;
        }
    }

    public function update_previous()
    {
        $input  = $this->input->post(NULL, TRUE);
        $id     = $input['id'];
        $func   = $input['process'];

        if ($func == 'delete') {
            $cond = ['pId' => $id];
            $delete = $this->promo_model->delete('tk', 'perday_previous_allow', $cond);
            if ($delete) {
                echo json_encode(['message' => 'success', 'feedback' => 'Previous cutoff has been deleted.']);
            } else {
                echo json_encode(['message' => 'failed']);
            }
        } else {
            $status = ($func == 'activate') ? 'active' : 'inactive';
            $cond = ['pId' => $id];
            $data = ['status' => $status];
            $update = $this->promo_model->update('tk', 'perday_previous_allow', $cond, $data);
            if ($update) {
                $msg = ($func == 'activate') ? 'activated' : 'deactivated';
                echo json_encode(['message' => 'success', 'feedback' => "Previous cutoff has been $msg."]);
            } else {
                echo json_encode(['message' => 'failed']);
            }
        }
    }

    public function searchManualLate()
    {
        echo '<ul class="list-group">';
        $input  = $this->input->post(NULL, TRUE);
        $string = $input['string'];
        $query  = $this->promo_model->searchManualInputs($string);
        if ($query) {
            foreach ($query as  $row) {
                $name       = ucwords(strtolower($row['name']));
                $empId      = $row['emp_id'];
                $recordNo   = $row['record_no'];
                $statCut    = $row['statCut'];

                $allowId = $row['allowId'];
                if ($allowId == "Biometric ID") {
                    $bioM = $row['bioMetricId'];
                }

                if ($allowId == "Logbox ID") {
                    $bioM = $row['barcodeId'];
                }

                $onclick = 'onclick="selectLate(\'' . $name . '\',\'' . $empId . '\')"';
                echo '<a href="javascript:;" class="list-group-item" ' . $onclick . '>[' . $empId . '] ' . $name . '</a>';
            }
        } else {
            echo '<span class="list-group-item">No Results Found!</span>';
        }
        echo '</ul>';
    }

    public function manualLate()
    {
        $input  = $this->input->post(NULL, TRUE);
        $empId  = $input['lateEmpId'];
        $date   = $input['date_late'];
        $type   = $input['type'];
        $late   = ($type == 'late 1') ? 'utIn1' : 'utIn2';
        $time   = ($type == 'late 1') ? $input['late1'] : $input['late2'];

        $cond   = ['empId' => $empId, 'date' => $date, 'type' => $type,];
        $check1 = $this->promo_model->select_rowArray('tk', 'manualinputs', $cond);

        $cond   = ['empId' => $empId, 'dateDuty' => $date, 'type' => $type,];
        $check2 = $this->promo_model->select_rowArray('tk', 'manual_ext', $cond);

        if (!$check1 && !$check2) {
            $data = [
                'empId'         => $empId,
                'date'          => $date,
                'type'          => $type,
                'assignedBy'    => $this->userId,
                'inputDate'     => date('Y-m-d'),
                'inputTime'     => date('h:i A'),
            ];

            $insert1 = $this->promo_model->insert('tk', 'manualinputs', $data);

            $data = [
                'empId'     => $empId,
                'dateDuty'  => $date,
                'type'      => $type,
                $late       => date('H:i:s', strtotime($time))
            ];

            // var_dump($data);

            $insert2 = $this->promo_model->insert('tk', 'manual_ext', $data);

            if ($insert1 && $insert2) {
                echo json_encode(['message' => 'success', 'feedback' => 'Manual late has been submitted.']);
            } else {
                echo json_encode(['message' => 'failed']);
            }
        } else {
            echo json_encode(['message' => 'Manual late already exist for this employee on the selected date.']);
        }
    }

    public function searchManualUndertime()
    {
        echo '<ul class="list-group">';
        $input  = $this->input->post(NULL, TRUE);
        $string = $input['string'];
        $query  = $this->promo_model->searchManualInputs($string);
        if ($query) {
            foreach ($query as  $row) {
                $name       = ucwords(strtolower($row['name']));
                $empId      = $row['emp_id'];
                $recordNo   = $row['record_no'];
                $statCut    = $row['statCut'];

                $allowId = $row['allowId'];
                if ($allowId == "Biometric ID") {
                    $bioM = $row['bioMetricId'];
                }

                if ($allowId == "Logbox ID") {
                    $bioM = $row['barcodeId'];
                }

                $onclick = 'onclick="selectUndertime(\'' . $name . '\',\'' . $empId . '\')"';
                echo '<a href="javascript:;" class="list-group-item" ' . $onclick . '>[' . $empId . '] ' . $name . '</a>';
            }
        } else {
            echo '<span class="list-group-item">No Results Found!</span>';
        }
        echo '</ul>';
    }

    public function manualUndertime()
    {
        $input  = $this->input->post(NULL, TRUE);
        $empId  = $input['undertimeEmpId'];
        $date   = $input['date_undertime'];
        $type   = $input['type'];

        $field  = ($type == 'undertime 1') ? 'utOut1' : 'utOut2';
        $hours  = ($type == 'undertime 1') ? $input['undertime1'] : $input['undertime2'];

        $t = explode(".", $hours);
        $h = isset($t[0]) ? $t[0] * 1 : 0;
        $m = isset($t[1]) ? $t[1] * 1 : 0;  // will be 0 if no decimal

        $ut = "-$h hours";
        if ($m == 5) {
            $ut = "-$h hours, -30 minutes";
        }

        $cond = "empId='$empId' AND barcodeId NOT LIKE '%\_%' AND bioMetricId NOT LIKE '%\_%' ORDER BY assignId DESC LIMIT 1";
        $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
        $shiftCode = $assign['shiftCode'];

        $allowId = $assign['allowId'];
        if ($allowId == "Biometric ID") {
            $bioM = $assign['bioMetricId'];
        }
        if ($allowId == "Logbox ID") {
            $bioM = $assign['barcodeId'];
        }

        $sc = $this->getSC($empId, $bioM, $date);
        if ($sc) {
            $shiftCode = $sc;
        }

        $cond = ['shiftCode' => $shiftCode];
        $sc = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
        if ($sc) {
            $s_out1 = $sc['1stOut'];
            $s_out2 = $sc['2ndOut'];
        }

        $cond = ['shiftCode' => $shiftCode];
        $scb = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
        if ($scb) {
            $s_out1 = $scb['1stOut'];
            $s_out2 = $scb['2ndOut'];
        }

        $out1   = date("H:i:s", strtotime("$s_out1 $ut"));
        $out2   = date("H:i:s", strtotime("$s_out2 $ut"));
        $time   = ($type == 'undertime 1') ? $out1 : $out2;

        $cond   = ['empId' => $empId, 'date' => $date, 'type' => $type];
        $check1 = $this->promo_model->select_rowArray('tk', 'manualinputs', $cond);
        $cond   = ['empId' => $empId, 'dateDuty' => $date, 'type' => $type];
        $check2 = $this->promo_model->select_rowArray('tk', 'manual_ext', $cond);
        if (!$check1 && !$check2) {

            $data = [
                'empId'         => $empId,
                'date'          => $date,
                'type'          => $type,
                'assignedBy'    => $this->userId,
                'inputDate'     => date('Y-m-d'),
                'inputTime'     => date('h:i A')
            ];

            $insert1 = $this->promo_model->insert('tk', 'manualinputs', $data);

            $data = ['empId' => $empId, 'dateDuty' => $date, 'type' => $type, $field => $time];
            $insert2 = $this->promo_model->insert('tk', 'manual_ext', $data);

            if ($insert1 && $insert2) {

                $utOut1 = $utOut2 = $uT = '00:00';
                $cond = ['empId' => $empId, 'dateDuty' => $date, 'type' => 'undertime 1'];
                $me1 = $this->promo_model->select_rowArray('tk', 'manual_ext', $cond);
                if ($me1) {

                    $meUt1 = $me1['utOut1'];
                    $t = explode(":", $meUt1);
                    $h = $t[0];
                    $m = $t[1];

                    $utOut1 = date("H:i", strtotime("$s_out1 -$h hours, -$m minutes"));
                }

                $cond = ['empId' => $empId, 'dateDuty' => $date, 'type' => 'undertime 2'];
                $me2 = $this->promo_model->select_rowArray('tk', 'manual_ext', $cond);
                if ($me2) {

                    $meUt2 = $me2['utOut2'];
                    $t = explode(":", $meUt2);
                    $h = $t[0];
                    $m = $t[1];

                    $utOut2 = date("H:i", strtotime("$s_out2 -$h hours, -$m minutes"));
                }

                $h1 = $m1 = $h2 = $m2 = 0;
                if ($utOut1 != '00:00') {
                    $t  = explode(":", $utOut1);
                    $h1 = $t[0];
                    $m1 = $t[1];
                }

                if ($utOut2 != '00:00') {
                    $t  = explode(":", $utOut2);
                    $h2 = $t[0];
                    $m2 = $t[1];
                }

                $hh = $h1 + $h2;
                $mm = $m1 + $m2;
                if ($mm > 30) {
                    $hh += 1;
                    $mm = '00';
                }

                $uT = sprintf('%02d:%02d', $hh, $mm);

                $cond   = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                $duty   = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                $dutyId = $duty['dutyId'];

                $cond   = ['dutyId' => $dutyId];
                $data   = ['utOut1' => $utOut1, 'utOut2' => $utOut2, 'uT' => $uT];
                $update = $this->promo_model->update('tk', 'perday', $cond, $data);

                echo json_encode(['message' => 'success', 'feedback' => 'Manual undertime has been submitted.']);
            } else {
                echo json_encode(['message' => 'failed']);
            }
        } else {
            echo json_encode(['message' => "Manual undertime already exist for this employee on the selected date."]);
        }
    }

    public function getSC($empId, $bioM, $date)
    {
        $cond = "bioMetricId='$bioM' OR barcodeId='$bioM'";
        $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
        if ($assign['shiftCode']) {
            $shiftCode = $assign['shiftCode'];
        }

        $day_sched = date('l', strtotime($date));
        $cond = ['empId' => $empId, 'day_sched' => $day_sched];
        $pds = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
        if ($pds) {
            $shiftCode = $pds['shiftCode'];
        }

        $cond = "empId='$empId' AND status='active' AND status2='Approved' AND '$date' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
        $ams = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond);
        if ($ams) {
            $shiftCode = $ams['shiftCode'];
        }

        return $shiftCode;
    }

    public function store_code_list()
    {
        $data = [];
        $query = $this->promo_model->selectAll('tk', 'promo_store_code');
        foreach ($query as $key => $row) {
            $id         = $row['id'];
            $baseId     = $row['baseId'];
            $name       = $row['store_name'];
            $field      = $row['bunit_field'];
            $terminal   = $row['terminal'];
            $bc         = $row['bc'];
            $cc         = $row['cc'];
            $code       = $row['store_code'];
            $status     = $row['status'];

            $func   = 'activate';
            $fabulb = '<i class="fa fa-lightbulb text-secondary ml-2"></i>';
            $status = '<span class="badge bg-warning">' . $row['status'] . '</span>';
            if ($row['status'] == 'active') {
                $func   = 'deactivate';
                $fabulb = '<i class="fa fa-lightbulb text-warning ml-2"></i>';
                $status = '<span class="badge bg-success">' . $row['status'] . '</span>';
            }

            $action = '<a href="javascript:;" onclick="storeCodes(\'' . $id . '\',\'delete\')"><i class="fa fa-trash text-danger"></i></a>';
            $action .= '<a href="javascript:;" onclick="updateStoreCode(\'' . $id . '\')"><i class="fa fa-edit text-primary ml-2"></i></a>';
            $action .= '<a href="javascript:;" onclick="storeCodes(\'' . $id . '\',\'' . $func . '\')">' . $fabulb . '</a>';

            $col    = [];
            $col[]  = $baseId;
            $col[]  = $name;
            $col[]  = $field;
            $col[]  = $terminal;
            $col[]  = $cc;
            $col[]  = $bc;
            $col[]  = $code;
            $col[]  = $status;
            $col[]  = $action;
            $data[] = $col;
        }

        echo json_encode(['data' => $data]);
    }

    public function addStoreCode()
    {
        $input      = $this->input->post(NULL, TRUE);
        $baseId     = $input['baseId'];
        $store      = $input['store_name'];
        $field      = $input['bunit_field'];
        $terminal   = $input['terminal'];
        $bc         = $input['bc'];
        $cc         = $input['cc'];
        $storeCode  = $input['storeCode'];

        $cond = ['baseId' => $baseId];
        $check = $this->promo_model->select_rowArray('tk', 'promo_store_code', $cond);
        if (!$check) {
            $data = [
                'baseId'        => $baseId,
                'store_name'    => $store,
                'bunit_field'   => $field,
                'terminal'      => $terminal,
                'bc'            => $bc,
                'cc'            => $cc,
                'store_code'     => $storeCode,
                'status'        => 'active'
            ];

            // var_dump($data);
            $insert = $this->promo_model->insert('tk', 'promo_store_code', $data);
            if ($insert) {
                echo json_encode(['message' => 'success', 'feedback' => 'Promo Store Code has been added.']);
            } else {
                echo json_encode(['message' => 'failed']);
            }
        } else {
            echo json_encode(['message' => 'Promo Store Code already exist.']);
        }
    }

    public function updateStoreCode()
    {
        $input  = $this->input->post(NULL, TRUE);
        $id     = $input['id'];

        $cond       = ['id' => $id];
        $psc        = $this->promo_model->select_rowArray('tk', 'promo_store_code', $cond);
        $store      = $psc['store_name'];
        $baseId     = $psc['baseId'];
        $field      = $psc['bunit_field'];
        $terminal   = $psc['terminal'];
        $bc         = $psc['bc'];
        $cc         = $psc['cc'];
        $storeCode  = $psc['store_code'];

        echo '<div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="store_name">Store Name</label>
                        <input type="hidden" name="process" value="edit" required>
                        <input type="hidden" name="codeId" value="' . $id . '" required>';
        echo            '<select class="form-control" id="store_name" name="store_name" onchange="getField(this,\'update_storeCode_form\')" required>
                            <option value="">---</option>';
        $bus = $this->promo_model->locate_promo_bu($this->hrCode);
        foreach ($bus as $key => $row) {
            $val = 'data-field="' . $row['bunit_field'] . '" data-bc="' . $row['bc'] . '" ';
            $val .= 'data-cc="' . $row['cc'] . '" data-bu="' . $row['bu'] . '"';
            $selected = ($store == $row['bunit_name']) ? 'selected' : '';
            echo            '<option value="' . $row['bunit_name'] . '" ' . $val . ' ' . $selected . '>' . $row['bunit_name'] . '</option>';
        }
        echo            '</select>';
        echo        '</div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="bunit_field">Field</label>
                        <input type="text" class="form-control" id="bunit_field" name="bunit_field" value="' . $field . '" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="baseId">Base ID</label>
                        <input type="text" class="form-control" id="baseId" name="baseId" value="' . $baseId . '" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="terminal">Terminal</label>
                        <select class="form-control" id="terminal" name="terminal" required>
                            <option value="">---</option>
                            <option value="Logbox" ' . ($terminal == 'Logbox' ? 'selected' : '') . '>Logbox</option>
                            <option value="Biometric" ' . ($terminal == 'Biometric' ? 'selected' : '') . '>Biometric</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="cc">CC</label>
                        <input type="text" class="form-control" id="cc" name="cc" value="' . $cc . '">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="bc">BC</label>
                        <input type="text" class="form-control" id="bc" name="bc" value="' . $bc . '">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="storeCode">Store Code</label>
                        <input type="text" class="form-control" id="storeCode" name="storeCode" value="' . $storeCode . '">
                    </div>
                </div>';
        echo '</div>';
    }

    public function storeCodes()
    {
        $input      = $this->input->post(NULL, TRUE);
        $process    = $input['process'];

        if ($process == 'delete') {
            $cond = ['id' => $input['id']];
            $delete = $this->promo_model->delete('tk', 'promo_store_code', $cond);
            if ($delete) {
                echo json_encode(['message' => 'success', 'feedback' => 'Promo Store Code has been deleted.']);
            } else {
                echo json_encode(['message' => 'failed']);
            }
        } else if ($process == 'edit') {
            $id         = $input['codeId'];
            $baseId     = $input['baseId'];
            $store      = $input['store_name'];
            $field      = $input['bunit_field'];
            $terminal   = $input['terminal'];
            $bc         = $input['bc'];
            $cc         = $input['cc'];
            $storeCode  = $input['storeCode'];

            $cond = ['baseId' => $baseId, 'id !=' => $id];
            $check = $this->promo_model->select_rowArray('tk', 'promo_store_code', $cond);
            if (!$check) {
                $cond = ['id' => $id];
                $data = [
                    'baseId'        => $baseId,
                    'store_name'    => $store,
                    'bunit_field'   => $field,
                    'terminal'      => $terminal,
                    'bc'            => $bc,
                    'cc'            => $cc,
                    'store_code'     => $storeCode
                ];

                $update = $this->promo_model->update('tk', 'promo_store_code', $cond, $data);
                if ($update) {
                    echo json_encode(['message' => 'success', 'feedback' => 'Promo Store Code has been updated.']);
                } else {
                    echo json_encode(['message' => 'failed']);
                }
            } else {
                echo json_encode(['message' => 'Promo Store Code already exist.']);
            }
        } else if ($process == 'activate') {
            $cond = ['id' => $input['id']];
            $data = ['status' => 'active'];
            $update = $this->promo_model->update('tk', 'promo_store_code', $cond, $data);
            if ($update) {
                echo json_encode(['message' => 'success', 'feedback' => 'Promo Store Code has been activated.']);
            } else {
                echo json_encode(['message' => 'failed']);
            }
        } else if ($process == 'deactivate') {
            $cond = ['id' => $input['id']];
            $data = ['status' => 'inactive'];
            $update = $this->promo_model->update('tk', 'promo_store_code', $cond, $data);
            if ($update) {
                echo json_encode(['message' => 'success', 'feedback' => 'Promo Store Code has been deactivated.']);
            } else {
                echo json_encode(['message' => 'failed']);
            }
        }
    }
}
