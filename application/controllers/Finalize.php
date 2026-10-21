<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Finalize extends CI_Controller
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
            $this->userId = $_SESSION['tkEmpId'];
            $this->hrCode = $_SESSION['hrCode'];
            $this->userName = $_SESSION['usr'];
        }
    }

    public function finalizeDtr()
    {
        $input      = $this->input->post(NULL, TRUE);
        $statCut    = $input['statCut'];
        $cut        = $input['cut'];
        $df         = date('Y-m-d', strtotime($input['df']));
        $dt         = date('Y-m-d', strtotime($input['dt']));
        $co_table   = ($cut == 1) ? '1stcutoff' : '2ndcutoff';
        $userName   = $this->userName;

        set_time_limit(0);
        ob_implicit_flush(true);
        ob_end_flush();

        echo json_encode(array(
            'result'    => 'loading',
            'msg'       => round(0) . '%',
            'msg1'      => round(0) . '%',
            'date'      => "Preparing DTR...",
            'table'     => "",
            'exported'  => 'table_update',
        ));

        $duty_temp = "duty_temp_promo_$userName";
        $create = $this->promo_model->duty_temp_user($duty_temp);

        $perday_temp = "perday_temp_promo_$userName";
        $create = $this->promo_model->perday_temp_user($perday_temp);

        echo json_encode(array(
            'result'    => 'loading',
            'msg'       => round(0) . '%',
            'msg1'      => round(0) . '%',
            'date'      => "Processing DTR...",
            'table'     => "",
            'exported'  => 'table_update',
        ));

        sleep(2);

        $percentage = 0;
        $percent = 100 / count($input['empIds']);

        $empIds = $input['empIds'];
        foreach ($empIds as $key => $empId) {

            $cond = ['emp_id' => $empId];
            $emp = $this->promo_model->select_rowArray('pis', 'employee3', $cond);
            $promo = $this->promo_model->select_rowArray('pis', 'promo_record', $cond);
            $promo_type = $promo['promo_type'];
            $sd = $emp['startdate'];
            $ed = $emp['eocdate'];
            $ed = ($emp['eocdate'] != '' || $emp['eocdate'] != '0000-00-00') ? $emp['eocdate'] : date('Y-m-d');

            $empType = $emp['emp_type'];

            $cond = "empId='$empId' AND barcodeId NOT LIKE '%\_%' AND bioMetricId NOT LIKE '%\_%' ORDER BY assignId DESC LIMIT 1";
            $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
            $allowId = $assign['allowId'];
            if ($allowId == "Biometric ID") {
                $bioM = $assign['bioMetricId'];
            }
            if ($allowId == "Logbox ID") {
                $bioM = $assign['barcodeId'];
            }

            $cond = ['dateFrom' => $df, 'dateTo' => $dt, 'statCut' => $statCut, 'bioMetricId' => $bioM];
            $co = $this->promo_model->select_rowArray('tk', $co_table, $cond);
            $coId = ($cut == 1) ? $co['fCOId'] : $co['sCOId'];

            $percentage += $percent;

            // calculate hoursWork for request
            if (true) {

                // SUSPENSION
                if (true) {
                    $cond = "empId='$empId' AND (dateSched BETWEEN '$df' AND '$dt')";
                    $query = $this->promo_model->select_resultArray('vsm', 'sched_suspension', $cond);
                    foreach ($query as $key => $ss) {

                        $date = $ss['dateSched'];
                        $cond = ['bioMetricId' => $bioM, 'date' => $date];
                        $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                        if ($duty) {

                            $d_in1  = $duty['in1'];
                            $d_out1 = $duty['out1'];
                            if ($ss['store1'] == '') {
                                $d_in1 = '';
                                $d_out1 = '';
                            }

                            $d_in2  = $duty['in2'];
                            $d_out2 = $duty['out2'];
                            if ($ss['store2'] == '') {
                                $d_in2 = '';
                                $d_out2 = '';
                            }

                            $d_in3  = $duty['in3'];
                            $d_out3 = $duty['out3'];

                            $this->netEntery($empId, $bioM, $date, $d_in1, $d_out1, $d_in2, $d_out2, $d_in3, $d_out3);
                        }
                    }
                }

                // DWA - Fieldwork & Business Trip Approved && Special
                if (true) {
                    $cond = "empId='$empId' AND (date BETWEEN '$df' AND '$dt')";
                    $query = $this->promo_model->select_resultArray('tk', 'off_date', $cond);
                    foreach ($query as $key => $od) {

                        $date       = $od['date'];
                        $od_day     = $od['day'];
                        $btfw_ID    = $od['btfw_ID'];

                        $cond = ['empId' => $empId, 'adj_errfai_id' => $btfw_ID, 'status' => 'Approved'];
                        $dwa = $this->promo_model->select_rowArray('tk', 'd_w_adjustment', $cond);
                        if ($dwa) {

                            $duty_in1 = $duty_out1 = $duty_in2 = $duty_out2 = $duty_in3 = $duty_out3 = "";
                            $cond = ['bioMetricId' => $bioM, 'date' => $date];
                            $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                            if ($duty) {

                                $duty_in1   = $duty['in1'];
                                $duty_out1  = $duty['out1'];
                                $duty_in2   = $duty['in2'];
                                $duty_out2  = $duty['out2'];
                                $duty_in3   = $duty['in3'];
                                $duty_out3  = $duty['out3'];

                                $cond = ['bioMetricId' => $bioM, 'dateRendered' => $date];
                                $check = $this->promo_model->select_rowArray('tk', $duty_temp, $cond);
                                if (!$check) {

                                    $data = [
                                        'bioMetricId'   => $bioM,
                                        'dateRendered'  => $date,
                                        'in1'           => $duty_in1,
                                        'out1'          => $duty_out1,
                                        'in2'           => $duty_in2,
                                        'out2'          => $duty_out2,
                                        'in3'           => $duty_in3,
                                        'out3'          => $duty_out3,
                                    ];

                                    $insert = $this->promo_model->insert('tk', $duty_temp, $data);
                                }
                            }

                            $shiftCode = $this->getSC($empId, $bioM, $date);

                            $cond = ['shiftCode' => $shiftCode];
                            $sc = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
                            if ($sc) {
                                $in1    = $sc['1stIn'];
                                $out1   = $sc['1stOut'];
                                $in2    = $sc['2ndIn'];
                                $out2   = $sc['2ndOut'];
                            }

                            $cond = ['empId' => $empId, 'btfw_ID' => $btfw_ID];
                            $check = $this->promo_model->select_rowArray('tk', 'dwa_special_as', $cond);
                            if ($check) {

                                if ($in1) {
                                    $duty_in1 = date('H:i:s', strtotime($in1));
                                }

                                if ($out1) {
                                    $duty_out1 = date('H:i:s', strtotime($out1));
                                }

                                if ($in2) {
                                    $duty_in2 = date('H:i:s', strtotime($in2));
                                }

                                if ($out2) {
                                    $duty_out2 = date('H:i:s', strtotime($out2));
                                }
                            }

                            if (!empty($in1) && !empty($out1) && !empty($in2) && !empty($out2)) {

                                $n_in1 = date('H:i:s', strtotime($in1));
                                if (!empty($duty_in1)) {
                                    $n_in1 = $duty_in1;
                                }

                                $n_out1 = date('H:i:s', strtotime($out1));
                                if (!empty($duty_out1)) {
                                    $n_out1 = $duty_out1;
                                }

                                $n_in2 = date('H:i:s', strtotime($in2));
                                if (!empty($duty_in2)) {
                                    $n_in2 = $duty_in2;
                                }

                                $n_out2 = date('H:i:s', strtotime($out2));
                                if (!empty($duty_out2)) {
                                    $n_out2 = $duty_out2;
                                }

                                if ($od_day == "Whole Day") {

                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $data = ['in1' => $n_in1, 'out1' => $n_out1, 'in2' => $n_in2, 'out2' => $n_out2];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                                }

                                if ($od_day == "Half Day AM") {

                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $data = ['in1' => $n_in1, 'out1' => $n_out1];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                                }

                                if ($od_day == "Half Day PM") {

                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $data = ['in2' => $n_in2, 'out2' => $n_out2];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                                }
                            }

                            if (!empty($in1) && !empty($out1) && empty($in2) && empty($out2)) {

                                $n_in1 = date('H:i:s', strtotime($in1));
                                if (!empty($duty_in1)) {
                                    $n_in1 = $duty_in1;
                                }

                                $n_out1 = date('H:i:s', strtotime($out1));
                                if (!empty($duty_out2)) {
                                    $n_out2 = $duty_out2;
                                }

                                if ($od_day == "Whole Day") {

                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $data = ['in1' => $n_in1, 'out1' => '', 'in2' => '', 'out2' => $n_out2];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                                }
                            }

                            $cond = ['bioMetricId' => $bioM, 'date' => $date];
                            $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                            if ($duty) {
                                $d_in1  = $duty['in1'];
                                $d_out1 = $duty['out1'];
                                $d_in2  = $duty['in2'];
                                $d_out2 = $duty['out2'];
                                $d_in3  = $duty['in3'];
                                $d_out3 = $duty['out3'];

                                $this->netEntery($empId, $bioM, $date, $d_in1, $d_out1, $d_in2, $d_out2, $d_in3, $d_out3);

                                $cond = ['empId' => $empId, 'date' => $date];
                                $query = $this->promo_model->select_resultArray('tk', 'manualinputs', $cond);
                                foreach ($query as $key => $row) {
                                    $type = $row['type'];
                                    $cond = ['empId' => $empId, 'dateDuty' => $date, 'type' => $type];
                                    $check = $this->promo_model->select_rowArray('tk', 'manual_ext', $cond);
                                    if ($check) {

                                        if ($type == 'late 1') {
                                            $d_in1 = $check['utIn1'];
                                        }
                                        if ($type == 'late 2') {
                                            $d_in2 = $check['utIn2'];
                                        }

                                        // if ($type == 'undertime 1') {
                                        //     $d_out1 = $check['utOut1'];
                                        // }
                                        // if ($type == 'undertime 2') {
                                        //     $d_out2 = $check['utOut2'];
                                        // }

                                        $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                        $data = ['in1' => $d_in1, 'out1' => $d_out1, 'in2' => $d_in2, 'out2' => $d_out2];
                                        $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                                        if ($update) {
                                            $this->netEntery($empId, $bioM, $date, $d_in1, $d_out1, $d_in2, $d_out2, $d_in3, $d_out3);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // System Error Approved
                if (true) {

                    $cond = "empId='$empId' AND (dateFrom BETWEEN '$df' AND '$dt') AND (dateTo BETWEEN '$df' AND '$dt') AND type='System Error' AND status='Approved'";
                    $query = $this->promo_model->select_resultArray('tk', 'd_w_adjustment', $cond);
                    foreach ($query as $key => $row) {

                        $dateFrom   = $row['dateFrom'];
                        $fs_id      = $row['adj_errfai_id'];

                        $cond = ['empId' => $empId, 'err_fai_id' => $fs_id, 'date' => $dateFrom];
                        $dwaf = $this->promo_model->select_rowArray('tk', 'dwa_error_failed', $cond);
                        if ($dwaf) {

                            $date   = $dwaf['date'];
                            $c_in1  = $dwaf['in1'];
                            $c_out1 = $dwaf['out1'];
                            $c_in2  = $dwaf['in2'];
                            $c_out2 = $dwaf['out2'];
                            $c_in3  = $dwaf['in3'];
                            $c_out3 = $dwaf['out3'];

                            $cond = ['bioMetricId' => $bioM, 'date' => $date];
                            $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                            if ($duty) {

                                $duty_in1   = $duty['in1'];
                                $duty_out1  = $duty['out1'];
                                $duty_in2   = $duty['in2'];
                                $duty_out2  = $duty['out2'];
                                $duty_in3   = $duty['in3'];
                                $duty_out3  = $duty['out3'];

                                $cond = ['bioMetricId' => $bioM, 'dateRendered' => $date];
                                $check = $this->promo_model->select_rowArray('tk', $duty_temp, $cond);
                                if (!$check) {
                                    $data = [
                                        'bioMetricId'   => $bioM,
                                        'dateRendered'  => $date,
                                        'in1'           => $duty_in1,
                                        'out1'          => $duty_out1,
                                        'in2'           => $duty_in2,
                                        'out2'          => $duty_out2,
                                        'in3'           => $duty_in3,
                                        'out3'          => $duty_out3,
                                    ];
                                    $insert = $this->promo_model->insert('tk', $duty_temp, $data);
                                }
                            }

                            $shiftCode = $this->getSC($empId, $bioM, $date);

                            $shIn1 = $shOut1 = $shIn2 = $shOut2 = '';
                            $cond = "empId='$empId' AND ('$date' BETWEEN dateFrom AND dateTo) AND shiftCode='$shiftCode'";
                            $check = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond);
                            if ($check) {
                                $cond = ['shiftCode' => $shiftCode];
                                $sc = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
                                if ($sc) {
                                    $shIn1  = $sc['1stIn'];
                                    $shOut1 = $sc['1stOut'];
                                    $shIn2  = $sc['2ndIn'];
                                    $shOut2 = $sc['2ndOut'];
                                }
                            }

                            $dv_in1 = $dv_out1 = $dv_in2 = $dv_out2 = $dv_in3 = $dv_out3 = "";
                            $cond = ['empId' => $empId, 'err_fai_id' => $fs_id, 'date' => $date];
                            $dwav = $this->promo_model->select_rowArray('tk', 'dwa_err_failed_value', $cond);
                            if ($dwav) {
                                $dv_in1  = $dwav['in1'];
                                $dv_out1 = $dwav['out1'];
                                $dv_in2  = $dwav['in2'];
                                $dv_out2 = $dwav['out2'];
                                $dv_in3  = $dwav['in3'];
                                $dv_out3 = $dwav['out3'];
                            }

                            if ($c_in1 == "True") {
                                if ($check) {
                                    if ($shIn1) {
                                        $v_in1 = date('H:i:s', strtotime($shIn1));
                                    } else if ($dv_in1) {
                                        $v_in1 = date('H:i:s', strtotime($dv_in1));
                                    } else {
                                        $v_in1 = date('H:i:s', strtotime($shIn1));
                                    }
                                } else {
                                    $v_in1 = date('H:i:s', strtotime($dv_in1));
                                }

                                $data = ['in1' => $v_in1];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                            }

                            if ($c_out1 == "True") {
                                if ($check) {
                                    if ($shOut1) {
                                        $v_out1 = date('H:i:s', strtotime($shOut1));
                                    } else if ($dv_out1) {
                                        $v_out1 = date('H:i:s', strtotime($dv_out1));
                                    } else {
                                        $v_out1 = date('H:i:s', strtotime($shOut1));
                                    }
                                } else {
                                    $v_out1 = date('H:i:s', strtotime($dv_out1));
                                }

                                $data = ['out1' => $v_out1];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                            }

                            if ($c_in2 == "True") {
                                if ($check) {
                                    if ($shIn2) {
                                        $v_in2 = date('H:i:s', strtotime($shIn2));
                                    } else if ($dv_in2) {
                                        $v_in2 = date('H:i:s', strtotime($dv_in2));
                                    } else {
                                        $v_in2 = date('H:i:s', strtotime($shIn2));
                                    }
                                } else {
                                    $v_in2 = date('H:i:s', strtotime($dv_in2));
                                }

                                $data = ['in2' => $v_in2];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                            }

                            if ($c_out2 == "True") {
                                if ($check) {
                                    if ($shOut2) {
                                        $v_out2 = date('H:i:s', strtotime($shOut2));
                                    } else if ($dv_out2) {
                                        $v_out2 = date('H:i:s', strtotime($dv_out2));
                                    } else {
                                        $v_out2 = date('H:i:s', strtotime($shOut2));
                                    }

                                    // if ($dv_out2) {
                                    //     $v_out2 = date('H:i:s', strtotime($dv_out2));
                                    // }
                                } else {
                                    $v_out2 = date('H:i:s', strtotime($dv_out2));
                                }

                                $data = ['out2' => $v_out2];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                            }

                            if ($c_in3 == "True") {
                                $v_in3 = date('H:i:s', strtotime($dv_in3));

                                $data = ['in3' => $v_in3];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                            }

                            if ($c_out3 == "True") {
                                $v_out3 = date('H:i:s', strtotime($dv_out3));

                                $data = ['out3' => $v_out3];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                            }

                            $cond = ['bioMetricId' => $bioM, 'date' => $date];
                            $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                            if ($duty) {
                                $d_in1  = $duty['in1'];
                                $d_out1 = $duty['out1'];
                                $d_in2  = $duty['in2'];
                                $d_out2 = $duty['out2'];
                                $d_in3  = $duty['in3'];
                                $d_out3 = $duty['out3'];

                                $this->netEntery($empId, $bioM, $date, $d_in1, $d_out1, $d_in2, $d_out2, $d_in3, $d_out3);

                                $cond = ['empId' => $empId, 'date' => $date];
                                $query = $this->promo_model->select_resultArray('tk', 'manualinputs', $cond);
                                foreach ($query as $key => $row) {
                                    $type = $row['type'];
                                    $cond = ['empId' => $empId, 'dateDuty' => $date, 'type' => $type];
                                    $check = $this->promo_model->select_rowArray('tk', 'manual_ext', $cond);
                                    if ($check) {

                                        if ($type == 'late 1') {
                                            $d_in1 = $check['utIn1'];
                                        }
                                        if ($type == 'late 2') {
                                            $d_in2 = $check['utIn2'];
                                        }

                                        // if ($type == 'undertime 1') {
                                        //     $d_out1 = $check['utOut1'];
                                        // }
                                        // if ($type == 'undertime 2') {
                                        //     $d_out2 = $check['utOut2'];
                                        // }

                                        $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                        $data = ['in1' => $d_in1, 'out1' => $d_out1, 'in2' => $d_in2, 'out2' => $d_out2];
                                        $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                                        if ($update) {
                                            $this->netEntery($empId, $bioM, $date, $d_in1, $d_out1, $d_in2, $d_out2, $d_in3, $d_out3);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // Failed to Log In or Out, Wrong Mode, Wrong User Approved
                if (true) {
                    for ($xcu = 1; $xcu <= 2; $xcu++) {

                        if ($xcu == 1) {
                            $cond = "empId='$empId' AND (dateFrom BETWEEN '$df' AND '$dt') AND (dateTo BETWEEN '$df' AND '$dt') AND status='Approved' AND (type='Failure' OR type='Wrong User')";
                        }

                        if ($xcu == 2) {
                            $cond = "empId='$empId' AND (dateFrom BETWEEN '$df' AND '$dt') AND (dateTo BETWEEN '$df' AND '$dt') AND status!='Pending' AND type='Wrong Mode'";
                        }
                        // echo json_encode(array(
                        //     'result'    => 'loading',
                        //     'msg'       => '%',
                        //     'msg1'      => '%',
                        //     'date'      => "get hwsss",
                        //     'table'     => "[empId: $empId; cond: $cond]",
                        //     'exported'  => 'table_update'
                        // ));

                        $query = $this->promo_model->select_resultArray('tk', 'd_w_adjustment', $cond);
                        foreach ($query as $key => $row) {

                            $dateFrom   = $row['dateFrom'];
                            $fs_id      = $row['adj_errfai_id'];

                            $cond = ['empId' => $empId, 'err_fai_id' => $fs_id, 'date' => $dateFrom];
                            $dwaf = $this->promo_model->select_rowArray('tk', 'dwa_error_failed', $cond);
                            if ($dwaf) {

                                $date   = $dwaf['date'];
                                $c_in1  = $dwaf['in1'];
                                $c_out1 = $dwaf['out1'];
                                $c_in2  = $dwaf['in2'];
                                $c_out2 = $dwaf['out2'];
                                $c_in3  = $dwaf['in3'];
                                $c_out3 = $dwaf['out3'];

                                $cond = ['bioMetricId' => $bioM, 'date' => $date];
                                $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                                if ($duty) {

                                    $duty_in1   = $duty['in1'];
                                    $duty_out1  = $duty['out1'];
                                    $duty_in2   = $duty['in2'];
                                    $duty_out2  = $duty['out2'];
                                    $duty_in3   = $duty['in3'];
                                    $duty_out3  = $duty['out3'];

                                    $cond = ['bioMetricId' => $bioM, 'dateRendered' => $date];
                                    $check = $this->promo_model->select_rowArray('tk', $duty_temp, $cond);
                                    if (!$check) {
                                        $data = [
                                            'bioMetricId'   => $bioM,
                                            'dateRendered'  => $date,
                                            'in1'           => $duty_in1,
                                            'out1'          => $duty_out1,
                                            'in2'           => $duty_in2,
                                            'out2'          => $duty_out2,
                                            'in3'           => $duty_in3,
                                            'out3'          => $duty_out3,
                                        ];
                                        $insert = $this->promo_model->insert('tk', $duty_temp, $data);
                                    }
                                }

                                $shiftCode = $this->getSC($empId, $bioM, $date);

                                $cond   = ['shiftCode' => $shiftCode];
                                $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
                                if ($sc) {
                                    $in1    = $sc['1stIn'];
                                    $out1   = $sc['1stOut'];
                                    $in2    = $sc['2ndIn'];
                                    $out2   = $sc['2ndOut'];
                                }

                                $cond   = ['shiftCode' => $shiftCode];
                                $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
                                if ($scb) {
                                    $in1    = $scb['1stIn'];
                                    $out1   = $scb['1stOut'];
                                    $in2    = $scb['2ndIn'];
                                    $out2   = $scb['2ndOut'];
                                }

                                $day_in1    = date('A', strtotime($in1));
                                $day_out1   = date('A', strtotime($out1));
                                $day_in2    = date('A', strtotime($in2));
                                $day_out2   = date('A', strtotime($out2));

                                $dateIn1    = date("Y-m-d H:i:s", strtotime("$date $in1"));
                                $dateOut1   = date("Y-m-d H:i:s", strtotime("$date $out1"));
                                $dateIn2    = date("Y-m-d H:i:s", strtotime("$date $in2"));
                                $dateOut2   = date("Y-m-d H:i:s", strtotime("$date $out2"));

                                $next_not = false;
                                if ($day_in2 == "AM" && $day_out2 == "AM" && !$scb) {
                                    $next_not = true;
                                }

                                if ($day_out1 == "AM" && $day_in2 == "AM" && $day_out2 == "AM" && !$scb) {
                                    $next_not = true;
                                }

                                if ($scb) {
                                    if ($day_in1 == "PM" && $day_in2 == "AM" && $day_out2 == "AM") {
                                        $next_not = true;
                                    }

                                    if ($day_in1 == "PM" && $day_out1 == "AM" && $day_in2 == "AM") {
                                        $next_not = true;
                                    }
                                }


                                if ($c_in1 == "True") {
                                    $v_in1 = date('H:i:s', strtotime($in1));

                                    $data = ['in1' => $v_in1];
                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                                }

                                if ($c_out1 == "True") {
                                    $v_out1 = date('H:i:s', strtotime($out1));

                                    $data = ['out1' => $v_out1];
                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                                }

                                if ($c_in2 == "True") {
                                    $v_in2 = date('H:i:s', strtotime($in2));

                                    $data = ['in2' => $v_in2];
                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                                }

                                if ($c_out2 == "True") {
                                    $v_out2 = date('H:i:s', strtotime($out2));

                                    $data = ['out2' => $v_out2];
                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                                }

                                if ($c_in3 == "True") {
                                    $v_in3 = date('H:i:s', strtotime($in3));

                                    $data = ['in3' => $v_in3];
                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                                }

                                if ($c_out3 == "True") {
                                    $v_out3 = date('H:i:s', strtotime($out3));

                                    $data = ['out3' => $v_out3];
                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                                }

                                if ($next_not) {

                                    $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                    $dnext = $this->promo_model->select_rowArray('tk', 'duty_next', $cond);
                                    if ($dnext) {

                                        $data = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                        $insert = $this->promo_model->insert('tk', 'duty_next', $data);
                                    }

                                    if ($c_in1 == "True") {
                                        $data = ['in1_ka' => $dateIn1];
                                        $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                        $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                    }

                                    if ($c_out1 == "True") {
                                        $data = ['out1_ka' => $dateOut1];
                                        $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                        $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                    }

                                    if ($c_in2 == "True") {
                                        $data = ['in2_ka' => $dateIn2];
                                        $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                        $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                    }

                                    if ($c_out2 == "True") {
                                        $data = ['out2_ka' => $dateOut2];
                                        $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                        $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                    }
                                }

                                $cond = ['bioMetricId' => $bioM, 'date' => $date];
                                $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                                if ($duty) {

                                    $d_in1  = $duty['in1'];
                                    $d_out1 = $duty['out1'];
                                    $d_in2  = $duty['in2'];
                                    $d_out2 = $duty['out2'];
                                    $d_in3  = $duty['in3'];
                                    $d_out3 = $duty['out3'];

                                    $this->netEntery($empId, $bioM, $date, $d_in1, $d_out1, $d_in2, $d_out2, $d_in3, $d_out3);

                                    $cond = ['empId' => $empId, 'date' => $date];
                                    $query = $this->promo_model->select_resultArray('tk', 'manualinputs', $cond);
                                    foreach ($query as $key => $row) {
                                        $type = $row['type'];
                                        $cond = ['empId' => $empId, 'dateDuty' => $date, 'type' => $type];
                                        $check = $this->promo_model->select_rowArray('tk', 'manual_ext', $cond);
                                        if ($check) {

                                            if ($type == 'late 1') {
                                                $d_in1 = $check['utIn1'];
                                            }
                                            if ($type == 'late 2') {
                                                $d_in2 = $check['utIn2'];
                                            }

                                            // if ($type == 'undertime 1') {
                                            //     $d_out1 = $check['utOut1'];
                                            // }
                                            // if ($type == 'undertime 2') {
                                            //     $d_out2 = $check['utOut2'];
                                            // }

                                            $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                            $data = ['in1' => $d_in1, 'out1' => $d_out1, 'in2' => $d_in2, 'out2' => $d_out2];
                                            $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                                            if ($update) {
                                                $this->netEntery($empId, $bioM, $date, $d_in1, $d_out1, $d_in2, $d_out2, $d_in3, $d_out3);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // CoverUp  Approved
                if (true) {
                    $cond = "empId='$empId' AND (cuDate BETWEEN '$df' AND '$dt') AND status='Approved'";
                    $query = $this->promo_model->select_resultArray('tk', 'coverup_auth', $cond);
                    foreach ($query as $key => $row) {
                        $eCAId      = $row['eCAId'];
                        $date       = $row['cuDate'];
                        $cuPurpose  = $row['cuPurpose'];

                        $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = "";

                        $duty_in1 = $duty_out1 = $duty_in2 = $duty_out2 = $duty_in3 = $duty_out3 = "";
                        $cond = ['bioMetricId' => $bioM, 'date' => $date];
                        $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                        if ($duty) {

                            $duty_in1   = $duty['in1'];
                            $duty_out1  = $duty['out1'];
                            $duty_in2   = $duty['in2'];
                            $duty_out2  = $duty['out2'];
                            $duty_in3   = $duty['in3'];
                            $duty_out3  = $duty['out3'];

                            $in1    = $duty['in1'];
                            $out1   = $duty['out1'];
                            $in2    = $duty['in2'];
                            $out2   = $duty['out2'];
                            $in3    = $duty['in3'];
                            $out3   = $duty['out3'];

                            $cond = ['bioMetricId' => $bioM, 'dateRendered' => $date];
                            $check = $this->promo_model->select_rowArray('tk', $duty_temp, $cond);
                            if (!$check) {
                                $data = [
                                    'bioMetricId'   => $bioM,
                                    'dateRendered'  => $date,
                                    'in1'           => $duty_in1,
                                    'out1'          => $duty_out1,
                                    'in2'           => $duty_in2,
                                    'out2'          => $duty_out2,
                                    'in3'           => $duty_in3,
                                    'out3'          => $duty_out3,
                                ];
                                $insert = $this->promo_model->insert('tk', $duty_temp, $data);
                            }
                        }

                        $shiftCode = $this->getSC($empId, $bioM, $date);

                        $cond = ['shiftCode' => $shiftCode];
                        $sc = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
                        if ($sc) {
                            $s_in1  = $sc['1stIn'];
                            $s_out1 = $sc['1stOut'];
                            $s_in2  = $sc['2ndIn'];
                            $s_out2 = $sc['2ndOut'];
                        }

                        $cond = ['shiftCode' => $shiftCode];
                        $scb = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
                        if ($scb) {
                            $s_in1  = $scb['1stIn'];
                            $s_out1 = $scb['1stOut'];
                            $s_in2  = $scb['2ndIn'];
                            $s_out2 = $scb['2ndOut'];
                        }

                        $day_in1    = date('A', strtotime($s_in1));
                        $day_out1   = date('A', strtotime($s_out1));
                        $day_in2    = date('A', strtotime($s_in2));
                        $day_out2   = date('A', strtotime($s_out2));

                        $in1_sc     = date("H:i:s", strtotime($s_in1));
                        $out1_sc    = date("H:i:s", strtotime($s_out1));
                        $in2_sc     = date("H:i:s", strtotime($s_in2));
                        $out2_sc    = date("H:i:s", strtotime($s_out2));

                        $next_not = false;
                        if ($day_in2 == "AM" && $day_out2 == "AM" && !$scb) {
                            $next_not = true;
                        }

                        if ($day_out1 == "AM" && $day_in2 == "AM" && $day_out2 == "AM" && !$scb) {
                            $next_not = true;
                        }

                        if ($scb) {
                            if ($day_in1 == "PM" && $day_in2 == "AM" && $day_out2 == "AM") {
                                $next_not = true;
                            }

                            if ($day_in1 == "PM" && $day_out1 == "AM" && $day_in2 == "AM") {
                                $next_not = true;
                            }
                        }

                        $usedOT = 0;
                        $cond = ['eCAId' => $eCAId];
                        $query = $this->promo_model->select_resultArray('tk', 'coverup_auth_ext', $cond);
                        foreach ($query as $key => $cu) {
                            $usedOT += $cu['usedOT'];
                        }

                        $t = explode('.', $usedOT);
                        $h = isset($t[0]) ? $t[0] * 1 : 0;
                        $m = isset($t[1]) ? $t[1] * 1 : 0;  // will be 0 if no decimal

                        if ($m == 5) {
                            $m = 30;
                        }

                        if ($cuPurpose != "wholeday") {

                            $t = explode(" ", $cuPurpose);
                            $numT = $t[1] * 1;

                            if ($cuPurpose == "in 1 awol") {

                                $tempH = date("H:i", strtotime($in1 . "-$h hours, -$m minutes"));
                                $tempH2 = date("H:i:s", strtotime($in1 . "-$h hours, -$m minutes"));

                                if (strtotime("$tempH:00") <= strtotime($in1_sc)) {
                                    $f_In1 = $in1_sc;
                                } else {
                                    $f_In1 = $tempH2;
                                }

                                $data = ['in1' => $f_In1];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $data = ['status2' => 'Done'];
                                $cond = ['empId' => $empId, 'cuDate' => $date];
                                $update = $this->promo_model->update('tk', 'coverup_auth', $cond, $data);
                            }

                            if ($cuPurpose == "in 2 awol") {

                                $tempH = date("H:i", strtotime($in2 . "-$h hours, -$m minutes"));
                                $tempH2 = date("H:i:s", strtotime($in2 . "-$h hours, -$m minutes"));

                                if (strtotime("$tempH:00") <= strtotime($in2_sc)) {
                                    $f_In2 = $in2_sc;
                                } else {
                                    $f_In2 = $tempH2;
                                }

                                $data = ['in2' => $f_In2];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $data = ['status2' => 'Done'];
                                $cond = ['empId' => $empId, 'cuDate' => $date];
                                $update = $this->promo_model->update('tk', 'coverup_auth', $cond, $data);
                            }

                            if ($cuPurpose == "out 1 undertime") {

                                $tempH = date("H:i", strtotime($out1 . "+$h hours, +$m minutes"));
                                $tempH2 = date("H:i:s", strtotime($out1 . "+$h hours, +$m minutes"));

                                if (strtotime("$tempH:00") >= strtotime($out1_sc)) {
                                    $f_Out1 = $out1_sc;
                                } else {
                                    $f_Out1 = $tempH2;
                                }

                                $data = ['out1' => $f_Out1];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $data = ['status2' => 'Done'];
                                $cond = ['empId' => $empId, 'cuDate' => $date];
                                $update = $this->promo_model->update('tk', 'coverup_auth', $cond, $data);
                            }

                            if ($cuPurpose == "out 2 undertime") {

                                $tempH = date("H:i", strtotime($out2 . "+$h hours, +$m minutes"));
                                $tempH2 = date("H:i:s", strtotime($out2 . "+$h hours, +$m minutes"));

                                if (strtotime("$tempH:00") >= strtotime($out2_sc)) {
                                    $f_Out2 = $out2_sc;
                                } else {
                                    $f_Out2 = $tempH2;
                                }

                                $data = ['out2' => $f_Out2];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $data = ['status2' => 'Done'];
                                $cond = ['empId' => $empId, 'cuDate' => $date];
                                $update = $this->promo_model->update('tk', 'coverup_auth', $cond, $data);
                            }

                            if ($cuPurpose == "halfday 1") {

                                $data = ['in1' => $in1_sc, 'out1' => $out1_sc];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $data = ['status2' => 'Done'];
                                $cond = ['empId' => $empId, 'cuDate' => $date];
                                $update = $this->promo_model->update('tk', 'coverup_auth', $cond, $data);
                            }

                            if ($cuPurpose == "halfday 2") {

                                $data = ['in2' => $in2_sc, 'out2' => $out2_sc];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $data = ['status2' => 'Done'];
                                $cond = ['empId' => $empId, 'cuDate' => $date];
                                $update = $this->promo_model->update('tk', 'coverup_auth', $cond, $data);
                            }
                        }

                        if ($cuPurpose == "wholeday") {

                            $data = ['in1' => $in1_sc, 'out1' => $out1_sc, 'in2' => $in2_sc, 'out2' => $out2_sc];
                            $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                            $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                            $data = ['status2' => 'Done'];
                            $cond = ['empId' => $empId, 'cuDate' => $date];
                            $update = $this->promo_model->update('tk', 'coverup_auth', $cond, $data);
                        }

                        if ($next_not) {

                            $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                            $dnext = $this->promo_model->select_rowArray('tk', 'duty_next', $cond);
                            if ($dnext) {
                                $data = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                $insert = $this->promo_model->insert('tk', 'duty_next', $data);
                            }

                            if ($cuPurpose == "wholeday") {
                                $data = ['in1_ka' => $in1_sc, 'out1_ka' => $out1_sc, 'in2_ka' => $in2_sc, 'out2_ka' => $out2_sc];
                                $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                            }

                            if ($cuPurpose != "wholeday") {

                                if ($cuPurpose == "in 1 awol") {

                                    $tempH = date("H:i", strtotime($in1 . "-$h hours, -$m minutes"));
                                    $tempH2 = date("H:i:s", strtotime($in1 . "-$h hours, -$m minutes"));

                                    if (strtotime("$tempH:00") <= strtotime($in1_sc)) {
                                        $f_In1 = $in1_sc;
                                    } else {
                                        $f_In1 = $tempH2;
                                    }

                                    $data = ['in1_ka' => $f_In1];
                                    $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                    $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                }

                                if ($cuPurpose == "in 2 awol") {

                                    $tempH = date("H:i", strtotime($in2 . "-$h hours, -$m minutes"));
                                    $tempH2 = date("H:i:s", strtotime($in2 . "-$h hours, -$m minutes"));

                                    if (strtotime("$tempH:00") <= strtotime($in2_sc)) {
                                        $f_In2 = $in2_sc;
                                    } else {
                                        $f_In2 = $tempH2;
                                    }

                                    $data = ['in2_ka' => $f_In2];
                                    $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                    $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                }

                                if ($cuPurpose == "out 1 undertime") {

                                    $tempH = date("H:i", strtotime($out1 . "+$h hours, +$m minutes"));
                                    $tempH2 = date("H:i:s", strtotime($out1 . "+$h hours, +$m minutes"));

                                    if (strtotime("$tempH:00") >= strtotime($out1_sc)) {
                                        $f_Out1 = $out1_sc;
                                    } else {
                                        $f_Out1 = $tempH2;
                                    }

                                    $data = ['out1_ka' => $f_Out1];
                                    $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                    $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                }

                                if ($cuPurpose == "out 2 undertime") {

                                    $tempH = date("H:i", strtotime($out2 . "+$h hours, +$m minutes"));
                                    $tempH2 = date("H:i:s", strtotime($out2 . "+$h hours, +$m minutes"));

                                    if (strtotime("$tempH:00") >= strtotime($out2_sc)) {
                                        $f_Out2 = $out2_sc;
                                    } else {
                                        $f_Out2 = $tempH2;
                                    }

                                    $data = ['out2_ka' => $f_Out2];
                                    $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                    $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                }

                                if ($cuPurpose == "halfday 1") {

                                    $data = ['in1_ka' => $in1_sc, 'out1_ka' => $out1_sc];
                                    $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                                }

                                if ($cuPurpose == "halfday 2") {

                                    $data = ['in2_ka' => $in2_sc, 'out2_ka' => $out2_sc];
                                    $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                    $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                }
                            }
                        }

                        $cond = ['bioMetricId' => $bioM, 'date' => $date];
                        $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                        if ($duty) {

                            $d_in1  = $duty['in1'];
                            $d_out1 = $duty['out1'];
                            $d_in2  = $duty['in2'];
                            $d_out2 = $duty['out2'];
                            $d_in3  = $duty['in3'];
                            $d_out3 = $duty['out3'];

                            $this->netEntery($empId, $bioM, $date, $d_in1, $d_out1, $d_in2, $d_out2, $d_in3, $d_out3);
                        }
                    }
                }

                // Under Time Approved
                if (true) {
                    $cond = "empId='$empId' AND (dateUndertime BETWEEN '$df' AND '$dt') AND status='Approved'";
                    $query = $this->promo_model->select_resultArray('tk', 'undertime_auth', $cond);
                    foreach ($query as $key => $row) {

                        $utWhen     = $row['utWhen'];
                        $numHours   = $row['numHours'];
                        $date       = $row['dateUndertime'];
                        $tempUH     = $numHours * 1;

                        $t = explode(".", $tempUH);
                        $tUH = isset($t[0]) ? $t[0] * 1 : 0;
                        $tUM = isset($t[1]) ? $t[1] * 1 : 0;  // will be 0 if no decimal

                        $numHours = "$tUH hours";

                        if ($tUM == 5) {
                            $numHours = "$tUH hours, -30 minutes";
                        }

                        $u_out1 = $u_out2 = "";

                        $cond = ['bioMetricId' => $bioM, 'date' => $date];
                        $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                        if ($duty) {

                            $duty_in1   = $duty['in1'];
                            $duty_out1  = $duty['out1'];
                            $duty_in2   = $duty['in2'];
                            $duty_out2  = $duty['out2'];
                            $duty_in3   = $duty['in3'];
                            $duty_out3  = $duty['out3'];

                            $u_out1 = $duty['out1'];
                            $u_out2 = $duty['out2'];

                            $cond = ['bioMetricId' => $bioM, 'dateRendered' => $date];
                            $check = $this->promo_model->select_rowArray('tk', $duty_temp, $cond);
                            if (!$check) {
                                $data = [
                                    'bioMetricId'   => $bioM,
                                    'dateRendered'  => $date,
                                    'in1'           => $duty_in1,
                                    'out1'          => $duty_out1,
                                    'in2'           => $duty_in2,
                                    'out2'          => $duty_out2,
                                    'in3'           => $duty_in3,
                                    'out3'          => $duty_out3,
                                ];
                                $insert = $this->promo_model->insert('tk', $duty_temp, $data);
                            }
                        }

                        $shiftCode = $this->getSC($empId, $bioM, $date);

                        $cond   = ['shiftCode' => $shiftCode];
                        $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
                        if ($sc) {

                            $s_in1  = $sc['1stIn'];
                            $s_out1 = $sc['1stOut'];
                            $s_in2  = $sc['2ndIn'];
                            $s_out2 = $sc['2ndOut'];
                        }

                        $cond   = ['shiftCode' => $shiftCode];
                        $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
                        if ($scb) {

                            $s_in1  = $scb['1stIn'];
                            $s_out1 = $scb['1stOut'];
                            $s_in2  = $scb['2ndIn'];
                            $s_out2 = $scb['2ndOut'];
                        }

                        $day_in1    = date('A', strtotime($s_in1));
                        $day_out1   = date('A', strtotime($s_out1));
                        $day_in2    = date('A', strtotime($s_in2));
                        $day_out2   = date('A', strtotime($s_out2));

                        $in1_sc     = date("H:i:s", strtotime($s_in1));
                        $out1_sc    = date("H:i:s", strtotime($s_out1));
                        $in2_sc     = date("H:i:s", strtotime($s_in2));
                        $out2_sc    = date("H:i:s", strtotime($s_out2));

                        $next_not = false;
                        if ($day_in2 == "AM" && $day_out2 == "AM" && !$scb) {
                            $next_not = true;
                        }

                        if ($day_out1 == "AM" && $day_in2 == "AM" && $day_out2 == "AM" && !$scb) {
                            $next_not = true;
                        }

                        if ($scb) {
                            if ($day_in1 == "PM" && $day_in2 == "AM" && $day_out2 == "AM") {
                                $next_not = true;
                            }

                            if ($day_in1 == "PM" && $day_out1 == "AM" && $day_in2 == "AM") {
                                $next_not = true;
                            }
                        }

                        if ($utWhen == "Out 1") {

                            $tempHour = date('H:i:s', strtotime("$out1_sc -$numHours"));
                            if (strtotime($u_out1) > strtotime($tempHour)) {

                                $data = ['out1' => $tempHour];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $cond = ['bioMetricId' => $bioM, 'date' => $date];
                                $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                                if ($duty) {

                                    $d_in1  = $duty['in1'];
                                    $d_out1 = $duty['out1'];
                                    $d_in2  = $duty['in2'];
                                    $d_out2 = $duty['out2'];
                                    $d_in3  = $duty['in3'];
                                    $d_out3 = $duty['out3'];

                                    $this->netEntery($empId, $bioM, $date, $d_in1, $d_out1, $d_in2, $d_out2, $d_in3, $d_out3);
                                }
                            }
                        }

                        if ($utWhen == "Out 2") {

                            $tempHour = date('H:i:s', strtotime("$out2_sc -$numHours"));
                            if (strtotime($u_out2) > strtotime($tempHour)) {

                                $data = ['out2' => $tempHour];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $cond = ['bioMetricId' => $bioM, 'date' => $date];
                                $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                                if ($duty) {

                                    $d_in1  = $duty['in1'];
                                    $d_out1 = $duty['out1'];
                                    $d_in2  = $duty['in2'];
                                    $d_out2 = $duty['out2'];
                                    $d_in3  = $duty['in3'];
                                    $d_out3 = $duty['out3'];

                                    $this->netEntery($empId, $bioM, $date, $d_in1, $d_out1, $d_in2, $d_out2, $d_in3, $d_out3);
                                }
                            }
                        }

                        if ($next_not == "true") {

                            if ($utWhen == "Out 1") {

                                $tempHour = date('H:i:s', strtotime("$out1_sc -$numHours"));
                                if (strtotime($u_out1) > strtotime($tempHour)) {

                                    $data = ['out1_ka' => $tempHour];
                                    $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                    $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                }
                            }

                            if ($utWhen == "Out 2") {

                                $tempHour = date('H:i:s', strtotime("$out2_sc -$numHours"));
                                if (strtotime($u_out2) > strtotime($tempHour)) {

                                    $data = ['out2_ka' => $tempHour];
                                    $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                    $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                }
                            }
                        }
                    }
                }

                // LOA Approved
                if (true) {
                    $cond = "empId='$empId' AND (incDate_sF BETWEEN '$df' AND '$dt') AND (incDate_sT BETWEEN '$df' AND '$dt') AND status='Approved'";
                    $query = $this->promo_model->select_resultArray('tk', 'r_l_absence', $cond);
                    foreach ($query as $key => $row) {

                        $rLAId      = $row['rLAId'];
                        $dateFrom   = $row['incDate_sF'];
                        $dateTo     = $row['incDate_sT'];

                        $cond = "rLAId='$rLAId' AND (date BETWEEN '$dateFrom' AND '$dateTo')";
                        $query = $this->promo_model->select_resultArray('tk', 'rla_day', $cond);
                        foreach ($query as $key => $rla) {

                            $date = $rla['date'];
                            $day = $rla['day'];

                            $cond = ['bioMetricId' => $bioM, 'date' => $date];
                            $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                            if ($duty) {

                                $duty_in1   = $duty['in1'];
                                $duty_out1  = $duty['out1'];
                                $duty_in2   = $duty['in2'];
                                $duty_out2  = $duty['out2'];
                                $duty_in3   = $duty['in3'];
                                $duty_out3  = $duty['out3'];

                                $cond = ['bioMetricId' => $bioM, 'dateRendered' => $date];
                                $check = $this->promo_model->select_rowArray('tk', $duty_temp, $cond);
                                if (!$check) {
                                    $data = [
                                        'bioMetricId'   => $bioM,
                                        'dateRendered'  => $date,
                                        'in1'           => $duty_in1,
                                        'out1'          => $duty_out1,
                                        'in2'           => $duty_in2,
                                        'out2'          => $duty_out2,
                                        'in3'           => $duty_in3,
                                        'out3'          => $duty_out3,
                                    ];
                                    $insert = $this->promo_model->insert('tk', $duty_temp, $data);
                                }
                            }

                            if ($day == "Whole Day") {

                                // $data = ['in1' => '', 'out1' => '', 'in2' => '', 'out2' => ''];
                                // $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                // $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                            }

                            if ($day == "Half Day AM") {

                                // $data = ['in1' => '', 'out1' => ''];
                                // $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                // $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                            }

                            if ($day == "Half Day PM") {

                                // $data = ['in2' => '', 'out2' => ''];
                                // $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                // $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                            }

                            $cond = ['bioMetricId' => $bioM, 'date' => $date];
                            $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                            if ($duty) {

                                $d_in1  = $duty['in1'];
                                $d_out1 = $duty['out1'];
                                $d_in2  = $duty['in2'];
                                $d_out2 = $duty['out2'];
                                $d_in3  = $duty['in3'];
                                $d_out3 = $duty['out3'];

                                if ($day == "Whole Day") {
                                    $d_in1  = '';
                                    $d_out1 = '';
                                    $d_in2  = '';
                                    $d_out2 = '';
                                }

                                if ($day == "Half Day AM") {
                                    $d_in1  = '';
                                    $d_out1 = '';
                                }

                                if ($day == "Half Day PM") {
                                    $d_in2  = '';
                                    $d_out2 = '';
                                }

                                $this->netEntery($empId, $bioM, $date, $d_in1, $d_out1, $d_in2, $d_out2, $d_in3, $d_out3);
                            }
                        }
                    }
                }
            }

            // calculation of total hoursWork exculded awol in1 in2
            if (true) {
                $cond = ['empId' => $empId];
                $chk = $this->promo_model->select_rowArray('tk', 'violation_awol_excempt', $cond);
                if (!$chk) {

                    $cond = "bioMetricId='$bioM' AND (in1 != '' OR in2 != '') AND (date BETWEEN '$df' AND '$dt')";
                    $query = $this->promo_model->select_resultArray('tk', 'duty', $cond);
                    foreach ($query as $key => $duty) {

                        $dutyId = $duty['dutyId'];
                        $date   = $duty['date'];
                        $d_in1  = $duty['in1'];
                        $d_out1 = $duty['out1'];
                        $d_in2  = $duty['in2'];
                        $d_out2 = $duty['out2'];
                        $d_in3  = $duty['in3'];
                        $d_out3 = $duty['out3'];

                        $cond = "od.btfw_ID=dwas.btfw_ID AND od.empId='$empId' AND od.date='$date'";
                        $chk1 = $this->promo_model->select_rowArray('tk', 'off_date as od, dwa_special_as as dwas', $cond);

                        $cond = ['empId' => $empId, 'cuDate' => $date, 'status' => 'Approved'];
                        $chk2 = $this->promo_model->select_rowArray('tk', 'coverup_auth', $cond);

                        if (!$chk1 && !$chk2) {

                            $in1_sc = $out1_sc = $in2_sc = $out2_sc = "";

                            $shiftCode = $this->getSC($empId, $bioM, $date);

                            $cond = ['shiftCode' => $shiftCode];
                            $sc = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
                            if ($sc) {

                                $s_in1  = $sc['1stIn'];
                                $s_out1 = $sc['1stOut'];
                                $s_in2  = $sc['2ndIn'];
                                $s_out2 = $sc['2ndOut'];

                                if (!empty($s_in1)) {
                                    $in1_sc = date("H:i:s", strtotime($s_in1));
                                }

                                if (!empty($s_out1)) {
                                    $out1_sc = date("H:i:s", strtotime($s_out1));
                                }

                                if (!empty($s_in2)) {
                                    $in2_sc = date("H:i:s", strtotime($s_in2));
                                }

                                if (!empty($s_out2)) {
                                    $out2_sc = date("H:i:s", strtotime($s_out2));
                                }
                            }

                            $hrId = $this->promo_model->select_rowArray('tk', 'houserules', ['offense' => 'Tardiness'])['houseRulesId'];
                            $so = $this->promo_model->select_rowArray('tk', 'setup_offense', ['hrId' => $hrId]);
                            if ($so['use'] == 'current') {

                                $cond = "1 ORDER BY slcId";
                                $slc = $this->promo_model->select_rowArray('tk', 'setup_late_cur', $cond);
                                $min15 = $slc['minutes']; // 15 min
                            }

                            if (!empty($in1_sc) && !empty($out1_sc) && !empty($in2_sc) && !empty($out2_sc)) {

                                $awLate1 = date("H:i", strtotime("$in1_sc +$min15 minutes"));
                                $awLate2 = date("H:i", strtotime("$in2_sc +$min15 minutes"));

                                $d_in11 = date("H:i", strtotime("$d_in1"));
                                $d_in22 = date("H:i", strtotime("$d_in2"));

                                if (strtotime($awLate1) < strtotime($d_in11)) {

                                    $cond = ['dutyId' => $dutyId];
                                    $perday = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                                    if ($perday) {
                                        $absence    = $perday['absence'];
                                        $utIn1      = $perday['utIn1'];
                                        $utIn2      = $perday['utIn2'];

                                        $cond = ['dutyId' => $dutyId];
                                        $check = $this->promo_model->select_rowArray('tk', $perday_temp, $cond);
                                        if (!$check) {
                                            $data = [
                                                'dutyId'    => $dutyId,
                                                'absence'   => $absence,
                                                'utIn1'     => $utIn1,
                                                'utIn2'     => $utIn2,
                                                'in1'       => $d_in1,
                                                'out1'      => $d_out1,
                                            ];

                                            $insert = $this->promo_model->insert('tk', $perday_temp, $data);
                                        }

                                        $this->netEntery($empId, $bioM, $date, '', '', $d_in2, $d_out2, $d_in3, $d_out3);
                                    }
                                }

                                if (strtotime($awLate2) < strtotime($d_in22)) {

                                    $cond = ['dutyId' => $dutyId];
                                    $perday = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                                    if ($perday) {
                                        $absence    = $perday['absence'];
                                        $utIn1      = $perday['utIn1'];
                                        $utIn2      = $perday['utIn2'];

                                        $cond = ['dutyId' => $dutyId];
                                        $check = $this->promo_model->select_rowArray('tk', $perday_temp, $cond);
                                        if (!$check) {
                                            $data = [
                                                'dutyId'    => $dutyId,
                                                'absence'   => $absence,
                                                'utIn1'     => $utIn1,
                                                'utIn2'     => $utIn2,
                                                'in1'       => $d_in1,
                                                'out1'      => $d_out1,
                                            ];
                                            $insert = $this->promo_model->insert('tk', $perday_temp, $data);
                                        } else {

                                            $cond = ['dutyId' => $dutyId];
                                            $data = ['utIn2' => $utIn2];
                                            $update = $this->promo_model->update('tk', $perday_temp, $cond, $data);
                                        }

                                        $this->netEntery($empId, $bioM, $date, $d_in1, $d_out1, '', '', $d_in3, $d_out3);
                                    }
                                }
                            }

                            if (!empty($in1_sc) && !empty($out1_sc) && empty($in2_sc) && empty($out2_sc)) {

                                $awLate1 = date("H:i", strtotime("$in1_sc +$min15 minutes"));
                                $d_in11 = date("H:i", strtotime("$d_in1"));
                                if (strtotime($awLate1) < strtotime($d_in11)) {
                                    $this->netEntery($empId, $bioM, $date, '', '', '', '', $d_in3, $d_out3);
                                }
                            }
                        }
                    }
                }
            }

            // calculation of total hoursWork
            if (true) {

                // calculate daysWork (duty)
                if (true) {

                    $sumH = 0;
                    $sumM = 0;
                    $totPer = 0;
                    $tot_nd = 0;

                    $d_in1 = $d_out1 = $d_in2 = $d_out2 = $d_in3 = $d_out3 = '';
                    $cond = "bioMetricId='$bioM' AND (date BETWEEN '$df' AND '$dt')";
                    $query = $this->promo_model->select_resultArray('tk', 'duty', $cond);
                    foreach ($query as $key => $duty) {

                        $dutyId = $duty['dutyId'];
                        $date   = $duty['date'];
                        $d_in1  = $duty['in1'];
                        $d_out1 = $duty['out1'];
                        $d_in2  = $duty['in2'];
                        $d_out2 = $duty['out2'];
                        $d_in3  = $duty['in3'];
                        $d_out3 = $duty['out3'];

                        $hoursWork = 0;
                        $night_diff = 0;
                        $cond = ['dutyId' => $dutyId];
                        $perday = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                        if ($perday) {
                            $hoursWork = $perday['hoursWork'];
                            $night_diff = $perday['night_diff'] * 1;
                        }

                        // dayoff ********************************************************************     
                        $show_do = false;
                        $old_do = false;
                        $cond = "empId='$empId' AND status='active' AND status2='Approved' AND '$date' BETWEEN dateFrom AND dateTo ORDER BY id DESC LIMIT 1";
                        $query = $this->promo_model->select_resultArray('tk', 'assign_mul_sc', $cond);
                        if ($query) {
                            foreach ($query as $key => $ams) {

                                $amsId  = $ams['amsId'];
                                $cond   = ['amsId' => $amsId, 'empId' => $empId, 'date' => $date];
                                $amd    = $this->promo_model->select_rowArray('tk', 'ass_mul_do', $cond);

                                $cond   = ['empId' => $empId, 'dateToChange' => $date];
                                $acd    = $this->promo_model->select_rowArray('tk', 'ass_change_do', $cond);

                                if ($amd && !$acd) {
                                    $show_do = true;
                                }

                                if ($acd) {
                                    if ($acd['status'] == 'Approved') {
                                        $show_do = false;
                                    } else {
                                        $show_do = true;
                                    }
                                }

                                $cond   = ['empId' => $empId, 'dateNew' => $date];
                                $acd    = $this->promo_model->select_rowArray('tk', 'ass_change_do', $cond);

                                if ($acd) {
                                    if ($acd['status'] == 'Approved') {
                                        $show_do = true;
                                    } else {
                                        $show_do = false;
                                    }
                                }

                                // echo json_encode(array(
                                //     'result'    => 'loading',
                                //     'msg'       => round($percentage) . '%',
                                //     'msg1'      => round($percentage) . '%',
                                //     'date'      => "Finalizing DTR data...",
                                //     'table'     => "[empId: $empId; date: $date; do: $show_dos]",
                                //     'exported'  => 'table_update'
                                // ));

                                $cond   = ['empId' => $empId, 'dateToChange' => $date, 'status' => 'Approved'];
                                $acd    = $this->promo_model->select_rowArray('tk', 'ass_change_do', $cond);
                                if ($amd && $acd) {
                                    $acdId  = $acd['acdId'];
                                    $cond   = ['requestId' => $acdId, 'requestType' => 'CDOS'];
                                    $aprby  = $this->promo_model->select_rowArray('tk', 'approveby', $cond);
                                    if ($aprby) {
                                        $dateReq = $aprby['date'];
                                        $timeReq = $aprby['time'];
                                        $app_details = date("Y-m-d H:i:s", strtotime("$dateReq $timeReq"));

                                        $cond = ['amsId' => $amsId];
                                        $rns = $this->promo_model->select_rowArray('tk', 'request_new_shift', $cond);
                                        if ($rns) {
                                            $dateReq = $rns['dateReq'];
                                            $timeReq = $rns['timeReq'];

                                            $req_details = date("Y-m-d H:i:s", strtotime("$dateReq $timeReq"));
                                            if ($app_details < $req_details) {
                                                $show_do = true;
                                            }
                                        }
                                    }
                                }

                                $cond   = ['empId' => $empId, 'dateNew' => $date, 'status' => 'Approved'];
                                $acd    = $this->promo_model->select_rowArray('tk', 'ass_change_do', $cond);
                                if ($amd && $acd) {
                                    $acdId  = $acd['acdId'];
                                    $cond   = ['requestId' => $acdId, 'requestType' => 'CDOS'];
                                    $aprby  = $this->promo_model->select_rowArray('tk', 'approveby', $cond);
                                    if ($aprby) {
                                        $dateReq = $aprby['date'];
                                        $timeReq = $aprby['time'];
                                        $app_details = date("Y-m-d H:i:s", strtotime("$dateReq $timeReq"));

                                        $cond = ['amsId' => $amsId];
                                        $rns = $this->promo_model->select_rowArray('tk', 'request_new_shift', $cond);
                                        if ($rns) {
                                            $dateReq = $rns['dateReq'];
                                            $timeReq = $rns['timeReq'];

                                            $req_details = date("Y-m-d H:i:s", strtotime("$dateReq $timeReq"));
                                            if ($app_details > $req_details) {
                                                $show_do = true;
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            $cond = array('empId' => $empId, 'dateNew' => $date, 'status' => 'Approved');
                            $acd = $this->promo_model->select_rowArray('tk', 'ass_change_do', $cond);
                            if ($acd) {
                                $show_do = true;
                            }

                            $cond   = array('empId' => $empId, 'dateToChange' => $date, 'status' => 'Approved');
                            $acd    = $this->promo_model->select_rowArray('tk', 'ass_change_do', $cond);
                            if ($acd) {
                                $old_do = true;
                            }
                        }

                        // default day-off if $show_do is false
                        if (!$query &&  !$show_do &&  !$old_do) {
                            $days = date('l', strtotime($date));
                            $cond = array('empId' => $empId, 'day' => date('l', strtotime($date)));
                            $sql = $this->promo_model->select_rowArray('tk', 'assign_day_off', $cond);
                            if ($sql) {
                                $show_do = true;
                            }
                        }
                        // dayoff ********************************************************************

                        // offduty of dayoff ********************************************************************
                        $odaDayoff = false;
                        if ($show_do) {
                            $cond  = "empId='$empId' AND '$date' BETWEEN dateFrom AND dateTo AND remark != ''";
                            $sql   = $this->promo_model->select_resultArray('tk', 'assign_mul_sc', $cond);
                            foreach ($sql as $ams) {
                                $amsId  = $ams['amsId'];
                                $cond   = ['amsId' => $amsId];
                                $sql    = $this->promo_model->select_rowArray('tk', 'request_odaf', $cond);
                                if ($sql) {
                                    $odaDayoff = true;
                                }
                            }
                        }
                        // offduty ********************************************************************   

                        if ($show_do && !$odaDayoff) {
                            $hoursWork = 0;
                            $night_diff = 0;
                        }

                        $t = explode("&", $hoursWork);
                        $hour = isset($t[0]) ? $t[0] * 1 : 0;
                        $min = isset($t[1]) ? $t[1] * 1 : 0;  // will be 0 if no decimal

                        $tempH = $hour / 8;
                        $tempM = ($min / 60) / 8;

                        $sumH = $sumH + $tempH;
                        $sumM = $sumM + $tempM;

                        // $check = round(($sumH + $sumM), 3);

                        // echo json_encode(array(
                        //     'result'    => 'loading',
                        //     'msg'       => round($percentage) . '%',
                        //     'msg1'      => round($percentage) . '%',
                        //     'date'      => "Finalizing DTR data...",
                        //     'table'     => "[empId: $empId; date: $date; DW: $check; hoursWork: $hoursWork]",
                        //     'exported'  => 'table_update'
                        // ));

                        $tot_nd = $tot_nd + $night_diff;

                        // holiday	********************************************************************
                        $holiday = false;
                        $cond = "(date='$date' AND moveDate='') OR moveDate='$date' LIMIT 1";
                        $chk = $this->promo_model->select_rowArray('tk', 'holidays_promo', $cond);
                        if ($chk) {
                            $holiday = true;
                            if ($promo_type == 'STATION' && ($promo['al_panglao'] == 'T' || $promo['cdc'] == 'T') && date('m-d', strtotime($date)) == '07-01') {
                                $holiday = false;
                            }
                        }
                        // holiday ********************************************************************

                        // offduty of holiday ********************************************************************
                        $odaHoliday = false;
                        if ($holiday) {

                            $cond  = "empId='$empId' AND '$date' BETWEEN dateFrom AND dateTo AND remark != ''";
                            $sql   = $this->promo_model->select_resultArray('tk', 'assign_mul_sc', $cond);
                            foreach ($sql as $ams) {
                                $amsId  = $ams['amsId'];
                                $cond   = ['amsId' => $amsId];
                                $sql    = $this->promo_model->select_rowArray('tk', 'request_odaf', $cond);
                                if ($sql) {
                                    $odaHoliday = true;
                                }
                            }
                        }
                        // offduty ********************************************************************   

                        if ($holiday && !$odaHoliday) {
                            $sumH = $sumH - $tempH;
                            $sumM = $sumM - $tempM;
                        }

                        // OT Entry ********************************************************************
                        $cond  = "tb1.empId=tb2.emp_id AND tb1.dateRendered=tb2.date_ot AND tb1.empId='$empId' AND tb1.dateRendered='$date'";
                        $check = $this->promo_model->select_rowArray('tk', 'ot_bt_fw_entry as tb1,ot_bt_fw_entry_ext as tb2', $cond);
                        if ($check) {
                            $ot1        = $check['ot1'];
                            $ot2        = $check['ot2'];
                            $ot3        = $check['ot3'];
                            $tStarted1  = $check['tStarted1'];
                            $tEnded2    = $check['tEnded2'];
                            $tStarted3  = $check['tStarted3'];
                            $tEnded3    = $check['tEnded3'];

                            if ($ot1 == 'Yes') {
                                if ($tStarted1) {
                                    $data = ['in1' => $tStarted1];
                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                                }
                            }

                            if ($ot2 == 'Yes') {
                                if ($tEnded2) {
                                    $data = ['out2' => $tEnded2];
                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                                }
                            }

                            if ($ot3 == 'Yes') {
                                if ($tStarted3 && $tEnded3) {
                                    $data = ['in3' => $tStarted3, 'out3' => $tEnded3];
                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                                }
                            }
                        }
                    }

                    $totPer = round(($sumH + $sumM), 3);
                }

                // calculate netOT
                if (true) {
                    $netOT = 0;
                    $numOT = 0;
                    $tempOT = 0;
                    $cond = "empId='$empId' AND (dateOvertime BETWEEN '$df' AND '$dt')";
                    $query = $this->promo_model->select_resultArray('tk', 'e_o_authorization', $cond);
                    foreach ($query as $key => $eoa) {
                        $otApp = $eoa['otApp'];
                        $dateO = $eoa['dateOvertime'];

                        if (empty($otApp)) {
                            $otApp = 0;
                        }

                        $tempOT = $otApp * 1;
                        $numOT += $tempOT;
                    }
                }

                // calculate otforpayment
                if (true) {
                    $otPay = 0;
                    $cond = "emp_id='$empId' AND supDF='$df' AND supDT='$dt' AND otDate<='$dt' AND status='Approved' GROUP BY eoa_id";
                    $query = $this->promo_model->select_resultArray('tk', 'otforpayment', $cond);
                    foreach ($query as $key => $otp) {

                        $pay_id = $otp['pay_id'];
                        $otApp  = $otp['otApp'] * 1;
                        $otPay  += $otApp;

                        $cond = ['pay_id' => $pay_id];
                        $data = ['status2' => 'Paid'];
                        $update = $this->promo_model->update('tk', 'otforpayment', $cond, $data);
                    }

                    // echo json_encode(array(
                    //     'result'    => 'loading',
                    //     'msg'       => round($percentage) . '%',
                    //     'msg1'      => round($percentage) . '%',
                    //     'date'      => "checking holiday...",
                    //     'table'     => "[empId: $empId; $otPay]",
                    //     'exported'  => 'table_update'
                    // ));
                }

                // calculate legHol (legal holiday)
                if (true) {
                    $legalHolidays = [];
                    $cond = "type='Legal' AND ((moveDate='' AND (date BETWEEN '$df' AND '$dt')) OR (moveDate!='' AND (moveDate BETWEEN '$df' AND '$dt')))";
                    $query = $this->promo_model->select_resultArray('tk', 'holidays_promo', $cond);
                    foreach ($query as $hol) {

                        $hDate = !empty($hol['moveDate']) ? $hol['moveDate'] : $hol['date'];
                        $cond = "e.emp_id='$empId' AND ('$hDate' BETWEEN startdate AND eocdate)";

                        $chk = $this->promo_model->promoDetails($cond);
                        if ($chk) {
                            $legalHolidays[$hDate] = true;
                        }

                        $chk = $this->promo_model->promoDetailsprev($cond);
                        if ($chk) {
                            $legalHolidays[$hDate] = true;
                        }
                    }

                    $legHol = count($legalHolidays);
                }

                // calculate speHol (special holiday)
                if (true) {

                    $specialHolidays = [];
                    $sumH_Hol = 0;
                    $sumM_Hol = 0;
                    $totPer_Hol = 0;
                    $cond = "type='Special' AND acr_cat='pay30_W' AND ((moveDate='' AND (date BETWEEN '$df' AND '$dt') AND date>='$sd') OR (moveDate!='' AND (moveDate BETWEEN '$df' AND '$dt') AND moveDate>='$sd'))";
                    $query = $this->promo_model->select_resultArray('tk', 'holidays_promo', $cond);
                    foreach ($query as $key => $hol) {

                        $dateH = $hol['date'];
                        if (!isset($specialHolidays[$dateH])) {
                            $specialHolidays[$dateH] = true;

                            $cond = ['bioMetricId' => $bioM, 'date' => $dateH];
                            $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                            if ($duty) {

                                $dutyId = $duty['dutyId'];
                                $cond   = ['dutyId' => $dutyId];
                                $perday = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                                if ($perday) {

                                    $hoursW = $perday['hoursWork'];
                                    $t      = explode("&", $hoursW);
                                    $hour   = isset($t[0]) ? $t[0] * 1 : 0;
                                    $min    = isset($t[1]) ? $t[1] * 1 : 0;  // will be 0 if no decimal
                                    $tempH  = $hour;
                                    $tempM  = $min;

                                    $sumH_Hol = $sumH_Hol + $tempH;
                                    $sumM_Hol = $sumM_Hol + $tempM;
                                    if ($promo_type == 'STATION' && ($promo['al_panglao'] == 'T' || $promo['cdc'] == 'T') && date('m-d', strtotime($dateH)) == '07-01') {
                                        if ($sumH_Hol) {
                                            $sumH_Hol = $sumH_Hol - $tempH;
                                            $sumM_Hol = $sumM_Hol - $tempM;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $cond = "e.emp_id='$empId'";
                    $query = $this->promo_model->promoDetailsprev($cond);
                    foreach ($query as $key => $row) {
                        $psd = $row['startdate'];
                        $ped = $row['eocdate'];

                        $cond = "type='Special' AND acr_cat='pay30_W' AND ((moveDate='' AND (date BETWEEN '$df' AND '$dt') AND (date BETWEEN '$psd' AND '$ped')) OR (moveDate!='' AND (moveDate BETWEEN '$df' AND '$dt') AND (moveDate BETWEEN '$psd' AND '$ped')))";
                        $sHol = $this->promo_model->select_resultArray('tk', 'holidays_promo', $cond);
                        foreach ($sHol as $key => $hol) {

                            $dateH = $hol['date'];
                            if (!isset($specialHolidays[$dateH])) {
                                $specialHolidays[$dateH] = true;

                                $cond = ['bioMetricId' => $bioM, 'date' => $dateH];
                                $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                                if ($duty) {

                                    $dutyId = $duty['dutyId'];
                                    $cond   = ['dutyId' => $dutyId];
                                    $perday = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                                    if ($perday) {

                                        $hoursW = $perday['hoursWork'];
                                        $t      = explode("&", $hoursW);
                                        $hour   = isset($t[0]) ? $t[0] * 1 : 0;
                                        $min    = isset($t[1]) ? $t[1] * 1 : 0;  // will be 0 if no decimal
                                        $tempH  = $hour;
                                        $tempM  = $min;
                                        $sumH_Hol = $sumH_Hol + $tempH;
                                        $sumM_Hol = $sumM_Hol + $tempM;
                                        if ($promo_type == 'STATION' && ($promo['al_panglao'] == 'T' || $promo['cdc'] == 'T') && date('m-d', strtotime($dateH)) == '07-01') {
                                            if ($sumH_Hol) {
                                                $sumH_Hol = $sumH_Hol - $tempH;
                                                $sumM_Hol = $sumM_Hol - $tempM;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // Holiday Special With or With Out Duty	
                    $cond = "type='Special' AND acr_cat='pay30_W_WO' AND ((moveDate='' AND (date BETWEEN '$df' AND '$dt') AND date>='$sd') OR (moveDate!='' AND (moveDate BETWEEN '$df' AND '$dt') AND moveDate>='$sd'))";
                    $query = $this->promo_model->select_resultArray('tk', 'holidays_promo', $cond);
                    foreach ($query as $key => $hol) {
                        $dateH = $hol['date'];
                        $sumH_Hol = $sumH_Hol + 8;
                    }

                    $cond = "emp_id='$empId' AND type='Special' AND acr_cat='pay30_W_WO' AND moveDate='' AND (date BETWEEN '$df' AND '$dt') AND ((date BETWEEN 'startdate' AND 'eocdate') AND ('$sd' NOT BETWEEN 'startdate' AND 'eocdate'))";
                    $query = $this->promo_model->select_resultArray('tk', 'holidays_promo as t1,pis.employmentrecord_ as t2', $cond);
                    foreach ($query as $key => $hol) {
                        $dateH = $hol['date'];
                        $sumH_Hol = $sumH_Hol + 8;
                    }

                    $cond = "emp_id='$empId' AND type='Special' AND acr_cat='pay30_W_WO' AND moveDate!='' AND  (moveDate BETWEEN '$df' AND '$dt') AND ((moveDate BETWEEN 'startdate' AND 'eocdate') AND ('$sd' NOT BETWEEN 'startdate' AND 'eocdate'))";
                    $query = $this->promo_model->select_resultArray('tk', 'holidays_promo as t1,pis.employmentrecord_ as t2', $cond);
                    foreach ($query as $key => $hol) {
                        $dateH = $hol['moveDate'];
                        $sumH_Hol = $sumH_Hol + 8;
                    }

                    $hol_min    = date("H:i", strtotime("00:00 +$sumM_Hol minutes"));

                    $r          = explode(":", $hol_min);
                    $t_HMin     = $r[0] * 1;
                    $t_MMin     = $r[1] * 1;

                    $sumH_Hol   = $sumH_Hol + $t_HMin;
                    $t_MMin     = ($t_MMin / 60);
                    $min        = round($t_MMin, 3);

                    $special_hours = $sumH_Hol + $min;
                }

                // calculate VL and ML
                if (true) {

                    $vlNo = 0;
                    $vlNoExp = 0;
                    $mlNo = 0;

                    if ($empType == "Regular") {

                        $cond = "1 LIMIT 1";
                        $sul = $this->promo_model->select_rowArray('tk', 'setup_leave', $cond);
                        $su_vl = $sul['vacationLeave'] * 1;

                        $cond = "1 LIMIT 1";
                        $suse = $this->promo_model->select_rowArray('tk', 'setup_sl_exp', $cond);
                        $noSL = $suse['noSL'] * 1;

                        $numVL = 0;
                        $dFVL = '';
                        $dTVL = '';
                        $cond = "t1.empId=t2.empId AND t1.empId='$empId' AND t1.totalUseVL='$su_vl' AND t2.typeLeave='Vacation Leave' 
                            AND (t2.incDate_sT BETWEEN '$df' AND '$dt') AND (t2.incDate_sT BETWEEN t1.dateFROM AND t1.dateTo)";
                        $sql = $this->promo_model->select_rowArray('tk', 'vl_rem_status as t1,r_l_absence as t2', $cond);
                        if ($sql) {
                            $numVL = count($sql);
                            $dFVL = $sql['dateFrom'];
                            $dTVL = $sql['dateTo'];
                        }

                        $cond = "empId='$empId' AND incDate_sT > '$dt' AND typeLeave='Vacation Leave'";
                        $rla = $this->promo_model->select_rowArray('tk', 'r_l_absence', $cond);
                        if ($rla) {
                            $numVL = 0;
                        }

                        if ($numVL != 0) {

                            $vlNo = $su_vl;

                            $cond = ['empId' => $empId, 'dateFrom' => $dFVL, 'dateTo' => $dTVL, 'remainingVL' => '0'];
                            $data = ['status' => 'Paid', 'paid' => $vlNo, 'dateFromPaid' => $df, 'dateToPaid' => $dt];
                            $update = $this->promo_model->update('tk', 'vl_rem_status', $cond, $data);
                        }

                        $cond = "empId='$empId' AND dateTo BETWEEN '$df' AND '$dt' AND totalUseVL!= '$su_vl'";
                        $vlrs = $this->promo_model->select_rowArray('tk', 'vl_rem_status', $cond);
                        if ($vlrs) {

                            $totalUseVL = $vlrs['totalUseVL'];
                            $dFVL = $vlrs['dateFrom'];
                            $dTVL = $vlrs['dateTo'];

                            $tempBASEVL = $su_vl - $noSL;
                            if ($totalUseVL > $tempBASEVL) {

                                $vlNoExp = $su_vl - $totalUseVL;
                            }

                            if ($tempBASEVL >= $totalUseVL && $totalUseVL >= 0) {
                                $vlNoExp = $noSL;
                            }

                            $vlNo = $vlNoExp;

                            $cond = ['empId' => $empId, 'dateFrom' => $dFVL, 'dateTo' => $dTVL];
                            $data = ['status' => 'ExpiredButPaid', 'paid' => $vlNo, 'dateFromPaid' => $df, 'dateToPaid' => $dt];
                            $update = $this->promo_model->update('tk', 'vl_rem_status', $cond, $data);
                        }
                    }
                }

                // Previous Days Worked ***********************************************

                // DWA - Fieldwork & Business Trip Approved && Special
                if (true) {
                    $cond = "empId='$empId' AND (date BETWEEN '$df' AND '$dt')";
                    $query = $this->promo_model->select_resultArray('tk', 'off_date', $cond);
                    foreach ($query as $key => $od) {

                        $date       = $od['date'];
                        $od_day     = $od['day'];
                        $btfw_ID    = $od['btfw_ID'];

                        $cond = ['empId' => $empId, 'adj_errfai_id' => $btfw_ID, 'status' => 'Approved', 'supStatus' => 'Previous'];
                        $dwa = $this->promo_model->select_rowArray('tk', 'd_w_adjustment', $cond);
                        if ($dwa) {

                            $duty_in1 = $duty_out1 = $duty_in2 = $duty_out2 = $duty_in3 = $duty_out3 = "";
                            $cond = ['bioMetricId' => $bioM, 'date' => $date];
                            $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                            if ($duty) {

                                $dutyId     = $duty['dutyId'];
                                $duty_in1   = $duty['in1'];
                                $duty_out1  = $duty['out1'];
                                $duty_in2   = $duty['in2'];
                                $duty_out2  = $duty['out2'];
                                $duty_in3   = $duty['in3'];
                                $duty_out3  = $duty['out3'];

                                $cond = ['bioMetricId' => $bioM, 'dateRendered' => $date];
                                $check = $this->promo_model->select_rowArray('tk', $duty_temp, $cond);
                                if (!$check) {

                                    $data = [
                                        'bioMetricId'   => $bioM,
                                        'dateRendered'  => $date,
                                        'in1'           => $duty_in1,
                                        'out1'          => $duty_out1,
                                        'in2'           => $duty_in2,
                                        'out2'          => $duty_out2,
                                        'in3'           => $duty_in3,
                                        'out3'          => $duty_out3,
                                    ];

                                    $insert = $this->promo_model->insert('tk', $duty_temp, $data);
                                }

                                $hw_temp = 0;
                                $cond = ['dutyId' => $dutyId];
                                $perday = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                                if ($perday) {
                                    $hw_temp = $perday['hoursWork'];
                                }

                                $cond = ['empId' => $empId, 'dateDuty' => $date];
                                $check = $this->promo_model->select_rowArray('tk', 'perday_previous', $cond);
                                if (!$check) {
                                    $data = [
                                        'empId'     => $empId,
                                        'dateDuty'  => $date,
                                        'hw_old'    => $hw_temp,
                                        'coDF1'     => $df,
                                        'coDF2'     => $dt,
                                        'request'   => 'DWA'
                                    ];

                                    $insert = $this->promo_model->insert('tk', 'perday_previous', $data);
                                }
                            }

                            $shiftCode = $this->getSC($empId, $bioM, $date);

                            $cond = ['shiftCode' => $shiftCode];
                            $sc = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
                            if ($sc) {
                                $in1    = $sc['1stIn'];
                                $out1   = $sc['1stOut'];
                                $in2    = $sc['2ndIn'];
                                $out2   = $sc['2ndOut'];
                            }

                            $cond = ['empId' => $empId, 'btfw_ID' => $btfw_ID];
                            $check = $this->promo_model->select_rowArray('tk', 'dwa_special_as', $cond);
                            if ($check) {

                                if ($in1) {
                                    $duty_in1 = date('H:i:s', strtotime($in1));
                                }

                                if ($out1) {
                                    $duty_out1 = date('H:i:s', strtotime($out1));
                                }

                                if ($in2) {
                                    $duty_in2 = date('H:i:s', strtotime($in2));
                                }

                                if ($out2) {
                                    $duty_out2 = date('H:i:s', strtotime($out2));
                                }
                            }

                            if (!empty($in1) && !empty($out1) && !empty($in2) && !empty($out2)) {

                                $n_in1 = date('H:i:s', strtotime($in1));
                                if (!empty($duty_in1)) {
                                    $n_in1 = $duty_in1;
                                }

                                $n_out1 = date('H:i:s', strtotime($out1));
                                if (!empty($duty_out1)) {
                                    $n_out1 = $duty_out1;
                                }

                                $n_in2 = date('H:i:s', strtotime($in2));
                                if (!empty($duty_in2)) {
                                    $n_in2 = $duty_in2;
                                }

                                $n_out2 = date('H:i:s', strtotime($out2));
                                if (!empty($duty_out2)) {
                                    $n_out2 = $duty_out2;
                                }

                                if ($od_day == "Whole Day") {

                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $data = ['in1' => $n_in1, 'out1' => $n_out1, 'in2' => $n_in2, 'out2' => $n_out2];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                    $this->insert_prev_duty($bioM, $date, 'DWA', $data);
                                }

                                if ($od_day == "Half Day AM") {

                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $data = ['in1' => $n_in1, 'out1' => $n_out1];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                    $this->insert_prev_duty($bioM, $date, 'DWA', $data);
                                }

                                if ($od_day == "Half Day PM") {

                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $data = ['in2' => $n_in2, 'out2' => $n_out2];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                    $this->insert_prev_duty($bioM, $date, 'DWA', $data);
                                }
                            }

                            if (!empty($in1) && !empty($out1) && empty($in2) && empty($out2)) {

                                $n_in1 = date('H:i:s', strtotime($in1));
                                if (!empty($duty_in1)) {
                                    $n_in1 = $duty_in1;
                                }

                                $n_out1 = date('H:i:s', strtotime($out1));
                                if (!empty($duty_out2)) {
                                    $n_out2 = $duty_out2;
                                }

                                if ($od_day == "Whole Day") {

                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $data = ['in1' => $n_in1, 'out1' => '', 'in2' => '', 'out2' => $n_out2];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                    $this->insert_prev_duty($bioM, $date, 'DWA', $data);
                                }
                            }

                            $cond = ['bioMetricId' => $bioM, 'date' => $date];
                            $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                            if ($duty) {

                                $dutyId = $duty['dutyId'];
                                $d_in1  = $duty['in1'];
                                $d_out1 = $duty['out1'];
                                $d_in2  = $duty['in2'];
                                $d_out2 = $duty['out2'];
                                $d_in3  = $duty['in3'];
                                $d_out3 = $duty['out3'];

                                $this->netEntery($empId, $bioM, $date, $d_in1, $d_out1, $d_in2, $d_out2, $d_in3, $d_out3);

                                $hw_n = 0;
                                $cond = ['dutyId' => $dutyId];
                                $perday = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                                if ($perday) {
                                    $hw_n = $perday['hoursWork'];
                                }

                                $cond = ['empId' => $empId, 'dateDuty' => $date];
                                $data = ['hw_added' => $hw_n];
                                $update = $this->promo_model->update('tk', 'perday_previous', $cond, $data);
                            }
                        }
                    }
                }

                // DWA - System Error
                if (true) {
                    $cond = "empId='$empId' AND (dateFrom BETWEEN '$df' AND '$dt') AND (dateTo BETWEEN '$df' AND '$dt') 
                    AND type='System Error' AND status='Approved' AND supStatus='Previous'";
                    $query = $this->promo_model->select_resultArray('tk', 'd_w_adjustment', $cond);
                    foreach ($query as $key => $row) {

                        $dateFrom   = $row['dateFrom'];
                        $fs_id      = $row['adj_errfai_id'];

                        $cond = ['empId' => $empId, 'err_fai_id' => $fs_id, 'date' => $dateFrom];
                        $dwaf = $this->promo_model->select_rowArray('tk', 'dwa_error_failed', $cond);
                        if ($dwaf) {

                            $date   = $dwaf['date'];
                            $c_in1  = $dwaf['in1'];
                            $c_out1 = $dwaf['out1'];
                            $c_in2  = $dwaf['in2'];
                            $c_out2 = $dwaf['out2'];
                            $c_in3  = $dwaf['in3'];
                            $c_out3 = $dwaf['out3'];

                            $cond = ['bioMetricId' => $bioM, 'date' => $date];
                            $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                            if ($duty) {

                                $dutyId     = $duty['dutyId'];
                                $duty_in1   = $duty['in1'];
                                $duty_out1  = $duty['out1'];
                                $duty_in2   = $duty['in2'];
                                $duty_out2  = $duty['out2'];
                                $duty_in3   = $duty['in3'];
                                $duty_out3  = $duty['out3'];

                                $cond = ['bioMetricId' => $bioM, 'dateRendered' => $date];
                                $check = $this->promo_model->select_rowArray('tk', $duty_temp, $cond);
                                if (!$check) {
                                    $data = [
                                        'bioMetricId'   => $bioM,
                                        'dateRendered'  => $date,
                                        'in1'           => $duty_in1,
                                        'out1'          => $duty_out1,
                                        'in2'           => $duty_in2,
                                        'out2'          => $duty_out2,
                                        'in3'           => $duty_in3,
                                        'out3'          => $duty_out3,
                                    ];
                                    $insert = $this->promo_model->insert('tk', $duty_temp, $data);
                                }

                                $hw_temp = 0;
                                $cond = ['dutyId' => $dutyId];
                                $perday = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                                if ($perday) {
                                    $hw_temp = $perday['hoursWork'];
                                }

                                $cond = ['empId' => $empId, 'dateDuty' => $date];
                                $check = $this->promo_model->select_rowArray('tk', 'perday_previous', $cond);
                                if (!$check) {
                                    $data = [
                                        'empId'     => $empId,
                                        'dateDuty'  => $date,
                                        'hw_old'    => $hw_temp,
                                        'coDF1'     => $df,
                                        'coDF2'     => $dt,
                                        'request'   => 'DWA'
                                    ];

                                    $insert = $this->promo_model->insert('tk', 'perday_previous', $data);
                                }
                            }

                            $shiftCode = $this->getSC($empId, $bioM, $date);

                            $shIn1 = $shOut1 = $shIn2 = $shOut2 = '';
                            $cond = "empId='$empId' AND ('$date' BETWEEN dateFrom AND dateTo) AND shiftCode='$shiftCode'";
                            $check = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond);
                            if ($check) {
                                $cond = ['shiftCode' => $shiftCode];
                                $sc = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
                                if ($sc) {
                                    $shIn1  = $sc['1stIn'];
                                    $shOut1 = $sc['1stOut'];
                                    $shIn2  = $sc['2ndIn'];
                                    $shOut2 = $sc['2ndOut'];
                                }
                            }

                            $dv_in1 = $dv_out1 = $dv_in2 = $dv_out2 = $dv_in3 = $dv_out3 = "";
                            $cond = ['empId' => $empId, 'err_fai_id' => $fs_id, 'date' => $date];
                            $dwav = $this->promo_model->select_rowArray('tk', 'dwa_err_failed_value', $cond);
                            if ($dwav) {
                                $dv_in1  = $dwav['in1'];
                                $dv_out1 = $dwav['out1'];
                                $dv_in2  = $dwav['in2'];
                                $dv_out2 = $dwav['out2'];
                                $dv_in3  = $dwav['in3'];
                                $dv_out3 = $dwav['out3'];
                            }

                            if ($c_in1 == "True") {
                                if ($check) {
                                    $v_in1 = date('H:i:s', strtotime($shIn1));
                                } else {
                                    $v_in1 = date('H:i:s', strtotime($dv_in1));
                                }

                                $data = ['in1' => $v_in1];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $this->insert_prev_duty($bioM, $date, 'DWA', $data);
                            }

                            if ($c_out1 == "True") {
                                if ($check) {
                                    $v_out1 = date('H:i:s', strtotime($shOut1));
                                } else {
                                    $v_out1 = date('H:i:s', strtotime($dv_out1));
                                }

                                $data = ['out1' => $v_out1];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $this->insert_prev_duty($bioM, $date, 'DWA', $data);
                            }

                            if ($c_in2 == "True") {
                                if ($check) {
                                    $v_in2 = date('H:i:s', strtotime($shIn2));
                                } else {
                                    $v_in2 = date('H:i:s', strtotime($dv_in2));
                                }

                                $data = ['in2' => $v_in2];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $this->insert_prev_duty($bioM, $date, 'DWA', $data);
                            }

                            if ($c_out2 == "True") {
                                if ($check) {
                                    if ($shOut2) {
                                        $v_out2 = date('H:i:s', strtotime($shOut2));
                                    } else {
                                        $v_out2 = date('H:i:s', strtotime($shOut1));
                                    }
                                } else {
                                    $v_out2 = date('H:i:s', strtotime($dv_out2));
                                }

                                $data = ['out2' => $v_out2];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $this->insert_prev_duty($bioM, $date, 'DWA', $data);
                            }

                            if ($c_in3 == "True") {
                                $v_in3 = date('H:i:s', strtotime($dv_in3));

                                $data = ['in3' => $v_in3];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $this->insert_prev_duty($bioM, $date, 'DWA', $data);
                            }

                            if ($c_out3 == "True") {
                                $v_out3 = date('H:i:s', strtotime($dv_out3));

                                $data = ['out3' => $v_out3];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $this->insert_prev_duty($bioM, $date, 'DWA', $data);
                            }

                            $cond = ['bioMetricId' => $bioM, 'date' => $date];
                            $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                            if ($duty) {

                                $dutyId = $duty['dutyId'];
                                $d_in1  = $duty['in1'];
                                $d_out1 = $duty['out1'];
                                $d_in2  = $duty['in2'];
                                $d_out2 = $duty['out2'];
                                $d_in3  = $duty['in3'];
                                $d_out3 = $duty['out3'];

                                $this->netEntery($empId, $bioM, $date, $d_in1, $d_out1, $d_in2, $d_out2, $d_in3, $d_out3);

                                $hw_n = 0;
                                $cond = ['dutyId' => $dutyId];
                                $perday = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                                if ($perday) {
                                    $hw_n = $perday['hoursWork'];
                                }

                                $cond = ['empId' => $empId, 'dateDuty' => $date];
                                $data = ['hw_added' => $hw_n];
                                $update = $this->promo_model->update('tk', 'perday_previous', $cond, $data);
                            }
                        }
                    }
                }

                // DWA - Failure - Wong Mode - Wrong User
                if (true) {
                    for ($xcu = 1; $xcu <= 2; $xcu++) {

                        if ($xcu == 1) {
                            $cond = "empId='$empId' AND (dateFrom BETWEEN '$df' AND '$dt') AND (dateTo BETWEEN '$df' AND '$dt') 
                            AND status='Approved' AND (type='Failure' OR type='Wrong User') AND supStatus='Previous'";
                        }

                        if ($xcu == 2) {
                            $cond = "empId='$empId' AND (dateFrom BETWEEN '$df' AND '$dt') AND (dateTo BETWEEN '$df' AND '$dt') 
                            AND type='Wrong Mode' AND status!='Pending' AND supStatus='Previous'";
                        }

                        $query = $this->promo_model->select_resultArray('tk', 'd_w_adjustment', $cond);
                        foreach ($query as $key => $row) {

                            $dateFrom   = $row['dateFrom'];
                            $fs_id      = $row['adj_errfai_id'];

                            $cond = ['empId' => $empId, 'err_fai_id' => $fs_id, 'date' => $dateFrom];
                            $dwaf = $this->promo_model->select_rowArray('tk', 'dwa_error_failed', $cond);
                            if ($dwaf) {

                                $date   = $dwaf['date'];
                                $c_in1  = $dwaf['in1'];
                                $c_out1 = $dwaf['out1'];
                                $c_in2  = $dwaf['in2'];
                                $c_out2 = $dwaf['out2'];
                                $c_in3  = $dwaf['in3'];
                                $c_out3 = $dwaf['out3'];

                                $cond = ['bioMetricId' => $bioM, 'date' => $date];
                                $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                                if ($duty) {

                                    $dutyId     = $duty['dutyId'];
                                    $duty_in1   = $duty['in1'];
                                    $duty_out1  = $duty['out1'];
                                    $duty_in2   = $duty['in2'];
                                    $duty_out2  = $duty['out2'];
                                    $duty_in3   = $duty['in3'];
                                    $duty_out3  = $duty['out3'];

                                    $cond = ['bioMetricId' => $bioM, 'dateRendered' => $date];
                                    $check = $this->promo_model->select_rowArray('tk', $duty_temp, $cond);
                                    if (!$check) {
                                        $data = [
                                            'bioMetricId'   => $bioM,
                                            'dateRendered'  => $date,
                                            'in1'           => $duty_in1,
                                            'out1'          => $duty_out1,
                                            'in2'           => $duty_in2,
                                            'out2'          => $duty_out2,
                                            'in3'           => $duty_in3,
                                            'out3'          => $duty_out3,
                                        ];
                                        $insert = $this->promo_model->insert('tk', $duty_temp, $data);
                                    }

                                    $hw_temp = 0;
                                    $cond = ['dutyId' => $dutyId];
                                    $perday = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                                    if ($perday) {
                                        $hw_temp = $perday['hoursWork'];
                                    }

                                    $cond = ['empId' => $empId, 'dateDuty' => $date];
                                    $check = $this->promo_model->select_rowArray('tk', 'perday_previous', $cond);
                                    if (!$check) {
                                        $data = [
                                            'empId'     => $empId,
                                            'dateDuty'  => $date,
                                            'hw_old'    => $hw_temp,
                                            'coDF1'     => $df,
                                            'coDF2'     => $dt,
                                            'request'   => 'DWA'
                                        ];

                                        $insert = $this->promo_model->insert('tk', 'perday_previous', $data);
                                    }
                                }

                                $shiftCode = $this->getSC($empId, $bioM, $date);

                                $cond   = ['shiftCode' => $shiftCode];
                                $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
                                if ($sc) {
                                    $in1    = $sc['1stIn'];
                                    $out1   = $sc['1stOut'];
                                    $in2    = $sc['2ndIn'];
                                    $out2   = $sc['2ndOut'];
                                }

                                $cond   = ['shiftCode' => $shiftCode];
                                $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
                                if ($scb) {
                                    $in1    = $scb['1stIn'];
                                    $out1   = $scb['1stOut'];
                                    $in2    = $scb['2ndIn'];
                                    $out2   = $scb['2ndOut'];
                                }

                                $day_in1    = date('A', strtotime($in1));
                                $day_out1   = date('A', strtotime($out1));
                                $day_in2    = date('A', strtotime($in2));
                                $day_out2   = date('A', strtotime($out2));

                                $dateIn1    = date("Y-m-d H:i:s", strtotime("$date $in1"));
                                $dateOut1   = date("Y-m-d H:i:s", strtotime("$date $out1"));
                                $dateIn2    = date("Y-m-d H:i:s", strtotime("$date $in2"));
                                $dateOut2   = date("Y-m-d H:i:s", strtotime("$date $out2"));

                                $next_not = false;
                                if ($day_in2 == "AM" && $day_out2 == "AM" && !$scb) {
                                    $next_not = true;
                                }

                                if ($day_out1 == "AM" && $day_in2 == "AM" && $day_out2 == "AM" && !$scb) {
                                    $next_not = true;
                                }

                                if ($scb) {
                                    if ($day_in1 == "PM" && $day_in2 == "AM" && $day_out2 == "AM") {
                                        $next_not = true;
                                    }

                                    if ($day_in1 == "PM" && $day_out1 == "AM" && $day_in2 == "AM") {
                                        $next_not = true;
                                    }
                                }

                                if ($c_in1 == "True") {
                                    $v_in1 = date('H:i:s', strtotime($in1));

                                    $data = ['in1' => $v_in1];
                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                    $this->insert_prev_duty($bioM, $date, 'DWA', $data);
                                }

                                if ($c_out1 == "True") {
                                    $v_out1 = date('H:i:s', strtotime($out1));

                                    $data = ['out1' => $v_out1];
                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                    $this->insert_prev_duty($bioM, $date, 'DWA', $data);
                                }

                                if ($c_in2 == "True") {
                                    $v_in2 = date('H:i:s', strtotime($in2));

                                    $data = ['in2' => $v_in2];
                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                    $this->insert_prev_duty($bioM, $date, 'DWA', $data);
                                }

                                if ($c_out2 == "True") {
                                    $v_out2 = date('H:i:s', strtotime($out2));

                                    $data = ['out2' => $v_out2];
                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                    $this->insert_prev_duty($bioM, $date, 'DWA', $data);
                                }

                                if ($c_in3 == "True") {
                                    $v_in3 = date('H:i:s', strtotime($in3));

                                    $data = ['in3' => $v_in3];
                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                    $this->insert_prev_duty($bioM, $date, 'DWA', $data);
                                }

                                if ($c_out3 == "True") {
                                    $v_out3 = date('H:i:s', strtotime($out3));

                                    $data = ['out3' => $v_out3];
                                    $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                    $this->insert_prev_duty($bioM, $date, 'DWA', $data);
                                }

                                if ($next_not) {

                                    $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                    $dnext = $this->promo_model->select_rowArray('tk', 'duty_next', $cond);
                                    if ($dnext) {

                                        $data = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                        $insert = $this->promo_model->insert('tk', 'duty_next', $data);
                                    }

                                    if ($c_in1 == "True") {
                                        $data = ['in1_ka' => $dateIn1];
                                        $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                        $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                    }

                                    if ($c_out1 == "True") {
                                        $data = ['out1_ka' => $dateOut1];
                                        $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                        $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                    }

                                    if ($c_in2 == "True") {
                                        $data = ['in2_ka' => $dateIn2];
                                        $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                        $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                    }

                                    if ($c_out2 == "True") {
                                        $data = ['out2_ka' => $dateOut2];
                                        $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                        $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                    }
                                }

                                $cond = ['bioMetricId' => $bioM, 'date' => $date];
                                $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                                if ($duty) {

                                    $dutyId = $duty['dutyId'];
                                    $d_in1  = $duty['in1'];
                                    $d_out1 = $duty['out1'];
                                    $d_in2  = $duty['in2'];
                                    $d_out2 = $duty['out2'];
                                    $d_in3  = $duty['in3'];
                                    $d_out3 = $duty['out3'];

                                    $this->netEntery($empId, $bioM, $date, $d_in1, $d_out1, $d_in2, $d_out2, $d_in3, $d_out3);

                                    $hw_n = 0;
                                    $cond = ['dutyId' => $dutyId];
                                    $perday = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                                    if ($perday) {
                                        $hw_n = $perday['hoursWork'];
                                    }

                                    $cond = ['empId' => $empId, 'dateDuty' => $date];
                                    $data = ['hw_added' => $hw_n];
                                    $update = $this->promo_model->update('tk', 'perday_previous', $cond, $data);
                                }
                            }
                        }
                    }
                }

                // Undertime Authorization
                if (true) {
                    $cond = "empId='$empId' AND supDF='$df' AND supDT='$dt' AND status='Approved' AND supStatus='Previous'";
                    $query = $this->promo_model->select_resultArray('tk', 'undertime_auth', $cond);
                    foreach ($query as $key => $row) {

                        $utWhen     = $row['utWhen'];
                        $numHours   = $row['numHours'];
                        $date       = $row['dateUndertime'];
                        $tempUH     = $numHours * 1;

                        $t = explode(".", $tempUH);
                        $tUH = isset($t[0]) ? $t[0] * 1 : 0;
                        $tUM = isset($t[1]) ? $t[1] * 1 : 0;  // will be 0 if no decimal

                        $numHours = "$tUH hours";

                        if ($tUM == 5) {
                            $numHours = "$tUH hours, -30 minutes";
                        }

                        $u_out1 = $u_out2 = "";
                        $cond = ['date' => $date, 'bioMetricId' => $bioM];
                        $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                        if ($duty) {

                            $u_out1     = $duty['out1'];
                            $u_out2     = $duty['out2'];

                            $dutyId     = $duty['dutyId'];
                            $duty_in1   = $duty['in1'];
                            $duty_out1  = $duty['out1'];
                            $duty_in2   = $duty['in2'];
                            $duty_out2  = $duty['out2'];
                            $duty_in3   = $duty['in3'];
                            $duty_out3  = $duty['out3'];

                            $cond = ['bioMetricId' => $bioM, 'dateRendered' => $date];
                            $check = $this->promo_model->select_rowArray('tk', $duty_temp, $cond);
                            if (!$check) {
                                $data = [
                                    'bioMetricId'   => $bioM,
                                    'dateRendered'  => $date,
                                    'in1'           => $duty_in1,
                                    'out1'          => $duty_out1,
                                    'in2'           => $duty_in2,
                                    'out2'          => $duty_out2,
                                    'in3'           => $duty_in3,
                                    'out3'          => $duty_out3,
                                ];
                                $insert = $this->promo_model->insert('tk', $duty_temp, $data);
                            }

                            $hw_temp = 0;
                            $cond = ['dutyId' => $dutyId];
                            $perday = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                            if ($perday) {
                                $hw_temp = $perday['hoursWork'];
                            }

                            $cond = ['empId' => $empId, 'dateDuty' => $date];
                            $chk1 = $this->promo_model->select_rowArray('tk', 'perday_previous', $cond);

                            $cond = "empId='$empId' AND date='$date' AND (type='undertime 1' OR type='undertime 2')";
                            $chk2 = $this->promo_model->select_rowArray('tk', 'manualinputs', $cond);

                            if (!$chk1 && !$chk2) {
                                $data = [
                                    'empId'     => $empId,
                                    'dateDuty'  => $date,
                                    'hw_old'    => $hw_temp,
                                    'coDF1'     => $df,
                                    'coDF2'     => $dt,
                                    'request'   => 'EUA'
                                ];

                                $insert = $this->promo_model->insert('tk', 'perday_previous', $data);
                            }
                        }

                        $shiftCode = $this->getSC($empId, $bioM, $date);

                        $cond   = ['shiftCode' => $shiftCode];
                        $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
                        if ($sc) {

                            $s_in1  = $sc['1stIn'];
                            $s_out1 = $sc['1stOut'];
                            $s_in2  = $sc['2ndIn'];
                            $s_out2 = $sc['2ndOut'];
                        }

                        $cond   = ['shiftCode' => $shiftCode];
                        $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
                        if ($scb) {

                            $s_in1  = $scb['1stIn'];
                            $s_out1 = $scb['1stOut'];
                            $s_in2  = $scb['2ndIn'];
                            $s_out2 = $scb['2ndOut'];
                        }

                        $day_in1    = date('A', strtotime($s_in1));
                        $day_out1   = date('A', strtotime($s_out1));
                        $day_in2    = date('A', strtotime($s_in2));
                        $day_out2   = date('A', strtotime($s_out2));

                        $next_not = false;
                        if ($day_in2 == "AM" && $day_out2 == "AM" && !$scb) {
                            $next_not = true;
                        }

                        if ($day_out1 == "AM" && $day_in2 == "AM" && $day_out2 == "AM" && !$scb) {
                            $next_not = true;
                        }

                        if ($scb) {
                            if ($day_in1 == "PM" && $day_in2 == "AM" && $day_out2 == "AM") {
                                $next_not = true;
                            }

                            if ($day_in1 == "PM" && $day_out1 == "AM" && $day_in2 == "AM") {
                                $next_not = true;
                            }
                        }

                        $in1_sc = $out1_sc = $in2_sc = $out2_sc = "";
                        $cond = ['shiftCode' => $shiftCode];
                        $sc = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
                        if ($sc) {

                            if (!empty($sc['1stIn'])) {
                                $in1_sc = date("H:i:s", strtotime($sc['1stIn']));
                            }

                            if (!empty($sc['1stOut'])) {
                                $out1_sc = date("H:i:s", strtotime($sc['1stOut']));
                            }

                            if (!empty($sc['2ndIn'])) {
                                $in2_sc = date("H:i:s", strtotime($sc['2ndIn']));
                            }

                            if (!empty($sc['2ndOut'])) {
                                $out2_sc = date("H:i:s", strtotime($sc['2ndOut']));
                            }
                        }

                        if ($utWhen == "Out 1") {

                            $tempHour = date('H:i:s', strtotime("$out1_sc -$numHours"));
                            if (strtotime($u_out1) > strtotime($tempHour)) {

                                $data = ['out1' => $tempHour];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $this->insert_prev_duty($bioM, $date, 'EUA', $data);
                            }
                        }

                        if ($utWhen == "Out 2") {

                            $tempHour = date('H:i:s', strtotime("$out2_sc -$numHours"));
                            if (strtotime($u_out2) > strtotime($tempHour)) {

                                $data = ['out2' => $tempHour];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $this->insert_prev_duty($bioM, $date, 'EUA', $data);
                            }
                        }

                        if ($next_not) {

                            if ($utWhen == "Out 1") {

                                $tempHour = date('H:i:s', strtotime("$out1_sc -$numHours"));
                                if (strtotime($u_out1) > strtotime($tempHour)) {

                                    $data = ['out1_ka' => $tempHour];
                                    $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                    $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                }
                            }

                            if ($utWhen == "Out 2") {

                                $tempHour = date('H:i:s', strtotime("$out2_sc -$numHours"));
                                if (strtotime($u_out2) > strtotime($tempHour)) {

                                    $data = ['out2_ka' => $tempHour];
                                    $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                    $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                }
                            }
                        }

                        $cond = ['bioMetricId' => $bioM, 'date' => $date];
                        $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                        if ($duty) {

                            $d_in1  = $duty['in1'];
                            $d_out1 = $duty['out1'];
                            $d_in2  = $duty['in2'];
                            $d_out2 = $duty['out2'];
                            $d_in3  = $duty['in3'];
                            $d_out3 = $duty['out3'];

                            $this->netEntery($empId, $bioM, $date, $d_in1, $d_out1, $d_in2, $d_out2, $d_in3, $d_out3);

                            $hw_n = 0;
                            $cond = ['dutyId' => $dutyId];
                            $perday = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                            if ($perday) {
                                $hw_n = $perday['hoursWork'];
                            }

                            $cond = ['empId' => $empId, 'dateDuty' => $date];
                            $data = ['hw_added' => $hw_n];
                            $update = $this->promo_model->update('tk', 'perday_previous', $cond, $data);
                        }
                    }
                }

                // Cover-Up Authorization
                if (true) {
                    $cond = "empId='$empId' AND supDF='$df' AND supDT='$dt' AND status='Approved' AND supStatus='Previous'";
                    $query = $this->promo_model->select_resultArray('tk', 'coverup_auth', $cond);
                    foreach ($query as $key => $row) {
                        $eCAId      = $row['eCAId'];
                        $date       = $row['cuDate'];
                        $cuPurpose  = $row['cuPurpose'];

                        $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = "";
                        $duty_in1 = $duty_out1 = $duty_in2 = $duty_out2 = $duty_in3 = $duty_out3 = "";
                        $cond = ['date' => $date, 'bioMetricId' => $bioM];
                        $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                        if ($duty) {

                            $in1    = $duty['in1'];
                            $out1   = $duty['out1'];
                            $in2    = $duty['in2'];
                            $out2   = $duty['out2'];
                            $in3    = $duty['in3'];
                            $out3   = $duty['out3'];

                            $dutyId     = $duty['dutyId'];
                            $duty_in1   = $duty['in1'];
                            $duty_out1  = $duty['out1'];
                            $duty_in2   = $duty['in2'];
                            $duty_out2  = $duty['out2'];
                            $duty_in3   = $duty['in3'];
                            $duty_out3  = $duty['out3'];

                            $cond = ['bioMetricId' => $bioM, 'dateRendered' => $date];
                            $check = $this->promo_model->select_rowArray('tk', $duty_temp, $cond);
                            if (!$check) {
                                $data = [
                                    'bioMetricId'   => $bioM,
                                    'dateRendered'  => $date,
                                    'in1'           => $duty_in1,
                                    'out1'          => $duty_out1,
                                    'in2'           => $duty_in2,
                                    'out2'          => $duty_out2,
                                    'in3'           => $duty_in3,
                                    'out3'          => $duty_out3,
                                ];
                                $insert = $this->promo_model->insert('tk', $duty_temp, $data);
                            }

                            $hw_temp = 0;
                            $cond = ['dutyId' => $dutyId];
                            $perday = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                            if ($perday) {
                                $hw_temp = $perday['hoursWork'];
                            }

                            $cond = ['empId' => $empId, 'dateDuty' => $date];
                            $chk1 = $this->promo_model->select_rowArray('tk', 'perday_previous', $cond);
                            if (!$chk1) {
                                $data = [
                                    'empId'     => $empId,
                                    'dateDuty'  => $date,
                                    'hw_old'    => $hw_temp,
                                    'coDF1'     => $df,
                                    'coDF2'     => $dt,
                                    'request'   => 'EUA'
                                ];

                                $insert = $this->promo_model->insert('tk', 'perday_previous', $data);
                            }
                        }

                        $shiftCode = $this->getSC($empId, $bioM, $date);

                        $cond   = ['shiftCode' => $shiftCode];
                        $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
                        if ($sc) {

                            $s_in1  = $sc['1stIn'];
                            $s_out1 = $sc['1stOut'];
                            $s_in2  = $sc['2ndIn'];
                            $s_out2 = $sc['2ndOut'];
                        }

                        $cond   = ['shiftCode' => $shiftCode];
                        $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
                        if ($scb) {

                            $s_in1  = $scb['1stIn'];
                            $s_out1 = $scb['1stOut'];
                            $s_in2  = $scb['2ndIn'];
                            $s_out2 = $scb['2ndOut'];
                        }

                        $day_in1    = date('A', strtotime($s_in1));
                        $day_out1   = date('A', strtotime($s_out1));
                        $day_in2    = date('A', strtotime($s_in2));
                        $day_out2   = date('A', strtotime($s_out2));

                        $in1_sc     = date("H:i:s", strtotime($s_in1));
                        $out1_sc    = date("H:i:s", strtotime($s_out1));
                        $in2_sc     = date("H:i:s", strtotime($s_in2));
                        $out2_sc    = date("H:i:s", strtotime($s_out2));

                        $next_not = false;
                        if ($day_in2 == "AM" && $day_out2 == "AM" && !$scb) {
                            $next_not = true;
                        }

                        if ($day_out1 == "AM" && $day_in2 == "AM" && $day_out2 == "AM" && !$scb) {
                            $next_not = true;
                        }

                        if ($scb) {
                            if ($day_in1 == "PM" && $day_in2 == "AM" && $day_out2 == "AM") {
                                $next_not = true;
                            }

                            if ($day_in1 == "PM" && $day_out1 == "AM" && $day_in2 == "AM") {
                                $next_not = true;
                            }
                        }

                        $usedOT = 0;
                        $cond = ['eCAId' => $eCAId];
                        $query = $this->promo_model->select_resultArray('tk', 'coverup_auth_ext', $cond);
                        foreach ($query as $key => $cu) {
                            $usedOT += $cu['usedOT'];
                        }

                        $t = explode('.', $usedOT);
                        $h = isset($t[0]) ? $t[0] * 1 : 0;
                        $m = isset($t[1]) ? $t[1] * 1 : 0;  // will be 0 if no decimal

                        if ($m == 5) {
                            $m = 30;
                        }

                        if ($cuPurpose != "wholeday") {

                            $t = explode(" ", $cuPurpose);
                            $numT = $t[1] * 1;

                            if ($cuPurpose == "in 1 awol") {

                                $tempH = date("H:i", strtotime("$in1 -$h hours, -$m minutes"));
                                $tempH2 = date("H:i:s", strtotime("$in1 -$h hours, -$m minutes"));

                                if (strtotime("$tempH:00") <= strtotime($in1_sc)) {
                                    $f_In1 = $in1_sc;
                                } else {
                                    $f_In1 = $tempH2;
                                }

                                $data = ['in1' => $f_In1];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $this->insert_prev_duty($bioM, $date, 'ECA', $data);

                                $data = ['status2' => 'Done'];
                                $cond = ['empId' => $empId, 'cuDate' => $date];
                                $update = $this->promo_model->update('tk', 'coverup_auth', $cond, $data);
                            }

                            if ($cuPurpose == "in 2 awol") {


                                $tempH = date("H:i", strtotime("$in2 -$h hours, -$m minutes"));
                                $tempH2 = date("H:i:s", strtotime("$in2 -$h hours, -$m minutes"));


                                if (strtotime("$tempH:00") <= strtotime($in2_sc)) {
                                    $f_In2 = $in2_sc;
                                } else {
                                    $f_In2 = $tempH2;
                                }

                                $data = ['in2' => $f_In2];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $this->insert_prev_duty($bioM, $date, 'ECA', $data);

                                $data = ['status2' => 'Done'];
                                $cond = ['empId' => $empId, 'cuDate' => $date];
                                $update = $this->promo_model->update('tk', 'coverup_auth', $cond, $data);
                            }

                            if ($cuPurpose == "out 1 undertime") {

                                $tempH = date("H:i", strtotime("$out1 +$h hours, +$m minutes"));
                                $tempH2 = date("H:i:s", strtotime("$out1 +$h hours, +$m minutes"));

                                if (strtotime("$tempH:00") >= strtotime($out1_sc)) {
                                    $f_Out1 = $out1_sc;
                                } else {
                                    $f_Out1 = $tempH2;
                                }

                                $data = ['out1' => $f_Out1];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $this->insert_prev_duty($bioM, $date, 'ECA', $data);

                                $data = ['status2' => 'Done'];
                                $cond = ['empId' => $empId, 'cuDate' => $date];
                                $update = $this->promo_model->update('tk', 'coverup_auth', $cond, $data);
                            }

                            if ($cuPurpose == "out 2 undertime") {

                                $tempH = date("H:i", strtotime("$out2 +$h hours, +$m minutes"));
                                $tempH2 = date("H:i:s", strtotime("$out2 +$h hours, +$m minutes"));

                                if (strtotime("$tempH:00") >= strtotime($out2_sc)) {
                                    $f_Out2 = $out2_sc;
                                } else {
                                    $f_Out2 = $tempH2;
                                }

                                $data = ['out2' => $f_Out2];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $this->insert_prev_duty($bioM, $date, 'ECA', $data);

                                $data = ['status2' => 'Done'];
                                $cond = ['empId' => $empId, 'cuDate' => $date];
                                $update = $this->promo_model->update('tk', 'coverup_auth', $cond, $data);
                            }

                            if ($cuPurpose == "halfday 1") {

                                $data = ['in1' => $in1_sc, 'out1' => $out1_sc];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $this->insert_prev_duty($bioM, $date, 'ECA', $data);

                                $data = ['status2' => 'Done'];
                                $cond = ['empId' => $empId, 'cuDate' => $date];
                                $update = $this->promo_model->update('tk', 'coverup_auth', $cond, $data);
                            }

                            if ($cuPurpose == "halfday 2") {

                                $data = ['in2' => $in2_sc, 'out2' => $out2_sc];
                                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $this->insert_prev_duty($bioM, $date, 'ECA', $data);

                                $data = ['status2' => 'Done'];
                                $cond = ['empId' => $empId, 'cuDate' => $date];
                                $update = $this->promo_model->update('tk', 'coverup_auth', $cond, $data);
                            }
                        }

                        if ($cuPurpose == "wholeday") {

                            $data = ['in1' => $in1_sc, 'out1' => $out1_sc, 'in2' => $in2_sc, 'out2' => $out2_sc];
                            $cond = ['bioMetricId' => $bioM, 'dateDuty' => $date];
                            $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                            $this->insert_prev_duty($bioM, $date, 'ECA', $data);

                            $data = ['status2' => 'Done'];
                            $cond = ['empId' => $empId, 'cuDate' => $date];
                            $update = $this->promo_model->update('tk', 'coverup_auth', $cond, $data);
                        }

                        if ($next_not) {

                            $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                            $dnext = $this->promo_model->select_rowArray('tk', 'duty_next', $cond);
                            if ($dnext) {
                                $data = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                $insert = $this->promo_model->insert('tk', 'duty_next', $data);
                            }

                            if ($cuPurpose == "wholeday") {
                                $data = ['in1_ka' => $in1_sc, 'out1_ka' => $out1_sc, 'in2_ka' => $in2_sc, 'out2_ka' => $out2_sc];
                                $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                            }

                            if ($cuPurpose != "wholeday") {

                                if ($cuPurpose == "in 1 awol") {

                                    $tempH = date("H:i", strtotime("$in1 -$h hours, -$m minutes"));
                                    $tempH2 = date("H:i:s", strtotime("$in1 -$h hours, -$m minutes"));

                                    if (strtotime("$tempH:00") <= strtotime($in1_sc)) {
                                        $f_In1 = $in1_sc;
                                    } else {
                                        $f_In1 = $tempH2;
                                    }

                                    $data = ['in1_ka' => $f_In1];
                                    $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                    $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                }

                                if ($cuPurpose == "in 2 awol") {

                                    $tempH = date("H:i", strtotime("$in2 -$h hours, -$m minutes"));
                                    $tempH2 = date("H:i:s", strtotime("$in2 -$h hours, -$m minutes"));

                                    if (strtotime("$tempH:00") <= strtotime($in2_sc)) {
                                        $f_In2 = $in2_sc;
                                    } else {
                                        $f_In2 = $tempH2;
                                    }

                                    $data = ['in2_ka' => $f_In2];
                                    $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                    $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                }

                                if ($cuPurpose == "out 1 undertime") {

                                    $tempH = date("H:i", strtotime("$out1 +$h hours, +$m minutes"));
                                    $tempH2 = date("H:i:s", strtotime("$out1 +$h hours, +$m minutes"));

                                    if (strtotime("$tempH:00") >= strtotime($out1_sc)) {
                                        $f_Out1 = $out1_sc;
                                    } else {
                                        $f_Out1 = $tempH2;
                                    }

                                    $data = ['out1_ka' => $f_Out1];
                                    $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                    $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                }

                                if ($cuPurpose == "out 2 undertime") {

                                    $tempH = date("H:i", strtotime("$out2 +$h hours, +$m minutes"));
                                    $tempH2 = date("H:i:s", strtotime("$out2 +$h hours, +$m minutes"));

                                    if (strtotime("$tempH:00") >= strtotime($out2_sc)) {
                                        $f_Out2 = $out2_sc;
                                    } else {
                                        $f_Out2 = $tempH2;
                                    }

                                    $data = ['out2_ka' => $f_Out2];
                                    $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                    $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                }

                                if ($cuPurpose == "halfday 1") {

                                    $data = ['in1_ka' => $in1_sc, 'out1_ka' => $out1_sc];
                                    $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                    $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                }

                                if ($cuPurpose == "halfday 2") {

                                    $data = ['in2_ka' => $in2_sc, 'out2_ka' => $out2_sc];
                                    $cond = ['bioMetricId_ka' => $bioM, 'dateDuty_ka' => $date];
                                    $update = $this->promo_model->update('tk', 'duty_next', $cond, $data);
                                }
                            }
                        }

                        $cond = ['bioMetricId' => $bioM, 'date' => $date];
                        $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                        if ($duty) {
                            $dutyId = $duty['dutyId'];
                            $d_in1  = $duty['in1'];
                            $d_out1 = $duty['out1'];
                            $d_in2  = $duty['in2'];
                            $d_out2 = $duty['out2'];
                            $d_in3  = $duty['in3'];
                            $d_out3 = $duty['out3'];

                            $this->netEntery($empId, $bioM, $date, $d_in1, $d_out1, $d_in2, $d_out2, $d_in3, $d_out3);

                            $hw_n = 0;
                            $cond = ['dutyId' => $dutyId];
                            $perday = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                            if ($perday) {
                                $hw_n = $perday['hoursWork'];
                            }

                            $cond = ['empId' => $empId, 'dateDuty' => $date];
                            $data = ['hw_added' => $hw_n];
                            $update = $this->promo_model->update('tk', 'perday_previous', $cond, $data);
                        }
                    }
                }

                // calculate otforPay_prev
                if (true) {
                    $otPay_Prev = 0;
                    $cond = ['empId' => $empId, 'coDF1' => $df, 'coDF2' => $dt];
                    $numEO = $this->promo_model->select_resultArray('tk', 'otforpayment_previous_not', $cond);
                    if ($numEO != 0) {
                        $otPay_Prev = 0;
                    }
                }

                // transfer to duty entry
                if (true) {
                    $cond = "1 ORDER BY dateRendered DESC";
                    $query = $this->promo_model->select_resultArray('tk', $duty_temp, $cond);
                    foreach ($query as $key => $row) {

                        $temp_bio   = $row['bioMetricId'];
                        $temp_date  = $row['dateRendered'];
                        $temp_in1   = $row['in1'];
                        $temp_out1  = $row['out1'];
                        $temp_in2   = $row['in2'];
                        $temp_out2  = $row['out2'];
                        $temp_in3   = $row['in3'];
                        $temp_out3  = $row['out3'];

                        if ($temp_out1 == $temp_in2 && $temp_out1 != '' && $temp_in2 != '') {

                            $cond = ['bioMetricId' => $temp_bio, 'dateDuty' => $temp_date];
                            $data = ['out1' => '', 'in2' => ''];
                            $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                        }
                    }
                }

                // update hours (perday_previous)
                if (true) {
                    $cond = ['empId' => $empId, 'coDF1' => $df, 'coDF2' => $dt];
                    $query = $this->promo_model->select_resultArray('tk', 'perday_previous', $cond);
                    foreach ($query as $key => $pdp) {

                        $dateDuty   = $pdp['dateDuty'];
                        $hw_old     = $pdp['hw_old'];
                        $hw_added   = $pdp['hw_added'];
                        $request    = $pdp['request'];

                        $t = explode("&", $hw_old);
                        $h_o = isset($t[0]) ? $t[0] * 1 : 0;
                        $m_o = isset($t[1]) ? $t[1] * 1 : 0;  // will be 0 if no decimal

                        $t = explode("&", $hw_added);
                        $h_a = isset($t[0]) ? $t[0] * 1 : 0;
                        $m_a = isset($t[1]) ? $t[1] * 1 : 0;  // will be 0 if no decimal

                        $sign = "+";
                        $tempHW = date("H:i", strtotime("$h_a:$m_a -$h_o hours, -$m_o minutes"));

                        if ($request == "EUA") {
                            $sign = "-";
                            $tempHW = date("H:i", strtotime("$h_o:$m_o -$h_a hours, -$m_a minutes"));
                        }

                        $tH = date("H", strtotime($tempHW)) * 1;
                        $tM = date("i", strtotime($tempHW)) * 1;

                        $ttM = "";
                        if ($tM != 0) {
                            $ttM = " & $tM min(s).";
                        }

                        $tot_HW = "$tH hr(s). $ttM";

                        // convert to decimal

                        $tempH = $tH / 8;
                        $tempM = ($tM / 60) / 8;

                        if (($tempH * 1) == 0) {
                            $tempM = ($tM / 60) / 8;
                        }

                        $tempHM = $tempH + $tempM;
                        $conHW = round($tempHM, 3);
                        $temp_conHW = round($tempHM, 3);

                        // end of convert to decimal

                        if ($temp_conHW > 1) {
                            $conHW = 0;
                        }

                        if (strtotime("$h_o:$m_o") > strtotime("$h_a:$m_a")) {
                            $tot_HW = "Error";
                        }

                        $cond = ['empId' => $empId, 'dateDuty' => $dateDuty, 'coDF1' => $df, 'coDF2' => $dt];
                        $data = ['hw_new' => $tot_HW, 'hw_con' => "$sign$conHW"];
                        $update = $this->promo_model->update('tk', 'perday_previous', $cond, $data);

                        $cond = ['bioMetricId' => $bioM, 'date' => $dateDuty];
                        $duty = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                        if ($duty) {
                            // echo json_encode(array(
                            //     'result'    => 'loading',
                            //     'msg'       => '%',
                            //     'msg1'      => '%',
                            //     'date'      => "get hwsss",
                            //     'table'     => "[date: $dateDuty; empId: $empId; hw: $hw_old]",
                            //     'exported'  => 'table_update'
                            // ));
                            $dutyId = $duty['dutyId'];
                            $cond = ['dutyId' => $dutyId];
                            $data = ['hoursWork' => $hw_old];
                            $update = $this->promo_model->update('tk', 'perday', $cond, $data);
                        }

                        if ($temp_conHW == '0') {
                            $cond = ['empId' => $empId, 'dateDuty' => $dateDuty, 'coDF1' => $df, 'coDF2' => $dt];
                            $update = $this->promo_model->delete('tk', 'perday_previous', $cond);
                        }
                    }
                }

                // calculate daysWork (perday_previous)
                if (true) {

                    $hw_con = 0;
                    $cond = ['empId' => $empId, 'coDF1' => $df, 'coDF2' => $dt];
                    $pdp = $this->promo_model->select_resultArray('tk', 'perday_previous', $cond);
                    if ($pdp) {
                        $hw_con = array_sum(array_column($pdp, 'hw_con'));
                    }

                    $cond = ['empId' => $empId, 'dateFrom' => $df, 'dateTo' => $dt, 'status' => 'active'];
                    $pdpa = $this->promo_model->select_resultArray('tk', 'perday_previous_allow', $cond);
                    if ($pdpa) {
                        $totPer = round($totPer + $hw_con, 3);
                    }
                }

                // calculate snackAllowance
                if (true) {
                    $newSM = 0;
                    $temp_HW = round($totPer, 1);
                    $t = explode(".", $temp_HW);
                    $s_H = isset($t[0]) ? $t[0] * 1 : 0;
                    $s_M = isset($t[1]) ? $t[1] * 1 : 0;  // will be 0 if no decimal

                    if ($s_M <= 3) {
                        $newSM = 0;
                    }
                    if ($s_M >= 4 && $s_M <= 7) {
                        $newSM = ".5";
                    }

                    if ($s_M >= 8) {
                        $newSM = 1;
                    }

                    $sa_Hours = $s_H + $newSM;
                }

                // calculate speHol and legHol (perday_prev_hol)
                if (true) {
                    $cond = ['empId' => $empId, 'coDF1' => $df, 'coDF2' => $dt];
                    $query = $this->promo_model->select_resultArray('tk', 'perday_prev_hol', $cond);
                    if ($query) {

                        $lghol = array_sum(array_column($query, 'legHol'));
                        if (is_float($lghol)) {
                            $res = explode(".", $lghol);
                            @$dec = $res[1];
                            @$dec = substr($dec, 0, 4);
                            @$lghol = $res[0] . '.' . $dec;
                            $legHol += $lghol;
                        } else {
                            $legHol += $lghol;
                        }

                        $sphol = array_sum(array_column($query, 'speHol'));
                        if (is_float($sphol)) {
                            $res = explode(".", $sphol);
                            @$dec = $res[1];
                            @$dec = substr($dec, 0, 4);
                            @$sphol = $res[0] . '.' . $dec;
                            $special_hours += $sphol;
                        } else {
                            $special_hours += $sphol;
                        }
                    }
                }

                // update cutoff table ($co_table)
                if (true) {

                    $otPay_cur = $otPay;
                    $otPay = $otPay_Prev + $otPay;

                    echo json_encode(array(
                        'result'    => 'loading',
                        'msg'       => round($percentage) . '%',
                        'msg1'      => round($percentage) . '%',
                        'date'      => "Finalizing DTR data...",
                        'table'     => "[empId: $empId; DW: $totPer; OT: $otPay; LH: $legHol; SH: $special_hours]",
                        'exported'  => 'table_update'
                    ));

                    $data = [
                        'daysWork'          => $totPer,
                        'netOT'             => $numOT,
                        'otforpayment'      => $otPay,
                        'otforPay_cur'      => $otPay_cur,
                        'otforPay_prev'     => $otPay_Prev,
                        'VL'                => $vlNo,
                        'ML'                => $mlNo,
                        'legHol'            => $legHol,
                        'speHol'            => $special_hours,
                        'snackAllowance'    => $sa_Hours,
                        'night_diff'        => $tot_nd
                    ];
                    $cond = ($cut == 1) ? ['fCOId' => $coId, 'bioMetricId' => $bioM] : ['sCOId' => $coId, 'bioMetricId' => $bioM];
                    $update = $this->promo_model->update('tk', $co_table, $cond, $data);
                }
            }

            // transfer to the original data perday
            if (true) {
                $cond = "1 ORDER BY dutyId";
                $query = $this->promo_model->select_resultArray('tk', $perday_temp, $cond);
                foreach ($query as $key => $row) {
                    $dutyId     = $row['dutyId'];
                    $absence    = $row['absence'];
                    $utIn1      = $row['utIn1'];
                    $utIn2      = $row['utIn2'];
                    $in1_t      = $row['in1'];
                    $out1_t     = $row['out1'];

                    $cond = ['dutyId' => $dutyId];
                    $data = ['absence' => $absence, 'utIn1' => $utIn1, 'utIn2' => $utIn2];
                    $update = $this->promo_model->update('tk', 'perday', $cond, $data);

                    $cond = ['dutyId' => $dutyId];
                    $data = ['in1' => $in1_t, 'out1' => $out1_t];
                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);
                }
            }

            // DO NOT COMMENT THIS CODE
            $truncate = $this->promo_model->truncate_table($perday_temp);
            $truncate = $this->promo_model->truncate_table($duty_temp);
        }

        sleep(2);
        echo json_encode(array(
            'result'    => 'success',
            'msg'       => round(100) . '%',
            'msg1'      => round(100) . '%',
            'date'      => "Process Complete!",
            'table'     => "",
            'exported'  => 'initialize'
        ));
    }

    public function netEntery($empId, $bioM, $date, $in1, $out1, $in2, $out2, $in3, $out3)
    {
        $date = date('Y-m-d', strtotime($date));
        $shiftCode = $this->getSC($empId, $bioM, $date);

        $cond = ['shiftCode' => $shiftCode];
        $sc = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
        if ($sc) {

            $fIn    = date("H:i:s", strtotime($sc['1stIn']));
            $fOut   = date("H:i:s", strtotime($sc['1stOut']));
            $sIn    = date("H:i:s", strtotime($sc['2ndIn']));
            $sOut   = date("H:i:s", strtotime($sc['2ndOut']));

            $s_in1  = $sc['1stIn'];
            $s_out1 = $sc['1stOut'];
            $s_in2  = $sc['2ndIn'];
            $s_out2 = $sc['2ndOut'];
        }

        $cond = ['shiftCode' => $shiftCode];
        $scb = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
        if ($scb) {

            $fIn    = date("H:i:s", strtotime($scb['1stIn']));
            $fOut   = date("H:i:s", strtotime($scb['1stOut']));
            $sIn    = date("H:i:s", strtotime($scb['2ndIn']));
            $sOut   = date("H:i:s", strtotime($scb['2ndOut']));

            $s_in1  = $scb['1stIn'];
            $s_out1 = $scb['1stOut'];
            $s_in2  = $scb['2ndIn'];
            $s_out2 = $scb['2ndOut'];
        }

        $day_in1    = date('A', strtotime($s_in1));
        $day_out1   = date('A', strtotime($s_out1));
        $day_in2    = date('A', strtotime($s_in2));
        $day_out2   = date('A', strtotime($s_out2));

        if (true) {

            $cond   = ['bioMetricId' => $bioM, 'dateDuty' => $date];
            $duty   = $this->promo_model->select_rowArray('tk', 'duty', $cond);
            $dutyId = $duty['dutyId'];

            $cond   = ['dutyId' => $dutyId];
            $check  = $this->promo_model->select_rowArray('tk', 'perday', $cond);
            if (!$check) {
                $data = ['dutyId' => $dutyId];
                $insert = $this->promo_model->insert('tk', 'perday', $data);
            }

            if ($scb) {

                $temp_entries1 = false;
                $temp_entries2 = false;
                if (!$out1) {
                    $temp_entries1 = true;
                    $out1 = date('H:i:s', strtotime($s_out1));
                }
                if (!$in2) {
                    $temp_entries2 = true;
                    $in2 = date('H:i:s', strtotime($s_in2));
                }
                if (!$in1) {
                    $out1 = '';
                }
                if (!$out2) {
                    $in2 = '';
                    $out1 = '';
                }

                if (!$in1 && $out2 && $temp_entries1) {
                    $in2 = '';
                }
                if ($in1 && !$out2 && $temp_entries2) {
                    $out1 = '';
                }
            }

            $utIn1 = false;
            $lIn1 = false;
            if ($in1 <= $fIn && $in1) {
                $newIn1 = $fIn;
            } else {
                $newIn1 = $in1;
                $utIn1 = true;
                $lIn1 = true;
            }

            $utOut1 = false;
            if ($out1 >= $fOut && $out1) {
                $newOut1 = $fOut;
            } else {
                $newOut1 = $out1;
                $utOut1 = true;
            }

            $utIn2 = false;
            $lIn2 = false;
            if ($in2 <= $sIn && $in2) {
                $newIn2 = $sIn;
            } else {
                $newIn2 = $in2;
                $utIn2 = true;
                $lIn2 = true;
            }

            $utOut2 = false;
            if ($out2 >= $sOut && $out2) {
                $newOut2 = $sOut;
            } else {
                $newOut2 = $out2;
                $utOut2 = true;
            }

            if ($out2 >= "00:00:00" && $out2 < $fIn && $out2) {
                $newOut2 = $sOut;
            }

            if ($newOut2 < $newIn2) {
                $newOut2 = $newIn2;
            }

            //Calculate Late ************************************
            if (true) {
                $late       = false;
                $late1      = 0;
                $late2      = 0;
                $attempt    = 0;
                $totalLate  = 0;

                $hrId = $this->promo_model->select_rowArray('tk', 'houserules', ['offense' => 'Tardiness'])['houseRulesId'];
                $so = $this->promo_model->select_rowArray('tk', 'setup_offense', ['hrId' => $hrId]);
                if ($so['use'] == 'current') {

                    $cond = "1 ORDER BY slcId";
                    $slc = $this->promo_model->select_rowArray('tk', 'setup_late_cur', $cond);
                    $min15 = $slc['minutes'];
                }

                // 15 min
                $maxLate = date("H:i", strtotime("00:00 +$min15 minutes"));

                if ($lIn1 && $in1) {

                    $t = explode(":", $fIn);
                    $h = $t[0];
                    $m = $t[1];

                    $minLate1 = date("H:i", strtotime("$in1 -$h hours, -$m minutes"));
                    if ($minLate1 <= $maxLate) {
                        $t      = explode(":", $minLate1);
                        $m      = $t[1] * 1;
                        $late1  = $m;
                        $late   = true;

                        $attempt++;
                    }
                }

                if ($lIn2 && $in2) {

                    $t = explode(":", $sIn);
                    $h = $t[0];
                    $m = $t[1];

                    $minLate2 = date("H:i", strtotime("$in2 -$h hours, -$m minutes"));
                    if ($minLate2 <= $maxLate) {
                        $t      = explode(":", $minLate2);
                        $m      = $t[1] * 1;
                        $late2  = $m;
                        $late   = true;

                        $attempt++;
                    }
                }

                //insert late violation
                if ($late) {
                    $cond = ['houseRulesId' => $hrId, 'dutyId' => $dutyId];
                    $check = $this->promo_model->select_rowArray('tk', 'violation', $cond);
                    if (!$check) {
                        $data = ['houseRulesId' => $hrId, 'dutyId' => $dutyId, 'attempt' => $attempt];
                        $insert = $this->promo_model->insert('tk', 'violation', $data);
                    }
                }

                $totalLate = $late1 + $late2;

                $cond = ['dutyId' => $dutyId];
                $data = ['late1' => $late1, 'late2' => $late2, 'totalLate' => $totalLate];
                $update = $this->promo_model->update('tk', 'perday', $cond, $data);
            }
            //Calculate Late ************************************

            //Calculate undertime ************************************
            if (true) {
                $timeIn1 = '00:00';
                if ($utIn1 && $in1 && !$late1) {
                    $t = explode(":", $fIn);
                    $h = $t[0];
                    $m = $t[1];

                    $timeIn1 = date("H:i", strtotime("$in1 -$h hours, -$m minutes"));
                }

                $timeOut1 = '00:00';
                if ($utOut1 && $out1) {
                    $t = explode(":", $out1);
                    $h = $t[0];
                    $m = $t[1];

                    $timeOut1 = date("H:i", strtotime("$fOut -$h hours, -$m minutes"));
                }

                $timeIn2 = '00:00';
                if ($utIn2 && $in2 && !$late2) {
                    $t = explode(":", $sIn);
                    $h = $t[0];
                    $m = $t[1];

                    $timeIn2 = date("H:i", strtotime("$in2 -$h hours, -$m minutes"));
                }

                $timeOut2 = '00:00';
                if ($utOut2 && $out2) {
                    $t = explode(":", $out2);
                    $h = $t[0];
                    $m = $t[1];

                    $timeOut2 = date("H:i", strtotime("$sOut -$h hours, -$m minutes"));
                }

                $t  = explode(":", $timeOut1);
                $h  = $t[0];
                $m  = $t[1];
                $uT = date("H:i", strtotime("$timeOut2 +$h hours, +$m minutes"));

                $cond = ['dutyId' => $dutyId];
                $data = [
                    'utIn1'     => $timeIn1,
                    'utOut1'    => $timeOut1,
                    'utIn2'     => $timeIn2,
                    'utOut2'    => $timeOut2,
                    'uT'        => $uT
                ];
                $update = $this->promo_model->update('tk', 'perday', $cond, $data);
            }
            //Calculate undertime ************************************

            //Calculate hw *******************************************************
            if (true) {
                $hw = 0;

                //fullday
                if ($in1 && $out1 && $in2 && $out2) {
                    $t  = explode(":", $newIn1);
                    $h  = $t[0];
                    $m  = $t[1];
                    $am = date("H:i", strtotime("$newOut1 -$h hours, -$m minutes"));

                    $t  = explode(":", $newIn2);
                    $h  = $t[0];
                    $m  = $t[1];
                    $pm = date("H:i", strtotime("$newOut2 -$h hours, -$m minutes"));

                    list($h1, $m1) = explode(":", $am);
                    list($h2, $m2) = explode(":", $pm);

                    $tm = ($h1 * 60 + $m1) + ($h2 * 60 + $m2);
                    $hh = floor($tm / 60);
                    $mm = $tm % 60;

                    $hm = sprintf("%02d:%02d", $hh, $mm);

                    $t  = explode(":", $hm);
                    $h  = $t[0] * 1;
                    $m  = $t[1] * 1;
                    $hw = ($m != 0) ? "$h hr(s). & $m min(s)." : "$h hr(s).";
                }



                //halfday AM
                if ($in1 && $out1 && !($in2 && $out2)) {

                    $t  = explode(":", $newIn1);
                    $h  = $t[0];
                    $m  = $t[1];
                    $am = date("H:i", strtotime("$newOut1 -$h hours, -$m minutes"));

                    $t  = explode(":", $am);
                    $h  = $t[0] * 1;
                    $m  = $t[1] * 1;
                    $hw = ($m != 0) ? "$h hr(s). & $m min(s)." : "$h hr(s).";
                }

                //halfday PM
                if (!($in1 && $out1) && $in2 && $out2) {
                    $t  = explode(":", $newIn2);
                    $h  = $t[0];
                    $m  = $t[1];
                    $pm = date("H:i", strtotime("$newOut2 -$h hours, -$m minutes"));

                    $t  = explode(":", $pm);
                    $h  = $t[0] * 1;
                    $m  = $t[1] * 1;
                    $hw = ($m != 0) ? "$h hr(s). & $m min(s)." : "$h hr(s).";
                }

                //fullday (same hour for in2 and out2)
                $in2_h  = date("H", strtotime($in2));
                $out2_h = date("H", strtotime($out2));
                if ($in1 && $out1 && $in2 && $out2 && $in2_h == $out2_h) {

                    $t = explode(":", $newIn1);
                    $h = isset($t[0]) ? $t[0] * 1 : 0;
                    $m = isset($t[1]) ? $t[1] * 1 : 0;

                    $hm = date("H:i", strtotime("$newOut1 -$h hours, -$m minutes"));
                    $t  = explode(":", $hm);
                    $h = isset($t[0]) ? $t[0] * 1 : 0;
                    $m = isset($t[1]) ? $t[1] * 1 : 0;

                    $hw = ($m != 0) ? "$h hr(s). & $m min(s)." : "$h hr(s).";
                }

                //if more than 8 hours
                if ($hw) {
                    $res = explode("&", $hw);
                    @$t_HH = $res[0] * 1;
                    @$t_MM = round((($res[1] * 1) / 60), 3);
                    $temp_HHMM = $t_HH + $t_MM;

                    if ($temp_HHMM > 8) {
                        $hw = 0;
                    }
                }

                $cond = ['dutyId' => $dutyId];
                $data = ['hoursWork' => $hw];
                $update = $this->promo_model->update('tk', 'perday', $cond, $data);
            }
            //Calculate hw *******************************************************

            //Calculate ot *******************************************************
            if (true) {

                $cond   = "1 ORDER BY socId";
                $sotc   = $this->promo_model->select_rowArray('tk', 'setup_ot_cur', $cond);
                $hour1  = $sotc['minimum']; // 1 hour

                //ot1
                $ot1 = '';
                $hi_ot1 = "00:00";
                if ($in1) {

                    $sub1 = date("H:i", strtotime("$fIn -$hour1"));

                    $t = explode(":", $in1);
                    $h = $t[0] * 1;
                    $m = $t[1] * 1;

                    if ($m > 30) {
                        $ot_round = date("H:i", strtotime("$h:00 +1 hour"));
                    } elseif ($m <= 30 && $m > 0) {
                        $ot_round = date("H:i", strtotime("$h:00 +30 minutes"));
                    } else {
                        $ot_round = date("H:i", strtotime("$h:00"));
                    }

                    if (strtotime($ot_round) <= strtotime($sub1)) {

                        $t = explode(":", $ot_round);
                        $h = $t[0] * 1;
                        $m = $t[1] * 1;
                        $hi_ot1 = date("H:i", strtotime("$fIn -$h hours , -$m minutes"));

                        $t = explode(":", $hi_ot1);
                        $h = $t[0] * 1;
                        $m = $t[1] * 1;
                        $ot1 = ($m != 0) ? "$h hr(s). & $m min(s)." : "$h hr(s).";
                    }
                }

                //ot2
                $ot2 = '';
                $hi_ot2 = "00:00";
                if ($out2) {

                    if ($day_in1 == "PM" && $day_out2 == "AM") {

                        $tom = date("Y-m-d", strtotime("$date +1 day"));
                        $add = date("Y-m-d H:i:s", strtotime("$tom $sOut +$hour1"));

                        $t = explode(":", $out2);
                        $h = $t[0] * 1;
                        $m = $t[1] * 1;

                        if ($m >= 30) {
                            $ot_round = date("H:i", strtotime("$h:00 +30 minutes"));
                        } else {
                            $ot_round = date("H:i", strtotime("$h:00"));
                        }

                        $date_out = date("Y-m-d H:i", strtotime("$date $ot_round"));

                        if (strtotime($add) <= strtotime($date_out)) {
                            $t = explode(":", $sOut);
                            $h = $t[0] * 1;
                            $m = $t[1] * 1;
                            $hi_ot2 = date("H:i", strtotime("$ot_round -$h hours , -$m minutes"));
                        }
                    } else {

                        $add1 = date("H:i", strtotime("$sOut +$hour1"));

                        $t = explode(":", $out2);
                        $h = $t[0] * 1;
                        $m = $t[1] * 1;

                        if ($m >= 30) {
                            $ot_round = date("H:i", strtotime("$h:00 +30 minutes"));
                        } else {
                            $ot_round = date("H:i", strtotime("$h:00"));
                        }

                        if (strtotime($add1) <= strtotime($ot_round)) {

                            $t = explode(":", $sOut);
                            $h = $t[0] * 1;
                            $m = $t[1] * 1;
                            $hi_ot2 = date("H:i", strtotime("$ot_round -$h hours , -$m minutes"));
                        }

                        //if out2 min is 15 or 45
                        if ((strpos($sOut, ':15') !== false) || (strpos($sOut, ':45') !== false)) {
                            if ($out2 >= $add1 && $out2) {
                                $t = explode(":", $out2);
                                $h = $t[0] * 1;
                                $m = $t[1] * 1;

                                if ($m >= 45) {
                                    $ot_round1 = date("H:i", strtotime("$h:00 +45 minutes"));
                                } else {
                                    $ot_round1 = date("H:i", strtotime("$h:00 +15 minutes"));
                                }

                                $t = explode(":", $sOut);
                                $h = $t[0] * 1;
                                $m = $t[1] * 1;
                                $hi_ot2 = date("H:i", strtotime("$ot_round1 -$h hours , -$m minutes"));
                            }
                        }

                        //if ot is tomorrow
                        if (date("A", strtotime($ot_round)) == "AM") {
                            $t = explode(":", $sOut);
                            $h = $t[0] * 1;
                            $m = $t[1] * 1;
                            $hi_ot2 = date("H:i", strtotime("$ot_round -$h hours , -$m minutes"));
                        }
                    }

                    $t = explode(":", $hi_ot2);
                    $h = $t[0] * 1;
                    $m = $t[1] * 1;
                    $ot2 = ($m != 0) ? "$h hr(s). & $m min(s)." : "$h hr(s).";
                }

                //ot3
                $ot3 = '';
                $hi_ot3 = "00:00";
                if ($in3 && $out3) {

                    $t = explode(":", $in3);
                    $h = $t[0] * 1;
                    $m = $t[1] * 1;

                    if ($m > 30) {
                        $ot_round = date("H:i", strtotime("$h:00 +1 hour"));
                    } elseif ($m <= 30 && $m > 0) {
                        $ot_round = date("H:i", strtotime("$h:00 +30 minutes"));
                    } else {
                        $ot_round = date("H:i", strtotime("$h:00"));
                    }

                    $t = explode(":", $out3);
                    $h = $t[0] * 1;
                    $m = $t[1] * 1;

                    if ($m >= 30) {
                        $ot_round1 = date("H:i", strtotime("$h:00 +30 minutes"));
                    } else {
                        $ot_round1 = date("H:i", strtotime("$h:00"));
                    }

                    $t = explode(":", $ot_round);
                    $h = $t[0] * 1;
                    $m = $t[1] * 1;
                    $hi_ot3 = date("H:i", strtotime("$ot_round1 -$h hours , -$m minutes"));

                    $t = explode(":", $hi_ot3);
                    $h = $t[0] * 1;
                    $m = $t[1] * 1;
                    $ot3 = ($m != 0) ? "$h hr(s). & $m min(s)." : "$h hr(s).";
                }

                //ot
                $ot = '';
                if ($hi_ot1 != "00:00" || $hi_ot2 != "00:00" || $hi_ot3 != "00:00") {

                    //caculate the ot1 + ot2 overtime
                    $t = explode(":", $hi_ot1);
                    $h = isset($t[0]) ? $t[0] * 1 : 0;
                    $m = isset($t[1]) ? $t[1] * 1 : 0;

                    $ot12 = date("H:i", strtotime("$hi_ot2 +$h hours, +$m minutes"));

                    //caculate the ot12 + ot3 overtime
                    $t  = explode(":", $ot12);
                    $h = isset($t[0]) ? $t[0] * 1 : 0;
                    $m = isset($t[1]) ? $t[1] * 1 : 0;

                    $ot123 = date('H:i', strtotime("$hi_ot3 +$h hours, +$m minutes"));
                    $t  = explode(":", $ot123);
                    $h = isset($t[0]) ? $t[0] * 1 : 0;
                    $m = isset($t[1]) ? $t[1] * 1 : 0;

                    $ot = ($m != 0) ? "$h hr(s). & $m min(s)." : "$h hr(s).";
                }

                $cond = ['dutyId' => $dutyId];
                $data = ['ot1' => $ot1, 'ot2' => $ot2, 'ot3' => $ot3, 'oT' => $ot];
                $update = $this->promo_model->update('tk', 'perday', $cond, $data);
            }
            //Calculate ot *******************************************************

            //inserting list_ot *******************************************************
            if (true) {

                $cond = ['empId' => $empId, 'dateOvertime' => $date];
                $delete = $this->promo_model->delete('tk', 'list_ot', $cond);

                if ($ot1 != 0 || $ot2 != 0 || $ot3 != 0) {

                    $cond   = "1 ORDER BY spoId";
                    $spo    = $this->promo_model->select_rowArray('tk', 'setup_park_ot', $cond);
                    $month3 = $spo['expiration']; // 3 months

                    $dateExp = date('Y-m-d', strtotime("$date +$month3"));

                    $cond   = ['empId' => $empId, 'dateOvertime' => $date];
                    $check  = $this->promo_model->select_rowArray('tk', 'list_ot', $cond);
                    if (!$check) {
                        $data = ['empId' => $empId, 'dateOvertime' => $date, 'dateExp' => $dateExp, 'status' => 'AVAILABLE'];
                        $insert = $this->promo_model->insert('tk', 'list_ot', $data);
                    }
                }

                if ($ot1 != 0) {
                    $cond = ['empId' => $empId, 'dateOvertime' => $date];
                    $data = [
                        'tStarted1' => $in1,
                        'tEnded1'   => $fIn,
                        'otLength1' => $ot1,
                        'totalOT'   => $ot,
                        'otLeft'    => $ot
                    ];

                    $update = $this->promo_model->update('tk', 'list_ot', $cond, $data);
                }

                if ($ot2 != 0) {
                    $cond = ['empId' => $empId, 'dateOvertime' => $date];
                    $data = [
                        'tStarted2' => $sOut,
                        'tEnded2'   => $out2,
                        'otLength2' => $ot2,
                        'totalOT'   => $ot,
                        'otLeft'    => $ot
                    ];

                    $update = $this->promo_model->update('tk', 'list_ot', $cond, $data);
                }

                if ($ot3 != 0) {
                    $cond = ['empId' => $empId, 'dateOvertime' => $date];
                    $data = [
                        'tStarted3' => $in3,
                        'tEnded3'   => $out3,
                        'otLength3' => $ot3,
                        'totalOT'   => $ot,
                        'otLeft'    => $ot
                    ];

                    $update = $this->promo_model->update('tk', 'list_ot', $cond, $data);
                }
            }
            //inserting list_ot *******************************************************

            //Calculate absence *******************************************************
            if (true) {
                $absence = 0;

                // halfday AM
                if (!$in1 && !$out1 && $in2 && $out2) {
                    $absence = .5;
                }

                // halfday PM
                if ($in1 && $out1 && !$in2 && !$out2) {
                    $absence = .5;
                }

                // wholeday
                if (!$in1 && !$out1 && !$in2 && !$out2) {
                    $absence = 1;
                }

                $filled = 0;
                $logbox = [$in1, $out1, $in2, $out2];
                foreach ($logbox as $key => $v) {
                    if (!empty($v)) {
                        $filled++;
                    }
                }

                if ($filled === 1) {
                    $absence = 1;
                }

                //updating halfday in shiftcode break  					
                if ($scb) {

                    $sIn_add = date('H:i:s', strtotime("$sIn +59 minutes, +59 second"));
                    $fOut_sub = date('H:i:s', strtotime("$fOut -30 minutes"));

                    //10:59am && 3pm <= 3:01pm && 3:01pm <= 3:59pm
                    if ($in1 && $sIn <= $out2 && $out2 <= $sIn_add) {
                        $absence = .5;
                        $cond = ['dutyId' => $dutyId];
                        $data = ['utOut2' => '00:00'];
                        $update = $this->promo_model->update('tk', 'perday', $cond, $data);
                    }

                    //3pm <= 7:01pm && 2:30pm < 02:59pm && 02:59pm <= 3pm
                    if ($sIn <= $out2 && $fOut_sub < $in1 && $in1 <= $fOut) {
                        $absence = .5;
                        $cond = ['dutyId' => $dutyId];
                        $data = ['utIn1' => '00:00', 'utOut1' => '00:00', 'utOut2' => '00:00'];
                        $update = $this->promo_model->update('tk', 'perday', $cond, $data);
                    }

                    if ($day_out2 == "AM") {

                        if ($fOut_sub < $in1 && $in1 <= $fOut) {
                            $absence = .5;
                            $cond = ['dutyId' => $dutyId];
                            $data = ['utIn1' => '00:00', 'utOut1' => '00:00'];
                            $update = $this->promo_model->update('tk', 'perday', $cond, $data);
                        }
                    }
                }

                $cond = ['dutyId' => $dutyId];
                $data = ['absence' => $absence];
                $update = $this->promo_model->update('tk', 'perday', $cond, $data);
            }
            //Calculate absence *******************************************************

            //change the status from Pending to Done - already calculated *******************************************************
            $data = ['status' => 'Done'];
            $cond = ['empId' => $empId, 'date' => $date];
            $update = $this->promo_model->update('tk', 'assign_mul_sc_exp', $cond, $data);
            //change the status from Pending to Done - already calculated *******************************************************
        }
    }

    public function insert_prev_duty($bioM, $date, $req_type, $data)
    {
        $cond = ['bioMetricId' => $bioM, 'dateRendered' => $date, 'req_type' => $req_type];
        $prev = $this->promo_model->select_resultArray('tk', 'promo_prev_duty', $cond);
        if (!$prev) {

            $data['bioMetricId']    = $bioM;
            $data['dateRendered']   = $date;
            $data['req_type']       = $req_type;

            $insert = $this->promo_model->insert('tk', 'promo_prev_duty', $data);
        } else {

            $update = $this->promo_model->update('tk', 'promo_prev_duty', $cond, $data);
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
}
