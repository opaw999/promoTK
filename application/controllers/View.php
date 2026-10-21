<?php
defined('BASEPATH') or exit('No direct script access allowed');

class View extends CI_Controller
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

    public function view_searchPromo()
    {
        echo '<ul class="list-group">';
        $input  = $this->input->post(NULL, TRUE);
        $string = $input['string'];
        $query  = $this->promo_model->view_searchPromo($string);
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

    public function masterfileDetails()
    {
        $input      = $this->input->post(NULL, TRUE);
        $empId      = $input['empId'];
        $recordNo   = $input['recordNo'];
        $statCut    = $input['statCut'];
        $bioM       = $input['bioM'];

        $app    = $this->promo_model->select_rowArray('pis', 'applicant', array('app_id' => $empId));
        $photo  = explode("../", $app['photo']);
        @$link  = 'http://' . $_SERVER['SERVER_ADDR'] . ':80' . $_SERVER['SERVER_PORT'] . '/hrms/' . $photo[1];

        $cond       = ['e.emp_id' => $empId, 'e.record_no' => $recordNo];
        $emp        = $this->promo_model->promoDetailsRow($cond);
        $recordNo   = $emp['record_no'];
        $name       = ucwords(strtolower($emp['name']));
        $promoType  = $emp['promo_type'];
        $company    = $emp['promo_company'];
        $department = $emp['promo_department'];
        $position   = $emp['position'];

        if ($emp['current_status'] == 'Active') {
            $status = '<span class="badge bg-success">Active</span>';
        } else if ($emp['current_status'] == 'blacklisted') {
            $status = '<span class="badge bg-danger">Blacklisted</span>';
        } else {
            $status = '<span class="badge bg-warning">' . $emp['current_status'] . '</span>';
        }

        $i = 0;
        $stores = '';
        $bus = $this->promo_model->locate_promo_bu($this->hrCode);
        foreach ($bus as $bu) {
            $field = $bu['bunit_field'];
            if ($emp[$field] == 'T') {

                $i++;
                $store = '<span class="badge bg-secondary">' . $bu['bunit_acronym'] . '</span>';
                $stores = ($i == 1) ? $store : $stores . ' ' . $store;
            }
        }

        $cond = "empId='$empId' ORDER BY assignId DESC LIMIT 1";
        $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
        $allowId = $assign['allowId'];

        $bioM = '';
        if ($allowId == 'Biometric ID') {
            $bioM = $assign['bioMetricId'];
        }

        $barcode = '';
        if ($allowId == 'Logbox ID') {
            $barcode = $assign['barcodeId'];
        }

        $del = '';
        $pId = '';
        $baseId = '';
        $check = $this->promo_model->select_rowArray('tk', 'promo_id', ['empId' => $empId]);
        if ($check) {
            // $baseId = '<span class="badge bg-info ml-1">' . $check['baseId'] . '</span>';
            // $bioM   = $check['biometricId'];
            // $pId    = $check['pId'];
            // $del = '<a href="javascript:;" onclick="delpromoId(\'' . $pId . '\',\'delete\')"><i class="fa fa-trash text-danger"></i></a>';

            $query = $this->promo_model->select_resultArray('tk', 'promo_id', ['empId' => $empId]);
            foreach ($query as $key => $row) {
                $pId    = $row['pId'];
                $bioM   = $row['biometricId'];
                $del = '<a href="javascript:;" onclick="delpromoId(\'' . $pId . '\',\'' . $row['baseId'] . '\',\'delete\')"><i class="fa fa-trash text-danger mr-1"></i></a>';
                $baseId .= $bioM . ' <span class="badge bg-info ml-1 mr-1">' . $row['baseId'] . '</span>' . $del;
            }
        }
        $add = '<a href="javascript:;" onclick="addpromoId(\'' . $empId . '\',\'add\')"><i class="fa fa-plus text-success"></i></a>';


        $dayoff = $this->promo_model->select_rowArray('tk', 'assign_day_off', ['empId' => $empId])['day'];
        if (!$dayoff) {
            $dayoff = 'No Dayoff';
        }

        $month  = date('m');
        $year   = date('Y');
        $load   = "$empId|$recordNo|$promoType|$month|$year";

        echo '<div class="row mt-3">';
        echo    '<div class="col-3 d-flex flex-column align-items-center text-center">
                    <img src="' . $link . '" alt="img" class="img-thumbnail" width="100" height="100">
                    <span><b>' . $empId . '</b></span>
                    ' . $status . '
                </div>';
        echo    '<div class="col-4">
                    <h2 class="lead mb-0"><b>' . $name . '</b></h2>
                    <p class="text-muted text-sm mt-0"> ' . $position . ' </p>
                    <ul class="ml-4 mb-0 fa-ul text-muted">
                        <li class="small"><span class="fa-li"><i class="fas fa-lg fa-store"></i> </span> ' . $stores . '</li>
                        <li class="small"><span class="fa-li"><i class="fas fa-lg fa-building"></i> </span> ' . $company . '</li>
                        <li class="small"><span class="fa-li"><i class="fas fa-shopping-cart fa-lg"></i> </span> ' . $department . '</li>
                    </ul>
                </div>';
        echo    '<div class="col-5">
                    <table class="">
                        <tr>
                            <th>Dayoff</th>
                            <th>:</th>
                            <td>' . $dayoff . '</td>
                        </tr>
                        <tr>
                            <th>Default ID</th>
                            <th>:</th>
                            <td>' . $allowId . '</td>
                        </tr>
                        <tr>
                            <th>Logbox ID</th>
                            <th>:</th>
                            <td>' . $barcode . '</td>
                        </tr>
                        <tr>
                            <th>Biometric ID</th>
                            <th>:</th>
                            <td>' . $baseId . ' (' . $add . ')</td>
                        </tr>
                    </table>
                    <div class="mt-3">
                       <a href="javascript:;" onclick="defSched(\'' . $empId . '\')" class="btn btn-sm btn-primary"> Default Schedule</a>
                       <a href="javascript:;" onclick="schedule_pager(\'' . $load . '\')" class="btn btn-sm btn-primary"> View Schedule</a>
                    </div>';
        echo    '</div>';
        echo '</div>';
    }

    public function delpromoId()
    {
        $input = $this->input->post(NULL, TRUE);
        $pId = $input['pId'];

        $cond = ['pId' => $pId];
        $delete = $this->promo_model->delete('tk', 'promo_id', $cond);
        if ($delete) {
            echo json_encode(['message' => 'success', 'feedback' => 'Biometric ID has been deleted.']);
        } else {
            echo json_encode(['message' => 'failed']);
        }
    }

    public function addpromoId()
    {
        $input  = $this->input->post(NULL, TRUE);
        $empId  = $input['empId'];

        $store = '<select class="form-control" name="store">
                    <option value="">---</option>';
        $cond = "status='active' ORDER BY storeCode ASC";
        $query = $this->promo_model->select_resultArray('tk', 'promo_store_start', $cond);
        foreach ($query as $key => $row) {
            $store .= '<option value="' . $row['storeCode'] . '">' . $row['storeCode'] . '</option>';
        }
        $store .= '</select>';

        echo '<table class="table table-sm table-bordered">
                <tr>
                    <th>Store</th>
                    <th>Biometric ID</th>
                    <th class="text-center">Action</th>
                </tr>
                <tr>
                    <td>' . $store . '</td>
                    <td><input type="text" class="form-control" minlength="11" maxlength="11" name="biometricId" placeholder="00000000000" autocomplete="off"></td>
                    <td class="text-center"><button type="button" class="btn btn-primary" onclick="submitpromoId(\'' . $empId . '\')">Submit</button></td>
                </tr>';
        echo '</table>';
    }

    public function submitpromoId()
    {
        $input = $this->input->post(NULL, TRUE);
        $empId = $input['empId'];
        $store = $input['store'];
        $biometricId = $input['id'];

        $cond = ['empId' => $empId];
        $barcodeId = $this->promo_model->select_rowArray('tk', 'assign', $cond)['barcodeId'];

        $cond   = ['biometricId' => $biometricId, 'empId' => $empId, 'baseId' => $store];
        $check  = $this->promo_model->select_rowArray('tk', 'promo_id', $cond);
        if ($check) {
            echo json_encode(['message' => 'exists', 'feedback' => 'Biometric ID or Store already exists.']);
        } else {
            $data = [
                'empId'         => $empId,
                'barcodeId'     => $barcodeId,
                'biometricId'   => $biometricId,
                'baseId'        => $store,
                'status'        => 'active'
            ];
            $insert = $this->promo_model->insert('tk', 'promo_id', $data);
            if ($insert) {
                echo json_encode(['message' => 'success', 'feedback' => 'Biometric ID has been added.']);
            } else {
                echo json_encode(['message' => 'failed']);
            }
        }
    }

    public function multiSched()
    {
        $input  = $this->input->post(NULL, TRUE);
        $empId  = $input['empId'];

        $data   = [];
        $cond   = "empId ='$empId' ORDER BY dateFrom DESC";
        $query  = $this->promo_model->select_resultArray('tk', 'assign_mul_sc', $cond);
        foreach ($query as $c) {
            $amsId  = $c['amsId'];
            $mSC    = $c['shiftCode'];
            $dF     = $c['dateFrom'];
            $dT     = $c['dateTo'];
            $status = $c['status'];
            $dFrom  = date('Y/m/d', strtotime($dF));
            $dTo    = date('Y/m/d', strtotime($dT));

            $e      = $this->promo_model->select_rowArray('tk', 'shiftcodes', ['shiftCode' => $mSC]);
            $in1    = $e['1stIn'];
            $out1   = $e['1stOut'];
            $in2    = $e['2ndIn'];
            $out2   = $e['2ndOut'];

            $sched =  $in1 . ' - ' . $out1 . ' | ' . $in2 . ' - ' . $out2;
            if (!$in2) {
                $sched =  $in1 . ' - ' . $out1;
            }

            $reqStat = '';
            $dateReq = '';

            $cond = "amsId ='$amsId' LIMIT 1";
            $odaf = $this->promo_model->select_rowArray('tk', 'request_odaf', $cond);
            if ($odaf) {
                $dateReq = $odaf['dateReq'];
                $timeReq = $odaf['timeReq'];
                $dateReq = date("Y/m/d g:i A", strtotime("$dateReq $timeReq"));
                $reqStat = "<b>O.D.A.</b>";
            }

            $action = '<i class="fa fa-folder-open text-secondary"></i>';
            $cond   = "amsId ='$amsId' LIMIT 1";
            $rns    = $this->promo_model->select_rowArray('tk', 'request_new_shift', $cond);
            if ($rns) {
                $dateReq    = $rns['dateReq'];
                $timeReq    = $rns['timeReq'];
                $dateReq    = date("Y/m/d g:i A", strtotime("$dateReq $timeReq"));
                $reqStat    = "<b>C.S.</b>";
                $action     = '<a href="javascript:;" onclick="viewDO(\'' . $empId . '\',\'' . $amsId . '\',\'' . $dF . '\',\'' . $dT . '\')" title="View Dayoff"><i class="fa fa-folder-open text-primary"></i></a>';
            }
            if ($c['status'] == 'active') {
                $status = '<span class="text-success">' . $c['status'] . '</span>';
            } else {
                $status = '<span class="text-warning">' . $c['status'] . '</span>';
            }

            $col    = [];
            $col[]  = $dFrom . ' - ' . $dTo;
            $col[]  = $mSC;
            $col[]  = $sched;
            $col[]  = $status;
            $col[]  = $reqStat;
            $col[]  = $dateReq;
            $col[]  = $action;
            $data[] = $col;
        }

        echo json_encode(['data' => $data]);
    }

    public function defSched()
    {
        $input  = $this->input->post(NULL, TRUE);
        $empId  = $input['empId'];

        $data   = [];
        $query  = $this->promo_model->select_resultArray('tk', 'promo_def_sched', ['empId' => $empId]);
        foreach ($query as $row) {

            $day    = $row['day_sched'];
            $sc     = $row['shiftCode'];
            $storea = $row['store1'];
            $storeb = $row['store2'];
            $table  = 'locate_business_unit';

            $s_a = '';
            if ($storea != '') {
                $s      = explode(',', $storea);
                $cond   = array('company_code' => $s[0], 'bunit_code' => $s[1]);
                $s_a    = $this->promo_model->select_rowArray('pis', $table, $cond)['business_unit'];
            }

            $s_b = '';
            if ($storeb != '') {
                $s      = explode(',', $storeb);
                $cond   = array('company_code' => $s[0], 'bunit_code' => $s[1]);
                $s_b    = $this->promo_model->select_rowArray('pis', $table, $cond)['business_unit'];
            }

            $sql    = $this->promo_model->select_rowArray('tk', 'shiftcodes', ['shiftCode' => $sc]);
            $in1    = $sql['1stIn'];
            $out1   = $sql['1stOut'];
            $in2    = $sql['2ndIn'];
            $out2   = $sql['2ndOut'];

            $sched = "$in1 - $out1 | $in2 - $out2";
            if (!$in2) {
                $sched = "$in1 - $out1";
            }

            $sub_array = [];
            $sub_array[] = $day;
            $sub_array[] = $sc;
            $sub_array[] = $sched;
            $sub_array[] = $s_a;
            $sub_array[] = $s_b;
            $data[] = $sub_array;
        }

        echo json_encode(['data' => $data]);
    }

    public function viewDO()
    {
        $input  = $this->input->post(NULL, TRUE);
        $empId  = $input['empId'];
        $amsId  = $input['amsId'];
        $df     = $input['df'];
        $dt     = $input['dt'];
        $cond   = "empId='$empId' AND amsId='$amsId' ORDER BY date DESC";
        $query  = $this->promo_model->select_resultArray('tk', 'ass_mul_do', $cond);
        if ($query) {
            echo '<table class="table table-sm table-hover table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                        </tr>
                    </thead>';
            foreach ($query as  $row) {
                $dayOff = $row['dayOff'];
                $date   = date("M. d, Y", strtotime($row['date']));
                echo '<tr>
                        <td>' . $date . '</td>
                        <td>' . $dayOff . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<h5 class="text-center text-danger">No data found!</h5>';
        }
    }

    public function schedule_pager()
    {
        $input      = $this->input->post(NULL, TRUE);
        $empId      = $input['empId'];
        $recordNo   = $input['recordNo'];
        $promoType  = $input['promoType'];
        $month      = $input['month'];
        $year       = $input['year'];

        $data           = "$empId|$recordNo|$promoType";
        $future_year    = ($year * 1) + 3;
        $previous_year  = ($year * 1) - 5;

        $temp_date  = date('Y-m-d', strtotime("$year-$month-01"));

        $back_month = date('m', strtotime($temp_date . '-1 month'));
        $back_year  = date('Y', strtotime($temp_date . '-1 month'));

        $add_month  = date('m', strtotime($temp_date . '+1 month'));
        $add_year   = date('Y', strtotime($temp_date . '+1 month'));

        $prev = "$empId|$recordNo|$promoType|$back_month|$back_year";
        $next = "$empId|$recordNo|$promoType|$add_month|$add_year";

        echo '<div class="d-flex justify-content-center align-items-center mb-3">';
        echo '<div class="col-md-3 text-right">
                <a href="javascript:;" onclick="schedule_pager(\'' . $prev . '\')">
                    <i class="fa fa-backward"></i>
                </a>';
        echo '</div>';
        echo '<div class="col-md-3">
                <select name="month_" class="form-control" onchange="month_n(\'' . $data . '\')">';
        for ($x = 1; $x <= 12; $x++) {
            $sel = ($month == $x) ? 'selected' : '';
            echo '<option value="' . $x . '" ' . $sel . '>' . date('F', strtotime("$year-$x-01")) . '</option>';
        }
        echo    '</select>';
        echo '</div>';
        echo '<div class="col-md-3">
                <select name="year_" class="form-control" onchange="year_n(\'' . $data . '\')">';
        for ($y = $future_year; $previous_year <= $y; $y--) {
            $sel = ($y == $year) ? 'selected' : '';
            echo '<option value="' . $y . '" ' . $sel . '>' . $y . '</option>';
        }
        echo    '</select>';
        echo '</div>';
        echo '<div class="col-md-3">
                <a href="javascript:;" onclick="schedule_pager(\'' . $next . '\')">
                    <i class="fa fa-forward"></i>
                </a>';
        echo '</div>';
        echo '</div>';
        echo '<div class="row">';
        echo '<div class="col-md-12">
                <div class="viewSchedPromo"></div>';
        echo '</div>';
        echo '</div>';
    }

    public function viewSched()
    {
        $input      = $this->input->post(NULL, TRUE);
        $empId      = $input['empId'];
        $recordNo   = $input['recordNo'];
        $promoType  = $input['promoType'];
        $month      = $input['month'];
        $year       = $input['year'];

        $last = date('t', strtotime("$year-$month-01"));
        echo '<table id="dt_schedule" class="table table-sm table-hover table-bordered">
                <tr>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Current Schedule</th>
                    <th>Store(s)</th>
                    <th class="text-center">Request(s)</th>
                </tr>';
        $i = 0;
        $stores = '';
        $bus = $this->promo_model->locate_promo_bu($this->hrCode);
        foreach ($bus as $bu) {
            $field = $bu['bunit_field'];
            $cond = ['emp_id' => $empId, 'record_no' => $recordNo, $field => 'T'];
            $check = $this->promo_model->select_rowArray('pis', 'promo_record', $cond);
            if ($check) {
                $i++;
                $stores = ($i == 1) ? $bu['bunit_name'] : $stores . ', ' . $bu['bunit_name'];
            }
        }

        if ($promoType == 'STATION') {

            for ($i = 1; $i <= $last; $i++) {

                $wholeDate  = date('M. d, Y', strtotime("$year-$month-$i"));
                $n_day      = date('Y-m-d', strtotime("$year-$month-$i"));
                $dayS       = date('l', strtotime($n_day));
                $schedule   = '';
                $storeSched = $stores;
                $name       = '';
                $request    = '';

                $cond  = "empId='$empId' AND '$n_day' BETWEEN dateFrom AND dateTo ORDER BY id DESC LIMIT 1";
                $co = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond);
                if ($co) {
                    $shiftCode = $co['shiftCode'];
                    $amsId = $co['amsId'];

                    $cond = "empId='$empId' AND amsId='$amsId' AND date='$n_day'";
                    $no_sched = $this->promo_model->select_rowArray('tk', 'promo_nosched', $cond);

                    $cond = "empId='$empId' AND amsId='$amsId' AND date='$n_day'";
                    $dayoff = $this->promo_model->select_rowArray('tk', 'ass_mul_do', $cond);

                    if ($dayoff) {
                        $schedule = '<font color="red">DAY-OFF</font>';
                    } else if ($no_sched) {
                        $schedule = '<font color="red">NO SCHEDULE</font>';
                    } else {

                        $sched = '';
                        $sc = $this->promo_model->select_rowArray('tk', 'shiftcodes', ['shiftCode' => $shiftCode]);
                        if ($sc) {
                            $fIn    = $sc['1stIn'];
                            $fOut   = $sc['1stOut'];
                            $sIn    = $sc['2ndIn'];
                            $sOut   = $sc['2ndOut'];
                            $sched = "<span style='font-size: smaller;'>$fIn - $fOut</span>";
                            if ($sIn && $sOut) {
                                $sched = "<span style='font-size: smaller;'>$fIn - $fOut | $sIn - $sOut</span>";
                            }
                        }
                        $sq = $this->promo_model->select_rowArray('tk', 'request_new_shift', ['amsId' => $amsId]);
                        if ($sq) {
                            $dateReq = date("Y/m/d", strtotime($sq['dateReq']));
                            $timeReq = date("h:i A", strtotime($sq['timeReq']));
                            $name = $this->promo_model->select_rowArray('pis', 'employee3', ['emp_id' => $sq['reqBy']])['name'];
                            $schedule = "[ <span data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ]'><font color = 'blue'>$shiftCode</font> = $sched</span> ]";
                        }

                        $sq = $this->promo_model->select_rowArray('tk', 'req_new_shift_emp', ['amsId' => $amsId]);
                        if ($sq) {
                            $dateReq = date("Y/m/d", strtotime($sq['dateReq']));
                            $timeReq = date("h:i A", strtotime($sq['timeReq']));
                            $name = $this->promo_model->select_rowArray('pis', 'employee3', ['emp_id' => $sq['reqBy']])['name'];
                            $schedule = "[ <span data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ]'><font color = 'blue'>$shiftCode</font> = $sched</span> ]";
                        }

                        $sq = $this->promo_model->select_rowArray('tk', 'request_odaf', ['amsId' => $amsId]);
                        if ($sq) {
                            $dateReq = date("Y/m/d", strtotime($sq['dateReq']));
                            $timeReq = date("h:i A", strtotime($sq['timeReq']));
                            $name = $this->promo_model->select_rowArray('pis', 'employee3', ['emp_id' => $sq['reqBy']])['name'];
                            $schedule = "[ <span data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ]'><font color = 'red'>$shiftCode</font> = $sched</span> ]";
                        }

                        $cond   = "empId='$empId' AND '$n_day' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
                        $query  = $this->promo_model->select_resultArray('tk', 'assign_mul_sc', $cond);
                        foreach ($query as $key => $row) {

                            $shiftCode = $row['shiftCode'];
                            $amsId = $row['amsId'];
                            $sched = '';
                            $sc = $this->promo_model->select_rowArray('tk', 'shiftcodes', ['shiftCode' => $shiftCode]);
                            if ($sc) {
                                $fIn    = $sc['1stIn'];
                                $fOut   = $sc['1stOut'];
                                $sIn    = $sc['2ndIn'];
                                $sOut   = $sc['2ndOut'];
                                $sched = "$fIn - $fOut";
                                if ($sIn && $sOut) {
                                    $sched = "$fIn - $fOut | $sIn - $sOut";
                                }
                            }
                            $sq = $this->promo_model->select_rowArray('tk', 'request_new_shift', ['amsId' => $amsId]);
                            if ($sq) {
                                $dateReq = date("Y/m/d", strtotime($sq['dateReq']));
                                $timeReq = date("h:i A", strtotime($sq['timeReq']));
                                $name = $this->promo_model->select_rowArray('pis', 'employee3', ['emp_id' => $sq['reqBy']])['name'];
                                // $request = "[ <span data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ]'><font color = 'blue'>$shiftCode</font> = $sched</span> ]";
                                $request .= "<span class='badge bg-primary mr-1' data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ] <br>[ $shiftCode = $sched ]'>CS</span>";
                            }

                            $sq = $this->promo_model->select_rowArray('tk', 'req_new_shift_emp', ['amsId' => $amsId]);
                            if ($sq) {
                                $dateReq = date("Y/m/d", strtotime($sq['dateReq']));
                                $timeReq = date("h:i A", strtotime($sq['timeReq']));
                                $name = $this->promo_model->select_rowArray('pis', 'employee3', ['emp_id' => $sq['reqBy']])['name'];
                                // $request = "[ <span data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ]'><font color = 'blue'>$shiftCode</font> = $sched</span> ]";
                                $request .= "<span class='badge bg-primary mr-1' data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ] <br>[ $shiftCode = $sched ]'>CS</span>";
                            }

                            $sq = $this->promo_model->select_rowArray('tk', 'request_odaf', ['amsId' => $amsId]);
                            if ($sq) {
                                $dateReq = date("Y/m/d", strtotime($sq['dateReq']));
                                $timeReq = date("h:i A", strtotime($sq['timeReq']));
                                $name = $this->promo_model->select_rowArray('pis', 'employee3', ['emp_id' => $sq['reqBy']])['name'];
                                // $request = "[ <span data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ]'><font color = 'red'>$shiftCode</font> = $sched</span> ]";
                                $request .= "<span class='badge bg-danger mr-1' data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ] <br>[ $shiftCode = $sched ]'>ODA</span>";
                            }
                        }
                    }

                    if ($dayoff || $no_sched) {

                        $cond   = "empId='$empId' AND '$n_day' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
                        $query  = $this->promo_model->select_resultArray('tk', 'assign_mul_sc', $cond);
                        foreach ($query as $key => $row) {
                            $shiftCode = $row['shiftCode'];
                            $amsId = $row['amsId'];

                            $sched = '';
                            $sc = $this->promo_model->select_rowArray('tk', 'shiftcodes', ['shiftCode' => $shiftCode]);
                            if ($sc) {
                                $fIn    = $sc['1stIn'];
                                $fOut   = $sc['1stOut'];
                                $sIn    = $sc['2ndIn'];
                                $sOut   = $sc['2ndOut'];
                                $sched = "$fIn - $fOut";
                                if ($sIn && $sOut) {
                                    $sched = "$fIn - $fOut | $sIn - $sOut";
                                }
                            }

                            $sq = $this->promo_model->select_rowArray('tk', 'request_new_shift', ['amsId' => $amsId]);
                            if ($sq) {
                                $dateReq = date("Y/m/d", strtotime($sq['dateReq']));
                                $timeReq = date("h:i A", strtotime($sq['timeReq']));
                                $name = $this->promo_model->select_rowArray('pis', 'employee3', ['emp_id' => $sq['reqBy']])['name'];
                                // $request = "[ <span data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ]'><font color = 'blue'>$shiftCode</font> = $sched</span> ]";
                                $request .= "<span class='badge bg-primary mr-1' data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ] <br>[ $shiftCode = $sched ]'>CS</span>";
                            }

                            $sq = $this->promo_model->select_rowArray('tk', 'request_odaf', ['amsId' => $amsId]);
                            if ($sq) {
                                $dateReq = date("Y/m/d", strtotime($sq['dateReq']));
                                $timeReq = date("h:i A", strtotime($sq['timeReq']));
                                $name = $this->promo_model->select_rowArray('pis', 'employee3', ['emp_id' => $sq['reqBy']])['name'];
                                // $request = "[ <span data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ]'><font color = 'red'>$shiftCode</font> = $sched</span> ]";
                                $request .= "<span class='badge bg-danger mr-1' data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ] <br>[ $shiftCode = $sched ]'>ODA</span>";
                            }
                        }
                    }
                } else {
                    $day = date('l', strtotime($n_day));
                    $cond = "empId='$empId' AND day='$day'";
                    $dayoff = $this->promo_model->select_rowArray('tk', 'assign_day_off', $cond);
                    if ($dayoff) {
                        $schedule = '<font color="red">DAY-OFF</font>';
                    } else {

                        $cond = "empId='$empId' AND barcodeId NOT LIKE '%\_%' AND bioMetricId NOT LIKE '%\_%' ORDER BY assignId DESC LIMIT 1";
                        $row = $this->promo_model->select_rowArray('tk', 'assign', $cond);
                        if ($row) {
                            $sched = '';
                            $shiftCode = $row['shiftCode'];
                            $sc = $this->promo_model->select_rowArray('tk', 'shiftcodes', ['shiftCode' => $shiftCode]);
                            if ($sc) {
                                $fIn    = $sc['1stIn'];
                                $fOut   = $sc['1stOut'];
                                $sIn    = $sc['2ndIn'];
                                $sOut   = $sc['2ndOut'];
                                $sched = "<span style='font-size: smaller;'>$fIn - $fOut</span>";
                                if ($sIn && $sOut) {
                                    $sched = "<span style='font-size: smaller;'>$fIn - $fOut | $sIn - $sOut</span>";
                                }

                                $schedule = "[ <span><font color = 'gray'>$shiftCode</font> = $sched</span> ]";
                            }
                        }
                    }
                }

                echo "<tr>
                        <td>$wholeDate</td>
                        <td>$dayS</td>
                        <td>$schedule</td>
                        <td>$storeSched</td>
                        <td class='text-center'>$request</td>";
                echo "</tr>";
            }
        } else {

            for ($i = 1; $i <= $last; $i++) {
                $wholeDate  = date('M. d, Y', strtotime("$year-$month-$i"));
                $n_day      = date('Y-m-d', strtotime("$year-$month-$i"));
                $dayS       = date('l', strtotime($n_day));
                $schedule   = '';
                $storeSched = '';
                $request    = '';

                // $cond  = "empId='$empId' AND '$n_day' BETWEEN dateFrom AND dateTo ORDER BY id DESC LIMIT 1";
                // $co = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond);

                $cond = "empId='$empId' AND dateRendered='$n_day' ORDER BY pId DESC LIMIT 1";
                $co = $this->promo_model->select_rowArray('tk', 'promo_mul_sc', $cond);
                if ($co) {
                    $shiftCode  = $co['shiftCode'];
                    $amsId      = $co['amsId'];
                    $no_store   = $co['noStore'];

                    $cond = "empId='$empId' AND amsId='$amsId' AND date='$n_day'";
                    $dayoff = $this->promo_model->select_rowArray('tk', 'ass_mul_do', $cond);
                    if ($dayoff) {
                        $schedule = '<font color="red">DAY-OFF</font>';
                    } else if ($no_store == 0) {
                        $schedule = '<font color="red">NO SCHEDULE</font>';
                    } else {
                        $sched = '';
                        $sg = $this->promo_model->select_rowArray('tk', 'shiftcodes', ['shiftCode' => $shiftCode]);
                        if ($sg) {
                            $fIn    = $sg['1stIn'];
                            $fOut   = $sg['1stOut'];
                            $sIn    = $sg['2ndIn'];
                            $sOut   = $sg['2ndOut'];
                            $sched = "<span style='font-size: smaller;'>$fIn - $fOut</span>";
                            if ($sIn && $sOut) {
                                $sched = "<span style='font-size: smaller;'>$fIn - $fOut | $sIn - $sOut</span>";
                            }
                        }

                        $sq = $this->promo_model->select_rowArray('tk', 'request_new_shift', ['amsId' => $amsId]);
                        if ($sq) {
                            $dateReq = date("Y/m/d", strtotime($sq['dateReq']));
                            $timeReq = date("h:i A", strtotime($sq['timeReq']));
                            $name = $this->promo_model->select_rowArray('pis', 'employee3', ['emp_id' => $sq['reqBy']])['name'];
                            $schedule = "[ <span  data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ]'><font color = 'blue'>$shiftCode</font> = $sched</span> ]";
                        }

                        $sq = $this->promo_model->select_rowArray('tk', 'request_odaf', ['amsId' => $amsId]);
                        if ($sq) {
                            $dateReq = date("Y/m/d", strtotime($sq['dateReq']));
                            $timeReq = date("h:i A", strtotime($sq['timeReq']));
                            $name = $this->promo_model->select_rowArray('pis', 'employee3', ['emp_id' => $sq['reqBy']])['name'];
                            $schedule = "[ <span  data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ]'><font color = 'red'>$shiftCode </font> = $sched</span> ]";
                        }

                        $cond   = "empId='$empId' AND '$n_day' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
                        $query  = $this->promo_model->select_resultArray('tk', 'assign_mul_sc', $cond);

                        // $cond = "empId='$empId' AND amsId!='$amsId' AND dateRendered='$n_day' ORDER BY pId DESC";
                        // $query = $this->promo_model->select_resultArray('tk', 'promo_mul_sc', $cond);
                        foreach ($query as $key => $row) {
                            $shiftCode = $row['shiftCode'];
                            $amsId = $row['amsId'];

                            $sched = '';
                            $sc = $this->promo_model->select_rowArray('tk', 'shiftcodes', ['shiftCode' => $shiftCode]);
                            if ($sc) {
                                $fIn    = $sc['1stIn'];
                                $fOut   = $sc['1stOut'];
                                $sIn    = $sc['2ndIn'];
                                $sOut   = $sc['2ndOut'];
                                $sched = "$fIn - $fOut";
                                if ($sIn && $sOut) {
                                    $sched = "$fIn - $fOut | $sIn - $sOut";
                                }
                            }

                            $sq = $this->promo_model->select_rowArray('tk', 'request_new_shift', ['amsId' => $amsId]);
                            if ($sq) {
                                $dateReq = date("Y/m/d", strtotime($sq['dateReq']));
                                $timeReq = date("h:i A", strtotime($sq['timeReq']));
                                $name = $this->promo_model->select_rowArray('pis', 'employee3', ['emp_id' => $sq['reqBy']])['name'];
                                // $request = "[ <span data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ]'><font color = 'blue'>$shiftCode</font> = $sched</span> ]";
                                $request .= "<span class='badge bg-primary mr-1' data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ] <br>[ $shiftCode = $sched ]'>CS</span>";
                            }

                            $sq = $this->promo_model->select_rowArray('tk', 'request_odaf', ['amsId' => $amsId]);
                            if ($sq) {
                                $dateReq = date("Y/m/d", strtotime($sq['dateReq']));
                                $timeReq = date("h:i A", strtotime($sq['timeReq']));
                                $name = $this->promo_model->select_rowArray('pis', 'employee3', ['emp_id' => $sq['reqBy']])['name'];
                                // $request = "[ <span data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ]'><font color = 'red'>$shiftCode</font> = $sched</span> ]";
                                $request .= "<span class='badge bg-danger mr-1' data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ] <br>[ $shiftCode = $sched ]'>ODA</span>";
                            }
                        }
                    }

                    if ($dayoff || $no_store == 0) {

                        $cond   = "empId='$empId' AND '$n_day' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
                        $query  = $this->promo_model->select_resultArray('tk', 'assign_mul_sc', $cond);
                        foreach ($query as $key => $row) {
                            $shiftCode = $row['shiftCode'];
                            $amsId = $row['amsId'];

                            $sched = '';
                            $sc = $this->promo_model->select_rowArray('tk', 'shiftcodes', ['shiftCode' => $shiftCode]);
                            if ($sc) {
                                $fIn    = $sc['1stIn'];
                                $fOut   = $sc['1stOut'];
                                $sIn    = $sc['2ndIn'];
                                $sOut   = $sc['2ndOut'];
                                $sched = "$fIn - $fOut";
                                if ($sIn && $sOut) {
                                    $sched = "$fIn - $fOut | $sIn - $sOut";
                                }
                            }

                            $sq = $this->promo_model->select_rowArray('tk', 'request_new_shift', ['amsId' => $amsId]);
                            if ($sq) {
                                $dateReq = date("Y/m/d", strtotime($sq['dateReq']));
                                $timeReq = date("h:i A", strtotime($sq['timeReq']));
                                $name = $this->promo_model->select_rowArray('pis', 'employee3', ['emp_id' => $sq['reqBy']])['name'];
                                // $request = "[ <span data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ]'><font color = 'blue'>$shiftCode</font> = $sched</span> ]";
                                $request .= "<span class='badge bg-primary mr-1' data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ] <br>[ $shiftCode = $sched ]'>CS</span>";
                            }

                            $sq = $this->promo_model->select_rowArray('tk', 'request_odaf', ['amsId' => $amsId]);
                            if ($sq) {
                                $dateReq = date("Y/m/d", strtotime($sq['dateReq']));
                                $timeReq = date("h:i A", strtotime($sq['timeReq']));
                                $name = $this->promo_model->select_rowArray('pis', 'employee3', ['emp_id' => $sq['reqBy']])['name'];
                                // $request = "[ <span data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ]'><font color = 'red'>$shiftCode</font> = $sched</span> ]";
                                $request .= "<span class='badge bg-danger mr-1' data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ] <br>[ $shiftCode = $sched ]'>ODA</span>";
                            }
                        }
                    }

                    if ($co['noStore'] > 0) {
                        $storea = str_replace(',', '', $co['store1']);
                        $storeb = str_replace(',', '', $co['store2']);
                        if ($co['noStore'] == '1') {
                            $row = $this->promo_model->select_rowArray('pis', 'locate_promo_business_unit', ['bu' => $storea]);
                            if ($row) {
                                $storeSched = $row['bunit_name'];
                            }
                        } else {
                            $row = $this->promo_model->select_rowArray('pis', 'locate_promo_business_unit', ['bu' => $storea]);
                            if ($row) {
                                $storeSched = $row['bunit_name'];
                            }

                            $row = $this->promo_model->select_rowArray('pis', 'locate_promo_business_unit', ['bu' => $storeb]);
                            if ($row) {
                                if ($storeSched) {
                                    $storeSched .= ' / ' . $row['bunit_name'];
                                } else {
                                    $storeSched .= $row['bunit_name'];
                                }
                            }
                        }
                    }
                } else {

                    $day = date('l', strtotime($n_day));
                    $cond = "empId='$empId' AND day_sched='$day'";
                    $ds = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
                    if ($ds) {
                        $shiftCode  = $ds['shiftCode'];
                        $no_store   = $ds['noStore'];

                        $day = date('l', strtotime($n_day));
                        $cond = "empId='$empId' AND day='$day'";
                        $dayoff = $this->promo_model->select_rowArray('tk', 'assign_day_off', $cond);
                        if ($dayoff) {
                            $schedule = '<font color="red">DAY-OFF</font>';
                        } else if ($no_store == '0') {
                            $schedule = '<font color="red">NO SCHEDULE</font>';
                        } else {
                            $sched = '';
                            $sg = $this->promo_model->select_rowArray('tk', 'shiftcodes', ['shiftCode' => $shiftCode]);
                            if ($sg) {
                                $fIn    = $sg['1stIn'];
                                $fOut   = $sg['1stOut'];
                                $sIn    = $sg['2ndIn'];
                                $sOut   = $sg['2ndOut'];
                                $sched = "<span style='font-size: smaller;'>$fIn - $fOut</span>";
                                if ($sIn && $sOut) {
                                    $sched = "<span style='font-size: smaller;'>$fIn - $fOut | $sIn - $sOut</span>";
                                }

                                $schedule = "[ <span><font color = 'gray'>$shiftCode</font> = $sched</span> ]";
                            }
                        }

                        if ($ds['noStore'] > 0) {
                            $storea = str_replace(',', '', $ds['store1']);
                            $storeb = str_replace(',', '', $ds['store2']);
                            if ($ds['noStore'] == '1') {
                                $row = $this->promo_model->select_rowArray('pis', 'locate_promo_business_unit', ['bu' => $storea]);
                                if ($row) {
                                    $storeSched = $row['bunit_name'];
                                }
                            } else {
                                $row = $this->promo_model->select_rowArray('pis', 'locate_promo_business_unit', ['bu' => $storea]);
                                if ($row) {
                                    $storeSched = $row['bunit_name'];
                                }

                                $row = $this->promo_model->select_rowArray('pis', 'locate_promo_business_unit', ['bu' => $storeb]);
                                if ($row) {
                                    if ($storeSched) {
                                        $storeSched .= ' / ' . $row['bunit_name'];
                                    } else {
                                        $storeSched .= $row['bunit_name'];
                                    }
                                }
                            }
                        }
                    }

                    $day = date('l', strtotime($n_day));
                    $cond = "empId='$empId' AND day='$day'";
                    $dayoff = $this->promo_model->select_rowArray('tk', 'assign_day_off', $cond);
                    if ($dayoff) {
                        $schedule = '<font color="red">DAY-OFF</font>';
                    }
                }
                echo "<tr>
                        <td>$wholeDate</td>
                        <td>$dayS</td>
                        <td>$schedule</td>
                        <td>$storeSched</td>
                        <td class='text-center'>$request</td>";
                echo "</tr>";
            }
        }
        echo '</table>';
    }

    public function assign_list()
    {
        $input  = $this->input->post(NULL, TRUE);
        $date   = $input['date'];
        echo '<table class="table table-sm table-hover align-middle">
                <thead>
                    <tr>
                        <th>HRMS ID</th>
                        <th>Name</th>
                        <th>Cutoff</th>
                        <th>Assigned By</th>
                    </tr>
                </thead>';
        $i = 0;
        $cond   = ['dateApplied' => $date];
        $query  = $this->promo_model->select_resultArray('tk', 'assign', $cond);
        foreach ($query as $key => $row) {

            $empId = $row['empId'];
            $addedBy = $this->promo_model->getName_employee3($row['added_by']);

            $cond = ['e.emp_id' => $empId, 'promo_type' => 'ROVING'];
            $promo = $this->promo_model->promoDetailsRow($cond);
            if ($promo) {

                $name = $promo['name'];
                $recordNo = $promo['record_no'];

                $cond = ['empId' => $empId, 'recordNo' => $recordNo];
                $statCut = $this->promo_model->select_rowArray('tk', 'promo_sched_emp', $cond)['statCut'];
                if ($statCut) {
                    $i++;
                    $cond = ['statCut' => $statCut];
                    $ps = $this->promo_model->select_rowArray('tk', 'promo_schedule', $cond);
                    $endFC = ($ps['endFC'] == '') ? 'Last' : $ps['endFC'];
                    $sched = $ps['startFC'] . '-' . $endFC . ' | ' . $ps['startSC'] . '-' . $ps['endSC'];

                    echo '<tr>
                            <td>' . $empId . '</td>
                            <td>' . $name . '</td>
                            <td>' . $sched . '</td>
                            <td>' . $addedBy . '</td>';
                    echo '</tr>';
                }
            }
        }
        if ($i == 0) {
            echo '<tr>
                    <th colspan="4" class="text-center text-danger">No Data Found!</th>';
            echo '</tr>';
        }
        echo '</table>';
    }

    public function holiday_list()
    {
        $data = [];
        $query = $this->promo_model->selectAll('tk', 'holidays_promo');
        foreach ($query as $key => $row) {
            $hId = $row['hId'];

            $date = '';
            $order = '';
            if ($row['date'] != '' && $row['date'] != '0000-00-00') {
                $date = date('M. d, Y', strtotime($row['date']));
                $order = '<span style="display:none;">' . strtotime($row['date']) . '</span>';
            }

            $moveDate = '';
            if ($row['moveDate'] != '' && $row['moveDate'] != '0000-00-00') {
                $moveDate = date('M. d, Y', strtotime($row['moveDate']));
            }

            $action = '<a href="javascript:;" onclick="holiday_update(\'' . $hId . '\',\'update\')"><i class="fa fa-edit text-md text-primary mr-2"></i></a>';
            $action .= '<a href="javascript:;" onclick="holiday_update(\'' . $hId . '\',\'delete\')"><i class="fa fa-trash text-md text-danger"></i></a>';

            $col    = [];
            $col[]  = $row['nameHoliday'];
            $col[]  = $order . $date;
            $col[]  = $moveDate;
            $col[]  = $row['type'];
            $col[]  = $row['category'];
            $col[]  = $action;
            $data[] = $col;
        }

        echo json_encode(['data' => $data]);
    }

    public function holiday_form()
    {
        $input  = $this->input->post(NULL, TRUE);
        $hId    = $input['id'];
        $cond   = ['hId' => $hId];
        $hol    = $this->promo_model->select_rowArray('tk', 'holidays_promo', $cond);
        echo '<div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="holiday">Holiday Name</label>
                        <input type="hidden" value="update" name="action" required>
                        <input type="hidden" value="' . $hId . '" name="hId" required>
                        <input type="text" class="form-control" value="' . $hol['nameHoliday'] . '" id="holiday" name="holiday" placeholder="Type holiday name..." autocomplete="off" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="date">Holiday Date</label>
                        <input type="text" class="form-control datetimepicker-input" value="' . $hol['date'] . '" id="date" name="date" data-toggle="datetimepicker" data-target="#date" placeholder="yyyy-mm-dd" autocomplete="off" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="moveDate">Move Date</label>
                        <input type="text" class="form-control datetimepicker-input" value="' . $hol['moveDate'] . '" id="moveDate" name="moveDate" data-toggle="datetimepicker" data-target="#moveDate" placeholder="yyyy-mm-dd" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="type">Holiday Type</label>
                        <select class="form-control" id="type" name="type" onchange="getCategory(this.value)" required>
                            <option value="">---</option>
                            <option value="Legal" ' . ($hol['type'] == 'Legal' ? 'selected' : '') . '>Legal</option>
                            <option value="Special" ' . ($hol['type'] == 'Special' ? 'selected' : '') . '>Special</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="category">Holiday Category</label>
                        <select class="form-control" id="category" name="category" required>
                            <option value="">---</option>
                            <option value="pay_100" ' . ($hol['acr_cat'] == 'pay_100' ? 'selected' : '') . '>+ 100% Pay (With or Without Duty)</option>
                            <option value="pay30_W_WO" ' . ($hol['acr_cat'] == 'pay30_W_WO' ? 'selected' : '') . '>+ 30% Pay(With or Without Duty)</option>
                            <option value="pay30_W" ' . ($hol['acr_cat'] == 'pay30_W' ? 'selected' : '') . '>+ 30% Pay(With Duty)</option>
                            <option value="pay_no" ' . ($hol['acr_cat'] == 'pay_no' ? 'selected' : '') . '>No Pay</option>
                        </select>
                    </div>
                </div>';
        echo '</div>';
    }

    public function holiday_update()
    {
        $input  = $this->input->post(NULL, TRUE);
        $action = $input['action'];
        if ($action == 'update') {

            $hId = $input['hId'];
            $acr_cat = $input['category'];
            if ($acr_cat == "pay_100") {
                $category = "+ 100% Pay (With or Without Duty)";
            }

            if ($acr_cat == "pay30_W_WO") {
                $category = "+ 30% Pay(With or Without Duty)";
            }

            if ($acr_cat == "pay30_W") {
                $category = "+ 30% Pay(With Duty)";
            }

            if ($acr_cat == "pay_no") {
                $category = "No Pay";
            }

            $data = [
                'hId'           => $hId,
                'date'          => $input['date'],
                'moveDate'      => $input['moveDate'],
                'acr_cat'       => $acr_cat,
                'category'      => $category,
                'nameHoliday'   => $input['holiday'],
                'type'          => $input['type'],
            ];

            $cond = ['hId' => $hId];
            $update = $this->promo_model->update('tk', 'holidays_promo', $cond, $data);
            if ($update) {
                echo json_encode(['message' => 'success', 'feedback' => 'Holiday has been updated.']);
            } else {
                echo json_encode(['message' => 'failed']);
            }
        } else {
            $hId = $input['id'];
            $cond = ['hId' => $hId];
            $delete = $this->promo_model->delete('tk', 'holidays_promo', $cond);
            if ($delete) {
                echo json_encode(['message' => 'success', 'feedback' => 'Holiday has been deleted.']);
            } else {
                echo json_encode(['message' => 'failed']);
            }
        }
    }
}
