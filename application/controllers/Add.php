<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Add extends CI_Controller
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

    public function add_promoCutoff()
    {
        $input      = $this->input->post(NULL, TRUE);
        $startFC    = $input['startFC'];
        $endFC      = $input['endFC'];
        $startSC    = $input['startSC'];
        $endSC      = $input['endSC'];

        $clrFC = $endFC + 3;
        if ($endFC >= 29) {
            $clrFC = '';
        }

        $clrSC = $endSC + 3;
        if ($endSC >= 29) {
            $clrSC = '';
        }

        $cond = "statCut != '' ORDER BY statCut DESC LIMIT 1";
        $statCut = $this->promo_model->select_rowArray('tk', 'promo_schedule', $cond)['statCut'] + 1;

        $cond = ['startFC' => $startFC, 'endFC' => $endFC, 'startSC' => $startSC, 'endSC' => $endSC];
        $check = $this->promo_model->select_rowArray('tk', 'promo_schedule', $cond);
        if ($check) {
            echo json_encode(['message' => 'Cutoff already exist!']);
        } else {
            $data = [
                'startFC'       => $startFC,
                'endFC'         => $endFC,
                'clearingFC'    => $clrFC,
                'startSC'       => $startSC,
                'endSC'         => $endSC,
                'clearingSC'    => $clrSC,
                'noDays'        => '3',
                'extension'     => '2',
                'statCut'       => $statCut,
                'remark'        => 'active',
            ];
            $insert = $this->promo_model->insert('tk', 'promo_schedule', $data);
            if ($insert) {
                echo json_encode(['message' => 'success', 'feedback' => 'Cutoff has been added.']);
            } else {
                echo json_encode(['message' => 'failed']);
            }
        }
    }

    public function add_searchPromo()
    {
        echo '<ul class="list-group">';
        $input  = $this->input->post(NULL, TRUE);
        $string = $input['string'];
        $bus    = $this->promo_model->locate_promo_bu($this->hrCode);
        $query  = $this->promo_model->add_searchPromo($string);
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

    public function add_getStores()
    {
        $input  = $this->input->post(NULL, TRUE);
        $cond   = ['emp_id' => $input['empId'], 'record_no' => $input['recordNo']];
        $row    = $this->promo_model->select_rowArray('pis', 'promo_record', $cond);
        $tk_bus  = $this->promo_model->tk_stores();
        $pis_bus = $this->promo_model->tk_bu();
        echo ' <option value="">---</option>';
        $bus = $this->promo_model->locate_promo_bu($this->hrCode);
        foreach ($bus as $bu) {
            if ($row[$bu['bunit_field']] == 'T') {
                $code = $pis_bus[$bu['bunit_acronym']];

                foreach ($tk_bus as $key => $str) {
                    if ($code == $key) {
                        echo '<option value="' . $key . '">' . $str . '</option>';
                    }
                }
            }
        }
    }

    public function add_manualOt()
    {
        $input  = $this->input->post(NULL, TRUE);
        $empId  = $input['empId'];
        $store  = $input['store'];
        $ot     = $input['overtime'];
        $dateOT = $input['dateOT'];

        $d_a    = date('Y-m-d', strtotime('- 2 day', strtotime($dateOT)));
        $today  = date('Y-m-d');
        $req_by = $this->userId;

        if ((strpos($ot, '.') !== false)) {
            $extHR = explode('.', $ot);
            $xhr = $extHR[0] * 1;
            $xmn = $extHR[1] * 1;
            if ($xmn != '5') {
                echo json_encode(['message' => 'failed']);
                exit;
            }
        }

        if ($ot > 1) {
            $overtime = $ot . ' hours';
        } else {
            $overtime = $ot . ' hour';
        }

        // echo $overtime;
        $tk_bu = $this->promo_model->tk_bu();
        $code  = array_search($store, $tk_bu);

        $cond   = ['bunit_acronym' => $code];
        $bu     = $this->promo_model->select_rowArray('pis', 'locate_promo_business_unit', $cond);
        $scod   = "$bu[cc],$bu[bc]";
        $s      = $store;


        $cond   = ['emp_id' => $empId, 'store_code' => $scod, 'ot_date' => $dateOT];
        $check  = $this->promo_model->select_rowArray('tk', 'ot_table_for_roving', $cond);
        if (!$check) {

            $fields = ['timeReq'];
            $cond   = "empId = '$empId' AND dateOvertime='$dateOT' AND status2 != 'Manual' ORDER BY dateOvertime DESC";
            $query  = $this->promo_model->select_resultArray('tk', 'e_o_authorization', $cond);
            foreach ($query as $row) {

                $data = [];
                foreach ($row as $field => $value) {
                    if (!in_array($field, $fields)) {
                        $data[$field] = $value;
                    }
                }

                $insert = $this->promo_model->insert('tk', 'backup_eoa', $data);
                $cond   = ['empId' => $empId, 'dateOvertime' => $dateOT];
                $delete = $this->promo_model->delete('tk', 'e_o_authorization', $cond);
            }

            $data = [
                'empId'         => $empId,
                'dateOvertime'  => $dateOT,
                'OTLength'      => $overtime,
                'otApp'         => $overtime,
                'otRendered'    => $overtime,
                'leftOt'        => $overtime,
                'remark'        => 'POST',
                'reqBy'         => $req_by,
                'dateReq'       => $today,
                'status'        => 'Approved',
                'status2'       => 'Manual',
            ];

            $otfr = $this->promo_model->insert('tk', 'e_o_authorization', $data);

            $data = [
                'eo_auth_id'    => $otfr,
                'emp_id'        => $empId,
                'ot_date'       => $dateOT,
                'ot_left'       => $overtime,
                'store'         => $s,
                'store_code'    => $scod,
                'status'        => '1st_level',
            ];

            $insert = $this->promo_model->insert('tk', 'ot_table_for_roving', $data);

            echo json_encode(['message' => 'success', 'feedback' => 'Manual Overtime has been added.']);
        } else {

            echo json_encode(['message' => 'Already exists.']);
        }
    }

    public function  manualot_list()
    {
        $data = [];
        $cond   = "emp_id!='' AND emp_id NOT LIKE '%\_%' GROUP BY emp_id";
        $query  = $this->promo_model->select_resultArray('tk', 'ot_table_for_roving', $cond);
        foreach ($query as $key => $row) {
            $empId  = $row['emp_id'];
            $eoaId  = $row['eo_auth_id'];
            $scode  = $row['store_code'];
            $date   = $row['ot_date'];
            $cond   = ['emp_id' => $empId];
            $name   = $this->promo_model->select_rowArray('pis', 'employee3', $cond)['name'];
            if ($name) {

                $action = '<a href="javascript:;" onclick="view_ot(\'' . $empId . '\',\'' . $scode . '\')"><i class="fa fa-list text-md text-primary"></i></a>';

                $col    = [];
                $col[]  = $empId;
                $col[]  = $name;
                $col[]  = $date;
                $col[]  = $action;
                $data[] = $col;
            }
        }

        echo json_encode(['data' => $data]);
    }

    public function  manualot_emp()
    {
        $input  = $this->input->post(NULL, TRUE);
        $empId  = $input['empId'];
        // $sCode  = $input['sCode'];
        $stores = $this->promo_model->tk_stores();

        $data   = [];
        $cond   = ['emp_id' => $empId];
        $query  = $this->promo_model->select_resultArray('tk', 'ot_table_for_roving', $cond);
        foreach ($query as $key => $row) {

            $id     = $row['id'];
            $eoaId  = $row['eo_auth_id'];
            $ot     = $row['ot_left'];
            $store  = $row['store'];
            if ($row['store']) {
                $store  = $stores[$row['store']];
            }

            $date   = date("M. d, Y", strtotime($row['ot_date']));
            $order  = '<span style="display:none;">' . strtotime($row['ot_date']) . '</span>';
            $action = '<a href="javascript:;" onclick="delete_ot(\'' . $empId . '\',\'' . $id . '\',\'' . $eoaId . '\')"><i class="fa fa-trash text-md text-danger"></i></a>';

            $col    = [];
            $col[]  = $order . $date;
            $col[]  = $store;
            $col[]  = $ot;
            $col[]  = $action;
            $data[] = $col;
        }
        echo json_encode(['data' => $data]);
    }

    public function delete_ot()
    {
        $input  = $this->input->post(NULL, TRUE);
        $empId  = $input['empId'];
        $id     = $input['id'];
        $eoaId  = $input['eoaId'];

        $cond = ['empId' => $empId, 'id' => $id, 'eo_auth_id' => $eoaId];
        $del1 = $this->promo_model->delete('tk', 'ot_table_for_roving', $cond);
        $cond = ['empId' => $empId, 'eOAId' => $eoaId];
        $del2 = $this->promo_model->delete('tk', 'e_o_authorization', $cond);
        if ($del1 && $del2) {
            echo json_encode(['message' => 'success', 'feedback' => 'Manual Overtime has been deleted.']);
        } else {
            echo json_encode(['message' => 'failed']);
        }
    }

    public function holidays_promo()
    {
        $data = [];
        $query = $this->promo_model->selectAll('tk', 'holidays_promo');
        foreach ($query as $key => $row) {
            $hId        = $row['hId'];
            $date       = date('M. d, Y', strtotime($row['date']));
            $order      = '<span style="display:none;">' . strtotime($row['date']) . '</span>';
            $moveDate   = date('M. d, Y', strtotime($row['moveDate']));

            $action = '<a href="javascript:;" onclick="enable_previous(\'' . $hId . '\',\'delete\')"><i class="fa fa-trash text-md text-danger"></i></a>';

            $col = [];
            $col[] = $row['nameHoliday'];
            $col[] =  $order . $date;
            $col[] = $moveDate;
            $col[] = $row['type'];
            $col[] = $row['category'];
            $col[] = '';
            $data[] = $col;
        }
        echo json_encode(['data' => $data]);
    }

    public function  add_holiday()
    {
        $input      = $this->input->post(NULL, TRUE);
        $holiday    = $input['holiday'];
        $type       = $input['type'];
        $acr_cat    = $input['category'];
        $date       = date('Y-m-d', strtotime($input['date']));


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

        $cond   = ['date' => $date];
        $check  = $this->promo_model->select_rowArray('tk', 'holidays_promo', $cond);
        if ($check) {
            echo json_encode(['message' => 'Date selected already exist.']);
        } else {

            $data = [
                'date'          => $date,
                'acr_cat'       => $acr_cat,
                'category'      => $category,
                'nameHoliday'   => $holiday,
                'type'          => $type,
                'added_by'      => $this->userId,
                'date_added'    => date('Y-m-d'),
                'time_added'    => date('H:i:s'),
            ];

            $insert = $this->promo_model->insert('tk', 'holidays_promo', $data);
            if ($insert) {
                echo json_encode(['message' => 'success', 'feedback' => 'Holiday has been added.']);
            } else {
                echo json_encode(['message' => 'failed']);
            }
        }
    }

    public function masterfileSearch()
    {
        echo '<ul class="list-group">';
        $input  = $this->input->post(NULL, TRUE);
        $string = trim($input['string']);
        $query  = $this->promo_model->masterfileSearch($string);
        if ($query) {
            foreach ($query as  $row) {
                $name       = ucwords(strtolower($row['name']));
                $empId      = $row['emp_id'];
                $recordNo   = $row['record_no'];
                $type       = $row['promo_type'];

                $onclick = 'onclick="selectMasterfile(\'' . $name . '\',\'' . $empId . '\',\'' . $recordNo . '\',\'' . $type . '\')"';
                echo '<a href="javascript:;" class="list-group-item" ' . $onclick . '>[' . $empId . '] ' . $name . '</a>';
            }
        } else {
            echo '<span class="list-group-item">No Results Found!</span>';
        }
        echo '</ul>';
    }

    public function addMasterfileForm()
    {
        $input = $this->input->post(NULL, TRUE);
        $empId = $input['empId'];
        $recordNo = $input['recordNo'];

        $cond = ['empId' => $empId];
        $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
        if ($assign) {
            $scode  = $assign['shiftCode'];
            $ter    = $assign['allowId'];
            $bar    = $assign['barcodeId'];
            $bio    = $assign['bioMetricId'];

            $cond = ['empId' => $empId];
            $doff = $this->promo_model->select_rowArray('tk', 'assign_day_off', $cond)['day'];

            $sc = '<select class="form-control form-control-sm select2" name="shiftCode" data-placeholder="Select ShiftCode" required>
                    <option value="">---</option>';
            $query = $this->promo_model->selectAll('tk', 'shiftcodes');
            foreach ($query as $row) {
                $shiftC = $row['shiftCode'];
                $fIn    = $row['1stIn'];
                $fOut   = $row['1stOut'];
                $sIn    = $row['2ndIn'];
                $sOut   = $row['2ndOut'];
                $sched = $fIn . ' ' . $fOut;
                if ($sIn && $sOut) {
                    $sched = $fIn . ' ' . $fOut . ' ' . $sIn . ' ' . $sOut;
                }

                $selected = ($scode == $shiftC) ? 'selected' : '';
                $option = '<option value="' . $shiftC . '" ' . $selected . '>' . $shiftC . ' = ' .  $sched . '</option>';
                if (!empty($shiftC)) {
                    $sc .= $option;
                }
            }
            $sc .= '</select>';

            $ter = '<select class="form-control form-control-sm" id="terminal" name="terminal" required>
                        <option value="">---</option>
                        <option value="Logbox ID" ' . ($assign['allowId'] == 'Logbox ID' ? 'selected' : '') . '>Logbox ID</option>
                        <option value="Biometric ID" ' . ($assign['allowId'] == 'Biometric ID' ? 'selected' : '') . '>Biometric ID</option>
                    </select>';

            $bar = '<input type="text" class="form-control form-control-sm" minlength="13" maxlength="13" name="barcodeId" value="' . $assign['barcodeId'] . '" placeholder="0000000000000" required>';
            $bio = '<input type="text" class="form-control form-control-sm" minlength="11" maxlength="11" name="bioMetricId" value="' . $assign['bioMetricId'] . '" placeholder="00000000000" required>';

            $week   = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday',);
            $do =   '<select class="form-control form-control-sm" id="dayoff" name="dayoff">
                        <option value="">---</option>';
            foreach ($week  as $day) {
                $selected = ($doff == $day) ? 'selected' : '';
                $do .= '<option value = "' . $day . '" ' . $selected . '>' . $day . '</option>';
            }
            $do .= '</select>';

            echo '<input type="hidden" name="empId" value="' . $empId . '">';
            echo '<div class="row">';
            echo '<div class="col-md-12">';

            echo '<h6>Assign Table</h6>';
            echo '<table class="table table-sm table-bordered">';
            echo    '<tr>';
            echo    '<th width="20%">Shift Code</th>';
            echo    '<td>' . $sc . '</td>';
            echo    '</tr>';
            echo    '<tr>';
            echo    '<th>Terminal</th>';
            echo    '<td>' . $ter . '</td>';
            echo    '</tr>';
            echo    '<tr>';
            echo    '<th>Barcode ID</th>';
            echo    '<td>' . $bar . '</td>';
            echo    '</tr>';
            echo    '<tr>';
            // echo    '<th>Biometric Id</th>';
            // echo    '<td>' . $bio . '</td>';
            // echo    '</tr>';
            echo    '<tr>';
            echo    '<th>Dayoff</th>';
            echo    '<td>' . $do . '</td>';
            echo    '</tr>';
            echo '</table>';

            echo '<h6>Promo ID Table</h6>';
            echo '<table class="table table-sm table-bordered">';
            echo    '<tr class="">
                        <th width="20%">Base ID</th>
                        <th>Biometric Id</th>';
            echo    '</tr>';

            $cond = "baseId!='' AND status='active' GROUP BY baseId";
            $query = $this->promo_model->select_resultArray('tk', 'promo_id', $cond);
            foreach ($query as $key => $row) {
                $baseId = $row['baseId'];
                if ($baseId != 'fixrite_panglao') {

                    $bar = '<input type="text" class="form-control form-control-sm" minlength = "13" maxlength="13" name="barc" placeholder="0000000000000">';
                    $bio = '<input type="text" class="form-control form-control-sm" minlength = "11" maxlength="11" name="' . $baseId . '" placeholder="00000000000">';
                    $cond = ['empId' => $empId, 'baseId' => $row['baseId']];
                    $check = $this->promo_model->select_rowArray('tk', 'promo_id', $cond);
                    if ($check) {
                        $bar = '<input type="text" class="form-control form-control-sm" minlength="13" maxlength="13" name="barc" value="' . $check['barcodeId'] . '" placeholder="0000000000000">';
                        $bio = '<input type="text" class="form-control form-control-sm" minlength="11" maxlength="11" name="' . $baseId . '" value="' . $check['biometricId'] . '" placeholder="00000000000">';
                    }

                    echo '<tr>';
                    echo '<td>' . $row['baseId'] . '</th>';
                    // echo '<td>' . $bar . '</td>';
                    echo '<td>' . $bio . '</td>';
                    echo '</tr>';
                }
            }
            echo '</table>';

            echo '</div>';
            echo '</div>';
        } else {

            $sc = '<select class="form-control form-control-sm select2" name="shiftCode" data-placeholder="Select ShiftCode" required>
                    <option value="">---</option>';
            $query = $this->promo_model->selectAll('tk', 'shiftcodes');
            foreach ($query as $row) {
                $shiftC = $row['shiftCode'];
                $fIn    = $row['1stIn'];
                $fOut   = $row['1stOut'];
                $sIn    = $row['2ndIn'];
                $sOut   = $row['2ndOut'];
                $sched = $fIn . ' ' . $fOut;
                if ($sIn && $sOut) {
                    $sched = $fIn . ' ' . $fOut . ' ' . $sIn . ' ' . $sOut;
                }

                $option = '<option value="' . $shiftC . '">' . $shiftC . ' = ' .  $sched . '</option>';
                if (!empty($shiftC)) {
                    $sc .= $option;
                }
            }
            $sc .= '</select>';

            $ter = '<select class="form-control form-control-sm" id="terminal" name="terminal" required>
                        <option value="">---</option>
                        <option value="Logbox ID">Logbox ID</option>
                        <option value="Biometric ID">Biometric ID</option>
                    </select>';

            $bar = '<input type="text" class="form-control form-control-sm" minlength="13" maxlength="13" name="barcodeId" placeholder="0000000000000" required>';
            $bio = '<input type="text" class="form-control form-control-sm" minlength="11" maxlength="11" name="bioMetricId" placeholder="00000000000" required>';

            $cond = ['empId' => $empId];
            $doff = $this->promo_model->select_rowArray('tk', 'assign_day_off', $cond)['day'];

            $week   = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday',);
            $do =   '<select class="form-control form-control-sm" id="dayoff" name="dayoff">
                        <option value="">---</option>';
            foreach ($week  as $day) {

                $selected = ($doff == $day) ? 'selected' : '';
                $do .= '<option value = "' . $day . '" ' . $selected . '>' . $day . '</option>';
            }
            $do .= '</select>';
            echo '<input type="hidden" name="empId" value="' . $empId . '">';
            echo '<div class="row">';
            echo '<div class="col-md-12">';

            echo '<h6>Assign Table</h6>';
            echo '<table class="table table-sm table-bordered">';
            echo    '<tr>';
            echo    '<th width="20%">Shift Code</th>';
            echo    '<td>' . $sc . '</td>';
            echo    '</tr>';
            echo    '<tr>';
            echo    '<th>Terminal</th>';
            echo    '<td>' . $ter . '</td>';
            echo    '</tr>';
            echo    '<tr>';
            echo    '<th>Barcode ID</th>';
            echo    '<td>' . $bar . '</td>';
            echo    '</tr>';
            echo    '<tr>';
            echo    '<tr>';
            echo    '<th>Dayoff</th>';
            echo    '<td>' . $do . '</td>';
            echo    '</tr>';
            echo '</table>';

            echo '<h6>Promo ID Table</h6>';
            echo '<table class="table table-sm table-bordered">';
            echo    '<tr class="">
                        <th width="20%">Base ID</th>
                        <th>Biometric Id</th>';
            echo    '</tr>';

            $cond = "baseId!='' AND status='active' GROUP BY baseId";
            $query = $this->promo_model->select_resultArray('tk', 'promo_id', $cond);
            foreach ($query as $key => $row) {
                $baseId = $row['baseId'];
                if ($baseId != 'fixrite_panglao') {
                    $bio = '<input type="text" class="form-control form-control-sm" minlength = "11" maxlength="11" name="' . $baseId . '" placeholder="00000000000">';
                    $cond = ['empId' => $empId, 'baseId' => $baseId];
                    $check = $this->promo_model->select_rowArray('tk', 'promo_id', $cond);
                    if ($check) {
                        $bio = '<input type="text" class="form-control form-control-sm" minlength="11" maxlength="11" name="' . $baseId . '" value="' . $check['biometricId'] . '" placeholder="00000000000">';
                    }

                    echo '<tr>';
                    echo '<td>' . $baseId . '</th>';
                    echo '<td>' . $bio . '</td>';
                    echo '</tr>';
                }
            }
            echo '</table>';

            echo '</div>';
            echo '</div>';
        }
    }

    public function addMasterfile()
    {
        $input  = $this->input->post(NULL, TRUE);
        $empId  = $input['empId'];
        $sc     = $input['shiftCode'];
        $allow  = $input['terminal'];
        $barc   = $input['barcodeId'];
        $day     = $input['dayoff'];

        // $cond = ['emp_id' => $empId];
        // $promo = $this->promo_model->select_rowArray('pis', 'promo_record', $cond);

        // $cond = "baseId!='' AND status='active' GROUP BY baseId";
        // $query = $this->promo_model->select_resultArray('tk', 'promo_id', $cond);
        // $baseIds = array_column($query, 'baseId');

        // $cond = ['status' => 'active'];
        // $strs = $this->promo_model->select_resultArray('tk', 'promo_store_code', $cond);
        // foreach ($strs as $key => $row) {
        //     $baseId = $row['baseId'];
        //     $field = $row['bunit_field'];
        //     if ($promo[$field] == 'T' && in_array($baseId, $baseIds)) {
        //         $cond = ['empId' => $empId, 'baseId' => $baseId];
        //         $check = $this->promo_model->select_rowArray('tk', 'promo_id', $cond);
        //         if ($check) {
        //             $data = ['biometricId' => $input[$baseId], 'barcodeId' => $barc];
        //             $update = $this->promo_model->update('tk', 'promo_id', $cond, $data);
        //         } else {
        //             $data = [
        //                 'empId'         => $empId,
        //                 'baseId'        => $baseId,
        //                 'barcodeId'     => $barc,
        //                 'biometricId'   => $input[$baseId],
        //                 'status'        => 'active'
        //             ];
        //             $insert = $this->promo_model->insert('tk', 'promo_id', $data);
        //         }
        //     }
        // }

        $cond = "baseId!='' AND baseId!='fixrite_panglao' AND status='active' GROUP BY baseId";
        $query = $this->promo_model->select_resultArray('tk', 'promo_id', $cond);
        foreach ($query as $key => $row) {
            $baseId = $row['baseId'];
            if ($input[$baseId]) {
                $cond = ['empId' => $empId, 'baseId' => $baseId];
                $check = $this->promo_model->select_rowArray('tk', 'promo_id', $cond);
                if ($check) {
                    $data = ['biometricId' => $input[$baseId], 'barcodeId' => $barc];
                    $update = $this->promo_model->update('tk', 'promo_id', $cond, $data);
                } else {
                    $data = [
                        'empId'         => $empId,
                        'baseId'        => $baseId,
                        'barcodeId'     => $barc,
                        'biometricId'   => $input[$baseId],
                        'status'        => 'active'
                    ];
                    $insert = $this->promo_model->insert('tk', 'promo_id', $data);
                }
            }
        }

        $cond = ['empId' => $empId];
        $delete = $this->promo_model->delete('tk', 'assign_day_off', $cond);
        $cond = ['empId' => $empId, 'day' => $day];
        $check = $this->promo_model->select_rowArray('tk', 'assign_day_off', $cond);
        if (!$check) {
            $data = ['empId' => $empId, 'day' => $day];
            $insert = $this->promo_model->insert('tk', 'assign_day_off', $data);
        }

        $cond   = ['empId' => $empId];
        $check  = $this->promo_model->select_rowArray('tk', 'assign', $cond);
        if ($check) {

            $cond = ['empId' => $empId];
            $data = ['shiftCode' => $sc, 'allowId' => $allow, 'barcodeId' => $barc];
            $update = $this->promo_model->update('tk', 'assign', $cond, $data);
            if ($update) {
                echo json_encode(['message' => 'success', 'feedback' => 'Masterfile data has been updated.']);
            } else {
                echo json_encode(['message' => 'failed']);
            }
        } else {
            $data = [
                'empId'         => $empId,
                'shiftCode'     => $sc,
                'allowId'       => $allow,
                'barcodeId'     => $barc,
                'dateApplied'    => date('Y-m-d'),
                'timeApplied'    => date('h:i:s A'),
                'added_by'      => $this->userId
            ];
            $insert = $this->promo_model->insert('tk', 'assign', $data);
            if ($insert) {
                echo json_encode(['message' => 'success', 'feedback' => 'Masterfile date has been assigned.']);
            } else {
                echo json_encode(['message' => 'failed']);
            }
        }
    }

    public function defaultSchedForm()
    {
        $input = $this->input->post(NULL, TRUE);
        $empId = $input['empId'];
        $recordNo = $input['recordNo'];
        echo '<input type="hidden" name="empId" value="' . $empId . '">';
        echo '<table class="table table-sm table-bordered align-middle">';
        $cond = ['emp_id' => $empId, 'record_no' => $recordNo];
        $promo = $this->promo_model->select_rowArray('pis', 'promo_record', $cond);
        if ($promo) {
            $type = $promo['promo_type'];

            $stores = [];
            $bus = $this->promo_model->locate_promo_bu($this->hrCode);
            foreach ($bus as $bu) {
                $field = $bu['bunit_field'];
                // $cc = $bu['cc'];
                // $bc = $bu['bc'];
                // $code = "$cc,$bc";
                $code = "";
                $cond = ['business_unit' => $bu['bunit_name']];
                $lbu = $this->promo_model->select_rowArray('pis', 'locate_business_unit', $cond);
                if ($lbu) {
                    $cc = $lbu['company_code'];
                    $bc = $lbu['bunit_code'];
                    $code = "$cc,$bc";
                }
                if ($promo[$field] == 'T') {
                    $stores[$code] = $bu['bunit_name'];
                }
            }

            $scs = $this->promo_model->selectAll('tk', 'shiftcodes');
            echo '<tr class="">
                    <th>Day</th>
                    <th width="30%">Shift Codes</th>
                    <th>No. of Store</th>
                    <th>Store 1</th>
                    <th>Store 2</th>
                    <th>Set Dayoff</th>';
            echo '</tr>';
            $week   = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday',);
            foreach ($week  as $key => $day) {

                $noStore = $store1 = $store2 = $shiftCode = '';
                $cond = ['empId' => $empId, 'day_sched' => $day];
                $pds = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
                if ($pds) {
                    $noStore    = $pds['noStore'];
                    $store1     = $pds['store1'];
                    $store2     = $pds['store2'];
                    $shiftCode  = $pds['shiftCode'];
                }

                $checked = '';
                $disabled = '';
                $cond = ['empId' => $empId, 'day' => $day];
                $check = $this->promo_model->select_rowArray('tk', 'assign_day_off', $cond);
                if ($check) {
                    $checked = 'checked';
                    $disabled = 'disabled';
                }
                $do = '<input type="checkbox" name="dayoff_' . $day . '" value="' . $day . '" onclick="setDayoff(this.value)" ' . $checked . '>';

                $sc = '<select class="form-control form-control-sm select2" name="shiftCode_' . $day . '" data-placeholder="Select Shift Code" ' . $disabled . '>
                        <option value="">Select Shift Code</option>';
                foreach ($scs as $row) {
                    $shiftC = $row['shiftCode'];
                    $fIn    = $row['1stIn'];
                    $fOut   = $row['1stOut'];
                    $sIn    = $row['2ndIn'];
                    $sOut   = $row['2ndOut'];
                    $sched = $fIn . ' ' . $fOut;
                    if ($sIn && $sOut) {
                        $sched = $fIn . ' ' . $fOut . ' ' . $sIn . ' ' . $sOut;
                    }

                    $selected = ($shiftCode == $shiftC) ? 'selected' : '';
                    $option = '<option value="' . $shiftC . '" ' . $selected . '>' . $shiftC . ' = ' .  $sched . '</option>';
                    if (!empty($shiftC)) {
                        $sc .= $option;
                    }
                }
                $sc .= '</select>';

                $ns = '<select name="storeNo_' . $day . '" class="form-control form-control-sm" onchange="showStore(this.value,\'' . $day . '\')" required ' . $disabled . '>
                            <option value="">---</option>
                            <option value="0" ' . ($noStore == '0' ? 'selected' : '') . '>0</option>
                            <option value="1" ' . ($noStore == '1' ? 'selected' : '') . '>1</option>';
                if ($type == 'ROVING') {
                    $ns .=  '<option value="2" ' . ($noStore == '2' ? 'selected' : '') . '>2</option>';
                }
                $ns .= '</select>';

                if ($noStore == 0) {
                    $display1 = 'style="display: none;" disabled';
                    $display2 = 'style="display: none;" disabled';
                } else if ($noStore == 1) {
                    $display1 = "$disabled";
                    $display2 = 'style="display: none;" disabled';
                } else if ($noStore == 2) {
                    $display1 = "$disabled";
                    $display2 = "$disabled";
                } else {
                    $display1 = "$disabled";
                    $display2 = "$disabled";
                }

                if (!$pds) {
                    $display1 = "$disabled";
                    $display2 = "$disabled";
                }

                $str1 = '<select name="store1_' . $day . '" class="form-control form-control-sm" required ' . $display1 . '>
                            <option value="">---</option>';
                foreach ($stores as $key => $store) {
                    $selected = ($store1 == $key) ? 'selected' : '';
                    $str1 .= '<option value="' . $key . '" ' . $selected . '>' . $store . '</option>';
                }
                $str1 .= '</select>';

                $str2 = '<select name="store2_' . $day . '" class="form-control form-control-sm" required ' . $display2 . '>
                            <option value="">---</option>';
                foreach ($stores as $key => $store) {
                    $selected = ($store2 == $key) ? 'selected' : '';
                    $str2 .= '<option value="' . $key . '" ' . $selected . '>' . $store . '</option>';
                }
                $str2 .= '</select>';

                echo '<tr>';
                echo    '<td>' . $day . '<input type="hidden" name="day_' . $day . '" value="' . $day . '" ' . $disabled . '></td>';
                echo    '<td>' . $sc . '</td>';
                echo    '<td>' . $ns . '</td>';
                echo    '<td>' . $str1 . '</td>';
                echo    '<td>' . $str2 . '</td>';
                echo    '<td class="text-center">' . $do . '</td>';
                echo '</tr>';
            }
        }
        echo '</table';
    }

    public function defaultSched()
    {
        $input = $this->input->post(NULL, TRUE);
        $empId = $input['empId'];
        $week = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday',);
        foreach ($week as $key => $day) {

            $d = "day_$day";
            $data = [];
            $data['empId'] = $empId;
            $data['day_sched'] = $day;
            $data['noStore'] = '0';
            $data['store1'] = '';
            $data['store2'] = '';
            $data['shiftCode'] = '246';
            if (isset($input[$d])) {
                $no = "storeNo_$day";
                $data['noStore'] = $input[$no];

                $str1 = "store1_$day";
                if (isset($input[$str1])) {
                    $data['store1'] = $input[$str1];
                }

                $str2 = "store2_$day";
                if (isset($input[$str2])) {
                    $data['store2'] = $input[$str2];
                }

                $sc = "shiftCode_$day";
                if ($input[$sc]) {
                    $data['shiftCode'] = $input[$sc];
                }
            }

            $cond = ['empId' => $empId, 'day_sched' => $day];
            $delete = $this->promo_model->delete('tk', 'promo_def_sched', $cond);
            $check = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
            if (!$check) {
                $insert = $this->promo_model->insert('tk', 'promo_def_sched', $data);
            }

            $do = "dayoff_$day";
            $cond = ['empId' => $empId, 'day' => $day];
            $delete = $this->promo_model->delete('tk', 'assign_day_off', $cond);
            if (isset($input[$do])) {
                $check = $this->promo_model->select_rowArray('tk', 'assign_day_off', $cond);
                if (!$check) {
                    $data = ['empId' => $empId, 'day' => $day];
                    $insert = $this->promo_model->insert('tk', 'assign_day_off', $data);
                }
            }
        }

        if ($insert) {
            echo json_encode(['message' => 'success', 'feedback' => 'Default weekly schedule has been setup.']);
        } else {
            echo json_encode(['message' => 'failed']);
        }
    }

    public function add_searchotdwa()
    {
        echo '<ul class="list-group">';
        $input  = $this->input->post(NULL, TRUE);
        $string = trim($input['string']);
        $query  = $this->promo_model->add_searchotdwa($string);
        if ($query) {
            foreach ($query as  $row) {
                $name       = $row['name'];
                $empId      = $row['emp_id'];
                $recordNo   = $row['record_no'];

                $allowId = $row['allowId'];
                if ($allowId == "Biometric ID") {
                    $bioM = $row['bioMetricId'];
                }
                if ($allowId == "Logbox ID") {
                    $bioM = $row['barcodeId'];
                }

                $onclick = 'onclick="selectotdwa(\'' . $name . '\',\'' . $empId . '\',\'' . $bioM . '\')"';
                echo '<a href="javascript:;" class="list-group-item" ' . $onclick . '>[' . $empId . '] ' . $name . '</a>';
            }
        } else {
            echo '<span class="list-group-item">No Results Found!</span>';
        }
        echo '</ul>';
    }

    public function getShiftdwa()
    {
        $input  = $this->input->post(NULL, TRUE);
        $empId  = $input['empId'];
        $bioM   = $input['bioM'];
        $date   = $input['date'];
        $scode  = $this->getSC($empId, $bioM, $date);
        if ($scode) {
            $cond   = ['shiftCode' => $scode];
            $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
            if ($sc) {
                $fIn    = date("h:i A", strtotime($sc['1stIn']));
                $fOut   = date("h:i A", strtotime($sc['1stOut']));
                $sIn    = date("h:i A", strtotime($sc['2ndIn']));
                $sOut   = date("h:i A", strtotime($sc['2ndOut']));
            }

            $cond   = ['shiftCode' => $scode];
            $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
            if ($scb) {
                $fIn    = date("h:i A", strtotime($scb['1stIn']));
                $fOut   = date("h:i A", strtotime($scb['1stOut']));
                $sIn    = date("h:i A", strtotime($scb['2ndIn']));
                $sOut   = date("h:i A", strtotime($scb['2ndOut']));
            }

            $yest   = date('Y-m-d', strtotime("$date -1 day"));
            $scnyt  = $this->getSC($empId, $bioM, $yest);
            if ($scnyt) {
                $scode = $scnyt;
            }

            $cond   = ['shiftCode' => $scode];
            $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
            if ($sc) {
                $fIny    = date("h:i A", strtotime($sc['1stIn']));
                $fOuty   = date("h:i A", strtotime($sc['1stOut']));
                $sIny    = date("h:i A", strtotime($sc['2ndIn']));
                $sOuty   = date("h:i A", strtotime($sc['2ndOut']));
            }

            $cond   = ['shiftCode' => $scode];
            $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
            if ($scb) {
                $fIny    = date("h:i A", strtotime($scb['1stIn']));
                $fOuty   = date("h:i A", strtotime($scb['1stOut']));
                $sIny    = date("h:i A", strtotime($scb['2ndIn']));
                $sOuty   = date("h:i A", strtotime($scb['2ndOut']));
            }

            $tom    = date('Y-m-d', strtotime("$date +1 day"));
            $scnxt  = $this->getSC($empId, $bioM, $tom);
            if ($scnxt) {
                $scode = $scnxt;
            }

            $cond   = ['shiftCode' => $scode];
            $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
            if ($sc) {
                $fInn    = date("h:i A", strtotime($sc['1stIn']));
                $fOutn   = date("h:i A", strtotime($sc['1stOut']));
                $sInn    = date("h:i A", strtotime($sc['2ndIn']));
                $sOutn   = date("h:i A", strtotime($sc['2ndOut']));
            }

            $cond   = ['shiftCode' => $scode];
            $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
            if ($scb) {
                $fInn    = date("h:i A", strtotime($scb['1stIn']));
                $fOutn   = date("h:i A", strtotime($scb['1stOut']));
                $sInn    = date("h:i A", strtotime($scb['2ndIn']));
                $sOutn   = date("h:i A", strtotime($scb['2ndOut']));
            }

            $data = [
                'in1'   => $fIn,
                'out1'  => $fOut,
                'in2'   => $sIn,
                'out2'  => $sOut,
                'in1y'  => $fIny,
                'out1y' => $fOuty,
                'in2y'  => $sIny,
                'out2y' => $sOuty,
                'in1n'  => $fInn,
                'out1n' => $fOutn,
                'in2n'  => $sInn,
                'out2n' => $sOutn
            ];
        }

        $opt = '<option value="">---</option>';
        $cond = "empId='$empId' AND ('$date' BETWEEN dateFrom AND dateTo)";
        $dwa = $this->promo_model->select_resultArray('tk', 'd_w_adjustment', $cond);
        if ($dwa) {
            foreach ($dwa as $key => $row) {
                $id = $row['dWAId'];
                $type = $row['type'];
                $details = $row['details'];
                $id2 = $row['adj_errfai_id'];

                $name = "$type";
                if ($type != $details) {
                    $name = "$type ($details)";
                }

                $cond = "empId = '$empId' AND type = '$type' AND (btfw_ID = '$id' OR btfw_ID = '$id2')";
                $od = $this->promo_model->select_rowArray('tk', 'off_date', $cond);
                if ($od) {
                    $day = $od['day'];
                    $name = "$name ($day)";
                }

                $opt .= '<option value="' . $id . '">' . $name . '</option>';
            }
            $data['opt'] = $opt;
        }

        if ($scode && $dwa) {
            $data['message'] = 'success';
        } else {
            if (!$scode) {
                $data['message'] = 'No Shiftcode.';
            }
            if (!$dwa) {
                $data['message'] = 'No DWA requested.';
            }
            if (!$scode && !$dwa) {
                $data['message'] = 'No Shiftcode and No DWA requested.';
            }
        }
        echo json_encode($data);
    }

    public function getSC($empId, $bioM, $date)
    {
        $cond = ['emp_id' => $empId];
        $type = $this->promo_model->select_rowArray('pis', 'promo_record', $cond)['promo_type'];

        $shiftCode = '';
        $cond = "bioMetricId='$bioM' OR barcodeId='$bioM'";
        $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
        if ($assign['shiftCode']) {
            $shiftCode = $assign['shiftCode'];
        }

        if ($type == 'ROVING') {
            $day_sched = date('l', strtotime($date));
            $cond = ['empId' => $empId, 'day_sched' => $day_sched];
            $pds = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
            if ($pds) {
                $shiftCode = $pds['shiftCode'];
            }
        }

        $cond = "empId='$empId' AND status='active' AND status2='Approved' AND '$date' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
        $ams = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond);
        if ($ams) {
            $shiftCode = $ams['shiftCode'];
        }

        return $shiftCode;
    }

    public function add_otdwa()
    {
        $input  = $this->input->post(NULL, TRUE);
        $empId  = $input['empId'];
        $bioM   = $input['bioM'];
        $date   = $input['date'];
        $dWAId  = $input['dwa'];
        $scode  = $this->getSC($empId, $bioM, $date);
        $cond   = ['shiftCode' => $scode];
        $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
        if ($sc) {
            $in1    = date("h:i A", strtotime($sc['1stIn']));
            $out1   = date("h:i A", strtotime($sc['1stOut']));
            $in2    = date("h:i A", strtotime($sc['2ndIn']));
            $out2   = date("h:i A", strtotime($sc['2ndOut']));
        }

        if (isset($input['d1'])) {
            $in1 = date("h:i A", strtotime($input['in1']));
            // $out1 = $input['out1'];
        }

        if (isset($input['d2'])) {
            // $in2 = $input['in2'];
            $out2 = date("h:i A", strtotime($input['out2']));
        }

        $in3 = $out3 = '';
        if (isset($input['d3'])) {
            $in3 = date("h:i A", strtotime($input['in3']));
            $out3 = date("h:i A", strtotime($input['out3']));
        }

        $cond = "empId='$empId' AND dWAId='$dWAId' AND ('$date' BETWEEN dateFrom AND dateTo)";
        $dwa = $this->promo_model->select_rowArray('tk', 'd_w_adjustment', $cond);
        if ($dwa) {

            $id = $dwa['adj_errfai_id'];

            $cond = ['empId' => $empId, 'date' => $date, 'err_fai_id' => $id];
            $dwav = $this->promo_model->select_rowArray('tk', 'dwa_err_failed_value', $cond);
            if ($dwav) {
                $data = [
                    'in1'   => $in1,
                    'out1'  => $out1,
                    'in2'   => $in2,
                    'out2'  => $out2,
                    'in3'   => $in3,
                    'out3'  => $out3,
                ];
                $update = $this->promo_model->update('tk', 'dwa_err_failed_value', $cond, $data);
                if ($update) {
                    echo json_encode(['message' => 'success', 'feedback' => 'Overtime DWA has been updated.']);
                } else {
                    echo json_encode(['message' => 'failed']);
                }
            } else {
                $data = [
                    'empId'         => $empId,
                    'empId_sup'     => $this->userId,
                    'date'          => $date,
                    'err_fai_id'    => $id,
                    'in1'           => $in1,
                    'out1'          => $out1,
                    'in2'           => $in2,
                    'out2'          => $out2,
                    'in3'           => $in3,
                    'out3'          => $out3,
                ];
                $insert = $this->promo_model->insert('tk', 'dwa_err_failed_value', $data);
                if ($insert) {
                    echo json_encode(['message' => 'success', 'feedback' => 'Overtime DWA has been added.']);
                } else {
                    echo json_encode(['message' => 'failed']);
                }
            }
        }
    }

    public function getShiftentry()
    {
        $input  = $this->input->post(NULL, TRUE);
        $empId  = $input['empId'];
        $bioM   = $input['bioM'];
        $date   = $input['date'];
        $data   = ['message' => 'No Shiftcode.'];
        $scode  = $this->getSC($empId, $bioM, $date);
        if ($scode) {
            $data = ['message' => 'success'];
            $data['in1e'] = $data['out1e'] = $data['in2e'] = $data['out2e'] = $data['in3e'] = $data['out3e'] = '';
            $cond = ['emp_id' => $empId, 'date_ot' => $date];
            $ent = $this->promo_model->select_rowArray('tk', 'ot_bt_fw_entry_ext', $cond);
            if ($ent) {
                $data['in1e']    = $ent['tStarted1'];
                $data['out1e']   = $ent['tEnded1'];
                $data['in2e']    = $ent['tStarted2'];
                $data['out2e']   = $ent['tEnded2'];
                $data['in3e']    = $ent['tStarted3'];
                $data['out3e']   = $ent['tEnded3'];
            }
            $yesterday  = date('Y-m-d', strtotime("$date -1 day"));
            $today      = date('Y-m-d', strtotime($date));
            $tomorrow   = date('Y-m-d', strtotime("$date +1 day"));
            $dates      = ['yesterday' => $yesterday, 'today' => $today, 'tomorrow' => $tomorrow];
            foreach ($dates as $key => $day) {

                $sc_chk = $this->getSC($empId, $bioM, $day);
                if ($sc_chk) {
                    $scode = $sc_chk;
                }

                $cond = ['shiftCode' => $scode];
                $sc = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
                if ($sc) {
                    $fIn    = date("h:i A", strtotime($sc['1stIn']));
                    $fOut   = date("h:i A", strtotime($sc['1stOut']));
                    $sIn    = date("h:i A", strtotime($sc['2ndIn']));
                    $sOut   = date("h:i A", strtotime($sc['2ndOut']));
                }

                $cond = ['shiftCode' => $scode];
                $scb = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
                if ($scb) {
                    $fIn    = date("h:i A", strtotime($scb['1stIn']));
                    $fOut   = date("h:i A", strtotime($scb['1stOut']));
                    $sIn    = date("h:i A", strtotime($scb['2ndIn']));
                    $sOut   = date("h:i A", strtotime($scb['2ndOut']));
                }

                if ($key == 'yesterday') {
                    $data['in1y']   = $fIn;
                    $data['out1y']  = $fOut;
                    $data['in2y']   = $sIn;
                    $data['out2y']  = $sOut;
                } else if ($key == 'today') {
                    $data['in1']    = $fIn;
                    $data['out1']   = $fOut;
                    $data['in2']    = $sIn;
                    $data['out2']   = $sOut;
                } else {
                    $data['in1n']   = $fIn;
                    $data['out1n']  = $fOut;
                    $data['in2n']   = $sIn;
                    $data['out2n']  = $sOut;
                }
            }
        }

        echo json_encode($data);
    }

    public function add_otentry()
    {
        $input  = $this->input->post(NULL, TRUE);
        $empId  = $input['empId'];
        $date   = $input['date'];

        $time = '';
        $ot1 = 'No';
        $in1 = $out1 = $otl1 = '';
        if (isset($input['d1'])) {
            $ot1    = 'Yes';
            $in1    = date("h:i A", strtotime($input['in1']));
            $out1   = date("h:i A", strtotime($input['out1']));
            $start  = strtotime($input['in1']);
            $end    = strtotime($input['out1']);
            $diff   = $end - $start;
            $h = floor($diff / 3600);
            $m = floor(($diff % 3600) / 60);
            $otl1 = "$h hr(s).";
            $time += $h;
            if ($m >= 30) {
                $time += .5;
                $otl1 = "$h hr(s). & 30 min(s).";
            }
        }

        $ot2 = 'No';
        $in2 = $out2 = $otl2 = '';
        if (isset($input['d2'])) {
            $ot2 = 'Yes';
            $in2 = date("h:i A", strtotime($input['in2']));
            $out2 = date("h:i A", strtotime($input['out2']));
            $start  = strtotime($input['in2']);
            $end    = strtotime($input['out2']);
            $diff   = $end - $start;
            $h = floor($diff / 3600);
            $m = floor(($diff % 3600) / 60);
            $otl2 = "$h hr(s).";
            $time += $h;
            if ($m >= 30) {
                $time += .5;
                $otl2 = "$h hr(s). & 30 min(s).";
            }
        }

        $ot3 = 'No';
        $in3 = $out3 = $otl3 = '';
        if (isset($input['d3'])) {
            $ot3 = 'Yes';
            $in3 = date("h:i A", strtotime($input['in3']));
            $out3 = date("h:i A", strtotime($input['out3']));
            $start  = strtotime($input['in3']);
            $end    = strtotime($input['out3']);
            $diff   = $end - $start;
            $h = floor($diff / 3600);
            $m = floor(($diff % 3600) / 60);
            $otl3 = "$h hr(s).";
            $time += $h;
            if ($m >= 30) {
                $time += .5;
                $otl3 = "$h hr(s). & 30 min(s).";
            }
        }

        $cond = ['empId' => $empId, 'dateOvertime' => $date];
        $chk = $this->promo_model->select_rowArray('tk', 'list_ot', $cond);
        if (!$chk) {
            $cond   = "1 ORDER BY spoId";
            $spo    = $this->promo_model->select_rowArray('tk', 'setup_park_ot', $cond);
            $month3 = $spo['expiration']; // 3 months
            $dateExp = date('Y-m-d', strtotime("$date +$month3"));
            $data = [
                'empId'         => $empId,
                'dateOvertime'  => $date,
                'dateExp'       => $dateExp,
                'tStarted1'     => $in1,
                'tEnded1'       => $out1,
                'otLength1'     => $otl1,
                'tStarted2'     => $in2,
                'tEnded2'       => $out2,
                'otLength2'     => $otl2,
                'tStarted3'     => $in3,
                'tEnded3'       => $out3,
                'otLength3'     => $otl3,
                'status'        => 'AVAILABLE',
            ];
            $list_ot = $this->promo_model->insert('tk', 'list_ot', $data);
        }

        $cond = ['empId' => $empId, 'dateRendered' => $date];
        $chk = $this->promo_model->select_rowArray('tk', 'ot_bt_fw_entry', $cond);
        if ($chk) {
            $data = [];
            $data['adjustment'] = $input['type'];
            $data['assignEmpId'] = $this->userId;
            $data['inputDate'] = date('Y-m-d');
            $data['inputTime'] = date('H:i A');
            $data['purpose'] = $input['purpose'];

            if ($ot1 == 'Yes') {
                $data['ot1'] = $ot1;
            }
            if ($ot2 == 'Yes') {
                $data['ot2'] = $ot2;
            }
            if ($ot3 == 'Yes') {
                $data['ot3'] = $ot3;
            }

            $entry1 = $this->promo_model->update('tk', 'ot_bt_fw_entry', $cond, $data);
        } else {
            $data = [
                'empId'         => $empId,
                'dateRendered'  => $date,
                'adjustment'    => $input['type'],
                'assignEmpId'   => $this->userId,
                'ot1'           => $ot1,
                'ot2'           => $ot2,
                'ot3'           => $ot3,
                'inputDate'     => date('Y-m-d'),
                'inputTime'     => date('H:i A'),
                'purpose'       => $input['purpose'],
            ];
            $entry1 = $this->promo_model->insert('tk', 'ot_bt_fw_entry', $data);
        }

        $cond = ['emp_id' => $empId, 'date_ot' => $date];
        $chk = $this->promo_model->select_rowArray('tk', 'ot_bt_fw_entry_ext', $cond);
        if ($chk) {
            $data = [];
            if ($ot1 == 'Yes') {
                $data['tStarted1']  = $in1;
                $data['tEnded1']    = $out1;
                $data['otLength1']  = $otl1;
            }
            if ($ot2 == 'Yes') {
                $data['tStarted2']  = $in2;
                $data['tEnded2']    = $out2;
                $data['otLength2']  = $otl2;
            }
            if ($ot3 == 'Yes') {
                $data['tStarted3']  = $in3;
                $data['tEnded3']    = $out3;
                $data['otLength3']  = $otl3;
            }

            $cond   = ['empId' => $empId, 'dateOvertime' => $date];
            $update = $this->promo_model->update('tk', 'list_ot', $cond, $data);
            $cond   = ['emp_id' => $empId, 'date_ot' => $date];
            $update = $this->promo_model->update('tk', 'ot_bt_fw_entry_ext', $cond, $data);
            if ($update) {
                $time = '';
                $cond = ['emp_id' => $empId, 'date_ot' => $date];
                $ent = $this->promo_model->select_rowArray('tk', 'ot_bt_fw_entry_ext', $cond);
                $h = $m = 0;
                if ($ent['otLength1']) {
                    $t = explode('&', $ent['otLength1']);
                    $h += (isset($t[0])) ? $t[0] * 1 : 0;
                    $m += (isset($t[1])) ? $t[1] * 1 : 0;
                }

                if ($ent['otLength2']) {
                    $t = explode('&', $ent['otLength2']);
                    $h += (isset($t[0])) ? $t[0] * 1 : 0;
                    $m += (isset($t[1])) ? $t[1] * 1 : 0;
                }

                if ($ent['otLength3']) {
                    $t = explode('&', $ent['otLength3']);
                    $h += (isset($t[0])) ? $t[0] * 1 : 0;
                    $m += (isset($t[1])) ? $t[1] * 1 : 0;
                }

                if ($m == 90) {
                    $h++;
                    $otTotal = "$h hr(s). & 30 min(s).";
                } else if ($m == 60) {
                    $h++;
                    $otTotal = "$h hr(s).";
                } else if ($m == 30) {
                    $otTotal = "$h hr(s). & 30 min(s).";
                } else {
                    $otTotal = "$h hr(s).";
                }

                $data       = ['totalOT' => $otTotal, 'otLeft' => $otTotal,];
                $cond       = ['empId' => $empId, 'dateOvertime' => $date];
                $list_ot    = $this->promo_model->update('tk', 'list_ot', $cond, $data);
                $cond       = ['emp_id' => $empId, 'date_ot' => $date];
                $entry2     = $this->promo_model->update('tk', 'ot_bt_fw_entry_ext', $cond, $data);
            }
        } else {
            $h = floor($time);
            $m = ($time - $h) * 60;
            $otTotal = "$h hr(s).";
            if ($m >= 30) {
                $otTotal = "$h hr(s). & 30 min(s).";
            }

            $data       = ['totalOT' => $otTotal, 'otLeft' => $otTotal,];
            $cond       = ['empId' => $empId, 'dateOvertime' => $date];
            $list_ot    = $this->promo_model->update('tk', 'list_ot', $cond, $data);

            $data = [
                'emp_id'    => $empId,
                'date_ot'   => $date,
                'tStarted1' => $in1,
                'tEnded1'   => $out1,
                'otLength1' => $otl1,
                'tStarted2' => $in2,
                'tEnded2'   => $out2,
                'otLength2' => $otl2,
                'tStarted3' => $in3,
                'tEnded3'   => $out3,
                'otLength3' => $otl3,
                'totalOT'   => $otTotal,
                'otLeft'    => $otTotal,
            ];
            $entry2 = $this->promo_model->insert('tk', 'ot_bt_fw_entry_ext', $data);
        }

        if ($list_ot && $entry1 && $entry2) {
            echo json_encode(['message' => 'success', 'feedback' => 'Overtime Entry has been added.']);
        } else {
            echo json_encode(['message' => 'failed']);
        }
    }
}
