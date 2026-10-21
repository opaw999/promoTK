<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Duty extends CI_Controller
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

    // public function searchManualLate()
    // {
    //     echo '<ul class="list-group">';
    //     $input  = $this->input->post(NULL, TRUE);
    //     $string = $input['string'];
    //     $query  = $this->promo_model->searchManualInputs($string);
    //     if ($query) {
    //         foreach ($query as  $row) {
    //             $name       = ucwords(strtolower($row['name']));
    //             $empId      = $row['emp_id'];
    //             $recordNo   = $row['record_no'];
    //             $statCut    = $row['statCut'];

    //             $allowId = $row['allowId'];
    //             if ($allowId == "Biometric ID") {
    //                 $bioM = $row['bioMetricId'];
    //             }

    //             if ($allowId == "Logbox ID") {
    //                 $bioM = $row['barcodeId'];
    //             }

    //             $onclick = 'onclick="selectLate(\'' . $name . '\',\'' . $empId . '\')"';
    //             echo '<a href="javascript:;" class="list-group-item" ' . $onclick . '>[' . $empId . '] ' . $name . '</a>';
    //         }
    //     } else {
    //         echo '<span class="list-group-item">No Results Found!</span>';
    //     }
    //     echo '</ul>';
    // }

    // public function manualLate()
    // {
    //     $input  = $this->input->post(NULL, TRUE);
    //     $empId  = $input['lateEmpId'];
    //     $date   = $input['date_late'];
    //     $type   = $input['type'];
    //     $late   = ($type == 'late 1') ? 'utIn1' : 'utIn2';
    //     $time   = ($type == 'late 1') ? $input['late1'] : $input['late2'];

    //     $cond   = ['empId' => $empId, 'date' => $date, 'type' => $type,];
    //     $check1 = $this->promo_model->select_rowArray('tk', 'manualinputs', $cond);

    //     $cond   = ['empId' => $empId, 'dateDuty' => $date, 'type' => $type,];
    //     $check2 = $this->promo_model->select_rowArray('tk', 'manual_ext', $cond);

    //     if (!$check1 && !$check2) {
    //         $data = [
    //             'empId'         => $empId,
    //             'date'          => $date,
    //             'type'          => $type,
    //             'assignedBy'    => $this->userId,
    //             'inputDate'     => date('Y-m-d'),
    //             'inputTime'     => date('h:i A'),
    //         ];

    //         $insert1 = $this->promo_model->insert('tk', 'manualinputs', $data);

    //         $data = [
    //             'empId'     => $empId,
    //             'dateDuty'  => $date,
    //             'type'      => $type,
    //             $late       => date('H:i:s', strtotime($time))
    //         ];

    //         // var_dump($data);

    //         $insert2 = $this->promo_model->insert('tk', 'manual_ext', $data);

    //         if ($insert1 && $insert2) {
    //             echo json_encode(['message' => 'success', 'feedback' => 'Manual late has been submitted.']);
    //         } else {
    //             echo json_encode(['message' => 'failed']);
    //         }
    //     } else {
    //         echo json_encode(['message' => 'Manual late already exist for this employee on the selected date.']);
    //     }
    // }

    // public function searchManualUndertime()
    // {
    //     echo '<ul class="list-group">';
    //     $input  = $this->input->post(NULL, TRUE);
    //     $string = $input['string'];
    //     $query  = $this->promo_model->searchManualInputs($string);
    //     if ($query) {
    //         foreach ($query as  $row) {
    //             $name       = ucwords(strtolower($row['name']));
    //             $empId      = $row['emp_id'];
    //             $recordNo   = $row['record_no'];
    //             $statCut    = $row['statCut'];

    //             $allowId = $row['allowId'];
    //             if ($allowId == "Biometric ID") {
    //                 $bioM = $row['bioMetricId'];
    //             }

    //             if ($allowId == "Logbox ID") {
    //                 $bioM = $row['barcodeId'];
    //             }

    //             $onclick = 'onclick="selectUndertime(\'' . $name . '\',\'' . $empId . '\')"';
    //             echo '<a href="javascript:;" class="list-group-item" ' . $onclick . '>[' . $empId . '] ' . $name . '</a>';
    //         }
    //     } else {
    //         echo '<span class="list-group-item">No Results Found!</span>';
    //     }
    //     echo '</ul>';
    // }

    // public function manualUndertime()
    // {
    //     $input  = $this->input->post(NULL, TRUE);
    //     $empId  = $input['undertimeEmpId'];
    //     $date   = $input['date_undertime'];
    //     $type   = $input['type'];

    //     $field  = ($type == 'undertime 1') ? 'utOut1' : 'utOut2';
    //     $hours  = ($type == 'undertime 1') ? $input['undertime1'] : $input['undertime2'];

    //     $t = explode(".", $hours);
    //     $h = isset($t[0]) ? $t[0] * 1 : 0;
    //     $m = isset($t[1]) ? $t[1] * 1 : 0;  // will be 0 if no decimal

    //     $ut = "-$h hours";
    //     if ($m == 5) {
    //         $ut = "-$h hours, -30 minutes";
    //     }

    //     $cond = "empId='$empId' AND barcodeId NOT LIKE '%\_%' AND bioMetricId NOT LIKE '%\_%' ORDER BY assignId DESC LIMIT 1";
    //     $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
    //     $shiftCode = $assign['shiftCode'];

    //     $allowId = $assign['allowId'];
    //     if ($allowId == "Biometric ID") {
    //         $bioM = $assign['bioMetricId'];
    //     }
    //     if ($allowId == "Logbox ID") {
    //         $bioM = $assign['barcodeId'];
    //     }

    //     $sc = $this->getSC($empId, $bioM, $date);
    //     if ($sc) {
    //         $shiftCode = $sc;
    //     }

    //     $cond = ['shiftCode' => $shiftCode];
    //     $sc = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
    //     if ($sc) {
    //         $s_out1 = $sc['1stOut'];
    //         $s_out2 = $sc['2ndOut'];
    //     }

    //     $cond = ['shiftCode' => $shiftCode];
    //     $scb = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
    //     if ($scb) {
    //         $s_out1 = $scb['1stOut'];
    //         $s_out2 = $scb['2ndOut'];
    //     }

    //     $out1   = date("H:i:s", strtotime("$s_out1 $ut"));
    //     $out2   = date("H:i:s", strtotime("$s_out2 $ut"));
    //     $time   = ($type == 'undertime 1') ? $out1 : $out2;

    //     $cond   = ['empId' => $empId, 'date' => $date, 'type' => $type];
    //     $check1 = $this->promo_model->select_rowArray('tk', 'manualinputs', $cond);
    //     $cond   = ['empId' => $empId, 'dateDuty' => $date, 'type' => $type];
    //     $check2 = $this->promo_model->select_rowArray('tk', 'manual_ext', $cond);
    //     if (!$check1 && !$check2) {

    //         $data = [
    //             'empId'         => $empId,
    //             'date'          => $date,
    //             'type'          => $type,
    //             'assignedBy'    => $this->userId,
    //             'inputDate'     => date('Y-m-d'),
    //             'inputTime'     => date('h:i A')
    //         ];

    //         $insert1 = $this->promo_model->insert('tk', 'manualinputs', $data);

    //         $data = ['empId' => $empId, 'dateDuty' => $date, 'type' => $type, $field => $time];
    //         $insert2 = $this->promo_model->insert('tk', 'manual_ext', $data);

    //         if ($insert1 && $insert2) {

    //             $utOut1 = $utOut2 = $uT = '00:00';
    //             $cond = ['empId' => $empId, 'dateDuty' => $date, 'type' => 'undertime 1'];
    //             $me1 = $this->promo_model->select_rowArray('tk', 'manual_ext', $cond);
    //             if ($me1) {

    //                 $meUt1 = $me1['utOut1'];
    //                 $t = explode(":", $meUt1);
    //                 $h = $t[0];
    //                 $m = $t[1];

    //                 $utOut1 = date("H:i", strtotime("$s_out1 -$h hours, -$m minutes"));
    //             }

    //             $cond = ['empId' => $empId, 'dateDuty' => $date, 'type' => 'undertime 2'];
    //             $me2 = $this->promo_model->select_rowArray('tk', 'manual_ext', $cond);
    //             if ($me2) {

    //                 $meUt2 = $me2['utOut2'];
    //                 $t = explode(":", $meUt2);
    //                 $h = $t[0];
    //                 $m = $t[1];

    //                 $utOut2 = date("H:i", strtotime("$s_out2 -$h hours, -$m minutes"));
    //             }

    //             $h1 = $m1 = $h2 = $m2 = 0;
    //             if ($utOut1 != '00:00') {
    //                 $t  = explode(":", $utOut1);
    //                 $h1 = $t[0];
    //                 $m1 = $t[1];
    //             }

    //             if ($utOut2 != '00:00') {
    //                 $t  = explode(":", $utOut2);
    //                 $h2 = $t[0];
    //                 $m2 = $t[1];
    //             }

    //             $hh = $h1 + $h2;
    //             $mm = $m1 + $m2;
    //             if ($mm > 30) {
    //                 $hh += 1;
    //                 $mm = '00';
    //             }

    //             $uT = sprintf('%02d:%02d', $hh, $mm);

    //             $cond   = ['bioMetricId' => $bioM, 'dateDuty' => $date];
    //             $duty   = $this->promo_model->select_rowArray('tk', 'duty', $cond);
    //             $dutyId = $duty['dutyId'];

    //             $cond   = ['dutyId' => $dutyId];
    //             $data   = ['utOut1' => $utOut1, 'utOut2' => $utOut2, 'uT' => $uT];
    //             $update = $this->promo_model->update('tk', 'perday', $cond, $data);

    //             echo json_encode(['message' => 'success', 'feedback' => 'Manual undertime has been submitted.']);
    //         } else {
    //             echo json_encode(['message' => 'failed']);
    //         }
    //     } else {
    //         echo json_encode(['message' => "Manual undertime already exist for this employee on the selected date."]);
    //     }
    // }

    // public function getSC($empId, $bioM, $date)
    // {
    //     $cond = "bioMetricId='$bioM' OR barcodeId='$bioM'";
    //     $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
    //     if ($assign['shiftCode']) {
    //         $shiftCode = $assign['shiftCode'];
    //     }

    //     $day_sched = date('l', strtotime($date));
    //     $cond = ['empId' => $empId, 'day_sched' => $day_sched];
    //     $pds = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
    //     if ($pds) {
    //         $shiftCode = $pds['shiftCode'];
    //     }

    //     $cond = "empId='$empId' AND status='active' AND status2='Approved' AND '$date' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
    //     $ams = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond);
    //     if ($ams) {
    //         $shiftCode = $ams['shiftCode'];
    //     }

    //     return $shiftCode;
    // }

    public function searchPromoDuty()
    {
        echo '<ul class="list-group">';
        $input  = $this->input->post(NULL, TRUE);
        $string = $input['string'];
        $query  = $this->promo_model->searchEmployee($string);
        if ($query) {
            foreach ($query as  $row) {
                $name   = ucwords(strtolower($row['name']));
                $empId  = $row['emp_id'];
                $onclick = 'onclick="selectDuty(\'' . $name . '\',\'' . $empId . '\')"';
                echo '<a href="javascript:;" class="list-group-item" ' . $onclick . '>[' . $empId . '] ' . $name . '</a>';
            }
        } else {
            echo '<span class="list-group-item">No Results Found!</span>';
        }
        echo '</ul>';
    }

    public function dutyInsert()
    {
        $input      = $this->input->post(NULL, TRUE);
        $empId      = $input['empId'];
        $dateFrom   = $input['dateFrom'];
        $dateTo     = $input['dateTo'];

        $cond   = "empId='$empId' AND barcodeId NOT LIKE '%\_%' AND bioMetricId NOT LIKE '%\_%' ORDER BY assignId DESC LIMIT 1";
        $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
        $allowId = $assign['allowId'];
        if ($allowId == "Biometric ID") {
            $bioM   = $assign['bioMetricId'];
        }
        if ($allowId == "Logbox ID") {
            $bioM   = $assign['barcodeId'];
        }

        $df = date('Y-m', strtotime($dateFrom));
        $dt = date('Y-m', strtotime($dateTo));
        while (strtotime($df) <= strtotime($dt)) {

            $start = date('Y-m-1', strtotime($df));
            $end = date('Y-m-t', strtotime($df));
            while ($start <= $end) {

                $date = date("Y-m-d", strtotime("$start"));

                $cond   = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                $check  = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                if (!$check) {
                    $data = ['bioMetricId' => $bioM, 'date' => $date, 'dateDuty' => $date, 'status' => 'Pending'];
                    $insert = $this->promo_model->insert('tk', 'duty', $data);
                }

                $start = date("Y-m-d", strtotime("$start +1 day"));
            }

            $df = date("Y-m", strtotime("$df +1 month"));
        }

        echo json_encode(['message' => 'success', 'feedback' => 'Duty table has been filled.']);
    }
}
