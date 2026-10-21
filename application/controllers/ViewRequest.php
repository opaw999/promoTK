<?php
defined('BASEPATH') or exit('No direct script access allowed');

class ViewRequest extends CI_Controller
{
    public $today;
    public $adminUser;
    public $userName;
    public $userId;
    public $hrCode;
    function __construct()
    {
        parent::__construct();
        $this->today        = date('Y-m-d');
        $this->adminUser    = array('01186-2023');
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

    public function otAuth()
    {
        $input = $this->input->post(NULL, TRUE);
        $statCut = $input['statCut'];
        $data = [];
        $query = $this->promo_model->viewReq_cutoff_table($statCut, '1stcutoff');
        foreach ($query as $row) {
            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));
            $id = $statCut . '|2|' . $df . '|' . $dt;

            $numRecords = [];
            $statuses   = ['Pending', 'Approved', 'Disapproved'];
            foreach ($statuses as $status) {
                $select = '*';
                $join   = 't1.empId=t2.empId';
                $cond   = "(dateOvertime BETWEEN '$row[dateFrom]' AND '$row[dateTo]') AND status = '$status' AND statCut = '$statCut'";
                $query  = $this->promo_model->viewRequest($select, 'e_o_authorization', $join, $cond);
                $numRecords[$status] = count($query);
            }

            $numP = $numRecords['Pending'];
            $numA = $numRecords['Approved'];
            $numD = $numRecords['Disapproved'];

            if ($numP != 0 || $numD != 0 || $numA != 0) {

                $action = '<a href="javascript:;" onclick="viewReqPerEmp(\'' . $statCut . '\',\'' . $df . '\',\'' . $dt . '\')"><i class="fa fa-list text-md text-primary"></i></a>';

                $sub_array = [];
                $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
                $sub_array[] = $dt;
                $sub_array[] = $numP;
                $sub_array[] = $numA;
                $sub_array[] = $numD;
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
        }

        $query = $this->promo_model->viewReq_cutoff_table($statCut, '2ndcutoff');
        foreach ($query as $row) {
            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));
            $id = $statCut . '|2|' . $df . '|' . $dt;

            $numRecords = [];
            $statuses   = ['Pending', 'Approved', 'Disapproved'];
            foreach ($statuses as $status) {
                $select = '*';
                $join   = 't1.empId=t2.empId';
                $cond   = "(dateOvertime BETWEEN '$row[dateFrom]' AND '$row[dateTo]') AND status = '$status' AND statCut = '$statCut'";
                $query  = $this->promo_model->viewRequest($select, 'e_o_authorization', $join, $cond);
                $numRecords[$status] = count($query);
            }

            $numP = $numRecords['Pending'];
            $numA = $numRecords['Approved'];
            $numD = $numRecords['Disapproved'];

            if ($numP != 0 || $numD != 0 || $numA != 0) {

                $action = '<a href="javascript:;" onclick="viewReqPerEmp(\'' . $statCut . '\',\'' . $df . '\',\'' . $dt . '\')"><i class="fa fa-list text-md text-primary"></i></a>';

                $sub_array = [];
                $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
                $sub_array[] = $dt;
                $sub_array[] = $numP;
                $sub_array[] = $numA;
                $sub_array[] = $numD;
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
        }

        echo json_encode(['data' => $data]);
    }

    public function otPayment()
    {
        $input = $this->input->post(NULL, TRUE);
        $statCut = $input['statCut'];
        $data = [];
        $query = $this->promo_model->viewReq_cutoff_table($statCut, '1stcutoff');
        foreach ($query as $row) {
            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));
            $id = $statCut . '|2|' . $df . '|' . $dt;

            $numRecords = [];
            $statuses   = ['Pending', 'Approved', 'Disapproved'];
            foreach ($statuses as $status) {
                $select = '*';
                $join   = 't1.emp_id=t2.empId';
                $cond   = "fromDate = '$row[dateFrom]' AND toDate = '$row[dateTo]' AND status = '$status' AND statCut = '$statCut'";
                $query  = $this->promo_model->viewRequest($select, 'otforpayment', $join, $cond);
                $numRecords[$status] = count($query);
            }

            $numP = $numRecords['Pending'];
            $numA = $numRecords['Approved'];
            $numD = $numRecords['Disapproved'];

            if ($numP != 0 || $numD != 0 || $numA != 0) {

                $action = '<a href="javascript:;" onclick="viewReqPerEmp(\'' . $statCut . '\',\'' . $df . '\',\'' . $dt . '\')"><i class="fa fa-list text-md text-primary"></i></a>';

                $sub_array = [];
                $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
                $sub_array[] = $dt;
                $sub_array[] = $numP;
                $sub_array[] = $numA;
                $sub_array[] = $numD;
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
        }

        $query = $this->promo_model->viewReq_cutoff_table($statCut, '2ndcutoff');
        foreach ($query as $row) {
            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));
            $id = $statCut . '|2|' . $df . '|' . $dt;

            $numRecords = [];
            $statuses   = ['Pending', 'Approved', 'Disapproved'];
            foreach ($statuses as $status) {
                $select = '*';
                $join   = 't1.emp_id=t2.empId';
                $cond   = "fromDate = '$row[dateFrom]' AND toDate = '$row[dateTo]' AND status = '$status' AND statCut = '$statCut'";
                $query  = $this->promo_model->viewRequest($select, 'otforpayment', $join, $cond);
                $numRecords[$status] = count($query);
            }

            $numP = $numRecords['Pending'];
            $numA = $numRecords['Approved'];
            $numD = $numRecords['Disapproved'];

            if ($numP != 0 || $numD != 0 || $numA != 0) {

                $action = '<a href="javascript:;" onclick="viewReqPerEmp(\'' . $statCut . '\',\'' . $df . '\',\'' . $dt . '\')"><i class="fa fa-list text-md text-primary"></i></a>';

                $sub_array = [];
                $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
                $sub_array[] = $dt;
                $sub_array[] = $numP;
                $sub_array[] = $numA;
                $sub_array[] = $numD;
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
        }

        echo json_encode(['data' => $data]);
    }

    public function cuAuth()
    {
        $input = $this->input->post(NULL, TRUE);
        $statCut = $input['statCut'];
        $data = [];
        $query = $this->promo_model->viewReq_cutoff_table($statCut, '1stcutoff');
        foreach ($query as $row) {
            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));
            $id = $statCut . '|2|' . $df . '|' . $dt;

            $numRecords = [];
            $statuses   = ['Pending', 'Approved', 'Disapproved'];
            foreach ($statuses as $status) {
                $select = '*';
                $join   = 't1.empId=t2.empId';
                $cond   = "(cuDate BETWEEN '$row[dateFrom]' AND '$row[dateTo]') AND status = '$status' AND statCut = '$statCut'";
                $query  = $this->promo_model->viewRequest($select, 'coverup_auth', $join, $cond);
                $numRecords[$status] = count($query);
            }

            $numP = $numRecords['Pending'];
            $numA = $numRecords['Approved'];
            $numD = $numRecords['Disapproved'];

            if ($numP != 0 || $numD != 0 || $numA != 0) {

                $action = '<a href="javascript:;" onclick="viewReqPerEmp(\'' . $statCut . '\',\'' . $df . '\',\'' . $dt . '\')"><i class="fa fa-list text-md text-primary"></i></a>';

                $sub_array = [];
                $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
                $sub_array[] = $dt;
                $sub_array[] = $numP;
                $sub_array[] = $numA;
                $sub_array[] = $numD;
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
        }

        $query = $this->promo_model->viewReq_cutoff_table($statCut, '2ndcutoff');
        foreach ($query as $row) {
            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));
            $id = $statCut . '|2|' . $df . '|' . $dt;

            $numRecords = [];
            $statuses   = ['Pending', 'Approved', 'Disapproved'];
            foreach ($statuses as $status) {
                $select = '*';
                $join   = 't1.empId=t2.empId';
                $cond   = "(cuDate BETWEEN '$row[dateFrom]' AND '$row[dateTo]') AND status = '$status' AND statCut = '$statCut'";
                $query  = $this->promo_model->viewRequest($select, 'coverup_auth', $join, $cond);
                $numRecords[$status] = count($query);
            }

            $numP = $numRecords['Pending'];
            $numA = $numRecords['Approved'];
            $numD = $numRecords['Disapproved'];

            if ($numP != 0 || $numD != 0 || $numA != 0) {

                $action = '<a href="javascript:;" onclick="viewReqPerEmp(\'' . $statCut . '\',\'' . $df . '\',\'' . $dt . '\')"><i class="fa fa-list text-md text-primary"></i></a>';

                $sub_array = [];
                $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
                $sub_array[] = $dt;
                $sub_array[] = $numP;
                $sub_array[] = $numA;
                $sub_array[] = $numD;
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
        }

        echo json_encode(['data' => $data]);
    }

    public function utAuth()
    {
        $input = $this->input->post(NULL, TRUE);
        $statCut = $input['statCut'];
        $data = [];
        $query = $this->promo_model->viewReq_cutoff_table($statCut, '1stcutoff');
        foreach ($query as $row) {
            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));
            $id = $statCut . '|2|' . $df . '|' . $dt;

            $numRecords = [];
            $statuses   = ['Pending', 'Approved', 'Disapproved'];
            foreach ($statuses as $status) {
                $select = '*';
                $join   = 't1.empId=t2.empId';
                $cond   = "(dateUndertime BETWEEN '$row[dateFrom]' AND '$row[dateTo]') AND status = '$status' AND statCut = '$statCut'";
                $query  = $this->promo_model->viewRequest($select, 'undertime_auth', $join, $cond);
                $numRecords[$status] = count($query);
            }

            $numP = $numRecords['Pending'];
            $numA = $numRecords['Approved'];
            $numD = $numRecords['Disapproved'];

            if ($numP != 0 || $numD != 0 || $numA != 0) {

                $action = '<a href="javascript:;" onclick="viewReqPerEmp(\'' . $statCut . '\',\'' . $df . '\',\'' . $dt . '\')"><i class="fa fa-list text-md text-primary"></i></a>';

                $sub_array = [];
                $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
                $sub_array[] = $dt;
                $sub_array[] = $numP;
                $sub_array[] = $numA;
                $sub_array[] = $numD;
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
        }

        $query = $this->promo_model->viewReq_cutoff_table($statCut, '2ndcutoff');
        foreach ($query as $row) {
            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));
            $id = $statCut . '|2|' . $df . '|' . $dt;

            $numRecords = [];
            $statuses   = ['Pending', 'Approved', 'Disapproved'];
            foreach ($statuses as $status) {
                $select = '*';
                $join   = 't1.empId=t2.empId';
                $cond   = "(dateUndertime BETWEEN '$row[dateFrom]' AND '$row[dateTo]') AND status = '$status' AND statCut = '$statCut'";
                $query  = $this->promo_model->viewRequest($select, 'undertime_auth', $join, $cond);
                $numRecords[$status] = count($query);
            }

            $numP = $numRecords['Pending'];
            $numA = $numRecords['Approved'];
            $numD = $numRecords['Disapproved'];

            if ($numP != 0 || $numD != 0 || $numA != 0) {

                $action = '<a href="javascript:;" onclick="viewReqPerEmp(\'' . $statCut . '\',\'' . $df . '\',\'' . $dt . '\')"><i class="fa fa-list text-md text-primary"></i></a>';

                $sub_array = [];
                $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
                $sub_array[] = $dt;
                $sub_array[] = $numP;
                $sub_array[] = $numA;
                $sub_array[] = $numD;
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
        }

        echo json_encode(['data' => $data]);
    }

    public function dwa()
    {
        $input = $this->input->post(NULL, TRUE);
        $statCut = $input['statCut'];
        $data = [];
        $query = $this->promo_model->viewReq_cutoff_table($statCut, '1stcutoff');
        foreach ($query as $row) {
            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));
            $id = $statCut . '|2|' . $df . '|' . $dt;

            $numRecords = [];
            $statuses   = ['Pending', 'Approved', 'Disapproved'];
            foreach ($statuses as $status) {
                $select = '*';
                $join   = 't1.empId=t2.empId';
                $cond   = "((dateFrom BETWEEN '$row[dateFrom]' AND '$row[dateTo]') OR (dateTo BETWEEN '$row[dateFrom]' AND '$row[dateTo]')) AND status = '$status' AND statCut = '$statCut'";
                $query  = $this->promo_model->viewRequest($select, 'd_w_adjustment', $join, $cond);
                $numRecords[$status] = count($query);
            }

            $numP = $numRecords['Pending'];
            $numA = $numRecords['Approved'];
            $numD = $numRecords['Disapproved'];

            if ($numP != 0 || $numD != 0 || $numA != 0) {

                $action = '<a href="javascript:;" onclick="viewReqPerEmp(\'' . $statCut . '\',\'' . $df . '\',\'' . $dt . '\')"><i class="fa fa-list text-md text-primary"></i></a>';

                $sub_array = [];
                $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
                $sub_array[] = $dt;
                $sub_array[] = $numP;
                $sub_array[] = $numA;
                $sub_array[] = $numD;
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
        }

        $query = $this->promo_model->viewReq_cutoff_table($statCut, '2ndcutoff');
        foreach ($query as $row) {
            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));
            $id = $statCut . '|2|' . $df . '|' . $dt;

            $numRecords = [];
            $statuses   = ['Pending', 'Approved', 'Disapproved'];
            foreach ($statuses as $status) {
                $select = '*';
                $join   = 't1.empId=t2.empId';
                $cond   = "((dateFrom BETWEEN '$row[dateFrom]' AND '$row[dateTo]') OR (dateTo BETWEEN '$row[dateFrom]' AND '$row[dateTo]')) AND status = '$status' AND statCut = '$statCut'";
                $query  = $this->promo_model->viewRequest($select, 'd_w_adjustment', $join, $cond);
                $numRecords[$status] = count($query);
            }

            $numP = $numRecords['Pending'];
            $numA = $numRecords['Approved'];
            $numD = $numRecords['Disapproved'];

            if ($numP != 0 || $numD != 0 || $numA != 0) {

                $action = '<a href="javascript:;" onclick="viewReqPerEmp(\'' . $statCut . '\',\'' . $df . '\',\'' . $dt . '\')"><i class="fa fa-list text-md text-primary"></i></a>';

                $sub_array = [];
                $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
                $sub_array[] = $dt;
                $sub_array[] = $numP;
                $sub_array[] = $numA;
                $sub_array[] = $numD;
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
        }

        echo json_encode(['data' => $data]);
    }

    public function loa()
    {
        $input = $this->input->post(NULL, TRUE);
        $statCut = $input['statCut'];
        $data = [];
        $query = $this->promo_model->viewReq_cutoff_table($statCut, '1stcutoff');
        foreach ($query as $row) {
            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));
            $id = $statCut . '|2|' . $df . '|' . $dt;

            $numRecords = [];
            $statuses   = ['Pending', 'Approved', 'Disapproved'];
            foreach ($statuses as $status) {
                $select = '*';
                $join   = 't1.empId=t2.empId';
                $cond   = "((incDate_sF BETWEEN '$row[dateFrom]' AND '$row[dateTo]') OR (incDate_sT BETWEEN '$row[dateFrom]' AND '$row[dateTo]')) AND status='$status' AND statCut = '$statCut'";
                $query  = $this->promo_model->viewRequest($select, 'r_l_absence', $join, $cond);
                $numRecords[$status] = count($query);
            }

            $numP = $numRecords['Pending'];
            $numA = $numRecords['Approved'];
            $numD = $numRecords['Disapproved'];

            if ($numP != 0 || $numD != 0 || $numA != 0) {

                $action = '<a href="javascript:;" onclick="viewReqPerEmp(\'' . $statCut . '\',\'' . $df . '\',\'' . $dt . '\')"><i class="fa fa-list text-md text-primary"></i></a>';

                $sub_array = [];
                $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
                $sub_array[] = $dt;
                $sub_array[] = $numP;
                $sub_array[] = $numA;
                $sub_array[] = $numD;
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
        }

        $query = $this->promo_model->viewReq_cutoff_table($statCut, '2ndcutoff');
        foreach ($query as $row) {
            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));
            $id = $statCut . '|2|' . $df . '|' . $dt;

            $numRecords = [];
            $statuses   = ['Pending', 'Approved', 'Disapproved'];
            foreach ($statuses as $status) {
                $select = '*';
                $join   = 't1.empId=t2.empId';
                $cond   = "((incDate_sF BETWEEN '$row[dateFrom]' AND '$row[dateTo]') OR (incDate_sT BETWEEN '$row[dateFrom]' AND '$row[dateTo]')) AND status='$status' AND statCut = '$statCut'";
                $query  = $this->promo_model->viewRequest($select, 'r_l_absence', $join, $cond);
                $numRecords[$status] = count($query);
            }

            $numP = $numRecords['Pending'];
            $numA = $numRecords['Approved'];
            $numD = $numRecords['Disapproved'];

            if ($numP != 0 || $numD != 0 || $numA != 0) {

                $action = '<a href="javascript:;" onclick="viewReqPerEmp(\'' . $statCut . '\',\'' . $df . '\',\'' . $dt . '\')"><i class="fa fa-list text-md text-primary"></i></a>';

                $sub_array = [];
                $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
                $sub_array[] = $dt;
                $sub_array[] = $numP;
                $sub_array[] = $numA;
                $sub_array[] = $numD;
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
        }

        echo json_encode(['data' => $data]);
    }

    public function doShort()
    {
        $input = $this->input->post(NULL, TRUE);
        $statCut = $input['statCut'];
        $data = [];
        $query = $this->promo_model->viewReq_cutoff_table($statCut, '1stcutoff');
        foreach ($query as $row) {
            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));
            $id = $statCut . '|2|' . $df . '|' . $dt;

            $numRecords = [];
            $statuses   = ['Pending', 'Approved', 'Disapproved'];
            foreach ($statuses as $status) {
                $select = '*';
                $join   = 't1.empId=t2.empId';
                $cond   = "(dateToChange BETWEEN '$row[dateFrom]' AND '$row[dateTo]') AND status = '$status' AND statCut = '$statCut'";
                $query  = $this->promo_model->viewRequest($select, 'ass_change_do', $join, $cond);
                $numRecords[$status] = count($query);
            }

            $numP = $numRecords['Pending'];
            $numA = $numRecords['Approved'];
            $numD = $numRecords['Disapproved'];

            if ($numP != 0 || $numD != 0 || $numA != 0) {

                $action = '<a href="javascript:;" onclick="viewReqPerEmp(\'' . $statCut . '\',\'' . $df . '\',\'' . $dt . '\')"><i class="fa fa-list text-md text-primary"></i></a>';

                $sub_array = [];
                $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
                $sub_array[] = $dt;
                $sub_array[] = $numP;
                $sub_array[] = $numA;
                $sub_array[] = $numD;
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
        }

        $query = $this->promo_model->viewReq_cutoff_table($statCut, '2ndcutoff');
        foreach ($query as $row) {
            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));
            $id = $statCut . '|2|' . $df . '|' . $dt;

            $numRecords = [];
            $statuses   = ['Pending', 'Approved', 'Disapproved'];
            foreach ($statuses as $status) {
                $select = '*';
                $join   = 't1.empId=t2.empId';
                $cond   = "(dateToChange BETWEEN '$row[dateFrom]' AND '$row[dateTo]') AND status = '$status' AND statCut = '$statCut'";
                $query  = $this->promo_model->viewRequest($select, 'ass_change_do', $join, $cond);
                $numRecords[$status] = count($query);
            }

            $numP = $numRecords['Pending'];
            $numA = $numRecords['Approved'];
            $numD = $numRecords['Disapproved'];

            if ($numP != 0 || $numD != 0 || $numA != 0) {

                $action = '<a href="javascript:;" onclick="viewReqPerEmp(\'' . $statCut . '\',\'' . $df . '\',\'' . $dt . '\')"><i class="fa fa-list text-md text-primary"></i></a>';

                $sub_array = [];
                $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
                $sub_array[] = $dt;
                $sub_array[] = $numP;
                $sub_array[] = $numA;
                $sub_array[] = $numD;
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
        }

        echo json_encode(['data' => $data]);
    }

    public function doLong()
    {
        $input = $this->input->post(NULL, TRUE);
        $statCut = $input['statCut'];
        $data = [];
        $query = $this->promo_model->viewReq_cutoff_table($statCut, '1stcutoff');
        foreach ($query as $row) {
            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));
            $id = $statCut . '|2|' . $df . '|' . $dt;

            $numRecords = [];
            $statuses   = ['Pending', 'Approved', 'Disapproved'];
            foreach ($statuses as $status) {
                $select = '*';
                $join   = 't1.empId=t2.empId';
                $cond   = "(effectiveDate BETWEEN '$row[dateFrom]' AND '$row[dateTo]') AND status = '$status' AND statCut = '$statCut'";
                $query  = $this->promo_model->viewRequest($select, 'ass_dayoff_long', $join, $cond);
                $numRecords[$status] = count($query);
            }

            $numP = $numRecords['Pending'];
            $numA = $numRecords['Approved'];
            $numD = $numRecords['Disapproved'];

            if ($numP != 0 || $numD != 0 || $numA != 0) {

                $action = '<a href="javascript:;" onclick="viewReqPerEmp(\'' . $statCut . '\',\'' . $df . '\',\'' . $dt . '\')"><i class="fa fa-list text-md text-primary"></i></a>';

                $sub_array = [];
                $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
                $sub_array[] = $dt;
                $sub_array[] = $numP;
                $sub_array[] = $numA;
                $sub_array[] = $numD;
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
        }

        $query = $this->promo_model->viewReq_cutoff_table($statCut, '2ndcutoff');
        foreach ($query as $row) {
            $dateFrom = strtotime($row['dateFrom']);
            $df = date('M. d, Y', strtotime($row['dateFrom']));
            $dt = date('M. d, Y', strtotime($row['dateTo']));
            $id = $statCut . '|2|' . $df . '|' . $dt;

            $numRecords = [];
            $statuses   = ['Pending', 'Approved', 'Disapproved'];
            foreach ($statuses as $status) {
                $select = '*';
                $join   = 't1.empId=t2.empId';
                $cond   = "(effectiveDate BETWEEN '$row[dateFrom]' AND '$row[dateTo]') AND status = '$status' AND statCut = '$statCut'";
                $query  = $this->promo_model->viewRequest($select, 'ass_dayoff_long', $join, $cond);
                $numRecords[$status] = count($query);
            }

            $numP = $numRecords['Pending'];
            $numA = $numRecords['Approved'];
            $numD = $numRecords['Disapproved'];

            if ($numP != 0 || $numD != 0 || $numA != 0) {

                $action = '<a href="javascript:;" onclick="viewReqPerEmp(\'' . $statCut . '\',\'' . $df . '\',\'' . $dt . '\')"><i class="fa fa-list text-md text-primary"></i></a>';

                $sub_array = [];
                $sub_array[] = '<span style="display:none;">' . $dateFrom . '</span>' . $df;
                $sub_array[] = $dt;
                $sub_array[] = $numP;
                $sub_array[] = $numA;
                $sub_array[] = $numD;
                $sub_array[] = $action;
                $data[] = $sub_array;
            }
        }

        echo json_encode(['data' => $data]);
    }

    public function viewReqPerEmp()
    {
        $input      = $this->input->post(NULL, TRUE);
        $mdy        = date("Y-m-d", strtotime($input['d1']));
        $mdy2       = date("Y-m-d", strtotime($input['d2']));
        $promoCut   = $input['promoCut'];
        $func       = $input['pageName'];

        echo '<div class="table-responsive">';
        if ($func == 'otAuth') {
            echo '<table class="table table-sm table-hover align-middle" style="width:100%;" id="viewReqPerEmp">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Date OT</th>
                            <th>OT Length</th>
                            <th>Date Requested</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';

            $select = '*';
            $join   = 't1.empId=t2.empId';
            $cond   = "(dateOvertime BETWEEN '$mdy' AND '$mdy2') AND statCut = '$promoCut'";
            $query  = $this->promo_model->viewRequest($select, 'e_o_authorization', $join, $cond);
        } else if ($func == 'otPayment') {
            echo '<table class="table table-sm table-hover align-middle" style="width:100%;" id="viewReqPerEmp">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Date OT</th>
                            <th>OT Length</th>
                            <th>Date Requested</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';

            $select = '*';
            $join   = 't1.emp_id=t2.empId';
            $cond   = "fromDate='$mdy' AND toDate='$mdy2' AND statCut='$promoCut'";
            $query  = $this->promo_model->viewRequest($select, 'otforpayment', $join, $cond);
        } else if ($func == 'cuAuth') {
            echo '<table class="table table-sm table-hover align-middle" style="width:100%;" id="viewReqPerEmp">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Cover-Up Date</th>
                            <th>No. of Hours</th>
                            <th>Date Requested</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';

            $select = '*';
            $join   = 't1.empId=t2.empId';
            $cond   = "(cuDate BETWEEN '$mdy' AND '$mdy2') AND statCut = '$promoCut'";
            $query  = $this->promo_model->viewRequest($select, 'coverup_auth', $join, $cond);
        } else if ($func == 'utAuth') {
            echo '<table class="table table-sm table-hover align-middle" style="width:100%;" id="viewReqPerEmp">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Undertime Date</th>
                            <th>No. of Hours</th>
                            <th>Date Requested</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';

            $select = '*';
            $join   = 't1.empId=t2.empId';
            $cond   = "(dateUndertime BETWEEN '$mdy' AND  '$mdy2') AND statCut = '$promoCut'";
            $query  = $this->promo_model->viewRequest($select, 'undertime_auth', $join, $cond);
        } else if ($func == 'dwa') {
            echo '<table class="table table-sm table-hover align-middle" style="width:100%;" id="viewReqPerEmp">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Date Rendered</th>
                            <th>Type</th>
                            <th>Date Requested</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';

            $select = '*,t1.type as type';
            $join   = 't1.empId=t2.empId';
            $cond   = "((dateFrom BETWEEN '$mdy' AND '$mdy2') OR (dateTo BETWEEN '$mdy' AND '$mdy2')) AND statCut = '$promoCut' ";
            $query  = $this->promo_model->viewRequest($select, 'd_w_adjustment', $join, $cond);
        } else if ($func == 'loa') {
            echo '<table class="table table-sm table-hover align-middle" style="width:100%;" id="viewReqPerEmp">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Date Rendered</th>
                            <th>Type</th>
                            <th>Date Requested</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';

            $select = '*';
            $join   = 't1.empId=t2.empId';
            $cond   = "((incDate_sF BETWEEN '$mdy' AND '$mdy2') OR (incDate_sT BETWEEN '$mdy' AND '$mdy2')) AND statCut = '$promoCut'";
            $query  = $this->promo_model->viewRequest($select, 'r_l_absence', $join, $cond);
        } else if ($func == 'doShort') {
            echo    '<table class="table table-sm table-hover align-middle" style="width:100%;" id="viewReqPerEmp">
                        <thead>
                          <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Current Dayoff</th>
                            <th>New Dayoff</th>
                            <th>Date Requested</th>
                            <th>Status</th>
                            <th>Action</th>
                          </tr>
                        </thead>
                        <tbody>';

            $select = '*';
            $join   = 't1.empId=t2.empId';
            $cond   = "(dateToChange BETWEEN '$mdy' AND '$mdy2') AND statCut = '$promoCut'";
            $query  = $this->promo_model->viewRequest($select, 'ass_change_do', $join, $cond);
        } else if ($func == 'doLong') {
            echo '<table class="table table-sm table-hover align-middle" style="width:100%;" id="viewReqPerEmp">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Current Dayoff</th>
                            <th>New Dayoff</th>
                            <th>Date Requested</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';

            $select = '*';
            $join   = 't1.empId=t2.empId';
            $cond   = "(effectiveDate BETWEEN '$mdy' AND '$mdy2') AND statCut = '$promoCut'";
            $query  = $this->promo_model->viewRequest($select, 'ass_dayoff_long', $join, $cond);
        }

        foreach ($query as $key => $row) {
            if ($func == 'otAuth') {
                $empId      = $row['empId'];
                $eOAId      = $row['eOAId'];
                $dateOT     = date('m/d/Y', strtotime($row['dateOvertime']));
                $OTLength   = $row['OTLength'];
                $dateReq    = date('m/d/Y', strtotime($row['dateReq']));
                $status     = $row['status'];
            } else if ($func == 'otPayment') {
                $empId      = $row['emp_id'];
                $pay_id     = $row['pay_id'];
                $dateOT     = date('m/d/Y', strtotime($row['otDate']));
                $OTLength   = $row['otLength'];
                $otDis      = $row['otDisapproved'];
                $dateReq    = date('m/d/Y', strtotime($row['dateReq']));
                $status     = $row['status'];
            } else if ($func == 'cuAuth') {
                $empId      = $row['empId'];
                $eCAId      = $row['eCAId'];
                $cuDate     = date('m/d/Y', strtotime($row['cuDate']));
                $dateReq    = date('m/d/Y', strtotime($row['dateReq']));
                $noHours    = $row['noHours'];
                $status     = $row['status'];
            } else if ($func == 'utAuth') {
                $empId      = $row['empId'];
                $eUAId      = $row['eUAId'];
                $dateUT     = date('m/d/Y', strtotime($row['dateUndertime']));
                $dateReq    = date('m/d/Y', strtotime($row['dateReq']));
                $noHours    = $row['numHours'];
                $status     = $row['status'];
            } else if ($func == 'dwa') {
                $empId      = $row['empId'];
                $dWAId      = $row['dWAId'];
                $type       = $row['type'];
                $dateFrom   = $row['dateFrom'];
                $dateTo     = $row['dateTo'];
                $status     = $row['status'];
                $dateReq    = date('m/d/Y', strtotime($row['dateReq']));
                $dateRen    = '';
                if ($dateFrom == $dateTo) {
                    $dateRen = date('m/d/Y', strtotime($dateFrom));
                } else {
                    $dateRen = date('m/d/Y', strtotime($dateFrom)) . " to " . date('m/d/Y', strtotime($dateTo));
                }

                $day = '';
                $cond = ['empId' => $empId, 'btfw_ID' => $dWAId, 'type' => $type];
                $od = $this->promo_model->select_rowArray('tk', 'off_date', $cond);
                if ($od) {
                    $day = ' <i>(' . $od['day'] . ')</i>';
                }
            } else if ($func == 'loa') {
                $empId      = $row['empId'];
                $rLAId      = $row['rLAId'];
                $incDate_sF = $row['incDate_sF'];
                $incDate_sT = $row['incDate_sT'];
                $type       = $row['typeLeave'];
                $dateReq    = date('m/d/Y', strtotime($row['dateReq']));
                $status     = $row['status'];

                $dateRen = '';
                if ($incDate_sF == $incDate_sT) {
                    $dateRen = date('m/d/Y', strtotime($incDate_sF));
                } else {
                    $dateRen = date('m/d/Y', strtotime($incDate_sF)) . " to " . date('m/d/Y', strtotime($incDate_sT));;
                }

                $day = '';
                $cond = ['empId' => $empId, 'rLAId' => $rLAId];
                $rd = $this->promo_model->select_rowArray('tk', 'rla_day', $cond);
                if ($rd) {
                    $day = ' <i>(' . $rd['day'] . ')</i>';
                }
            } else if ($func == 'doShort') {
                $empId      = $row['empId'];
                $acdId      = $row['acdId'];
                $curDay     = $row['dayToChange'];
                $newDay     = $row['dayNew'];
                $curDate    = date('m/d/Y', strtotime($row['dateToChange']));
                $newDate    = date('m/d/Y', strtotime($row['dateNew']));
                $dateReq    = date('m/d/Y', strtotime($row['dateReq']));
                $status     = $row['status'];
            } else if ($func == 'doLong') {
                $empId      = $row['empId'];
                $adlid      = $row['adlid'];
                $newDay     = $row['newDay'];
                $curDay     = $row['curDayOff'];
                $dateReq    = date('m/d/Y', strtotime($row['dateRequested']));
                $status     = $row['status'];
            }

            $name = $this->promo_model->getName_employee3($empId);

            if ($func == 'otAuth') {
                $action = '<a href="javascript:;" onclick="viewEmpEOA(\'' . $eOAId . '\')"><i class="fa fa-list text-md text-primary"></i></a>';
                echo '<tr>
                        <td>' . $empId . '</td>
                        <td>' . $name . '</td>
                        <td>' . $dateOT . '</td>
                        <td>' . $OTLength . '</td>
                        <td>' . $dateReq . '</td>
                        <td>' . $status . '</td>
                        <td>' . $action . '</td>';
                echo '</tr>';
            } else if ($func == 'otPayment') {
                $action = '<a href="javascript:;" onclick="viewEmpOTP(\'' . $pay_id . '\')"><i class="fa fa-list text-md text-primary"></i></a>';
                echo '<tr>
                        <td>' . $empId . '</td>
                        <td>' . $name . '</td>
                        <td>' . $dateOT . '</td>
                        <td>' . $OTLength . '</td>
                        <td>' . $dateReq . '</td>
                        <td>' . $status . '</td>
                        <td>' . $action . '</td>';
                echo '</tr>';
            } else if ($func == 'cuAuth') {
                $action = '<a href="javascript:;" onclick="viewEmpCUA(\'' . $eCAId . '\')"><i class="fa fa-list text-md text-primary"></i></a>';
                echo '<tr>
                        <td>' . $empId . '</td>
                        <td>' . $name . '</td>
                        <td>' . $cuDate . '</td>
                        <td>' . $noHours . '</td>
                        <td>' . $dateReq . '</td>
                        <td>' . $status . '</td>
                        <td>' . $action . '</td>';
                echo '</tr>';
            } else if ($func == 'utAuth') {
                $action = '<a href="javascript:;" onclick="viewEmpUTA(\'' . $eUAId . '\')"><i class="fa fa-list text-md text-primary"></i></a>';
                echo '<tr>
                        <td>' . $empId . '</td>
                        <td>' . $name . '</td>
                        <td>' . $dateUT . '</td>
                        <td>' . $noHours . '</td>
                        <td>' . $dateReq . '</td>
                        <td>' . $status . '</td>
                        <td>' . $action . '</td>';
                echo '</tr>';
            } else if ($func == 'dwa') {
                $action = '<a href="javascript:;" onclick="viewEmpDWA(\'' . $dWAId . '\')"><i class="fa fa-list text-md text-primary"></i></a>';
                echo '<tr>
                        <td>' . $empId . '</td>
                        <td>' . $name . '</td>
                        <td>' . $dateRen . $day . '</td>
                        <td>' . $type . '</td>
                        <td>' . $dateReq . '</td>
                        <td>' . $status . '</td>
                        <td>' . $action . '</td>';
                echo '</tr>';
            } else if ($func == 'loa') {
                $action = '<a href="javascript:;" onclick="viewEmpLOA(\'' . $rLAId . '\')"><i class="fa fa-list text-md text-primary"></i></a>';
                echo '<tr>
                        <td>' . $empId . '</td>
                        <td>' . $name . '</td>
                        <td>' . $dateRen  . $day . '</td>
                        <td>' . $type . '</td>
                        <td>' . $dateReq . '</td>
                        <td>' . $status . '</td>
                        <td>' . $action . '</td>';
                echo '</tr>';
            } else if ($func == 'doShort') {
                $action = '<a href="javascript:;" onclick="viewEmpDOS(\'' . $acdId . '\')"><i class="fa fa-list text-md text-primary"></i></a>';
                echo '<tr>
                        <td>' . $empId . '</td>
                        <td>' . $name . '</td>
                        <td>' . $curDate . '</td>
                        <td>' . $newDate . '</td>
                        <td>' . $dateReq . '</td>
                        <td>' . $status . '</td>
                        <td>' . $action . '</td>';
                echo '</tr>';
            } else if ($func == 'doLong') {
                $action = '<a href="javascript:;" onclick="viewEmpDOL(\'' . $adlid . '\')"><i class="fa fa-list text-md text-primary"></i></a>';
                echo '<tr>
                        <td>' . $empId . '</td>
                        <td>' . $name . '</td>
                        <td>' . $curDay . '</td>
                        <td>' . $newDay . '</td>
                        <td>' . $dateReq . '</td>
                        <td>' . $status . '</td>
                        <td>' . $action . '</td>';
                echo '</tr>';
            }
        }
        echo '</tbody>';
        echo '</table>';
        echo '</div>';
    }

    public function viewEmpEOA()
    {
        $input  = $this->input->post(NULL, TRUE);
        $eOAId  = $input['eOAId'];
        $c      = $this->promo_model->select_rowArray('tk', 'e_o_authorization', ['eOAId' => $eOAId]);

        $empId  = $c['empId'];
        $dateO  = date('M. d, Y', strtotime($c['dateOvertime']));
        $dateR  = date('M. d, Y', strtotime($c['dateReq']));
        $in1    = $c['ot1st_time'];
        $out1   = $c['ot1en_time'];
        $ot1    = $c['ot1_length'];
        $in2    = $c['ot2st_time'];
        $out2   = $c['ot2en_time'];
        $ot2    = $c['ot2_length'];

        $otLength       = $c['OTLength'];
        $otApp          = $c['otApp'];
        $remark         = $c['remark'];
        $purpose        = $c['purpose'];
        $dateReq        = $c['dateReq'];
        $reqBy          = $c['reqBy'];
        $sup_message    = $c['sup_message'];
        $status         = $c['status'];
        $statusB        = $c['status'];

        $cond   = "e.emp_id = '$empId'";
        $emp    = $this->promo_model->promoDetailsRow($cond);
        $app    = $this->promo_model->select_rowArray('pis', 'applicant', ['app_id' => $emp['emp_id']]);

        $photo = explode("../", $app['photo']);
        $photoLink = 'http://' . $_SERVER['SERVER_ADDR'] . ':80' . $_SERVER['SERVER_PORT'] . '/hrms/' . $photo[1];
        if ($emp['current_status'] == 'Active') {
            $stat = 'success';
        } else if ($emp['current_status'] == 'blacklisted') {
            $stat = 'danger';
        } else {
            $stat = 'warning';
        }

        $stores = '';
        $i      = 0;
        $bus    = $this->promo_model->locate_promo_bu($this->hrCode);
        foreach ($bus as $bu) {
            $hasBu = $this->promo_model->empStores('promo_record', $emp['emp_id'], $emp['record_no'], $bu['bunit_field']);
            if ($hasBu) {
                $i++;
                $buName = '<span class="badge mb-1 bg-secondary mr-1">' . $bu['bunit_acronym'] . '</span>';
                $stores = ($i == 1) ? $buName : $stores . $buName;
            }
        }

        if ($status == 'Approved') {
            $color = 'text-success';
        } else if ($status == 'Disapproved') {
            $color = 'text-danger';
        } else if ($status == 'Cancelled') {
            $color = 'text-primary';
        } else if ($status == 'Pending') {
            $color = 'text-warning';
        } else {
            $color = '';
        }

        echo '<div class="row">';

        echo '<div class="col-md-12">';
        echo '<div class="d-flex  gap-2">';
        echo '<img src="' . $photoLink . '" alt="..." class="border rounded shadow p-1 bg-white" width="90" height="90">';
        echo '<table class="table table-sm table-borderless">';
        echo    '<tr>';
        echo    '<th>Employee ID</th>';
        echo    '<td>: ' . $emp['emp_id'] . '</td>';
        echo    '<th>Name</th>';
        echo    '<td>: ' . $emp['name'] . '</td>';
        echo    '</tr>';
        echo    '<tr>';
        echo    '<th>Status</th>';
        echo    '<td>: <span class="badge mb-1 bg-' . $stat . '">' . $emp['current_status'] . '</span></td>';
        echo    '<th>Store(s)</th>';
        echo    '<td>: ' . $stores . '</td>';
        echo    '</tr>';
        echo    '<tr>';
        echo    '<th>Department</th>';
        echo    '<td>: ' . $emp['promo_department'] . '</td>';
        echo    '<th>Company</th>';
        echo    '<td class="text-truncate d-inline-block" style="max-width: 300px;" title="' . $emp['promo_company'] . '">: ' . $emp['promo_company'] . '</td>';
        echo    '</tr>';
        echo '</table>';
        echo '</div>';
        echo '</div>';

        echo '<div class="col-md-12">';
        echo '<table class="table table-sm table-bordered" style="width:100%;">
                <tr>
                    <th>Date Requested</th>
                    <td>' . $dateR . '</td>
                    <th>Date Overtime</th>
                    <td>' . $dateO . '</td>
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
                    <td class="' . $color . '">' . $status . '</td>
                </tr>
                <tr>
                    <th>Purpose</th>
                    <td colspan="3">' . $purpose . '</td>
                </tr>';
        echo '</table>';

        $cond   = array('requestId' => $eOAId, 'requestType' => 'EOA');
        $s      = $this->promo_model->select_resultArray('tk', 'approveby', $cond);
        $cancel = $this->promo_model->select_resultArray('tk', 'hr_canceled_by', $cond);
        $rthr   = $this->promo_model->select_rowArray('tk', 'request_to_hr', $cond);

        if ($s) {
            echo '<table class="table table-bordered table-sm">
                    <tr>
                        <th>Status</th>
                        <th>By</th>
                        <th>On</th>
                    </tr>';

            foreach ($s as  $val) {
                $status     = $val['status'];
                $date       = date('M. d, Y', strtotime($val['date']));
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
                        <td class="' . $color . '">' . $status . '</td>
                        <td>' . $appName . '</td>
                        <td>' . $datime . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }

        if ($cancel) {

            echo '<table class="table table-bordered table-sm">
                    <tr>
                        <th>Cancelled By</th>
                        <th>Position</th>
                        <th>Date</th>
                    </tr>';
            foreach ($cancel as $sel) {
                $date       = date('M. d, Y', strtotime($sel['date']));
                $time       = $sel['time'];
                $hrEmpId    = $sel['hrEmpId'];
                $hrMessage  = $sel['hrMessage'];
                @$datime    = "$date | $time";
                $appNameHR  = $this->promo_model->getName_employee3($hrEmpId);
                $cond       = array('emp_id' => $hrEmpId);
                $positionHR = $this->promo_model->select_rowArray('pis', 'employee3', $cond)['position'];


                echo '<tr>
                        <td>' . $appNameHR . '</td>
                        <td>' . $positionHR . '</td>
                        <td>' . $datime . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }

        echo '<table class="table table-sm" style="width:100%;">';
        if ($s) {
            echo '<tr>
                    <th width="35%">Remark from Supervisor to Employee</th>
                    <td>: ' . $sup_message . '</td>';
            echo '</tr>';
        }

        if ($rthr) {
            $cancelMess = $rthr['message'];

            echo '<tr>
                    <th>Reason for Cancellation</th>
                    <td>: ' . $cancelMess . '</td>';
            echo '</tr>';
        }

        if ($cancel) {

            echo '<tr>     
                    <th>HR Response</th>
                    <td>: ' . $hrMessage . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        echo '</div>';
        echo '</div>';
    }

    public function viewEmpOTP()
    {
        $input      = $this->input->post(NULL, TRUE);
        $pay_id     = $input['pay_id'];
        $c          = $this->promo_model->select_rowArray('tk', 'otforpayment', array('pay_id' => $pay_id));
        $empId      = $c['emp_id'];
        $otLength   = $c['otLength'];
        $otApp      = $c['otApp'];
        $otDis      = $c['otDisapproved'];
        $otDate     = $c['otDate'];
        $dateReq    = $c['dateReq'];
        $status     = $c['status'];
        $approval   = $c['status2'];

        $otD        = date('M. d, Y', strtotime($otDate));
        $dateR      = date('M. d, Y', strtotime($dateReq));

        $cond   = "e.emp_id = '$empId'";
        $emp    = $this->promo_model->promoDetailsRow($cond);
        $app    = $this->promo_model->select_rowArray('pis', 'applicant', ['app_id' => $emp['emp_id']]);

        $photo = explode("../", $app['photo']);
        $photoLink = 'http://' . $_SERVER['SERVER_ADDR'] . ':80' . $_SERVER['SERVER_PORT'] . '/hrms/' . $photo[1];
        if ($emp['current_status'] == 'Active') {
            $stat = 'success';
        } else if ($emp['current_status'] == 'blacklisted') {
            $stat = 'danger';
        } else {
            $stat = 'warning';
        }

        $stores = '';
        $i      = 0;
        $bus    = $this->promo_model->locate_promo_bu($this->hrCode);
        foreach ($bus as $bu) {
            $hasBu = $this->promo_model->empStores('promo_record', $emp['emp_id'], $emp['record_no'], $bu['bunit_field']);
            if ($hasBu) {
                $i++;
                $buName = '<span class="badge mb-1 bg-secondary mr-1">' . $bu['bunit_acronym'] . '</span>';
                $stores = ($i == 1) ? $buName : $stores . $buName;
            }
        }

        if ($status == 'Approved') {
            $color = 'text-success';
        } else if ($status == 'Disapproved') {
            $color = 'text-danger';
        } else if ($status == 'Cancelled') {
            $color = 'text-primary';
        } else if ($status == 'Pending') {
            $color = 'text-warning';
        } else {
            $color = '';
        }

        echo '<div class="row">';

        echo '<div class="col-md-12">';
        echo '<div class="d-flex  gap-2">';
        echo '<img src="' . $photoLink . '" alt="..." class="border rounded shadow p-1 bg-white" width="90" height="90">';
        echo '<table class="table table-sm table-borderless">';
        echo    '<tr>';
        echo    '<th>Employee ID</th>';
        echo    '<td>: ' . $emp['emp_id'] . '</td>';
        echo    '<th>Name</th>';
        echo    '<td>: ' . $emp['name'] . '</td>';
        echo    '</tr>';
        echo    '<tr>';
        echo    '<th>Status</th>';
        echo    '<td>: <span class="badge mb-1 bg-' . $stat . '">' . $emp['current_status'] . '</span></td>';
        echo    '<th>Store(s)</th>';
        echo    '<td>: ' . $stores . '</td>';
        echo    '</tr>';
        echo    '<tr>';
        echo    '<th>Department</th>';
        echo    '<td>: ' . $emp['promo_department'] . '</td>';
        echo    '<th>Company</th>';
        echo    '<td class="text-truncate d-inline-block" style="max-width: 300px;" title="' . $emp['promo_company'] . '">: ' . $emp['promo_company'] . '</td>';
        echo    '</tr>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
        echo '<div class="col-md-12">';
        echo    '<table class="table table-sm table-bordered" style="width:100%;">
                    <tr>
                        <th>Date Overtime</th>
                        <td>' . $otD . '</td>
                        <th>Date Requested</th>
                        <td>' . $dateR . '</td>
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
                        <td><span class="' . $color . '">' . $status . '</span> [' . $approval . ']</span></td>
                    </tr>
                </table>';

        $cond       = array('requestId' => $pay_id, 'requestType' => 'EOP');
        $s          = $this->promo_model->select_resultArray('tk', 'approveby', $cond);
        $rthr       = $this->promo_model->select_rowArray('tk', 'request_to_hr', $cond);
        $hrcancel   = $this->promo_model->select_resultArray('tk', 'hr_canceled_by', $cond);

        if ($s) {
            echo '<table class="table table-bordered table-sm" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>By</th>
                            <th>On</th>
                        </tr>
                    </thead>';

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
                echo    '<tr>
                            <td class="' . $color . '">' . $status . '</td>
                            <td>' . ucwords(strtolower($appName)) . '</td>
                            <td>' . $datime . '</td>
                        </tr>';
            }
            echo '</table>';
        }

        if ($rthr) {

            $payId2 = $rthr['rhId'];
            $cond   = array('requestId' => $payId2, 'requestType' => 'EOP');
            $cancel = $this->promo_model->select_resultArray('tk', 'hr_canceled_by', $cond);
            if ($cancel) {

                echo '<table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Cancelled By</th>
                                <th>Date</th>
                                <th>Position</th>
                            </tr>
                        </thead>';
                foreach ($cancel as $sel) {
                    $date       = $sel['date'];
                    $time       = $sel['time'];
                    $hrEmpId    = $sel['hrEmpId'];
                    $hrMessage  = $sel['hrMessage'];
                    @$datime    = "$date | $time";
                    $a          = $this->promo_model->select_rowArray('pis', 'employee3', array('emp_id' => $hrEmpId));
                    $appNameHR  = ucwords(strtolower($a['name']));
                    $positionHR = $a['position'];

                    echo    '<tr>
                                <td>' . $appNameHR . '</td>
                                <td>' . $datime . '</td>
                                <td>' . $positionHR . '</td>
                            </tr>';
                }
                echo '</table>';
            }
        }

        if ($hrcancel) {

            echo '<table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Cancelled By</th>
                            <th>Date</th>
                            <th>Position</th>
                        </tr>
                    </thead>';
            foreach ($hrcancel as $sel) {
                $date       = $sel['date'];
                $time       = $sel['time'];
                $hrEmpId    = $sel['hrEmpId'];
                $hrMessage  = $sel['hrMessage'];
                @$datime    = "$date | $time";
                $a          = $this->promo_model->select_rowArray('pis', 'employee3', array('emp_id' => $hrEmpId));
                $appNameHR  = ucwords(strtolower($a['name']));
                $positionHR = $a['position'];

                echo    '<tr>
                            <td>' . $appNameHR . '</td>
                            <td>' . $datime . '</td>
                            <td>' . $positionHR . '</td>
                        </tr>';
            }
            echo '</table>';
        }

        echo '<table class="table table-sm" style="width:100%;">';
        if ($rthr) {

            $cancelMess = $rthr['message'];
            echo    '<tr>
                        <th>Reason for Cancellation</th>
                        <th>:</th>
                        <td>' . $cancelMess . '</td>
                    </tr>';
        }

        if ($hrcancel) {

            echo    '<tr>     
                        <th>HR Response</th>
                        <th>:</th>
                        <td>' . $hrMessage . '</td>
                    </tr>';
        }
        echo '</table>';
        echo '</div>';
        echo '</div>';
    }

    public function viewEmpCUA()
    {
        $input  = $this->input->post(NULL, TRUE);
        $eCAId  = $input['eCAId'];
        $c      = $this->promo_model->select_rowArray('tk', 'coverup_auth', ['eCAId' => $eCAId]);

        $empId      = $c['empId'];
        $cuDate     = $c['cuDate'];
        $noHours    = $c['noHours'];
        $cuPurpose  = $c['cuPurpose'];
        $apl        = $c['approval'];
        $dateR      = $c['dateReq'];
        $sup_message    = $c['sup_message'];
        $status     = $c['status'];
        $statusB    = $c['status'];

        $dReq   = date('M. d, Y', strtotime($dateR));
        $cDate  = date('M. d, Y', strtotime($cuDate));

        $cond   = "e.emp_id = '$empId'";
        $emp    = $this->promo_model->promoDetailsRow($cond);
        $app    = $this->promo_model->select_rowArray('pis', 'applicant', ['app_id' => $emp['emp_id']]);

        $photo = explode("../", $app['photo']);
        $photoLink = 'http://' . $_SERVER['SERVER_ADDR'] . ':80' . $_SERVER['SERVER_PORT'] . '/hrms/' . $photo[1];
        if ($emp['current_status'] == 'Active') {
            $stat = 'success';
        } else if ($emp['current_status'] == 'blacklisted') {
            $stat = 'danger';
        } else {
            $stat = 'warning';
        }

        $stores = '';
        $i      = 0;
        $bus    = $this->promo_model->locate_promo_bu($this->hrCode);
        foreach ($bus as $bu) {
            $hasBu = $this->promo_model->empStores('promo_record', $emp['emp_id'], $emp['record_no'], $bu['bunit_field']);
            if ($hasBu) {
                $i++;
                $buName = '<span class="badge mb-1 bg-secondary mr-1">' . $bu['bunit_acronym'] . '</span>';
                $stores = ($i == 1) ? $buName : $stores . $buName;
            }
        }

        if ($status == 'Approved') {
            $color = 'text-success';
        } else if ($status == 'Disapproved') {
            $color = 'text-danger';
        } else if ($status == 'Cancelled') {
            $color = 'text-primary';
        } else if ($status == 'Pending') {
            $color = 'text-warning';
        } else {
            $color = '';
        }

        echo '<div class="row">';

        echo '<div class="col-md-12">';
        echo '<div class="d-flex  gap-2">';
        echo '<img src="' . $photoLink . '" alt="..." class="border rounded shadow p-1 bg-white" width="90" height="90">';
        echo '<table class="table table-sm table-borderless">';
        echo    '<tr>';
        echo    '<th>Employee ID</th>';
        echo    '<td>: ' . $emp['emp_id'] . '</td>';
        echo    '<th>Name</th>';
        echo    '<td>: ' . $emp['name'] . '</td>';
        echo    '</tr>';
        echo    '<tr>';
        echo    '<th>Status</th>';
        echo    '<td>: <span class="badge mb-1 bg-' . $stat . '">' . $emp['current_status'] . '</span></td>';
        echo    '<th>Store(s)</th>';
        echo    '<td>: ' . $stores . '</td>';
        echo    '</tr>';
        echo    '<tr>';
        echo    '<th>Department</th>';
        echo    '<td>: ' . $emp['promo_department'] . '</td>';
        echo    '<th>Company</th>';
        echo    '<td class="text-truncate d-inline-block" style="max-width: 300px;" title="' . $emp['promo_company'] . '">: ' . $emp['promo_company'] . '</td>';
        echo    '</tr>';
        echo '</table>';
        echo '</div>';
        echo '</div>';

        echo '<div class="col-md-12">';
        echo    '<table class="table table-sm table-bordered" style="width:100%;">
                    <tr>
                        <th>Date Cover-Up</th>
                        <td>' . $cDate . '</td>
                        <th>Date Requested</th>
                        <td>' . $dReq . '</td>
                    </tr>
                    <tr>
                        <th>No. of Hours</th>
                        <td>' . $noHours . ' hour(s)</td>
                        <th>Approval</th>
                        <td>' . $apl . '</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td class="' . $color . '">' . $status . '</td>
                        <th>Purpose</th>
                        <td>' . $cuPurpose . '</td>
                    </tr>
                </table>';

        $cond = ['eCAId' => $eCAId];
        $cov = $this->promo_model->select_resultArray('tk', 'coverup_auth_ext', $cond);
        if ($cov) {
            echo '<table class="table table-bordered table-sm" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Date Overtime</th>
                            <th>OT Approved</th>
                            <th>OT used to Cover-Up</th>
                        </tr>
                    </thead>';

            foreach ($cov as  $v) {
                $dateOT = $v['dateOT'];
                $usedOT = $v['usedOT'];
                $cond   = array('empId' => $empId, 'dateOvertime' => $dateOT);
                $eoa    = $this->promo_model->select_rowArray('tk', 'e_o_authorization', $cond);
                if ($eoa) {
                    $otApp  = $eoa['otApp'];
                    $dOT    = date('M. d, Y', strtotime($dateOT));

                    echo    '<tr>
                                <td>' . $dOT . '</td>
                                <td>' . $otApp . '</td>
                                <td>' . $usedOT . ' hour(s)</td>
                            </tr>';
                }
            }
            echo '</table>';
        }

        $cond   = array('requestId' => $eCAId, 'requestType' => 'ECA');
        $s      = $this->promo_model->select_resultArray('tk', 'approveby', $cond);
        $cancel = $this->promo_model->select_resultArray('tk', 'hr_canceled_by', $cond);
        $rthr   = $this->promo_model->select_rowArray('tk', 'request_to_hr', $cond);

        if ($s) {
            echo '<table class="table table-bordered table-sm" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>By</th>
                            <th>On</th>
                        </tr>
                    </thead>';

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
                echo    '<tr>
                            <td class="' . $color . '">' . $status . '</td>
                            <td>' . ucwords(strtolower($appName)) . '</td>
                            <td>' . $datime . '</td>
                        </tr>';
            }
            echo '</table>';
        }

        if ($cancel) {

            echo '<table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Cancelled By</th>
                            <th>Date</th>
                            <th>Position</th>
                        </tr>
                    </thead>';
            foreach ($cancel as $sel) {
                $date       = $sel['date'];
                $time       = $sel['time'];
                $hrEmpId    = $sel['hrEmpId'];
                $hrMessage  = $sel['hrMessage'];
                @$datime    = "$date | $time";
                $a          = $this->promo_model->select_rowArray('pis', 'employee3', array('emp_id' => $hrEmpId));
                $appNameHR  = ucwords(strtolower($a['name']));
                $positionHR = $a['position'];

                echo    '<tr>
                            <td>' . $appNameHR . '</td>
                            <td>' . $datime . '</td>
                            <td>' . $positionHR . '</td>
                        </tr>';
            }
            echo '</table>';
        }

        echo '<table class="table" style="width:100%;">';
        if ($s) {

            echo    '<tr>
                        <th>Remark from Supervisor to Employee</th>
                        <th>:</th>
                        <td>' . $sup_message . '</td>
                    </tr>';
        }

        if ($rthr) {
            $cancelMess = $rthr['message'];

            echo    '<tr>
                        <th>Reason for Cancellation</th>
                        <th>:</th>
                        <td>' . $cancelMess . '</td>
                    </tr>';
        }

        if ($cancel) {

            echo    '<tr>     
                        <th>HR Response</th>
                        <th>:</th>
                        <td>' . $hrMessage . '</td>
                    </tr>';
        }
        echo '</table>';
        echo '</div>';
        echo '</div>';
    }

    public function viewEmpUTA()
    {
        $input  = $this->input->post(NULL, TRUE);
        $eUAId  = $input['eUAId'];
        $c      = $this->promo_model->select_rowArray('tk', 'undertime_auth', ['eUAId' => $eUAId]);

        $empId      = $c['empId'];
        $dateUT     = $c['dateUndertime'];
        $utWhen     = $c['utWhen'];
        $noHours    = $c['numHours'];
        $reason     = $c['reason'];
        $remark     = $c['remark'];
        $dateReq    = $c['dateReq'];
        $sup_message = $c['sup_message'];
        $status     = $c['status'];
        $statusB    = $c['status'];

        $dateU = date('M. d, Y', strtotime($dateUT));
        $dateR = date('M. d, Y', strtotime($dateReq));

        $cond   = "e.emp_id = '$empId'";
        $emp    = $this->promo_model->promoDetailsRow($cond);
        $app    = $this->promo_model->select_rowArray('pis', 'applicant', ['app_id' => $emp['emp_id']]);

        $photo = explode("../", $app['photo']);
        $photoLink = 'http://' . $_SERVER['SERVER_ADDR'] . ':80' . $_SERVER['SERVER_PORT'] . '/hrms/' . $photo[1];
        if ($emp['current_status'] == 'Active') {
            $stat = 'success';
        } else if ($emp['current_status'] == 'blacklisted') {
            $stat = 'danger';
        } else {
            $stat = 'warning';
        }

        $stores = '';
        $i      = 0;
        $bus    = $this->promo_model->locate_promo_bu($this->hrCode);
        foreach ($bus as $bu) {
            $hasBu = $this->promo_model->empStores('promo_record', $emp['emp_id'], $emp['record_no'], $bu['bunit_field']);
            if ($hasBu) {
                $i++;
                $buName = '<span class="badge mb-1 bg-secondary mr-1">' . $bu['bunit_acronym'] . '</span>';
                $stores = ($i == 1) ? $buName : $stores . $buName;
            }
        }

        if ($status == 'Approved') {
            $color = 'text-success';
        } else if ($status == 'Disapproved') {
            $color = 'text-danger';
        } else if ($status == 'Cancelled') {
            $color = 'text-primary';
        } else if ($status == 'Pending') {
            $color = 'text-warning';
        } else {
            $color = '';
        }

        echo '<div class="row">';

        echo '<div class="col-md-12">';
        echo '<div class="d-flex  gap-2">';
        echo '<img src="' . $photoLink . '" alt="..." class="border rounded shadow p-1 bg-white" width="90" height="90">';
        echo '<table class="table table-sm table-borderless">';
        echo    '<tr>';
        echo    '<th>Employee ID</th>';
        echo    '<td>: ' . $emp['emp_id'] . '</td>';
        echo    '<th>Name</th>';
        echo    '<td>: ' . $emp['name'] . '</td>';
        echo    '</tr>';
        echo    '<tr>';
        echo    '<th>Status</th>';
        echo    '<td>: <span class="badge mb-1 bg-' . $stat . '">' . $emp['current_status'] . '</span></td>';
        echo    '<th>Store(s)</th>';
        echo    '<td>: ' . $stores . '</td>';
        echo    '</tr>';
        echo    '<tr>';
        echo    '<th>Department</th>';
        echo    '<td>: ' . $emp['promo_department'] . '</td>';
        echo    '<th>Company</th>';
        echo    '<td class="text-truncate d-inline-block" style="max-width: 300px;" title="' . $emp['promo_company'] . '">: ' . $emp['promo_company'] . '</td>';
        echo    '</tr>';
        echo '</table>';
        echo '</div>';
        echo '</div>';

        echo '<div class="col-md-12">';
        echo    '<table class="table table-sm table-bordered" style="width:100%;">
                    <tr>
                        <th>Date Undertime</th>
                        <td>' . $dateU . '</td>
                        <th>Date Requested</th>
                        <td>' . $dateR . '</td>
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
                        <td class="' . $color . '">' . $status . '</td>
                    </tr>
                    <tr>
                        <th>Reason</th>
                        <td colspan="3">' . $reason . '</td>
                    </tr>
                </table>';

        $cond   = array('requestId' => $eUAId, 'requestType' => 'EUA');
        $s      = $this->promo_model->select_resultArray('tk', 'approveby', $cond);
        $cancel = $this->promo_model->select_resultArray('tk', 'hr_canceled_by', $cond);
        $rthr   = $this->promo_model->select_rowArray('tk', 'request_to_hr', $cond);

        if ($s) {
            echo '<table class="table table-bordered table-sm" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>By</th>
                            <th>On</th>
                        </tr>
                    </thead>';

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
                echo    '<tr>
                            <td class="' . $color . '">' . $status . '</td>
                            <td>' . ucwords(strtolower($appName)) . '</td>
                            <td>' . $datime . '</td>
                        </tr>';
            }
            echo '</table>';
        }

        if ($cancel) {

            echo '<table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Cancelled By</th>
                            <th>Date</th>
                            <th>Position</th>
                        </tr>
                    </thead>';
            foreach ($cancel as $sel) {
                $date       = $sel['date'];
                $time       = $sel['time'];
                $hrEmpId    = $sel['hrEmpId'];
                $hrMessage  = $sel['hrMessage'];
                @$datime    = "$date | $time";
                $a          = $this->promo_model->select_rowArray('pis', 'employee3', array('emp_id' => $hrEmpId));
                $appNameHR  = ucwords(strtolower($a['name']));
                $positionHR = $a['position'];

                echo    '<tr>
                            <td>' . $appNameHR . '</td>
                            <td>' . $datime . '</td>
                            <td>' . $positionHR . '</td>
                        </tr>';
            }
            echo '</table>';
        }

        echo '<table class="table" style="width:100%;">';
        if ($s) {

            echo    '<tr>
                        <th>Remark from Supervisor to Employee</th>
                        <th>:</th>
                        <td>' . $sup_message . '</td>
                    </tr>';
        }

        if ($rthr) {
            $cancelMess = $rthr['message'];

            echo    '<tr>
                        <th>Reason for Cancellation</th>
                        <th>:</th>
                        <td>' . $cancelMess . '</td>
                    </tr>';
        }

        if ($cancel) {

            echo    '<tr>     
                        <th>HR Response</th>
                        <th>:</th>
                        <td>' . $hrMessage . '</td>
                    </tr>';
        }
        echo '</table>';
        echo '</div>';
        echo '</div>';
    }

    public function viewEmpDWA()
    {
        $input  = $this->input->post(NULL, TRUE);
        $dWAId  = $input['dWAId'];
        $c      = $this->promo_model->select_rowArray('tk', 'd_w_adjustment', ['dWAId' => $dWAId]);

        $empId      = $c['empId'];
        $type       = $c['type'];
        $details    = $c['details'];
        $dateFrom   = $c['dateFrom'];
        $dateTo     = $c['dateTo'];
        $noDays     = $c['noDays'];
        $remark     = $c['remark'];
        $dateReq    = $c['dateReq'];
        $sup_message = $c['sup_message'];
        $status     = $c['status'];
        $adj_errfai_id = $c['adj_errfai_id'];


        $dF         = date("M. d, Y", strtotime($dateFrom));
        $dT         = date("M. d, Y", strtotime($dateTo));
        $dateReq    = date("M. d, Y", strtotime($dateReq));
        $dateRen    = '';
        if ($dateFrom == $dateTo) {
            $dateRen = $dF;
        } else {
            $dateRen = $dF . ' - ' . $dT;
        }

        $in1_c  = '';
        $out1_c = '';
        $in2_c  = '';
        $out2_c = '';
        $sched  = '';

        // if ($type == "Failure" || $type == "System Error") {

        $cond   = array('err_fai_id' => $adj_errfai_id);
        $f      = $this->promo_model->select_rowArray('tk', 'dwa_error_failed', $cond);

        $in1    = $f['in1'];
        $out1   = $f['out1'];
        $in2    = $f['in2'];
        $out2   = $f['out2'];


        $in1_V  = '';
        $out1_V = '';
        $in2_V  = '';
        $out2_V = '';

        $cond   = array('err_fai_id' => $adj_errfai_id);
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

        $sched = '(<b><font color = "red">' . $in1_c . $out1_c . $in2_c . $out2_c . '</font></b>)';
        // }


        $cond   = "e.emp_id = '$empId'";
        $emp    = $this->promo_model->promoDetailsRow($cond);
        $app    = $this->promo_model->select_rowArray('pis', 'applicant', ['app_id' => $emp['emp_id']]);

        $photo = explode("../", $app['photo']);
        $photoLink = 'http://' . $_SERVER['SERVER_ADDR'] . ':80' . $_SERVER['SERVER_PORT'] . '/hrms/' . $photo[1];
        if ($emp['current_status'] == 'Active') {
            $stat = 'success';
        } else if ($emp['current_status'] == 'blacklisted') {
            $stat = 'danger';
        } else {
            $stat = 'warning';
        }

        $stores = '';
        $i      = 0;
        $bus    = $this->promo_model->locate_promo_bu($this->hrCode);
        foreach ($bus as $bu) {
            $hasBu = $this->promo_model->empStores('promo_record', $emp['emp_id'], $emp['record_no'], $bu['bunit_field']);
            if ($hasBu) {
                $i++;
                $buName = '<span class="badge mb-1 bg-secondary mr-1">' . $bu['bunit_acronym'] . '</span>';
                $stores = ($i == 1) ? $buName : $stores . $buName;
            }
        }

        if ($status == 'Approved') {
            $color = 'text-success';
        } else if ($status == 'Disapproved') {
            $color = 'text-danger';
        } else if ($status == 'Cancelled') {
            $color = 'text-primary';
        } else if ($status == 'Pending') {
            $color = 'text-warning';
        } else {
            $color = '';
        }

        echo '<div class="row">';

        echo '<div class="col-md-12">';
        echo '<div class="d-flex  gap-2">';
        echo '<img src="' . $photoLink . '" alt="..." class="border rounded shadow p-1 bg-white" width="90" height="90">';
        echo '<table class="table table-sm table-borderless">';
        echo    '<tr>';
        echo    '<th>Employee ID</th>';
        echo    '<td>: ' . $emp['emp_id'] . '</td>';
        echo    '<th>Name</th>';
        echo    '<td>: ' . $emp['name'] . '</td>';
        echo    '</tr>';
        echo    '<tr>';
        echo    '<th>Status</th>';
        echo    '<td>: <span class="badge mb-1 bg-' . $stat . '">' . $emp['current_status'] . '</span></td>';
        echo    '<th>Store(s)</th>';
        echo    '<td>: ' . $stores . '</td>';
        echo    '</tr>';
        echo    '<tr>';
        echo    '<th>Department</th>';
        echo    '<td>: ' . $emp['promo_department'] . '</td>';
        echo    '<th>Company</th>';
        echo    '<td class="text-truncate d-inline-block" style="max-width: 300px;" title="' . $emp['promo_company'] . '">: ' . $emp['promo_company'] . '</td>';
        echo    '</tr>';
        echo '</table>';
        echo '</div>';
        echo '</div>';


        echo '<div class="col-md-12">';
        echo    '<table class="table table-sm table-bordered" style="width:100%;">
                    <tr>
                        <th>Inclusive Dates</th>
                        <td>' . $dateRen . '</td>
                        <th>Date Requested</th>
                        <td>' . $dateReq . '</td>
                    </tr>
                    <tr>
                        <th>Type</th>
                        <td>' . $type . ' ' . $sched . '</td>
                        <th>No. of Days</th>
                        <td>' . $noDays . ' day(s)</td>
                    </tr>
                    <tr>
                        <th>Remark</th>
                        <td>' . $remark . '</td>
                        <th>Status</th>
                        <td class="' . $color . '">' . $status . '</td>
                    </tr>
                    <tr>
                        <th>Reason</th>
                        <td colspan="3">' . $details . '</td>
                    </tr>
                </table>';

        if ($type != "Failure" || $type != "System Error" || $type != "Wrong Mode" || $type != "Wrong User" || $type != "No ID") {
            // $cond   = "empId = '$empId' AND (date BETWEEN '$dateFrom' AND '$dateTo') AND type = '$type'";
            $cond   = ['empId' => $empId, 'btfw_ID' => $dWAId, 'type' => $type];
            $dis    = $this->promo_model->select_resultArray('tk', 'off_date', $cond);
            if ($dis) {
                echo '<table class="table table-bordered table-sm" style="width:100%;">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Day</th>
                            </tr>
                        </thead>';

                foreach ($dis as $d) {
                    $dateO  = $d['date'];
                    $timeS  = $d['timeStarted'];
                    $timeE  = $d['timeEnded'];
                    $day    = $d['day'];
                    $dateO  = date("M. d, Y", strtotime($dateO));
                    echo    '<tr>
                                <td>' . $dateO . '</td>
                                <td>' . $timeS . '</td>
                                <td>' . $timeE . '</td>
                                <td>' . $day . '</td>
                            </tr>';
                }
                echo '</table>';
            }
        }

        $cond   = array('requestId' => $dWAId, 'requestType' => 'DWA');
        $s      = $this->promo_model->select_resultArray('tk', 'approveby', $cond);
        $cancel = $this->promo_model->select_resultArray('tk', 'hr_canceled_by', $cond);
        $rthr   = $this->promo_model->select_rowArray('tk', 'request_to_hr', $cond);

        if ($s) {
            echo '<table class="table table-bordered table-sm" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>By</th>
                            <th>On</th>
                        </tr>
                    </thead>';

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
                echo    '<tr>
                            <td class="' . $color . '">' . $status . '</td>
                            <td>' . ucwords(strtolower($appName)) . '</td>
                            <td>' . $datime . '</td>
                        </tr>';
            }
            echo '</table>';
        }

        if ($cancel) {

            echo '<table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Cancelled By</th>
                            <th>Date</th>
                            <th>Position</th>
                        </tr>
                    </thead>';
            foreach ($cancel as $sel) {
                $date       = $sel['date'];
                $time       = $sel['time'];
                $hrEmpId    = $sel['hrEmpId'];
                $hrMessage  = $sel['hrMessage'];
                @$datime    = "$date | $time";
                $a          = $this->promo_model->select_rowArray('pis', 'employee3', array('emp_id' => $hrEmpId));
                $appNameHR  = ucwords(strtolower($a['name']));
                $positionHR = $a['position'];

                echo    '<tr>
                            <td>' . $appNameHR . '</td>
                            <td>' . $datime . '</td>
                            <td>' . $positionHR . '</td>
                        </tr>';
            }
            echo '</table>';
        }

        echo '<table class="table" style="width:100%;">';
        if ($s) {

            echo    '<tr>
                        <th>Remark from Supervisor to Employee</th>
                        <th>:</th>
                        <td>' . $sup_message . '</td>
                    </tr>';
        }

        if ($rthr) {
            $cancelMess = $rthr['message'];

            echo    '<tr>
                        <th>Reason for Cancellation</th>
                        <th>:</th>
                        <td>' . $cancelMess . '</td>
                    </tr>';
        }

        if ($cancel) {

            echo    '<tr>     
                        <th>HR Response</th>
                        <th>:</th>
                        <td>' . $hrMessage . '</td>
                    </tr>';
        }
        echo '</table>';
        echo '</div>';
        echo '</div>';
    }

    public function viewEmpLOA()
    {
        $input      = $this->input->post(NULL, TRUE);
        $rLAId      = $input['rLAId'];
        $c          = $this->promo_model->select_rowArray('tk', 'r_l_absence', ['rLAId' => $rLAId]);
        $empId      = $c['empId'];
        $dateFrom   = $c['incDate_sF'];
        $dateTo     = $c['incDate_sT'];
        $noDays     = $c['noDays'];
        $type       = $c['typeLeave'];
        $reason     = $c['if_emer_pers'];
        $dateReq    = $c['dateReq'];
        $sup_message = $c['sup_message'];
        $remark     = $c['remark'];
        $status     = $c['status'];


        $dF = date("M. d, Y", strtotime($dateFrom));
        $dT = date("M. d, Y", strtotime($dateTo));
        $dateReq = date("M. d, Y", strtotime($dateReq));

        $dateRen = '';
        if ($dateFrom == $dateTo) {
            $dateRen = $dF;
        } else {
            $dateRen = $dF . ' - ' . $dT;
        }

        $cond   = "e.emp_id = '$empId'";
        $emp    = $this->promo_model->promoDetailsRow($cond);
        $app    = $this->promo_model->select_rowArray('pis', 'applicant', ['app_id' => $emp['emp_id']]);

        $photo = explode("../", $app['photo']);
        $photoLink = 'http://' . $_SERVER['SERVER_ADDR'] . ':80' . $_SERVER['SERVER_PORT'] . '/hrms/' . $photo[1];
        if ($emp['current_status'] == 'Active') {
            $stat = 'success';
        } else if ($emp['current_status'] == 'blacklisted') {
            $stat = 'danger';
        } else {
            $stat = 'warning';
        }

        $stores = '';
        $i      = 0;
        $bus    = $this->promo_model->locate_promo_bu($this->hrCode);
        foreach ($bus as $bu) {
            $hasBu = $this->promo_model->empStores('promo_record', $emp['emp_id'], $emp['record_no'], $bu['bunit_field']);
            if ($hasBu) {
                $i++;
                $buName = '<span class="badge mb-1 bg-secondary mr-1">' . $bu['bunit_acronym'] . '</span>';
                $stores = ($i == 1) ? $buName : $stores . $buName;
            }
        }

        if ($status == 'Approved') {
            $color = 'text-success';
        } else if ($status == 'Disapproved') {
            $color = 'text-danger';
        } else if ($status == 'Cancelled') {
            $color = 'text-primary';
        } else if ($status == 'Pending') {
            $color = 'text-warning';
        } else {
            $color = '';
        }

        echo '<div class="row">';
        echo '<div class="col-md-12">';
        echo '<div class="d-flex  gap-2">';
        echo '<img src="' . $photoLink . '" alt="..." class="border rounded shadow p-1 bg-white" width="90" height="90">';
        echo '<table class="table table-sm table-borderless">';
        echo    '<tr>';
        echo    '<th>Employee ID</th>';
        echo    '<td>: ' . $emp['emp_id'] . '</td>';
        echo    '<th>Name</th>';
        echo    '<td>: ' . $emp['name'] . '</td>';
        echo    '</tr>';
        echo    '<tr>';
        echo    '<th>Status</th>';
        echo    '<td>: <span class="badge mb-1 bg-' . $stat . '">' . $emp['current_status'] . '</span></td>';
        echo    '<th>Store(s)</th>';
        echo    '<td>: ' . $stores . '</td>';
        echo    '</tr>';
        echo    '<tr>';
        echo    '<th>Department</th>';
        echo    '<td>: ' . $emp['promo_department'] . '</td>';
        echo    '<th>Company</th>';
        echo    '<td class="text-truncate d-inline-block" style="max-width: 300px;" title="' . $emp['promo_company'] . '">: ' . $emp['promo_company'] . '</td>';
        echo    '</tr>';
        echo '</table>';
        echo '</div>';
        echo '</div>';

        echo '<div class="col-md-12">';
        echo '<table class="table table-sm table-bordered align-middle" style="width:100%;">
                <tr>
                    <th>Date Requested</th>
                    <td>' . $dateReq . '</td>
                    <th>No. of Days</th>
                    <td>' . $noDays . ' day(s)</td>
                </tr>
                <tr>
                    <th>Remark</th>
                    <td>' . $remark . '</td>
                    <th>Status</th>
                    <td class="' . $color . '">' . $status . '</td>
                </tr>
                <tr>
                    <th>Type</th>
                    <td colspan="3">' . $type . '</td>
                </tr>
                <tr>
                    <th>Reason</th>
                    <td colspan="3">';
        if ($type == 'Personal') {
            echo    '<div class="d-flex align-items-center gap-1">
                        <input type="text" name="reason" value="' . $reason . '" class="form-control form-control-sm" autocomplete="off">
                        <button type="button" onclick="updateReason(\'' . $rLAId . '\')" class="btn btn-sm btn-primary">Update</button>
                    </div>';
        } else {
            echo $reason;
        }

        echo '</td>';
        echo '</tr>';
        echo '</table>';

        $cond   = ['empId' => $empId, 'rLAId' => $rLAId];
        $dis    = $this->promo_model->select_resultArray('tk', 'rla_day', $cond);
        if ($dis) {
            echo '<h5 class="text-theme">Inclusive Date(s)</h5>';
            echo '<table class="table table-bordered table-sm" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Time Started</th>
                            <th>Time Out</th>
                        </tr>
                    </thead>';

            foreach ($dis as $d) {
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

        $cond   = array('requestId' => $rLAId, 'requestType' => 'RLA');
        $s      = $this->promo_model->select_resultArray('tk', 'approveby', $cond);
        $cancel = $this->promo_model->select_resultArray('tk', 'hr_canceled_by', $cond);
        $rthr   = $this->promo_model->select_rowArray('tk', 'request_to_hr', $cond);

        if ($s) {
            echo '<table class="table table-bordered table-sm" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>By</th>
                            <th>On</th>
                        </tr>
                    </thead>';

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
                echo    '<tr>
                            <td class="' . $color . '">' . $status . '</td>
                            <td>' . ucwords(strtolower($appName)) . '</td>
                            <td>' . $datime . '</td>
                        </tr>';
            }
            echo '</table>';
        }

        if ($cancel) {

            echo '<table class="table table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Cancelled By</th>
                            <th>Date</th>
                            <th>Position</th>
                        </tr>
                    </thead>';
            foreach ($cancel as $sel) {
                $date       = $sel['date'];
                $time       = $sel['time'];
                $hrEmpId    = $sel['hrEmpId'];
                $hrMessage  = $sel['hrMessage'];
                @$datime    = "$date | $time";
                $a          = $this->promo_model->select_rowArray('pis', 'employee3', array('emp_id' => $hrEmpId));
                $appNameHR  = ucwords(strtolower($a['name']));
                $positionHR = $a['position'];

                echo    '<tr>
                            <td>' . $appNameHR . '</td>
                            <td>' . $datime . '</td>
                            <td>' . $positionHR . '</td>
                        </tr>';
            }
            echo '</table>';
        }

        echo '<table class="table" style="width:100%;">';
        if ($s) {

            echo    '<tr>
                        <th>Remark from Supervisor to Employee</th>
                        <th>:</th>
                        <td>' . $sup_message . '</td>
                    </tr>';
        }

        if ($rthr) {
            $cancelMess = $rthr['message'];

            echo    '<tr>
                        <th>Reason for Cancellation</th>
                        <th>:</th>
                        <td>' . $cancelMess . '</td>
                    </tr>';
        }

        if ($cancel) {

            echo    '<tr>     
                        <th>HR Response</th>
                        <th>:</th>
                        <td>' . $hrMessage . '</td>
                    </tr>';
        }
        echo '</table>';
        echo '</div>';
        echo '</div>';
    }

    public function viewEmpDOS()
    {
        $input      = $this->input->post(NULL, TRUE);
        $acdId      = $input['acdId'];
        $c          = $this->promo_model->select_rowArray('tk', 'ass_change_do', array('acdId' => $acdId));

        $empId      = $c['empId'];
        $curDay     = $c['dayToChange'];
        $newDay     = $c['dayNew'];
        $status     = $c['status'];
        $details    = $c['details'];
        $curDate    = date("M. d, Y", strtotime($c['dateToChange']));
        $newDate    = date("M. d, Y", strtotime($c['dateNew']));
        $dateReq    = date("M. d, Y", strtotime($c['dateReq']));

        $cond   = "e.emp_id = '$empId'";
        $emp    = $this->promo_model->promoDetailsRow($cond);
        $app    = $this->promo_model->select_rowArray('pis', 'applicant', ['app_id' => $emp['emp_id']]);

        $photo = explode("../", $app['photo']);
        $photoLink = 'http://' . $_SERVER['SERVER_ADDR'] . ':80' . $_SERVER['SERVER_PORT'] . '/hrms/' . $photo[1];
        if ($emp['current_status'] == 'Active') {
            $stat = 'success';
        } else if ($emp['current_status'] == 'blacklisted') {
            $stat = 'danger';
        } else {
            $stat = 'warning';
        }

        $stores = '';
        $i      = 0;
        $bus    = $this->promo_model->locate_promo_bu($this->hrCode);
        foreach ($bus as $bu) {
            $hasBu = $this->promo_model->empStores('promo_record', $emp['emp_id'], $emp['record_no'], $bu['bunit_field']);
            if ($hasBu) {
                $i++;
                $buName = '<span class="badge mb-1 bg-secondary mr-1">' . $bu['bunit_acronym'] . '</span>';
                $stores = ($i == 1) ? $buName : $stores . $buName;
            }
        }

        if ($status == 'Approved') {
            $color = 'text-success';
        } else if ($status == 'Disapproved') {
            $color = 'text-danger';
        } else if ($status == 'Cancelled') {
            $color = 'text-primary';
        } else if ($status == 'Pending') {
            $color = 'text-warning';
        } else {
            $color = '';
        }

        echo '<div class="row">';
        echo '<div class="col-md-12">';
        echo '<div class="d-flex  gap-2">';
        echo '<img src="' . $photoLink . '" alt="..." class="border rounded shadow p-1 bg-white" width="90" height="90">';
        echo '<table class="table table-sm table-borderless">';
        echo    '<tr>';
        echo    '<th>Employee ID</th>';
        echo    '<td>: ' . $emp['emp_id'] . '</td>';
        echo    '<th>Name</th>';
        echo    '<td>: ' . $emp['name'] . '</td>';
        echo    '</tr>';
        echo    '<tr>';
        echo    '<th>Status</th>';
        echo    '<td>: <span class="badge mb-1 bg-' . $stat . '">' . $emp['current_status'] . '</span></td>';
        echo    '<th>Store(s)</th>';
        echo    '<td>: ' . $stores . '</td>';
        echo    '</tr>';
        echo    '<tr>';
        echo    '<th>Department</th>';
        echo    '<td>: ' . $emp['promo_department'] . '</td>';
        echo    '<th>Company</th>';
        echo    '<td class="text-truncate d-inline-block" style="max-width: 300px;" title="' . $emp['promo_company'] . '">: ' . $emp['promo_company'] . '</td>';
        echo    '</tr>';
        echo '</table>';
        echo '</div>';
        echo '</div>';

        echo '<div class="col-md-12">';
        echo    '<table class="table table-sm table-bordered" style="width:100%;">
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
                        <td class="' . $color . '">' . $status . '</td>
                    </tr>
                    <tr>
                        <th>Reason</th>
                        <td colspan="3">' . $details . '</td>
                    </tr>
                </table>';

        $cond   = array('requestId' => $acdId, 'requestType' => 'CDOS');
        $s      = $this->promo_model->select_resultArray('tk', 'approveby', $cond);
        if ($s) {
            echo '<table class="table table-bordered table-sm" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>By</th>
                            <th>On</th>
                        </tr>
                    </thead>';

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
                echo    '<tr>
                            <td class="' . $color . '">' . $status . '</td>
                            <td>' . $appName . '</td>
                            <td>' . $datime . '</td>
                        </tr>';
            }
            echo '</table>';
        }
        echo '</div>';
        echo '</div>';
    }

    public function viewEmpDOL()
    {
        $input      = $this->input->post(NULL, TRUE);
        $adlid      = $input['adlid'];
        $c          = $this->promo_model->select_rowArray('tk', 'ass_dayoff_long', ['adlid' => $adlid]);

        $empId      = $c['empId'];
        $adlid      = $c['adlid'];
        $newDay     = $c['newDay'];
        $curDay     = $c['curDayOff'];
        $eDate      = date("M. d, Y", strtotime($c['effectiveDate']));
        $dateReq    = date("M. d, Y", strtotime($c['dateRequested']));
        $status     = $c['status'];

        $dateReq = date("M. d, Y", strtotime($dateReq));

        $cond   = "e.emp_id = '$empId'";
        $emp    = $this->promo_model->promoDetailsRow($cond);
        $app    = $this->promo_model->select_rowArray('pis', 'applicant', ['app_id' => $emp['emp_id']]);

        $photo = explode("../", $app['photo']);
        $photoLink = 'http://' . $_SERVER['SERVER_ADDR'] . ':80' . $_SERVER['SERVER_PORT'] . '/hrms/' . $photo[1];
        if ($emp['current_status'] == 'Active') {
            $stat = 'success';
        } else if ($emp['current_status'] == 'blacklisted') {
            $stat = 'danger';
        } else {
            $stat = 'warning';
        }

        $stores = '';
        $i      = 0;
        $bus    = $this->promo_model->locate_promo_bu($this->hrCode);
        foreach ($bus as $bu) {
            $hasBu = $this->promo_model->empStores('promo_record', $emp['emp_id'], $emp['record_no'], $bu['bunit_field']);
            if ($hasBu) {
                $i++;
                $buName = '<span class="badge mb-1 bg-secondary mr-1">' . $bu['bunit_acronym'] . '</span>';
                $stores = ($i == 1) ? $buName : $stores . $buName;
            }
        }

        if ($status == 'Approved') {
            $color = 'text-success';
        } else if ($status == 'Disapproved') {
            $color = 'text-danger';
        } else if ($status == 'Cancelled') {
            $color = 'text-primary';
        } else if ($status == 'Pending') {
            $color = 'text-warning';
        } else {
            $color = '';
        }

        echo '<div class="row">';
        echo '<div class="col-md-12">';
        echo '<div class="d-flex  gap-2">';
        echo '<img src="' . $photoLink . '" alt="..." class="border rounded shadow p-1 bg-white" width="90" height="90">';
        echo '<table class="table table-sm table-borderless">';
        echo    '<tr>';
        echo    '<th>Employee ID</th>';
        echo    '<td>: ' . $emp['emp_id'] . '</td>';
        echo    '<th>Name</th>';
        echo    '<td>: ' . $emp['name'] . '</td>';
        echo    '</tr>';
        echo    '<tr>';
        echo    '<th>Status</th>';
        echo    '<td>: <span class="badge mb-1 bg-' . $stat . '">' . $emp['current_status'] . '</span></td>';
        echo    '<th>Store(s)</th>';
        echo    '<td>: ' . $stores . '</td>';
        echo    '</tr>';
        echo    '<tr>';
        echo    '<th>Department</th>';
        echo    '<td>: ' . $emp['promo_department'] . '</td>';
        echo    '<th>Company</th>';
        echo    '<td class="text-truncate d-inline-block" style="max-width: 300px;" title="' . $emp['promo_company'] . '">: ' . $emp['promo_company'] . '</td>';
        echo    '</tr>';
        echo '</table>';
        echo '</div>';
        echo '</div>';

        echo '<div class="col-md-12">';
        echo    '<table class="table table-sm table-bordered" style="width:100%;">
                    <tr>
                        <th>Current Dayoff</th>
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
                        <td colspan="3" class="' . $color . '">' . $status . '</td>
                    </tr>
                </table>';

        $cond   = ['requestId' => $adlid, 'requestType' => 'CDOL'];
        $s      = $this->promo_model->select_resultArray('tk', 'approveby', $cond);
        if ($s) {
            echo '<table class="table table-bordered table-sm" style="width:100%;">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>By</th>
                            <th>On</th>
                        </tr>
                    </thead>';

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
                echo    '<tr>
                            <td class="' . $color . '">' . $status . '</td>
                            <td>' . ucwords(strtolower($appName)) . '</td>
                            <td>' . $datime . '</td>
                        </tr>';
            }
            echo '</table>';
        }

        echo '</div>';
        echo '</div>';
    }

    public function updateReason()
    {
        $input  = $this->input->post(NULL, TRUE);
        $cond   = ['rLAId' => $input['rLAId']];
        $data   = ['if_emer_pers' => $input['reason']];
        $update = $this->promo_model->update('tk', 'r_l_absence', $cond, $data);
        if ($update) {
            echo json_encode(['message' => 'success', 'feedback' => 'Reason has been updated.']);
        } else {
            echo json_encode(['message' => 'failed']);
        }
    }
}
