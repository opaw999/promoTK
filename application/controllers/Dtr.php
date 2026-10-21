<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Dtr extends CI_Controller
{
    public $userName;
    public $userId;
    public $hrCode;
    public $ppdf;
    function __construct()
    {
        parent::__construct();
        $this->load->library('ppdf');
        $cond   = array('empId' => $_SESSION['tkEmpId'], 'status' => 'active');
        $user   = $this->promo_model->select_rowArray('tk', 'user', $cond);

        $cond   = array('empId' => $_SESSION['tkEmpId'], 'order_by' => '1', 'status' => '1');
        $access = $this->promo_model->select_rowArray('tk', 'setup_user_menu', $cond);

        if (!isset($_SESSION['tkEmpId']) && !$user && !$access) {
            redirect('http://' . $_SERVER['SERVER_ADDR'] . ":" . $_SERVER['SERVER_PORT'] . '/timekeeping');
        } else {
            $this->userId = $_SESSION['tkEmpId'];
            $this->userId = $_SESSION['hrCode'];
            $this->userName   = $_SESSION['usr'];
        }
    }

    public function searchPromo()
    {
        echo '<ul class="list-group">';
        $input  = $this->input->post(NULL, TRUE);
        $string = trim($input['string']);
        $query  = $this->promo_model->searchPromo($string);
        if ($query) {
            foreach ($query as  $row) {
                $name       = ucwords(strtolower($row['name']));
                $empId      = $row['emp_id'];
                $recordNo   = $row['record_no'];
                $statCut    = $row['statCut'];

                $endFC = ($row['endFC'] == '') ? 'Last' : $row['endFC'];
                $sched = $row['startFC'] . '-' . $endFC . ' / ' . $row['startSC'] . '-' . $row['endSC'];

                $onclick = 'onclick="selectPromo(\'' . $name . '\',\'' . $empId . '\',\'' . $recordNo . '\',\'' . $statCut . '\',\'' . $sched . '\')"';
                echo '<a href="javascript:;" class="list-group-item" ' . $onclick . '>[' . $empId . '] ' . $name . '</a>';
            }
        } else {
            echo '<span class="list-group-item">No Results Found!</span>';
        }
        echo '</ul>';
    }

    public function searchEarlyCo()
    {
        echo '<ul class="list-group">';
        $input  = $this->input->post(NULL, TRUE);
        $string = trim($input['string']);
        $query  = $this->promo_model->searchPromo($string);
        if ($query) {
            foreach ($query as  $row) {
                $name       = ucwords(strtolower($row['name']));
                $empId      = $row['emp_id'];
                $recordNo   = $row['record_no'];
                $statCut    = $row['statCut'];

                $endFC = ($row['endFC'] == '') ? 'Last' : $row['endFC'];
                $sched = $row['startFC'] . '-' . $endFC . ' / ' . $row['startSC'] . '-' . $row['endSC'];

                $id = "$empId|$name";

                $onclick = 'onclick="selectEarlyCo(\'' . $id . '\')"';
                echo '<a href="javascript:;" class="list-group-item" ' . $onclick . '>[' . $empId . '] ' . $name . '</a>';
            }
        } else {
            echo '<span class="list-group-item">No Results Found!</span>';
        }
        echo '</ul>';
    }

    public function calculateDate()
    {
        $input      = $this->input->post(NULL, TRUE);
        $datetime1  = new DateTime($input['dateFrom']);
        $datetime2  = new DateTime($input['dateTo']);
        $interval   = $datetime2->diff($datetime1);
        $duration   = $interval->format('%a') + 1;
        $dateFrom   = strtotime($input['dateFrom']);
        $dateTo     = strtotime($input['dateTo']);
        if ($dateTo >= $dateFrom) {
            echo $duration;
        } else {
            echo 'failed';
        }
    }

    public function promoDetails()
    {
        $input      = $this->input->post(NULL, TRUE);
        $empId      = $input['empId'];
        $recordNo   = $input['recordNo'];

        $app    = $this->promo_model->select_rowArray('pis', 'applicant', array('app_id' => $empId));
        $photo  = explode("../", $app['photo']);
        @$link  = 'http://' . $_SERVER['SERVER_ADDR'] . ':80' . $_SERVER['SERVER_PORT'] . '/hrms/' . $photo[1];

        $cond       = ['e.emp_id' => $empId, 'e.record_no' => $recordNo];
        $emp        = $this->promo_model->promoDetailsRow($cond);
        $recordNo   = $emp['record_no'];
        $name       = ucwords(strtolower($emp['name']));
        $company    = $emp['promo_company'];
        $department = $emp['promo_department'];
        $position   = $emp['position'];
        $subStat = '';
        if ($emp['sub_status'] != '') {
            $subStat = '(' . $emp['sub_status'] . ')';
        }

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

        echo '<div class="row mt-3">';
        echo    '<div class="col-md-3 d-flex flex-column align-items-center text-center">
                    <img src="' . $link . '" alt="img" class="img-thumbnail" width="100" height="100">
                    <span><b>' . $empId . '</b></span>
                    ' . $status . '<i>' . $subStat . '</i>
                </div>';
        echo    '<div class="col-md-9">
                    <h2 class="lead mb-0"><b>' . $name . '</b></h2>
                    <p class="text-muted text-sm mt-0"> ' . $position . ' </p>
                    <ul class="ml-4 mb-0 fa-ul text-muted">
                        <li class="small"><span class="fa-li"><i class="fas fa-lg fa-store"></i> </span> ' . $stores . '</li>
                        <li class="small"><span class="fa-li"><i class="fas fa-lg fa-building"></i> </span> ' . $company . '</li>
                        <li class="small"><span class="fa-li"><i class="fas fa-shopping-cart fa-lg"></i> </span> ' . $department . '</li>
                    </ul>
                </div>';
        echo '</div>';
    }

    public function promoCutoffs()
    {
        $input      = $this->input->post(NULL, TRUE);
        $empId      = $input['empId'];
        $recordNo   = $input['recordNo'];
        $statCut    = $input['statCut'];

        $cond   = "empId='$empId' AND barcodeId NOT LIKE '%\_%' AND bioMetricId NOT LIKE '%\_%' ORDER BY assignId DESC LIMIT 1";
        $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);

        $allowId = $assign['allowId'];
        if ($allowId == "Biometric ID") {
            $bioM = $assign['bioMetricId'];
        }

        if ($allowId == "Logbox ID") {
            $bioM = $assign['barcodeId'];
        }

        echo '<table id="promoCutoffs" class="table table-hover table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Date From</th>
                        <th>Date To</th>
                        <th>Action</th>
                    </tr>
                </thead>';

        $query = $this->promo_model->promoCutoffs($bioM, $statCut);
        foreach ($query as $key => $row) {

            $dateFrom   = date('Y-m-d', strtotime($row['dateFrom']));
            $dateTo     = date('Y-m-d', strtotime($row['dateTo']));
            $cut        = $row['cut'];

            $date   = strtotime($row['dateFrom']);
            $df     = date('M. d, Y', strtotime($row['dateFrom']));
            $dt     = date('M. d, Y', strtotime($row['dateTo']));

            $load   = "$empId|$recordNo|$dateFrom|$dateTo|$cut|$statCut|$bioM";
            $click  = 'onclick="view_dtr(\'' . $load . '\')"';
            $action = '<a href="javascript:;" ' . $click . '><i class="fa fa-list text-md text-primary"></i></a>';

            echo '<tr>';
            echo '<td><span style="display:none;">' . $date . '</span>' . $df . '</td>';
            echo '<td> ' . $dt . '</td>';
            echo '<td>' . $action . '</td>';
            echo '</tr>';
        }

        echo '</table>';

        $query = $this->promo_model->promoCutoffs2($bioM, $statCut);
        if ($query) {
            echo '<h5 class="mt-3">Previous Cutoffs</h5>';
            echo '<table id="promoCutoffs2" class="table table-hover table-bordered table-sm ">
                <thead>
                    <tr>
                        <th>Date From</th>
                        <th>Date To</th>
                        <th>Action</th>
                    </tr>
                </thead>';

            $query = $this->promo_model->promoCutoffs2($bioM, $statCut);
            foreach ($query as $key => $row) {

                $dateFrom   = date('Y-m-d', strtotime($row['dateFrom']));
                $dateTo     = date('Y-m-d', strtotime($row['dateTo']));
                $cut        = $row['cut'];

                $date   = strtotime($row['dateFrom']);
                $df     = date('M. d, Y', strtotime($row['dateFrom']));
                $dt     = date('M. d, Y', strtotime($row['dateTo']));

                $load   = "$empId|$recordNo|$dateFrom|$dateTo|$cut|$statCut|$bioM";
                $click  = 'onclick="view_dtr(\'' . $load . '\')"';
                $action = '<a href="javascript:;" ' . $click . '><i class="fa fa-list text-md text-primary"></i></a>';

                echo '<tr>';
                echo '<td><span style="display:none;">' . $date . '</span>' . $df . '</td>';
                echo '<td> ' . $dt . '</td>';
                echo '<td>' . $action . '</td>';
                echo '</tr>';
            }
        }

        echo '</table>';
    }

    public function cutoff_table()
    {
        $input = $this->input->post(NULL, TRUE);
        $statCut = $input['statCut'];
        $data = [];
        $query = $this->promo_model->cutoff_table($statCut, '1stcutoff');
        foreach ($query as $row) {
            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));
            $id = $statCut . '|1|' . $df . '|' . $dt;

            $sub_array = [];
            $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
            $sub_array[] = $dt;
            $sub_array[] = '<a href="javascript:;" id="' . $id . '" class="emp_list"><i class="fa fa-list text-md text-primary"></i></a>';
            $data[] = $sub_array;
        }

        $query = $this->promo_model->cutoff_table($statCut, '2ndcutoff');
        foreach ($query as $row) {
            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));
            $id = $statCut . '|2|' . $df . '|' . $dt;

            $sub_array = [];
            $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
            $sub_array[] = $dt;
            $sub_array[] = '<a href="javascript:;" id="' . $id . '" class="emp_list"><i class="fa fa-list text-md text-primary"></i></a>';
            $data[] = $sub_array;
        }

        echo json_encode(['data' => $data]);
    }

    public function employee_list()
    {
        $input      = $this->input->post(NULL, TRUE);
        $statCut    = $input['statCut'];
        $cut        = $input['cut'];
        $dateFrom   = date('Y-m-d', strtotime($input['dateFrom']));
        $dateTo     = date('Y-m-d', strtotime($input['dateTo']));

        $data   = [];
        $table  = ($cut == 1) ? '1stcutoff' : '2ndcutoff';
        $query  = $this->promo_model->promo_dtr_list($statCut, $dateFrom, $dateTo, $table);
        foreach ($query as $key => $row) {
            $empId      = $row['empId'];
            $recordNo   = $row['recordNo'];
            $name       = $row['name'];
            $company    = $row['promo_company'];
            $department = $row['promo_department'];
            $type       = $row['promo_type'];

            $allowId = $row['allowId'];
            if ($allowId == "Biometric ID") {
                $bioM = $row['bioMetricId'];
            }

            if ($allowId == "Logbox ID") {
                $bioM = $row['barcodeId'];
            }

            $load   = "$empId|$recordNo|$dateFrom|$dateTo|$cut|$statCut|$bioM";
            $click  = 'onclick="view_dtr(\'' . $load . '\')"';
            $action = '<a href="javascript:;" ' . $click . '><i class="fa fa-list text-md text-primary"></i></a>';

            $table  = ($cut == 1) ? '1stcutoff' : '2ndcutoff';
            $cond   = ['statCut' => $statCut, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'bioMetricId' => $bioM];
            $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
            if ($check) {

                $sub_array = [];
                $sub_array[] = $empId;
                $sub_array[] = $name;
                $sub_array[] = $company;
                $sub_array[] = $department;
                $sub_array[] = $type;
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
        }

        echo json_encode(['data' => $data]);
    }

    public function dtr_info()
    {
        $input      = $this->input->post(NULL, TRUE);
        $empId      = $input['empId'];
        $recordNo   = $input['recordNo'];
        $bioM       = $input['bioM'];
        $dateFrom   = date('Y-m-d', strtotime($input['dateFrom']));
        $dateTo     = date('Y-m-d', strtotime($input['dateTo']));
        $cut        = $input['i'];
        $statCut    = $input['statCut'];

        $select = '<select class="form-control form-control-sm" onchange="dtr_details(this.value)">';
        $query  = $this->promo_model->promoCutoffs($bioM, $statCut);
        foreach ($query as $row) {

            $nCut   = $row['cut'];
            $ndf    = date('Y-m-d', strtotime($row['dateFrom']));
            $ndt    = date('Y-m-d', strtotime($row['dateTo']));
            $val    = "$empId|$recordNo|$ndf|$ndt|$nCut|$statCut|$bioM";

            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));
            $sel = ($df == date('M. d, Y', strtotime($input['dateFrom']))) ? 'selected' : '';
            $select .= '<option value="' . $val . '" ' . $sel . '>' . $df . ' - ' . $dt . '</option>';
        }
        $select .= '</select>';
        if (!isset($_SESSION['audit_guest'])) {
            $load = "$empId|$recordNo|$statCut|$bioM";
            $click = 'onclick="dtr_process(\'' . $load . '\')"';
            $select .= '<a href="javascript:;" ' . $click . '>click to process</a>';
        }


        $cond       = ['e.emp_id' => $empId, 'e.record_no' => $recordNo];
        $promo      = $this->promo_model->promoDetailsRow($cond);
        $name       = ucwords(strtolower($promo['name']));
        $emp_type   = $promo['emp_type'];
        $company    = $promo['promo_company'];
        $department = $promo['promo_department'];
        $promo_type = $promo['promo_type'];
        $position   = $promo['position'];
        $status     = $promo['current_status'];
        if ($status == 'Active') {
            $stat = '<span class="badge mb-1 bg-success">' . $status . '</span>';
        } else if ($status == 'blacklisted') {
            $stat = '<span class="badge mb-1 bg-danger">' . $status . '</span>';
        } else {
            $stat = '<span class="badge mb-1 bg-warning">' . $status . '</span>';
        }

        $i = 0;
        $stores = '';
        $bus = $this->promo_model->locate_promo_bu($this->hrCode);
        foreach ($bus as $bu) {
            $hasBu = $this->promo_model->empStores('promo_record', $empId, $recordNo, $bu['bunit_field']);
            if ($hasBu) {
                $i++;
                $buName = '<span class="badge badge-secondary mr-1">' . $bu['bunit_acronym'] . '</span>';
                $stores = ($i == 1) ? $buName : $stores . $buName;
            }
        }

        $app    = $this->promo_model->select_rowArray('pis', 'applicant', ['app_id' => $empId]);
        $photo  = explode("../", $app['photo']);
        $link   = 'http://' . $_SERVER['SERVER_ADDR'] . ':8080/hrms/' . $photo[1];

        $load = "$empId|$recordNo|$statCut|$bioM";
        $click = 'onclick="viewMasterfile(\'' . $load . '\')"';
        $emp = '<a href="javascript:;">' . $empId . '</a>';
        if (!isset($_SESSION['audit_guest'])) {
            $emp = '<a href="javascript:;" ' . $click . '>' . $empId . '</a>';
        }


        echo '<div class="row">';
        echo '<div class="col-md-2 d-flex flex-column align-items-center text-center">
                <img src="' . $link . '" alt="img" class="img-thumbnail" width="100">
                <span class=""><b>' . $empId . '</b></span>' . $stat;
        echo '</div>';
        echo '<div class="col-md-10">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <th>HRMS ID</th>
                        <td>:</td>
                        <td width="30%">' . $emp . '</td>
                        <th width="15%">Company</th>
                        <td width="1%">:</td>
                        <td>' . $company . '</td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td>:</td>
                        <td width="30%">' . $name . '</td>
                        <th width="15%">Store(s)</th>
                        <td width="1%">:</td>
                        <td>' . $stores . '</td>
                    </tr>
                    <tr>
                        <th>Department</th>
                        <td>:</td>
                        <td>' . $department . '</td>
                        <th>Employee Type</th>
                        <td>:</td>
                        <td>' . $emp_type . '</td>
                    </tr>
                    <tr>
                        <th>Position</th>
                        <td>:</td>
                        <td>' . $position . '</td>
                        <th>Promo Type</th>
                        <td>:</td>
                        <td>' . $promo_type . '</td>
                    </tr>
                    <tr>
                        <th>Cutoff</th>
                        <td>:</td>
                        <td>' . $select . '</td>
                        <td colspan="3" class="net_data"><span class="mr-5"><span class="font-weight-bold">DW</span> : 0</span><span class="mr-5"><span class="font-weight-bold">OT</span> : 0</span><span class="mr-5"><span class="font-weight-bold">SIL</span> : 0</span><span class="mr-5"><span class="font-weight-bold">LH</span> : 0</span><span class="mr-5"><span class="font-weight-bold">SH</span> : 0</span></td>
                    </tr>
                </table>';
        echo '</div>';
        echo '<div class="col-md-12 dtr_details mt-2">';
        echo '</div>';
        echo '<div class="col-md-12 d-flex flex-wrap">';
        echo '<span class="mr-3">Legend:</span>';
        $stores = $this->promo_model->dtr_legend();
        foreach ($stores as $key => $store) {
            echo  '<span class="mr-3"><span class="text-danger">' . $key . '</span> - ' . $store . '</span>';
        }
        echo '</div>';
        echo '</div>';
    }

    public function dtr_net()
    {
        $input      = $this->input->post(NULL, TRUE);
        $empId      = $input['empId'];
        $bioM       = $input['bioM'];
        $dateFrom   = date('Y-m-d', strtotime($input['dateFrom']));
        $dateTo     = date('Y-m-d', strtotime($input['dateTo']));
        $cut        = $input['i'];

        $dw = 0;
        $ot = 0;
        $lh = 0;
        $sh = 0;
        $nd = 0;
        $tbl = ($input['i'] == 1) ? '1stcutoff' : '2ndcutoff';
        $cond = ['bioMetricId' => $bioM, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo];
        $co = $this->promo_model->select_rowArray('tk', $tbl, $cond);
        if ($co) {

            if ($co['daysWork'] != '' || $co['daysWork'] != 0) {
                $dw = $co['daysWork'];
                $decimal = explode('.', (string)$co['daysWork']);
                if (isset($decimal[1]) && strlen($decimal[1]) > 3) {
                    $dw = number_format($co['daysWork'], 3, '.', '');
                }
            }

            if ($co['otForPayment'] != '' || $co['otForPayment'] != 0) {
                $ot = $co['otForPayment'];
            }

            if ($co['legHol'] != '' || $co['legHol'] != 0) {
                $lh = $co['legHol'];
            }

            if ($co['speHol'] != '' || $co['speHol'] != 0) {
                $sh = $co['speHol'];
            }

            if ($co['night_diff'] != '' || $co['night_diff'] != 0) {
                $nd = $co['night_diff'];
            }
        }

        $sil        = 0;
        $cu         = $this->promo_model->no_sil_current($input)['sil'];
        $sil_cu     = ($cu) ? $cu : 0;
        $prev       = $this->promo_model->no_sil_previous($input)['sil'];
        $sil_prev   = ($prev) ? $prev : 0;
        $sil        = $sil_prev + $sil_cu;

        $net = [
            'DW'    => $dw,
            'OT'    => $ot,
            'SIL'   => $sil,
            // 'ND'    => $nd,
            'LH'    => $lh,
            'SH'    => $sh,
        ];

        $i = 0;
        $net_data = '';
        foreach ($net as $key => $net_val) {
            $i++;
            if ($i == 1) {
                $net_data .= '<span><span class="font-weight-bold">' . $key . '</span> : ' . $net_val . '</span>';
            } else {
                $net_data .= '<span class="ml-5"><span class="font-weight-bold">' . $key . '</span> : ' . $net_val . '</span>';
            }
        }

        echo $net_data;

        $dateFrom = date('M. d, Y', strtotime($input['dateFrom']));
        $dateTo = date('M. d, Y', strtotime($input['dateTo']));
        echo '<input type="hidden" name="empIds[]" value="' . $empId . '">';
        echo '<input type="hidden" name="dateFrom" value="' . $dateFrom . '">';
        echo '<input type="hidden" name="dateTo" value="' . $dateTo . '">';
        echo '<input type="hidden" name="cut" value="' . $cut . '">';
    }

    public function dtr_details()
    {
        $input      = $this->input->post(NULL, TRUE);
        $empId      = $input['empId'];
        $recordNo   = $input['recordNo'];
        $bioM       = $input['bioM'];
        $dateFrom   = date('Y-m-d', strtotime($input['dateFrom']));
        $dateTo     = date('Y-m-d', strtotime($input['dateTo']));
        $cut        = $input['i'];
        $statCut    = $input['statCut'];

        $cond       = ['e.emp_id' => $empId, 'e.record_no' => $recordNo];
        $promo      = $this->promo_model->promoDetailsRow($cond);
        $promo_type = $promo['promo_type'];

        $cond       = "empId='$empId' AND barcodeId NOT LIKE '%\_%' AND bioMetricId NOT LIKE '%\_%' ORDER BY assignId DESC LIMIT 1";
        $allowId    = $this->promo_model->select_rowArray('tk', 'assign', $cond)['allowId'];

        $table = '';
        if ($allowId == 'Biometric ID') {
            $table = 'duty_biometric';
        }

        if ($allowId == 'Logbox ID') {
            $table = 'duty_logbox';
        }

        echo '<div class="table-responsive">';
        echo '<table id="dt_dtrEntry" class="table table-hover table-sm table-sm align-middle" style="width:100%;">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Store(s)</th>
                        <th>IN1</th>
                        <th>OUT1</th>
                        <th>IN2</th>
                        <th>OUT2</th>
                        <th>IN3</th>
                        <th>OUT3</th>
                        <th class="text-center">HW</th>
                        <th class="text-center">DW</th>
                        <th class="text-center">OT</th>
                        <th class="text-center">Requests</th>
                        <th class="text-center">Entries</th>
                    </tr>
                </thead>';
        while (strtotime($dateFrom) <= strtotime($dateTo)) {

            $tempDate = date('Y-m-d', strtotime($dateFrom));
            $day = date('D', strtotime($dateFrom));

            $dutyId = $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';
            $cond   = "bioMetricId='$bioM' AND date='$tempDate' ORDER BY dutyId DESC";
            $dtr    = $this->promo_model->select_rowArray('tk', 'duty', $cond);
            $dutyId = $dtr['dutyId'];

            if ($dtr['in1']) {
                $in1 = date('h:i A', strtotime($dtr['in1']));
            }
            if ($dtr['out1']) {
                $out1 = date('h:i A', strtotime($dtr['out1']));
            }
            if ($dtr['in2']) {
                $in2 = date('h:i A', strtotime($dtr['in2']));
            }
            if ($dtr['out2']) {
                $out2 = date('h:i A', strtotime($dtr['out2']));
            }
            if ($dtr['in3']) {
                $in3 = date('h:i A', strtotime($dtr['in3']));
            }
            if ($dtr['out3']) {
                $out3 = date('h:i A', strtotime($dtr['out3']));
            }

            $stores = $this->promo_model->store_entry($bioM, $table, $tempDate);

            $cond   = ['bioMetricId' => $bioM, 'dateDuty' => $tempDate];
            $dutyl  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);

            $dw     = 0;
            $hw     = 0;
            $cond   = ['dutyId' => $dutyId];
            $perday = $this->promo_model->select_rowArray('tk', 'perday', $cond);
            if ($perday) {
                $phw = $perday['hoursWork'];
                $res = explode("&", $phw);
                if ($res[0] == '') {
                    $res[0] = 0;
                }

                if (!isset($res[1])) {
                    $res[1] = 0;
                } else if ($res[1] == '') {
                    $res[1] = 0;
                }

                $h = floatval($res[0]) * 1;
                $m = floatval($res[1]) * 1;

                $hrs = $h / 8;
                $min = ($m / 60) / 8;

                // days worked ********************************************************************
                $dw = round($hrs + $min, 3);
                $decimal = explode('.', (string)$dw);
                if (isset($decimal[1]) && strlen($decimal[1]) > 3) {
                    $dw = number_format($dw, 3, '.', '');
                }

                // hours worked ********************************************************************
                $min = round(($m / 60), 3);
                $hw =  round($h + $min, 3);
                $decimal = explode('.', (string)$h + $min);
                if (isset($decimal[1]) && strlen($decimal[1]) > 3) {
                    $hw = number_format($h + $min, 3, '.', '');
                }
            }

            // overtime ********************************************************************
            $ot = 0;
            $cond = "emp_id='$empId' AND otDate='$tempDate' ORDER BY pay_id DESC";
            $otApp = $this->promo_model->select_rowArray('tk', 'otforpayment', $cond)['otApp'];
            if ($otApp && $otApp != '' && $otApp != 0) {
                $ot = floatval($otApp) * 1;
            }
            // overtime ********************************************************************

            // holiday	********************************************************************
            $hol = '';
            $holiday = false;
            $cond = "(date='$tempDate' AND moveDate='') OR moveDate='$tempDate' LIMIT 1";
            $chk = $this->promo_model->select_rowArray('tk', 'holidays_promo', $cond);
            if ($chk) {
                $holiday = true;
                $nH     = '<span class="text-danger">' . $chk['nameHoliday'] . '</span>';
                $cat    = '<span class="text-danger">' . $chk['category'] . '</span>';
                $hol    = $nH . ' [ ' . $cat . ' ]';
                if ($promo_type == 'STATION' && ($promo['al_panglao'] == 'T' || $promo['cdc'] == 'T') && date('m-d', strtotime($tempDate)) == '07-01') {
                    $holiday = false;
                }
            }
            // holiday ********************************************************************

            // offduty of holiday ********************************************************************
            $hd = '';
            $odaHoliday = false;
            if ($holiday) {

                $cond  = "empId='$empId' AND remark!='' AND '$tempDate' BETWEEN dateFrom AND dateTo";
                $sql   = $this->promo_model->select_resultArray('tk', 'assign_mul_sc', $cond);
                foreach ($sql as $ams) {
                    $amsId  = $ams['amsId'];
                    $cond   = ['amsId' => $amsId];
                    $sql    = $this->promo_model->select_rowArray('tk', 'request_odaf', $cond);
                    if ($sql) {
                        $hd = $amsId;
                        $odaHoliday = true;
                    }
                }
            }
            // offduty ********************************************************************               

            // dayoff ********************************************************************     
            $show_do = false;
            $old_do = false;
            $cond = "empId='$empId' AND status='active' AND status2='Approved' AND '$tempDate' BETWEEN dateFrom AND dateTo ORDER BY id DESC LIMIT 1";
            $sql = $this->promo_model->select_resultArray('tk', 'assign_mul_sc', $cond);
            if ($sql) {
                foreach ($sql as $ams) {

                    $amsId  = $ams['amsId'];
                    $cond   = ['amsId' => $amsId, 'empId' => $empId, 'date' => $tempDate];
                    $amd    = $this->promo_model->select_rowArray('tk', 'ass_mul_do', $cond);

                    // $cond   = ['empId' => $empId, 'dateToChange' => $tempDate, 'status' => 'Approved'];
                    $cond   = ['empId' => $empId, 'dateToChange' => $tempDate];
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

                    $cond   = ['empId' => $empId, 'dateNew' => $tempDate];
                    $acd    = $this->promo_model->select_rowArray('tk', 'ass_change_do', $cond);

                    if ($acd) {
                        if ($acd['status'] == 'Approved') {
                            $show_do = true;
                        } else {
                            $show_do = false;
                        }
                    }

                    $cond   = ['empId' => $empId, 'dateToChange' => $tempDate, 'status' => 'Approved'];
                    $acd    = $this->promo_model->select_rowArray('tk', 'ass_change_do', $cond);
                    if ($amd && $acd) {
                        $acdId  = $acd['acdId'];
                        $cond   = ['requestId' => $acdId, 'requestType' => 'CDOS'];
                        $appby  = $this->promo_model->select_rowArray('tk', 'approveby', $cond);
                        if ($appby) {

                            $dateReq = $appby['date'];
                            $timeReq = $appby['time'];
                            $app_details = date("Y-m-d H:i:s", strtotime("$dateReq $timeReq"));
                            $cond   = ['amsId' => $amsId];
                            $rns    = $this->promo_model->select_rowArray('tk', 'request_new_shift', $cond);
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

                    $cond   = ['empId' => $empId, 'dateNew' => $tempDate, 'status' => 'Approved'];
                    $acd    = $this->promo_model->select_rowArray('tk', 'ass_change_do', $cond);
                    if ($amd && $acd) {
                        $acdId  = $acd['acdId'];
                        $cond   = ['requestId' => $acdId, 'requestType' => 'CDOS'];
                        $appby  = $this->promo_model->select_rowArray('tk', 'approveby', $cond);
                        if ($appby) {

                            $dateReq = $appby['date'];
                            $timeReq = $appby['time'];
                            $app_details = date("Y-m-d H:i:s", strtotime("$dateReq $timeReq"));
                            $cond   = ['amsId' => $amsId];
                            $rns    = $this->promo_model->select_rowArray('tk', 'request_new_shift', $cond);
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

                $cond   = ['empId' => $empId, 'dateNew' => $tempDate, 'status' => 'Approved'];
                $acd    = $this->promo_model->select_rowArray('tk', 'ass_change_do', $cond);
                if ($acd) {
                    $show_do = true;
                }

                $cond   = ['empId' => $empId, 'dateToChange' => $tempDate, 'status' => 'Approved'];
                $acd    = $this->promo_model->select_rowArray('tk', 'ass_change_do', $cond);
                if ($acd) {
                    $old_do = true;
                }
            }

            // default day-off if $show_do is false
            if (!$sql &&  !$show_do &&  !$old_do) {
                $days = date('l', strtotime($tempDate));
                $cond = ['empId' => $empId, 'day' => date('l', strtotime($tempDate))];
                $sql = $this->promo_model->select_rowArray('tk', 'assign_day_off', $cond);
                if ($sql) {
                    $show_do = true;
                }
            }
            // dayoff ********************************************************************

            // offduty of dayoff ********************************************************************
            $do = '';
            $odaDayoff = false;
            if ($show_do) {
                $cond  = "empId='$empId' AND remark!='' AND '$tempDate' BETWEEN dateFrom AND dateTo";
                $sql   = $this->promo_model->select_resultArray('tk', 'assign_mul_sc', $cond);
                foreach ($sql as $ams) {
                    $amsId  = $ams['amsId'];
                    $cond   = ['amsId' => $amsId];
                    $sql    = $this->promo_model->select_rowArray('tk', 'request_odaf', $cond);
                    if ($sql) {
                        $do = $amsId;
                        $odaDayoff = true;
                    }
                }
            }
            // offduty ********************************************************************   

            // no schedule ********************************************************************
            $no_schedule = false;
            if ($promo_type == 'ROVING') {
                $cond   = "empId = '$empId' AND dateRendered = '$tempDate' ORDER BY pId DESC";
                $pms    = $this->promo_model->select_rowArray('tk', 'promo_mul_sc', $cond);
                if ($pms) {
                    $nStore = $pms['noStore'];
                    if ($nStore == 0) {
                        $no_schedule = true;
                    }
                } else {
                    $cond   = "empId='$empId' AND day_sched='$day' ORDER BY pdId DESC";
                    $pds    = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
                    if ($pds) {
                        $nStore = $pds['noStore'];
                        if ($nStore == 0) {
                            $no_schedule = true;
                        }
                    }
                }
            } else {

                $cond = "empId='$empId' AND status='active' AND status2='Approved' AND '$tempDate' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
                $amsId = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond)['amsId'];
                if ($amsId) {
                    $cond  = ['empId' => $empId, 'amsId' => $amsId, 'date' => $tempDate];
                    $pns   = $this->promo_model->select_rowArray('tk', 'promo_nosched', $cond);
                    if ($pns) {
                        $no_schedule = true;
                    }
                }
            }
            // no schedule ********************************************************************

            // request checking ********************************************************************
            $cond = ['empId' => $empId, 'effectiveDate' => $tempDate];
            $sqlcdol = $this->promo_model->select_rowArray('tk', 'ass_dayoff_long', $cond);
            $numCDOL = count($sqlcdol);

            $cond = ['empId' => $empId, 'dateToChange' => $tempDate];
            $sqlcdos = $this->promo_model->select_rowArray('tk', 'ass_change_do', $cond);
            $numCDOS = count($sqlcdos);

            //loa
            $cond = "t1.empId='$empId' AND t1.empId=t2.empId AND t2.date='$tempDate' 
            AND '$tempDate' BETWEEN t1.incDate_sF AND t1.incDate_sT AND (status='Approved' OR status='Pending')";
            $sqlloa = $this->promo_model->select_rowArray('tk', 'r_l_absence as t1, rla_day as t2', $cond);
            $numLOA = count($sqlloa);
            //end of loa

            //dwa
            $cond   = "t1.empId='$empId' AND t1.empId=t2.empId AND t2.date='$tempDate' 
            AND '$tempDate' BETWEEN t1.dateFrom AND t1.dateTo AND (status='Approved' OR status='Pending' OR status='Disapproved') ORDER BY t1.dWAId DESC";
            $sqldwa = $this->promo_model->select_rowArray('tk', 'd_w_adjustment as t1, off_date as t2', $cond);
            $numDWA = count($sqldwa);

            if ($numDWA == 0) {
                $cond   = "empId='$empId' AND (type='Failure' OR type='System Error' OR type='Wrong Mode') 
                AND '$tempDate' BETWEEN dateFrom AND dateTo AND (status='Approved' OR status='Pending' OR status='Disapproved') ORDER BY d_w_adjustment.dWAId DESC";
                $sqldwa = $this->promo_model->select_rowArray('tk', 'd_w_adjustment', $cond);
                $numDWA = count($sqldwa);
            }
            //end of dwa

            //eoa
            $cond   = "empId='$empId' AND dateOvertime='$tempDate' AND (status='Approved' OR status='Pending')";
            $sqleoa = $this->promo_model->select_rowArray('tk', 'e_o_authorization', $cond);
            $numEOA = count($sqleoa);
            // var_dump($cond);
            //end of eoa

            //EOP
            $cond   = "emp_id='$empId' AND otDate='$tempDate' AND (status='Approved' OR status='Pending')";
            $sqleop = $this->promo_model->select_rowArray('tk', 'otforpayment', $cond);
            $numEOP = count($sqleop);
            //end of EOP

            //ECA
            $cond   = "empId='$empId' AND cuDate='$tempDate' AND (status='Approved' OR status='Pending')";
            $sqleca = $this->promo_model->select_rowArray('tk', 'coverup_auth', $cond);
            $numECA = count($sqleca);
            //end of ECA

            //EUA
            $cond   = "empId='$empId' AND dateUndertime='$tempDate' AND (status='Approved' OR status='Pending')";
            $sqleua = $this->promo_model->select_rowArray('tk', 'undertime_auth', $cond);
            $numEUA = count($sqleua);
            //end of EUA

            //SS
            $cond   = "empId='$empId' AND dateSched='$tempDate'";
            $sqlss = $this->promo_model->select_rowArray('vsm', 'sched_suspension', $cond);
            $numSS = count($sqlss);
            //end of SS

            $SS = '';
            if ($numSS != 0) {
                $SS = '<span class="badge bg-secondary" title="Suspension">SS</span>';
                // $SS = '<a href="javascript:;" onclick="odaRequest(\'' . $promo_type . '\',\'' . $hd . '\')">' . $span . '</a>';
            }

            $ODAHD = '';
            if ($hd != '') {
                $span = '<span class="badge bg-secondary" title="Holiday">ODA</span>';
                // $ODAHD = '<a href="javascript:;" onclick="odaRequest(\'' . $promo_type . '\',\'' . $hd . '\')">' . $span . '</a>';
                $ODAHD = '<a href="javascript:;" onclick="viewRequest(\'ODA\',\'' . $empId . '\',\'' . $tempDate . '\')">' . $span . '</a>';
            }

            $ODADO = '';
            if ($do != '') {
                if ($do == $hd) {
                    $ODAHD = '';
                }
                $span = '<span class="badge bg-secondary" title="Dayoff">ODA</span>';
                // $ODADO = '<a href="javascript:;" onclick="odaRequest(\'' . $promo_type . '\',\'' . $do . '\')">' . $span . '</a>';
                $ODADO = '<a href="javascript:;" onclick="viewRequest(\'ODA\',\'' . $empId . '\',\'' . $tempDate . '\')">' . $span . '</a>';
            }

            $CDOL = '';
            if ($numCDOL != 0) {
                $span = ($sqlcdol['status'] == 'Approved') ? '<span class="badge bg-primary">CDOL</span>' : '<span class="badge bg-warning">CDOL</span>';
                $CDOL = '<a href="javascript:;" onclick="viewRequest(\'CDOL\',\'' . $empId . '\',\'' . $tempDate . '\')">' . $span . '</a>';
            }

            $CDOS = '';
            if ($numCDOS != 0) {
                $span = ($sqlcdos['status'] == 'Approved') ? '<span class="badge bg-primary">CDOS</span>' : '<span class="badge bg-warning">CDOS</span>';
                if ($sqlcdos['status'] == 'Disapproved') {
                    $span = '<span class="badge bg-danger">CDOS</span>';
                }
                $CDOS = '<a href="javascript:;" onclick="viewRequest(\'CDOS\',\'' . $empId . '\',\'' . $tempDate . '\')">' . $span . '</a>';
            }

            $LOA = '';
            if ($numLOA != 0) {
                $span = ($sqlloa['status'] == 'Approved') ? '<span class="badge bg-primary">LOA</span>' : '<span class="badge bg-warning">LOA</span>';
                $LOA = '<a href="javascript:;" onclick="viewRequest(\'LOA\',\'' . $empId . '\',\'' . $tempDate . '\')">' . $span . '</a>';
            }

            $numSIL = 0;
            if ($numLOA != 0) {
                //sil
                $cond   = "t1.empId='$empId' AND t1.empId=t2.empId AND t2.date='$tempDate' 
                    AND '$tempDate' BETWEEN t1.incDate_sF AND t1.incDate_sT AND (status = 'Approved' OR status = 'Pending') 
                    AND (if_emer_pers LIKE '%Service Incentive Leave%' OR if_emer_pers LIKE '%SIL%' OR if_emer_pers LIKE '%S.I.L%')";
                $sqlsil = $this->promo_model->select_rowArray('tk', 'r_l_absence as t1, rla_day as t2', $cond);
                $numSIL = count($sqlsil);
                //end of sil
                if ($numSIL != 0) {
                    $span = ($sqlsil['status'] == 'Approved') ? '<span class="badge bg-primary">SIL</span>' : '<span class="badge bg-warning">SIL</span>';
                    $LOA = '<a href="javascript:;" onclick="viewRequest(\'LOA\',\'' . $empId . '\',\'' . $tempDate . '\')">' . $span . '</a>';
                }
            }

            $DWA = '';
            if ($numDWA != 0) {
                $span = ($sqldwa['status'] == 'Approved') ? '<span class="badge bg-primary">DWA</span>' : '<span class="badge bg-warning">DWA</span>';
                if ($sqldwa['status'] == 'Disapproved') {
                    $span = '<span class="badge bg-danger">DWA</span>';
                }
                $DWA = '<a href="javascript:;" onclick="viewRequest(\'DWA\',\'' . $empId . '\',\'' . $tempDate . '\')">' . $span . '</a>';
            }

            $EOA = '';
            if ($numEOA != 0) {
                $span = ($sqleoa['status'] == 'Approved') ? '<span class="badge bg-primary">EOA</span>' : '<span class="badge bg-warning">EOA</span>';
                $EOA = '<a href="javascript:;" onclick="viewRequest(\'EOA\',\'' . $empId . '\',\'' . $tempDate . '\')">' . $span . '</a>';
            }

            $EOP = '';
            if ($numEOP != 0) {
                $span = ($sqleop['status'] == 'Approved') ? '<span class="badge bg-primary">EOP</span>' : '<span class="badge bg-warning">EOP</span>';
                $EOP = '<a href="javascript:;" onclick="viewRequest(\'EOP\',\'' . $empId . '\',\'' . $tempDate . '\')">' . $span . '</a>';
            }

            $ECA = '';
            if ($numECA != 0) {
                $span = ($sqleca['status'] == 'Approved') ? '<span class="badge bg-primary">ECA</span>' : '<span class="badge bg-warning">ECA</span>';
                $ECA = '<a href="javascript:;" onclick="viewRequest(\'ECA\',\'' . $empId . '\',\'' . $tempDate . '\')">' . $span . '</a>';
            }

            $EUA = '';
            if ($numEUA != 0) {
                $span = ($sqleua['status'] == 'Approved') ? '<span class="badge bg-primary">EUA</span>' : '<span class="badge bg-warning">EUA</span>';
                $EUA = '<a href="javascript:;" onclick="viewRequest(\'EUA\',\'' . $empId . '\',\'' . $tempDate . '\')">' . $span . '</a>';
            }
            // request checking ********************************************************************

            $csoda = '';
            $cond   = "empId='$empId' AND '$tempDate' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
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
                    $csoda .= "<span class='badge bg-primary mr-1' data-html='true' data-toggle='tooltip' data-placement='top' title = '$dateReq - $timeReq <br>[ $name ] <br>[ $shiftCode = $sched ]'>CS</span>";
                }

                $sq = $this->promo_model->select_rowArray('tk', 'request_odaf', ['amsId' => $amsId]);
                if ($sq) {
                    if ($holiday) {
                        $ODAHD = '';
                        $addODA = 'HOLIDAY';
                    } else if ($show_do) {
                        $ODADO = '';
                        $addODA = 'DAYOFF';
                    } else {
                        $addODA = '';
                    }

                    $dateReq = date("Y/m/d", strtotime($sq['dateReq']));
                    $timeReq = date("h:i A", strtotime($sq['timeReq']));
                    $name = $this->promo_model->select_rowArray('pis', 'employee3', ['emp_id' => $sq['reqBy']])['name'];
                    $csoda .= "<span class='badge bg-danger mr-1' data-html='true' data-toggle='tooltip' data-placement='top' title = '$addODA <br>$dateReq - $timeReq <br>[ $name ] <br>[ $shiftCode = $sched ]'>ODA</span>";
                }
            }

            $tableDate = date('M. d, Y', strtotime($dateFrom));

            if ($holiday && !$odaHoliday && $show_do && !$odaDayoff) {
                echo '<tr>';
                echo    '<td>' . $tableDate . '</td>';
                echo    '<td>' . $day . '</td>';
                echo    '<td colspan="5" class="text-center">' . $hol . ' [ <span class="text-danger">DAY-OFF</span> ]</td>';
                echo    '<td>' . $in3 . '</td>';
                echo    '<td>' . $out3 . '</td>';
                echo    '<td class="text-center">' . $hw . '</td>';
                echo    '<td class="text-center">' . $dw . '</td>';
                echo    '<td class="text-center">' . $ot . '</td>';
                echo    '<td class="text-center">' . $LOA . ' ' . $DWA . ' ' . $EOA . ' ' . $EOP . ' ' . $ECA . ' ' . $EUA . ' ' . $CDOS . ' ' . $CDOL . ' ' . $ODADO . ' ' . $ODAHD . ' ' . $SS . ' ' . $csoda . '</td>';
                echo    '<td class="text-center"><a href="javascript:;" onclick="viewTime(\'' . $bioM . '\',\'' . $dateFrom . '\',\'' . $table . '\',\'' . $allowId . '\')"><i class="fa fa-eye text-primary"></i></a></td>';
                echo '</tr>';
            } else if ($holiday && !$odaHoliday) {
                echo '<tr>';
                echo    '<td>' . $tableDate . '</td>';
                echo    '<td>' . $day . '</td>';
                echo    '<td colspan="5" class="text-center">' . $hol . '</td>';
                echo    '<td>' . $in3 . '</td>';
                echo    '<td>' . $out3 . '</td>';
                echo    '<td class="text-center">' . $hw . '</td>';
                echo    '<td class="text-center">' . $dw . '</td>';
                echo    '<td class="text-center">' . $ot . '</td>';
                echo    '<td class="text-center">' . $LOA . ' ' . $DWA . ' ' . $EOA . ' ' . $EOP . ' ' . $ECA . ' ' . $EUA . ' ' . $CDOS . ' ' . $CDOL . ' ' . $ODADO . ' ' . $ODAHD . ' ' . $SS . ' ' . $csoda . '</td>';
                echo    '<td class="text-center"><a href="javascript:;" onclick="viewTime(\'' . $bioM . '\',\'' . $dateFrom . '\',\'' . $table . '\',\'' . $allowId . '\')"><i class="fa fa-eye text-primary"></i></a></td>';
                echo '</tr>';
            } else if ($show_do && !$odaDayoff) {
                echo '<tr>';
                echo    '<td>' . $tableDate . '</td>';
                echo    '<td>' . $day . '</td>';
                echo    '<td colspan="5" class="text-center text-danger">DAY-OFF</td>';
                echo    '<td>' . $in3 . '</td>';
                echo    '<td>' . $out3 . '</td>';
                echo    '<td class="text-center">' . $hw . '</td>';
                echo    '<td class="text-center">' . $dw . '</td>';
                echo    '<td class="text-center">' . $ot . '</td>';
                echo    '<td class="text-center">' . $LOA . ' ' . $DWA . ' ' . $EOA . ' ' . $EOP . ' ' . $ECA . ' ' . $EUA . ' ' . $CDOS . ' ' . $CDOL . ' ' . $ODADO . ' ' . $ODAHD . ' ' . $SS . ' ' . $csoda . '</td>';
                echo    '<td class="text-center"><a href="javascript:;" onclick="viewTime(\'' . $bioM . '\',\'' . $dateFrom . '\',\'' . $table . '\',\'' . $allowId . '\')"><i class="fa fa-eye text-primary"></i></a></td>';
                echo '</tr>';
            } else if ($no_schedule) {
                echo '<tr>';
                echo    '<td>' . $tableDate . '</td>';
                echo    '<td>' . $day . '</td>';
                echo    '<td colspan="5" class="text-center text-danger">NO SCHEDULE</td>';
                echo    '<td>' . $in3 . '</td>';
                echo    '<td>' . $out3 . '</td>';
                echo    '<td class="text-center">' . $hw . '</td>';
                echo    '<td class="text-center">' . $dw . '</td>';
                echo    '<td class="text-center">' . $ot . '</td>';
                echo    '<td class="text-center">' . $LOA . ' ' . $DWA . ' ' . $EOA . ' ' . $EOP . ' ' . $ECA . ' ' . $EUA . ' ' . $CDOS . ' ' . $CDOL . ' ' . $ODADO . ' ' . $ODAHD . ' ' . $SS . ' ' . $csoda . '</td>';
                echo    '<td class="text-center"><a href="javascript:;" onclick="viewTime(\'' . $bioM . '\',\'' . $dateFrom . '\',\'' . $table . '\',\'' . $allowId . '\')"><i class="fa fa-eye text-primary"></i></a></td>';
                echo '</tr>';
            } else {
                echo '<tr>';
                echo    '<td>' . $tableDate . '</td>';
                echo    '<td>' . $day . '</td>';
                echo    '<td>' . $stores . '</td>';
                echo    '<td>' . $in1 . '</td>';
                echo    '<td>' . $out1 . '</td>';
                echo    '<td>' . $in2 . '</td>';
                echo    '<td>' . $out2 . '</td>';
                echo    '<td>' . $in3 . '</td>';
                echo    '<td>' . $out3 . '</td>';
                echo    '<td class="text-center">' . $hw . '</td>';
                echo    '<td class="text-center">' . $dw . '</td>';
                echo    '<td class="text-center">' . $ot . '</td>';
                echo    '<td class="text-center">' . $LOA . ' ' . $DWA . ' ' . $EOA . ' ' . $EOP . ' ' . $ECA . ' ' . $EUA . ' ' . $CDOS . ' ' . $CDOL . ' ' . $ODADO . ' ' . $ODAHD . ' ' . $SS . ' ' . $csoda . '</td>';
                echo    '<td class="text-center"><a href="javascript:;" onclick="viewTime(\'' . $bioM . '\',\'' . $dateFrom . '\',\'' . $table . '\',\'' . $allowId . '\')"><i class="fa fa-eye text-primary"></i></a></td>';
                echo '</tr>';
            }

            $dateFrom = date('M. d, Y', strtotime("$dateFrom +1 day"));
        }

        // Previous Cut Off
        $df     = date('Y-m-d', strtotime($input['dateFrom']));
        $dt     = date('Y-m-d', strtotime($input['dateTo']));
        $cond   = ['empId' => $empId, 'coDF1' => $df, 'coDF2' => $dt];
        $pdp    = $this->promo_model->select_resultArray('tk', 'perday_previous', $cond);
        $cond   = ['empId' => $empId, 'dateFrom' => $df, 'dateTo' => $dt];
        $pdpa   = $this->promo_model->select_rowArray('tk', 'perday_previous_allow', $cond);
        if ($pdp && $pdpa) {

            echo '<tr>';
            echo '<th colspan="13"><span class="text-theme">Previous Cutoff (Late Requested/Approved)</span></th>';
            echo '</tr>';
            foreach ($pdp as $nf) {

                $dateDuty   = $nf['dateDuty'];
                $dateFrom   = date('M. d, Y', strtotime($nf['dateDuty']));

                $hw_new     = floatval($nf['hw_new']);
                $hw_con     = $nf['hw_con'] + 0;
                $request    = $nf['request'];
                $dayS       = date("D", strtotime($dateDuty));
                $dateD      = date("M. d, Y", strtotime($dateDuty));

                $dutyId = $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';
                $cond   = ['bioMetricId' => $bioM, 'date' => $dateDuty];
                $dtr    = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                $dutyId = $dtr['dutyId'];

                if ($dtr['in1']) {
                    $in1 = date('h:i A', strtotime($dtr['in1']));
                }
                if ($dtr['out1']) {
                    $out1 = date('h:i A', strtotime($dtr['out1']));
                }
                if ($dtr['in2']) {
                    $in2 = date('h:i A', strtotime($dtr['in2']));
                }
                if ($dtr['out2']) {
                    $out2 = date('h:i A', strtotime($dtr['out2']));
                }
                if ($dtr['in3']) {
                    $in3 = date('h:i A', strtotime($dtr['in3']));
                }
                if ($dtr['out3']) {
                    $out3 = date('h:i A', strtotime($dtr['out3']));
                }

                $cond   = "d_w_adjustment.empId = '$empId' AND d_w_adjustment.empId = off_date.empId AND off_date.date = '$dateDuty' AND '$dateDuty' BETWEEN d_w_adjustment.dateFrom AND d_w_adjustment.dateTo AND (status = 'Approved' OR status = 'Pending')";
                $sqldwa = $this->promo_model->select_rowArray('tk', 'd_w_adjustment, off_date', $cond);
                $numDWA = count($sqldwa);

                if ($numDWA == 0) {
                    $cond   = "empId = '$empId' AND (type = 'Failure' OR type = 'System Error' OR type = 'Wrong Mode') AND '$dateDuty' BETWEEN dateFrom AND dateTo AND (status = 'Approved' OR status = 'Pending')";
                    $sqldwa = $this->promo_model->select_rowArray('tk', 'd_w_adjustment', $cond);
                    $numDWA = count($sqldwa);
                }

                $DWA = '';
                if ($numDWA != 0) {
                    $span = ($sqldwa['status'] == 'Approved') ? '<span class="badge bg-primary">DWA</span>' : '<span class="badge bg-warning">DWA</span>';
                    $DWA = '<a href="javascript:;" onclick="viewRequest(\'DWA\',\'' . $empId . '\',\'' . $dateDuty . '\')">' . $span . '</a>';
                }

                $onclick = 'onclick="viewTime(\'' . $bioM . '\',\'' . $dateFrom . '\',\'' . $table . '\',\'' . $allowId . '\')"';
                $action = '<a href="javascript:;" ' . $onclick . '><i class="fa fa-eye text-primary"></i></a>';

                $stores = $this->promo_model->store_entry($bioM, $table, $dateDuty);

                $inC1 = $in1;
                $inC2 = $in2;
                $inC3 = $in3;
                $outC1 = $out1;
                $outC2 = $out2;
                $outC3 = $out3;
                $cond = ['empId' => $empId, 'date' => $dateDuty];
                $od = $this->promo_model->select_rowArray('tk', 'off_date', $cond);
                if ($od) {

                    $inC1 = '';
                    $inC2 = '';
                    $inC3 = '';
                    $outC1 = '';
                    $outC2 = '';
                    $outC3 = '';

                    $ts = $od['timeStarted'];
                    if ($ts == $in1) {
                        $inC1 = date('h:i A', strtotime($in1));
                    }
                    if ($ts == $in2) {
                        $inC2 = date('h:i A', strtotime($in2));
                    }
                    if ($ts == $in3) {
                        $inC3 = date('h:i A', strtotime($in3));
                    }

                    $te = $od['timeEnded'];
                    if ($te == $out1) {
                        $outC1 = date('h:i A', strtotime($out1));
                    }
                    if ($te == $out2) {
                        $outC2 = date('h:i A', strtotime($out2));
                    }
                    if ($te == $out3) {
                        $outC3 = date('h:i A', strtotime($out3));
                    }
                }

                echo '<tr>';
                echo    '<td>' . $dateD . '</td>';
                echo    '<td>' . $dayS . '</td>';
                echo    '<td>' . $stores . '</td>';
                echo    '<td>' . $inC1 . '</td>';
                echo    '<td>' . $outC1 . '</td>';
                echo    '<td>' . $inC2 . '</td>';
                echo    '<td>' . $outC2 . '</td>';
                echo    '<td>' . $inC3 . '</td>';
                echo    '<td>' . $outC3 . '</td>';
                echo    '<td class="text-center">' . $hw_new . '</td>';
                echo    '<td class="text-center">' . $hw_con . '</td>';
                echo    '<td class="text-center">0</td>';
                echo    '<td class="text-center">' . $DWA . '</td>';
                echo    '<td class="text-center">' . $action . '</td>';
                echo '</tr>';
            }
        }

        $cond = ['emp_id' => $empId, 'supDF' => $df, 'supDT' => $dt, 'otDate <' => $df];
        $otp  = $this->promo_model->select_resultArray('tk', 'otforpayment', $cond);
        if ($otp) {

            echo '<tr>';
            echo '<th colspan="13"><span class="text-theme">Previous Cutoff (Over Time)</span></th>';
            echo '</tr>';
            foreach ($otp as $nf) {

                $dateDuty   = $nf['otDate'];
                $dateFrom   = date('M. d, Y', strtotime($nf['otDate']));
                $otApp  = $nf['otApp'] * 1;
                $dayS   = date("D", strtotime($dateDuty));
                $dateD  = date("M. d, Y", strtotime($dateDuty));

                $dutyId = $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';
                $cond   = ['bioMetricId' => $bioM, 'date' => $dateDuty];
                $dtr    = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                $dutyId = $dtr['dutyId'];
                if ($dtr['in1']) {
                    $in1 = date('h:i A', strtotime($dtr['in1']));
                }
                if ($dtr['out1']) {
                    $out1 = date('h:i A', strtotime($dtr['out1']));
                }
                if ($dtr['in2']) {
                    $in2 = date('h:i A', strtotime($dtr['in2']));
                }
                if ($dtr['out2']) {
                    $out2 = date('h:i A', strtotime($dtr['out2']));
                }
                if ($dtr['in3']) {
                    $in3 = date('h:i A', strtotime($dtr['in3']));
                }
                if ($dtr['out3']) {
                    $out3 = date('h:i A', strtotime($dtr['out3']));
                }
                $stores = $this->promo_model->store_entry($bioM, $table, $dateDuty);

                $onclick = 'onclick="viewTime(\'' . $bioM . '\',\'' . $dateFrom . '\',\'' . $table . '\',\'' . $allowId . '\')"';
                $action = '<a href="javascript:;" ' . $onclick . '><i class="fa fa-eye text-primary"></i></a>';

                $cond   = "d_w_adjustment.empId = '$empId' AND d_w_adjustment.empId = off_date.empId AND off_date.date = '$dateDuty' AND '$dateDuty' BETWEEN d_w_adjustment.dateFrom AND d_w_adjustment.dateTo AND (status = 'Approved' OR status = 'Pending')";
                $sqldwa = $this->promo_model->select_rowArray('tk', 'd_w_adjustment, off_date', $cond);
                $numDWA = count($sqldwa);

                if ($numDWA == 0) {
                    $cond   = "empId = '$empId' AND (type = 'Failure' OR type = 'System Error' OR type = 'Wrong Mode') AND '$dateDuty' BETWEEN dateFrom AND dateTo AND (status = 'Approved' OR status = 'Pending')";
                    $sqldwa = $this->promo_model->select_rowArray('tk', 'd_w_adjustment', $cond);
                    $numDWA = count($sqldwa);
                }

                $DWA = '';
                if ($numDWA != 0) {
                    $span = ($sqldwa['status'] == 'Approved') ? '<span class="badge bg-primary">DWA</span>' : '<span class="badge bg-warning" >DWA</span>';
                    $DWA = '<a href="javascript:;" onclick="viewRequest(\'DWA\',\'' . $empId . '\',\'' . $dateDuty . '\')">' . $span . '</a>';
                }

                $cond   = "empId='$empId' AND dateOvertime='$dateDuty' AND (status = 'Approved' OR status = 'Pending')";
                $sqleoa = $this->promo_model->select_rowArray('tk', 'e_o_authorization', $cond);
                $numEOA = count($sqleoa);

                $EOA = '';
                if ($numEOA != 0) {
                    $span = ($sqleoa['status'] == 'Approved') ? '<span class="badge bg-primary">EOA</span>' : '<span class="badge bg-warning" >EOA</span>';
                    $EOA = '<a href="javascript:;" onclick="viewRequest(\'EOA\',\'' . $empId . '\',\'' . $dateDuty . '\')">' . $span . '</a>';
                }

                $cond   = "emp_id='$empId' AND otDate='$dateDuty' AND (status = 'Approved' OR status = 'Pending')";
                $sqleop = $this->promo_model->select_rowArray('tk', 'otforpayment', $cond);
                $numEOP = count($sqleop);

                $EOP = '';
                if ($numEOP != 0) {
                    $span = ($sqleop['status'] == 'Approved') ? '<span class="badge bg-primary">EOP</span>' : '<span class="badge bg-warning" >EOP</span>';
                    $EOP = '<a href="javascript:;" onclick="viewRequest(\'EOP\',\'' . $empId . '\',\'' . $dateDuty . '\')">' . $span . '</a>';
                }

                echo '<tr>';
                echo    '<td>' . $dateD . '</td>';
                echo    '<td>' . $dayS . '</td>';
                echo    '<td>' . $stores . '</td>';
                echo    '<td>' . $in1 . '</td>';
                echo    '<td>' . $out1 . '</td>';
                echo    '<td>' . $in2 . '</td>';
                echo    '<td>' . $out2 . '</td>';
                echo    '<td>' . $in3 . '</td>';
                echo    '<td>' . $out3 . '</td>';
                echo    '<td class="text-center">0</td>';
                echo    '<td class="text-center">0</td>';
                echo    '<td class="text-center">' . $otApp . '</td>';
                echo    '<td class="text-center">' . $DWA . ' ' . $EOA . ' ' . $EOP . '</td>';
                echo    '<td class="text-center">' . $action . '</td>';
                echo '</tr>';
            }
        }

        echo '</table>';
        echo '</div>';
    }

    public function time_entries()
    {
        $input      = $this->input->post(NULL, TRUE);
        $bioM       = $input['bioM'];
        $date       = $input['dateFrom'];
        $allowId    = $input['allowId'];
        $table      = $input['table'];
        $nDate      = date('m-d-Y', strtotime($date));
        echo '<table class="table table-sm table-hover">
                <tr>
                    <th>Date</th>
                    <th>Time</th>
                    <th class="text-center">Mode</th>
                    <th>Terminal</th>
                    <th>Store</th>
                </tr>';

        $noData = '<tr class="text-center"><td colspan="5">No Data</td></tr>';
        $cond = "bioMetricId = '$bioM' AND date = '$nDate' ORDER BY ADDTIME(timeIn,timeOut) ASC";
        $query = $this->promo_model->select_resultArray('tk', $table, $cond);
        if ($query) {
            foreach ($query as $c) {

                $mode       = $c['mode'];
                $timeIn     = $c['timeIn'];
                $timeOut    = $c['timeOut'];

                $dateDuty = date('Y-m-d', strtotime($c['dateDuty']));
                $cond = ['bioMetricId' => $bioM, 'dateDuty' => $dateDuty, 'timeIn' => $timeIn, 'timeOut' => $timeOut];
                if ($table == 'duty_logbox') {
                    $cond['dlId'] = $c['dlId'];
                }

                $store = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond)['store'];

                if ($timeOut == 0) {
                    $time = $timeIn;
                }

                if ($timeIn == 0) {
                    $time = $timeOut;
                }

                $cDate = date('M. d, Y', strtotime($c['dateDuty']));

                echo "<tr>
                        <td>$cDate</td>
                        <td>$time</td>
                        <td class='text-center'>$mode</td>
                        <td>$allowId</td>
                        <td>$store</td>";
                echo "</tr>";
            }
        } else {
            echo $noData;
        }
        echo '</table>';
    }

    public function dtr_requests()
    {
        $input  = $this->input->post(NULL, TRUE);
        $empId  = $input['empId'];
        $req    = $input['request'];
        $date   = $input['date'];
        $render = date('M. d, Y', strtotime($input['date']));
        echo '<div class="row">';
        if ($req == 'LOA') {
            $cond   = "t1.empId = '$empId' AND t1.empId = t2.empId AND t1.rLAId = t2.rLAId AND t2.date = '$date' AND '$date' BETWEEN t1.incDate_sF AND t1.incDate_sT AND (status = 'Approved' OR status = 'Pending')";
            $query  = $this->promo_model->select_resultArray('tk', 'r_l_absence as t1, rla_day as t2', $cond);
            foreach ($query as $key => $sql) {
                $rLAId  = $sql['rLAId'];
                $type   = $sql['typeLeave'];
                $reason = $sql['if_emer_pers'];
                $reqD   = date('M. d, Y', strtotime($sql['dateReq']));
                $remark = $sql['remark'];
                $status = $sql['status'];
                $class  = ($status == 'Approved') ? 'class="text-success"' : 'class="text-warning"';

                echo '<div class="col-md-12">';
                echo '<div class="card card-primary card-outline">';
                echo '<div class="card-body">';

                echo '<h5 class="text-info">LOA Details</h5>';
                echo '<table class="table table-sm table-bordered align-middle" style="width:100%;">
                        <tr>
                            <th>Date Requested</th>
                            <td>' . $reqD . '</td>
                            <th>Date Rendered</th>
                            <td>' . $render . '</td>
                        </tr>
                        <tr>
                            <th>Remark</th>
                            <td>' . $remark . '</td>
                            <th>Status</th>
                            <td ' . $class . '>' . $status . '</td>
                        </tr>
                        <tr>
                            <th>Type</th>
                            <td colspan="3">' . $type . '</td>
                        </tr>
                        <tr>
                            <th>Reason</th>
                            <td colspan="3">' . $reason . '</td>
                        </tr>';
                echo '</table>';

                $cond   = ['empId' => $empId, 'rLAId' => $rLAId];
                $query  = $this->promo_model->select_resultArray('tk', 'rla_day', $cond);
                if ($query) {

                    echo '<h5 class="text-info mt-3">Inclusive Date(s)</h5>';
                    echo '<table class="table table-bordered table-sm" style="width:100%;">
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th>Time Started</th>
                                <th>Time Out</th>
                            </tr>';

                    foreach ($query as $d) {
                        $day = $d['day'];
                        $date_r = $d['date'];
                        $tIn_r = $d['timeStarted'];
                        $tOut_r = $d['timeEnded'];
                        $new_date_r = date("M. d, Y", strtotime($date_r));
                        echo    '<tr>
                                    <td>' . $new_date_r . '</td>
                                    <td>' . $day . '</td>
                                    <td>' . $tIn_r . '</td>
                                    <td>' . $tOut_r . '</td>
                                </tr>';
                    }
                    echo '</table>';
                }

                $cond   = ['requestId' => $rLAId, 'requestType' => 'RLA'];
                $query  = $this->promo_model->select_resultArray('tk', 'approveby', $cond);
                if ($query) {

                    echo '<h5 class="text-info mt-3">Supervisors Approval</h5>';
                    echo '<table class="table table-bordered table-sm" style="width:100%;">
                            <tr>
                                <th>Supervisor</th>
                                <th>Status</th>
                                <th>Date Approved</th>
                            </tr>';

                    foreach ($query as  $val) {

                        $status     = $val['status'];
                        $date       = $val['date'];
                        $time       = $val['time'];
                        $userId     = $val['userId'];
                        @$datime    = "$date | $time";
                        $appName    = $this->promo_model->getName_employee3($userId);
                        if ($status == 'Approved') {
                            $color = 'text-success';
                        } else if ($status == 'Disapproved') {
                            $color = 'text-danger';
                        } else {
                            $color = '';
                        }
                        echo '<tr>
                                <td>' . ucwords(strtolower($appName)) . '</td>
                                <td class="' . $color . '">' . $status . '</td>
                                <td>' . $datime . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                }

                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
        } else if ($req == 'DWA') {
            $cond = "t1.empId = '$empId' AND t1.empId = t2.empId AND t1.adj_errfai_id = t2.btfw_ID AND t2.date = '$date' 
            AND '$date' BETWEEN t1.dateFrom AND t1.dateTo AND (status = 'Approved' OR status = 'Pending' or status = 'Disapproved') ORDER BY t1.dWAId DESC";
            $query  = $this->promo_model->select_resultArray('tk', 'd_w_adjustment as t1, off_date as t2', $cond);
            if ($query) {
                foreach ($query as $key => $sql) {

                    $dWAId      = $sql['dWAId'];
                    $type       = $sql['type'];
                    $reqD       = date("M. d, Y", strtotime($sql['dateReq']));
                    $remark     = $sql['remark'];
                    $status     = $sql['status'];
                    $details    = $sql['details'];
                    $errId      = $sql['adj_errfai_id'];
                    $class  = ($status == 'Approved') ? 'class="text-success"' : 'class="text-warning"';

                    echo '<div class="col-md-12">';
                    echo '<div class="card card-primary card-outline">';
                    echo '<div class="card-body">';

                    echo '<h5 class="text-info">DWA Details</h5>';
                    echo '<table class="table table-sm table-bordered align-middle" style="width:100%;">
                            <tr>
                                <th>Date Requested</th>
                                <td>' . $reqD . '</td>
                                 <th>Remark</th>
                                <td>' . $remark . '</td>
                            </tr>
                            <tr>
                                <th>Type</th>
                                <td>' . $type . '</td>
                                <th>Status</th>
                                <td ' . $class . '>' . $status . '</td>
                            </tr>
                            <tr>
                                <th>Details</th>
                                <td colspan="3">' . $details . '</td>
                            </tr>';
                    echo '</table>';

                    echo '<h5 class="text-info mt-3">Inclusive Date(s)</h5>';
                    echo '<table class="table table-bordered table-sm" style="width:100%;">
                            <tr>
                                <th>Date Rendered</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Day</th>
                            </tr>';

                    $cond = ['empId' => $empId, 'btfw_ID' => $errId];
                    $query = $this->promo_model->select_resultArray('tk', 'off_date', $cond);
                    foreach ($query as $key => $row) {
                        $dateO  = $row['date'];
                        $timeS  = $row['timeStarted'];
                        $timeE  = $row['timeEnded'];
                        $day    = $row['day'];
                        $dateO  = date("M. d, Y", strtotime($dateO));
                        echo '<tr>
                                <td>' . $dateO . '</td>
                                <td>' . $timeS . '</td>
                                <td>' . $timeE . '</td>
                                <td>' . $day . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';

                    $cond   = array('requestId' => $dWAId, 'requestType' => 'DWA');
                    $s      = $this->promo_model->select_resultArray('tk', 'approveby', $cond);
                    if ($s) {
                        echo '<h5 class="text-info mt-3">Supervisors Approval</h5>';
                        echo '<table class="table table-bordered table-sm" style="width:100%;">
                                <tr>
                                    <th>Supervisor</th>
                                    <th>Status</th>
                                    <th>Date Approved</th>
                                </tr>';

                        foreach ($s as  $val) {

                            $status     = $val['status'];
                            $date       = $val['date'];
                            $time       = $val['time'];
                            $userId     = $val['userId'];
                            @$datime    = "$date | $time";
                            $appName    = $this->promo_model->getName_employee3($userId);
                            if ($status == 'Approved') {
                                $color = 'text-success';
                            } else if ($status == 'Disapproved') {
                                $color = 'text-danger';
                            } else {
                                $color = '';
                            }
                            echo '<tr>
                                    <td>' . ucwords(strtolower($appName)) . '</td>
                                    <td class="' . $color . '">' . $status . '</td>
                                    <td>' . $datime . '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    }

                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                $cond   = "empId='$empId' AND (type = 'Failure' OR type = 'System Error' OR type = 'Wrong Mode') AND '$date' BETWEEN dateFrom AND dateTo AND (status = 'Approved' OR status = 'Pending' OR status = 'Disapproved') ORDER BY d_w_adjustment.dWAId DESC";
                $sql    = $this->promo_model->select_rowArray('tk', 'd_w_adjustment', $cond);
                if (!$sql) {
                    $cond = "empId = '$empId' AND ('$date' BETWEEN dateFrom AND dateTo) AND (status = 'Approved' OR status = 'Pending' OR status = 'Disapproved')";
                    $sql = $this->promo_model->select_rowArray('tk', 'd_w_adjustment', $cond);
                }
                $dWAId      = $sql['dWAId'];
                $type       = $sql['type'];
                $reqD       = date("M. d, Y", strtotime($sql['dateReq']));
                $remark     = $sql['remark'];
                $status     = $sql['status'];
                $class      = ($status == 'Approved') ? 'class="text-success"' : 'class="text-warning"';
                $details    = $sql['details'];
                $errId      = $sql['adj_errfai_id'];
                $noDays     = $sql['noDays'];

                $in1_c  = '';
                $out1_c = '';
                $in2_c  = '';
                $out2_c = '';
                $sched  = '';

                $cond   = array('err_fai_id' => $errId);
                $f      = $this->promo_model->select_rowArray('tk', 'dwa_error_failed', $cond);

                $in1    = $f['in1'];
                $out1   = $f['out1'];
                $in2    = $f['in2'];
                $out2   = $f['out2'];


                $in1_V  = '';
                $out1_V = '';
                $in2_V  = '';
                $out2_V = '';

                $cond   = array('err_fai_id' => $errId);
                $v      = $this->promo_model->select_rowArray('tk', 'dwa_err_failed_value', $cond);

                if (count($v) == 1) {

                    $in1_V  = $v['in1'];
                    $out1_V = $v['out1'];

                    $in2_V  = $v['in2'];
                    $out2_V = $v['out2'];
                }


                if ($in1 == "True") {
                    $in1_c = "In1";
                    $in1_V = "$in1_V";
                }

                if ($out1 == "True") {
                    $out1_c = "Out1";
                    $out1_V = "<font color = 'red'>$out1_V</font>";
                }

                if ($in2 == "True") {
                    $in2_c = "In2";
                    $in1_V = "<font color = 'red'>$in2_V</font>";
                }

                if ($out2 == "True") {
                    $out2_c = "Out2";
                    $out1_V = "<font color = 'red'>$out2_V</font>";
                }

                $sched = '(<b><font color = "red">' . $in1_c . ' ' . $out1_c . ' ' . $in2_c . ' ' . $out2_c . '</font></b>)';

                echo '<div class="col-md-12">';
                echo '<h5 class="text-info">DWA Details</h5>';
                echo '<table class="table table-sm table-bordered align-middle" style="width:100%;">
                        <tr>
                            <th>Date Requested</th>
                            <td>' . $reqD . '</td>
                            <th>Date Rendered</th>
                            <td>' . $render . '</td>
                        </tr>
                        <tr>
                            <th>Remark</th>
                            <td>' . $remark . '</td>
                            <th>No. of Days</th>
                            <td>' . $noDays . ' day(s)</td>
                        </tr>
                        <tr>
                            <th>Type</th>
                            <td>' . $type . ' ' . $sched . '</td>
                            <th>Status</th>
                            <td ' . $class . '>' . $status . '</td>
                        </tr>
                        <tr>
                            <th>Details</th>
                            <td colspan="3">' . $details . '</td>
                        </tr>';
                echo '</table>';
                echo '</div>';

                $cond   = array('requestId' => $dWAId, 'requestType' => 'DWA');
                $s      = $this->promo_model->select_resultArray('tk', 'approveby', $cond);
                if ($s) {
                    echo '<div class="col-md-12">';
                    echo '<h5 class="text-info">Supervisors Approval</h5>';
                    echo '<table class="table table-bordered table-sm" style="width:100%;">
                            <tr>
                                <th>Supervisor</th>
                                <th>Status</th>
                                <th>Date Approved</th>
                            </tr>';

                    foreach ($s as  $val) {

                        $status     = $val['status'];
                        $date       = $val['date'];
                        $time       = $val['time'];
                        $userId     = $val['userId'];
                        @$datime    = "$date | $time";
                        $appName    = $this->promo_model->getName_employee3($userId);
                        if ($status == 'Approved') {
                            $color = 'text-success';
                        } else if ($status == 'Disapproved') {
                            $color = 'text-danger';
                        } else {
                            $color = '';
                        }
                        echo '<tr>
                                <td>' . ucwords(strtolower($appName)) . '</td>
                                <td class="' . $color . '">' . $status . '</td>
                                <td>' . $datime . '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                    echo '</div>';
                }
            }
        } else if ($req == 'EOA') {
            $cond   = "empId='$empId' AND dateOvertime='$date' AND (status = 'Approved' OR status = 'Pending')";
            $sql    = $this->promo_model->select_rowArray('tk', 'e_o_authorization', $cond);
            $eOAId  = $sql['eOAId'];
            $reqD   = date("M. d, Y", strtotime($sql['dateReq']));
            $status = $sql['status'];
            $in1    = $sql['ot1st_time'];
            $out1   = $sql['ot1en_time'];
            $ot1    = $sql['ot1_length'];
            $in2    = $sql['ot2st_time'];
            $out2   = $sql['ot2en_time'];
            $ot2    = $sql['ot2_length'];

            $otLength   = $sql['OTLength'];
            $otApp      = $sql['otApp'];
            $remark     = $sql['remark'];
            $purpose    = $sql['purpose'];
            $class  = ($status == 'Approved') ? 'class="text-success"' : 'class="text-warning"';
            echo '<div class="col-md-12">';
            echo '<h5 class="text-info">EOA Details</h5>';
            echo '<table class="table table-sm table-bordered" style="width:100%;">
                    <tr>
                        <th>Date Requested</th>
                        <td>' . $reqD . '</td>
                        <th>Date Overtime</th>
                        <td>' . $render . '</td>
                    </tr>
                    <tr>
                        <th>Time (Before Duty)</th>
                        <td>' . $in1 . ' - ' . $out1 . '</td>
                        <th>O.T. 1 Length</th>
                        <td>' . $ot1 . ' hr(s).</td>
                    </tr>
                    <tr>
                        <th>Time (After Duty)</th>
                        <td>' . $in2 . ' - ' . $out2 . '</td>
                        <th>O.T. 2 Length</th>
                        <td>' . $ot2 . ' hr(s).</td>
                    </tr>
                    <tr>
                        <th>Total OT Requested</th>
                        <td>' . $otLength . '</td>
                        <th>Total OT Approved</th>
                        <td>' . $otApp . '</td>
                    </tr>
                    <tr>
                        <th>Remark</th>
                        <td>' . $remark . '</td>
                        <th>Status</th>
                        <td ' . $class . '>' . $status . '</td>
                    </tr>
                    <tr>
                        <th>Purpose</th>
                        <td colspan="3">' . $purpose . '</td>
                    </tr>';
            echo '</table>';
            echo '</div>';
            $cond   = array('requestId' => $eOAId, 'requestType' => 'EOA');
            $query  = $this->promo_model->select_resultArray('tk', 'approveby', $cond);
            if ($query) {
                echo '<div class="col-md-12">';
                echo '<h5 class="text-info">Supervisors Approval</h5>';
                echo '<table class="table table-bordered table-sm" style="width:100%;">
                        <tr>
                            <th>Supervisor</th>
                            <th>Status</th>
                            <th>Date Approved</th>
                        </tr>';

                foreach ($query as  $val) {

                    $status     = $val['status'];
                    $date       = $val['date'];
                    $time       = $val['time'];
                    $userId     = $val['userId'];
                    @$datime    = "$date | $time";
                    $appName    = $this->promo_model->getName_employee3($userId);
                    if ($status == 'Approved') {
                        $color = 'text-success';
                    } else if ($status == 'Disapproved') {
                        $color = 'text-danger';
                    } else {
                        $color = '';
                    }
                    echo    '<tr>
                                <td>' . ucwords(strtolower($appName)) . '</td>
                                <td class="' . $color . '">' . $status . '</td>
                                <td>' . $datime . '</td>
                            </tr>';
                }
                echo '</table>';
                echo '</div>';
            }
        } else if ($req == 'EOP') {
            $cond   = "emp_id='$empId' AND otDate='$date' AND (status = 'Approved' OR status = 'Pending')";
            $sql        = $this->promo_model->select_rowArray('tk', 'otforpayment', $cond);
            $pay_id     = $sql['pay_id'];
            $otLength   = $sql['otLength'];
            $otApp      = $sql['otApp'];
            $otDis      = $sql['otDisapproved'];
            $otDate     = $sql['otDate'];
            $dateReq    = $sql['dateReq'];
            $status     = $sql['status'];
            $approval   = $sql['status2'];
            if ($sql['status2']) {
                $approval = '[' . $approval . ']';
            }
            $otD        = date('M. d, Y', strtotime($otDate));
            $dateR      = date('M. d, Y', strtotime($dateReq));
            $class  = ($status == 'Approved') ? 'class="text-success"' : 'class="text-warning"';
            echo '<div class="col-md-12">';
            echo '<h5 class="text-info">EOP Details</h5>';
            echo '<table class="table table-sm table-bordered" style="width:100%;">
                    <tr>
                        <th>Date Requested</th>
                        <td>' . $dateR . '</td>
                        <th>Date Overtime</th>
                        <td>' . $otD . '</td>
                    </tr>
                    <tr>
                        <th>Total OT Requested</th>
                        <td>' . $otLength . '</td>
                        <th>Total OT Approved</th>
                        <td>' . $otApp . '</td>
                    </tr>
                    <tr>
                        <th>Total OT Disapproved</th>
                        <td>' . $otDis . '</td>
                        <th>Status</th>
                        <td><span ' . $class . '>' . $status . ' ' . $approval . '</span></td>
                    </tr>';
            echo '</table>';
            echo '</div>';
            $cond   = array('requestId' => $pay_id, 'requestType' => 'EOP');
            $s      = $this->promo_model->select_resultArray('tk', 'approveby', $cond);
            if ($s) {
                echo '<div class="col-md-12">';
                echo '<h5 class="text-info">Supervisors Approval</h5>';
                echo '<table class="table table-bordered table-sm" style="width:100%;">
                        <tr>
                            <th>Supervisor</th>
                            <th>Status</th>
                            <th>Date Approved</th>
                        </tr>';

                foreach ($s as  $val) {

                    $status     = $val['status'];
                    $date       = $val['date'];
                    $time       = $val['time'];
                    $userId     = $val['userId'];
                    @$datime    = "$date | $time";
                    $appName    = $this->promo_model->getName_employee3($userId);
                    if ($status == 'Approved') {
                        $color = 'text-success';
                    } else if ($status == 'Disapproved') {
                        $color = 'text-danger';
                    } else {
                        $color = '';
                    }
                    echo '<tr>
                            <td>' . ucwords(strtolower($appName)) . '</td>
                            <td class="' . $color . '">' . $status . '</td>
                            <td>' . $datime . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                echo '</div>';
            }
        } else if ($req == 'ECA') {
            $cond   = "empId='$empId' AND cuDate='$date' AND (status = 'Approved' OR status = 'Pending')";
            $sql        = $this->promo_model->select_rowArray('tk', 'coverup_auth', $cond);
            $eCAId      = $sql['eCAId'];
            $empId      =  $sql['empId'];
            $cuDate     =  $sql['cuDate'];
            $cuPurpose  =  $sql['cuPurpose'];
            $apl        =  $sql['approval'];
            $dateR      =  $sql['dateReq'];
            $status     =  $sql['status'];
            $dReq       = date('M. d, Y', strtotime($dateR));
            $cDate      = date('M. d, Y', strtotime($cuDate));
            $class  = ($status == 'Approved') ? 'class="text-success"' : 'class="text-warning"';
            echo '<div class="col-md-12">';
            echo '<h5 class="text-info">ECA Details</h5>';
            echo '<table class="table table-sm table-bordered" style="width:100%;">
                    <tr>
                        <th>Date Requested</th>
                        <td>' . $dReq . '</td>
                        <th>Date Cover-Up</th>
                        <td>' . $cDate . '</td>
                    </tr>
                    <tr>
                        <th>Approval</th>
                        <td>' . $apl . '</td>
                        <th>Status</th>
                        <td ' . $class . '>' . $status . '</td>
                    </tr>
                    <tr>
                        <th>Purpose</th>
                        <td colspan="3">' . $cuPurpose . '</td>
                    </tr>';
            echo '</table>';
            echo '</div>';
            $cond = ['eCAId' => $eCAId];
            $cov = $this->promo_model->select_resultArray('tk', 'coverup_auth_ext', $cond);
            if ($cov) {
                echo '<table class="table table-bordered table-sm" style="width:100%;">
                        <tr>
                            <th>Date Overtime</th>
                            <th>OT Approved</th>
                            <th>OT used to Cover-Up</th>
                        </tr>';

                foreach ($cov as  $v) {
                    $dateOT = $v['dateOT'];
                    $usedOT = $v['usedOT'];
                    $cond   = ['empId' => $empId, 'dateOvertime' => $dateOT];
                    $eoa    = $this->promo_model->select_rowArray('tk', 'e_o_authorization', $cond);
                    if (count($eoa) != 0) {

                        $otApp  = $eoa['otApp'];
                        $dOT    = date('M. d, Y', strtotime($dateOT));

                        echo '<tr>
                                <td>' . $dOT . '</td>
                                <td>' . $otApp . '</td>
                                <td>' . $usedOT . ' hour(s)</td>';
                        echo '</tr>';
                    }
                }
                echo '</table>';
            }
            echo '</div>';

            $cond = ['requestId' => $eCAId, 'requestType' => 'ECA'];
            $s = $this->promo_model->select_resultArray('tk', 'approveby', $cond);
            if ($s) {
                echo '<div class="col-md-12">';
                echo '<h5 class="text-info">Supervisors Approval</h5>';
                echo '<table class="table table-bordered table-sm" style="width:100%;">
                        <tr>
                            <th>Supervisor</th>
                            <th>Status</th>
                            <th>Date Approved</th>
                        </tr>';

                foreach ($s as  $val) {

                    $status     = $val['status'];
                    $date       = $val['date'];
                    $time       = $val['time'];
                    $userId     = $val['userId'];
                    @$datime    = "$date | $time";
                    $appName    = $this->promo_model->getName_employee3($userId);
                    if ($status == 'Approved') {
                        $color = 'text-success';
                    } else if ($status == 'Disapproved') {
                        $color = 'text-danger';
                    } else {
                        $color = '';
                    }
                    echo '<tr>
                            <td>' . ucwords(strtolower($appName)) . '</td>
                            <td class="' . $color . '">' . $status . '</td>
                            <td>' . $datime . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                echo '</div>';
            }
        } else if ($req == 'EUA') {
            $cond   = "empId='$empId' AND dateUndertime='$date' AND (status = 'Approved' OR status = 'Pending')";
            $sql    = $this->promo_model->select_rowArray('tk', 'undertime_auth', $cond);
            $eUAId  = $sql['eUAId'];
            $dateUT     = $sql['dateUndertime'];
            $utWhen     = $sql['utWhen'];
            $noHours    = $sql['numHours'];
            $reason     = $sql['reason'];
            $remark     = $sql['remark'];
            $dateReq    = $sql['dateReq'];
            $status     = $sql['status'];

            $dateU = date('M. d, Y', strtotime($dateUT));
            $dateR = date('M. d, Y', strtotime($dateReq));
            $class = ($status == 'Approved') ? 'class="text-success"' : 'class="text-warning"';
            echo '<div class="col-md-12">';
            echo '<h5 class="text-info">EUA Details</h5>';
            echo '<table class="table table-sm table-bordered" style="width:100%;">
                    <tr>
                        <th>Date Requested</th>
                        <td>' . $dateR . '</td>
                        <th>Date Undertime</th>
                        <td>' . $dateU . '</td>
                    </tr>
                    <tr>
                        <th>Undertime Schedule</th>
                        <td>' . $utWhen . '</td>
                        <th>No. of Hours</th>
                        <td>' . $noHours . ' hour(s)</td>
                    </tr>
                    <tr>
                        <th>Remark</th>
                        <td>' . $remark . '</td>
                        <th>Status</th>
                        <td ' . $class . '>' . $status . '</td>
                    </tr>
                    <tr>
                        <th>Reason</th>
                        <td colspan="3">' . $reason . '</td>
                    </tr>';
            echo '</table>';
            echo '</div>';
            $cond = ['requestId' => $eUAId, 'requestType' => 'EUA'];
            $s = $this->promo_model->select_resultArray('tk', 'approveby', $cond);
            if ($s) {
                echo '<div class="col-md-12">';
                echo '<h5 class="text-info">Supervisors Approval</h5>';
                echo '<table class="table table-bordered table-sm" style="width:100%;">
                        <tr>
                            <th>Supervisor</th>
                            <th>Status</th>
                            <th>Date Approved</th>
                        </tr>';

                foreach ($s as  $val) {

                    $status     = $val['status'];
                    $date       = $val['date'];
                    $time       = $val['time'];
                    $userId     = $val['userId'];
                    @$datime    = "$date | $time";
                    $appName    = $this->promo_model->getName_employee3($userId);
                    if ($status == 'Approved') {
                        $color = 'text-success';
                    } else if ($status == 'Disapproved') {
                        $color = 'text-danger';
                    } else {
                        $color = '';
                    }
                    echo '<tr>
                            <td>' . ucwords(strtolower($appName)) . '</td>
                            <td class="' . $color . '">' . $status . '</td>
                            <td>' . $datime . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                echo '</div>';
            }
        } else if ($req == 'CDOS') {
            $cond       = "empId='$empId' AND dateToChange='$date' AND (status = 'Approved' OR status = 'Pending' OR status = 'Disapproved')";
            $sql        = $this->promo_model->select_rowArray('tk', 'ass_change_do', $cond);
            $acdId      = $sql['acdId'];
            $curDay     = $sql['dayToChange'];
            $newDay     = $sql['dayNew'];
            $status     = $sql['status'];
            $details    = $sql['details'];
            $curDate    = date("M. d, Y", strtotime($sql['dateToChange']));
            $newDate    = date("M. d, Y", strtotime($sql['dateNew']));
            $dateReq    = date("M. d, Y", strtotime($sql['dateReq']));
            // $class  = ($status == 'Approved') ? 'class="text-success"' : 'class="text-warning"';
            if ($status == 'Approved') {
                $class = 'class="text-success"';
            } else if ($status == 'Disapproved') {
                $class = 'class="text-danger"';
            } else {
                $class = 'class="text-warning"';
            }
            echo '<div class="col-md-12">';
            echo '<h5 class="text-info">CDOS Details</h5>';
            echo '<table class="table table-sm table-bordered" style="width:100%;">
                    <tr>
                        <th>Current Dayoff</th>
                        <td>' . $curDate . ' (' . $curDay . ')</td>
                        <th>Date Requested</th>
                        <td>' . $dateReq . '</td>
                    </tr>
                    <tr>
                        <th>New Dayoff</th>
                        <td>' . $newDate . ' (' . $newDay . ')</td>
                        <th>Status</th>
                        <td ' . $class . '>' . $status . '</td>
                    </tr>
                    <tr>
                        <th>Reason</th>
                        <td colspan="3">' . $details . '</td>
                    </tr>';
            echo '</table>';
            echo '</div>';
            $cond = ['requestId' => $acdId, 'requestType' => 'CDOS'];
            $s = $this->promo_model->select_resultArray('tk', 'approveby', $cond);
            if ($s) {
                echo '<div class="col-md-12">';
                echo '<h5 class="text-info">Supervisors Approval</h5>';
                echo '<table class="table table-bordered table-sm" style="width:100%;">
                        <tr>
                            <th>Supervisor</th>
                            <th>Status</th>
                            <th>Date Approved</th>
                        </tr>';

                foreach ($s as  $val) {

                    $status     = $val['status'];
                    $date       = $val['date'];
                    $time       = $val['time'];
                    $userId     = $val['userId'];
                    @$datime    = "$date | $time";
                    $appName    = $this->promo_model->getName_employee3($userId);
                    if ($status == 'Approved') {
                        $color = 'text-success';
                    } else if ($status == 'Disapproved') {
                        $color = 'text-danger';
                    } else {
                        $color = '';
                    }
                    echo '<tr>
                            <td>' . ucwords(strtolower($appName)) . '</td>
                            <td class="' . $color . '">' . $status . '</td>
                            <td>' . $datime . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                echo '</div>';
            }
        } else if ($req == 'CDOL') {
            $cond = ['empId' => $empId, 'effectiveDate' => $date];
            $sql = $this->promo_model->select_rowArray('tk', 'ass_dayoff_long', $cond);
            $adlid      = $sql['adlid'];
            $newDay     = $sql['newDay'];
            $curDay     = $sql['curDayOff'];
            $eDate      = date("M. d, Y", strtotime($sql['effectiveDate']));
            $dateReq    = date("M. d, Y", strtotime($sql['dateRequested']));
            $status     = $sql['status'];
            $class  = ($status == 'Approved') ? 'class="text-success"' : 'class="text-warning"';
            echo '<div class="col-md-12">';
            echo '<h5 class="text-info">CDOS Details</h5>';
            echo '<table class="table table-sm table-bordered" style="width:100%;">
                    <tr>
                        <th>Current Default Dayoff</th>
                        <td>' . $curDay . '</td>
                        <th>Date Requested</th>
                        <td>' . $dateReq . '</td>
                    </tr>
                    <tr>
                        <th>New Dayoff</th>
                        <td>' . $newDay . '</td>
                        <th>Effective Date</th>
                        <td>' . $eDate . '</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td colspan="3" ' . $class . '>' . $status . '</td>
                    </tr>';
            echo '</table>';
            echo '</div>';
            $cond = ['requestId' => $adlid, 'requestType' => 'CDOL'];
            $s = $this->promo_model->select_resultArray('tk', 'approveby', $cond);
            if ($s) {
                echo '<div class="col-md-12">';
                echo '<h5 class="text-info">Supervisors Approval</h5>';
                echo '<table class="table table-bordered table-sm" style="width:100%;">
                        <tr>
                            <th>Supervisor</th>
                            <th>Status</th>
                            <th>Date Approved</th>
                        </tr>';

                foreach ($s as  $val) {

                    $status     = $val['status'];
                    $date       = $val['date'];
                    $time       = $val['time'];
                    $userId     = $val['userId'];
                    @$datime    = "$date | $time";
                    $appName    = $this->promo_model->getName_employee3($userId);
                    if ($status == 'Approved') {
                        $color = 'text-success';
                    } else if ($status == 'Disapproved') {
                        $color = 'text-danger';
                    } else {
                        $color = '';
                    }
                    echo '<tr>
                            <td>' . ucwords(strtolower($appName)) . '</td>
                            <td class="' . $color . '">' . $status . '</td>
                            <td>' . $datime . '</td>';
                    echo '</tr>';
                }
                echo '</table>';
                echo '</div>';
            }
        } else if ($req == 'ODA') {

            $cond  = "empId='$empId' AND remark!='' AND '$date' BETWEEN dateFrom AND dateTo";
            $sql   = $this->promo_model->select_resultArray('tk', 'assign_mul_sc', $cond);
            foreach ($sql as $ams) {
                $amsId  = $ams['amsId'];
                $cond   = ['amsId' => $amsId];
                $sql    = $this->promo_model->select_rowArray('tk', 'request_odaf', $cond);
                if ($sql) {

                    $reqD   = date('M. d, Y', strtotime($sql['dateReq']));
                    $sup    = $this->promo_model->getName_employee3($sql['supEmpId']);
                    $reqBy  = $this->promo_model->getName_employee3($sql['reqBy']);

                    $date   = date('M. d, Y', strtotime($sql['dateFrom'])) . ' to ' . date('M. d, Y', strtotime($sql['dateTo']));

                    echo '<div class="col-md-12">';
                    echo '<h5 class="text-info">ODA Details</h5>';
                    echo '<table class="table table-sm table-bordered align-middle" style="width:100%;">
                            <tr>
                                <th>Inclusive Dates</th>
                                <td>' . $date . '</td>
                                <th>Supervisor</th>
                                <td>' . $sup . '</td>
                            </tr>
                            <tr>
                                <th>Date Requested</th>
                                <td>' . $reqD . '</td>
                                <th>Requested By</th>
                                <td>' . $reqBy . '</td>
                            </tr>';
                    echo '</table>';
                    echo '</div>';
                }
            }
        }
        echo '</div>';
    }

    public function dtr_processss()
    {
        $input      = $this->input->post(NULL, TRUE);
        $empId      = $input['empId'];
        // $recordNo   = $input['recordNo'];
        // $bioM       = $input['bioM'];
        $dF         = date('Y-m-d', strtotime($input['dateFrom']));
        $dT         = date('Y-m-d', strtotime($input['dateTo']));
        $cut        = $input['i'];
        $statCut    = $input['statCut'];

        // $this->promo_model->duty_temp_user();
        // $this->promo_model->perday_temp_promo_user();

        // $this->promo_model->drop_table('duty_temp_nald');

        $bioM   = [];
        $cond   = "empId='$empId' AND barcodeId NOT LIKE '%\_%' AND bioMetricId NOT LIKE '%\_%' ORDER BY assignId DESC LIMIT 1";
        $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
        if ($assign) {
            $allowId = $assign['allowId'];
            if ($allowId == "Biometric ID") {
                $bioM[] = $assign['bioMetricId'];
            }
            if ($allowId == "Logbox ID") {
                $bioM[] = $assign['barcodeId'];
            }
        }


        if ($cut == 1) {
        }
    }

    public function printEarlyCO()
    {
        $input  = $this->input->get(NULL, TRUE);

        // Set PDF properties
        $this->ppdf->SetAuthor('Arnaldo L. Parambita Jr.');
        $this->ppdf->SetTitle('Promo DTR');
        $this->ppdf->SetSubject('DTR Entry');
        $this->ppdf->SetKeywords('DTR, promodiser, merchandiser, diser, promo');

        // remove default header/footer
        $this->ppdf->setPrintHeader(false);
        $this->ppdf->setPrintFooter(false);

        // set header and footer fonts
        $this->ppdf->setHeaderFont(array('PDF_FONT_NAME_MAIN', '', 'PDF_FONT_SIZE_MAIN'));
        $this->ppdf->setFooterFont(array('PDF_FONT_NAME_DATA', '', 'PDF_FONT_SIZE_DATA'));

        // set default monospaced font
        $this->ppdf->SetDefaultMonospacedFont('PDF_FONT_MONOSPACED');

        // set margins
        $this->ppdf->SetMargins(12, 12, 12);
        $this->ppdf->SetHeaderMargin('PDF_MARGIN_HEADER');
        $this->ppdf->SetFooterMargin('PDF_MARGIN_FOOTER');

        // set auto page breaks
        $this->ppdf->SetAutoPageBreak(TRUE, 15);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once(dirname(__FILE__) . '/lang/eng.php');
            $this->ppdf->setLanguageArray($l);
        }

        // set font
        $this->ppdf->SetFont('times', '', 10);

        // add a page
        // $this->ppdf->AddPage();

        // set margins
        $this->ppdf->SetMargins(12, 12, 12);
        $this->ppdf->SetHeaderMargin('PDF_MARGIN_HEADER');
        $this->ppdf->SetFooterMargin('PDF_MARGIN_FOOTER');


        $dF     = date('Y-m-d', strtotime($input['dateFrom']));
        $dT     = date('Y-m-d', strtotime($input['dateTo']));
        $dF2    = date('Y-m-d', strtotime($input['dateFrom']));
        $dT2    = date('Y-m-d', strtotime($input['dateTo']));
        $newDF  = date('F d, Y', strtotime($dF));
        $newDT  = date('F d, Y', strtotime($dT));
        $empIds = $input['empIds'];
        foreach ($empIds as $key => $empId) {
            $this->ppdf->AddPage();
            $cond   = ['e.emp_id' => $empId];
            // $promo  = $this->promo_model->promoDetails_wttR($cond, 'employee3', 'promo_record');
            $promo      = $this->promo_model->promoDetailsRow($cond);

            $name       = ucwords(strtolower($promo['name']));
            $company    = $promo['promo_company'];
            $dept       = $promo['promo_department'];
            $promo_type = $promo['promo_type'];

            $bioM = '';
            $cond = "empId = '$empId' ORDER BY assignId DESC LIMIT 1";
            $s = $this->promo_model->select_rowArray('tk', 'assign', $cond);
            $allowId = $s['allowId'];

            if ($allowId == "Biometric ID") {
                $bioM = $s['bioMetricId'];
            }
            if ($allowId == "Logbox ID") {
                $bioM = $s['barcodeId'];
            }

            // Service Incentive Leave 
            $sil    = 0;
            $select = '*';
            $t1     = 'r_l_absence';
            $t2     = 'rla_day';
            $join   = 't1.empId=t2.empId AND t1.rLAId = t2.rLAId';
            $cond   = "t1.empId = '$empId' AND t2.empId = '$empId' AND t1.typeLeave = 'Personal' ";
            $cond   .= "AND (if_emer_pers LIKE '%Service Incentive Leave%' OR if_emer_pers LIKE '%SIL%') ";
            $cond   .= "AND (t2.date BETWEEN '$dF' AND '$dT') AND t1.status = 'Approved'";
            $cond1  = "$cond AND t1.supStatus != 'Previous'";
            $cond2  = "$cond AND t1.supStatus = 'Previous'";
            $sil1   = count($this->promo_model->join_ttjcoA_tk($select, $t1, $t2, $join, null, $cond1, null, null));
            $sil2   = count($this->promo_model->join_ttjcoA_tk($select, $t1, $t2, $join, null, $cond2, null, null));
            $sil    = $sil1 + $sil2;
            // Service Incentive Leave

            $cond   = "type = 'Legal' AND date BETWEEN '$dF' AND '$dT'";
            $sql    = $this->promo_model->select_resultArray('tk', 'holidays_promo', $cond);
            $lh     = count($sql);

            $sh     = 0;
            $sumDW  = 0;
            $sumOT  = 0;
            while (strtotime($dF2) <= strtotime($dT2)) {

                $cond   = ['bioMetricId' => $bioM, 'date' => $dF2];
                $dutyId = $this->promo_model->select_rowArray('tk', 'duty', $cond)['dutyId'];

                $hw = 0;
                $cond = ['dutyId' => $dutyId];
                $perday = $this->promo_model->select_rowArray('tk', 'perday', $cond)['hoursWork'];
                if ($perday) {

                    $res = explode("&", $perday);
                    if ($res[0] == '') {
                        $res[0] = 0;
                    }

                    if (!isset($res[1])) {
                        $res[1] = 0;
                    } else if ($res[1] == '') {
                        $res[1] = 0;
                    }

                    $h = floatval($res[0]) * 1;
                    $m = floatval($res[1]) * 1;

                    $hrs = $h / 8;
                    $min = ($m / 60) / 8;

                    // days worked ********************************************************************
                    $dw = round($hrs + $min, 3);
                    $decimal = explode('.', (string)$dw);
                    if (isset($decimal[1]) && strlen($decimal[1]) > 3) {
                        $dw = number_format($dw, 3, '.', '');
                    }

                    $sumDW += $dw;

                    // hours worked ********************************************************************
                    $min = round(($m / 60), 3);
                    $hw =  round($h + $min, 3);
                    $decimal = explode('.', (string)$h + $min);
                    if (isset($decimal[1]) && strlen($decimal[1]) > 3) {
                        $hw = number_format($h + $min, 3, '.', '');
                    }
                }

                $cond   = ['type' => 'Special', 'date' => $dF2];
                $shChk  = $this->promo_model->select_rowArray('tk', 'holidays_promo', $cond);
                if ($shChk) {
                    $sh += $hw;
                }

                $ot = 0;
                $cond = "emp_id = '$empId' AND otDate = '$dF2' ORDER BY pay_id DESC";
                $otApp = $this->promo_model->select_rowArray('tk', 'otforpayment', $cond)['otApp'];
                if ($otApp && $otApp != '' && $otApp != 0) {
                    $ot = floatval($otApp) * 1;
                }

                $sumOT += $ot;

                $dF2 = date("Y-m-d", strtotime("$dF2 +1 day"));
            }

            // start PDF here
            $this->ppdf->SetFont('times', 'B', 10);
            $this->ppdf->Cell(180, 5, 'Integrated Daily Time Record for Promo & Merchandiser', 0, 1, 'C');
            $this->ppdf->Cell(180, 5, 'For the Period of ' . $newDF . ' to ' . $newDT, 0, 1, 'C');

            $this->ppdf->Ln(2);
            $this->ppdf->SetFont('times', 'B', 9, 'L');

            $this->ppdf->Cell(20, 5, 'Name', 0, 0, 'L');
            $this->ppdf->Cell(90, 5, " : $name", 0, 0, 'L');
            $this->ppdf->SetX(125);
            $this->ppdf->Cell(0, 5, "Days Worked");
            $this->ppdf->SetX(150);
            $this->ppdf->Cell(0, 5, ": $sumDW");
            $this->ppdf->SetX(165);
            $this->ppdf->Cell(0, 5, "Legal Holiday");
            $this->ppdf->SetX(190);
            $this->ppdf->Cell(0, 5, ": $lh");

            $this->ppdf->Ln('5');
            $y1 = $this->ppdf->GetY();
            $this->ppdf->Cell(20, 5, 'Company', 0, 0, 'L');
            $this->ppdf->MultiCell(90, 5, " : $company", 0, 'L');
            $y2 = $this->ppdf->GetY();
            $this->ppdf->SetY($y1);
            $this->ppdf->SetX(125);
            $this->ppdf->Cell(0, 5, "Overtime");
            $this->ppdf->SetX(150);
            $this->ppdf->Cell(0, 5, ": $sumOT");
            $this->ppdf->SetX(165);
            $this->ppdf->Cell(0, 5, "SIL");
            $this->ppdf->SetX(190);
            $this->ppdf->Cell(0, 5, ": $sil");
            $this->ppdf->Ln('5');
            $this->ppdf->SetX(125);
            $this->ppdf->Cell(0, 5, "Special Holiday");
            $this->ppdf->SetX(150);
            $this->ppdf->Cell(0, 5, ": $sh");
            $this->ppdf->SetY($y2);
            $this->ppdf->Cell(20, 5, 'Department', 0, 0, 'L');
            $this->ppdf->Cell(90, 5, " : $dept", 0, 0, 'L');

            $this->ppdf->Ln(2);
            $this->ppdf->Cell(20, 5, '____________________________________________________________________________________________________________________', 0, 0, '');
            $this->ppdf->Ln(5);

            $this->ppdf->Cell(22, 3, 'Date', 0, 0, 'L');
            $this->ppdf->Cell(11, 3, 'Day', 0, 0, 'L');
            $this->ppdf->Cell(20, 3, 'IN1', 0, 0, 'L');
            $this->ppdf->Cell(20, 3, 'OUT1', 0, 0, 'L');
            $this->ppdf->Cell(20, 3, 'IN2', 0, 0, 'L');
            $this->ppdf->Cell(20, 3, 'OUT2', 0, 0, 'L');
            $this->ppdf->Cell(20, 3, 'IN3', 0, 0, 'L');
            $this->ppdf->Cell(20, 3, 'OUT3', 0, 0, 'L');
            $this->ppdf->Cell(12, 3, 'HW', 0, 0, 'L');
            $this->ppdf->Cell(12, 3, 'DW', 0, 0, 'L');
            $this->ppdf->Cell(12, 3, 'OT', 0, 0, 'L');

            $this->ppdf->Ln(2);
            $this->ppdf->Cell(20, 5, '____________________________________________________________________________________________________________________', 0, 0, '');
            $this->ppdf->Ln(5);
            $this->ppdf->SetFont('times', '', 10, 'L');

            $dateFrom   = $input['dateFrom'];
            $dateTo     = $input['dateTo'];
            while (strtotime($dateFrom) <= strtotime($dateTo)) {

                $tempDate   = date('Y-m-d', strtotime($dateFrom));
                $date       = date("m/d/Y", strtotime($dateFrom));
                $day        = date("D", strtotime($dateFrom));

                $dutyId = '';
                $in1    = $out1 = $in2 = $out2 = $in3 = $out3 = '';
                $cond   = "bioMetricId = '$bioM' AND date = '$tempDate' ORDER BY dutyId DESC";
                $dtr    = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                $dutyId = $dtr['dutyId'];
                $in1    = $dtr['in1'];
                $out1   = $dtr['out1'];
                $in2    = $dtr['in2'];
                $out2   = $dtr['out2'];
                $in3    = $dtr['in3'];
                $out3   = $dtr['out3'];

                $in1_store  = $out1_store = $in2_store = $out2_store = $in3_store = $out3_store = '';

                $in1_store  = $this->promo_model->store_entry2($bioM, $allowId, $tempDate, $in1, "I");
                $out1_store = $this->promo_model->store_entry2($bioM, $allowId, $tempDate, $out1, "O");
                $in2_store  = $this->promo_model->store_entry2($bioM, $allowId, $tempDate, $in2, "I");
                $out2_store = $this->promo_model->store_entry2($bioM, $allowId, $tempDate, $out2, "O");
                $in3_store  = $this->promo_model->store_entry2($bioM, $allowId, $tempDate, $in3, "I");
                $out3_store = $this->promo_model->store_entry2($bioM, $allowId, $tempDate, $out3, "O");

                if (empty($out3_store)) {
                    $out3_store = $this->promo_model->store_entry2($bioM, $allowId, date('Y-m-d', strtotime($tempDate . " +1 day")), $out3, "O");
                }

                if (!empty($in1)) {
                    $in1 = date('h:i A', strtotime($in1));
                }

                if (!empty($out1)) {
                    $out1 = date('h:i A', strtotime($out1));
                }

                if (!empty($in2)) {
                    $in2 = date('h:i A', strtotime($in2));
                }

                if (!empty($out2)) {
                    $out2 = date('h:i A', strtotime($out2));
                }

                if (!empty($in3)) {
                    $in3 = date('h:i A', strtotime($in3));
                }

                if (!empty($out3)) {

                    $out3 = date('h:i A', strtotime($out3));
                }

                $cond   = ['bioMetricId' => $bioM, 'dateDuty' => $tempDate];
                $dutyl  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);

                $dw = 0;
                $hw = 0;
                $cond = ['dutyId' => $dutyId];
                $perday = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                if ($perday) {
                    $phw = $perday['hoursWork'];
                    $res = explode("&", $phw);
                    if ($res[0] == '') {
                        $res[0] = 0;
                    }

                    if (!isset($res[1])) {
                        $res[1] = 0;
                    } else if ($res[1] == '') {
                        $res[1] = 0;
                    }

                    $h = floatval($res[0]) * 1;
                    $m = floatval($res[1]) * 1;

                    $hrs = $h / 8;
                    $min = ($m / 60) / 8;

                    // days worked ********************************************************************
                    $dw = round($hrs + $min, 3);
                    $decimal = explode('.', (string)$dw);
                    if (isset($decimal[1]) && strlen($decimal[1]) > 3) {
                        $dw = number_format($dw, 3, '.', '');
                    }

                    // hours worked ********************************************************************
                    $min = round(($m / 60), 3);
                    $hw =  round($h + $min, 3);
                    $decimal = explode('.', (string)$h + $min);
                    if (isset($decimal[1]) && strlen($decimal[1]) > 3) {
                        $hw = number_format($h + $min, 3, '.', '');
                    }
                }

                // overtime ********************************************************************
                $ot = 0;
                $cond = "emp_id = '$empId' AND otDate = '$tempDate' ORDER BY pay_id DESC";
                $otApp = $this->promo_model->select_rowArray('tk', 'otforpayment', $cond)['otApp'];
                if ($otApp && $otApp != '' && $otApp != 0) {
                    $ot = floatval($otApp) * 1;
                }
                // overtime ********************************************************************

                // holiday	********************************************************************
                $holiday = false;
                $cond = "(date='$tempDate' AND moveDate='') OR moveDate='$tempDate' LIMIT 1";
                $chk = $this->promo_model->select_rowArray('tk', 'holidays_promo', $cond);
                if ($chk) {
                    $holiday = true;
                    $hol    = $chk['nameHoliday'] . ' [ ' . $chk['category'] . ' ]';
                }
                // holiday ********************************************************************

                // offduty of holiday ********************************************************************
                $hd = '';
                $odaHoliday = false;
                if ($holiday) {

                    $cond  = "empId = '$empId' AND '$tempDate' BETWEEN dateFrom AND dateTo AND remark != ''";
                    $sql   = $this->promo_model->select_resultArray('tk', 'assign_mul_sc', $cond);
                    foreach ($sql as $ams) {
                        $amsId  = $ams['amsId'];
                        $cond   = ['amsId' => $amsId];
                        $sql    = $this->promo_model->select_rowArray('tk', 'request_odaf', $cond);
                        if ($sql) {
                            $hd = $amsId;
                            $odaHoliday = true;
                        }
                    }
                }
                // offduty ********************************************************************               

                // dayoff ********************************************************************     
                $show_do = false;
                $old_do = false;
                $cond = "empId = '$empId' AND status = 'active' AND status2 = 'Approved' AND '$tempDate' BETWEEN dateFrom AND dateTo";
                $sql = $this->promo_model->select_resultArray('tk', 'assign_mul_sc', $cond);
                if ($sql) {
                    foreach ($sql as $ams) {

                        $amsId  = $ams['amsId'];
                        $cond   = array('amsId' => $amsId, 'empId' => $empId, 'date' => $tempDate);
                        $amd    = $this->promo_model->select_rowArray('tk', 'ass_mul_do', $cond);

                        $cond   = array('empId' => $empId, 'dateToChange' => $tempDate, 'status' => 'Approved');
                        $acd    = $this->promo_model->select_rowArray('tk', 'ass_change_do', $cond);

                        if ($amd && !$acd) {
                            $show_do = true;
                        }

                        if ($amd && $acd) {
                            $acdId  = $acd['acdId'];
                            $cond   = array('requestId' => $acdId, 'requestType' => 'CDOS');
                            $aprby  = $this->promo_model->select_rowArray('tk', 'approveby', $cond);
                            if ($aprby) {
                                $dateReq    = $aprby['date'];
                                $timeReq    = $aprby['time'];
                                $app_details = date("Y-m-d H:i:s", strtotime("$dateReq $timeReq"));

                                $cond   = array('amsId' => $amsId);
                                $rns    = $this->promo_model->select_rowArray('tk', 'request_new_shift', $cond);
                                if ($rns) {
                                    $dateReq = $rns['dateReq'];
                                    $timeReq = $rns['timeReq'];

                                    $req_details    = date("Y-m-d H:i:s", strtotime("$dateReq $timeReq"));
                                    if ($app_details < $req_details) {
                                        $show_do = true;
                                    }
                                }
                            }
                        }

                        $cond   = array('empId' => $empId, 'dateNew' => $tempDate, 'status' => 'Approved');
                        $acd    = $this->promo_model->select_rowArray('tk', 'ass_change_do', $cond);
                        if ($acd) {
                            $acdId  = $acd['acdId'];
                            $cond   = array('requestId' => $acdId, 'requestType' => 'CDOS');
                            $aprby  = $this->promo_model->select_rowArray('tk', 'approveby', $cond);
                            if ($aprby) {
                                $dateReq    = $aprby['date'];
                                $timeReq    = $aprby['time'];
                                $app_details = date("Y-m-d H:i:s", strtotime("$dateReq $timeReq"));

                                $cond   = array('amsId' => $amsId);
                                $rns    = $this->promo_model->select_rowArray('tk', 'request_new_shift', $cond);
                                if ($rns) {
                                    $dateReq = $rns['dateReq'];
                                    $timeReq = $rns['timeReq'];

                                    $req_details    = date("Y-m-d H:i:s", strtotime("$dateReq $timeReq"));
                                    if ($app_details > $req_details) {
                                        $show_do = true;
                                    }
                                }
                            }
                        }
                    }
                } else {

                    $cond   = array('empId' => $empId, 'dateNew' => $tempDate, 'status' => 'Approved');
                    $acd    = $this->promo_model->select_rowArray('tk', 'ass_change_do', $cond);
                    if ($acd) {
                        $show_do = true;
                    }

                    $cond   = array('empId' => $empId, 'dateToChange' => $tempDate, 'status' => 'Approved');
                    $acd    = $this->promo_model->select_rowArray('tk', 'ass_change_do', $cond);
                    if ($acd) {
                        $old_do = true;
                    }
                }

                // default day-off if $show_do is false
                if (!$sql &&  !$show_do &&  !$old_do) {
                    $days = date('l', strtotime($tempDate));
                    $cond = array('empId' => $empId, 'day' => date('l', strtotime($tempDate)));
                    $sql = $this->promo_model->select_rowArray('tk', 'assign_day_off', $cond);
                    if ($sql) {
                        $show_do = true;
                    }
                }
                // dayoff ********************************************************************

                // offduty of dayoff ********************************************************************
                $do = '';
                $odaDayoff = false;
                if ($show_do) {
                    $cond  = "empId = '$empId' AND '$tempDate' BETWEEN dateFrom AND dateTo AND remark != ''";
                    $sql   = $this->promo_model->select_resultArray('tk', 'assign_mul_sc', $cond);
                    foreach ($sql as $ams) {
                        $amsId  = $ams['amsId'];
                        $cond   = array('amsId' => $amsId);
                        $sql    = $this->promo_model->select_rowArray('tk', 'request_odaf', $cond);
                        if ($sql) {
                            $do = $amsId;
                            $odaDayoff = true;
                        }
                    }
                }
                // offduty ********************************************************************   

                // no schedule ********************************************************************
                $no_schedule = false;
                if ($promo_type == 'ROVING') {
                    $nStore = '';
                    $cond   = "empId = '$empId' AND dateRendered = '$tempDate' ORDER BY pId DESC";
                    $pms    = $this->promo_model->select_rowArray('tk', 'promo_mul_sc', $cond);
                    if ($pms) {
                        $nStore = $pms['noStore'];
                    }

                    if ($nStore == '') {
                        $cond   = "empId = '$empId' AND day_sched = '$day' ORDER BY pdId DESC";
                        $pds    = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
                        if ($pds) {
                            $nStore = $pds['noStore'];
                        }

                        if ($nStore == 0) {
                            $no_schedule = true;
                        }
                    } else {
                        if ($nStore == 0) {
                            $no_schedule = true;
                        }
                    }
                } else {

                    $cond = "empId = '$empId' AND status = 'active' AND status2 = 'Approved' AND '$tempDate' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
                    $amsId = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond)['amsId'];
                    if ($amsId) {
                        $cond  = array('empId' => $empId, 'amsId' => $amsId, 'date' => $tempDate);
                        $pns   = $this->promo_model->select_rowArray('tk', 'promo_nosched', $cond);
                        if ($pns) {
                            $no_schedule = true;
                        }
                    }
                }
                // no schedule ********************************************************************

                $loa = false;
                $cond = "r_l_absence.empId = '$empId' AND r_l_absence.empId = rla_day.empId AND rla_day.date = '$tempDate' 
                AND '$tempDate' BETWEEN r_l_absence.incDate_sF AND r_l_absence.incDate_sT AND (status = 'Approved' OR status = 'Pending')";
                $sqlloa = $this->promo_model->select_rowArray('tk', 'r_l_absence, rla_day', $cond);
                if (count($sqlloa) != 0) {
                    $loa = true;
                }

                $sil = false;
                if ($loa) {
                    $cpnd = "r_l_absence.empId = '$empId' AND r_l_absence.empId = rla_day.empId AND rla_day.date = '$tempDate' 
                    AND '$tempDate' BETWEEN r_l_absence.incDate_sF AND r_l_absence.incDate_sT AND (status = 'Approved' OR status = 'Pending') 
                    AND (if_emer_pers LIKE '%Service Incentive Leave%' OR if_emer_pers LIKE '%SIL%' OR if_emer_pers LIKE '%S.I.L%')";
                    $sqlsil = $this->promo_model->select_rowArray('tk', 'r_l_absence, rla_day', $cpnd);
                    if (count($sqlsil) != 0) {
                        $sil = true;
                    }
                }

                if ($holiday && !$odaHoliday) {
                    $this->ppdf->Cell(22, 3, $date, 0, 0);
                    $this->ppdf->Cell(11, 3, $day, 0, 0);
                    $this->ppdf->Cell(120, 3, "$hol", 0, 0, 'C');
                    $this->ppdf->Cell(12, 3, "", 0, 0);
                    $this->ppdf->Cell(12, 3, "", 0, 0);
                    $this->ppdf->Cell(12, 3, "", 0, 0);
                } else if ($show_do && !$odaDayoff) {
                    $this->ppdf->Cell(22, 3, $date, 0, 0);
                    $this->ppdf->Cell(11, 3, $day, 0, 0);
                    $this->ppdf->Cell(120, 3, "DAYOFF", 0, 0, 'C');
                    $this->ppdf->Cell(12, 3, "", 0, 0);
                    $this->ppdf->Cell(12, 3, "", 0, 0);
                    $this->ppdf->Cell(12, 3, "", 0, 0);
                } else if ($no_schedule && !$dutyl) {
                    $this->ppdf->Cell(22, 3, $date, 0, 0);
                    $this->ppdf->Cell(11, 3, $day, 0, 0);
                    $this->ppdf->Cell(120, 3, "NO SCHEDULE", 0, 0, 'C');
                    $this->ppdf->Cell(12, 3, "", 0, 0);
                    $this->ppdf->Cell(12, 3, "", 0, 0);
                    $this->ppdf->Cell(12, 3, "", 0, 0);
                } else if ($loa) {
                    $this->ppdf->Cell(22, 3, $date, 0, 0);
                    $this->ppdf->Cell(11, 3, $day, 0, 0);

                    if ($sqlloa['status'] == 'Pending') {
                        $leave = ($sil) ? 'SIL (Pending)' : 'LOA (Pending)';
                    } else {
                        $leave = ($sil) ? 'SIL' : 'LOA';
                    }

                    if ($in1 == '' && $out1 == '' && $in2 == '' && $out2 == '' && $in3 == '' && $out3 == '') {
                        $this->ppdf->Cell(120, 3, $leave, 0, 0, 'C');
                    } else if ($in1 == '' && $out1 == '' && $in2 == '' && $out2 == '') {
                        $this->ppdf->Cell(80, 3, $leave, 0, 0, 'C');
                    } else {
                        $check = 0;
                        if ($in1 != '') {
                            $this->ppdf->Cell(20, 3, $in1_store . '' . $in1, 0, 0);
                        }
                        if ($out1 != '') {
                            $this->ppdf->Cell(20, 3, $out1_store . '' . $out1, 0, 0);
                        }

                        if ($in1 == '' && $out1 == '') {
                            $check++;
                            $this->ppdf->Cell(40, 3, $leave, 0, 0, 'C');
                        }

                        if ($in2 != '') {
                            $this->ppdf->Cell(20, 3, $in2_store . '' . $in2, 0, 0);
                        }
                        if ($out2 != '') {
                            $this->ppdf->Cell(20, 3, $out2_store . '' . $out2, 0, 0);
                        }

                        if ($in2 == '' && $out2 == '') {
                            $check++;
                            $this->ppdf->Cell(40, 3, $leave, 0, 0, 'C');
                        }

                        if ($in3 != '') {
                            $this->ppdf->Cell(20, 3, $in3_store . '' . $in3, 0, 0);
                        }
                        if ($out3 != '') {
                            $this->ppdf->Cell(20, 3, $out3_store . '' . $out3, 0, 0);
                        }

                        if ($in3 == '' && $out3 == '' &&  $check == 1) {
                            $this->ppdf->Cell(40, 3, '', 0, 0, 'C');
                        } else if ($in3 == '' && $out3 == '' &&  $check == 2) {
                            $this->ppdf->Cell(40, 3, $leave, 0, 0, 'C');
                        } else if ($in3 == '' && $out3 == '') {
                            $this->ppdf->Cell(40, 3, '', 0, 0, 'C');
                        }
                    }

                    $this->ppdf->Cell(12, 3, $hw, 0, 0);
                    $this->ppdf->Cell(12, 3, $dw, 0, 0);
                    $this->ppdf->Cell(12, 3, $ot, 0, 0);
                } else {
                    $this->ppdf->Cell(22, 3, $date, 0, 0);
                    $this->ppdf->Cell(11, 3, $day, 0, 0);
                    $this->ppdf->Cell(20, 3, $in1_store . '' . $in1, 0, 0);
                    $this->ppdf->Cell(20, 3, $out1_store . '' . $out1, 0, 0);
                    $this->ppdf->Cell(20, 3, $in2_store . '' . $in2, 0, 0);
                    $this->ppdf->Cell(20, 3, $out2_store . '' . $out2, 0, 0);
                    $this->ppdf->Cell(20, 3, $in3_store . '' . $in3, 0, 0);
                    $this->ppdf->Cell(20, 3, $out3_store . '' . $out3, 0, 0);
                    $this->ppdf->Cell(12, 3, $hw, 0, 0);
                    $this->ppdf->Cell(12, 3, $dw, 0, 0);
                    $this->ppdf->Cell(12, 3, $ot, 0, 0);
                }

                $this->ppdf->Ln('4');
                $dateFrom = date("Y-m-d", strtotime($dateFrom . "+1 day"));
            }

            $this->ppdf->Cell(20, 5, '________________________________________________________________________________________________________', 0, 0, '');
            $this->ppdf->Ln(5);

            $this->ppdf->SetFont('times', '', 7, 'L');
            $this->ppdf->Cell(20, 5, "LEGEND :");
            $this->ppdf->SetX(25);
            $this->ppdf->Cell(20, 5, "A - Alturas Mall");
            $this->ppdf->SetX(45);
            $this->ppdf->Cell(0, 5, "B - Alturas Talibon");
            $this->ppdf->SetX(70);
            $this->ppdf->Cell(0, 5, "C - Alturas Tubigon");
            $this->ppdf->SetX(95);
            $this->ppdf->Cell(0, 5, "D - Island City Mall");
            $this->ppdf->SetX(119);
            $this->ppdf->Cell(0, 5, "E - Plaza Marcela");
            $this->ppdf->SetX(140);
            $this->ppdf->Cell(0, 5, "H - Alta Citta");
            $this->ppdf->SetX(157);
            $this->ppdf->Cell(0, 5, "I - Alturas Panglao");
            // $this->ppdf->Ln(28);
        }
        //Close and output PDF document
        ob_clean();
        $this->ppdf->Output('promo_dtr.pdf', 'I');

        // $this->ppdf->Output();
    }

    public function viewMasterfile()
    {
        $input      = $this->input->post(NULL, TRUE);
        $empId      = $input['empId'];
        $recordNo   = $input['recordNo'];
        $bioM       = $input['bioM'];
        $statCut    = $input['statCut'];

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
        $shiftCode = $assign['shiftCode'];
        $allowId = $assign['allowId'];

        $bioM = '';
        if ($allowId == 'Biometric ID') {
            $bioM = $assign['bioMetricId'];
        }

        $barcode = '';
        if ($allowId == 'Logbox ID') {
            $barcode = $assign['barcodeId'];
        }

        $e      = $this->promo_model->select_rowArray('tk', 'shiftcodes', ['shiftCode' => $shiftCode]);
        $in1    = $e['1stIn'];
        $out1   = $e['1stOut'];
        $in2    = $e['2ndIn'];
        $out2   = $e['2ndOut'];

        $sched =  $in1 . ' - ' . $out1 . ' | ' . $in2 . ' - ' . $out2;
        if (!$in2) {
            $sched =  $in1 . ' - ' . $out1;
        }

        $add = '';
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
                $del = '<a href="javascript:;" onclick="delpromoId(\'' . $pId . '\',\'delete\')"><i class="fa fa-trash text-danger mr-1"></i></a>';
                $baseId .= $bioM . ' <span class="badge bg-info ml-1 mr-1">' . $row['baseId'] . '</span>';
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

        echo '<div class="row">';
        echo '<div class="col-2 d-flex flex-column align-items-center text-center">
                <img src="' . $link . '" alt="img" class="img-thumbnail" width="100" height="100">
                <span><b>' . $empId . '</b></span>' . $status;
        echo '</div>';
        echo '<div class="col-4">
                <h2 class="lead mb-0"><b>' . $name . '</b></h2>
                <p class="text-muted text-sm mt-0"> ' . $position . ' </p>
                <ul class="ml-4 mb-0 fa-ul text-muted">
                    <li class="small"><span class="fa-li"><i class="fas fa-lg fa-store"></i> </span> ' . $stores . '</li>
                    <li class="small"><span class="fa-li"><i class="fas fa-lg fa-building"></i> </span> ' . $company . '</li>
                    <li class="small"><span class="fa-li"><i class="fas fa-shopping-cart fa-lg"></i> </span> ' . $department . '</li>
                </ul>';
        echo '</div>';
        echo '<div class="col-6">
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
                        <td>' . $baseId . '</td>
                    </tr>
                </table>';
        echo '<div class="mt-2">
                <a href="javascript:;" onclick="defSched(\'' . $empId . '\')" class="btn btn-sm btn-primary"> Default Schedule</a>
                <a href="javascript:;" onclick="schedule_pager(\'' . $load . '\')" class="btn btn-sm btn-primary"> View Schedule</a>';
        echo '</div>';
        echo '</div>';
        echo '<div class="col-12">';
        echo '<hr>';
        echo '<h5 class="text-center"><b>MULTIPLE SHIFT SCHEDULE</b></h5>';
        echo '<table class="table table-hover table-sm align-middle" id="multiSched">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Shift Code</th>
                        <th>Time Schedule</th>
                        <th>Status</th>
                        <th>Request</th>
                        <th>Request Date</th>
                        <th>Dayoff</th>
                    </tr>
                </thead>';

        $filter = date('Y-m-d', strtotime('-5 month'));
        $cond   = "empId='$empId' AND dateFrom >='$filter' ORDER BY dateFrom DESC";
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

            echo '<tr>
                    <td>' . $dFrom . ' - ' . $dTo . '</td>
                    <td>' . $mSC . '</td>
                    <td>' . $sched . '</td>
                    <td>' . $status . '</td>
                    <td>' . $reqStat . '</td>
                    <td>' . $dateReq . '</td>
                    <td>' . $action . '</td>
                </tr>';
        }

        echo '</table>';
        echo '</div>';
        echo '</div>';
    }
}
