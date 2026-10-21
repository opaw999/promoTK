<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Process extends CI_Controller
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

    public function processMenu()
    {
        $input = $this->input->post(NULL, TRUE);
        $process = $input['process'];
        if ($process == 'single') {
            echo '<div class="row ">
                    <div class="col-md-12">
                        <input type="text" class="form-control" name="searchPromo" onkeyup="searchPromos(this.value)" placeholder="Search employee..." autocomplete="off">
                        <div class="dropdown-list"></div>
                    </div>';
            echo '<div class="col-md-12 mt-2" id="promosTable" style="display: none;">
                    <label class="form-label text-secondary">Employee List</label>
                    <table class="table table-sm table-hover table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>HRMS ID</th>
                                <th>Name</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody class="promos"></tbody>
                    </table>
                </div>';
            echo '</div>';
            echo '<div class="processList"></div>';
        } else if ($process == 'multiple') {
            echo '<div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="bu">Business Unit</label>
                            <select class="form-control" id="bu" name="bu" required>
                                <option value="">---</option>';
            $bus = $this->promo_model->bu_process('corporate');
            foreach ($bus as $key => $bu) {
                echo '<option value="' . $bu . '">' . $key . '</option>';
            }
            echo            '</select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="department">Department</label>
                            <select class="form-control" id="department" name="department">
                                <option value="">---</option>';
            $cond = "status='active' group by dept_name";
            $query = $this->promo_model->select_resultArray('pis', 'locate_promo_department', $cond);
            foreach ($query as $key => $row) {
                echo '<option value="' . $row['dept_name'] . '">' . $row['dept_name'] . '</option>';
            }
            echo            '</select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="promo_type">Promo Type</label>
                            <select class="form-control" id="promo_type" name="promo_type">
                                <option value="">---</option>
                                <option value="STATION">STATION</option>
                                <option value="ROVING">ROVING</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <div class="form-group">
                            <button type="button" class="btn btn-primary" onclick="processFilter()"><i class="fa fa-filter"></i> Filter</button>
                        </div>
                    </div>
                    <div class="col-md-12 filteredPromos"></div>';
            echo '</div>';
        } else if ($process == 'roving') {
            echo '<div class="row ">';
            echo '<div class="col-md-12">';
            echo '<table class="table table-sm table-hover" id="processtable" style="font-size:small;">
					<thead>
						<tr>
							<th width="12%">HRMS ID</th>
							<th>Name</th>
							<th>Type</th>
							<th>Company</th>
							<th>Store</th>
							<th><input type="checkbox" name="chkAll" onclick="chkAllbox(\'processtable\')"></th>
						</tr>
					</thead>';
            $i = 0;
            $filter = '';
            $bus = $this->promo_model->bu_process('corporate');
            foreach ($bus as $key => $bu) {
                $i++;
                $cond   = ['bunit_acronym' => $bu];
                $field  = $this->promo_model->select_rowArray('pis', 'locate_promo_business_unit', $cond)['bunit_field'];
                $filter = ($i == 1) ? "$field='T'" : "$filter OR $field='T'";
            }

            $cond   = "(current_status = 'Active' OR (current_status IN ('Ad-Resigned', 'End of Contract', 'V-Resigned') AND sub_status = 'Uncleared')) AND promo_type='ROVING' AND ($filter)";
            $query  = $this->promo_model->promoDetails($cond);
            foreach ($query as $key => $row) {

                $i = 0;
                $store = '';
                foreach ($bus as $key => $bu) {
                    $i++;
                    $cond = ['bunit_acronym' => $bu];
                    $field = $this->promo_model->select_rowArray('pis', 'locate_promo_business_unit', $cond)['bunit_field'];
                    if ($row[$field] == 'T') {
                        $store .= ($i == 1) ? '<span class="badge bg-secondary mr-1">' . $bu . '</span>' : '<span class="badge bg-secondary mr-1">' . $bu . '</span>';
                    }
                }

                echo    '<tr>
							<td>' . $row['emp_id'] . '</td>
							<td>' . $row['name'] . '</td>
							<td>' . $row['promo_type'] . '</td>
							<td>' . $row['promo_company'] . '</td>
							<td>' . $store . '</td>
							<td><input type="checkbox" class="processtable" name="empId[]" value="' . $row['emp_id'] . '"></td>
						</tr>';
            }

            echo '</table>';
            echo '</div>';
            echo '</div>';
        } else {
            echo '<div class="row ">';
            echo '<div class="col-md-12">';
            echo '<table class="table table-sm table-hover" id="processtable" style="font-size:small;">
					<thead>
						<tr>
							<th width="12%">HRMS ID</th>
							<th width="30%">Name</th>
							<th>Type</th>
							<th>Company</th>
							<th>Store</th>
							<th><input type="checkbox" name="chkAll" onclick="chkAllbox(\'processtable\')"></th>
						</tr>
					</thead>';
            $cond   = ['bunit_acronym' => $process];
            $field  = $this->promo_model->select_rowArray('pis', 'locate_promo_business_unit', $cond)['bunit_field'];
            $cond   = "(current_status = 'Active' OR (current_status IN ('Ad-Resigned', 'End of Contract', 'V-Resigned') AND sub_status = 'Uncleared')) AND promo_type = 'STATION' AND $field='T'";
            $query  = $this->promo_model->promoDetails($cond);
            foreach ($query as $key => $row) {
                echo    '<tr>
							<td>' . $row['emp_id'] . '</td>
							<td>' . $row['name'] . '</td>
							<td>' . $row['promo_type'] . '</td>
							<td>' . $row['promo_company'] . '</td>
							<td><span class="badge bg-secondary">' . $process . '</span></td>
							<td><input type="checkbox" class="processtable" name="empId[]" value="' . $row['emp_id'] . '"></td>
						</tr>';
            }

            echo '</table>';
            echo '</div>';
            echo '</div>';
        }
    }

    public function processFilter()
    {
        $input      = $this->input->post(NULL, TRUE);
        $store      = $input['store'];
        $department = $input['department'];
        $promoType  = $input['promoType'];

        echo '<table class="table table-sm table-hover" id="processtable" style="font-size:small;">
				<thead>
					<tr>
						<th width="12%">HRMS ID</th>
						<th>Name</th>
						<th>Type</th>
						<th>Company</th>
						<th>Store</th>
						<th><input type="checkbox" name="chkAll" onclick="chkAllbox(\'processtable\')"></th>
					</tr>
				</thead>';
        $i      = 0;
        $filter = '';
        $bus    = $this->promo_model->bu_process('corporate');
        $cond   = ['bunit_acronym' => $store];
        $field  = $this->promo_model->select_rowArray('pis', 'locate_promo_business_unit', $cond)['bunit_field'];
        $filter = "$field='T'";

        $cond = "";
        if ($department) {
            $cond .= "promo_department='$department' AND ";
        }

        if ($promoType) {
            $cond .= "promo_type='$promoType' AND ";
        }

        $cond .= "(current_status = 'Active' OR (current_status IN ('Ad-Resigned', 'End of Contract', 'V-Resigned') AND sub_status = 'Uncleared')) AND ($filter)";
        $query  = $this->promo_model->promoDetails($cond);
        foreach ($query as $key => $row) {

            $i = 0;
            $store = '';
            foreach ($bus as $key => $bu) {
                $i++;
                $cond = ['bunit_acronym' => $bu];
                $field = $this->promo_model->select_rowArray('pis', 'locate_promo_business_unit', $cond)['bunit_field'];
                if ($row[$field] == 'T') {
                    $store .= ($i == 1) ? '<span class="badge bg-secondary mr-1">' . $bu . '</span>' : '<span class="badge bg-secondary mr-1">' . $bu . '</span>';
                }
            }

            echo    '<tr>
						<td>' . $row['emp_id'] . '</td>
						<td>' . $row['name'] . '</td>
						<td>' . $row['promo_type'] . '</td>
						<td>' . $row['promo_company'] . '</td>
						<td>' . $store . '</td>
						<td><input type="checkbox" class="processtable" name="empId[]" value="' . $row['emp_id'] . '"></td>
					</tr>';
        }

        echo '</table>';
    }

    public function process_searchPromo()
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
                $a1 = '<span>[' . $empId . '] ' . $name . '</span>';
                $a2 = '<button class="btn btn-success btn-xs" ' . $onclick . '><i class="fa fa-plus"></i> Add</button>';
                echo '<a href="javascript:;" class="list-group-item d-flex justify-content-between align-items-center">' . $a1 . $a2 . '</a>';
            }
        } else {
            echo '<span class="list-group-item">No Results Found!</span>';
        }
        echo '</ul>';
    }

    public function processLogbox()
    {
        $input      = $this->input->post(NULL, TRUE);
        $empIds     = $input['empId'];
        $startDate  = date('Y-m-d', strtotime($input['startDate']));
        $endDate    = date('Y-m-d', strtotime($input['endDate']));
        $data_log   = $input['data_log'];
        $terminal   = 'Logbox';

        $userName   = ucwords($this->userName);
        $timeHis    = date("His");

        set_time_limit(0);
        ob_implicit_flush(true);
        ob_end_flush();

        echo json_encode(array(
            'result'    => 'loading',
            'msg'       => round(0) . '%',
            'msg1'      => round(0) . '%',
            'date'      => "Preparing Logfile Data",
            'table'     => "",
            'exported'  => 'table_update',
        ));

        if ($data_log == 'database') {

            $dateF = date("Y-m-d", strtotime($startDate));
            $dateT = date("Y-m-d", strtotime($endDate));

            $mergeTxtfile = '';
            foreach ($empIds as $key => $empId) {

                $cond   = "empId='$empId' AND barcodeId NOT LIKE '%\_%' AND bioMetricId NOT LIKE '%\_%' ORDER BY assignId DESC LIMIT 1";
                $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
                if ($assign) {

                    $table  = "duty_logbox";
                    $allowId = $assign['allowId'];
                    if ($allowId == "Biometric ID") {
                        $bioM       = $assign['bioMetricId'];
                        $table      = "duty_biometric";
                        $terminal   = "Biometric";
                    }
                    if ($allowId == "Logbox ID") {
                        $bioM       = $assign['barcodeId'];
                        $table      = "duty_logbox";
                        $terminal   = "Logbox";
                    }

                    if ($terminal == "Biometric") {
                        die("Biometric - $empId");
                    }

                    $cond   = "bioMetricId='$bioM' AND dateDuty BETWEEN '$dateF' AND '$dateT'";
                    $query  = $this->promo_model->select_resultArray('tk', $table, $cond);
                    foreach ($query as $key => $co) {

                        $dateDuty   = $co['dateDuty'];
                        $timeIn     = $co['timeIn'];
                        $timeOut    = $co['timeOut'];

                        $mdY    = date("mdY", strtotime($dateDuty));
                        $time   = '';
                        $mode   = '';

                        if ($timeIn != '0') {
                            $time   = date('His', strtotime($timeIn));
                            $mode   = 'I';
                        }

                        if ($timeOut != '0') {
                            $time   = date('His', strtotime($timeOut));
                            $mode   = 'O';
                        }

                        $mergeTxtfile .= "$bioM$mdY$time$mode\r\n";
                    }
                }
            }

            $ext = $terminal . $userName . 'time' . $timeHis;
            $filePath = FCPATH . "../upload/Promo/mergeMonth$ext.LOG";
            $fp = fopen($filePath, 'w');
            if (!$fp) {
                die('Could not create / open text file for writing.');
            }

            if (fwrite($fp, $mergeTxtfile) === false) {
                die('Could not write to text file.');
            }
            fclose($fp);
        } else {

            $bioM = [];
            foreach ($empIds as $key => $empId) {

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
            }

            // merging ******************************************************************************
            $b_d1 = date('Y-m-1', strtotime($startDate));
            $b_d2 = date('Y-m-1', strtotime($endDate));

            $mergeTxtfile = '';
            while (strtotime($b_d1) <= strtotime($b_d2)) {

                $monthYear = date('F Y', strtotime($b_d1));
                $file = "$monthYear.LOG";
                $filePath = FCPATH . "../upload/Promo/$terminal/$file";
                $mergeTxtfile .= file_get_contents($filePath);
                $b_d1 = date('Y-m', strtotime("$b_d1 +1 month"));
            }

            $ext = $terminal . $userName . 'time' . $timeHis;
            $filePath = FCPATH . "../upload/Promo/mergeMonth$ext.LOG";
            $fp = fopen($filePath, 'w');
            if (!$fp) {
                die('Could not create / open text file for writing.');
            }

            if (fwrite($fp, $mergeTxtfile) === false) {
                die('Could not write to text file.');
            }
            fclose($fp);
            // merging ******************************************************************************

            // filtering bioM ******************************************************************************
            $line_entry = '';
            $bioLookup = array_flip($bioM);
            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $percentage = 0;
            $pdiv1 = 100 / ($lines ? count($lines) : 1);
            foreach ($lines as $line) {
                $id = substr($line, 0, 13);
                $percentage += $pdiv1;
                if (isset($bioLookup[$id])) {
                    $line_entry .= $line . "\r\n";

                    echo json_encode(array(
                        'result'    => 'loading',
                        'msg'       => round($percentage) . '%',
                        'msg1'      => round($percentage) . '%',
                        'date'      => "First filtering of data...",
                        'table'     => "[data: $line]",
                        'exported'  => 'table_update'
                    ));
                }
            }

            $fp = fopen($filePath, 'w');
            if (!$fp) {
                die('Could not create / open text file for writing.');
            }

            if (fwrite($fp, $line_entry) === false) {
                die('Could not write to text file.');
            }

            fclose($fp);
            // filtering bioM ******************************************************************************

            // filtering date ******************************************************************************
            $newTXT = "";
            $dateLookup = [];
            $d1 = strtotime($startDate);
            $d2 = strtotime($endDate);

            while ($d1 <= $d2) {
                $dateLookup[date('mdY', $d1)] = true;
                $d1 = strtotime('+1 day', $d1);
            }

            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $percentage = 0;
            $pdiv1 = 100 / ($lines ? count($lines) : 1);
            foreach ($lines as $row) {
                $date = substr($row, 13, 8);
                $percentage += $pdiv1;
                if (isset($dateLookup[$date])) {
                    $newTXT .= $row . "\r\n";
                    echo json_encode(array(
                        'result'    => 'loading',
                        'msg'       => round($percentage) . '%',
                        'msg1'      => round($percentage) . '%',
                        'date'      => "Second filtering of data...",
                        'table'     => "[data: $row]",
                        'exported'  => 'table_update'
                    ));
                }
            }

            $fp = fopen($filePath, 'w');
            if (!$fp) {
                die('Could not create / open text file for writing.');
            }

            if (fwrite($fp, $newTXT) === false) {
                die('Could not write to text file.');
            }

            fclose($fp);
            // filtering date ******************************************************************************
        }

        $table = "dupduty_$userName$timeHis";
        $create = $this->promo_model->dupduty_user($table);
        if ($create) {

            $df = date('Y-m-d', strtotime($startDate));
            $dt = date('Y-m-d', strtotime($endDate));

            $path = FCPATH . "../upload/Promo/mergeMonth$ext.LOG";

            // $data   = file_get_contents($path);
            // $lines  = explode(PHP_EOL, $data);
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $key => $row) {

                $id     = substr($row, 0, 13);
                $date   = substr($row, 13, 8);
                $time   = substr($row, 21, 6);
                $mode   = substr($row, 27, 1);

                $month  = substr($date, 0, 2);
                $day    = substr($date, 2, 2);
                $year   = substr($date, 4, 4);

                $d  = "$year-$month-$day";
                $d1 = "$month-$day-$year";

                $date   = date('Y-m-d', strtotime("$year-$month-$day"));
                $date1  = date('m-d-Y', strtotime("$month-$day-$year"));

                $h = substr($time, 0, 2);
                $m = substr($time, 2, 2);
                $s = substr($time, 4, 2);

                $time = date('H:i:s', strtotime("$h:$m:$s"));

                if (strtotime($df) <= strtotime($date) && strtotime($date) <= strtotime($dt)) {

                    $data = ['bioMetricId' => $id, 'date' => $d1, 'dateDuty' => $date, 'mode' => $mode];
                    $cond = ['bioMetricId' => $id, 'dateDuty' => $date];
                    if ($mode == 'I') {
                        $data['timeIn']         = $time;
                        $data['timeOut']        = '0';
                        $data['dateTimeIn']     = $time;
                        $data['dateTimeOut']    = '0';

                        $cond['timeIn']         = $time;
                        $cond['timeOut']        = '0';
                    }

                    if ($mode == 'O') {
                        $data['timeIn']         = '0';
                        $data['timeOut']        = $time;
                        $data['dateTimeIn']     = '0';
                        $data['dateTimeOut']    = $time;

                        $cond['timeIn']         = '0';
                        $cond['timeOut']        = $time;
                    }

                    $query = $this->promo_model->select_rowArray('tk', $table, $cond);
                    if (!$query) {
                        $insert = $this->promo_model->insert('tk', $table, $data);
                    }
                }
            }
        }

        //insert duty/duty_next **************************************************************
        echo json_encode(array(
            'result'    => 'loading',
            'msg'       => round(0) . '%',
            'msg1'      => round(0) . '%',
            'date'      => "Inserting Tables (duty,duty_next)",
            'table'     => "",
            'exported'  => 'table_update',
        ));

        sleep(1);

        $count = 0;
        $dateF = date('Y-m-01', strtotime($input['startDate']));
        $dateT = date('Y-m-t', strtotime($input['endDate']));
        while (strtotime($dateF) <= strtotime($dateT)) {
            $count++;
            $dateF = date('Y-m-d', strtotime("$dateF +1 day"));
        }

        $cond = "1 GROUP BY bioMetricId, YEAR(date), MONTH(date)";
        $query = $this->promo_model->select_resultArray('tk', $table, $cond);

        $percentage = 0;
        $pdiv1 = 100 / ($query ? count($query) : 1);
        foreach ($query as $key => $row) {

            $id     = $row['bioMetricId'];
            $date   = date('Y-m-d', strtotime($row['dateDuty']));

            $dateF = date('Y-m-01', strtotime($input['startDate']));
            $dateT = date('Y-m-t', strtotime($input['endDate']));

            $percent = $pdiv1 / $count;

            while (strtotime($dateF) <= strtotime($dateT)) {

                $percentage += $percent;

                echo json_encode(array(
                    'result'    => 'loading',
                    'msg'       => round($percentage) . '%',
                    'msg1'      => round($percentage) . '%',
                    'date'      => "Initializing: $id",
                    'table'     => "[Date: $dateF]",
                    'exported'  => 'table_update'
                ));

                $cond   = ['bioMetricId' => $id, 'dateDuty' => $dateF];
                $check  = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                if (!$check) {
                    $data = ['bioMetricId' => $id, 'date' => $dateF, 'dateDuty' => $dateF, 'status' => 'Pending'];
                    $insert = $this->promo_model->insert('tk', 'duty', $data);
                }

                $cond   = ['bioMetricId_ka' => $id, 'dateDuty_ka' => $dateF];
                $check  = $this->promo_model->select_rowArray('tk', 'duty_next', $cond);
                if (!$check) {
                    $data = ['bioMetricId_ka' => $id, 'dateDuty_ka' => $dateF];
                    $insert = $this->promo_model->insert('tk', 'duty_next', $data);
                }

                $dateF = date('Y-m-d', strtotime("$dateF +1 day"));
            }
        }

        //insert 1stcutoff/2ndcutoff **************************************************************
        echo json_encode(array(
            'result'    => 'loading',
            'msg'       => round(0) . '%',
            'msg1'      => round(0) . '%',
            'date'      => "Inserting Tables (1stcutoff,2ndcutoff)",
            'table'     => "",
            'exported'  => 'table_update',
        ));

        sleep(1);
        $count = 0;
        $cosDate = date('Y-m-01', strtotime($input['startDate']));
        $coeDate = date('Y-m-01', strtotime($input['endDate']));
        while (strtotime($cosDate) <= strtotime($coeDate)) {
            $count++;
            $cosDate = date('Y-m-01', strtotime("$cosDate +1 month"));
        }

        $cond = "1 GROUP BY bioMetricId, YEAR(date), MONTH(date)";
        $query = $this->promo_model->select_resultArray('tk', $table, $cond);

        $percentage = 0;
        $pdiv1 = 100 / ($query ? count($query) : 1);
        foreach ($query as $key => $row) {

            $pdiv2 = $pdiv1 / $count;
            $cosDate = date('Y-m-01', strtotime($input['startDate']));
            $coeDate = date('Y-m-01', strtotime($input['endDate']));
            while (strtotime($cosDate) <= strtotime($coeDate)) {

                $id = $row['bioMetricId'];
                $date = date('Y-m-01', strtotime($cosDate));

                $cond   = "bioMetricId='$id' OR barcodeId='$id' ORDER BY assignId DESC";
                $empId  = $this->promo_model->select_rowArray('tk', 'assign', $cond)['empId'];

                $cond       = ['emp_id' => $empId];
                $recordNo   = $this->promo_model->select_rowArray('pis', 'employee3', $cond)['record_no'];

                $cond       = ['empId' => $empId, 'recordNo' => $recordNo];
                $statCut    = $this->promo_model->select_rowArray('tk', 'promo_sched_emp', $cond)['statCut'];
                if (!$statCut) {
                    $cond       = "empId='$empId' ORDER BY date_setup DESC";
                    $statCut    = $this->promo_model->select_rowArray('tk', 'promo_sched_emp', $cond)['statCut'];
                }

                $cond   = ['statCut' => $statCut];
                $ps     = $this->promo_model->select_rowArray('tk', 'promo_schedule', $cond);
                $sFC    = $ps['startFC'];
                $eFC    = ($ps['endFC'] == '') ? date('t', strtotime($cosDate)) : $ps['endFC'];
                $cFC    = $ps['clearingFC'];
                $sSC    = $ps['startSC'];
                $eSC    = $ps['endSC'];
                $cSC    = $ps['clearingSC'];

                $t = explode('-', $date);
                $y = $t[0];
                $m = $t[1];
                $d = $t[2];

                $startFC = date('Y-m-d', strtotime("$y-$m-$sFC"));
                $endFC = date('Y-m-d', strtotime("$y-$m-$eFC"));
                if ($ps['startFC'] > 16) {
                    $endFC = date('Y-m-d', strtotime("$y-$m-$eFC +1 month"));
                }

                $startSC = date('Y-m-d', strtotime("$y-$m-$sSC"));
                $endSC = date('Y-m-d', strtotime("$y-$m-$eSC"));
                if ($ps['startSC'] > 16) {
                    $endSC = date('Y-m-d', strtotime("$y-$m-$eSC +1 month"));
                }

                $percentage += $pdiv2;

                echo json_encode(array(
                    'result'    => 'loading',
                    'msg'       => round($percentage) . '%',
                    'msg1'      => round($percentage) . '%',
                    'date'      => "Initializing: $id",
                    'table'     => "[dateFrom: $startFC; dateTo: $endFC; statCut: $statCut;]",
                    'exported'  => 'table_update'
                ));

                $cond = ['bioMetricId' => $id, 'dateFrom' => $startFC, 'dateTo' => $endFC, 'statCut' => $statCut];
                $check = $this->promo_model->select_rowArray('tk', '1stcutoff', $cond);
                if (!$check) {
                    $data = [
                        'bioMetricId'   => $id,
                        'dateFrom'      => $startFC,
                        'dateTo'        => $endFC,
                        'statCut'       => $statCut,
                        'date_added'    => date('Y-m-d'),
                        'time_added'    => date('H:i:s'),
                    ];
                    $insert = $this->promo_model->insert('tk', '1stcutoff', $data);
                }

                $cond = ['bioMetricId' => $id, 'dateFrom' => $startSC, 'dateTo' => $endSC, 'statCut' => $statCut];
                $check = $this->promo_model->select_rowArray('tk', '2ndcutoff', $cond);
                if (!$check) {
                    $data = [
                        'bioMetricId'   => $id,
                        'dateFrom'      => $startSC,
                        'dateTo'        => $endSC,
                        'statCut'       => $statCut,
                        'date_added'    => date('Y-m-d'),
                        'time_added'    => date('H:i:s'),
                    ];
                    $insert = $this->promo_model->insert('tk', '2ndcutoff', $data);
                }

                // echo json_encode(array(
                //     'result'    => 'loading',
                //     'msg'       => round($percentage) . '%',
                //     'msg1'      => round($percentage) . '%',
                //     'date'      => "Initializing: $id test",
                //     'table'     => "[date: $date;]",
                //     'exported'  => 'table_update'
                // ));

                $cosDate = date('Y-m-d', strtotime("$cosDate +1 month"));
            }
        }

        //insert duty_logbox table **************************************************************
        echo json_encode(array(
            'result'    => 'loading',
            'msg'       => round(0) . '%',
            'msg1'      => round(0) . '%',
            'date'      => "Inserting Table (duty_logbox)",
            'table'     => "",
            'exported'  => 'table_update',
        ));

        sleep(1);

        $i = 0;
        $start = date('Y-m-01', strtotime($input['startDate']));
        $end = date('Y-m-01', strtotime($input['endDate']));
        while ($start <= $end) {
            $i++;
            $start = date('Y-m-01', strtotime("$start +1 month"));
        }

        $percentage = 0;
        $pdiv1 = 100 / $i;

        $strchk = [];
        $start = date('Y-m-01', strtotime($input['startDate']));
        $end = date('Y-m-01', strtotime($input['endDate']));
        while ($start <= $end) {

            $my     = date('F Y', strtotime($start));
            $month  = strtoupper(date('M', strtotime($my)));
            $m      = date('m', strtotime($my));
            $y      = date('Y', strtotime($my));

            $cond   = "1 AND YEAR(dateDuty)='$y' AND MONTH(dateDuty)='$m' ORDER BY dateDuty ASC";
            $query  = $this->promo_model->select_resultArray('tk', $table, $cond);

            $tk_bu      = $this->promo_model->tk_bu();
            $cond   = "statusLogs='1' ORDER BY storeCode DESC";
            $stores = $this->promo_model->select_resultArray('tk', 'promo_store_start', $cond);

            $pdiv2 = $pdiv1 / count($stores);
            foreach ($stores as $key => $store) {

                $storeCode  = $store['storeCode'];
                $cond       = ['baseId' => $storeCode];
                $code       = $this->promo_model->select_rowArray('tk', 'promo_id_bio_log', $cond);
                $codeLog    = $code['code'];

                $percentage += $pdiv2;

                $path = FCPATH . "../upload/Promo/Backup_Month_Year/$storeCode/Logbox/$my/$month$codeLog.LOG";
                $check = is_file($path);
                if ($check == 1) {


                    $storeName  = array_search($storeCode, $tk_bu);

                    $logLookup = array_flip(file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
                    foreach ($query as $key => $row) {
                        $id     = $row['bioMetricId'];
                        $date   = date('Y-m-d', strtotime($row['dateDuty']));

                        $dMDY       = $row['date'];
                        $timeIn     = $row['timeIn'];
                        $timeOut    = $row['timeOut'];
                        $mode       = $row['mode'];

                        $in_out = "";
                        $dateTimeIn = "";
                        if ($timeOut == 0) {
                            $dateTimeIn = "$date $timeIn";
                            $in_out = date("His", strtotime($timeIn));
                        }

                        $dateTimeOut = "";
                        if ($timeIn == 0) {
                            $dateTimeOut = "$date $timeOut";
                            $in_out = date("His", strtotime($timeOut));
                        }

                        $dp1 = date("mdY", strtotime($date));
                        $dp2 = date("Ymd", strtotime($date));

                        $search1 = "$id$dp1$in_out$mode";
                        $search2 = "$id$dp2$in_out$mode";

                        echo json_encode(array(
                            'result'    => 'loading',
                            'msg'       => round($percentage) . '%',
                            'msg1'      => round($percentage) . '%',
                            'date'      => "Checking Logs per store...",
                            'table'     => "[data: $search1; store: $storeCode;]",
                            'exported'  => 'table_update'
                        ));

                        // if (in_array($search1 . "\r\n", $strings) || in_array($search2 . "\r\n", $strings)) {
                        if (isset($logLookup[$search1]) || isset($logLookup[$search2])) {
                            echo json_encode(array(
                                'result'    => 'loading',
                                'msg'       => round($percentage) . '%',
                                'msg1'      => round($percentage) . '%',
                                'date'      => "Inserting Table (duty_logbox)",
                                'table'     => "[Date: $date; biometricId: $id; store: $storeCode; timeIn: $timeIn; timeOut: $timeOut; mode: $mode;]",
                                'exported'  => 'table_update'
                            ));

                            if (!in_array($storeName, $strchk)) {
                                $strchk[] = $storeName;
                            }

                            $cond = [
                                'bioMetricId'   => $id,
                                'dateDuty'      => $date,
                                'timeIn'        => $timeIn,
                                'timeOut'       => $timeOut,
                                'mode'          => $mode,
                                'store'         => '',
                            ];

                            $check = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                            if ($check) {
                                $data = ['store' => $storeCode];
                                $update = $this->promo_model->update('tk', 'duty_logbox', $cond, $data);
                            } else {
                                $cond = [
                                    'bioMetricId'   => $id,
                                    'dateDuty'      => $date,
                                    'timeIn'        => $timeIn,
                                    'timeOut'       => $timeOut,
                                    'mode'          => $mode,
                                    'store'         => $storeCode,
                                ];

                                $check = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                if (!$check) {
                                    $data = [
                                        'bioMetricId'   => $id,
                                        'date'          => $dMDY,
                                        'dateDuty'      => $date,
                                        'timeIn'        => $timeIn,
                                        'timeOut'       => $timeOut,
                                        'dateTimeIn'    => $dateTimeIn,
                                        'dateTimeOut'   => $dateTimeOut,
                                        'mode'          => $mode,
                                        'store'         => $storeCode,
                                    ];

                                    $insert = $this->promo_model->insert('tk', 'duty_logbox', $data);
                                }
                            }
                        }
                    }
                }
            }

            $start = date('Y-m-01', strtotime("$start +1 month"));
        }

        foreach ($strchk as $key => $str) {

            $data = [
                'storeName'         => $str,
                'dateFrom'          => $input['startDate'],
                'dateTo'            => $input['endDate'],
                'date_calculated'   => date('Y-m-d'),
                'time_calculated'   => date('H:i:s'),
                'process_by'        => $this->userId,
            ];

            $insert = $this->promo_model->insert('tk', 'promo_upload_calc', $data);
        }

        //update duty/duty_next entries **************************************************************
        $cond = "1 GROUP BY bioMetricId, dateDuty ORDER BY dateDuty ASC";
        $query = $this->promo_model->select_resultArray('tk', $table, $cond);

        $percentage = 0;
        $percent    = 100 / ($query ? count($query) : 1);
        foreach ($query as $key => $row) {

            $id     = $row['bioMetricId'];
            $date   = date('Y-m-d', strtotime($row['dateDuty']));

            $cond   = "bioMetricId='$id' OR barcodeId='$id'";
            $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
            $empId  = $assign['empId'];

            if ($assign['shiftCode']) {
                $shiftCode = $assign['shiftCode'];
            }

            $day = date('l', strtotime($row['dateDuty']));
            $no_schedule = $this->noSchedule($empId, $date, $day);

            $cond   = ['empId' => $empId, 'day_sched' => $day];
            $pds    = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
            if ($pds) {
                $shiftCode = $pds['shiftCode'];
            }

            $cond   = "empId='$empId' AND status = 'active' AND status2 = 'Approved' AND '$date' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
            $ams    = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond);
            if ($ams) {
                $shiftCode = $ams['shiftCode'];
            }

            $four_entries = false;
            $cond   = ['shiftCode' => $shiftCode];
            $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
            if ($sc) {

                $fIn    = date("H:i:s", strtotime($sc['1stIn']));
                $fOut   = date("H:i:s", strtotime($sc['1stOut']));
                $sIn    = date("H:i:s", strtotime($sc['2ndIn']));
                $sOut   = date("H:i:s", strtotime($sc['2ndOut']));

                $s_in1  = $sc['1stIn'];
                $s_out1 = $sc['1stOut'];
                $s_in2  = $sc['2ndIn'];
                $s_out2 = $sc['2ndOut'];

                if ($fIn && $fOut && $sIn && $sOut) {
                    $four_entries = true;
                }
            }

            $cond   = ['shiftCode' => $shiftCode];
            $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
            if ($scb) {

                $four_entries = true;

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

            $day_outH   = date("H", strtotime($s_out2));
            $day_outM   = date("i", strtotime($s_out2));

            if ($four_entries) {

                if (($day_in1 == 'AM' && $day_out2 == 'PM') || ($day_in1 == 'AM' && $day_out2 == 'AM') || ($day_in1 == 'PM' && $day_out2 == 'PM')) {

                    $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';

                    $time_in1   = date("H:i:s", strtotime($s_in1));
                    $time_out1  = date("H:i:s", strtotime($s_out1));
                    $time_in2   = date("H:i:s", strtotime($s_in2));
                    $time_out2  = date("H:i:s", strtotime($s_out2));

                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn < '$time_out1' ORDER BY timeIn ASC";
                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                    if ($check) {
                        $in1 = $check['timeIn'];
                        $time_in1 = $check['timeIn'];
                    } else {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn < '$time_out1' ORDER BY timeIn ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $in1 = $check['timeIn'];
                            $time_in1 = $check['timeIn'];
                        }
                    }

                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut < '$time_in2' AND timeOut > '$time_in1' ORDER BY timeOut ASC";
                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                    if ($check) {
                        $out1 = $check['timeOut'];
                        $time_out1 = $check['timeOut'];
                    } else {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut < '$time_in2' AND timeOut > '$time_in1' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $out1 = $check['timeOut'];
                            $time_out1 = $check['timeOut'];
                        }
                    }

                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND timeIn < '$time_out2' ORDER BY timeIn ASC";
                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                    if ($check) {
                        $in2 = $check['timeIn'];
                        $time_in2 = $check['timeIn'];
                    } else {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND timeIn < '$time_out2' ORDER BY timeIn ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $in2 = $check['timeIn'];
                            $time_in2 = $check['timeIn'];
                        }
                    }

                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_in2' ORDER BY timeOut ASC";
                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                    if ($check) {
                        $out2 = $check['timeOut'];
                        $time_out2 = $check['timeOut'];

                        if ($time_out2 < "$sOut:00") {
                            $time_out2 = "$sOut:00";
                        }
                    } else {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_in2' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $out2 = $check['timeOut'];
                            $time_out2 = $check['timeOut'];

                            if ($time_out2 < "$sOut:00") {
                                $time_out2 = "$sOut:00";
                            }
                        } else {
                            $tomorrow = date("Y-m-d", strtotime("$date +1 day"));
                            $cond   = "bioMetricId='$id' AND dateDuty='$tomorrow' AND mode='O' AND timeOut < '$time_in1' ORDER BY timeOut ASC";
                            $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                            if ($check) {
                                $out2 = $check['timeOut'];
                            }
                        }
                    }

                    if ($out2 != '') {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out2' AND timeIn > '$out2' ORDER BY timeIn ASC";
                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                        if ($check) {
                            $in3 = $check['timeIn'];
                        } else {
                            $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out2' AND timeIn > '$out2' ORDER BY timeIn ASC";
                            $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                            if ($check) {
                                $in3 = $check['timeIn'];
                            }
                        }
                    }

                    if ($in3 != '') {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_out2' AND timeOut > '$in3' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                        if ($check) {
                            $out3 = $check['timeOut'];
                        } else {
                            $tomorrow = date("Y-m-d", strtotime("$date +1 day"));
                            $to_day = date("Y-m-d H:i:s", strtotime("$date $in3"));
                            $cond   = "bioMetricId='$id' AND dateDuty='$tomorrow' AND mode='O' AND timeOut < '$fIn' AND dateTimeOut > '$to_day' ORDER BY timeOut ASC";
                            $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                            if ($check) {
                                $out3 = $check['timeOut'];
                            }
                        }
                    }
                }

                if ($day_in1 == 'PM' && $day_out1 == 'AM' && $day_in2 == 'AM' && $day_out2 == 'AM') {

                    $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';

                    $time_in1   = date("H:i:s", strtotime($s_in1));
                    $time_out1  = date("H:i:s", strtotime($s_out1));
                    $time_in2   = date("H:i:s", strtotime($s_in2));
                    $time_out2  = date("H:i:s", strtotime($s_out2));

                    $dt_in1     = date("Y-m-d H:i:s", strtotime("$date $s_in1"));
                    $dt_out1    = date("Y-m-d H:i:s", strtotime("$date $s_out1"));
                    $dt_in2     = date("Y-m-d H:i:s", strtotime("$date $s_in2"));
                    $dt_out2    = date("Y-m-d H:i:s", strtotime("$date $s_out2"));

                    $date_tom       = date("Y-m-d", strtotime("$date +1 day"));
                    $dt_in1_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in1"));
                    $dt_out1_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out1"));
                    $dt_in2_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in2"));
                    $dt_out2_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out2"));

                    // shift 10PM - 2AM | 3AM - 6AM
                    // entry 9:59PM - 2:01AM | 2:59AM - 6:01AM

                    // 9:59PM > 2:00AM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND dateTimeIn > '$dt_out1' ORDER BY timeIn ASC";
                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                    if ($check) {
                        $in1 = $check['timeIn'];
                        $time_in1 = $check['timeIn'];
                    }

                    // 2:01AM < 3AM AND 2025-11-10 2:01AM > 2025-11-09 10:00PM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut < '$time_in2' AND dateTimeOut > '$dt_in1' ORDER BY timeOut ASC";
                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                    if ($check) {
                        $out1 = $check['timeOut'];
                        $time_out1 = $check['timeOut'];
                    }

                    // 2:59AM > 2:01AM AND 2025-11-10 2:59AM < 2025-11-10 06:00AM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn > '$time_out1' AND dateTimeIn < '$dt_out2_tom' ORDER BY timeIn ASC";
                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                    if ($check) {
                        $in2 = $check['timeIn'];
                        $time_in2 = $check['timeIn'];
                    }

                    // 6:01AM > 2:59AM AND 2025-11-10 6:01AM < 2025-11-10 10:00PM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_in2' AND dateTimeOut < '$dt_in1_tom' ORDER BY timeOut ASC";
                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                    if ($check) {
                        $out2 = $check['timeOut'];
                        $time_out2 = $check['timeOut'];
                    }

                    if ($out2 != '') {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn > '$time_out2' AND dateTimeIn < '$dt_in1_tom' ORDER BY timeIn ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $in3 = $check['timeIn'];
                        }
                    }

                    if ($in3 != '') {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_out2' AND timeOut > '$in3' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                        if ($check) {
                            $out3 = $check['timeOut'];
                        }
                    }
                }

                if ($day_in1 == 'PM' && $day_out1 == 'PM' && $day_in2 == 'AM' && $day_out2 == 'AM') {

                    $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';

                    $time_in1   = date("H:i:s", strtotime($s_in1));
                    $time_out1  = date("H:i:s", strtotime($s_out1));
                    $time_in2   = date("H:i:s", strtotime($s_in2));
                    $time_out2  = date("H:i:s", strtotime($s_out2));

                    $dt_in1     = date("Y-m-d H:i:s", strtotime("$date $s_in1"));
                    $dt_out1    = date("Y-m-d H:i:s", strtotime("$date $s_out1"));
                    $dt_in2     = date("Y-m-d H:i:s", strtotime("$date $s_in2"));
                    $dt_out2    = date("Y-m-d H:i:s", strtotime("$date $s_out2"));

                    $date_tom       = date("Y-m-d", strtotime("$date +1 day"));
                    $dt_in1_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in1"));
                    $dt_out1_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out1"));
                    $dt_in2_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in2"));
                    $dt_out2_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out2"));

                    // shift 10PM - 11PM | 12AM - 6AM
                    // entry 9:59PM - 11:01PM | 11:59PM - 6:01AM

                    // 9:59PM < 11:00PM AND 2025-11-09 9:59PM < 2025-11-09 11:00PM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn < '$time_out1' AND dateTimeIn < '$dt_out1' ORDER BY timeIn ASC";
                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                    if ($check) {
                        $in1 = $check['timeIn'];
                        $time_in1 = $check['timeIn'];
                    }

                    // 11:01PM > 12AM AND 2025-11-09 11:01PM > 2025-11-09 10:00PM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_in2' AND dateTimeOut > '$dt_in1' ORDER BY timeOut ASC";
                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                    if ($check) {
                        $out1 = $check['timeOut'];
                        $time_out1 = $check['timeOut'];
                    }

                    // if dili late 
                    // 11:59PM > 11:00PM AND 2025-11-09 11:59PM < 2025-11-10 06:00AM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND dateTimeIn < '$dt_out2_tom' ORDER BY timeIn ASC";
                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                    if ($check) {
                        $in2 = $check['timeIn'];
                        $time_in2 = $check['timeIn'];
                    } else {
                        // if late 
                        // 12:01AM < 11:00PM AND 2025-11-10 12:01AM < 2025-11-10 06:00AM
                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn < '$time_out1' AND dateTimeIn < '$dt_out2_tom' ORDER BY timeIn ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $in2 = $check['timeIn'];
                            $time_in2 = $check['timeIn'];
                        }
                    }

                    // if dili late 
                    // 6:01AM < 11:59PM AND 2025-11-10 6:01AM < 2025-11-10 10:00PM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut < '$time_in2' AND dateTimeOut < '$dt_in1_tom' ORDER BY timeOut ASC";
                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                    if ($check) {
                        $out2 = $check['timeOut'];
                        $time_out2 = $check['timeOut'];
                    } else {
                        // if late 
                        // 6:01AM > 12:01AM AND 2025-11-10 6:01AM > 2025-11-10 10:00PM
                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_in2' AND dateTimeOut > '$dt_in1_tom' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $out2 = $check['timeOut'];
                            $time_out2 = $check['timeOut'];
                        }
                    }


                    if ($out2 != '') {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn > '$time_out2' AND dateTimeIn < '$dt_in1_tom' ORDER BY timeIn ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $in3 = $check['timeIn'];
                        }
                    }

                    if ($in3 != '') {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_out2' AND timeOut > '$in3' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                        if ($check) {
                            $out3 = $check['timeOut'];
                        }
                    }
                }

                if ($day_in1 == 'PM' && $day_out1 == 'PM' && $day_in2 == 'PM' && $day_out2 == 'AM') {

                    $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';

                    $time_in1       = date("H:i:s", strtotime($s_in1));
                    $time_out1      = date("H:i:s", strtotime($s_out1));
                    $time_in2       = date("H:i:s", strtotime($s_in2));
                    $time_out2      = date("H:i:s", strtotime($s_out2));

                    $dt_in1         = date("Y-m-d H:i:s", strtotime("$date $s_in1"));
                    $dt_out1        = date("Y-m-d H:i:s", strtotime("$date $s_out1"));
                    $dt_in2         = date("Y-m-d H:i:s", strtotime("$date $s_in2"));
                    $dt_out2        = date("Y-m-d H:i:s", strtotime("$date $s_out2"));

                    $date_tom       = date("Y-m-d", strtotime("$date +1 day"));
                    $dt_in1_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in1"));
                    $dt_out1_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out1"));
                    $dt_in2_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in2"));
                    $dt_out2_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out2"));

                    // shift 9PM - 10PM | 11PM - 12AM
                    // entry 8:59PM - 10:01PM | 10:59PM - 12:01AM

                    // 8:59PM < 10:00PM AND 2025-11-09 8:59PM < 2025-11-09 11:00PM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn < '$time_out1' AND dateTimeIn < '$dt_out1' ORDER BY timeIn ASC";
                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                    if ($check) {
                        $in1 = $check['timeIn'];
                        $time_in1 = $check['timeIn'];
                    }

                    // 10:01PM < 11PM AND 2025-11-09 10:01PM > 2025-11-09 09:00PM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut < '$time_in2' AND dateTimeOut > '$dt_in1' ORDER BY timeOut ASC";
                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                    if ($check) {
                        $out1 = $check['timeOut'];
                        $time_out1 = $check['timeOut'];
                    }

                    // 10:59PM > 10:00PM AND 2025-11-09 10:59PM < 2025-11-10 06:00AM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND dateTimeIn < '$dt_out2_tom' ORDER BY timeIn ASC";
                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                    if ($check) {
                        $in2 = $check['timeIn'];
                        $time_in2 = $check['timeIn'];
                    }

                    // if dili undertime
                    // 6:01AM < 10:59PM AND 2025-11-10 6:01AM < 2025-11-10 10:00PM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut < '$time_in2' AND dateTimeOut < '$dt_in1_tom' ORDER BY timeOut ASC";
                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                    if ($check) {
                        $out2 = $check['timeOut'];
                        $time_out2 = $check['timeOut'];
                    } else {
                        // if undertime
                        // 11:59PM > 10:59PM AND 2025-11-09 11:59PM < 2025-11-10 10:00PM
                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_in2' AND dateTimeOut < '$dt_in1_tom' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $out2 = $check['timeOut'];
                            $time_out2 = $check['timeOut'];
                        }
                    }

                    if ($out2 != '') {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn > '$time_out2' AND dateTimeIn < '$dt_in1_tom' ORDER BY timeIn ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $in3 = $check['timeIn'];
                        }
                    }

                    if ($in3 != '') {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_out2' AND timeOut > '$in3' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                        if ($check) {
                            $out3 = $check['timeOut'];
                        }
                    }
                }

                if ($out1 && $in2) {

                    $dayEx  = date("D", strtotime($date));
                    $cond   = ['empId' => $empId, 'status' => 'active', 'day' => $dayEx];
                    $check  = $this->promo_model->select_rowArray('tk', 'setup_inter_io_exempt', $cond);
                    if (!$check) {
                        $sub_in2    = date("H:i:s", strtotime("$in2 -1 minutes"));
                        $temp_out1  = date("H:i:s", strtotime($out1));

                        if (strtotime($sub_in2) <= strtotime($temp_out1)) {
                            $out1 = "";
                        }
                    }
                }

                if ($no_schedule) {
                    $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';
                }

                $percentage = $percentage + $percent;

                echo json_encode(array(
                    'result'    => 'loading',
                    'msg'       => round($percentage) . '%',
                    'msg1'      => round($percentage) . '%',
                    'date'      => "Inserting Table (duty/duty_next)",
                    'table'     => "[Date: $date; biometricId: $id; in1: $in1; out1: $out1; in2: $in2; out2: $out2; in3: $in3; out3: $out3;]",
                    'exported'  => 'table_update'
                ));

                $data = [
                    'in1'   => $in1,
                    'out1'  => $out1,
                    'in2'   => $in2,
                    'out2'  => $out2,
                    'in3'   => $in3,
                    'out3'  => $out3,
                ];

                $cond = ['bioMetricId' => $id, 'dateDuty' => $date];
                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                $data_ka = [
                    'in1_ka'   => date("Y-m-d H:i:s", strtotime("$date $in1")),
                    'out1_ka'  => date("Y-m-d H:i:s", strtotime("$date $out1")),
                    'in2_ka'   => date("Y-m-d H:i:s", strtotime("$date $in2")),
                    'out2_ka'  => date("Y-m-d H:i:s", strtotime("$date $out2")),
                    'in3_ka'   => date("Y-m-d H:i:s", strtotime("$date $in3")),
                    'out3_ka'  => date("Y-m-d H:i:s", strtotime("$date $out3")),
                ];

                $cond   = ['bioMetricId_ka' => $id, 'dateDuty_ka' => $date];
                $update = $this->promo_model->update('tk', 'duty_next', $cond, $data_ka);
            }
        }

        //update perday entries **************************************************************
        $cond = "1 GROUP BY bioMetricId, dateDuty ORDER BY dateDuty ASC";
        $query = $this->promo_model->select_resultArray('tk', $table, $cond);

        $percentage = 0;
        $percent    = 100 / ($query ? count($query) : 1);
        foreach ($query as $key => $row) {

            $id     = $row['bioMetricId'];
            $date   = date('Y-m-d', strtotime($row['dateDuty']));

            $percentage = $percentage + $percent;

            echo json_encode(array(
                'result'    => 'loading',
                'msg'       => round($percentage) . '%',
                'msg1'      => round($percentage) . '%',
                'date'      => "Inserting Table (perday)",
                'table'     => "[Date: $date; biometricId: $id;]",
                'exported'  => 'table_update'
            ));

            $cond   = "bioMetricId='$id' OR barcodeId='$id'";
            $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
            $empId  = $assign['empId'];
            if ($assign['shiftCode']) {
                $shiftCode = $assign['shiftCode'];
            }


            $day = date('l', strtotime($row['dateDuty']));

            $cond   = ['empId' => $empId, 'day_sched' => $day];
            $pds    = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
            if ($pds) {
                $shiftCode = $pds['shiftCode'];
            }

            $cond   = "empId='$empId' AND status = 'active' AND status2 = 'Approved' AND '$date' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
            $ams    = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond);
            if ($ams) {
                $shiftCode = $ams['shiftCode'];
            }

            $four_entries = false;
            $cond   = ['shiftCode' => $shiftCode];
            $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
            if ($sc) {

                $night_diff = $sc['night_diff'];

                $fIn    = date("H:i:s", strtotime($sc['1stIn']));
                $fOut   = date("H:i:s", strtotime($sc['1stOut']));
                $sIn    = date("H:i:s", strtotime($sc['2ndIn']));
                $sOut   = date("H:i:s", strtotime($sc['2ndOut']));

                $s_in1  = $sc['1stIn'];
                $s_out1 = $sc['1stOut'];
                $s_in2  = $sc['2ndIn'];
                $s_out2 = $sc['2ndOut'];

                if ($fIn && $fOut && $sIn && $sOut) {
                    $four_entries = true;
                }
            }

            $cond   = ['shiftCode' => $shiftCode];
            $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
            if ($scb) {

                $four_entries = true;

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

            $time_in1   = date("H:i:s", strtotime($s_in1));
            $time_out1  = date("H:i:s", strtotime($s_out1));
            $time_in2   = date("H:i:s", strtotime($s_in2));
            $time_out2  = date("H:i:s", strtotime($s_out2));

            if (true) {

                $cond   = ['bioMetricId' => $id, 'dateDuty' => $date];
                $duty   = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                $dutyId = $duty['dutyId'];
                $in1    = $duty['in1'];
                $out1   = $duty['out1'];
                $in2    = $duty['in2'];
                $out2   = $duty['out2'];
                $in3    = $duty['in3'];
                $out3   = $duty['out3'];

                $cond   = ['dutyId' => $dutyId];
                $check  = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                if (!$check) {
                    $data = ['dutyId' => $dutyId];
                    $insert = $this->promo_model->insert('tk', 'perday', $data);
                }

                $next_not = false;
                if ($day_in2 == "AM" && $day_out2 == "AM" && !$scb) {
                    $next_not = true;
                }

                if ($day_out1 == "AM" && $day_in2 == "AM" && $day_out2 == "AM" && !$scb) {
                    $next_not = true;
                }

                $two_four = false;
                if ($scb) {
                    if ($day_in1 == "PM" && $day_in2 == "AM" && $day_out2 == "AM") {
                        $next_not = true;
                        $two_four = true;
                    }

                    if ($day_in1 == "PM" && $day_out1 == "AM" && $day_in2 == "AM") {
                        $next_not = true;
                        $two_four = true;
                    }

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

                    if ($lIn1 && $in1) {
                        if ($minLate1 < $in1) {
                            $am  = '00:00';
                        }
                    }

                    if ($lIn2 && $in2) {
                        if ($minLate2 < $in2) {
                            $pm  = '00:00';
                        }
                    }

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
                        $h = $t[0];
                        $m = $t[1];

                        $hm = date("H:i", strtotime("$newOut1 -$h hours, -$m minutes"));
                        $t  = explode(":", $hm);
                        $h  = $t[0] * 1;
                        $m  = $t[1] * 1;

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
                        $h  = $t[0] * 1;
                        $m  = $t[1] * 1;

                        $ot12 = date("H:i", strtotime("$hi_ot2 +$h hours, +$m minutes"));

                        //caculate the ot12 + ot3 overtime
                        $t  = explode(":", $ot12);
                        $h  = $t[0] * 1;
                        $m  = $t[1] * 1;

                        $ot123 = date('H:i', strtotime("$hi_ot3 +$h hours, +$m minutes"));
                        $t  = explode(":", $ot123);
                        $h  = $t[0] * 1;
                        $m  = $t[1] * 1;

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

        //update perday nigt diff **************************************************************
        $cond = "1 GROUP BY bioMetricId, dateDuty ORDER BY dateDuty ASC";
        $query = $this->promo_model->select_resultArray('tk', $table, $cond);
        foreach ($query as $key => $row) {

            $id     = $row['bioMetricId'];
            $date   = date('Y-m-d', strtotime($row['dateDuty']));

            $cond   = ['bioMetricId' => $id, 'dateDuty' => $date];
            $duty   = $this->promo_model->select_rowArray('tk', 'duty', $cond);
            $dutyId = $duty['dutyId'];
            $in1    = $duty['in1'];
            $out1   = $duty['out1'];
            $in2    = $duty['in2'];
            $out2   = $duty['out2'];
            $in3    = $duty['in3'];
            $out3   = $duty['out3'];

            $cond   = "bioMetricId='$id' OR barcodeId='$id'";
            $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
            $empId  = $assign['empId'];
            if ($assign['shiftCode']) {
                $shiftCode = $assign['shiftCode'];
            }

            $day = date('l', strtotime($row['dateDuty']));
            $cond = ['empId' => $empId, 'day_sched' => $day];
            $pds = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
            if ($pds) {
                $shiftCode = $pds['shiftCode'];
            }

            $cond = "empId='$empId' AND status='active' AND status2='Approved' AND '$date' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
            $ams = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond);
            if ($ams) {
                $shiftCode = $ams['shiftCode'];
            }

            $night_diff = '';
            $four_entries = false;
            $cond   = ['shiftCode' => $shiftCode];
            $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
            if ($sc) {

                $fIn    = date("H:i:s", strtotime($sc['1stIn']));
                $fOut   = date("H:i:s", strtotime($sc['1stOut']));
                $sIn    = date("H:i:s", strtotime($sc['2ndIn']));
                $sOut   = date("H:i:s", strtotime($sc['2ndOut']));

                $s_in1  = $sc['1stIn'];
                $s_out1 = $sc['1stOut'];
                $s_in2  = $sc['2ndIn'];
                $s_out2 = $sc['2ndOut'];

                $night_diff = $sc['night_diff'];

                if ($fIn && $fOut && $sIn && $sOut) {
                    $four_entries = true;
                }
            }

            $cond   = ['shiftCode' => $shiftCode];
            $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
            if ($scb) {

                $four_entries = true;

                $fIn    = date("H:i:s", strtotime($scb['1stIn']));
                $fOut   = date("H:i:s", strtotime($scb['1stOut']));
                $sIn    = date("H:i:s", strtotime($scb['2ndIn']));
                $sOut   = date("H:i:s", strtotime($scb['2ndOut']));

                $s_in1  = $scb['1stIn'];
                $s_out1 = $scb['1stOut'];
                $s_in2  = $scb['2ndIn'];
                $s_out2 = $scb['2ndOut'];

                $temp_entries1 = false;
                $temp_entries2 = false;
                if (empty($out1)) {
                    $temp_entries1 = true;
                    $out1 = date('H:i:s', strtotime($s_out1));
                }
                if (empty($in2)) {
                    $temp_entries2 = true;
                    $in2 = date('H:i:s', strtotime($s_in2));
                }
                if (empty($in1)) {
                    $out1 = '';
                }
                if (empty($out2)) {
                    $in2 = '';
                    $out1 = '';
                }

                if (empty($in1) && !empty($out2) && $temp_entries1) {
                    $in2 = '';
                }
                if (!empty($in1) && empty($out2) && $temp_entries2) {
                    $out1 = '';
                }
            }

            $day_in1    = date('A', strtotime($s_in1));
            $day_out1   = date('A', strtotime($s_out1));
            $day_in2    = date('A', strtotime($s_in2));
            $day_out2   = date('A', strtotime($s_out2));

            $cond = "1 ORDER BY snId";
            $snd = $this->promo_model->select_rowArray('tk', 'setup_n_d', $cond);
            $ndStart = $snd['timeStarted']; //10PM
            $ndEnded = $snd['timeEnded']; //06AM
            $minTime = $snd['minTime']; //30min

            $nd_in1 = $nd_out1 = $nd_in2 = $nd_out2 = '00:00';

            //in1
            if ($day_in1 == 'PM' && $in1) {

                if ($in1 >= $ndStart && $in1 <= strtotime('23:59:59')) {
                    $nd_in1 = ($in1 <= $fIn) ? $fIn : $in1;
                } else {
                    $nd_in1 = $ndStart;
                }
            }

            if ($day_in1 == 'AM' && $in1) {

                if ($in1 <= $ndEnded) {
                    $nd_in1 = ($in1 <= $fIn) ? $fIn : $in1;
                } else {
                    if (date('A', strtotime($in1)) == 'PM') {
                        $nd_in1 = ($in1 >= $fIn) ? $fIn : $in1;
                    } else {
                        $nd_in1 = $ndEnded;
                    }
                }
            }

            //out1
            if ($day_out1 == 'PM' && $out1) {

                if ($out1 >= $ndStart && $out1 <= strtotime('23:59:59')) {

                    if ($ndStart <= $fOut) {
                        $nd_out1 = ($out1 <= $fOut) ? $out1 : $fOut;
                    } else {
                        $nd_out1 = $ndStart;
                    }
                } else {
                    $nd_out1 = $ndStart;
                }
            }

            if ($day_out1 == 'AM' && $out1) {

                if ($out1 <= $ndEnded) {
                    $nd_out1 = ($out1 <= $fOut) ? $out1 : $fOut;
                } else {
                    if (date('A', strtotime($out1)) == 'PM') {
                        $nd_out1 = ($out1 >= $fOut) ? $out1 : $fOut;
                    } else {
                        $nd_out1 = $ndEnded;
                    }
                }
            }

            //in2
            if ($day_in2 == 'PM' && $in2) {

                if ($in2 >= $ndStart && $in2 <= strtotime('23:59:59')) {
                    $nd_in2 = ($in2 <= $sIn) ? $sIn : $in2;
                } else {
                    $nd_in2 = $ndStart;
                }
            }

            if ($day_in2 == 'AM' && $in2) {

                if ($in2 <= $ndEnded) {
                    $nd_in2 = ($in2 <= $sIn) ? $sIn : $in2;
                } else {
                    if (date('A', strtotime($in2)) == 'PM') {
                        $nd_in2 = ($in2 >= $sIn) ? $sIn : $in2;
                    } else {
                        $nd_in2 = $ndEnded;
                    }
                }
            }

            //out2
            if ($day_out2 == 'PM' && $out2) {
                if ($out2 >= $ndStart && $out2 <= strtotime('23:59:59')) {

                    if ($ndStart <= $sOut) {
                        $nd_out2 = ($out2 <= $sOut) ? $out2 : $sOut;
                    } else {
                        $nd_out2 = $ndStart;
                    }
                } else {
                    $nd_out2 = $ndStart;
                }
            }

            if ($day_out2 == 'AM' && $out2) {

                if ($out2 <= $ndEnded) {
                    $nd_out2 = ($out2 <= $sOut) ? $out2 : $sOut;
                } else {
                    if (date('A', strtotime($out2)) == 'PM') {
                        $nd_out2 = ($out2 >= $sOut) ? $out2 : $sOut;
                    } else {
                        $nd_out2 = $ndEnded;
                    }
                }
            }

            //nd
            $nd = 0;
            if ($night_diff == 'yes') {

                $t  = explode(":", $nd_in1);
                $h  = $t[0];
                $m  = $t[1];
                $t1 = date("H:i", strtotime("$nd_out1 -$h hours, -$m minutes"));

                $t  = explode(":", $nd_in2);
                $h  = $t[0];
                $m  = $t[1];
                $t2 = date("H:i", strtotime("$nd_out2 -$h hours, -$m minutes"));

                list($h1, $m1) = explode(":", $t1);
                list($h2, $m2) = explode(":", $t2);

                $totalMinutes = ($h1 * 60 + $m1) + ($h2 * 60 + $m2);
                $hh = floor($totalMinutes / 60);
                $mm = $totalMinutes % 60;

                $hm = sprintf("%02d:%02d", $hh, $mm);

                $t  = explode(":", $hm);
                $h  = $t[0] * 1;
                $m  = $t[1] * 1;

                if ($m >= $minTime) {
                    $m = '30';
                } else {
                    $m = '00';
                }

                $hrs = $h / 8;
                $min = ($m / 60) / 8;

                $min = round(($m / 60), 3);
                $nd =  round($h + $min, 3);
                $decimal = explode('.', (string)$h + $min);
                if (isset($decimal[1]) && strlen($decimal[1]) > 3) {
                    $nd = number_format($h + $min, 3, '.', '');
                }

                $cond = ['dutyId' => $dutyId];
                $data = ['night_diff' => $nd];
                $update = $this->promo_model->update('tk', 'perday', $cond, $data);
            }
        }

        //update if data is empty **************************************************************
        $count = 0;
        $start = date('Y-m-d', strtotime($input['startDate']));
        $end = date('Y-m-d', strtotime($input['endDate']));
        while ($start <= $end) {
            $count++;
            $start = date('Y-m-d', strtotime("$start +1 day"));
        }

        // $cond = "1 GROUP BY bioMetricId, dateDuty ORDER BY dateDuty ASC";
        // $query = $this->promo_model->select_resultArray('tk', $table, $cond);
        // if (!$query) {
        //     echo json_encode(array(
        //         'result'    => 'loading',
        //         'msg'       => round(0) . '%',
        //         'msg1'      => round(0) . '%',
        //         'date'      => "Update Table (duty/perday)",
        //         'table'     => "",
        //         'exported'  => 'table_update',
        //     ));
        //     $percentage = 0;
        //     $pDiv1    = 100 / count($empIds);
        //     foreach ($empIds as $key => $empId) {
        //         $id = '';
        //         $cond = "empId='$empId' AND barcodeId NOT LIKE '%\_%' AND bioMetricId NOT LIKE '%\_%' ORDER BY assignId DESC LIMIT 1";
        //         $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
        //         if ($assign) {
        //             $allowId = $assign['allowId'];
        //             if ($allowId == "Biometric ID") {
        //                 $id = $assign['bioMetricId'];
        //             }
        //             if ($allowId == "Logbox ID") {
        //                 $id = $assign['barcodeId'];
        //             }
        //         }
        //         $percent = $pDiv1 / $count;
        //         $dateF = date('Y-m-d', strtotime($input['startDate']));
        //         $dateT = date('Y-m-d', strtotime($input['endDate']));
        //         while (strtotime($dateF) <= strtotime($dateT)) {

        //             $percentage = $percentage + $percent;

        //             echo json_encode(array(
        //                 'result'    => 'loading',
        //                 'msg'       => round($percentage) . '%',
        //                 'msg1'      => round($percentage) . '%',
        //                 'date'      => "Updating Table (duty/perday)",
        //                 'table'     => "[Date: $dateF; biometricId: $id; empId: $empId]",
        //                 'exported'  => 'table_update'
        //             ));

        //             $cond = ['bioMetricId' => $id, 'dateDuty' => $dateF];
        //             $data = ['in1' => '', 'out1' => '', 'in2' => '', 'out2' => '', 'in3' => '', 'out3' => ''];
        //             $this->promo_model->update('tk', 'duty', $cond, $data);
        //             $this->netEntery($empId, $id, $dateF, '', '', '', '', '', '');

        //             $dateF = date('Y-m-d', strtotime("$dateF +1 day"));
        //         }
        //     }
        // }

        $percentage = 0;
        $pDiv1 = 100 / $count;
        $start = date('Y-m-d', strtotime($input['startDate']));
        $end = date('Y-m-d', strtotime($input['endDate']));
        while ($start <= $end) {

            $percent = $pDiv1 / count($empIds);
            foreach ($empIds as $key => $empId) {
                $id = '';
                $cond = "empId='$empId' AND barcodeId NOT LIKE '%\_%' AND bioMetricId NOT LIKE '%\_%' ORDER BY assignId DESC LIMIT 1";
                $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
                if ($assign) {
                    $allowId = $assign['allowId'];
                    if ($allowId == "Biometric ID") {
                        $id = $assign['bioMetricId'];
                    }
                    if ($allowId == "Logbox ID") {
                        $id = $assign['barcodeId'];
                    }
                } else {
                    $cond = "empId='$empId' AND status='active'";
                    $id = $this->promo_model->select_rowArray('tk', 'promo_id', $cond)['barcodeId'];
                }
                $percentage += $percent;
                $cond = ['bioMetricId' => $id, 'dateDuty' => $start];
                $query = $this->promo_model->select_rowArray('tk', $table, $cond);
                if (!$query) {
                    echo json_encode(array(
                        'result'    => 'loading',
                        'msg'       => round($percentage) . '%',
                        'msg1'      => round($percentage) . '%',
                        'date'      => "Updating Table (duty/perday) NO DATA",
                        'table'     => "[Date: $start; biometricId: $id; empId: $empId]",
                        'exported'  => 'table_update'
                    ));

                    $cond   = ['bioMetricId' => $id, 'dateDuty' => $start];
                    $dutyId = $this->promo_model->select_rowArray('tk', 'duty', $cond)['dutyId'];
                    if ($dutyId) {
                        $cond = ['bioMetricId' => $id, 'dateDuty' => $start];
                        $data = ['in1' => '', 'out1' => '', 'in2' => '', 'out2' => '', 'in3' => '', 'out3' => ''];
                        $this->promo_model->update('tk', 'duty', $cond, $data);

                        $cond   = ['dutyId' => $dutyId];
                        $check  = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                        if ($check) {
                            $this->netEntery($empId, $id, $start, '', '', '', '', '', '');
                        }
                    }
                }
            }
            $start = date('Y-m-d', strtotime("$start +1 day"));
        }

        unlink($filePath);
        $this->promo_model->drop_table($table); // delete table

        echo json_encode(array(
            'result'    => 'loading',
            'msg'       => round(100) . '%',
            'msg1'      => round(100) . '%',
            'date'      => "Process Complete!",
            'table'     => "",
            'exported'  => 'initialize'
        ));

        sleep(1);

        echo json_encode(array(
            'result'    => 'success',
            'msg'       => round(100) . '%',
            'msg1'      => round(100) . '%',
            'date'      => "Process Complete!",
            'table'     => "",
            'exported'  => 'initialize'
        ));
    }

    public function bioPromoList()
    {
        echo '<table class="table table-sm table-hover" id="bioPromoList" style="font-size:small;">
                <thead>
                    <tr>
                        <th width="12%">HRMS ID</th>
                        <th width="30%">Name</th>
                        <th>Type</th>
                        <th>Company</th>
                        <th>Store</th>
                        <th><input type="checkbox" name="chkAll" onclick="chkAllbox(\'bioPromoList\')"></th>
                    </tr>
                </thead>';

        $bus = $this->promo_model->bu_process('corporate');
        $cond = "status='active' GROUP BY empId";
        $query = $this->promo_model->select_resultArray('tk', 'promo_id', $cond);
        foreach ($query as $key => $list) {

            $empId = $list['empId'];

            $cond = "e.emp_id='$empId' AND (current_status = 'Active' OR (current_status IN ('Ad-Resigned', 'End of Contract', 'V-Resigned') AND sub_status = 'Uncleared'))";
            $row  = $this->promo_model->promoDetailsRow($cond);
            if ($row) {
                $i = 0;
                $store = '';
                foreach ($bus as $key => $bu) {
                    $i++;
                    $cond = ['bunit_acronym' => $bu];
                    $field = $this->promo_model->select_rowArray('pis', 'locate_promo_business_unit', $cond)['bunit_field'];
                    if ($row[$field] == 'T') {
                        $store .= ($i == 1) ? '<span class="badge bg-secondary mr-1">' . $bu . '</span>' : '<span class="badge bg-secondary mr-1">' . $bu . '</span>';
                    }
                }

                echo    '<tr>
							<td>' . $row['emp_id'] . '</td>
							<td>' . $row['name'] . '</td>
							<td>' . $row['promo_type'] . '</td>
							<td>' . $row['promo_company'] . '</td>
							<td>' . $store . '</td>
							<td><input type="checkbox" class="bioPromoList" name="empId[]" value="' . $row['emp_id'] . '"></td>
						</tr>';
            }
        }

        echo '</table>';
    }

    public function processBiometric()
    {
        $input      = $this->input->post(NULL, TRUE);
        $empIds     = $input['empId'];
        $startDate  = $input['startDate'];
        $endDate    = $input['endDate'];
        $data_log   = $input['data_log'];
        $terminal   = 'Logbox';

        $userName   = ucwords($this->userName);
        $timeHis    = date("His");

        $dateFrom   = date('Y-m-d', strtotime($startDate));
        $dateTo     = date('Y-m-d', strtotime("$endDate"));

        set_time_limit(0);
        ob_implicit_flush(true);
        ob_end_flush();

        if ($data_log == 'logfile') {

            echo json_encode(array(
                'result'    => 'loading',
                'msg'       => round(0) . '%',
                'msg1'      => round(0) . '%',
                'date'      => "Creating Logfile Data (Text File)",
                'table'     => "",
                'exported'  => 'table_update',
            ));

            sleep(1);

            $percentage = 0;
            $pdiv1      = 100 / count($empIds);

            $all_entries = '';
            $addedEntries = [];
            foreach ($empIds as $key => $empId) {

                $cond   = "empId='$empId' AND status='active' GROUP BY baseId";
                $pid    = $this->promo_model->select_rowArray('tk', 'promo_id', $cond);

                $bioM   = $pid['biometricId'];
                $barC   = $pid['barcodeId'];
                $baseId = $pid['baseId'];

                if ($baseId == 'alta_cita') {
                    $store = 'alta_cita';
                } else if ($baseId == 'cdc') {
                    $store = 'cortes';
                } else if ($baseId == 'fixrite_panglao') {
                    $store = 'fixrite_panglao';
                } else if ($baseId == 'fr_panglao') {
                    $store = 'fixrite_panglao';
                } else if ($baseId == 'alturas_panglao') {
                    $store = 'fixrite_panglao';
                } else {
                    $store = $baseId;
                }

                $cond = ['baseId' => $store, 'terminal' => 'Biometric'];
                if ($baseId == 'fixrite_panglao' || $baseId == 'alturas_panglao') {
                    $cond = "(baseId='fixrite_panglao' OR baseId='alturas_panglao') AND terminal='Biometric'";
                }

                $df = date('Y-m-1', strtotime($startDate));
                $dt = date('Y-m-1', strtotime($endDate));
                while (strtotime($df) <= strtotime($dt)) {

                    $my     = date("F Y", strtotime($df));
                    $month  = strtoupper(date("M", strtotime($df)));

                    // $cond = "terminal='Biometric' AND baseId='$store'";
                    $query  = $this->promo_model->select_resultArray('tk', 'id_biometric_logbox', $cond);

                    $pdiv2  = $pdiv1 / count($query);

                    foreach ($query as $key => $row) {
                        $logNo = $row['code'];

                        $percent    = $pdiv2 / count($bioEntries);
                        $path       = FCPATH . "../upload/Backup_Month_Year_Others/$store/Biometric/$my/$month$logNo.LOG";
                        $bioEntries = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                        foreach ($bioEntries as $key => $lines) {

                            $bio    = substr($lines, 0, 11);
                            $date   = substr($lines, 11, 8);
                            $time   = substr($lines, 19, 6);
                            $mode   = substr($lines, 25, 1);

                            $m = substr($date, 0, 2);
                            $d = substr($date, 2, 2);
                            $y = substr($date, 4, 4);

                            $Ymd = date("Y-m-d", strtotime("$y-$m-$d"));

                            $percentage += $percent;

                            if (strtotime($dateFrom) <= strtotime($Ymd) && strtotime($Ymd) <= strtotime($dateTo)) {

                                if ($bioM == $bio) {

                                    $entry = "$barC$date$time$mode$bioM$baseId\r\n";
                                    if (!in_array($entry, $addedEntries)) {
                                        $all_entries .= $entry;
                                        $addedEntries[] = $entry;

                                        echo json_encode(array(
                                            'result'    => 'loading',
                                            'msg'       => round($percentage) . '%',
                                            'msg1'      => round($percentage) . '%',
                                            'date'      => "Initializing: $my",
                                            'table'     => "[Date: $Ymd; biometricId: $bioM; barcodeId: $barC; baseId: $baseId]",
                                            'exported'  => 'table_update'
                                        ));
                                    }
                                }
                            }

                            if (round($percentage) == round(100)) {
                                echo json_encode(array(
                                    'result'    => 'loading',
                                    'msg'       => round($percentage) . '%',
                                    'msg1'      => round($percentage) . '%',
                                    'date'      => "Finalizing Logfile Data",
                                    'table'     => "",
                                    'exported'  => 'table_update'
                                ));
                            }
                        }
                    }

                    $df = date("Y-m", strtotime("$df +1 month"));
                }
            }

            //create logfile **********************************************************************
            $ext = $terminal . $userName . 'time' . $timeHis;
            $filePath = FCPATH . "../upload/mergeMonth$ext.LOG";
            $fp = fopen($filePath, 'w');
            if (!$fp) {
                die('Could not create / open text file for writing.');
            }

            if (fwrite($fp, $all_entries) === false) {
                die('Could not write to text file.');
            }
            fclose($fp);

            //create temporary table **********************************************************************
            $table = "dupduty_$userName$timeHis";
            echo json_encode(array(
                'result'    => 'loading',
                'msg'       => round(0) . '%',
                'msg1'      => round(0) . '%',
                'date'      => "Creating Temporary Table ($table)",
                'table'     => "",
                'exported'  => 'table_update',
            ));

            sleep(1);

            $create = $this->promo_model->dupduty_user($table);
            if ($create) {

                $df = date('Y-m-d', strtotime($startDate));
                $dt = date('Y-m-d', strtotime($endDate));

                // $data   = file_get_contents($filePath);
                // $lines  = explode(PHP_EOL, $data);
                $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $percentage = 0;
                $percent = 100 / ($lines ? count($lines) : 1);
                foreach ($lines as $key => $row) {

                    if ($terminal == "Logbox") {
                        $id     = substr($row, 0, 13);
                        $date   = substr($row, 13, 8);
                        $time   = substr($row, 21, 6);
                        $mode   = substr($row, 27, 1);
                    } else {
                        $id     = substr($row, 0, 11);
                        $date   = substr($row, 11, 8);
                        $time   = substr($row, 19, 6);
                        $mode   = substr($row, 25, 1);
                    }

                    $month  = substr($date, 0, 2);
                    $day    = substr($date, 2, 2);
                    $year   = substr($date, 4, 4);

                    $d  = "$year-$month-$day";
                    $d1 = "$month-$day-$year";

                    $date   = date('Y-m-d', strtotime("$year-$month-$day"));
                    $date1  = date('m-d-Y', strtotime("$month-$day-$year"));

                    $h = substr($time, 0, 2);
                    $m = substr($time, 2, 2);
                    $s = substr($time, 4, 2);

                    $time = date('H:i:s', strtotime("$h:$m:$s"));

                    $ti = ($mode == 'I') ? $time : '0';
                    $to = ($mode == 'I') ? '0' : $time;

                    $datetime = date('Y-m-d H:i:s', strtotime("$date $time"));

                    $percentage += $percent;

                    echo json_encode(array(
                        'result'    => 'loading',
                        'msg'       => round($percentage) . '%',
                        'msg1'      => round($percentage) . '%',
                        'date'      => "Inserting Temporary Table ($table)",
                        'table'     => "[Date: $date; biometricId: $id; timeIn: $ti; timeOut: $to; mode: $mode]",
                        'exported'  => 'table_update'
                    ));

                    if (strtotime($df) <= strtotime($date) && strtotime($date) <= strtotime($dt)) {

                        $data = ['bioMetricId' => $id, 'date' => $d1, 'dateDuty' => $date, 'mode' => $mode];
                        $cond = ['bioMetricId' => $id, 'dateDuty' => $date];
                        if ($mode == 'I') {
                            $data['timeIn']         = $time;
                            $data['timeOut']        = '0';
                            $data['dateTimeIn']     = $datetime;
                            $data['dateTimeOut']    = '0';

                            $cond['timeIn']         = $time;
                            $cond['timeOut']        = '0';
                        }

                        if ($mode == 'O') {
                            $data['timeIn']         = '0';
                            $data['timeOut']        = $time;
                            $data['dateTimeIn']     = '0';
                            $data['dateTimeOut']    = $datetime;

                            $cond['timeIn']         = '0';
                            $cond['timeOut']        = $time;
                        }

                        $query = $this->promo_model->select_rowArray('tk', $table, $cond);
                        if (!$query) {
                            $insert = $this->promo_model->insert('tk', $table, $data);
                        }
                    }
                }
            }

            //insert duty/duty_next **************************************************************
            echo json_encode(array(
                'result'    => 'loading',
                'msg'       => round(0) . '%',
                'msg1'      => round(0) . '%',
                'date'      => "Inserting Tables (duty,duty_next)",
                'table'     => "",
                'exported'  => 'table_update',
            ));

            sleep(1);

            $count = 0;
            $dateF = date('Y-m-01', strtotime($input['startDate']));
            $dateT = date('Y-m-t', strtotime($input['endDate']));
            while (strtotime($dateF) <= strtotime($dateT)) {
                $count++;
                $dateF = date('Y-m-d', strtotime("$dateF +1 day"));
            }

            $cond = "1 GROUP BY bioMetricId, YEAR(date), MONTH(date)";
            $query = $this->promo_model->select_resultArray('tk', $table, $cond);

            $percentage = 0;
            $pdiv1 = 100 / ($query ? count($query) : 1);
            foreach ($query as $key => $row) {

                $id     = $row['bioMetricId'];
                $date   = date('Y-m-d', strtotime($row['dateDuty']));

                $dateF = date('Y-m-01', strtotime($input['startDate']));
                $dateT = date('Y-m-t', strtotime($input['endDate']));

                $percent = $pdiv1 / $count;

                while (strtotime($dateF) <= strtotime($dateT)) {

                    $percentage += $percent;

                    echo json_encode(array(
                        'result'    => 'loading',
                        'msg'       => round($percentage) . '%',
                        'msg1'      => round($percentage) . '%',
                        'date'      => "Initializing: $id",
                        'table'     => "[Date: $dateF]",
                        'exported'  => 'table_update'
                    ));

                    $cond   = ['bioMetricId' => $id, 'dateDuty' => $dateF];
                    $check  = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                    if (!$check) {
                        $data = ['bioMetricId' => $id, 'date' => $dateF, 'dateDuty' => $dateF, 'status' => 'Pending'];
                        $insert = $this->promo_model->insert('tk', 'duty', $data);
                    }

                    $cond   = ['bioMetricId_ka' => $id, 'dateDuty_ka' => $dateF];
                    $check  = $this->promo_model->select_rowArray('tk', 'duty_next', $cond);
                    if (!$check) {
                        $data = ['bioMetricId_ka' => $id, 'dateDuty_ka' => $dateF];
                        $insert = $this->promo_model->insert('tk', 'duty_next', $data);
                    }

                    $dateF = date('Y-m-d', strtotime("$dateF +1 day"));
                }
            }

            //insert 1stcutoff/2ndcutoff **************************************************************
            echo json_encode(array(
                'result'    => 'loading',
                'msg'       => round(0) . '%',
                'msg1'      => round(0) . '%',
                'date'      => "Inserting Tables (1stcutoff,2ndcutoff)",
                'table'     => "",
                'exported'  => 'table_update',
            ));

            sleep(1);
            $count = 0;
            $cosDate = date('Y-m-01', strtotime($input['startDate']));
            $coeDate = date('Y-m-01', strtotime($input['endDate']));
            while (strtotime($cosDate) <= strtotime($coeDate)) {
                $count++;
                $cosDate = date('Y-m-01', strtotime("$cosDate +1 month"));
            }

            $cond = "1 GROUP BY bioMetricId, YEAR(date), MONTH(date)";
            $query = $this->promo_model->select_resultArray('tk', $table, $cond);

            $percentage = 0;
            $pdiv1 = 100 / ($query ? count($query) : 1);
            foreach ($query as $key => $row) {

                $pdiv2 = $pdiv1 / $count;
                $cosDate = date('Y-m-01', strtotime($input['startDate']));
                $coeDate = date('Y-m-01', strtotime($input['endDate']));
                while (strtotime($cosDate) <= strtotime($coeDate)) {

                    $id = $row['bioMetricId'];
                    $date = date('Y-m-01', strtotime($cosDate));


                    $cond   = "biometricId='$id' OR barcodeId='$id' AND status='active'";
                    $empId  = $this->promo_model->select_rowArray('tk', 'promo_id', $cond)['empId'];
                    // echo json_encode(array(
                    //     'result'    => 'loading',
                    //     'msg'       => round($percentage) . '%',
                    //     'msg1'      => round($percentage) . '%',
                    //     'date'      => "Initializingsss: $id",
                    //     'table'     => "[empId: $empId;]",
                    //     'exported'  => 'table_update'
                    // ));
                    if (!$empId) {
                        $cond   = "bioMetricId='$id' OR barcodeId='$id' ORDER BY assignId DESC";
                        $empId  = $this->promo_model->select_rowArray('tk', 'assign', $cond)['empId'];
                        // echo json_encode(array(
                        //     'result'    => 'loading',
                        //     'msg'       => round($percentage) . '%',
                        //     'msg1'      => round($percentage) . '%',
                        //     'date'      => "Initializing: $id",
                        //     'table'     => "[empId: $empId;]",
                        //     'exported'  => 'table_update'
                        // ));
                    }



                    $cond       = ['emp_id' => $empId];
                    $recordNo   = $this->promo_model->select_rowArray('pis', 'employee3', $cond)['record_no'];

                    $cond       = ['empId' => $empId, 'recordNo' => $recordNo];
                    $statCut    = $this->promo_model->select_rowArray('tk', 'promo_sched_emp', $cond)['statCut'];
                    if (!$statCut) {
                        $cond       = "empId='$empId' ORDER BY date_setup DESC";
                        $statCut    = $this->promo_model->select_rowArray('tk', 'promo_sched_emp', $cond)['statCut'];
                    }

                    $cond   = ['statCut' => $statCut];
                    $ps     = $this->promo_model->select_rowArray('tk', 'promo_schedule', $cond);
                    $sFC    = $ps['startFC'];
                    $eFC    = ($ps['endFC'] == '') ? date('t', strtotime($cosDate)) : $ps['endFC'];
                    $cFC    = $ps['clearingFC'];
                    $sSC    = $ps['startSC'];
                    $eSC    = $ps['endSC'];
                    $cSC    = $ps['clearingSC'];

                    $t = explode('-', $date);
                    $y = $t[0];
                    $m = $t[1];
                    $d = $t[2];

                    $startFC = date('Y-m-d', strtotime("$y-$m-$sFC"));
                    $endFC = date('Y-m-d', strtotime("$y-$m-$eFC"));
                    if ($ps['startFC'] > 16) {
                        $endFC = date('Y-m-d', strtotime("$y-$m-$eFC +1 month"));
                    }

                    $startSC = date('Y-m-d', strtotime("$y-$m-$sSC"));
                    $endSC = date('Y-m-d', strtotime("$y-$m-$eSC"));
                    if ($ps['startSC'] > 16) {
                        $endSC = date('Y-m-d', strtotime("$y-$m-$eSC +1 month"));
                    }

                    $percentage += $pdiv2;

                    echo json_encode(array(
                        'result'    => 'loading',
                        'msg'       => round($percentage) . '%',
                        'msg1'      => round($percentage) . '%',
                        'date'      => "Initializing: $id",
                        'table'     => "[empId: $empId; dateFrom: $startFC; dateTo: $endFC; statCut: $statCut;]",
                        'exported'  => 'table_update'
                    ));

                    $cond = ['bioMetricId' => $id, 'dateFrom' => $startFC, 'dateTo' => $endFC, 'statCut' => $statCut];
                    $check = $this->promo_model->select_rowArray('tk', '1stcutoff', $cond);
                    if (!$check) {
                        $data = [
                            'bioMetricId'   => $id,
                            'dateFrom'      => $startFC,
                            'dateTo'        => $endFC,
                            'statCut'       => $statCut,
                            'date_added'    => date('Y-m-d'),
                            'time_added'    => date('H:i:s'),
                        ];
                        $insert = $this->promo_model->insert('tk', '1stcutoff', $data);
                    }

                    $cond = ['bioMetricId' => $id, 'dateFrom' => $startSC, 'dateTo' => $endSC, 'statCut' => $statCut];
                    $check = $this->promo_model->select_rowArray('tk', '2ndcutoff', $cond);
                    if (!$check) {
                        $data = [
                            'bioMetricId'   => $id,
                            'dateFrom'      => $startSC,
                            'dateTo'        => $endSC,
                            'statCut'       => $statCut,
                            'date_added'    => date('Y-m-d'),
                            'time_added'    => date('H:i:s'),
                        ];
                        $insert = $this->promo_model->insert('tk', '2ndcutoff', $data);
                    }

                    // echo json_encode(array(
                    //     'result'    => 'loading',
                    //     'msg'       => round($percentage) . '%',
                    //     'msg1'      => round($percentage) . '%',
                    //     'date'      => "Initializing: $id test",
                    //     'table'     => "[date: $date;]",
                    //     'exported'  => 'table_update'
                    // ));

                    $cosDate = date('Y-m-d', strtotime("$cosDate +1 month"));
                }
            }

            //insert duty_logbox/duty_biometric table **************************************************************
            echo json_encode(array(
                'result'    => 'loading',
                'msg'       => round(0) . '%',
                'msg1'      => round(0) . '%',
                'date'      => "Inserting Tables (duty_logbox,duty_biometric)",
                'table'     => "",
                'exported'  => 'table_update',
            ));

            sleep(1);

            $df = date('Y-m-d', strtotime($startDate));
            $dt = date('Y-m-d', strtotime($endDate));
            $duty_table = ($terminal == "Logbox") ? "duty_logbox" : "duty_biometric";

            // $data   = file_get_contents($filePath);
            // $lines  = explode(PHP_EOL, $data);
            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $percentage = 0;
            $percent = 100 / ($lines ? count($lines) : 1);
            foreach ($lines as $key => $row) {

                $str_len    = strlen(trim($row));
                $len_sub    = $str_len - 39;

                if ($terminal == "Logbox") {
                    $id     = substr($row, 0, 13);
                    $date   = substr($row, 13, 8);
                    $time   = substr($row, 21, 6);
                    $mode   = substr($row, 27, 1);
                    $bio    = substr($row, 28, 11);
                    $baseId = substr($row, 39, $len_sub);
                } else {
                    $id     = substr($row, 0, 11);
                    $date   = substr($row, 11, 8);
                    $time   = substr($row, 19, 6);
                    $mode   = substr($row, 25, 1);
                    $bio    = substr($row, 26, 11);
                    $baseId = substr($row, 37, $len_sub);
                }

                $month  = substr($date, 0, 2);
                $day    = substr($date, 2, 2);
                $year   = substr($date, 4, 4);

                $d  = "$year-$month-$day";
                $d1 = "$month-$day-$year";

                $date   = date('Y-m-d', strtotime("$year-$month-$day"));
                $date1  = date('m-d-Y', strtotime("$month-$day-$year"));

                $h = substr($time, 0, 2);
                $m = substr($time, 2, 2);
                $s = substr($time, 4, 2);

                $time = date('H:i:s', strtotime("$h:$m:$s"));

                $ti = ($mode == 'I') ? $time : '0';
                $to = ($mode == 'I') ? '0' : $time;

                $datetime = date('Y-m-d H:i:s', strtotime("$date $time"));

                $percentage += $percent;

                echo json_encode(array(
                    'result'    => 'loading',
                    'msg'       => round($percentage) . '%',
                    'msg1'      => round($percentage) . '%',
                    'date'      => "Inserting Table ($duty_table)",
                    'table'     => "[Date: $date; biometricId: $id; timeIn: $ti; timeOut: $to; mode: $mode]",
                    'exported'  => 'table_update'
                ));

                if (strtotime($df) <= strtotime($date) && strtotime($date) <= strtotime($dt)) {

                    $data = [
                        'bioMetricId'   => $id,
                        'date'          => $d1,
                        'dateDuty'      => $date,
                        'mode'          => $mode,
                        'store'         => $baseId,
                        'store_bio_no'  => $bio
                    ];

                    $cond = ['bioMetricId' => $id, 'dateDuty' => $date];
                    if ($mode == 'I') {
                        $data['timeIn']         = $time;
                        $data['timeOut']        = '0';
                        $data['dateTimeIn']     = $datetime;
                        $data['dateTimeOut']    = '0';

                        $cond['timeIn']         = $time;
                        $cond['timeOut']        = '0';
                    }

                    if ($mode == 'O') {
                        $data['timeIn']         = '0';
                        $data['timeOut']        = $time;
                        $data['dateTimeIn']     = '0';
                        $data['dateTimeOut']    = $datetime;

                        $cond['timeIn']         = '0';
                        $cond['timeOut']        = $time;
                    }

                    // $cond = ['bioMetricId' => $id, 'dateDuty' => $date];
                    $query = $this->promo_model->select_rowArray('tk', $duty_table, $cond);
                    if (!$query) {
                        $insert = $this->promo_model->insert('tk', $duty_table, $data);
                    }
                }
            }

            //update duty/duty_next entries **************************************************************
            $cond = "1 GROUP BY bioMetricId, dateDuty ORDER BY dateDuty ASC";
            $query = $this->promo_model->select_resultArray('tk', $table, $cond);

            $percentage = 0;
            $totalLoad  = count($query);
            foreach ($query as $key => $row) {

                $id     = $row['bioMetricId'];
                $date   = date('Y-m-d', strtotime($row['dateDuty']));

                $cond   = "bioMetricId='$id' OR barcodeId='$id'";
                $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
                $empId  = $assign['empId'];
                if ($assign['shiftCode']) {
                    $shiftCode = $assign['shiftCode'];
                }

                $day = date('l', strtotime($row['dateDuty']));
                $no_schedule = $this->noSchedule($empId, $date, $day);

                $cond   = ['empId' => $empId, 'day_sched' => $day];
                $pds    = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
                if ($pds) {
                    $shiftCode = $pds['shiftCode'];
                }

                $cond   = "empId='$empId' AND status='active' AND status2='Approved' AND '$date' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
                $ams    = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond);
                if ($ams) {
                    $shiftCode = $ams['shiftCode'];
                }

                $four_entries = false;
                $cond   = ['shiftCode' => $shiftCode];
                $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
                if ($sc) {

                    $fIn    = date("H:i:s", strtotime($sc['1stIn']));
                    $fOut   = date("H:i:s", strtotime($sc['1stOut']));
                    $sIn    = date("H:i:s", strtotime($sc['2ndIn']));
                    $sOut   = date("H:i:s", strtotime($sc['2ndOut']));

                    $s_in1  = $sc['1stIn'];
                    $s_out1 = $sc['1stOut'];
                    $s_in2  = $sc['2ndIn'];
                    $s_out2 = $sc['2ndOut'];

                    if ($fIn && $fOut && $sIn && $sOut) {
                        $four_entries = true;
                    }
                }

                $cond   = ['shiftCode' => $shiftCode];
                $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
                if ($scb) {

                    $four_entries = true;

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

                $day_outH   = date("H", strtotime($s_out2));
                $day_outM   = date("i", strtotime($s_out2));

                if ($four_entries) {

                    if (($day_in1 == 'AM' && $day_out2 == 'PM') || ($day_in1 == 'AM' && $day_out2 == 'AM') || ($day_in1 == 'PM' && $day_out2 == 'PM')) {

                        $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';

                        $time_in1   = date("H:i:s", strtotime($s_in1));
                        $time_out1  = date("H:i:s", strtotime($s_out1));
                        $time_in2   = date("H:i:s", strtotime($s_in2));
                        $time_out2  = date("H:i:s", strtotime($s_out2));

                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn < '$time_out1' ORDER BY timeIn ASC";
                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                        if ($check) {
                            $in1 = $check['timeIn'];
                            $time_in1 = $check['timeIn'];
                        } else {
                            $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn < '$time_out1' ORDER BY timeIn ASC";
                            $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                            if ($check) {
                                $in1 = $check['timeIn'];
                                $time_in1 = $check['timeIn'];
                            }
                        }

                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut < '$time_in2' AND timeOut > '$time_in1' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                        if ($check) {
                            $out1 = $check['timeOut'];
                            $time_out1 = $check['timeOut'];
                        } else {
                            $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut < '$time_in2' AND timeOut > '$time_in1' ORDER BY timeOut ASC";
                            $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                            if ($check) {
                                $out1 = $check['timeOut'];
                                $time_out1 = $check['timeOut'];
                            }
                        }

                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND timeIn < '$time_out2' ORDER BY timeIn ASC";
                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                        if ($check) {
                            $in2 = $check['timeIn'];
                            $time_in2 = $check['timeIn'];
                        } else {
                            $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND timeIn < '$time_out2' ORDER BY timeIn ASC";
                            $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                            if ($check) {
                                $in2 = $check['timeIn'];
                                $time_in2 = $check['timeIn'];
                            }
                        }

                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_in2' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                        if ($check) {
                            $out2 = $check['timeOut'];
                            $time_out2 = $check['timeOut'];

                            if ($time_out2 < "$sOut:00") {
                                $time_out2 = "$sOut:00";
                            }
                        } else {
                            $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_in2' ORDER BY timeOut ASC";
                            $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                            if ($check) {
                                $out2 = $check['timeOut'];
                                $time_out2 = $check['timeOut'];

                                if ($time_out2 < "$sOut:00") {
                                    $time_out2 = "$sOut:00";
                                }
                            } else {
                                $tomorrow = date("Y-m-d", strtotime("$date +1 day"));
                                $cond   = "bioMetricId='$id' AND dateDuty='$tomorrow' AND mode='O' AND timeOut < '$time_in1' ORDER BY timeOut ASC";
                                $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                if ($check) {
                                    $out2 = $check['timeOut'];
                                }
                            }
                        }

                        if ($out2 != '') {
                            $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out2' AND timeIn > '$out2' ORDER BY timeIn ASC";
                            $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                            if ($check) {
                                $in3 = $check['timeIn'];
                            } else {
                                $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out2' AND timeIn > '$out2' ORDER BY timeIn ASC";
                                $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                if ($check) {
                                    $in3 = $check['timeIn'];
                                }
                            }
                        }

                        if ($in3 != '') {
                            $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_out2' AND timeOut > '$in3' ORDER BY timeOut ASC";
                            $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                            if ($check) {
                                $out3 = $check['timeOut'];
                            } else {
                                $tomorrow = date("Y-m-d", strtotime("$date +1 day"));
                                $to_day = date("Y-m-d H:i:s", strtotime("$date $in3"));
                                $cond   = "bioMetricId='$id' AND dateDuty='$tomorrow' AND mode='O' AND timeOut < '$fIn' AND dateTimeOut > '$to_day' ORDER BY timeOut ASC";
                                $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                if ($check) {
                                    $out3 = $check['timeOut'];
                                }
                            }
                        }
                    }

                    if ($day_in1 == 'PM' && $day_out1 == 'AM' && $day_in2 == 'AM' && $day_out2 == 'AM') {

                        $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';

                        $time_in1   = date("H:i:s", strtotime($s_in1));
                        $time_out1  = date("H:i:s", strtotime($s_out1));
                        $time_in2   = date("H:i:s", strtotime($s_in2));
                        $time_out2  = date("H:i:s", strtotime($s_out2));

                        $dt_in1     = date("Y-m-d H:i:s", strtotime("$date $s_in1"));
                        $dt_out1    = date("Y-m-d H:i:s", strtotime("$date $s_out1"));
                        $dt_in2     = date("Y-m-d H:i:s", strtotime("$date $s_in2"));
                        $dt_out2    = date("Y-m-d H:i:s", strtotime("$date $s_out2"));

                        $date_tom       = date("Y-m-d", strtotime("$date +1 day"));
                        $dt_in1_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in1"));
                        $dt_out1_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out1"));
                        $dt_in2_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in2"));
                        $dt_out2_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out2"));

                        // shift 10PM - 2AM | 3AM - 6AM
                        // entry 9:59PM - 2:01AM | 2:59AM - 6:01AM

                        // 9:59PM > 2:00AM
                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND dateTimeIn > '$dt_out1' ORDER BY timeIn ASC";
                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                        if ($check) {
                            $in1 = $check['timeIn'];
                            $time_in1 = $check['timeIn'];
                        }

                        // 2:01AM < 3AM AND 2025-11-10 2:01AM > 2025-11-09 10:00PM
                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut < '$time_in2' AND dateTimeOut > '$dt_in1' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $out1 = $check['timeOut'];
                            $time_out1 = $check['timeOut'];
                        }

                        // 2:59AM > 2:01AM AND 2025-11-10 2:59AM < 2025-11-10 06:00AM
                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn > '$time_out1' AND dateTimeIn < '$dt_out2_tom' ORDER BY timeIn ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $in2 = $check['timeIn'];
                            $time_in2 = $check['timeIn'];
                        }

                        // 6:01AM > 2:59AM AND 2025-11-10 6:01AM < 2025-11-10 10:00PM
                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_in2' AND dateTimeOut < '$dt_in1_tom' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $out2 = $check['timeOut'];
                            $time_out2 = $check['timeOut'];
                        }

                        if ($out2 != '') {
                            $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn > '$time_out2' AND dateTimeIn < '$dt_in1_tom' ORDER BY timeIn ASC";
                            $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                            if ($check) {
                                $in3 = $check['timeIn'];
                            }
                        }

                        if ($in3 != '') {
                            $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_out2' AND timeOut > '$in3' ORDER BY timeOut ASC";
                            $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                            if ($check) {
                                $out3 = $check['timeOut'];
                            }
                        }
                    }

                    if ($day_in1 == 'PM' && $day_out1 == 'PM' && $day_in2 == 'AM' && $day_out2 == 'AM') {

                        $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';

                        $time_in1   = date("H:i:s", strtotime($s_in1));
                        $time_out1  = date("H:i:s", strtotime($s_out1));
                        $time_in2   = date("H:i:s", strtotime($s_in2));
                        $time_out2  = date("H:i:s", strtotime($s_out2));

                        $dt_in1     = date("Y-m-d H:i:s", strtotime("$date $s_in1"));
                        $dt_out1    = date("Y-m-d H:i:s", strtotime("$date $s_out1"));
                        $dt_in2     = date("Y-m-d H:i:s", strtotime("$date $s_in2"));
                        $dt_out2    = date("Y-m-d H:i:s", strtotime("$date $s_out2"));

                        $date_tom       = date("Y-m-d", strtotime("$date +1 day"));
                        $dt_in1_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in1"));
                        $dt_out1_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out1"));
                        $dt_in2_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in2"));
                        $dt_out2_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out2"));

                        // shift 10PM - 11PM | 12AM - 6AM
                        // entry 9:59PM - 11:01PM | 11:59PM - 6:01AM

                        // 9:59PM < 11:00PM AND 2025-11-09 9:59PM < 2025-11-09 11:00PM
                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn < '$time_out1' AND dateTimeIn < '$dt_out1' ORDER BY timeIn ASC";
                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                        if ($check) {
                            $in1 = $check['timeIn'];
                            $time_in1 = $check['timeIn'];
                        }

                        // 11:01PM > 12AM AND 2025-11-09 11:01PM > 2025-11-09 10:00PM
                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_in2' AND dateTimeOut > '$dt_in1' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                        if ($check) {
                            $out1 = $check['timeOut'];
                            $time_out1 = $check['timeOut'];
                        }

                        // if dili late 
                        // 11:59PM > 11:00PM AND 2025-11-09 11:59PM < 2025-11-10 06:00AM
                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND dateTimeIn < '$dt_out2_tom' ORDER BY timeIn ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $in2 = $check['timeIn'];
                            $time_in2 = $check['timeIn'];
                        } else {
                            // if late 
                            // 12:01AM < 11:00PM AND 2025-11-10 12:01AM < 2025-11-10 06:00AM
                            $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn < '$time_out1' AND dateTimeIn < '$dt_out2_tom' ORDER BY timeIn ASC";
                            $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                            if ($check) {
                                $in2 = $check['timeIn'];
                                $time_in2 = $check['timeIn'];
                            }
                        }

                        // if dili late 
                        // 6:01AM < 11:59PM AND 2025-11-10 6:01AM < 2025-11-10 10:00PM
                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut < '$time_in2' AND dateTimeOut < '$dt_in1_tom' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $out2 = $check['timeOut'];
                            $time_out2 = $check['timeOut'];
                        } else {
                            // if late 
                            // 6:01AM > 12:01AM AND 2025-11-10 6:01AM > 2025-11-10 10:00PM
                            $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_in2' AND dateTimeOut > '$dt_in1_tom' ORDER BY timeOut ASC";
                            $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                            if ($check) {
                                $out2 = $check['timeOut'];
                                $time_out2 = $check['timeOut'];
                            }
                        }


                        if ($out2 != '') {
                            $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn > '$time_out2' AND dateTimeIn < '$dt_in1_tom' ORDER BY timeIn ASC";
                            $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                            if ($check) {
                                $in3 = $check['timeIn'];
                            }
                        }

                        if ($in3 != '') {
                            $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_out2' AND timeOut > '$in3' ORDER BY timeOut ASC";
                            $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                            if ($check) {
                                $out3 = $check['timeOut'];
                            }
                        }
                    }

                    if ($day_in1 == 'PM' && $day_out1 == 'PM' && $day_in2 == 'PM' && $day_out2 == 'AM') {

                        $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';

                        $time_in1       = date("H:i:s", strtotime($s_in1));
                        $time_out1      = date("H:i:s", strtotime($s_out1));
                        $time_in2       = date("H:i:s", strtotime($s_in2));
                        $time_out2      = date("H:i:s", strtotime($s_out2));

                        $dt_in1         = date("Y-m-d H:i:s", strtotime("$date $s_in1"));
                        $dt_out1        = date("Y-m-d H:i:s", strtotime("$date $s_out1"));
                        $dt_in2         = date("Y-m-d H:i:s", strtotime("$date $s_in2"));
                        $dt_out2        = date("Y-m-d H:i:s", strtotime("$date $s_out2"));

                        $date_tom       = date("Y-m-d", strtotime("$date +1 day"));
                        $dt_in1_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in1"));
                        $dt_out1_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out1"));
                        $dt_in2_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in2"));
                        $dt_out2_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out2"));

                        // shift 9PM - 10PM | 11PM - 12AM
                        // entry 8:59PM - 10:01PM | 10:59PM - 12:01AM

                        // 8:59PM < 10:00PM AND 2025-11-09 8:59PM < 2025-11-09 11:00PM
                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn < '$time_out1' AND dateTimeIn < '$dt_out1' ORDER BY timeIn ASC";
                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                        if ($check) {
                            $in1 = $check['timeIn'];
                            $time_in1 = $check['timeIn'];
                        }

                        // 10:01PM < 11PM AND 2025-11-09 10:01PM > 2025-11-09 09:00PM
                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut < '$time_in2' AND dateTimeOut > '$dt_in1' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                        if ($check) {
                            $out1 = $check['timeOut'];
                            $time_out1 = $check['timeOut'];
                        }

                        // 10:59PM > 10:00PM AND 2025-11-09 10:59PM < 2025-11-10 06:00AM
                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND dateTimeIn < '$dt_out2_tom' ORDER BY timeIn ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $in2 = $check['timeIn'];
                            $time_in2 = $check['timeIn'];
                        }

                        // if dili undertime
                        // 6:01AM < 10:59PM AND 2025-11-10 6:01AM < 2025-11-10 10:00PM
                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut < '$time_in2' AND dateTimeOut < '$dt_in1_tom' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $out2 = $check['timeOut'];
                            $time_out2 = $check['timeOut'];
                        } else {
                            // if undertime
                            // 11:59PM > 10:59PM AND 2025-11-09 11:59PM < 2025-11-10 10:00PM
                            $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_in2' AND dateTimeOut < '$dt_in1_tom' ORDER BY timeOut ASC";
                            $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                            if ($check) {
                                $out2 = $check['timeOut'];
                                $time_out2 = $check['timeOut'];
                            }
                        }

                        if ($out2 != '') {
                            $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn > '$time_out2' AND dateTimeIn < '$dt_in1_tom' ORDER BY timeIn ASC";
                            $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                            if ($check) {
                                $in3 = $check['timeIn'];
                            }
                        }

                        if ($in3 != '') {
                            $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_out2' AND timeOut > '$in3' ORDER BY timeOut ASC";
                            $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                            if ($check) {
                                $out3 = $check['timeOut'];
                            }
                        }
                    }

                    if ($out1 && $in2) {

                        $dayEx  = date("D", strtotime($date));
                        $cond   = ['empId' => $empId, 'status' => 'active', 'day' => $dayEx];
                        $check  = $this->promo_model->select_rowArray('tk', 'setup_inter_io_exempt', $cond);
                        if (!$check) {
                            $sub_in2    = date("H:i:s", strtotime("$in2 -1 minutes"));
                            $temp_out1  = date("H:i:s", strtotime($out1));

                            if (strtotime($sub_in2) <= strtotime($temp_out1)) {
                                $out1 = "";
                            }
                        }
                    }

                    if ($no_schedule) {
                        $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';
                    }

                    $percent = 100 / ($totalLoad ? $totalLoad : 1);
                    $percentage = $percentage + $percent;

                    echo json_encode(array(
                        'result'    => 'loading',
                        'msg'       => round($percentage) . '%',
                        'msg1'      => round($percentage) . '%',
                        'date'      => "Inserting Table (duty/duty_next)",
                        'table'     => "[Date: $date; biometricId: $id; in1: $in1; out1: $out1; in2: $in2; out2: $out2; in3: $in3; out3: $out3;]",
                        'exported'  => 'table_update'
                    ));

                    $data = [
                        'in1'   => $in1,
                        'out1'  => $out1,
                        'in2'   => $in2,
                        'out2'  => $out2,
                        'in3'   => $in3,
                        'out3'  => $out3,
                    ];

                    $cond = ['bioMetricId' => $id, 'dateDuty' => $date];
                    $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                    $data_ka = [
                        'in1_ka'   => date("Y-m-d H:i:s", strtotime("$date $in1")),
                        'out1_ka'  => date("Y-m-d H:i:s", strtotime("$date $out1")),
                        'in2_ka'   => date("Y-m-d H:i:s", strtotime("$date $in2")),
                        'out2_ka'  => date("Y-m-d H:i:s", strtotime("$date $out2")),
                        'in3_ka'   => date("Y-m-d H:i:s", strtotime("$date $in3")),
                        'out3_ka'  => date("Y-m-d H:i:s", strtotime("$date $out3")),
                    ];

                    $cond   = ['bioMetricId_ka' => $id, 'dateDuty_ka' => $date];
                    $update = $this->promo_model->update('tk', 'duty_next', $cond, $data_ka);
                }
            }

            //update perday entries **************************************************************
            $cond = "1 GROUP BY bioMetricId, dateDuty ORDER BY dateDuty ASC";
            $query = $this->promo_model->select_resultArray('tk', $table, $cond);

            $percentage = 0;
            $totalLoad  = count($query);
            foreach ($query as $key => $row) {

                $id     = $row['bioMetricId'];
                $date   = date('Y-m-d', strtotime($row['dateDuty']));

                $percent = 100 / ($totalLoad ? $totalLoad : 1);
                $percentage = $percentage + $percent;

                echo json_encode(array(
                    'result'    => 'loading',
                    'msg'       => round($percentage) . '%',
                    'msg1'      => round($percentage) . '%',
                    'date'      => "Inserting Table (perday)",
                    'table'     => "[Date: $date; biometricId: $id;]",
                    'exported'  => 'table_update'
                ));

                $cond   = "bioMetricId='$id' OR barcodeId='$id'";
                $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
                $empId  = $assign['empId'];
                if ($assign['shiftCode']) {
                    $shiftCode = $assign['shiftCode'];
                }

                $day = date('l', strtotime($row['dateDuty']));

                $cond   = ['empId' => $empId, 'day_sched' => $day];
                $pds    = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
                if ($pds) {
                    $shiftCode = $pds['shiftCode'];
                }

                $cond   = "empId='$empId' AND status = 'active' AND status2 = 'Approved' AND '$date' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
                $ams    = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond);
                if ($ams) {
                    $shiftCode = $ams['shiftCode'];
                }

                $four_entries = false;
                $cond   = ['shiftCode' => $shiftCode];
                $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
                if ($sc) {

                    $night_diff = $sc['night_diff'];

                    $fIn    = date("H:i:s", strtotime($sc['1stIn']));
                    $fOut   = date("H:i:s", strtotime($sc['1stOut']));
                    $sIn    = date("H:i:s", strtotime($sc['2ndIn']));
                    $sOut   = date("H:i:s", strtotime($sc['2ndOut']));

                    $s_in1  = $sc['1stIn'];
                    $s_out1 = $sc['1stOut'];
                    $s_in2  = $sc['2ndIn'];
                    $s_out2 = $sc['2ndOut'];

                    if ($fIn && $fOut && $sIn && $sOut) {
                        $four_entries = true;
                    }
                }

                $cond   = ['shiftCode' => $shiftCode];
                $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
                if ($scb) {

                    $four_entries = true;

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

                $time_in1   = date("H:i:s", strtotime($s_in1));
                $time_out1  = date("H:i:s", strtotime($s_out1));
                $time_in2   = date("H:i:s", strtotime($s_in2));
                $time_out2  = date("H:i:s", strtotime($s_out2));

                if (true) {

                    $cond   = ['bioMetricId' => $id, 'dateDuty' => $date];
                    $duty   = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                    $dutyId = $duty['dutyId'];
                    $in1    = $duty['in1'];
                    $out1   = $duty['out1'];
                    $in2    = $duty['in2'];
                    $out2   = $duty['out2'];
                    $in3    = $duty['in3'];
                    $out3   = $duty['out3'];

                    $cond   = ['dutyId' => $dutyId];
                    $check  = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                    if (!$check) {
                        $data = ['dutyId' => $dutyId];
                        $insert = $this->promo_model->insert('tk', 'perday', $data);
                    }

                    $next_not = false;
                    if ($day_in2 == "AM" && $day_out2 == "AM" && !$scb) {
                        $next_not = true;
                    }

                    if ($day_out1 == "AM" && $day_in2 == "AM" && $day_out2 == "AM" && !$scb) {
                        $next_not = true;
                    }

                    $two_four = false;
                    if ($scb) {
                        if ($day_in1 == "PM" && $day_in2 == "AM" && $day_out2 == "AM") {
                            $next_not = true;
                            $two_four = true;
                        }

                        if ($day_in1 == "PM" && $day_out1 == "AM" && $day_in2 == "AM") {
                            $next_not = true;
                            $two_four = true;
                        }

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

                            $min = date("H:i", strtotime("$in1 -$h hours, -$m minutes"));
                            if ($min <= $maxLate) {
                                $t      = explode(":", $min);
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

                            $min = date("H:i", strtotime("$in2 -$h hours, -$m minutes"));
                            if ($min <= $maxLate) {
                                $t      = explode(":", $min);
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
                            $h = $t[0];
                            $m = $t[1];

                            $hm = date("H:i", strtotime("$newOut1 -$h hours, -$m minutes"));
                            $t  = explode(":", $hm);
                            $h  = $t[0] * 1;
                            $m  = $t[1] * 1;

                            $hw = ($m != 0) ? "$h hr(s). & $m min(s)." : "$h hr(s).";
                        }

                        //if more than 8 hours
                        if (!$hw) {
                            $res = explode("&", $hw);
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

                            $dw = round($hrs + $min, 3);
                            $decimal = explode('.', (string)$dw);
                            if (isset($decimal[1]) && strlen($decimal[1]) > 3) {
                                $dw = number_format($dw, 3, '.', '');
                            }

                            if ($dw > 8) {
                                $hw = 0;
                            }

                            if ($dw > 8.5) {
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
                            $h  = $t[0] * 1;
                            $m  = $t[1] * 1;

                            $ot12 = date("H:i", strtotime("$hi_ot2 +$h hours, +$m minutes"));

                            //caculate the ot12 + ot3 overtime
                            $t  = explode(":", $ot12);
                            $h  = $t[0] * 1;
                            $m  = $t[1] * 1;

                            $ot123 = date('H:i', strtotime("$hi_ot3 +$h hours, +$m minutes"));
                            $t  = explode(":", $ot123);
                            $h  = $t[0] * 1;
                            $m  = $t[1] * 1;

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

            //update perday nigt diff **************************************************************
            $cond = "1 GROUP BY bioMetricId, dateDuty ORDER BY dateDuty ASC";
            $query = $this->promo_model->select_resultArray('tk', $table, $cond);
            foreach ($query as $key => $row) {

                $id     = $row['bioMetricId'];
                $date   = date('Y-m-d', strtotime($row['dateDuty']));

                $cond   = ['bioMetricId' => $id, 'dateDuty' => $date];
                $duty   = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                $dutyId = $duty['dutyId'];
                $in1    = $duty['in1'];
                $out1   = $duty['out1'];
                $in2    = $duty['in2'];
                $out2   = $duty['out2'];
                $in3    = $duty['in3'];
                $out3   = $duty['out3'];

                $cond   = "bioMetricId='$id' OR barcodeId='$id'";
                $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
                $empId  = $assign['empId'];
                if ($assign['shiftCode']) {
                    $shiftCode = $assign['shiftCode'];
                }

                $day = date('l', strtotime($row['dateDuty']));
                $cond = ['empId' => $empId, 'day_sched' => $day];
                $pds = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
                if ($pds) {
                    $shiftCode = $pds['shiftCode'];
                }

                $cond = "empId='$empId' AND status = 'active' AND status2 = 'Approved' AND '$date' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
                $ams = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond);
                if ($ams) {
                    $shiftCode = $ams['shiftCode'];
                }

                $night_diff = '';
                $four_entries = false;
                $cond   = ['shiftCode' => $shiftCode];
                $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
                if ($sc) {

                    $fIn    = date("H:i:s", strtotime($sc['1stIn']));
                    $fOut   = date("H:i:s", strtotime($sc['1stOut']));
                    $sIn    = date("H:i:s", strtotime($sc['2ndIn']));
                    $sOut   = date("H:i:s", strtotime($sc['2ndOut']));

                    $s_in1  = $sc['1stIn'];
                    $s_out1 = $sc['1stOut'];
                    $s_in2  = $sc['2ndIn'];
                    $s_out2 = $sc['2ndOut'];

                    $night_diff = $sc['night_diff'];

                    if ($fIn && $fOut && $sIn && $sOut) {
                        $four_entries = true;
                    }
                }

                $cond   = ['shiftCode' => $shiftCode];
                $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
                if ($scb) {

                    $four_entries = true;

                    $fIn    = date("H:i:s", strtotime($scb['1stIn']));
                    $fOut   = date("H:i:s", strtotime($scb['1stOut']));
                    $sIn    = date("H:i:s", strtotime($scb['2ndIn']));
                    $sOut   = date("H:i:s", strtotime($scb['2ndOut']));

                    $s_in1  = $scb['1stIn'];
                    $s_out1 = $scb['1stOut'];
                    $s_in2  = $scb['2ndIn'];
                    $s_out2 = $scb['2ndOut'];

                    $temp_entries1 = false;
                    $temp_entries2 = false;
                    if (empty($out1)) {
                        $temp_entries1 = true;
                        $out1 = date('H:i:s', strtotime($s_out1));
                    }
                    if (empty($in2)) {
                        $temp_entries2 = true;
                        $in2 = date('H:i:s', strtotime($s_in2));
                    }
                    if (empty($in1)) {
                        $out1 = '';
                    }
                    if (empty($out2)) {
                        $in2 = '';
                        $out1 = '';
                    }

                    if (empty($in1) && !empty($out2) && $temp_entries1) {
                        $in2 = '';
                    }
                    if (!empty($in1) && empty($out2) && $temp_entries2) {
                        $out1 = '';
                    }
                }

                $day_in1    = date('A', strtotime($s_in1));
                $day_out1   = date('A', strtotime($s_out1));
                $day_in2    = date('A', strtotime($s_in2));
                $day_out2   = date('A', strtotime($s_out2));

                $cond = "1 ORDER BY snId";
                $snd = $this->promo_model->select_rowArray('tk', 'setup_n_d', $cond);
                $ndStart = $snd['timeStarted']; //10PM
                $ndEnded = $snd['timeEnded']; //06AM
                $minTime = $snd['minTime']; //30min

                $nd_in1 = $nd_out1 = $nd_in2 = $nd_out2 = '00:00';

                //in1
                if ($day_in1 == 'PM' && $in1) {

                    if ($in1 >= $ndStart && $in1 <= strtotime('23:59:59')) {
                        $nd_in1 = ($in1 <= $fIn) ? $fIn : $in1;
                    } else {
                        $nd_in1 = $ndStart;
                    }
                }

                if ($day_in1 == 'AM' && $in1) {

                    if ($in1 <= $ndEnded) {
                        $nd_in1 = ($in1 <= $fIn) ? $fIn : $in1;
                    } else {
                        if (date('A', strtotime($in1)) == 'PM') {
                            $nd_in1 = ($in1 >= $fIn) ? $fIn : $in1;
                        } else {
                            $nd_in1 = $ndEnded;
                        }
                    }
                }

                //out1
                if ($day_out1 == 'PM' && $out1) {

                    if ($out1 >= $ndStart && $out1 <= strtotime('23:59:59')) {

                        if ($ndStart <= $fOut) {
                            $nd_out1 = ($out1 <= $fOut) ? $out1 : $fOut;
                        } else {
                            $nd_out1 = $ndStart;
                        }
                    } else {
                        $nd_out1 = $ndStart;
                    }
                }

                if ($day_out1 == 'AM' && $out1) {

                    if ($out1 <= $ndEnded) {
                        $nd_out1 = ($out1 <= $fOut) ? $out1 : $fOut;
                    } else {
                        if (date('A', strtotime($out1)) == 'PM') {
                            $nd_out1 = ($out1 >= $fOut) ? $out1 : $fOut;
                        } else {
                            $nd_out1 = $ndEnded;
                        }
                    }
                }

                //in2
                if ($day_in2 == 'PM' && $in2) {

                    if ($in2 >= $ndStart && $in2 <= strtotime('23:59:59')) {
                        $nd_in2 = ($in2 <= $sIn) ? $sIn : $in2;
                    } else {
                        $nd_in2 = $ndStart;
                    }
                }

                if ($day_in2 == 'AM' && $in2) {

                    if ($in2 <= $ndEnded) {
                        $nd_in2 = ($in2 <= $sIn) ? $sIn : $in2;
                    } else {
                        if (date('A', strtotime($in2)) == 'PM') {
                            $nd_in2 = ($in2 >= $sIn) ? $sIn : $in2;
                        } else {
                            $nd_in2 = $ndEnded;
                        }
                    }
                }

                //out2
                if ($day_out2 == 'PM' && $out2) {
                    if ($out2 >= $ndStart && $out2 <= strtotime('23:59:59')) {

                        if ($ndStart <= $sOut) {
                            $nd_out2 = ($out2 <= $sOut) ? $out2 : $sOut;
                        } else {
                            $nd_out2 = $ndStart;
                        }
                    } else {
                        $nd_out2 = $ndStart;
                    }
                }

                if ($day_out2 == 'AM' && $out2) {

                    if ($out2 <= $ndEnded) {
                        $nd_out2 = ($out2 <= $sOut) ? $out2 : $sOut;
                    } else {
                        if (date('A', strtotime($out2)) == 'PM') {
                            $nd_out2 = ($out2 >= $sOut) ? $out2 : $sOut;
                        } else {
                            $nd_out2 = $ndEnded;
                        }
                    }
                }

                //nd
                $nd = 0;
                if ($night_diff == 'yes') {

                    $t  = explode(":", $nd_in1);
                    $h  = $t[0];
                    $m  = $t[1];
                    $t1 = date("H:i", strtotime("$nd_out1 -$h hours, -$m minutes"));

                    $t  = explode(":", $nd_in2);
                    $h  = $t[0];
                    $m  = $t[1];
                    $t2 = date("H:i", strtotime("$nd_out2 -$h hours, -$m minutes"));

                    list($h1, $m1) = explode(":", $t1);
                    list($h2, $m2) = explode(":", $t2);

                    $totalMinutes = ($h1 * 60 + $m1) + ($h2 * 60 + $m2);
                    $hh = floor($totalMinutes / 60);
                    $mm = $totalMinutes % 60;

                    $hm = sprintf("%02d:%02d", $hh, $mm);

                    $t  = explode(":", $hm);
                    $h  = $t[0] * 1;
                    $m  = $t[1] * 1;

                    if ($m >= $minTime) {
                        $m = '30';
                    } else {
                        $m = '00';
                    }

                    $hrs = $h / 8;
                    $min = ($m / 60) / 8;

                    $min = round(($m / 60), 3);
                    $nd =  round($h + $min, 3);
                    $decimal = explode('.', (string)$h + $min);
                    if (isset($decimal[1]) && strlen($decimal[1]) > 3) {
                        $nd = number_format($h + $min, 3, '.', '');
                    }

                    $cond = ['dutyId' => $dutyId];
                    $data = ['night_diff' => $nd];
                    $update = $this->promo_model->update('tk', 'perday', $cond, $data);
                }
            }

            //update if data is empty **************************************************************
            $count = 0;
            $start = date('Y-m-d', strtotime($input['startDate']));
            $end = date('Y-m-d', strtotime($input['endDate']));
            while ($start <= $end) {
                $count++;
                $start = date('Y-m-d', strtotime("$start +1 day"));
            }

            $percentage = 0;
            $pDiv1 = 100 / $count;
            $start = date('Y-m-d', strtotime($input['startDate']));
            $end = date('Y-m-d', strtotime($input['endDate']));
            while ($start <= $end) {

                $percent = $pDiv1 / count($empIds);
                foreach ($empIds as $key => $empId) {
                    $id = '';
                    $cond = "empId='$empId' AND barcodeId NOT LIKE '%\_%' AND bioMetricId NOT LIKE '%\_%' ORDER BY assignId DESC LIMIT 1";
                    $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
                    if ($assign) {
                        $allowId = $assign['allowId'];
                        if ($allowId == "Biometric ID") {
                            $id = $assign['bioMetricId'];
                        }
                        if ($allowId == "Logbox ID") {
                            $id = $assign['barcodeId'];
                        }
                    } else {
                        $cond = "empId='$empId' AND status='active'";
                        $id = $this->promo_model->select_rowArray('tk', 'promo_id', $cond)['barcodeId'];
                    }


                    $percentage += $percent;
                    $cond = ['bioMetricId' => $id, 'dateDuty' => $start];
                    $query = $this->promo_model->select_rowArray('tk', $table, $cond);
                    if (!$query) {
                        echo json_encode(array(
                            'result'    => 'loading',
                            'msg'       => round($percentage) . '%',
                            'msg1'      => round($percentage) . '%',
                            'date'      => "Updating Table (duty/perday) NO DATA",
                            'table'     => "[Date: $start; biometricId: $id; empId: $empId]",
                            'exported'  => 'table_update'
                        ));

                        $cond   = ['bioMetricId' => $id, 'dateDuty' => $start];
                        $dutyId = $this->promo_model->select_rowArray('tk', 'duty', $cond)['dutyId'];
                        if ($dutyId) {
                            $cond = ['bioMetricId' => $id, 'dateDuty' => $start];
                            $data = ['in1' => '', 'out1' => '', 'in2' => '', 'out2' => '', 'in3' => '', 'out3' => ''];
                            $this->promo_model->update('tk', 'duty', $cond, $data);

                            $cond   = ['dutyId' => $dutyId];
                            $check  = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                            if ($check) {
                                $this->netEntery($empId, $id, $start, '', '', '', '', '', '');
                            }
                        }
                    }
                }
                $start = date('Y-m-d', strtotime("$start +1 day"));
            }

            unlink($filePath);
            $this->promo_model->drop_table($table); // delete table

            echo json_encode(array(
                'result'    => 'loading',
                'msg'       => round(100) . '%',
                'msg1'      => round(100) . '%',
                'date'      => "Process Complete!",
                'table'     => "",
                'exported'  => 'initialize'
            ));

            sleep(1);

            echo json_encode(array(
                'result'    => 'success',
                'msg'       => round(100) . '%',
                'msg1'      => round(100) . '%',
                'date'      => "Process Complete!",
                'table'     => "",
                'exported'  => 'initialize'
            ));
        }
    }

    public function processSingle()
    {
        $input      = $this->input->post(NULL, TRUE);
        $empIds     = $input['empId'];
        $startDate  = date('Y-m-d', strtotime($input['startDate']));
        $endDate    = date('Y-m-d', strtotime($input['endDate']));
        $data_log   = $input['data_log'];
        $terminal   = 'Logbox';

        $dateFrom   = date('Y-m-d', strtotime($startDate));
        $dateTo     = date('Y-m-d', strtotime("$endDate"));

        $userName   = ucwords($this->userName);
        $timeHis    = date("His");

        set_time_limit(0);
        ob_implicit_flush(true);
        ob_end_flush();

        // Creating Logfile Data (Text File) **********************************************************************
        if (true) {
            echo json_encode(array(
                'result'    => 'loading',
                'msg'       => round(0) . '%',
                'msg1'      => round(0) . '%',
                'date'      => "Creating Logfile Data (Text File)",
                'table'     => "",
                'exported'  => 'table_update',
            ));

            sleep(1);

            if ($data_log == 'database') {

                $dateF = date("Y-m-d", strtotime($startDate));
                $dateT = date("Y-m-d", strtotime($endDate));

                $mergeTxtfile = '';
                foreach ($empIds as $key => $empId) {

                    $cond   = "empId='$empId' AND barcodeId NOT LIKE '%\_%' AND bioMetricId NOT LIKE '%\_%' ORDER BY assignId DESC LIMIT 1";
                    $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
                    if ($assign) {

                        $table  = "duty_logbox";
                        $allowId = $assign['allowId'];
                        if ($allowId == "Biometric ID") {
                            $bioM       = $assign['bioMetricId'];
                            $table      = "duty_biometric";
                            $terminal   = "Biometric";
                        }
                        if ($allowId == "Logbox ID") {
                            $bioM       = $assign['barcodeId'];
                            $table      = "duty_logbox";
                            $terminal   = "Logbox";
                        }

                        if ($terminal == "Biometric") {
                            die("Biometric - $empId");
                        }

                        $cond   = "bioMetricId='$bioM' AND dateDuty BETWEEN '$dateF' AND '$dateT'";
                        $query  = $this->promo_model->select_resultArray('tk', $table, $cond);
                        foreach ($query as $key => $co) {

                            $dateDuty   = $co['dateDuty'];
                            $timeIn     = $co['timeIn'];
                            $timeOut    = $co['timeOut'];
                            $store      = $co['store'];
                            $bio        = '00000000000';
                            $mdY    = date("mdY", strtotime($dateDuty));
                            $time   = '';
                            $mode   = '';

                            if ($timeIn != '0') {
                                $time   = date('His', strtotime($timeIn));
                                $mode   = 'I';
                            }

                            if ($timeOut != '0') {
                                $time   = date('His', strtotime($timeOut));
                                $mode   = 'O';
                            }

                            $mergeTxtfile .= "$bioM$mdY$time$mode$bio$store\r\n";
                        }
                    }
                }

                $ext = $terminal . $userName . 'time' . $timeHis;
                // $filePath = FCPATH . "../upload/Promo/mergeMonth$ext.LOG";
                $filePath = FCPATH . "../upload/mergeMonth$ext.LOG";
                $fp = fopen($filePath, 'w');
                if (!$fp) {
                    die('Could not create / open text file for writing.');
                }

                if (fwrite($fp, $mergeTxtfile) === false) {
                    die('Could not write to text file.');
                }
                fclose($fp);
            } else {


                $all_entries = '';
                $addedEntries = [];
                foreach ($empIds as $key => $empId) {

                    $cond   = "empId='$empId' AND status='active' GROUP BY baseId";
                    $pids   = $this->promo_model->select_resultArray('tk', 'promo_id', $cond);
                    foreach ($pids as $key => $pid) {

                        $bioM   = $pid['biometricId'];
                        $barC   = $pid['barcodeId'];
                        $baseId = $pid['baseId'];

                        if ($baseId == 'alta_cita') {
                            $store = 'alta_cita';
                        } else if ($baseId == 'cdc') {
                            $store = 'cortes';
                        } else if ($baseId == 'fixrite_panglao') {
                            $store = 'fixrite_panglao';
                        } else if ($baseId == 'fr_panglao') {
                            $store = 'fixrite_panglao';
                        } else if ($baseId == 'alturas_panglao') {
                            $store = 'fixrite_panglao';
                        } else {
                            $store = $baseId;
                        }

                        $cond = ['baseId' => $store, 'terminal' => 'Biometric'];
                        if ($baseId == 'fixrite_panglao' || $baseId == 'alturas_panglao') {
                            $cond = "(baseId='fixrite_panglao' OR baseId='alturas_panglao') AND terminal='Biometric'";
                        }

                        $df = date('Y-m-01', strtotime($input['startDate']));
                        $dt = date('Y-m-01', strtotime($input['endDate']));
                        while (strtotime($df) <= strtotime($dt)) {

                            $my     = date("F Y", strtotime($df));
                            $month  = strtoupper(date("M", strtotime($df)));
                            $query  = $this->promo_model->select_resultArray('tk', 'id_biometric_logbox', $cond);

                            foreach ($query as $key => $row) {
                                $logNo = $row['code'];

                                $path       = FCPATH . "../upload/Backup_Month_Year_Others/$store/Biometric/$my/$month$logNo.LOG";
                                $content    = file_get_contents($path);
                                $bioEntries = explode("\r\n", $content);

                                foreach ($bioEntries as $key => $lines) {

                                    $bio    = substr($lines, 0, 11);
                                    $date   = substr($lines, 11, 8);
                                    $time   = substr($lines, 19, 6);
                                    $mode   = substr($lines, 25, 1);

                                    $m = substr($date, 0, 2);
                                    $d = substr($date, 2, 2);
                                    $y = substr($date, 4, 4);

                                    $Ymd = date("Y-m-d", strtotime("$y-$m-$d"));

                                    if (strtotime($dateFrom) <= strtotime($Ymd) && strtotime($Ymd) <= strtotime($dateTo)) {

                                        if ($bioM == $bio) {

                                            $entry = "$barC$date$time$mode$bioM$baseId\r\n";
                                            if (!in_array($entry, $addedEntries)) {
                                                $all_entries .= $entry;
                                                $addedEntries[] = $entry;
                                            }
                                        }
                                    }
                                }
                            }

                            $df = date("Y-m", strtotime("$df +1 month"));
                        }
                    }
                }

                $bioM = [];
                foreach ($empIds as $key => $empId) {
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
                }
                // merging ******************************************************************************
                $b_d1 = date('Y-m-1', strtotime($startDate));
                $b_d2 = date('Y-m-1', strtotime($endDate));

                while (strtotime($b_d1) <= strtotime($b_d2)) {

                    $monthYear = date('F Y', strtotime($b_d1));
                    $file = "$monthYear.LOG";
                    $filePath = FCPATH . "../upload/Promo/$terminal/$file";
                    $all_entries .= file_get_contents($filePath);
                    $b_d1 = date('Y-m', strtotime("$b_d1 +1 month"));
                }

                $ext = $terminal . $userName . 'time' . $timeHis;
                $filePath = FCPATH . "../upload/mergeMonth$ext.LOG";
                $fp = fopen($filePath, 'w');
                if (!$fp) {
                    die('Could not create / open text file for writing.');
                }

                if (fwrite($fp, $all_entries) === false) {
                    die('Could not write to text file.');
                }
                fclose($fp);

                $filter1 = '';
                $bioLookup = array_flip($bioM);
                $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {

                    $id = substr($line, 0, 13);

                    if (isset($bioLookup[$id])) {
                        $filter1 .= $line . "\r\n";
                    }
                }

                $fp = fopen($filePath, 'w');
                if (!$fp) {
                    die('Could not create / open text file for writing.');
                }

                if (fwrite($fp, $filter1) === false) {
                    die('Could not write to text file.');
                }

                fclose($fp);
                // filtering bioM ******************************************************************************

                // filtering date ******************************************************************************
                $filter2 = "";
                $dateLookup = [];
                $d1 = strtotime($startDate);
                $d2 = strtotime($endDate);

                while ($d1 <= $d2) {
                    $dateLookup[date('mdY', $d1)] = true;
                    $d1 = strtotime('+1 day', $d1);
                }

                $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $row) {
                    $date = substr($row, 13, 8);

                    if (isset($dateLookup[$date])) {
                        $filter2 .= $row . "\r\n";
                    }
                }

                $fp = fopen($filePath, 'w');
                if (!$fp) {
                    die('Could not create / open text file for writing.');
                }

                if (fwrite($fp, $filter2) === false) {
                    die('Could not write to text file.');
                }

                fclose($fp);
                // filtering date ******************************************************************************

                $filter3 = '';
                $start = date('Y-m-01', strtotime($input['startDate']));
                $end = date('Y-m-01', strtotime($input['endDate']));
                while ($start <= $end) {

                    $my     = date('F Y', strtotime($start));
                    $month  = strtoupper(date('M', strtotime($my)));
                    $m      = date('m', strtotime($my));
                    $y      = date('Y', strtotime($my));

                    $cond   = "statusLogs='1' ORDER BY storeCode DESC";
                    $stores = $this->promo_model->select_resultArray('tk', 'promo_store_start', $cond);
                    foreach ($stores as $key => $store) {

                        $storeCode  = $store['storeCode'];
                        $cond       = ['baseId' => $storeCode];
                        $code       = $this->promo_model->select_rowArray('tk', 'promo_id_bio_log', $cond);
                        $codeLog    = $code['code'];

                        $path = FCPATH . "../upload/Promo/Backup_Month_Year/$storeCode/Logbox/$my/$month$codeLog.LOG";
                        if (is_file($path)) {

                            $strings = array_flip(file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
                            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                            foreach ($lines as $row) {
                                if (!$row) continue;

                                $row = trim($row);
                                if (strlen($row) === 28) {

                                    if (isset($strings[$row])) {
                                        $bio = '00000000000';
                                        $filter3 .= $row . $bio . $storeCode . "\r\n";
                                    }
                                }
                            }
                        }
                    }

                    $start = date('Y-m-01', strtotime("$start +1 month"));
                }

                $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $row) {
                    $row = trim($row);
                    if (strlen($row) != 28) {
                        $filter3 .= $row . "\r\n";
                    }
                }

                $fp = fopen($filePath, 'w');
                if (!$fp) {
                    die('Could not create / open text file for writing.');
                }

                if (fwrite($fp, $filter3) === false) {
                    die('Could not write to text file.');
                }

                fclose($fp);
            }
        }

        //create temporary table **********************************************************************
        $table = "dupduty_$userName$timeHis";
        echo json_encode(array(
            'result'    => 'loading',
            'msg'       => round(0) . '%',
            'msg1'      => round(0) . '%',
            'date'      => "Creating Temporary Table ($table)",
            'table'     => "",
            'exported'  => 'table_update',
        ));

        sleep(1);

        $create = $this->promo_model->dupduty_user($table);
        if ($create) {

            $df = date('Y-m-d', strtotime($startDate));
            $dt = date('Y-m-d', strtotime($endDate));

            // $data   = file_get_contents($filePath);
            // $lines  = explode(PHP_EOL, $data);
            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $percentage = 0;
            $percent = 100 / ($lines ? count($lines) : 1);
            foreach ($lines as $key => $row) {

                if ($terminal == "Logbox") {
                    $id     = substr($row, 0, 13);
                    $date   = substr($row, 13, 8);
                    $time   = substr($row, 21, 6);
                    $mode   = substr($row, 27, 1);
                } else {
                    $id     = substr($row, 0, 11);
                    $date   = substr($row, 11, 8);
                    $time   = substr($row, 19, 6);
                    $mode   = substr($row, 25, 1);
                }

                $month  = substr($date, 0, 2);
                $day    = substr($date, 2, 2);
                $year   = substr($date, 4, 4);

                $d  = "$year-$month-$day";
                $d1 = "$month-$day-$year";

                $date   = date('Y-m-d', strtotime("$year-$month-$day"));
                $date1  = date('m-d-Y', strtotime("$month-$day-$year"));

                $h = substr($time, 0, 2);
                $m = substr($time, 2, 2);
                $s = substr($time, 4, 2);

                $time = date('H:i:s', strtotime("$h:$m:$s"));

                $ti = ($mode == 'I') ? $time : '0';
                $to = ($mode == 'I') ? '0' : $time;

                $datetime = date('Y-m-d H:i:s', strtotime("$date $time"));

                $percentage += $percent;

                echo json_encode(array(
                    'result'    => 'loading',
                    'msg'       => round($percentage) . '%',
                    'msg1'      => round($percentage) . '%',
                    'date'      => "Inserting Temporary Table ($table)",
                    'table'     => "[Date: $date; biometricId: $id; timeIn: $ti; timeOut: $to; mode: $mode]",
                    'exported'  => 'table_update'
                ));

                if (strtotime($df) <= strtotime($date) && strtotime($date) <= strtotime($dt)) {

                    $data = ['bioMetricId' => $id, 'date' => $d1, 'dateDuty' => $date, 'mode' => $mode];
                    $cond = ['bioMetricId' => $id, 'dateDuty' => $date];
                    if ($mode == 'I') {
                        $data['timeIn']         = $time;
                        $data['timeOut']        = '0';
                        $data['dateTimeIn']     = $datetime;
                        $data['dateTimeOut']    = '0';

                        $cond['timeIn']         = $time;
                        $cond['timeOut']        = '0';
                    }

                    if ($mode == 'O') {
                        $data['timeIn']         = '0';
                        $data['timeOut']        = $time;
                        $data['dateTimeIn']     = '0';
                        $data['dateTimeOut']    = $datetime;

                        $cond['timeIn']         = '0';
                        $cond['timeOut']        = $time;
                    }

                    $query = $this->promo_model->select_rowArray('tk', $table, $cond);
                    if (!$query) {
                        $insert = $this->promo_model->insert('tk', $table, $data);
                    }
                }
            }
        }

        //insert duty/duty_next **************************************************************
        echo json_encode(array(
            'result'    => 'loading',
            'msg'       => round(0) . '%',
            'msg1'      => round(0) . '%',
            'date'      => "Inserting Tables (duty,duty_next)",
            'table'     => "",
            'exported'  => 'table_update',
        ));

        sleep(1);

        $count = 0;
        $dateF = date('Y-m-01', strtotime($input['startDate']));
        $dateT = date('Y-m-t', strtotime($input['endDate']));
        while (strtotime($dateF) <= strtotime($dateT)) {
            $count++;
            $dateF = date('Y-m-d', strtotime("$dateF +1 day"));
        }

        $cond = "1 GROUP BY bioMetricId, YEAR(date), MONTH(date)";
        $query = $this->promo_model->select_resultArray('tk', $table, $cond);

        $percentage = 0;
        $pdiv1 = 100 / ($query ? count($query) : 1);
        foreach ($query as $key => $row) {

            $id     = $row['bioMetricId'];
            $date   = date('Y-m-d', strtotime($row['dateDuty']));

            $dateF = date('Y-m-01', strtotime($input['startDate']));
            $dateT = date('Y-m-t', strtotime($input['endDate']));

            $percent = $pdiv1 / $count;

            while (strtotime($dateF) <= strtotime($dateT)) {

                $percentage += $percent;

                echo json_encode(array(
                    'result'    => 'loading',
                    'msg'       => round($percentage) . '%',
                    'msg1'      => round($percentage) . '%',
                    'date'      => "Initializing: $id",
                    'table'     => "[Date: $dateF]",
                    'exported'  => 'table_update'
                ));

                $cond   = ['bioMetricId' => $id, 'dateDuty' => $dateF];
                $check  = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                if (!$check) {
                    $data = ['bioMetricId' => $id, 'date' => $dateF, 'dateDuty' => $dateF, 'status' => 'Pending'];
                    $insert = $this->promo_model->insert('tk', 'duty', $data);
                }

                $cond   = ['bioMetricId_ka' => $id, 'dateDuty_ka' => $dateF];
                $check  = $this->promo_model->select_rowArray('tk', 'duty_next', $cond);
                if (!$check) {
                    $data = ['bioMetricId_ka' => $id, 'dateDuty_ka' => $dateF];
                    $insert = $this->promo_model->insert('tk', 'duty_next', $data);
                }

                $dateF = date('Y-m-d', strtotime("$dateF +1 day"));
            }
        }

        //insert 1stcutoff/2ndcutoff **************************************************************
        echo json_encode(array(
            'result'    => 'loading',
            'msg'       => round(0) . '%',
            'msg1'      => round(0) . '%',
            'date'      => "Inserting Tables (1stcutoff,2ndcutoff)",
            'table'     => "",
            'exported'  => 'table_update',
        ));

        sleep(1);
        $count = 0;
        $cosDate = date('Y-m-01', strtotime($input['startDate']));
        $coeDate = date('Y-m-01', strtotime($input['endDate']));
        while (strtotime($cosDate) <= strtotime($coeDate)) {
            $count++;
            $cosDate = date('Y-m-01', strtotime("$cosDate +1 month"));
        }

        $cond = "1 GROUP BY bioMetricId, YEAR(date), MONTH(date)";
        $query = $this->promo_model->select_resultArray('tk', $table, $cond);

        $percentage = 0;
        $pdiv1 = 100 / ($query ? count($query) : 1);
        foreach ($query as $key => $row) {

            $pdiv2 = $pdiv1 / $count;
            $cosDate = date('Y-m-01', strtotime($input['startDate']));
            $coeDate = date('Y-m-01', strtotime($input['endDate']));
            while (strtotime($cosDate) <= strtotime($coeDate)) {

                $id = $row['bioMetricId'];
                $date = date('Y-m-01', strtotime($cosDate));

                $cond   = "bioMetricId='$id' OR barcodeId='$id' ORDER BY assignId DESC";
                $empId  = $this->promo_model->select_rowArray('tk', 'assign', $cond)['empId'];

                $cond       = ['emp_id' => $empId];
                $recordNo   = $this->promo_model->select_rowArray('pis', 'employee3', $cond)['record_no'];

                $cond       = ['empId' => $empId, 'recordNo' => $recordNo];
                $statCut    = $this->promo_model->select_rowArray('tk', 'promo_sched_emp', $cond)['statCut'];
                if (!$statCut) {
                    $cond       = "empId='$empId' ORDER BY date_setup DESC";
                    $statCut    = $this->promo_model->select_rowArray('tk', 'promo_sched_emp', $cond)['statCut'];
                }

                $cond   = ['statCut' => $statCut];
                $ps     = $this->promo_model->select_rowArray('tk', 'promo_schedule', $cond);
                $sFC    = $ps['startFC'];
                $eFC    = ($ps['endFC'] == '') ? date('t', strtotime($cosDate)) : $ps['endFC'];
                $cFC    = $ps['clearingFC'];
                $sSC    = $ps['startSC'];
                $eSC    = $ps['endSC'];
                $cSC    = $ps['clearingSC'];

                $t = explode('-', $date);
                $y = $t[0];
                $m = $t[1];
                $d = $t[2];

                $startFC = date('Y-m-d', strtotime("$y-$m-$sFC"));
                $endFC = date('Y-m-d', strtotime("$y-$m-$eFC"));
                if ($ps['startFC'] > 16) {
                    $endFC = date('Y-m-d', strtotime("$y-$m-$eFC +1 month"));
                }

                $startSC = date('Y-m-d', strtotime("$y-$m-$sSC"));
                $endSC = date('Y-m-d', strtotime("$y-$m-$eSC"));
                if ($ps['startSC'] > 16) {
                    $endSC = date('Y-m-d', strtotime("$y-$m-$eSC +1 month"));
                }

                $percentage += $pdiv2;

                // echo json_encode(array(
                //     'result'    => 'loading',
                //     'msg'       => round($percentage) . '%',
                //     'msg1'      => round($percentage) . '%',
                //     'date'      => "Initializing: $id",
                //     'table'     => "[dateFrom: $startFC; dateTo: $endFC; statCut: $statCut;]",
                //     'exported'  => 'table_update'
                // ));

                $cond = ['bioMetricId' => $id, 'dateFrom' => $startFC, 'dateTo' => $endFC, 'statCut' => $statCut];
                $check = $this->promo_model->select_rowArray('tk', '1stcutoff', $cond);
                if (!$check) {
                    echo json_encode(array(
                        'result'    => 'loading',
                        'msg'       => round($percentage) . '%',
                        'msg1'      => round($percentage) . '%',
                        'date'      => "Initializing: $id",
                        'table'     => "[dateFrom: $startFC; dateTo: $endFC; statCut: $statCut;]",
                        'exported'  => 'table_update'
                    ));
                    $data = [
                        'bioMetricId'   => $id,
                        'dateFrom'      => $startFC,
                        'dateTo'        => $endFC,
                        'statCut'       => $statCut,
                        'date_added'    => date('Y-m-d'),
                        'time_added'    => date('H:i:s'),
                    ];
                    $insert = $this->promo_model->insert('tk', '1stcutoff', $data);
                }

                $cond = ['bioMetricId' => $id, 'dateFrom' => $startSC, 'dateTo' => $endSC, 'statCut' => $statCut];
                $check = $this->promo_model->select_rowArray('tk', '2ndcutoff', $cond);
                if (!$check) {
                    echo json_encode(array(
                        'result'    => 'loading',
                        'msg'       => round($percentage) . '%',
                        'msg1'      => round($percentage) . '%',
                        'date'      => "Initializing: $id",
                        'table'     => "[dateFrom: $startFC; dateTo: $endFC; statCut: $statCut;]",
                        'exported'  => 'table_update'
                    ));
                    $data = [
                        'bioMetricId'   => $id,
                        'dateFrom'      => $startSC,
                        'dateTo'        => $endSC,
                        'statCut'       => $statCut,
                        'date_added'    => date('Y-m-d'),
                        'time_added'    => date('H:i:s'),
                    ];
                    $insert = $this->promo_model->insert('tk', '2ndcutoff', $data);
                }

                // echo json_encode(array(
                //     'result'    => 'loading',
                //     'msg'       => round($percentage) . '%',
                //     'msg1'      => round($percentage) . '%',
                //     'date'      => "Initializing: $id test",
                //     'table'     => "[date: $date;]",
                //     'exported'  => 'table_update'
                // ));

                $cosDate = date('Y-m-d', strtotime("$cosDate +1 month"));
            }
        }

        //insert duty_logbox/duty_biometric table **************************************************************
        echo json_encode(array(
            'result'    => 'loading',
            'msg'       => round(0) . '%',
            'msg1'      => round(0) . '%',
            'date'      => "Inserting Tables (duty_logbox,duty_biometric)",
            'table'     => "",
            'exported'  => 'table_update',
        ));

        sleep(1);

        $df = date('Y-m-d', strtotime($startDate));
        $dt = date('Y-m-d', strtotime($endDate));
        $duty_table = ($terminal == "Logbox") ? "duty_logbox" : "duty_biometric";

        // $data   = file_get_contents($filePath);
        // $lines  = explode(PHP_EOL, $data);
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $percentage = 0;
        $percent = 100 / ($lines ? count($lines) : 1);
        foreach ($lines as $key => $row) {

            $str_len    = strlen(trim($row));
            $len_sub    = $str_len - 39;

            if ($terminal == "Logbox") {
                $id     = substr($row, 0, 13);
                $date   = substr($row, 13, 8);
                $time   = substr($row, 21, 6);
                $mode   = substr($row, 27, 1);
                $bio    = substr($row, 28, 11);
                $baseId = substr($row, 39, $len_sub);
            } else {
                $id     = substr($row, 0, 11);
                $date   = substr($row, 11, 8);
                $time   = substr($row, 19, 6);
                $mode   = substr($row, 25, 1);
                $bio    = substr($row, 26, 11);
                $baseId = substr($row, 37, $len_sub);
            }

            $month  = substr($date, 0, 2);
            $day    = substr($date, 2, 2);
            $year   = substr($date, 4, 4);

            $d  = "$year-$month-$day";
            $d1 = "$month-$day-$year";

            $date   = date('Y-m-d', strtotime("$year-$month-$day"));
            $date1  = date('m-d-Y', strtotime("$month-$day-$year"));

            $h = substr($time, 0, 2);
            $m = substr($time, 2, 2);
            $s = substr($time, 4, 2);

            $time = date('H:i:s', strtotime("$h:$m:$s"));

            $ti = ($mode == 'I') ? $time : '0';
            $to = ($mode == 'I') ? '0' : $time;

            $datetime = date('Y-m-d H:i:s', strtotime("$date $time"));

            $percentage += $percent;

            echo json_encode(array(
                'result'    => 'loading',
                'msg'       => round($percentage) . '%',
                'msg1'      => round($percentage) . '%',
                'date'      => "Inserting Table ($duty_table)",
                'table'     => "[Date: $date; biometricId: $id; timeIn: $ti; timeOut: $to; mode: $mode]",
                'exported'  => 'table_update'
            ));

            if (strtotime($df) <= strtotime($date) && strtotime($date) <= strtotime($dt)) {

                $data = [
                    'bioMetricId'   => $id,
                    'date'          => $d1,
                    'dateDuty'      => $date,
                    'mode'          => $mode,
                    'store'         => $baseId,
                    'store_bio_no'  => $bio
                ];

                $cond = ['bioMetricId' => $id, 'dateDuty' => $date];
                if ($mode == 'I') {
                    $data['timeIn']         = $time;
                    $data['timeOut']        = '0';
                    $data['dateTimeIn']     = $datetime;
                    $data['dateTimeOut']    = '0';

                    $cond['timeIn']         = $time;
                    $cond['timeOut']        = '0';
                }

                if ($mode == 'O') {
                    $data['timeIn']         = '0';
                    $data['timeOut']        = $time;
                    $data['dateTimeIn']     = '0';
                    $data['dateTimeOut']    = $datetime;

                    $cond['timeIn']         = '0';
                    $cond['timeOut']        = $time;
                }

                // $cond = ['bioMetricId' => $id, 'dateDuty' => $date];
                $query = $this->promo_model->select_rowArray('tk', $duty_table, $cond);
                if (!$query) {
                    $insert = $this->promo_model->insert('tk', $duty_table, $data);
                }
            }
        }

        //update duty/duty_next entries **************************************************************
        $cond = "1 GROUP BY bioMetricId, dateDuty ORDER BY dateDuty ASC";
        $query = $this->promo_model->select_resultArray('tk', $table, $cond);

        $percentage = 0;
        $percent = 100 / ($query ? count($query) : 1);
        foreach ($query as $key => $row) {

            $id     = $row['bioMetricId'];
            $date   = date('Y-m-d', strtotime($row['dateDuty']));

            $cond   = "bioMetricId='$id' OR barcodeId='$id'";
            $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
            $empId  = $assign['empId'];
            if ($assign['shiftCode']) {
                $shiftCode = $assign['shiftCode'];
            }

            $day = date('l', strtotime($row['dateDuty']));

            $no_schedule = $this->noSchedule($empId, $date, $day);

            $cond   = ['empId' => $empId, 'day_sched' => $day];
            $pds    = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
            if ($pds) {
                $shiftCode = $pds['shiftCode'];
            }

            $cond   = "empId='$empId' AND status='active' AND status2='Approved' AND '$date' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
            $ams    = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond);
            if ($ams) {
                $shiftCode = $ams['shiftCode'];
            }

            $four_entries = false;
            $cond   = ['shiftCode' => $shiftCode];
            $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
            if ($sc) {

                $fIn    = date("H:i:s", strtotime($sc['1stIn']));
                $fOut   = date("H:i:s", strtotime($sc['1stOut']));
                $sIn    = date("H:i:s", strtotime($sc['2ndIn']));
                $sOut   = date("H:i:s", strtotime($sc['2ndOut']));

                $s_in1  = $sc['1stIn'];
                $s_out1 = $sc['1stOut'];
                $s_in2  = $sc['2ndIn'];
                $s_out2 = $sc['2ndOut'];

                if ($fIn && $fOut && $sIn && $sOut) {
                    $four_entries = true;
                }
            }

            $cond   = ['shiftCode' => $shiftCode];
            $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
            if ($scb) {

                $four_entries = true;

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

            $day_outH   = date("H", strtotime($s_out2));
            $day_outM   = date("i", strtotime($s_out2));

            if ($four_entries) {

                if (($day_in1 == 'AM' && $day_out2 == 'PM') || ($day_in1 == 'AM' && $day_out2 == 'AM') || ($day_in1 == 'PM' && $day_out2 == 'PM')) {

                    $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';

                    $time_in1   = date("H:i:s", strtotime($s_in1));
                    $time_out1  = date("H:i:s", strtotime($s_out1));
                    $time_in2   = date("H:i:s", strtotime($s_in2));
                    $time_out2  = date("H:i:s", strtotime($s_out2));

                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn < '$time_out1' ORDER BY timeIn ASC";
                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                    if ($check) {
                        $in1 = $check['timeIn'];
                        $time_in1 = $check['timeIn'];
                    } else {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn < '$time_out1' ORDER BY timeIn ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $in1 = $check['timeIn'];
                            $time_in1 = $check['timeIn'];
                        }
                    }

                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut < '$time_in2' AND timeOut > '$time_in1' ORDER BY timeOut ASC";
                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                    if ($check) {
                        $out1 = $check['timeOut'];
                        $time_out1 = $check['timeOut'];
                    } else {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut < '$time_in2' AND timeOut > '$time_in1' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $out1 = $check['timeOut'];
                            $time_out1 = $check['timeOut'];
                        }
                    }

                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND timeIn < '$time_out2' ORDER BY timeIn ASC";
                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                    if ($check) {
                        $in2 = $check['timeIn'];
                        $time_in2 = $check['timeIn'];
                    } else {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND timeIn < '$time_out2' ORDER BY timeIn ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $in2 = $check['timeIn'];
                            $time_in2 = $check['timeIn'];
                        }
                    }

                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_in2' ORDER BY timeOut ASC";
                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                    if ($check) {
                        $out2 = $check['timeOut'];
                        $time_out2 = $check['timeOut'];

                        if ($time_out2 < "$sOut:00") {
                            $time_out2 = "$sOut:00";
                        }
                    } else {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_in2' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $out2 = $check['timeOut'];
                            $time_out2 = $check['timeOut'];

                            if ($time_out2 < "$sOut:00") {
                                $time_out2 = "$sOut:00";
                            }
                        } else {
                            $tomorrow = date("Y-m-d", strtotime("$date +1 day"));
                            $cond   = "bioMetricId='$id' AND dateDuty='$tomorrow' AND mode='O' AND timeOut < '$time_in1' ORDER BY timeOut ASC";
                            $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                            if ($check) {
                                $out2 = $check['timeOut'];
                            }
                        }
                    }

                    if ($out2 != '') {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out2' AND timeIn > '$out2' ORDER BY timeIn ASC";
                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                        if ($check) {
                            $in3 = $check['timeIn'];
                        } else {
                            $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out2' AND timeIn > '$out2' ORDER BY timeIn ASC";
                            $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                            if ($check) {
                                $in3 = $check['timeIn'];
                            }
                        }
                    }

                    if ($in3 != '') {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_out2' AND timeOut > '$in3' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                        if ($check) {
                            $out3 = $check['timeOut'];
                        } else {
                            $tomorrow = date("Y-m-d", strtotime("$date +1 day"));
                            $to_day = date("Y-m-d H:i:s", strtotime("$date $in3"));
                            $cond   = "bioMetricId='$id' AND dateDuty='$tomorrow' AND mode='O' AND timeOut < '$fIn' AND dateTimeOut > '$to_day' ORDER BY timeOut ASC";
                            $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                            if ($check) {
                                $out3 = $check['timeOut'];
                            }
                        }
                    }
                }

                if ($day_in1 == 'PM' && $day_out1 == 'AM' && $day_in2 == 'AM' && $day_out2 == 'AM') {

                    $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';

                    $time_in1   = date("H:i:s", strtotime($s_in1));
                    $time_out1  = date("H:i:s", strtotime($s_out1));
                    $time_in2   = date("H:i:s", strtotime($s_in2));
                    $time_out2  = date("H:i:s", strtotime($s_out2));

                    $dt_in1     = date("Y-m-d H:i:s", strtotime("$date $s_in1"));
                    $dt_out1    = date("Y-m-d H:i:s", strtotime("$date $s_out1"));
                    $dt_in2     = date("Y-m-d H:i:s", strtotime("$date $s_in2"));
                    $dt_out2    = date("Y-m-d H:i:s", strtotime("$date $s_out2"));

                    $date_tom       = date("Y-m-d", strtotime("$date +1 day"));
                    $dt_in1_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in1"));
                    $dt_out1_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out1"));
                    $dt_in2_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in2"));
                    $dt_out2_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out2"));

                    // shift 10PM - 2AM | 3AM - 6AM
                    // entry 9:59PM - 2:01AM | 2:59AM - 6:01AM

                    // 9:59PM > 2:00AM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND dateTimeIn > '$dt_out1' ORDER BY timeIn ASC";
                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                    if ($check) {
                        $in1 = $check['timeIn'];
                        $time_in1 = $check['timeIn'];
                    }

                    // 2:01AM < 3AM AND 2025-11-10 2:01AM > 2025-11-09 10:00PM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut < '$time_in2' AND dateTimeOut > '$dt_in1' ORDER BY timeOut ASC";
                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                    if ($check) {
                        $out1 = $check['timeOut'];
                        $time_out1 = $check['timeOut'];
                    }

                    // 2:59AM > 2:01AM AND 2025-11-10 2:59AM < 2025-11-10 06:00AM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn > '$time_out1' AND dateTimeIn < '$dt_out2_tom' ORDER BY timeIn ASC";
                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                    if ($check) {
                        $in2 = $check['timeIn'];
                        $time_in2 = $check['timeIn'];
                    }

                    // 6:01AM > 2:59AM AND 2025-11-10 6:01AM < 2025-11-10 10:00PM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_in2' AND dateTimeOut < '$dt_in1_tom' ORDER BY timeOut ASC";
                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                    if ($check) {
                        $out2 = $check['timeOut'];
                        $time_out2 = $check['timeOut'];
                    }

                    if ($out2 != '') {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn > '$time_out2' AND dateTimeIn < '$dt_in1_tom' ORDER BY timeIn ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $in3 = $check['timeIn'];
                        }
                    }

                    if ($in3 != '') {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_out2' AND timeOut > '$in3' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                        if ($check) {
                            $out3 = $check['timeOut'];
                        }
                    }
                }

                if ($day_in1 == 'PM' && $day_out1 == 'PM' && $day_in2 == 'AM' && $day_out2 == 'AM') {

                    $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';

                    $time_in1   = date("H:i:s", strtotime($s_in1));
                    $time_out1  = date("H:i:s", strtotime($s_out1));
                    $time_in2   = date("H:i:s", strtotime($s_in2));
                    $time_out2  = date("H:i:s", strtotime($s_out2));

                    $dt_in1     = date("Y-m-d H:i:s", strtotime("$date $s_in1"));
                    $dt_out1    = date("Y-m-d H:i:s", strtotime("$date $s_out1"));
                    $dt_in2     = date("Y-m-d H:i:s", strtotime("$date $s_in2"));
                    $dt_out2    = date("Y-m-d H:i:s", strtotime("$date $s_out2"));

                    $date_tom       = date("Y-m-d", strtotime("$date +1 day"));
                    $dt_in1_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in1"));
                    $dt_out1_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out1"));
                    $dt_in2_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in2"));
                    $dt_out2_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out2"));

                    // shift 10PM - 11PM | 12AM - 6AM
                    // entry 9:59PM - 11:01PM | 11:59PM - 6:01AM

                    // 9:59PM < 11:00PM AND 2025-11-09 9:59PM < 2025-11-09 11:00PM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn < '$time_out1' AND dateTimeIn < '$dt_out1' ORDER BY timeIn ASC";
                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                    if ($check) {
                        $in1 = $check['timeIn'];
                        $time_in1 = $check['timeIn'];
                    }

                    // 11:01PM > 12AM AND 2025-11-09 11:01PM > 2025-11-09 10:00PM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_in2' AND dateTimeOut > '$dt_in1' ORDER BY timeOut ASC";
                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                    if ($check) {
                        $out1 = $check['timeOut'];
                        $time_out1 = $check['timeOut'];
                    }

                    // if dili late 
                    // 11:59PM > 11:00PM AND 2025-11-09 11:59PM < 2025-11-10 06:00AM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND dateTimeIn < '$dt_out2_tom' ORDER BY timeIn ASC";
                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                    if ($check) {
                        $in2 = $check['timeIn'];
                        $time_in2 = $check['timeIn'];
                    } else {
                        // if late 
                        // 12:01AM < 11:00PM AND 2025-11-10 12:01AM < 2025-11-10 06:00AM
                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn < '$time_out1' AND dateTimeIn < '$dt_out2_tom' ORDER BY timeIn ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $in2 = $check['timeIn'];
                            $time_in2 = $check['timeIn'];
                        }
                    }

                    // if dili late 
                    // 6:01AM < 11:59PM AND 2025-11-10 6:01AM < 2025-11-10 10:00PM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut < '$time_in2' AND dateTimeOut < '$dt_in1_tom' ORDER BY timeOut ASC";
                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                    if ($check) {
                        $out2 = $check['timeOut'];
                        $time_out2 = $check['timeOut'];
                    } else {
                        // if late 
                        // 6:01AM > 12:01AM AND 2025-11-10 6:01AM > 2025-11-10 10:00PM
                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_in2' AND dateTimeOut > '$dt_in1_tom' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $out2 = $check['timeOut'];
                            $time_out2 = $check['timeOut'];
                        }
                    }


                    if ($out2 != '') {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn > '$time_out2' AND dateTimeIn < '$dt_in1_tom' ORDER BY timeIn ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $in3 = $check['timeIn'];
                        }
                    }

                    if ($in3 != '') {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_out2' AND timeOut > '$in3' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                        if ($check) {
                            $out3 = $check['timeOut'];
                        }
                    }
                }

                if ($day_in1 == 'PM' && $day_out1 == 'PM' && $day_in2 == 'PM' && $day_out2 == 'AM') {

                    $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';

                    $time_in1       = date("H:i:s", strtotime($s_in1));
                    $time_out1      = date("H:i:s", strtotime($s_out1));
                    $time_in2       = date("H:i:s", strtotime($s_in2));
                    $time_out2      = date("H:i:s", strtotime($s_out2));

                    $dt_in1         = date("Y-m-d H:i:s", strtotime("$date $s_in1"));
                    $dt_out1        = date("Y-m-d H:i:s", strtotime("$date $s_out1"));
                    $dt_in2         = date("Y-m-d H:i:s", strtotime("$date $s_in2"));
                    $dt_out2        = date("Y-m-d H:i:s", strtotime("$date $s_out2"));

                    $date_tom       = date("Y-m-d", strtotime("$date +1 day"));
                    $dt_in1_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in1"));
                    $dt_out1_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out1"));
                    $dt_in2_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in2"));
                    $dt_out2_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out2"));

                    // shift 9PM - 10PM | 11PM - 12AM
                    // entry 8:59PM - 10:01PM | 10:59PM - 12:01AM

                    // 8:59PM < 10:00PM AND 2025-11-09 8:59PM < 2025-11-09 11:00PM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn < '$time_out1' AND dateTimeIn < '$dt_out1' ORDER BY timeIn ASC";
                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                    if ($check) {
                        $in1 = $check['timeIn'];
                        $time_in1 = $check['timeIn'];
                    }

                    // 10:01PM < 11PM AND 2025-11-09 10:01PM > 2025-11-09 09:00PM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut < '$time_in2' AND dateTimeOut > '$dt_in1' ORDER BY timeOut ASC";
                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                    if ($check) {
                        $out1 = $check['timeOut'];
                        $time_out1 = $check['timeOut'];
                    }

                    // 10:59PM > 10:00PM AND 2025-11-09 10:59PM < 2025-11-10 06:00AM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND dateTimeIn < '$dt_out2_tom' ORDER BY timeIn ASC";
                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                    if ($check) {
                        $in2 = $check['timeIn'];
                        $time_in2 = $check['timeIn'];
                    }

                    // if dili undertime
                    // 6:01AM < 10:59PM AND 2025-11-10 6:01AM < 2025-11-10 10:00PM
                    $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut < '$time_in2' AND dateTimeOut < '$dt_in1_tom' ORDER BY timeOut ASC";
                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                    if ($check) {
                        $out2 = $check['timeOut'];
                        $time_out2 = $check['timeOut'];
                    } else {
                        // if undertime
                        // 11:59PM > 10:59PM AND 2025-11-09 11:59PM < 2025-11-10 10:00PM
                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_in2' AND dateTimeOut < '$dt_in1_tom' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $out2 = $check['timeOut'];
                            $time_out2 = $check['timeOut'];
                        }
                    }

                    if ($out2 != '') {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn > '$time_out2' AND dateTimeIn < '$dt_in1_tom' ORDER BY timeIn ASC";
                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                        if ($check) {
                            $in3 = $check['timeIn'];
                        }
                    }

                    if ($in3 != '') {
                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_out2' AND timeOut > '$in3' ORDER BY timeOut ASC";
                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                        if ($check) {
                            $out3 = $check['timeOut'];
                        }
                    }
                }

                if ($out1 && $in2) {

                    $dayEx  = date("D", strtotime($date));
                    $cond   = ['empId' => $empId, 'status' => 'active', 'day' => $dayEx];
                    $check  = $this->promo_model->select_rowArray('tk', 'setup_inter_io_exempt', $cond);
                    if (!$check) {
                        $sub_in2    = date("H:i:s", strtotime("$in2 -1 minutes"));
                        $temp_out1  = date("H:i:s", strtotime($out1));

                        if (strtotime($sub_in2) <= strtotime($temp_out1)) {
                            $out1 = "";
                        }
                    }
                }

                $percentage += $percent;

                if ($no_schedule) {
                    $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';
                }

                echo json_encode(array(
                    'result'    => 'loading',
                    'msg'       => round($percentage) . '%',
                    'msg1'      => round($percentage) . '%',
                    'date'      => "Inserting Table (duty/duty_next)",
                    'table'     => "[Date: $date; biometricId: $id; in1: $in1; out1: $out1; in2: $in2; out2: $out2; in3: $in3; out3: $out3;]",
                    'exported'  => 'table_update'
                ));

                $data = [
                    'in1'   => $in1,
                    'out1'  => $out1,
                    'in2'   => $in2,
                    'out2'  => $out2,
                    'in3'   => $in3,
                    'out3'  => $out3,
                ];

                $cond = ['bioMetricId' => $id, 'dateDuty' => $date];
                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                $data_ka = [
                    'in1_ka'   => date("Y-m-d H:i:s", strtotime("$date $in1")),
                    'out1_ka'  => date("Y-m-d H:i:s", strtotime("$date $out1")),
                    'in2_ka'   => date("Y-m-d H:i:s", strtotime("$date $in2")),
                    'out2_ka'  => date("Y-m-d H:i:s", strtotime("$date $out2")),
                    'in3_ka'   => date("Y-m-d H:i:s", strtotime("$date $in3")),
                    'out3_ka'  => date("Y-m-d H:i:s", strtotime("$date $out3")),
                ];

                $cond   = ['bioMetricId_ka' => $id, 'dateDuty_ka' => $date];
                $update = $this->promo_model->update('tk', 'duty_next', $cond, $data_ka);
            }
        }

        //update perday entries **************************************************************
        $cond = "1 GROUP BY bioMetricId, dateDuty ORDER BY dateDuty ASC";
        $query = $this->promo_model->select_resultArray('tk', $table, $cond);

        $percentage = 0;
        $totalLoad  = count($query);
        foreach ($query as $key => $row) {

            $id     = $row['bioMetricId'];
            $date   = date('Y-m-d', strtotime($row['dateDuty']));

            $percent = 100 / ($totalLoad ? $totalLoad : 1);
            $percentage = $percentage + $percent;

            echo json_encode(array(
                'result'    => 'loading',
                'msg'       => round($percentage) . '%',
                'msg1'      => round($percentage) . '%',
                'date'      => "Inserting Table (perday)",
                'table'     => "[Date: $date; biometricId: $id;]",
                'exported'  => 'table_update'
            ));

            $cond   = "bioMetricId='$id' OR barcodeId='$id'";
            $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
            $empId  = $assign['empId'];
            if ($assign['shiftCode']) {
                $shiftCode = $assign['shiftCode'];
            }

            $day = date('l', strtotime($row['dateDuty']));

            $cond   = ['empId' => $empId, 'day_sched' => $day];
            $pds    = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
            if ($pds) {
                $shiftCode = $pds['shiftCode'];
            }

            $cond   = "empId='$empId' AND status = 'active' AND status2 = 'Approved' AND '$date' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
            $ams    = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond);
            if ($ams) {
                $shiftCode = $ams['shiftCode'];
            }

            $four_entries = false;
            $cond   = ['shiftCode' => $shiftCode];
            $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
            if ($sc) {

                $night_diff = $sc['night_diff'];

                $fIn    = date("H:i:s", strtotime($sc['1stIn']));
                $fOut   = date("H:i:s", strtotime($sc['1stOut']));
                $sIn    = date("H:i:s", strtotime($sc['2ndIn']));
                $sOut   = date("H:i:s", strtotime($sc['2ndOut']));

                $s_in1  = $sc['1stIn'];
                $s_out1 = $sc['1stOut'];
                $s_in2  = $sc['2ndIn'];
                $s_out2 = $sc['2ndOut'];

                if ($fIn && $fOut && $sIn && $sOut) {
                    $four_entries = true;
                }
            }

            $cond   = ['shiftCode' => $shiftCode];
            $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
            if ($scb) {

                $four_entries = true;

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

            $time_in1   = date("H:i:s", strtotime($s_in1));
            $time_out1  = date("H:i:s", strtotime($s_out1));
            $time_in2   = date("H:i:s", strtotime($s_in2));
            $time_out2  = date("H:i:s", strtotime($s_out2));

            if (true) {

                $cond   = ['bioMetricId' => $id, 'dateDuty' => $date];
                $duty   = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                $dutyId = $duty['dutyId'];
                $in1    = $duty['in1'];
                $out1   = $duty['out1'];
                $in2    = $duty['in2'];
                $out2   = $duty['out2'];
                $in3    = $duty['in3'];
                $out3   = $duty['out3'];

                $cond   = ['dutyId' => $dutyId];
                $check  = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                if (!$check) {
                    $data = ['dutyId' => $dutyId];
                    $insert = $this->promo_model->insert('tk', 'perday', $data);
                }

                $next_not = false;
                if ($day_in2 == "AM" && $day_out2 == "AM" && !$scb) {
                    $next_not = true;
                }

                if ($day_out1 == "AM" && $day_in2 == "AM" && $day_out2 == "AM" && !$scb) {
                    $next_not = true;
                }

                $two_four = false;
                if ($scb) {
                    if ($day_in1 == "PM" && $day_in2 == "AM" && $day_out2 == "AM") {
                        $next_not = true;
                        $two_four = true;
                    }

                    if ($day_in1 == "PM" && $day_out1 == "AM" && $day_in2 == "AM") {
                        $next_not = true;
                        $two_four = true;
                    }

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

                        $min = date("H:i", strtotime("$in1 -$h hours, -$m minutes"));
                        if ($min <= $maxLate) {
                            $t      = explode(":", $min);
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

                        $min = date("H:i", strtotime("$in2 -$h hours, -$m minutes"));
                        if ($min <= $maxLate) {
                            $t      = explode(":", $min);
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
                        $h = $t[0];
                        $m = $t[1];

                        $hm = date("H:i", strtotime("$newOut1 -$h hours, -$m minutes"));
                        $t  = explode(":", $hm);
                        $h  = $t[0] * 1;
                        $m  = $t[1] * 1;

                        $hw = ($m != 0) ? "$h hr(s). & $m min(s)." : "$h hr(s).";
                    }

                    //if more than 8 hours
                    if (!$hw) {
                        $res = explode("&", $hw);
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

                        $dw = round($hrs + $min, 3);
                        $decimal = explode('.', (string)$dw);
                        if (isset($decimal[1]) && strlen($decimal[1]) > 3) {
                            $dw = number_format($dw, 3, '.', '');
                        }

                        if ($dw > 8) {
                            $hw = 0;
                        }

                        if ($dw > 8.5) {
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
                        $h  = $t[0] * 1;
                        $m  = $t[1] * 1;

                        $ot12 = date("H:i", strtotime("$hi_ot2 +$h hours, +$m minutes"));

                        //caculate the ot12 + ot3 overtime
                        $t  = explode(":", $ot12);
                        $h  = $t[0] * 1;
                        $m  = $t[1] * 1;

                        $ot123 = date('H:i', strtotime("$hi_ot3 +$h hours, +$m minutes"));
                        $t  = explode(":", $ot123);
                        $h  = $t[0] * 1;
                        $m  = $t[1] * 1;

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

        //update perday nigt diff **************************************************************
        $cond = "1 GROUP BY bioMetricId, dateDuty ORDER BY dateDuty ASC";
        $query = $this->promo_model->select_resultArray('tk', $table, $cond);
        foreach ($query as $key => $row) {

            $id     = $row['bioMetricId'];
            $date   = date('Y-m-d', strtotime($row['dateDuty']));

            $cond   = ['bioMetricId' => $id, 'dateDuty' => $date];
            $duty   = $this->promo_model->select_rowArray('tk', 'duty', $cond);
            $dutyId = $duty['dutyId'];
            $in1    = $duty['in1'];
            $out1   = $duty['out1'];
            $in2    = $duty['in2'];
            $out2   = $duty['out2'];
            $in3    = $duty['in3'];
            $out3   = $duty['out3'];

            $cond   = "bioMetricId='$id' OR barcodeId='$id'";
            $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
            $empId  = $assign['empId'];
            if ($assign['shiftCode']) {
                $shiftCode = $assign['shiftCode'];
            }

            $day = date('l', strtotime($row['dateDuty']));
            $cond = ['empId' => $empId, 'day_sched' => $day];
            $pds = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
            if ($pds) {
                $shiftCode = $pds['shiftCode'];
            }

            $cond = "empId='$empId' AND status = 'active' AND status2 = 'Approved' AND '$date' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
            $ams = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond);
            if ($ams) {
                $shiftCode = $ams['shiftCode'];
            }

            $night_diff = '';
            $four_entries = false;
            $cond   = ['shiftCode' => $shiftCode];
            $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
            if ($sc) {

                $fIn    = date("H:i:s", strtotime($sc['1stIn']));
                $fOut   = date("H:i:s", strtotime($sc['1stOut']));
                $sIn    = date("H:i:s", strtotime($sc['2ndIn']));
                $sOut   = date("H:i:s", strtotime($sc['2ndOut']));

                $s_in1  = $sc['1stIn'];
                $s_out1 = $sc['1stOut'];
                $s_in2  = $sc['2ndIn'];
                $s_out2 = $sc['2ndOut'];

                $night_diff = $sc['night_diff'];

                if ($fIn && $fOut && $sIn && $sOut) {
                    $four_entries = true;
                }
            }

            $cond   = ['shiftCode' => $shiftCode];
            $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
            if ($scb) {

                $four_entries = true;

                $fIn    = date("H:i:s", strtotime($scb['1stIn']));
                $fOut   = date("H:i:s", strtotime($scb['1stOut']));
                $sIn    = date("H:i:s", strtotime($scb['2ndIn']));
                $sOut   = date("H:i:s", strtotime($scb['2ndOut']));

                $s_in1  = $scb['1stIn'];
                $s_out1 = $scb['1stOut'];
                $s_in2  = $scb['2ndIn'];
                $s_out2 = $scb['2ndOut'];

                $temp_entries1 = false;
                $temp_entries2 = false;
                if (empty($out1)) {
                    $temp_entries1 = true;
                    $out1 = date('H:i:s', strtotime($s_out1));
                }
                if (empty($in2)) {
                    $temp_entries2 = true;
                    $in2 = date('H:i:s', strtotime($s_in2));
                }
                if (empty($in1)) {
                    $out1 = '';
                }
                if (empty($out2)) {
                    $in2 = '';
                    $out1 = '';
                }

                if (empty($in1) && !empty($out2) && $temp_entries1) {
                    $in2 = '';
                }
                if (!empty($in1) && empty($out2) && $temp_entries2) {
                    $out1 = '';
                }
            }

            $day_in1    = date('A', strtotime($s_in1));
            $day_out1   = date('A', strtotime($s_out1));
            $day_in2    = date('A', strtotime($s_in2));
            $day_out2   = date('A', strtotime($s_out2));

            $cond = "1 ORDER BY snId";
            $snd = $this->promo_model->select_rowArray('tk', 'setup_n_d', $cond);
            $ndStart = $snd['timeStarted']; //10PM
            $ndEnded = $snd['timeEnded']; //06AM
            $minTime = $snd['minTime']; //30min

            $nd_in1 = $nd_out1 = $nd_in2 = $nd_out2 = '00:00';

            //in1
            if ($day_in1 == 'PM' && $in1) {

                if ($in1 >= $ndStart && $in1 <= strtotime('23:59:59')) {
                    $nd_in1 = ($in1 <= $fIn) ? $fIn : $in1;
                } else {
                    $nd_in1 = $ndStart;
                }
            }

            if ($day_in1 == 'AM' && $in1) {

                if ($in1 <= $ndEnded) {
                    $nd_in1 = ($in1 <= $fIn) ? $fIn : $in1;
                } else {
                    if (date('A', strtotime($in1)) == 'PM') {
                        $nd_in1 = ($in1 >= $fIn) ? $fIn : $in1;
                    } else {
                        $nd_in1 = $ndEnded;
                    }
                }
            }

            //out1
            if ($day_out1 == 'PM' && $out1) {

                if ($out1 >= $ndStart && $out1 <= strtotime('23:59:59')) {

                    if ($ndStart <= $fOut) {
                        $nd_out1 = ($out1 <= $fOut) ? $out1 : $fOut;
                    } else {
                        $nd_out1 = $ndStart;
                    }
                } else {
                    $nd_out1 = $ndStart;
                }
            }

            if ($day_out1 == 'AM' && $out1) {

                if ($out1 <= $ndEnded) {
                    $nd_out1 = ($out1 <= $fOut) ? $out1 : $fOut;
                } else {
                    if (date('A', strtotime($out1)) == 'PM') {
                        $nd_out1 = ($out1 >= $fOut) ? $out1 : $fOut;
                    } else {
                        $nd_out1 = $ndEnded;
                    }
                }
            }

            //in2
            if ($day_in2 == 'PM' && $in2) {

                if ($in2 >= $ndStart && $in2 <= strtotime('23:59:59')) {
                    $nd_in2 = ($in2 <= $sIn) ? $sIn : $in2;
                } else {
                    $nd_in2 = $ndStart;
                }
            }

            if ($day_in2 == 'AM' && $in2) {

                if ($in2 <= $ndEnded) {
                    $nd_in2 = ($in2 <= $sIn) ? $sIn : $in2;
                } else {
                    if (date('A', strtotime($in2)) == 'PM') {
                        $nd_in2 = ($in2 >= $sIn) ? $sIn : $in2;
                    } else {
                        $nd_in2 = $ndEnded;
                    }
                }
            }

            //out2
            if ($day_out2 == 'PM' && $out2) {
                if ($out2 >= $ndStart && $out2 <= strtotime('23:59:59')) {

                    if ($ndStart <= $sOut) {
                        $nd_out2 = ($out2 <= $sOut) ? $out2 : $sOut;
                    } else {
                        $nd_out2 = $ndStart;
                    }
                } else {
                    $nd_out2 = $ndStart;
                }
            }

            if ($day_out2 == 'AM' && $out2) {

                if ($out2 <= $ndEnded) {
                    $nd_out2 = ($out2 <= $sOut) ? $out2 : $sOut;
                } else {
                    if (date('A', strtotime($out2)) == 'PM') {
                        $nd_out2 = ($out2 >= $sOut) ? $out2 : $sOut;
                    } else {
                        $nd_out2 = $ndEnded;
                    }
                }
            }

            //nd
            $nd = 0;
            if ($night_diff == 'yes') {

                $t  = explode(":", $nd_in1);
                $h  = $t[0];
                $m  = $t[1];
                $t1 = date("H:i", strtotime("$nd_out1 -$h hours, -$m minutes"));

                $t  = explode(":", $nd_in2);
                $h  = $t[0];
                $m  = $t[1];
                $t2 = date("H:i", strtotime("$nd_out2 -$h hours, -$m minutes"));

                list($h1, $m1) = explode(":", $t1);
                list($h2, $m2) = explode(":", $t2);

                $totalMinutes = ($h1 * 60 + $m1) + ($h2 * 60 + $m2);
                $hh = floor($totalMinutes / 60);
                $mm = $totalMinutes % 60;

                $hm = sprintf("%02d:%02d", $hh, $mm);

                $t  = explode(":", $hm);
                $h  = $t[0] * 1;
                $m  = $t[1] * 1;

                if ($m >= $minTime) {
                    $m = '30';
                } else {
                    $m = '00';
                }

                $hrs = $h / 8;
                $min = ($m / 60) / 8;

                $min = round(($m / 60), 3);
                $nd =  round($h + $min, 3);
                $decimal = explode('.', (string)$h + $min);
                if (isset($decimal[1]) && strlen($decimal[1]) > 3) {
                    $nd = number_format($h + $min, 3, '.', '');
                }

                $cond = ['dutyId' => $dutyId];
                $data = ['night_diff' => $nd];
                $update = $this->promo_model->update('tk', 'perday', $cond, $data);
            }
        }

        //update if data is empty **************************************************************
        $count = 0;
        $start = date('Y-m-d', strtotime($input['startDate']));
        $end = date('Y-m-d', strtotime($input['endDate']));
        while ($start <= $end) {
            $count++;
            $start = date('Y-m-d', strtotime("$start +1 day"));
        }

        $percentage = 0;
        $pDiv1 = 100 / $count;
        $start = date('Y-m-d', strtotime($input['startDate']));
        $end = date('Y-m-d', strtotime($input['endDate']));
        while ($start <= $end) {

            $percent = $pDiv1 / count($empIds);
            foreach ($empIds as $key => $empId) {
                $id = '';
                $cond = "empId='$empId' AND barcodeId NOT LIKE '%\_%' AND bioMetricId NOT LIKE '%\_%' ORDER BY assignId DESC LIMIT 1";
                $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
                if ($assign) {
                    $allowId = $assign['allowId'];
                    if ($allowId == "Biometric ID") {
                        $id = $assign['bioMetricId'];
                    }
                    if ($allowId == "Logbox ID") {
                        $id = $assign['barcodeId'];
                    }
                } else {
                    $cond = "empId='$empId' AND status='active'";
                    $id = $this->promo_model->select_rowArray('tk', 'promo_id', $cond)['barcodeId'];
                }
                $percentage += $percent;
                $cond = ['bioMetricId' => $id, 'dateDuty' => $start];
                $query = $this->promo_model->select_rowArray('tk', $table, $cond);
                if (!$query) {
                    echo json_encode(array(
                        'result'    => 'loading',
                        'msg'       => round($percentage) . '%',
                        'msg1'      => round($percentage) . '%',
                        'date'      => "Updating Table (duty/perday) NO DATA",
                        'table'     => "[Date: $start; biometricId: $id; empId: $empId]",
                        'exported'  => 'table_update'
                    ));

                    $cond   = ['bioMetricId' => $id, 'dateDuty' => $start];
                    $dutyId = $this->promo_model->select_rowArray('tk', 'duty', $cond)['dutyId'];
                    if ($dutyId) {
                        $cond = ['bioMetricId' => $id, 'dateDuty' => $start];
                        $data = ['in1' => '', 'out1' => '', 'in2' => '', 'out2' => '', 'in3' => '', 'out3' => ''];
                        $this->promo_model->update('tk', 'duty', $cond, $data);

                        $cond   = ['dutyId' => $dutyId];
                        $check  = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                        if ($check) {
                            $this->netEntery($empId, $id, $start, '', '', '', '', '', '');
                        }
                    }
                }
            }
            $start = date('Y-m-d', strtotime("$start +1 day"));
        }

        unlink($filePath);
        $this->promo_model->drop_table($table); // delete table

        echo json_encode(array(
            'result'    => 'loading',
            'msg'       => round(100) . '%',
            'msg1'      => round(100) . '%',
            'date'      => "Process Complete!",
            'table'     => "",
            'exported'  => 'initialize'
        ));

        sleep(1);

        echo json_encode(array(
            'result'    => 'success',
            'msg'       => round(100) . '%',
            'msg1'      => round(100) . '%',
            'date'      => "Process Complete!",
            'table'     => "",
            'exported'  => 'initialize'
        ));
    }

    public function cs_first()
    {
        $input = $this->input->post(NULL, TRUE);
        $statCut = $input['statCut'];
        $data = [];

        $i = 0;
        $query = $this->promo_model->process_cutoff_table($statCut, '1stcutoff');
        foreach ($query as $row) {
            $i++;
            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));

            $onclick = 'onclick=processCSfirst(\'' . $statCut . '\',\'1\',\'' . $row['dateFrom'] . '\',\'' . $row['dateTo'] . '\')';


            $sub_array = [];
            $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
            $sub_array[] = $dt;
            $sub_array[] = '<a href="javascript:;" ' . $onclick . ' class="csProcess"><span class="btn btn-primary btn-xs">Process</span></a>';
            $data[] = $sub_array;
        }

        $i = 0;
        $query = $this->promo_model->process_cutoff_table($statCut, '2ndcutoff');
        foreach ($query as $row) {
            $i++;
            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));

            $onclick = 'onclick=processCSfirst(\'' . $statCut . '\',\'2\',\'' . $row['dateFrom'] . '\',\'' . $row['dateTo'] . '\')';


            $sub_array = [];
            $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
            $sub_array[] = $dt;
            $sub_array[] = '<a href="javascript:;" ' . $onclick . ' class="csProcess"><span class="btn btn-primary btn-xs">Process</span></a>';
            $data[] = $sub_array;
        }

        echo json_encode(['data' => $data]);
    }

    public function processCSfirst()
    {
        $input      = $this->input->post(NULL, TRUE);
        $dateFrom   = $input['df'];
        $dateTo     = $input['dt'];
        $statCut    = $input['statCut'];

        set_time_limit(0);
        ob_implicit_flush(true);
        ob_end_flush();

        echo json_encode(array(
            'result'    => 'loading',
            'msg'       => round(0) . '%',
            'msg1'      => round(0) . '%',
            'date'      => "Preparing list...",
            'table'     => "",
            'exported'  => 'table_update',
        ));

        sleep(1);

        //update duty/duty_next entries **************************************************************
        echo json_encode(array(
            'result'    => 'loading',
            'msg'       => round(0) . '%',
            'msg1'      => round(0) . '%',
            'date'      => "Preparing duty/duty_next table...",
            'table'     => "",
            'exported'  => 'table_update',
        ));

        $csList = $this->promo_model->csFirst($dateFrom, $dateTo, $statCut);

        $empIds = [];
        $percentage = 0;
        $pdiv1      = 100 / count($csList);
        foreach ($csList as $key => $row) {
            $empId = $row['emp_id'];
            $amsId = $row['amsId'];

            $cond   = "empId='$empId' AND barcodeId NOT LIKE '%\_%' AND bioMetricId NOT LIKE '%\_%' ORDER BY assignId DESC LIMIT 1";
            $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);

            $table = "";
            $allowId = $assign['allowId'];
            if ($allowId == "Biometric ID") {
                $bioM   = $assign['bioMetricId'];
                $table  = "duty_biometric";
            }
            if ($allowId == "Logbox ID") {
                $bioM   = $assign['barcodeId'];
                $table  = "duty_logbox";
            }

            $cond = ['amsId' => $amsId, 'status' => 'Done'];
            $check = $this->promo_model->select_rowArray('tk', 'request_new_shift_calc', $cond);
            if (!$check) {
                if (!in_array($empId, $empIds)) {
                    $empIds[] = $empId;
                    $cond = "bioMetricId='$bioM' AND dateDuty BETWEEN '$dateFrom' AND '$dateTo' GROUP BY bioMetricId, dateDuty ORDER BY dateDuty ASC";
                    $query = $this->promo_model->select_resultArray('tk', $table, $cond);
                    if (count($query)) {
                        $percent = $pdiv1 / count($query);
                        foreach ($query as $key => $row) {

                            $id     = $row['bioMetricId'];
                            $date   = date('Y-m-d', strtotime($row['dateDuty']));
                            $cond   = "bioMetricId='$id' OR barcodeId='$id'";
                            $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
                            $empId  = $assign['empId'];
                            if ($assign['shiftCode']) {
                                $shiftCode = $assign['shiftCode'];
                            }

                            $day = date('l', strtotime($row['dateDuty']));

                            $cond   = ['empId' => $empId, 'day_sched' => $day];
                            $pds    = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
                            if ($pds) {
                                $shiftCode = $pds['shiftCode'];
                            }

                            $cond   = "empId='$empId' AND status = 'active' AND status2 = 'Approved' AND '$date' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
                            $ams    = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond);
                            if ($ams) {
                                $shiftCode = $ams['shiftCode'];
                            }

                            $four_entries = false;
                            $cond   = ['shiftCode' => $shiftCode];
                            $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
                            if ($sc) {

                                $fIn    = date("H:i:s", strtotime($sc['1stIn']));
                                $fOut   = date("H:i:s", strtotime($sc['1stOut']));
                                $sIn    = date("H:i:s", strtotime($sc['2ndIn']));
                                $sOut   = date("H:i:s", strtotime($sc['2ndOut']));

                                $s_in1  = $sc['1stIn'];
                                $s_out1 = $sc['1stOut'];
                                $s_in2  = $sc['2ndIn'];
                                $s_out2 = $sc['2ndOut'];

                                if ($fIn && $fOut && $sIn && $sOut) {
                                    $four_entries = true;
                                }
                            }

                            $cond   = ['shiftCode' => $shiftCode];
                            $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
                            if ($scb) {

                                $four_entries = true;

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

                            $day_outH   = date("H", strtotime($s_out2));
                            $day_outM   = date("i", strtotime($s_out2));

                            if ($four_entries) {

                                if (($day_in1 == 'AM' && $day_out2 == 'PM') || ($day_in1 == 'AM' && $day_out2 == 'AM') || ($day_in1 == 'PM' && $day_out2 == 'PM')) {

                                    $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';

                                    $time_in1   = date("H:i:s", strtotime($s_in1));
                                    $time_out1  = date("H:i:s", strtotime($s_out1));
                                    $time_in2   = date("H:i:s", strtotime($s_in2));
                                    $time_out2  = date("H:i:s", strtotime($s_out2));

                                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn < '$time_out1' ORDER BY timeIn ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                    if ($check) {
                                        $in1 = $check['timeIn'];
                                        $time_in1 = $check['timeIn'];
                                    } else {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn < '$time_out1' ORDER BY timeIn ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                        if ($check) {
                                            $in1 = $check['timeIn'];
                                            $time_in1 = $check['timeIn'];
                                        }
                                    }

                                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut < '$time_in2' AND timeOut > '$time_in1' ORDER BY timeOut ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                    if ($check) {
                                        $out1 = $check['timeOut'];
                                        $time_out1 = $check['timeOut'];
                                    } else {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut < '$time_in2' AND timeOut > '$time_in1' ORDER BY timeOut ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                        if ($check) {
                                            $out1 = $check['timeOut'];
                                            $time_out1 = $check['timeOut'];
                                        }
                                    }

                                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND timeIn < '$time_out2' ORDER BY timeIn ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                    if ($check) {
                                        $in2 = $check['timeIn'];
                                        $time_in2 = $check['timeIn'];
                                    } else {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND timeIn < '$time_out2' ORDER BY timeIn ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                        if ($check) {
                                            $in2 = $check['timeIn'];
                                            $time_in2 = $check['timeIn'];
                                        }
                                    }

                                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_in2' ORDER BY timeOut ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                    if ($check) {
                                        $out2 = $check['timeOut'];
                                        $time_out2 = $check['timeOut'];

                                        if ($time_out2 < "$sOut:00") {
                                            $time_out2 = "$sOut:00";
                                        }
                                    } else {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_in2' ORDER BY timeOut ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                        if ($check) {
                                            $out2 = $check['timeOut'];
                                            $time_out2 = $check['timeOut'];

                                            if ($time_out2 < "$sOut:00") {
                                                $time_out2 = "$sOut:00";
                                            }
                                        } else {
                                            $tomorrow = date("Y-m-d", strtotime("$date +1 day"));
                                            $cond   = "bioMetricId='$id' AND dateDuty='$tomorrow' AND mode='O' AND timeOut < '$time_in1' ORDER BY timeOut ASC";
                                            $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                            if ($check) {
                                                $out2 = $check['timeOut'];
                                            }
                                        }
                                    }

                                    if ($out2 != '') {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out2' AND timeIn > '$out2' ORDER BY timeIn ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                        if ($check) {
                                            $in3 = $check['timeIn'];
                                        } else {
                                            $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out2' AND timeIn > '$out2' ORDER BY timeIn ASC";
                                            $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                            if ($check) {
                                                $in3 = $check['timeIn'];
                                            }
                                        }
                                    }

                                    if ($in3 != '') {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_out2' AND timeOut > '$in3' ORDER BY timeOut ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                        if ($check) {
                                            $out3 = $check['timeOut'];
                                        } else {
                                            $tomorrow = date("Y-m-d", strtotime("$date +1 day"));
                                            $to_day = date("Y-m-d H:i:s", strtotime("$date $in3"));
                                            $cond   = "bioMetricId='$id' AND dateDuty='$tomorrow' AND mode='O' AND timeOut < '$fIn' AND dateTimeOut > '$to_day' ORDER BY timeOut ASC";
                                            $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                            if ($check) {
                                                $out3 = $check['timeOut'];
                                            }
                                        }
                                    }
                                }

                                if ($day_in1 == 'PM' && $day_out1 == 'AM' && $day_in2 == 'AM' && $day_out2 == 'AM') {

                                    $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';

                                    $time_in1   = date("H:i:s", strtotime($s_in1));
                                    $time_out1  = date("H:i:s", strtotime($s_out1));
                                    $time_in2   = date("H:i:s", strtotime($s_in2));
                                    $time_out2  = date("H:i:s", strtotime($s_out2));

                                    $dt_in1     = date("Y-m-d H:i:s", strtotime("$date $s_in1"));
                                    $dt_out1    = date("Y-m-d H:i:s", strtotime("$date $s_out1"));
                                    $dt_in2     = date("Y-m-d H:i:s", strtotime("$date $s_in2"));
                                    $dt_out2    = date("Y-m-d H:i:s", strtotime("$date $s_out2"));

                                    $date_tom       = date("Y-m-d", strtotime("$date +1 day"));
                                    $dt_in1_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in1"));
                                    $dt_out1_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out1"));
                                    $dt_in2_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in2"));
                                    $dt_out2_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out2"));

                                    // shift 10PM - 2AM | 3AM - 6AM
                                    // entry 9:59PM - 2:01AM | 2:59AM - 6:01AM

                                    // 9:59PM > 2:00AM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND dateTimeIn > '$dt_out1' ORDER BY timeIn ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                    if ($check) {
                                        $in1 = $check['timeIn'];
                                        $time_in1 = $check['timeIn'];
                                    }

                                    // 2:01AM < 3AM AND 2025-11-10 2:01AM > 2025-11-09 10:00PM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut < '$time_in2' AND dateTimeOut > '$dt_in1' ORDER BY timeOut ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                    if ($check) {
                                        $out1 = $check['timeOut'];
                                        $time_out1 = $check['timeOut'];
                                    }

                                    // 2:59AM > 2:01AM AND 2025-11-10 2:59AM < 2025-11-10 06:00AM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn > '$time_out1' AND dateTimeIn < '$dt_out2_tom' ORDER BY timeIn ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                    if ($check) {
                                        $in2 = $check['timeIn'];
                                        $time_in2 = $check['timeIn'];
                                    }

                                    // 6:01AM > 2:59AM AND 2025-11-10 6:01AM < 2025-11-10 10:00PM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_in2' AND dateTimeOut < '$dt_in1_tom' ORDER BY timeOut ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                    if ($check) {
                                        $out2 = $check['timeOut'];
                                        $time_out2 = $check['timeOut'];
                                    }

                                    if ($out2 != '') {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn > '$time_out2' AND dateTimeIn < '$dt_in1_tom' ORDER BY timeIn ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                        if ($check) {
                                            $in3 = $check['timeIn'];
                                        }
                                    }

                                    if ($in3 != '') {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_out2' AND timeOut > '$in3' ORDER BY timeOut ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                        if ($check) {
                                            $out3 = $check['timeOut'];
                                        }
                                    }
                                }

                                if ($day_in1 == 'PM' && $day_out1 == 'PM' && $day_in2 == 'AM' && $day_out2 == 'AM') {

                                    $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';

                                    $time_in1   = date("H:i:s", strtotime($s_in1));
                                    $time_out1  = date("H:i:s", strtotime($s_out1));
                                    $time_in2   = date("H:i:s", strtotime($s_in2));
                                    $time_out2  = date("H:i:s", strtotime($s_out2));

                                    $dt_in1     = date("Y-m-d H:i:s", strtotime("$date $s_in1"));
                                    $dt_out1    = date("Y-m-d H:i:s", strtotime("$date $s_out1"));
                                    $dt_in2     = date("Y-m-d H:i:s", strtotime("$date $s_in2"));
                                    $dt_out2    = date("Y-m-d H:i:s", strtotime("$date $s_out2"));

                                    $date_tom       = date("Y-m-d", strtotime("$date +1 day"));
                                    $dt_in1_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in1"));
                                    $dt_out1_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out1"));
                                    $dt_in2_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in2"));
                                    $dt_out2_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out2"));

                                    // shift 10PM - 11PM | 12AM - 6AM
                                    // entry 9:59PM - 11:01PM | 11:59PM - 6:01AM

                                    // 9:59PM < 11:00PM AND 2025-11-09 9:59PM < 2025-11-09 11:00PM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn < '$time_out1' AND dateTimeIn < '$dt_out1' ORDER BY timeIn ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                    if ($check) {
                                        $in1 = $check['timeIn'];
                                        $time_in1 = $check['timeIn'];
                                    }

                                    // 11:01PM > 12AM AND 2025-11-09 11:01PM > 2025-11-09 10:00PM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_in2' AND dateTimeOut > '$dt_in1' ORDER BY timeOut ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                    if ($check) {
                                        $out1 = $check['timeOut'];
                                        $time_out1 = $check['timeOut'];
                                    }

                                    // if dili late 
                                    // 11:59PM > 11:00PM AND 2025-11-09 11:59PM < 2025-11-10 06:00AM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND dateTimeIn < '$dt_out2_tom' ORDER BY timeIn ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                    if ($check) {
                                        $in2 = $check['timeIn'];
                                        $time_in2 = $check['timeIn'];
                                    } else {
                                        // if late 
                                        // 12:01AM < 11:00PM AND 2025-11-10 12:01AM < 2025-11-10 06:00AM
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn < '$time_out1' AND dateTimeIn < '$dt_out2_tom' ORDER BY timeIn ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                        if ($check) {
                                            $in2 = $check['timeIn'];
                                            $time_in2 = $check['timeIn'];
                                        }
                                    }

                                    // if dili late 
                                    // 6:01AM < 11:59PM AND 2025-11-10 6:01AM < 2025-11-10 10:00PM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut < '$time_in2' AND dateTimeOut < '$dt_in1_tom' ORDER BY timeOut ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                    if ($check) {
                                        $out2 = $check['timeOut'];
                                        $time_out2 = $check['timeOut'];
                                    } else {
                                        // if late 
                                        // 6:01AM > 12:01AM AND 2025-11-10 6:01AM > 2025-11-10 10:00PM
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_in2' AND dateTimeOut > '$dt_in1_tom' ORDER BY timeOut ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                        if ($check) {
                                            $out2 = $check['timeOut'];
                                            $time_out2 = $check['timeOut'];
                                        }
                                    }


                                    if ($out2 != '') {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn > '$time_out2' AND dateTimeIn < '$dt_in1_tom' ORDER BY timeIn ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                        if ($check) {
                                            $in3 = $check['timeIn'];
                                        }
                                    }

                                    if ($in3 != '') {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_out2' AND timeOut > '$in3' ORDER BY timeOut ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                        if ($check) {
                                            $out3 = $check['timeOut'];
                                        }
                                    }
                                }

                                if ($day_in1 == 'PM' && $day_out1 == 'PM' && $day_in2 == 'PM' && $day_out2 == 'AM') {

                                    $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';

                                    $time_in1       = date("H:i:s", strtotime($s_in1));
                                    $time_out1      = date("H:i:s", strtotime($s_out1));
                                    $time_in2       = date("H:i:s", strtotime($s_in2));
                                    $time_out2      = date("H:i:s", strtotime($s_out2));

                                    $dt_in1         = date("Y-m-d H:i:s", strtotime("$date $s_in1"));
                                    $dt_out1        = date("Y-m-d H:i:s", strtotime("$date $s_out1"));
                                    $dt_in2         = date("Y-m-d H:i:s", strtotime("$date $s_in2"));
                                    $dt_out2        = date("Y-m-d H:i:s", strtotime("$date $s_out2"));

                                    $date_tom       = date("Y-m-d", strtotime("$date +1 day"));
                                    $dt_in1_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in1"));
                                    $dt_out1_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out1"));
                                    $dt_in2_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in2"));
                                    $dt_out2_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out2"));

                                    // shift 9PM - 10PM | 11PM - 12AM
                                    // entry 8:59PM - 10:01PM | 10:59PM - 12:01AM

                                    // 8:59PM < 10:00PM AND 2025-11-09 8:59PM < 2025-11-09 11:00PM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn < '$time_out1' AND dateTimeIn < '$dt_out1' ORDER BY timeIn ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                    if ($check) {
                                        $in1 = $check['timeIn'];
                                        $time_in1 = $check['timeIn'];
                                    }

                                    // 10:01PM < 11PM AND 2025-11-09 10:01PM > 2025-11-09 09:00PM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut < '$time_in2' AND dateTimeOut > '$dt_in1' ORDER BY timeOut ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                    if ($check) {
                                        $out1 = $check['timeOut'];
                                        $time_out1 = $check['timeOut'];
                                    }

                                    // 10:59PM > 10:00PM AND 2025-11-09 10:59PM < 2025-11-10 06:00AM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND dateTimeIn < '$dt_out2_tom' ORDER BY timeIn ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                    if ($check) {
                                        $in2 = $check['timeIn'];
                                        $time_in2 = $check['timeIn'];
                                    }

                                    // if dili undertime
                                    // 6:01AM < 10:59PM AND 2025-11-10 6:01AM < 2025-11-10 10:00PM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut < '$time_in2' AND dateTimeOut < '$dt_in1_tom' ORDER BY timeOut ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                    if ($check) {
                                        $out2 = $check['timeOut'];
                                        $time_out2 = $check['timeOut'];
                                    } else {
                                        // if undertime
                                        // 11:59PM > 10:59PM AND 2025-11-09 11:59PM < 2025-11-10 10:00PM
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_in2' AND dateTimeOut < '$dt_in1_tom' ORDER BY timeOut ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                        if ($check) {
                                            $out2 = $check['timeOut'];
                                            $time_out2 = $check['timeOut'];
                                        }
                                    }

                                    if ($out2 != '') {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn > '$time_out2' AND dateTimeIn < '$dt_in1_tom' ORDER BY timeIn ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                        if ($check) {
                                            $in3 = $check['timeIn'];
                                        }
                                    }

                                    if ($in3 != '') {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_out2' AND timeOut > '$in3' ORDER BY timeOut ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                        if ($check) {
                                            $out3 = $check['timeOut'];
                                        }
                                    }
                                }

                                if ($out1 && $in2) {

                                    $dayEx  = date("D", strtotime($date));
                                    $cond   = ['empId' => $empId, 'status' => 'active', 'day' => $dayEx];
                                    $check  = $this->promo_model->select_rowArray('tk', 'setup_inter_io_exempt', $cond);
                                    if (!$check) {
                                        $sub_in2    = date("H:i:s", strtotime("$in2 -1 minutes"));
                                        $temp_out1  = date("H:i:s", strtotime($out1));

                                        if (strtotime($sub_in2) <= strtotime($temp_out1)) {
                                            $out1 = "";
                                        }
                                    }
                                }

                                $percentage = $percentage + $percent;

                                echo json_encode(array(
                                    'result'    => 'loading',
                                    'msg'       => round($percentage) . '%',
                                    'msg1'      => round($percentage) . '%',
                                    'date'      => "Updating Table (duty/duty_next)",
                                    'table'     => "[Date: $date; biometricId: $id; in1: $in1; out1: $out1; in2: $in2; out2: $out2; in3: $in3; out3: $out3;]",
                                    'exported'  => 'table_update'
                                ));

                                $data = [
                                    'in1'   => $in1,
                                    'out1'  => $out1,
                                    'in2'   => $in2,
                                    'out2'  => $out2,
                                    'in3'   => $in3,
                                    'out3'  => $out3,
                                ];

                                $cond = ['bioMetricId' => $id, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $data_ka = [
                                    'in1_ka'   => date("Y-m-d H:i:s", strtotime("$date $in1")),
                                    'out1_ka'  => date("Y-m-d H:i:s", strtotime("$date $out1")),
                                    'in2_ka'   => date("Y-m-d H:i:s", strtotime("$date $in2")),
                                    'out2_ka'  => date("Y-m-d H:i:s", strtotime("$date $out2")),
                                    'in3_ka'   => date("Y-m-d H:i:s", strtotime("$date $in3")),
                                    'out3_ka'  => date("Y-m-d H:i:s", strtotime("$date $out3")),
                                ];

                                $cond   = ['bioMetricId_ka' => $id, 'dateDuty_ka' => $date];
                                $update = $this->promo_model->update('tk', 'duty_next', $cond, $data_ka);
                            }
                        }
                    } else {

                        $percentage = $percentage + $pdiv1;
                        echo json_encode(array(
                            'result'    => 'loading',
                            'msg'       => round($percentage) . '%',
                            'msg1'      => round($percentage) . '%',
                            'date'      => "Updating Table (duty/duty_next)",
                            'table'     => "[empId: $empId; bioMetricId: $bioM;]",
                            'exported'  => 'table_update'
                        ));
                    }
                } else {
                    $percentage = $percentage + $pdiv1;
                    echo json_encode(array(
                        'result'    => 'loading',
                        'msg'       => round($percentage) . '%',
                        'msg1'      => round($percentage) . '%',
                        'date'      => "Updating Table (duty/duty_next)",
                        'table'     => "[empId: $empId; bioMetricId: $bioM;]",
                        'exported'  => 'table_update'
                    ));
                }
            } else {
                $percentage = $percentage + $pdiv1;
                echo json_encode(array(
                    'result'    => 'loading',
                    'msg'       => round($percentage) . '%',
                    'msg1'      => round($percentage) . '%',
                    'date'      => "Updating Table (duty/duty_next)",
                    'table'     => "[empId: $empId; bioMetricId: $bioM;]",
                    'exported'  => 'table_update'
                ));
            }
        }

        //update perday entries **************************************************************
        echo json_encode(array(
            'result'    => 'loading',
            'msg'       => round(0) . '%',
            'msg1'      => round(0) . '%',
            'date'      => "Preparing perday table...",
            'table'     => "",
            'exported'  => 'table_update',
        ));

        sleep(1);

        $empIds = [];
        $percentage = 0;
        $pdiv1      = 100 / count($csList);
        foreach ($csList as $key => $row) {
            $empId = $row['emp_id'];
            $amsId = $row['amsId'];

            $cond   = "empId='$empId' AND barcodeId NOT LIKE '%\_%' AND bioMetricId NOT LIKE '%\_%' ORDER BY assignId DESC LIMIT 1";
            $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);

            $table = "";
            $allowId = $assign['allowId'];
            if ($allowId == "Biometric ID") {
                $bioM   = $assign['bioMetricId'];
                $table  = "duty_biometric";
            }
            if ($allowId == "Logbox ID") {
                $bioM   = $assign['barcodeId'];
                $table  = "duty_logbox";
            }

            $cond = ['amsId' => $amsId, 'status' => 'Done'];
            $check = $this->promo_model->select_rowArray('tk', 'request_new_shift_calc', $cond);
            if (!$check) {
                if (!in_array($empId, $empIds)) {
                    $empIds[] = $empId;
                    $cond = "bioMetricId='$bioM' AND dateDuty BETWEEN '$dateFrom' AND '$dateTo' GROUP BY bioMetricId, dateDuty ORDER BY dateDuty ASC";
                    $query = $this->promo_model->select_resultArray('tk', $table, $cond);
                    if (count($query)) {

                        $percent = $pdiv1 / count($query);
                        foreach ($query as $key => $row) {

                            $id     = $row['bioMetricId'];
                            $date   = date('Y-m-d', strtotime($row['dateDuty']));

                            $percentage = $percentage + $percent;

                            echo json_encode(array(
                                'result'    => 'loading',
                                'msg'       => round($percentage) . '%',
                                'msg1'      => round($percentage) . '%',
                                'date'      => "Updating Table (perday)",
                                'table'     => "[Date: $date; biometricId: $id;]",
                                'exported'  => 'table_update'
                            ));

                            $cond   = "bioMetricId='$id' OR barcodeId='$id'";
                            $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
                            $empId  = $assign['empId'];
                            if ($assign['shiftCode']) {
                                $shiftCode = $assign['shiftCode'];
                            }

                            $day = date('l', strtotime($row['dateDuty']));

                            $cond   = ['empId' => $empId, 'day_sched' => $day];
                            $pds    = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
                            if ($pds) {
                                $shiftCode = $pds['shiftCode'];
                            }

                            $cond   = "empId='$empId' AND status = 'active' AND status2 = 'Approved' AND '$date' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
                            $ams    = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond);
                            if ($ams) {
                                $shiftCode = $ams['shiftCode'];
                            }

                            $four_entries = false;
                            $cond   = ['shiftCode' => $shiftCode];
                            $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
                            if ($sc) {

                                $night_diff = $sc['night_diff'];

                                $fIn    = date("H:i:s", strtotime($sc['1stIn']));
                                $fOut   = date("H:i:s", strtotime($sc['1stOut']));
                                $sIn    = date("H:i:s", strtotime($sc['2ndIn']));
                                $sOut   = date("H:i:s", strtotime($sc['2ndOut']));

                                $s_in1  = $sc['1stIn'];
                                $s_out1 = $sc['1stOut'];
                                $s_in2  = $sc['2ndIn'];
                                $s_out2 = $sc['2ndOut'];

                                if ($fIn && $fOut && $sIn && $sOut) {
                                    $four_entries = true;
                                }
                            }

                            $cond   = ['shiftCode' => $shiftCode];
                            $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
                            if ($scb) {

                                $four_entries = true;

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

                            $time_in1   = date("H:i:s", strtotime($s_in1));
                            $time_out1  = date("H:i:s", strtotime($s_out1));
                            $time_in2   = date("H:i:s", strtotime($s_in2));
                            $time_out2  = date("H:i:s", strtotime($s_out2));

                            if (true) {

                                $cond   = ['bioMetricId' => $id, 'dateDuty' => $date];
                                $duty   = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                                $dutyId = $duty['dutyId'];
                                $in1    = $duty['in1'];
                                $out1   = $duty['out1'];
                                $in2    = $duty['in2'];
                                $out2   = $duty['out2'];
                                $in3    = $duty['in3'];
                                $out3   = $duty['out3'];

                                $cond   = ['dutyId' => $dutyId];
                                $check  = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                                if (!$check) {
                                    $data = ['dutyId' => $dutyId];
                                    $insert = $this->promo_model->insert('tk', 'perday', $data);
                                }

                                $next_not = false;
                                if ($day_in2 == "AM" && $day_out2 == "AM" && !$scb) {
                                    $next_not = true;
                                }

                                if ($day_out1 == "AM" && $day_in2 == "AM" && $day_out2 == "AM" && !$scb) {
                                    $next_not = true;
                                }

                                $two_four = false;
                                if ($scb) {
                                    if ($day_in1 == "PM" && $day_in2 == "AM" && $day_out2 == "AM") {
                                        $next_not = true;
                                        $two_four = true;
                                    }

                                    if ($day_in1 == "PM" && $day_out1 == "AM" && $day_in2 == "AM") {
                                        $next_not = true;
                                        $two_four = true;
                                    }

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

                                        $min = date("H:i", strtotime("$in1 -$h hours, -$m minutes"));
                                        if ($min <= $maxLate) {
                                            $t      = explode(":", $min);
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

                                        $min = date("H:i", strtotime("$in2 -$h hours, -$m minutes"));
                                        if ($min <= $maxLate) {
                                            $t      = explode(":", $min);
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
                                        $h = $t[0];
                                        $m = $t[1];

                                        $hm = date("H:i", strtotime("$newOut1 -$h hours, -$m minutes"));
                                        $t  = explode(":", $hm);
                                        $h  = $t[0] * 1;
                                        $m  = $t[1] * 1;

                                        $hw = ($m != 0) ? "$h hr(s). & $m min(s)." : "$h hr(s).";
                                    }

                                    //if more than 8 hours
                                    if (!$hw) {
                                        $res = explode("&", $hw);
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

                                        $dw = round($hrs + $min, 3);
                                        $decimal = explode('.', (string)$dw);
                                        if (isset($decimal[1]) && strlen($decimal[1]) > 3) {
                                            $dw = number_format($dw, 3, '.', '');
                                        }

                                        if ($dw > 8) {
                                            $hw = 0;
                                        }

                                        if ($dw > 8.5) {
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
                                        $h  = $t[0] * 1;
                                        $m  = $t[1] * 1;

                                        $ot12 = date("H:i", strtotime("$hi_ot2 +$h hours, +$m minutes"));

                                        //caculate the ot12 + ot3 overtime
                                        $t  = explode(":", $ot12);
                                        $h  = $t[0] * 1;
                                        $m  = $t[1] * 1;

                                        $ot123 = date('H:i', strtotime("$hi_ot3 +$h hours, +$m minutes"));
                                        $t  = explode(":", $ot123);
                                        $h  = $t[0] * 1;
                                        $m  = $t[1] * 1;

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
                    } else {

                        $percentage += $pdiv1;
                        echo json_encode(array(
                            'result'    => 'loading',
                            'msg'       => round($percentage) . '%',
                            'msg1'      => round($percentage) . '%',
                            'date'      => "Updating Table (perday)",
                            'table'     => "[empId: $empId; bioMetricId: $bioM;]",
                            'exported'  => 'table_update'
                        ));
                    }
                } else {
                    $percentage = $percentage + $pdiv1;
                    echo json_encode(array(
                        'result'    => 'loading',
                        'msg'       => round($percentage) . '%',
                        'msg1'      => round($percentage) . '%',
                        'date'      => "Updating Table (perday)",
                        'table'     => "[empId: $empId; bioMetricId: $bioM;]",
                        'exported'  => 'table_update'
                    ));
                }
            } else {
                $percentage += $pdiv1;
                echo json_encode(array(
                    'result'    => 'loading',
                    'msg'       => round($percentage) . '%',
                    'msg1'      => round($percentage) . '%',
                    'date'      => "Updating Table (perday)",
                    'table'     => "[empId: $empId; bioMetricId: $bioM;]",
                    'exported'  => 'table_update'
                ));
            }
        }

        //update perday nigt diff **************************************************************
        echo json_encode(array(
            'result'    => 'loading',
            'msg'       => round(0) . '%',
            'msg1'      => round(0) . '%',
            'date'      => "Calculating night differential...",
            'table'     => "",
            'exported'  => 'table_update',
        ));

        sleep(1);

        $empIds = [];
        $percentage = 0;
        $pdiv1      = 100 / count($csList);
        foreach ($csList as $key => $row) {
            $empId = $row['emp_id'];
            $amsId = $row['amsId'];

            $cond   = "empId='$empId' AND barcodeId NOT LIKE '%\_%' AND bioMetricId NOT LIKE '%\_%' ORDER BY assignId DESC LIMIT 1";
            $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);

            $table = "";
            $allowId = $assign['allowId'];
            if ($allowId == "Biometric ID") {
                $bioM   = $assign['bioMetricId'];
                $table  = "duty_biometric";
            }
            if ($allowId == "Logbox ID") {
                $bioM   = $assign['barcodeId'];
                $table  = "duty_logbox";
            }

            $cond = ['amsId' => $amsId, 'status' => 'Done'];
            $check = $this->promo_model->select_rowArray('tk', 'request_new_shift_calc', $cond);
            if (!$check) {
                if (!in_array($empId, $empIds)) {
                    $empIds[] = $empId;
                    $cond = "bioMetricId='$bioM' AND dateDuty BETWEEN '$dateFrom' AND '$dateTo' GROUP BY bioMetricId, dateDuty ORDER BY dateDuty ASC";
                    $query = $this->promo_model->select_resultArray('tk', $table, $cond);
                    if (count($query)) {

                        $percent = $pdiv1 / count($query);
                        foreach ($query as $key => $row) {

                            $id     = $row['bioMetricId'];
                            $date   = date('Y-m-d', strtotime($row['dateDuty']));

                            $percentage = $percentage + $percent;

                            echo json_encode(array(
                                'result'    => 'loading',
                                'msg'       => round($percentage) . '%',
                                'msg1'      => round($percentage) . '%',
                                'date'      => "Updating Table (perday - night differential)",
                                'table'     => "[Date: $date; biometricId: $id;]",
                                'exported'  => 'table_update'
                            ));

                            $cond   = ['bioMetricId' => $id, 'dateDuty' => $date];
                            $duty   = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                            $dutyId = $duty['dutyId'];
                            $in1    = $duty['in1'];
                            $out1   = $duty['out1'];
                            $in2    = $duty['in2'];
                            $out2   = $duty['out2'];
                            $in3    = $duty['in3'];
                            $out3   = $duty['out3'];

                            $cond   = "bioMetricId='$id' OR barcodeId='$id'";
                            $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
                            $empId  = $assign['empId'];
                            if ($assign['shiftCode']) {
                                $shiftCode = $assign['shiftCode'];
                            }

                            $day = date('l', strtotime($row['dateDuty']));
                            $cond = ['empId' => $empId, 'day_sched' => $day];
                            $pds = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
                            if ($pds) {
                                $shiftCode = $pds['shiftCode'];
                            }

                            $cond = "empId='$empId' AND status = 'active' AND status2 = 'Approved' AND '$date' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
                            $ams = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond);
                            if ($ams) {
                                $shiftCode = $ams['shiftCode'];
                            }

                            $night_diff = '';
                            $four_entries = false;
                            $cond   = ['shiftCode' => $shiftCode];
                            $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
                            if ($sc) {

                                $fIn    = date("H:i:s", strtotime($sc['1stIn']));
                                $fOut   = date("H:i:s", strtotime($sc['1stOut']));
                                $sIn    = date("H:i:s", strtotime($sc['2ndIn']));
                                $sOut   = date("H:i:s", strtotime($sc['2ndOut']));

                                $s_in1  = $sc['1stIn'];
                                $s_out1 = $sc['1stOut'];
                                $s_in2  = $sc['2ndIn'];
                                $s_out2 = $sc['2ndOut'];

                                $night_diff = $sc['night_diff'];

                                if ($fIn && $fOut && $sIn && $sOut) {
                                    $four_entries = true;
                                }
                            }

                            $cond   = ['shiftCode' => $shiftCode];
                            $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
                            if ($scb) {

                                $four_entries = true;

                                $fIn    = date("H:i:s", strtotime($scb['1stIn']));
                                $fOut   = date("H:i:s", strtotime($scb['1stOut']));
                                $sIn    = date("H:i:s", strtotime($scb['2ndIn']));
                                $sOut   = date("H:i:s", strtotime($scb['2ndOut']));

                                $s_in1  = $scb['1stIn'];
                                $s_out1 = $scb['1stOut'];
                                $s_in2  = $scb['2ndIn'];
                                $s_out2 = $scb['2ndOut'];

                                $temp_entries1 = false;
                                $temp_entries2 = false;
                                if (empty($out1)) {
                                    $temp_entries1 = true;
                                    $out1 = date('H:i:s', strtotime($s_out1));
                                }
                                if (empty($in2)) {
                                    $temp_entries2 = true;
                                    $in2 = date('H:i:s', strtotime($s_in2));
                                }
                                if (empty($in1)) {
                                    $out1 = '';
                                }
                                if (empty($out2)) {
                                    $in2 = '';
                                    $out1 = '';
                                }

                                if (empty($in1) && !empty($out2) && $temp_entries1) {
                                    $in2 = '';
                                }
                                if (!empty($in1) && empty($out2) && $temp_entries2) {
                                    $out1 = '';
                                }
                            }

                            $day_in1    = date('A', strtotime($s_in1));
                            $day_out1   = date('A', strtotime($s_out1));
                            $day_in2    = date('A', strtotime($s_in2));
                            $day_out2   = date('A', strtotime($s_out2));

                            $cond = "1 ORDER BY snId";
                            $snd = $this->promo_model->select_rowArray('tk', 'setup_n_d', $cond);
                            $ndStart = $snd['timeStarted']; //10PM
                            $ndEnded = $snd['timeEnded']; //06AM
                            $minTime = $snd['minTime']; //30min

                            $nd_in1 = $nd_out1 = $nd_in2 = $nd_out2 = '00:00';

                            //in1
                            if ($day_in1 == 'PM' && $in1) {

                                if ($in1 >= $ndStart && $in1 <= strtotime('23:59:59')) {
                                    $nd_in1 = ($in1 <= $fIn) ? $fIn : $in1;
                                } else {
                                    $nd_in1 = $ndStart;
                                }
                            }

                            if ($day_in1 == 'AM' && $in1) {

                                if ($in1 <= $ndEnded) {
                                    $nd_in1 = ($in1 <= $fIn) ? $fIn : $in1;
                                } else {
                                    if (date('A', strtotime($in1)) == 'PM') {
                                        $nd_in1 = ($in1 >= $fIn) ? $fIn : $in1;
                                    } else {
                                        $nd_in1 = $ndEnded;
                                    }
                                }
                            }

                            //out1
                            if ($day_out1 == 'PM' && $out1) {

                                if ($out1 >= $ndStart && $out1 <= strtotime('23:59:59')) {

                                    if ($ndStart <= $fOut) {
                                        $nd_out1 = ($out1 <= $fOut) ? $out1 : $fOut;
                                    } else {
                                        $nd_out1 = $ndStart;
                                    }
                                } else {
                                    $nd_out1 = $ndStart;
                                }
                            }

                            if ($day_out1 == 'AM' && $out1) {

                                if ($out1 <= $ndEnded) {
                                    $nd_out1 = ($out1 <= $fOut) ? $out1 : $fOut;
                                } else {
                                    if (date('A', strtotime($out1)) == 'PM') {
                                        $nd_out1 = ($out1 >= $fOut) ? $out1 : $fOut;
                                    } else {
                                        $nd_out1 = $ndEnded;
                                    }
                                }
                            }

                            //in2
                            if ($day_in2 == 'PM' && $in2) {

                                if ($in2 >= $ndStart && $in2 <= strtotime('23:59:59')) {
                                    $nd_in2 = ($in2 <= $sIn) ? $sIn : $in2;
                                } else {
                                    $nd_in2 = $ndStart;
                                }
                            }

                            if ($day_in2 == 'AM' && $in2) {

                                if ($in2 <= $ndEnded) {
                                    $nd_in2 = ($in2 <= $sIn) ? $sIn : $in2;
                                } else {
                                    if (date('A', strtotime($in2)) == 'PM') {
                                        $nd_in2 = ($in2 >= $sIn) ? $sIn : $in2;
                                    } else {
                                        $nd_in2 = $ndEnded;
                                    }
                                }
                            }

                            //out2
                            if ($day_out2 == 'PM' && $out2) {
                                if ($out2 >= $ndStart && $out2 <= strtotime('23:59:59')) {

                                    if ($ndStart <= $sOut) {
                                        $nd_out2 = ($out2 <= $sOut) ? $out2 : $sOut;
                                    } else {
                                        $nd_out2 = $ndStart;
                                    }
                                } else {
                                    $nd_out2 = $ndStart;
                                }
                            }

                            if ($day_out2 == 'AM' && $out2) {

                                if ($out2 <= $ndEnded) {
                                    $nd_out2 = ($out2 <= $sOut) ? $out2 : $sOut;
                                } else {
                                    if (date('A', strtotime($out2)) == 'PM') {
                                        $nd_out2 = ($out2 >= $sOut) ? $out2 : $sOut;
                                    } else {
                                        $nd_out2 = $ndEnded;
                                    }
                                }
                            }

                            //nd
                            $nd = 0;
                            if ($night_diff == 'yes') {

                                $t  = explode(":", $nd_in1);
                                $h  = $t[0];
                                $m  = $t[1];
                                $t1 = date("H:i", strtotime("$nd_out1 -$h hours, -$m minutes"));

                                $t  = explode(":", $nd_in2);
                                $h  = $t[0];
                                $m  = $t[1];
                                $t2 = date("H:i", strtotime("$nd_out2 -$h hours, -$m minutes"));

                                list($h1, $m1) = explode(":", $t1);
                                list($h2, $m2) = explode(":", $t2);

                                $totalMinutes = ($h1 * 60 + $m1) + ($h2 * 60 + $m2);
                                $hh = floor($totalMinutes / 60);
                                $mm = $totalMinutes % 60;

                                $hm = sprintf("%02d:%02d", $hh, $mm);

                                $t  = explode(":", $hm);
                                $h  = $t[0] * 1;
                                $m  = $t[1] * 1;

                                if ($m >= $minTime) {
                                    $m = '30';
                                } else {
                                    $m = '00';
                                }

                                $hrs = $h / 8;
                                $min = ($m / 60) / 8;

                                $min = round(($m / 60), 3);
                                $nd =  round($h + $min, 3);
                                $decimal = explode('.', (string)$h + $min);
                                if (isset($decimal[1]) && strlen($decimal[1]) > 3) {
                                    $nd = number_format($h + $min, 3, '.', '');
                                }

                                $cond = ['dutyId' => $dutyId];
                                $data = ['night_diff' => $nd];
                                $update = $this->promo_model->update('tk', 'perday', $cond, $data);
                            }
                        }
                    } else {

                        $percentage += $pdiv1;
                        echo json_encode(array(
                            'result'    => 'loading',
                            'msg'       => round($percentage) . '%',
                            'msg1'      => round($percentage) . '%',
                            'date'      => "Updating Table (perday - night differential)",
                            'table'     => "[empId: $empId; bioMetricId: $bioM;]",
                            'exported'  => 'table_update'
                        ));
                    }
                } else {
                    $percentage = $percentage + $pdiv1;
                    echo json_encode(array(
                        'result'    => 'loading',
                        'msg'       => round($percentage) . '%',
                        'msg1'      => round($percentage) . '%',
                        'date'      => "Updating Table (perday - night differential)",
                        'table'     => "[empId: $empId; bioMetricId: $bioM;]",
                        'exported'  => 'table_update'
                    ));
                }
            } else {
                $percentage += $pdiv1;
                echo json_encode(array(
                    'result'    => 'loading',
                    'msg'       => round($percentage) . '%',
                    'msg1'      => round($percentage) . '%',
                    'date'      => "Updating Table (perday - night differential)",
                    'table'     => "[empId: $empId; bioMetricId: $bioM;]",
                    'exported'  => 'table_update'
                ));
            }
        }

        //insert request_new_shift_calc **************************************************************
        echo json_encode(array(
            'result'    => 'loading',
            'msg'       => round(0) . '%',
            'msg1'      => round(0) . '%',
            'date'      => "Preparing request_new_shift_calc table...",
            'table'     => "",
            'exported'  => 'table_update',
        ));

        sleep(1);

        $percentage = 0;
        $pdiv1      = 100 / count($csList);
        foreach ($csList as $key => $row) {
            $empId = $row['emp_id'];
            $amsId = $row['amsId'];

            $cond   = "empId='$empId' AND barcodeId NOT LIKE '%\_%' AND bioMetricId NOT LIKE '%\_%' ORDER BY assignId DESC LIMIT 1";
            $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);

            $table = "";
            $allowId = $assign['allowId'];
            if ($allowId == "Biometric ID") {
                $bioM   = $assign['bioMetricId'];
                $table  = "duty_biometric";
            }
            if ($allowId == "Logbox ID") {
                $bioM   = $assign['barcodeId'];
                $table  = "duty_logbox";
            }

            $percentage += $pdiv1;
            echo json_encode(array(
                'result'    => 'loading',
                'msg'       => round($percentage) . '%',
                'msg1'      => round($percentage) . '%',
                'date'      => "Inserting Table (request_new_shift_calc)",
                'table'     => "[empId: $empId; bioMetricId: $bioM; amsId: $amsId;]",
                'exported'  => 'table_update'
            ));

            $cond = ['amsId' => $amsId, 'status' => 'Done'];
            $check = $this->promo_model->select_rowArray('tk', 'request_new_shift_calc', $cond);
            if (!$check) {
                $data = ['amsId' => $amsId, 'status' => 'Done'];
                $insert = $this->promo_model->insert('tk', 'request_new_shift_calc', $data);
            }
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

    public function od_second()
    {
        $input = $this->input->post(NULL, TRUE);
        $statCut = $input['statCut'];
        $data = [];

        $query = $this->promo_model->process_cutoff_table($statCut, '1stcutoff');
        foreach ($query as $row) {

            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));

            $pending = 0;
            $action = '<span class="btn btn-primary btn-xs disabled">Process</span>';
            $od = $this->promo_model->odSecond($row['dateFrom'], $row['dateTo'], $statCut);
            if ($od) {

                $amsIds = array_column($od, 'amsId');
                $amsIdsStr = implode(',', $amsIds);
                $cond = "amsId IN ($amsIdsStr) AND status='Done'";
                $check = $this->promo_model->select_resultArray('tk', 'request_odaf_calc', $cond);
                $pending = count($od) - count($check);
                if ($pending) {
                    $onclick = 'onclick=processODsecond(\'' . $statCut . '\',\'' . $row['dateFrom'] . '\',\'' . $row['dateTo'] . '\')';
                    $action = '<a href="javascript:; ' . $onclick . '"><span class="btn btn-primary btn-xs">Process</span></a>';
                }
            }

            $sub_array = [];
            $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
            $sub_array[] = $dt;
            $sub_array[] = count($od);
            $sub_array[] = '<span class="text-danger">' . $pending . '</span>';
            $sub_array[] = $action;
            $data[] = $sub_array;
        }

        $query = $this->promo_model->process_cutoff_table($statCut, '2ndcutoff');
        foreach ($query as $row) {

            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));

            $pending = 0;
            $action = '<span class="btn btn-primary btn-xs disabled">Process</span>';
            $od = $this->promo_model->odSecond($row['dateFrom'], $row['dateTo'], $statCut);
            if ($od) {

                $amsIds = array_column($od, 'amsId');
                $amsIdsStr = implode(',', $amsIds);
                $cond = "amsId IN ($amsIdsStr) AND status='Done'";
                $check = $this->promo_model->select_resultArray('tk', 'request_odaf_calc', $cond);
                $pending = count($od) - count($check);
                if ($pending) {
                    $onclick = 'onclick=processODsecond(\'' . $statCut . '\',\'' . $row['dateFrom'] . '\',\'' . $row['dateTo'] . '\')';
                    $action = '<a href="javascript:; ' . $onclick . '"><span class="btn btn-primary btn-xs">Process</span></a>';
                }
            }

            $sub_array = [];
            $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
            $sub_array[] = $dt;
            $sub_array[] = count($od);
            $sub_array[] = '<span class="text-danger">' . $pending . '</span>';
            $sub_array[] = $action;
            $data[] = $sub_array;
        }

        echo json_encode(['data' => $data]);
    }

    public function processODsecond()
    {
        $input      = $this->input->post(NULL, TRUE);
        $dateFrom   = $input['df'];
        $dateTo     = $input['dt'];
        $statCut    = $input['statCut'];

        set_time_limit(0);
        ob_implicit_flush(true);
        ob_end_flush();

        echo json_encode(array(
            'result'    => 'loading',
            'msg'       => round(0) . '%',
            'msg1'      => round(0) . '%',
            'date'      => "Preparing list...",
            'table'     => "",
            'exported'  => 'table_update',
        ));

        sleep(2);

        //update duty/duty_next entries **************************************************************
        echo json_encode(array(
            'result'    => 'loading',
            'msg'       => round(0) . '%',
            'msg1'      => round(0) . '%',
            'date'      => "Preparing duty/duty_next table...",
            'table'     => "",
            'exported'  => 'table_update',
        ));

        $odList = $this->promo_model->odSecond($dateFrom, $dateTo, $statCut);

        $empIds = [];
        $percentage = 0;
        $pdiv1 = 25 / count($odList);
        foreach ($odList as $key => $row) {
            $empId = $row['empId'];
            $amsId = $row['amsId'];

            $cond   = "empId='$empId' AND barcodeId NOT LIKE '%\_%' AND bioMetricId NOT LIKE '%\_%' ORDER BY assignId DESC LIMIT 1";
            $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);

            $table = "";
            $allowId = $assign['allowId'];
            if ($allowId == "Biometric ID") {
                $bioM   = $assign['bioMetricId'];
                $table  = "duty_biometric";
            }
            if ($allowId == "Logbox ID") {
                $bioM   = $assign['barcodeId'];
                $table  = "duty_logbox";
            }

            $cond = ['amsId' => $amsId, 'status' => 'Done'];
            $check = $this->promo_model->select_rowArray('tk', 'request_odaf_calc', $cond);
            if (!$check) {
                if (!in_array($empId, $empIds)) {
                    $empIds[] = $empId;
                    $cond = "bioMetricId='$bioM' AND dateDuty BETWEEN '$dateFrom' AND '$dateTo' GROUP BY bioMetricId, dateDuty ORDER BY dateDuty ASC";
                    $query = $this->promo_model->select_resultArray('tk', $table, $cond);
                    if (count($query)) {
                        $percent = $pdiv1 / count($query);
                        foreach ($query as $key => $row) {

                            $id     = $row['bioMetricId'];
                            $date   = date('Y-m-d', strtotime($row['dateDuty']));
                            $cond   = "bioMetricId='$id' OR barcodeId='$id'";
                            $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
                            $empId  = $assign['empId'];
                            if ($assign['shiftCode']) {
                                $shiftCode = $assign['shiftCode'];
                            }

                            $day = date('l', strtotime($row['dateDuty']));

                            $cond   = ['empId' => $empId, 'day_sched' => $day];
                            $pds    = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
                            if ($pds) {
                                $shiftCode = $pds['shiftCode'];
                            }

                            $cond   = "empId='$empId' AND status = 'active' AND status2 = 'Approved' AND '$date' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
                            $ams    = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond);
                            if ($ams) {
                                $shiftCode = $ams['shiftCode'];
                            }

                            $four_entries = false;
                            $cond   = ['shiftCode' => $shiftCode];
                            $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
                            if ($sc) {

                                $fIn    = date("H:i:s", strtotime($sc['1stIn']));
                                $fOut   = date("H:i:s", strtotime($sc['1stOut']));
                                $sIn    = date("H:i:s", strtotime($sc['2ndIn']));
                                $sOut   = date("H:i:s", strtotime($sc['2ndOut']));

                                $s_in1  = $sc['1stIn'];
                                $s_out1 = $sc['1stOut'];
                                $s_in2  = $sc['2ndIn'];
                                $s_out2 = $sc['2ndOut'];

                                if ($fIn && $fOut && $sIn && $sOut) {
                                    $four_entries = true;
                                }
                            }

                            $cond   = ['shiftCode' => $shiftCode];
                            $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
                            if ($scb) {

                                $four_entries = true;

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

                            $day_outH   = date("H", strtotime($s_out2));
                            $day_outM   = date("i", strtotime($s_out2));

                            if ($four_entries) {

                                if (($day_in1 == 'AM' && $day_out2 == 'PM') || ($day_in1 == 'AM' && $day_out2 == 'AM') || ($day_in1 == 'PM' && $day_out2 == 'PM')) {

                                    $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';

                                    $time_in1   = date("H:i:s", strtotime($s_in1));
                                    $time_out1  = date("H:i:s", strtotime($s_out1));
                                    $time_in2   = date("H:i:s", strtotime($s_in2));
                                    $time_out2  = date("H:i:s", strtotime($s_out2));

                                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn < '$time_out1' ORDER BY timeIn ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                    if ($check) {
                                        $in1 = $check['timeIn'];
                                        $time_in1 = $check['timeIn'];
                                    } else {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn < '$time_out1' ORDER BY timeIn ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                        if ($check) {
                                            $in1 = $check['timeIn'];
                                            $time_in1 = $check['timeIn'];
                                        }
                                    }

                                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut < '$time_in2' AND timeOut > '$time_in1' ORDER BY timeOut ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                    if ($check) {
                                        $out1 = $check['timeOut'];
                                        $time_out1 = $check['timeOut'];
                                    } else {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut < '$time_in2' AND timeOut > '$time_in1' ORDER BY timeOut ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                        if ($check) {
                                            $out1 = $check['timeOut'];
                                            $time_out1 = $check['timeOut'];
                                        }
                                    }

                                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND timeIn < '$time_out2' ORDER BY timeIn ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                    if ($check) {
                                        $in2 = $check['timeIn'];
                                        $time_in2 = $check['timeIn'];
                                    } else {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND timeIn < '$time_out2' ORDER BY timeIn ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                        if ($check) {
                                            $in2 = $check['timeIn'];
                                            $time_in2 = $check['timeIn'];
                                        }
                                    }

                                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_in2' ORDER BY timeOut ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                    if ($check) {
                                        $out2 = $check['timeOut'];
                                        $time_out2 = $check['timeOut'];

                                        if ($time_out2 < "$sOut:00") {
                                            $time_out2 = "$sOut:00";
                                        }
                                    } else {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_in2' ORDER BY timeOut ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                        if ($check) {
                                            $out2 = $check['timeOut'];
                                            $time_out2 = $check['timeOut'];

                                            if ($time_out2 < "$sOut:00") {
                                                $time_out2 = "$sOut:00";
                                            }
                                        } else {
                                            $tomorrow = date("Y-m-d", strtotime("$date +1 day"));
                                            $cond   = "bioMetricId='$id' AND dateDuty='$tomorrow' AND mode='O' AND timeOut < '$time_in1' ORDER BY timeOut ASC";
                                            $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                            if ($check) {
                                                $out2 = $check['timeOut'];
                                            }
                                        }
                                    }

                                    if ($out2 != '') {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out2' AND timeIn > '$out2' ORDER BY timeIn ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                        if ($check) {
                                            $in3 = $check['timeIn'];
                                        } else {
                                            $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out2' AND timeIn > '$out2' ORDER BY timeIn ASC";
                                            $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                            if ($check) {
                                                $in3 = $check['timeIn'];
                                            }
                                        }
                                    }

                                    if ($in3 != '') {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_out2' AND timeOut > '$in3' ORDER BY timeOut ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                        if ($check) {
                                            $out3 = $check['timeOut'];
                                        } else {
                                            $tomorrow = date("Y-m-d", strtotime("$date +1 day"));
                                            $to_day = date("Y-m-d H:i:s", strtotime("$date $in3"));
                                            $cond   = "bioMetricId='$id' AND dateDuty='$tomorrow' AND mode='O' AND timeOut < '$fIn' AND dateTimeOut > '$to_day' ORDER BY timeOut ASC";
                                            $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                            if ($check) {
                                                $out3 = $check['timeOut'];
                                            }
                                        }
                                    }
                                }

                                if ($day_in1 == 'PM' && $day_out1 == 'AM' && $day_in2 == 'AM' && $day_out2 == 'AM') {

                                    $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';

                                    $time_in1   = date("H:i:s", strtotime($s_in1));
                                    $time_out1  = date("H:i:s", strtotime($s_out1));
                                    $time_in2   = date("H:i:s", strtotime($s_in2));
                                    $time_out2  = date("H:i:s", strtotime($s_out2));

                                    $dt_in1     = date("Y-m-d H:i:s", strtotime("$date $s_in1"));
                                    $dt_out1    = date("Y-m-d H:i:s", strtotime("$date $s_out1"));
                                    $dt_in2     = date("Y-m-d H:i:s", strtotime("$date $s_in2"));
                                    $dt_out2    = date("Y-m-d H:i:s", strtotime("$date $s_out2"));

                                    $date_tom       = date("Y-m-d", strtotime("$date +1 day"));
                                    $dt_in1_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in1"));
                                    $dt_out1_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out1"));
                                    $dt_in2_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in2"));
                                    $dt_out2_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out2"));

                                    // shift 10PM - 2AM | 3AM - 6AM
                                    // entry 9:59PM - 2:01AM | 2:59AM - 6:01AM

                                    // 9:59PM > 2:00AM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND dateTimeIn > '$dt_out1' ORDER BY timeIn ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                    if ($check) {
                                        $in1 = $check['timeIn'];
                                        $time_in1 = $check['timeIn'];
                                    }

                                    // 2:01AM < 3AM AND 2025-11-10 2:01AM > 2025-11-09 10:00PM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut < '$time_in2' AND dateTimeOut > '$dt_in1' ORDER BY timeOut ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                    if ($check) {
                                        $out1 = $check['timeOut'];
                                        $time_out1 = $check['timeOut'];
                                    }

                                    // 2:59AM > 2:01AM AND 2025-11-10 2:59AM < 2025-11-10 06:00AM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn > '$time_out1' AND dateTimeIn < '$dt_out2_tom' ORDER BY timeIn ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                    if ($check) {
                                        $in2 = $check['timeIn'];
                                        $time_in2 = $check['timeIn'];
                                    }

                                    // 6:01AM > 2:59AM AND 2025-11-10 6:01AM < 2025-11-10 10:00PM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_in2' AND dateTimeOut < '$dt_in1_tom' ORDER BY timeOut ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                    if ($check) {
                                        $out2 = $check['timeOut'];
                                        $time_out2 = $check['timeOut'];
                                    }

                                    if ($out2 != '') {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn > '$time_out2' AND dateTimeIn < '$dt_in1_tom' ORDER BY timeIn ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                        if ($check) {
                                            $in3 = $check['timeIn'];
                                        }
                                    }

                                    if ($in3 != '') {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_out2' AND timeOut > '$in3' ORDER BY timeOut ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                        if ($check) {
                                            $out3 = $check['timeOut'];
                                        }
                                    }
                                }

                                if ($day_in1 == 'PM' && $day_out1 == 'PM' && $day_in2 == 'AM' && $day_out2 == 'AM') {

                                    $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';

                                    $time_in1   = date("H:i:s", strtotime($s_in1));
                                    $time_out1  = date("H:i:s", strtotime($s_out1));
                                    $time_in2   = date("H:i:s", strtotime($s_in2));
                                    $time_out2  = date("H:i:s", strtotime($s_out2));

                                    $dt_in1     = date("Y-m-d H:i:s", strtotime("$date $s_in1"));
                                    $dt_out1    = date("Y-m-d H:i:s", strtotime("$date $s_out1"));
                                    $dt_in2     = date("Y-m-d H:i:s", strtotime("$date $s_in2"));
                                    $dt_out2    = date("Y-m-d H:i:s", strtotime("$date $s_out2"));

                                    $date_tom       = date("Y-m-d", strtotime("$date +1 day"));
                                    $dt_in1_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in1"));
                                    $dt_out1_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out1"));
                                    $dt_in2_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in2"));
                                    $dt_out2_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out2"));

                                    // shift 10PM - 11PM | 12AM - 6AM
                                    // entry 9:59PM - 11:01PM | 11:59PM - 6:01AM

                                    // 9:59PM < 11:00PM AND 2025-11-09 9:59PM < 2025-11-09 11:00PM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn < '$time_out1' AND dateTimeIn < '$dt_out1' ORDER BY timeIn ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                    if ($check) {
                                        $in1 = $check['timeIn'];
                                        $time_in1 = $check['timeIn'];
                                    }

                                    // 11:01PM > 12AM AND 2025-11-09 11:01PM > 2025-11-09 10:00PM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_in2' AND dateTimeOut > '$dt_in1' ORDER BY timeOut ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                    if ($check) {
                                        $out1 = $check['timeOut'];
                                        $time_out1 = $check['timeOut'];
                                    }

                                    // if dili late 
                                    // 11:59PM > 11:00PM AND 2025-11-09 11:59PM < 2025-11-10 06:00AM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND dateTimeIn < '$dt_out2_tom' ORDER BY timeIn ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                    if ($check) {
                                        $in2 = $check['timeIn'];
                                        $time_in2 = $check['timeIn'];
                                    } else {
                                        // if late 
                                        // 12:01AM < 11:00PM AND 2025-11-10 12:01AM < 2025-11-10 06:00AM
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn < '$time_out1' AND dateTimeIn < '$dt_out2_tom' ORDER BY timeIn ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                        if ($check) {
                                            $in2 = $check['timeIn'];
                                            $time_in2 = $check['timeIn'];
                                        }
                                    }

                                    // if dili late 
                                    // 6:01AM < 11:59PM AND 2025-11-10 6:01AM < 2025-11-10 10:00PM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut < '$time_in2' AND dateTimeOut < '$dt_in1_tom' ORDER BY timeOut ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                    if ($check) {
                                        $out2 = $check['timeOut'];
                                        $time_out2 = $check['timeOut'];
                                    } else {
                                        // if late 
                                        // 6:01AM > 12:01AM AND 2025-11-10 6:01AM > 2025-11-10 10:00PM
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_in2' AND dateTimeOut > '$dt_in1_tom' ORDER BY timeOut ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                        if ($check) {
                                            $out2 = $check['timeOut'];
                                            $time_out2 = $check['timeOut'];
                                        }
                                    }


                                    if ($out2 != '') {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn > '$time_out2' AND dateTimeIn < '$dt_in1_tom' ORDER BY timeIn ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                        if ($check) {
                                            $in3 = $check['timeIn'];
                                        }
                                    }

                                    if ($in3 != '') {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_out2' AND timeOut > '$in3' ORDER BY timeOut ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                        if ($check) {
                                            $out3 = $check['timeOut'];
                                        }
                                    }
                                }

                                if ($day_in1 == 'PM' && $day_out1 == 'PM' && $day_in2 == 'PM' && $day_out2 == 'AM') {

                                    $in1 = $out1 = $in2 = $out2 = $in3 = $out3 = '';

                                    $time_in1       = date("H:i:s", strtotime($s_in1));
                                    $time_out1      = date("H:i:s", strtotime($s_out1));
                                    $time_in2       = date("H:i:s", strtotime($s_in2));
                                    $time_out2      = date("H:i:s", strtotime($s_out2));

                                    $dt_in1         = date("Y-m-d H:i:s", strtotime("$date $s_in1"));
                                    $dt_out1        = date("Y-m-d H:i:s", strtotime("$date $s_out1"));
                                    $dt_in2         = date("Y-m-d H:i:s", strtotime("$date $s_in2"));
                                    $dt_out2        = date("Y-m-d H:i:s", strtotime("$date $s_out2"));

                                    $date_tom       = date("Y-m-d", strtotime("$date +1 day"));
                                    $dt_in1_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in1"));
                                    $dt_out1_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out1"));
                                    $dt_in2_tom     = date("Y-m-d H:i:s", strtotime("$date_tom $s_in2"));
                                    $dt_out2_tom    = date("Y-m-d H:i:s", strtotime("$date_tom $s_out2"));

                                    // shift 9PM - 10PM | 11PM - 12AM
                                    // entry 8:59PM - 10:01PM | 10:59PM - 12:01AM

                                    // 8:59PM < 10:00PM AND 2025-11-09 8:59PM < 2025-11-09 11:00PM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn < '$time_out1' AND dateTimeIn < '$dt_out1' ORDER BY timeIn ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                    if ($check) {
                                        $in1 = $check['timeIn'];
                                        $time_in1 = $check['timeIn'];
                                    }

                                    // 10:01PM < 11PM AND 2025-11-09 10:01PM > 2025-11-09 09:00PM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut < '$time_in2' AND dateTimeOut > '$dt_in1' ORDER BY timeOut ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                    if ($check) {
                                        $out1 = $check['timeOut'];
                                        $time_out1 = $check['timeOut'];
                                    }

                                    // 10:59PM > 10:00PM AND 2025-11-09 10:59PM < 2025-11-10 06:00AM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='I' AND timeIn > '$time_out1' AND dateTimeIn < '$dt_out2_tom' ORDER BY timeIn ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                    if ($check) {
                                        $in2 = $check['timeIn'];
                                        $time_in2 = $check['timeIn'];
                                    }

                                    // if dili undertime
                                    // 6:01AM < 10:59PM AND 2025-11-10 6:01AM < 2025-11-10 10:00PM
                                    $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut < '$time_in2' AND dateTimeOut < '$dt_in1_tom' ORDER BY timeOut ASC";
                                    $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                    if ($check) {
                                        $out2 = $check['timeOut'];
                                        $time_out2 = $check['timeOut'];
                                    } else {
                                        // if undertime
                                        // 11:59PM > 10:59PM AND 2025-11-09 11:59PM < 2025-11-10 10:00PM
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date' AND mode='O' AND timeOut > '$time_in2' AND dateTimeOut < '$dt_in1_tom' ORDER BY timeOut ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                        if ($check) {
                                            $out2 = $check['timeOut'];
                                            $time_out2 = $check['timeOut'];
                                        }
                                    }

                                    if ($out2 != '') {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='I' AND timeIn > '$time_out2' AND dateTimeIn < '$dt_in1_tom' ORDER BY timeIn ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', 'duty_logbox', $cond);
                                        if ($check) {
                                            $in3 = $check['timeIn'];
                                        }
                                    }

                                    if ($in3 != '') {
                                        $cond   = "bioMetricId='$id' AND dateDuty='$date_tom' AND mode='O' AND timeOut > '$time_out2' AND timeOut > '$in3' ORDER BY timeOut ASC";
                                        $check  = $this->promo_model->select_rowArray('tk', $table, $cond);
                                        if ($check) {
                                            $out3 = $check['timeOut'];
                                        }
                                    }
                                }

                                if ($out1 && $in2) {

                                    $dayEx  = date("D", strtotime($date));
                                    $cond   = ['empId' => $empId, 'status' => 'active', 'day' => $dayEx];
                                    $check  = $this->promo_model->select_rowArray('tk', 'setup_inter_io_exempt', $cond);
                                    if (!$check) {
                                        $sub_in2    = date("H:i:s", strtotime("$in2 -1 minutes"));
                                        $temp_out1  = date("H:i:s", strtotime($out1));

                                        if (strtotime($sub_in2) <= strtotime($temp_out1)) {
                                            $out1 = "";
                                        }
                                    }
                                }


                                $percentage = $percentage + $percent;

                                echo json_encode(array(
                                    'result'    => 'loading',
                                    'msg'       => round($percentage) . '%',
                                    'msg1'      => round($percentage) . '%',
                                    'date'      => "Updating Table (duty/duty_next)",
                                    'table'     => "[Date: $date; biometricId: $id; in1: $in1; out1: $out1; in2: $in2; out2: $out2; in3: $in3; out3: $out3;]",
                                    'exported'  => 'table_update'
                                ));

                                $data = [
                                    'in1'   => $in1,
                                    'out1'  => $out1,
                                    'in2'   => $in2,
                                    'out2'  => $out2,
                                    'in3'   => $in3,
                                    'out3'  => $out3,
                                ];

                                $cond = ['bioMetricId' => $id, 'dateDuty' => $date];
                                $update = $this->promo_model->update('tk', 'duty', $cond, $data);

                                $data_ka = [
                                    'in1_ka'   => date("Y-m-d H:i:s", strtotime("$date $in1")),
                                    'out1_ka'  => date("Y-m-d H:i:s", strtotime("$date $out1")),
                                    'in2_ka'   => date("Y-m-d H:i:s", strtotime("$date $in2")),
                                    'out2_ka'  => date("Y-m-d H:i:s", strtotime("$date $out2")),
                                    'in3_ka'   => date("Y-m-d H:i:s", strtotime("$date $in3")),
                                    'out3_ka'  => date("Y-m-d H:i:s", strtotime("$date $out3")),
                                ];

                                $cond   = ['bioMetricId_ka' => $id, 'dateDuty_ka' => $date];
                                $update = $this->promo_model->update('tk', 'duty_next', $cond, $data_ka);
                            }
                        }
                    } else {

                        $percentage = $percentage + $pdiv1;
                        echo json_encode(array(
                            'result'    => 'loading',
                            'msg'       => round($percentage) . '%',
                            'msg1'      => round($percentage) . '%',
                            'date'      => "Updating Table (duty/duty_next)",
                            'table'     => "[empId: $empId; bioMetricId: $bioM;]",
                            'exported'  => 'table_update'
                        ));
                    }
                } else {

                    $percentage = $percentage + $pdiv1;
                    echo json_encode(array(
                        'result'    => 'loading',
                        'msg'       => round($percentage) . '%',
                        'msg1'      => round($percentage) . '%',
                        'date'      => "Updating Table (duty/duty_next)",
                        'table'     => "[empId: $empId; bioMetricId: $bioM;]",
                        'exported'  => 'table_update'
                    ));
                }
            } else {
                $percentage = $percentage + $pdiv1;
                echo json_encode(array(
                    'result'    => 'loading',
                    'msg'       => round($percentage) . '%',
                    'msg1'      => round($percentage) . '%',
                    'date'      => "Updating Table (duty/duty_next)",
                    'table'     => "[empId: $empId; bioMetricId: $bioM;]",
                    'exported'  => 'table_update'
                ));
            }
        }

        //update perday entries **************************************************************
        echo json_encode(array(
            'result'    => 'loading',
            'msg'       => round($percentage) . '%',
            'msg1'      => round($percentage) . '%',
            'date'      => "Preparing perday table...",
            'table'     => "",
            'exported'  => 'table_update',
        ));

        sleep(1);

        $empIds = [];
        // $percentage = 0;
        $pdiv1 = 25 / count($odList);
        foreach ($odList as $key => $row) {
            $empId = $row['empId'];
            $amsId = $row['amsId'];

            $cond   = "empId='$empId' AND barcodeId NOT LIKE '%\_%' AND bioMetricId NOT LIKE '%\_%' ORDER BY assignId DESC LIMIT 1";
            $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);

            $table = "";
            $allowId = $assign['allowId'];
            if ($allowId == "Biometric ID") {
                $bioM   = $assign['bioMetricId'];
                $table  = "duty_biometric";
            }
            if ($allowId == "Logbox ID") {
                $bioM   = $assign['barcodeId'];
                $table  = "duty_logbox";
            }

            $cond = ['amsId' => $amsId, 'status' => 'Done'];
            $check = $this->promo_model->select_rowArray('tk', 'request_odaf_calc', $cond);
            if (!$check) {
                if (!in_array($empId, $empIds)) {
                    $empIds[] = $empId;
                    $cond = "bioMetricId='$bioM' AND dateDuty BETWEEN '$dateFrom' AND '$dateTo' GROUP BY bioMetricId, dateDuty ORDER BY dateDuty ASC";
                    $query = $this->promo_model->select_resultArray('tk', $table, $cond);
                    if (count($query)) {

                        $percent = $pdiv1 / count($query);
                        foreach ($query as $key => $row) {

                            $id     = $row['bioMetricId'];
                            $date   = date('Y-m-d', strtotime($row['dateDuty']));

                            $percentage = $percentage + $percent;

                            echo json_encode(array(
                                'result'    => 'loading',
                                'msg'       => round($percentage) . '%',
                                'msg1'      => round($percentage) . '%',
                                'date'      => "Updating Table (perday)",
                                'table'     => "[Date: $date; biometricId: $id;]",
                                'exported'  => 'table_update'
                            ));

                            $cond   = "bioMetricId='$id' OR barcodeId='$id'";
                            $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
                            $empId  = $assign['empId'];
                            if ($assign['shiftCode']) {
                                $shiftCode = $assign['shiftCode'];
                            }

                            $day = date('l', strtotime($row['dateDuty']));

                            $cond   = ['empId' => $empId, 'day_sched' => $day];
                            $pds    = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
                            if ($pds) {
                                $shiftCode = $pds['shiftCode'];
                            }

                            $cond   = "empId='$empId' AND status = 'active' AND status2 = 'Approved' AND '$date' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
                            $ams    = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond);
                            if ($ams) {
                                $shiftCode = $ams['shiftCode'];
                            }

                            $four_entries = false;
                            $cond   = ['shiftCode' => $shiftCode];
                            $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
                            if ($sc) {

                                $night_diff = $sc['night_diff'];

                                $fIn    = date("H:i:s", strtotime($sc['1stIn']));
                                $fOut   = date("H:i:s", strtotime($sc['1stOut']));
                                $sIn    = date("H:i:s", strtotime($sc['2ndIn']));
                                $sOut   = date("H:i:s", strtotime($sc['2ndOut']));

                                $s_in1  = $sc['1stIn'];
                                $s_out1 = $sc['1stOut'];
                                $s_in2  = $sc['2ndIn'];
                                $s_out2 = $sc['2ndOut'];

                                if ($fIn && $fOut && $sIn && $sOut) {
                                    $four_entries = true;
                                }
                            }

                            $cond   = ['shiftCode' => $shiftCode];
                            $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
                            if ($scb) {

                                $four_entries = true;

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

                            $time_in1   = date("H:i:s", strtotime($s_in1));
                            $time_out1  = date("H:i:s", strtotime($s_out1));
                            $time_in2   = date("H:i:s", strtotime($s_in2));
                            $time_out2  = date("H:i:s", strtotime($s_out2));

                            if (true) {

                                $cond   = ['bioMetricId' => $id, 'dateDuty' => $date];
                                $duty   = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                                $dutyId = $duty['dutyId'];
                                $in1    = $duty['in1'];
                                $out1   = $duty['out1'];
                                $in2    = $duty['in2'];
                                $out2   = $duty['out2'];
                                $in3    = $duty['in3'];
                                $out3   = $duty['out3'];

                                $cond   = ['dutyId' => $dutyId];
                                $check  = $this->promo_model->select_rowArray('tk', 'perday', $cond);
                                if (!$check) {
                                    $data = ['dutyId' => $dutyId];
                                    $insert = $this->promo_model->insert('tk', 'perday', $data);
                                }

                                $next_not = false;
                                if ($day_in2 == "AM" && $day_out2 == "AM" && !$scb) {
                                    $next_not = true;
                                }

                                if ($day_out1 == "AM" && $day_in2 == "AM" && $day_out2 == "AM" && !$scb) {
                                    $next_not = true;
                                }

                                $two_four = false;
                                if ($scb) {
                                    if ($day_in1 == "PM" && $day_in2 == "AM" && $day_out2 == "AM") {
                                        $next_not = true;
                                        $two_four = true;
                                    }

                                    if ($day_in1 == "PM" && $day_out1 == "AM" && $day_in2 == "AM") {
                                        $next_not = true;
                                        $two_four = true;
                                    }

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

                                        $min = date("H:i", strtotime("$in1 -$h hours, -$m minutes"));
                                        if ($min <= $maxLate) {
                                            $t      = explode(":", $min);
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

                                        $min = date("H:i", strtotime("$in2 -$h hours, -$m minutes"));
                                        if ($min <= $maxLate) {
                                            $t      = explode(":", $min);
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
                                        $h = $t[0];
                                        $m = $t[1];

                                        $hm = date("H:i", strtotime("$newOut1 -$h hours, -$m minutes"));
                                        $t  = explode(":", $hm);
                                        $h  = $t[0] * 1;
                                        $m  = $t[1] * 1;

                                        $hw = ($m != 0) ? "$h hr(s). & $m min(s)." : "$h hr(s).";
                                    }

                                    //if more than 8 hours
                                    if (!$hw) {
                                        $res = explode("&", $hw);
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

                                        $dw = round($hrs + $min, 3);
                                        $decimal = explode('.', (string)$dw);
                                        if (isset($decimal[1]) && strlen($decimal[1]) > 3) {
                                            $dw = number_format($dw, 3, '.', '');
                                        }

                                        if ($dw > 8) {
                                            $hw = 0;
                                        }

                                        if ($dw > 8.5) {
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
                                        $h  = $t[0] * 1;
                                        $m  = $t[1] * 1;

                                        $ot12 = date("H:i", strtotime("$hi_ot2 +$h hours, +$m minutes"));

                                        //caculate the ot12 + ot3 overtime
                                        $t  = explode(":", $ot12);
                                        $h  = $t[0] * 1;
                                        $m  = $t[1] * 1;

                                        $ot123 = date('H:i', strtotime("$hi_ot3 +$h hours, +$m minutes"));
                                        $t  = explode(":", $ot123);
                                        $h  = $t[0] * 1;
                                        $m  = $t[1] * 1;

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
                    } else {

                        $percentage += $pdiv1;
                        echo json_encode(array(
                            'result'    => 'loading',
                            'msg'       => round($percentage) . '%',
                            'msg1'      => round($percentage) . '%',
                            'date'      => "Updating Table (perday)",
                            'table'     => "[empId: $empId; bioMetricId: $bioM;]",
                            'exported'  => 'table_update'
                        ));
                    }
                } else {
                    $percentage = $percentage + $pdiv1;
                    echo json_encode(array(
                        'result'    => 'loading',
                        'msg'       => round($percentage) . '%',
                        'msg1'      => round($percentage) . '%',
                        'date'      => "Updating Table (perday)",
                        'table'     => "[empId: $empId; bioMetricId: $bioM;]",
                        'exported'  => 'table_update'
                    ));
                }
            } else {
                $percentage += $pdiv1;
                echo json_encode(array(
                    'result'    => 'loading',
                    'msg'       => round($percentage) . '%',
                    'msg1'      => round($percentage) . '%',
                    'date'      => "Updating Table (perday)",
                    'table'     => "[empId: $empId; bioMetricId: $bioM;]",
                    'exported'  => 'table_update'
                ));
            }
        }

        //update perday nigt diff **************************************************************
        echo json_encode(array(
            'result'    => 'loading',
            'msg'       => round($percentage) . '%',
            'msg1'      => round($percentage) . '%',
            'date'      => "Calculating night differential...",
            'table'     => "",
            'exported'  => 'table_update',
        ));

        sleep(1);

        $empIds = [];
        // $percentage = 0;
        $pdiv1 = 25 / count($odList);
        foreach ($odList as $key => $row) {
            $empId = $row['empId'];
            $amsId = $row['amsId'];

            $cond   = "empId='$empId' AND barcodeId NOT LIKE '%\_%' AND bioMetricId NOT LIKE '%\_%' ORDER BY assignId DESC LIMIT 1";
            $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);

            $table = "";
            $allowId = $assign['allowId'];
            if ($allowId == "Biometric ID") {
                $bioM   = $assign['bioMetricId'];
                $table  = "duty_biometric";
            }
            if ($allowId == "Logbox ID") {
                $bioM   = $assign['barcodeId'];
                $table  = "duty_logbox";
            }

            $cond = ['amsId' => $amsId, 'status' => 'Done'];
            $check = $this->promo_model->select_rowArray('tk', 'request_odaf_calc', $cond);
            if (!$check) {
                if (!in_array($empId, $empIds)) {
                    $empIds[] = $empId;
                    $cond = "bioMetricId='$bioM' AND dateDuty BETWEEN '$dateFrom' AND '$dateTo' GROUP BY bioMetricId, dateDuty ORDER BY dateDuty ASC";
                    $query = $this->promo_model->select_resultArray('tk', $table, $cond);
                    if (count($query)) {

                        $percent = $pdiv1 / count($query);
                        foreach ($query as $key => $row) {

                            $id     = $row['bioMetricId'];
                            $date   = date('Y-m-d', strtotime($row['dateDuty']));

                            $cond   = ['bioMetricId' => $id, 'dateDuty' => $date];
                            $duty   = $this->promo_model->select_rowArray('tk', 'duty', $cond);
                            $dutyId = $duty['dutyId'];
                            $in1    = $duty['in1'];
                            $out1   = $duty['out1'];
                            $in2    = $duty['in2'];
                            $out2   = $duty['out2'];
                            $in3    = $duty['in3'];
                            $out3   = $duty['out3'];

                            $cond   = "bioMetricId='$id' OR barcodeId='$id'";
                            $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);
                            $empId  = $assign['empId'];
                            if ($assign['shiftCode']) {
                                $shiftCode = $assign['shiftCode'];
                            }

                            $day = date('l', strtotime($row['dateDuty']));
                            $cond = ['empId' => $empId, 'day_sched' => $day];
                            $pds = $this->promo_model->select_rowArray('tk', 'promo_def_sched', $cond);
                            if ($pds) {
                                $shiftCode = $pds['shiftCode'];
                            }

                            $cond = "empId='$empId' AND status = 'active' AND status2 = 'Approved' AND '$date' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
                            $ams = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond);
                            if ($ams) {
                                $shiftCode = $ams['shiftCode'];
                            }

                            $night_diff = '';
                            $four_entries = false;
                            $cond   = ['shiftCode' => $shiftCode];
                            $sc     = $this->promo_model->select_rowArray('tk', 'shiftcodes', $cond);
                            if ($sc) {

                                $fIn    = date("H:i:s", strtotime($sc['1stIn']));
                                $fOut   = date("H:i:s", strtotime($sc['1stOut']));
                                $sIn    = date("H:i:s", strtotime($sc['2ndIn']));
                                $sOut   = date("H:i:s", strtotime($sc['2ndOut']));

                                $s_in1  = $sc['1stIn'];
                                $s_out1 = $sc['1stOut'];
                                $s_in2  = $sc['2ndIn'];
                                $s_out2 = $sc['2ndOut'];

                                $night_diff = $sc['night_diff'];

                                if ($fIn && $fOut && $sIn && $sOut) {
                                    $four_entries = true;
                                }
                            }

                            $cond   = ['shiftCode' => $shiftCode];
                            $scb    = $this->promo_model->select_rowArray('tk', 'shiftcodes_break', $cond);
                            if ($scb) {

                                $four_entries = true;

                                $fIn    = date("H:i:s", strtotime($scb['1stIn']));
                                $fOut   = date("H:i:s", strtotime($scb['1stOut']));
                                $sIn    = date("H:i:s", strtotime($scb['2ndIn']));
                                $sOut   = date("H:i:s", strtotime($scb['2ndOut']));

                                $s_in1  = $scb['1stIn'];
                                $s_out1 = $scb['1stOut'];
                                $s_in2  = $scb['2ndIn'];
                                $s_out2 = $scb['2ndOut'];

                                $temp_entries1 = false;
                                $temp_entries2 = false;
                                if (empty($out1)) {
                                    $temp_entries1 = true;
                                    $out1 = date('H:i:s', strtotime($s_out1));
                                }
                                if (empty($in2)) {
                                    $temp_entries2 = true;
                                    $in2 = date('H:i:s', strtotime($s_in2));
                                }
                                if (empty($in1)) {
                                    $out1 = '';
                                }
                                if (empty($out2)) {
                                    $in2 = '';
                                    $out1 = '';
                                }

                                if (empty($in1) && !empty($out2) && $temp_entries1) {
                                    $in2 = '';
                                }
                                if (!empty($in1) && empty($out2) && $temp_entries2) {
                                    $out1 = '';
                                }
                            }

                            $day_in1    = date('A', strtotime($s_in1));
                            $day_out1   = date('A', strtotime($s_out1));
                            $day_in2    = date('A', strtotime($s_in2));
                            $day_out2   = date('A', strtotime($s_out2));

                            $cond = "1 ORDER BY snId";
                            $snd = $this->promo_model->select_rowArray('tk', 'setup_n_d', $cond);
                            $ndStart = $snd['timeStarted']; //10PM
                            $ndEnded = $snd['timeEnded']; //06AM
                            $minTime = $snd['minTime']; //30min

                            $nd_in1 = $nd_out1 = $nd_in2 = $nd_out2 = '00:00';

                            //in1
                            if ($day_in1 == 'PM' && $in1) {

                                if ($in1 >= $ndStart && $in1 <= strtotime('23:59:59')) {
                                    $nd_in1 = ($in1 <= $fIn) ? $fIn : $in1;
                                } else {
                                    $nd_in1 = $ndStart;
                                }
                            }

                            if ($day_in1 == 'AM' && $in1) {

                                if ($in1 <= $ndEnded) {
                                    $nd_in1 = ($in1 <= $fIn) ? $fIn : $in1;
                                } else {
                                    if (date('A', strtotime($in1)) == 'PM') {
                                        $nd_in1 = ($in1 >= $fIn) ? $fIn : $in1;
                                    } else {
                                        $nd_in1 = $ndEnded;
                                    }
                                }
                            }

                            //out1
                            if ($day_out1 == 'PM' && $out1) {

                                if ($out1 >= $ndStart && $out1 <= strtotime('23:59:59')) {

                                    if ($ndStart <= $fOut) {
                                        $nd_out1 = ($out1 <= $fOut) ? $out1 : $fOut;
                                    } else {
                                        $nd_out1 = $ndStart;
                                    }
                                } else {
                                    $nd_out1 = $ndStart;
                                }
                            }

                            if ($day_out1 == 'AM' && $out1) {

                                if ($out1 <= $ndEnded) {
                                    $nd_out1 = ($out1 <= $fOut) ? $out1 : $fOut;
                                } else {
                                    if (date('A', strtotime($out1)) == 'PM') {
                                        $nd_out1 = ($out1 >= $fOut) ? $out1 : $fOut;
                                    } else {
                                        $nd_out1 = $ndEnded;
                                    }
                                }
                            }

                            //in2
                            if ($day_in2 == 'PM' && $in2) {

                                if ($in2 >= $ndStart && $in2 <= strtotime('23:59:59')) {
                                    $nd_in2 = ($in2 <= $sIn) ? $sIn : $in2;
                                } else {
                                    $nd_in2 = $ndStart;
                                }
                            }

                            if ($day_in2 == 'AM' && $in2) {

                                if ($in2 <= $ndEnded) {
                                    $nd_in2 = ($in2 <= $sIn) ? $sIn : $in2;
                                } else {
                                    if (date('A', strtotime($in2)) == 'PM') {
                                        $nd_in2 = ($in2 >= $sIn) ? $sIn : $in2;
                                    } else {
                                        $nd_in2 = $ndEnded;
                                    }
                                }
                            }

                            //out2
                            if ($day_out2 == 'PM' && $out2) {
                                if ($out2 >= $ndStart && $out2 <= strtotime('23:59:59')) {

                                    if ($ndStart <= $sOut) {
                                        $nd_out2 = ($out2 <= $sOut) ? $out2 : $sOut;
                                    } else {
                                        $nd_out2 = $ndStart;
                                    }
                                } else {
                                    $nd_out2 = $ndStart;
                                }
                            }

                            if ($day_out2 == 'AM' && $out2) {

                                if ($out2 <= $ndEnded) {
                                    $nd_out2 = ($out2 <= $sOut) ? $out2 : $sOut;
                                } else {
                                    if (date('A', strtotime($out2)) == 'PM') {
                                        $nd_out2 = ($out2 >= $sOut) ? $out2 : $sOut;
                                    } else {
                                        $nd_out2 = $ndEnded;
                                    }
                                }
                            }

                            //nd
                            $nd = 0;
                            if ($night_diff == 'yes') {

                                $t  = explode(":", $nd_in1);
                                $h  = $t[0];
                                $m  = $t[1];
                                $t1 = date("H:i", strtotime("$nd_out1 -$h hours, -$m minutes"));

                                $t  = explode(":", $nd_in2);
                                $h  = $t[0];
                                $m  = $t[1];
                                $t2 = date("H:i", strtotime("$nd_out2 -$h hours, -$m minutes"));

                                list($h1, $m1) = explode(":", $t1);
                                list($h2, $m2) = explode(":", $t2);

                                $totalMinutes = ($h1 * 60 + $m1) + ($h2 * 60 + $m2);
                                $hh = floor($totalMinutes / 60);
                                $mm = $totalMinutes % 60;

                                $hm = sprintf("%02d:%02d", $hh, $mm);

                                $t  = explode(":", $hm);
                                $h  = $t[0] * 1;
                                $m  = $t[1] * 1;

                                if ($m >= $minTime) {
                                    $m = '30';
                                } else {
                                    $m = '00';
                                }

                                $hrs = $h / 8;
                                $min = ($m / 60) / 8;

                                $min = round(($m / 60), 3);
                                $nd =  round($h + $min, 3);
                                $decimal = explode('.', (string)$h + $min);
                                if (isset($decimal[1]) && strlen($decimal[1]) > 3) {
                                    $nd = number_format($h + $min, 3, '.', '');
                                }

                                $cond = ['dutyId' => $dutyId];
                                $data = ['night_diff' => $nd];
                                $update = $this->promo_model->update('tk', 'perday', $cond, $data);
                            }

                            $percentage = $percentage + $percent;

                            echo json_encode(array(
                                'result'    => 'loading',
                                'msg'       => round($percentage) . '%',
                                'msg1'      => round($percentage) . '%',
                                'date'      => "Updating Table (perday - night differential)",
                                'table'     => "[Date: $date; biometricId: $id; night_diff: $nd;]",
                                'exported'  => 'table_update'
                            ));
                        }
                    } else {

                        $percentage += $pdiv1;
                        echo json_encode(array(
                            'result'    => 'loading',
                            'msg'       => round($percentage) . '%',
                            'msg1'      => round($percentage) . '%',
                            'date'      => "Updating Table (perday - night differential)",
                            'table'     => "[empId: $empId; bioMetricId: $bioM;]",
                            'exported'  => 'table_update'
                        ));
                    }
                } else {
                    $percentage = $percentage + $pdiv1;
                    echo json_encode(array(
                        'result'    => 'loading',
                        'msg'       => round($percentage) . '%',
                        'msg1'      => round($percentage) . '%',
                        'date'      => "Updating Table (perday - night differential)",
                        'table'     => "[empId: $empId; bioMetricId: $bioM;]",
                        'exported'  => 'table_update'
                    ));
                }
            } else {
                $percentage += $pdiv1;
                echo json_encode(array(
                    'result'    => 'loading',
                    'msg'       => round($percentage) . '%',
                    'msg1'      => round($percentage) . '%',
                    'date'      => "Updating Table (perday - night differential)",
                    'table'     => "[empId: $empId; bioMetricId: $bioM;]",
                    'exported'  => 'table_update'
                ));
            }
        }

        //insert request_odaf_calc **************************************************************
        echo json_encode(array(
            'result'    => 'loading',
            'msg'       => round($percentage) . '%',
            'msg1'      => round($percentage) . '%',
            'date'      => "Preparing request_odaf_calc table...",
            'table'     => "",
            'exported'  => 'table_update',
        ));

        sleep(1);

        // $percentage = 0;
        $pdiv1 = 25 / count($odList);
        foreach ($odList as $key => $row) {
            $empId = $row['empId'];
            $amsId = $row['amsId'];

            $cond   = "empId='$empId' AND barcodeId NOT LIKE '%\_%' AND bioMetricId NOT LIKE '%\_%' ORDER BY assignId DESC LIMIT 1";
            $assign = $this->promo_model->select_rowArray('tk', 'assign', $cond);

            $table = "";
            $allowId = $assign['allowId'];
            if ($allowId == "Biometric ID") {
                $bioM   = $assign['bioMetricId'];
                $table  = "duty_biometric";
            }
            if ($allowId == "Logbox ID") {
                $bioM   = $assign['barcodeId'];
                $table  = "duty_logbox";
            }

            $percentage += $pdiv1;
            echo json_encode(array(
                'result'    => 'loading',
                'msg'       => round($percentage) . '%',
                'msg1'      => round($percentage) . '%',
                'date'      => "Inserting Table (request_odaf_calc)",
                'table'     => "[empId: $empId; bioMetricId: $bioM; amsId: $amsId;]",
                'exported'  => 'table_update'
            ));

            $cond = ['amsId' => $amsId, 'status' => 'Done'];
            $check = $this->promo_model->select_rowArray('tk', 'request_odaf_calc', $cond);
            if (!$check) {
                $data = ['amsId' => $amsId, 'status' => 'Done'];
                $insert = $this->promo_model->insert('tk', 'request_odaf_calc', $data);
            }
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

    public function co_third()
    {
        $input = $this->input->post(NULL, TRUE);
        $statCut = $input['statCut'];
        $data = [];

        $i = 0;
        $query = $this->promo_model->cutoff_table2($statCut, '1stcutoff');
        foreach ($query as $row) {
            $i++;
            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));

            $onclick = "onclick='coList(\"$statCut\",\"1\",\"$df\",\"$dt\")'";
            $sub_array = [];
            $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
            $sub_array[] = $dt;
            $sub_array[] = '<a href="javascript:;" ' . $onclick . '><i class="fa fa-list text-md text-primary"></i></a>';
            $data[] = $sub_array;
        }

        $i = 0;
        $query = $this->promo_model->cutoff_table2($statCut, '2ndcutoff');
        foreach ($query as $row) {
            $i++;
            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));

            $onclick = "onclick='coList(\"$statCut\",\"2\",\"$df\",\"$dt\")'";
            $sub_array = [];
            $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
            $sub_array[] = $dt;
            $sub_array[] = '<a href="javascript:;" ' . $onclick . '><i class="fa fa-list text-md text-primary"></i></a>';
            $data[] = $sub_array;
        }

        echo json_encode(['data' => $data]);
    }

    public function co_list()
    {
        $input      = $this->input->post(NULL, TRUE);
        $statCut    = $input['statCut'];
        $cut        = $input['cut'];
        $dateFrom   = date('Y-m-d', strtotime($input['dateFrom']));
        $dateTo     = date('Y-m-d', strtotime($input['dateTo']));

        echo '<div class="row">';
        echo '<div class="col-md-12">';
        echo '<input type="hidden" name="statCut" value="' . $statCut . '">';
        echo '<input type="hidden" name="dateFrom" value="' . $dateFrom . '">';
        echo '<input type="hidden" name="dateTo" value="' . $dateTo . '">';
        echo '<input type="hidden" name="cut" value="' . $cut . '">';
        echo    '<div class="form-check form-check-inline ml-2">
                  <input class="form-check-input" type="radio" name="batch" onclick="chkAllbatch(\'batch1\')">
                  <label class="form-check-label">Batch 1</label>
                </div>';
        echo    '<div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="batch" onclick="chkAllbatch(\'batch2\')">
                  <label class="form-check-label">Batch 2</label>
                </div>';
        echo    '<div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="batch" onclick="chkAllbatch(\'batch3\')">
                  <label class="form-check-label">Batch 3</label>
                </div>';
        echo    '<div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="batch" onclick="chkAllbatch(\'batch4\')">
                  <label class="form-check-label">Batch 4</label>
                </div>';
        echo '</div>';
        echo '<div class="col-md-12 table-responsive">';
        echo '<table id="employee_list_table" class="table table-hover table-bordered table-sm">
                <thead>
                    <tr>
                        <th>HRMS ID</th>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Department</th>
                        <th>Batch</th>
                        <th><input type="checkbox" name="chkAll" onclick="chkAllbox(\'employee_list_table\')"></th>
                        <th>Status</th>
                    </tr>
                </thead>';

        $batch = 1;
        $count = 0;
        $co_table = ($cut == 1) ? '1stcutoff' : '2ndcutoff';
        $query = $this->promo_model->process_co_list($statCut, $dateFrom, $dateTo);
        foreach ($query as $key => $row) {
            $empId      = $row['empId'];
            $recordNo   = $row['recordNo'];
            $name       = $row['name'];
            $company    = $row['promo_company'];
            $department = $row['promo_department'];

            $allowId = $row['allowId'];
            if ($allowId == "Biometric ID") {
                $bioM = $row['bioMetricId'];
                $duty_table = 'duty_biometric';
            }

            if ($allowId == "Logbox ID") {
                $bioM = $row['barcodeId'];
                $duty_table = 'duty_logbox';
            }

            // $status = "No entries";
            // $cond   = "bioMetricId='$bioM' AND dateDuty BETWEEN '$dateFrom' AND '$dateTo' LIMIT 1";
            // $check  = $this->promo_model->select_rowArray('tk', $duty_table, $cond);
            // if ($check) {

            $cond   = ['statCut' => $statCut, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo, 'bioMetricId' => $bioM];
            $check  = $this->promo_model->select_rowArray('tk', $co_table, $cond);
            if ($check) {
                $status = "OK";
                // if ($check['daysWork'] == '') {
                //     $status = "No entries";
                // }
            } else {
                $status = "OK but no C.O.";
            }
            // }

            $count++;
            $batch = ceil($count / 500);

            $action = '<input type="checkbox" class="employee_list_table batch' . $batch . '" name="empId[]" value="' . $empId . '">';

            echo '<tr>
                    <td>' . $empId . '</td>
                    <td>' . $name . '</td>
                    <td>' . $company . '</td>
                    <td>' . $department . '</td>
                    <td>' . $batch . '</td>
                    <td>' . $action . '</td>
                    <td>' . $status . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
        echo '</div>';
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

            //Calculate Late ************************************
            if (true) {
                $late       = false;
                $late1      = 0;
                $late2      = 0;
                $attempt    = 0;
                $totalLate  = 0;

                $min15 = '15';
                $cond = "1 ORDER BY slcId";
                $slc = $this->promo_model->select_rowArray('tk', 'setup_late_cur', $cond);
                if ($slc) {
                    $min15 = $slc['minutes']; // 15 min
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
                    $cond = ['dutyId' => $dutyId];
                    $check = $this->promo_model->select_rowArray('tk', 'violation', $cond);
                    if (!$check) {
                        $data = ['dutyId' => $dutyId, 'attempt' => $attempt];
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

                // echo json_encode(array(
                //     'result'    => 'loading',
                //     'msg'       => '',
                //     'msg1'      => '',
                //     'date'      => "Finalizing DTR data...",
                //     'table'     => "[empId: $empId; $date, $hw]",
                //     'exported'  => 'table_update'
                // ));

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

    public function noSchedule($empId, $date, $day)
    {
        $cond = "e.emp_id='$empId'";
        $promo_type = $this->promo_model->promoDetailsRow($cond)['promo_type'];

        $no_schedule = false;
        if ($promo_type == 'ROVING') {
            $cond   = "empId='$empId' AND dateRendered='$date' ORDER BY pId DESC";
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

            $cond = "empId='$empId' AND status='active' AND status2='Approved' AND '$date' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
            $amsId = $this->promo_model->select_rowArray('tk', 'assign_mul_sc', $cond)['amsId'];
            if ($amsId) {
                $cond  = ['empId' => $empId, 'amsId' => $amsId, 'date' => $date];
                $pns   = $this->promo_model->select_rowArray('tk', 'promo_nosched', $cond);
                if ($pns) {
                    $no_schedule = true;
                }
            }
        }

        return $no_schedule;
    }
}
