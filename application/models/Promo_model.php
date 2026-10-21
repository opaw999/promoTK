<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Promo_model extends CI_Model
{
    public $pis;
    public $tk;
    public $vsm;
    public $userId;
    public $userName;
    public $hrCode;
    function __construct()
    {
        parent::__construct();
        $this->pis      = $this->load->database('default', TRUE);
        $this->tk       = $this->load->database('timekeeping', TRUE);
        $this->vsm      = $this->load->database('vsm', TRUE);
        if (isset($_SESSION['audit_guest'])) {
            $_SESSION['tkEmpId'] = $_SESSION['emp_id'];
            $_SESSION['usr'] = 'audit';
        }
        $this->userId   = $_SESSION['tkEmpId'];
        $this->userName = $_SESSION['usr'];

        $cond = array('emp_id' => $_SESSION['tkEmpId']);
        $cc = $this->select_rowArray('pis', 'employee3', $cond)['company_code'];
        $_SESSION['hrCode'] = ($cc != '07') ? 'asc' : 'cebo';
        $this->hrCode = $_SESSION['hrCode'];
    }

    public function user_info()
    {
        $query = $this->pis->select('*')
            ->from('applicant')
            ->join('employee3', 'applicant.app_id = employee3.emp_id')
            ->where('employee3.emp_id', $this->userId)
            ->get();
        return $query->row_array();
    }

    public function getName_employee3($empId)
    {
        $query = $this->pis->select('name')
            ->from('employee3')
            ->where('emp_id', $empId)
            ->get();
        return $query->row_array()['name'];
    }

    public function selectAll($database, $table)
    {
        $db = $this->$database;
        $query = $db->get_where($table);
        return $query->result_array();
    }

    public function select_rowArray($database, $table, $condition)
    {
        $db = $this->$database;
        $query = $db->get_where($table, $condition);
        return $query->row_array();
    }

    public function select_resultArray($database, $table, $condition)
    {
        $db = $this->$database;
        $query = $db->get_where($table, $condition);
        return $query->result_array();
    }

    public function update($database, $table, $condition, $data)
    {
        $db = $this->$database;
        return $db->where($condition)
            ->update($table, $data);
    }

    public function insert($database, $table, $data)
    {
        $db = $this->$database;
        $db->insert($table, $data);
        return $db->insert_id();
    }

    public function delete($database, $table, $condition)
    {
        $db = $this->$database;
        return $db->delete($table, $condition);
    }

    public function locate_promo_bu($hrd_location = null)
    {
        $db = $this->pis;
        $db->select('*')
            ->from('locate_promo_business_unit');
        if ($hrd_location !== null) {
            $db->where('hrd_location', $hrd_location);
        }
        $db->where('status', 'active');
        $db->order_by('bunit_name');
        $query = $db->get();
        return $query->result_array();
    }

    public function cutoff_table($statCut, $table)
    {
        $db = $this->tk;
        $query = $db->select('dateFrom, dateTo')
            ->from($table)
            ->where('statCut', $statCut)
            ->where('dateFrom >=', '2020-01-01')
            ->group_by('dateFrom')
            ->order_by('dateFrom', 'desc')
            ->get();
        return $query->result_array();
    }

    public function cutoff_table2($statCut, $table)
    {
        $df = '2020-01-01';
        $cond = "statCut='$statCut' ORDER BY coId DESC LIMIT 1";
        $date = $this->select_rowArray('tk', 'setup_cutoff_pro', $cond);
        if ($date) {
            $df = $date['dateFrom'];
            $dt = $date['dateTo'];
        }

        $db = $this->tk;
        $query = $db->select('dateFrom, dateTo')
            ->from($table)
            ->where('statCut', $statCut)
            ->where('dateFrom >=', $df)
            ->group_by('dateFrom')
            ->order_by('dateFrom', 'desc')
            ->get();
        return $query->result_array();
    }

    public function process_cutoff_table($statCut, $table)
    {
        $db = $this->tk;
        $query = $db->select('dateFrom, dateTo')
            ->from($table)
            ->where('statCut', $statCut)
            ->where('dateFrom >=', '2020-01-01')
            ->group_by('dateFrom')
            ->order_by('dateFrom', 'desc')
            ->limit(3)
            ->get();
        return $query->result_array();
    }

    public function viewReq_cutoff_table($statCut, $table)
    {
        $db = $this->tk;
        $query = $db->select('dateFrom, dateTo')
            ->from($table)
            ->where('statCut', $statCut)
            // ->where('dateFrom >=', '2020-01-01')
            ->group_by('dateFrom')
            ->order_by('dateFrom', 'desc')
            ->limit(15)
            ->get();
        return $query->result_array();
    }

    public function viewRequest($select, $table, $join, $condition)
    {
        $this->tk->select($select)
            ->from($table . ' as t1')
            ->join('promo_sched_emp as t2', $join)
            ->join('pis.employee3 as t3', 't3.emp_id=t2.empId AND t3.record_no=t2.recordNo')
            ->join('pis.promo_record as t4', 't4.emp_id=t3.emp_id AND t4.record_no=t3.record_no')
            ->where('current_status !=', 'blacklisted')
            ->where_in('emp_type', ['Promo', 'Promo-NESCO'])
            ->where($condition);
        $query = $this->tk->get();
        return $query->result_array();
    }

    public function promo_dtr_list($statCut, $dateFrom, $dateTo)
    {
        $db = $this->tk;
        $empType = ['Promo', 'Promo-NESCO'];
        if (isset($_SESSION['audit_guest'])) {
            $empType = ['Promo-NESCO'];
        }

        $query = $db->select('*')
            ->from('promo_sched_emp as s')
            ->join('pis.employee3 as e', 's.empId=e.emp_id and s.recordNo=e.record_no')
            ->join('pis.promo_record as p', 'p.emp_id=e.emp_id and p.record_no=e.record_no')
            ->join('assign as a', 'a.empId=s.empId')
            ->where('statCut', $statCut)
            ->group_start()
            ->where("current_status = 'Active'")
            ->or_where("current_status = 'Resigned'")
            ->or_where("current_status = 'Ad-Resigned'")
            ->or_where("current_status = 'V-Resigned'")
            ->or_group_start()
            ->or_where("current_status = 'End of Contract'")
            ->where("eocdate BETWEEN '$dateFrom' AND '$dateTo'")
            ->group_end()
            ->group_end()
            ->where_in('emp_type', $empType)
            ->order_by('startdate DESC, e.record_no DESC')
            ->get();
        return $query->result_array();
    }

    public function process_co_list($statCut, $dateFrom, $dateTo)
    {
        $db = $this->tk;
        $query = $db->select('*')
            ->from('promo_sched_emp as s')
            ->join('pis.employee3 as e', 's.empId=e.emp_id and s.recordNo=e.record_no')
            ->join('pis.promo_record as p', 'p.emp_id=e.emp_id and p.record_no=e.record_no')
            ->join('assign as a', 'a.empId=s.empId')
            ->where("a.barcodeId NOT LIKE '%\_%' AND a.bioMetricId NOT LIKE '%\_%'")
            ->where('statCut', $statCut)
            ->where("(current_status='Active' OR (current_status IN ('Ad-Resigned', 'End of Contract', 'V-Resigned') AND sub_status='Uncleared'))")
            ->where_in('emp_type', ['Promo', 'Promo-NESCO'])
            ->order_by('e.name ASC')
            ->group_by('e.emp_id')
            ->get();
        return $query->result_array();
    }

    public function promoDetails($condition)
    {
        $db = $this->pis;
        $query = $db->select('*')
            ->from('employee3 as e')
            ->join('promo_record as p', 'p.emp_id=e.emp_id and p.record_no=e.record_no')
            ->where($condition)
            ->where_in('emp_type', ['Promo', 'Promo-NESCO'])
            ->get();
        return $query->result_array();
    }

    public function promoDetailsprev($condition)
    {
        $db = $this->pis;
        $query = $db->select('*')
            ->from('employmentrecord_ as e')
            ->join('promo_history_record as p', 'p.emp_id=e.emp_id and p.record_no=e.record_no')
            ->where($condition)
            ->where_in('emp_type', ['Promo', 'Promo-NESCO'])
            ->get();
        return $query->result_array();
    }

    public function promoDetailsRow($condition)
    {
        $db = $this->pis;
        $query = $db->select('*')
            ->from('employee3 as e')
            ->join('promo_record as p', 'p.emp_id=e.emp_id and p.record_no=e.record_no')
            ->where($condition)
            ->where_in('emp_type', ['Promo', 'Promo-NESCO'])
            ->get();
        return $query->row_array();
    }

    public function empStores($table, $emp_id, $record_no, $field)
    {
        $db = $this->pis;
        $query = $db->select('COUNT(promo_id) AS num')
            ->get_where($table, array('emp_id' => $emp_id, 'record_no' => $record_no, $field => 'T'));
        return $query->row_array()['num'];
    }

    public function store_entry($bioM, $table, $dateDuty)
    {
        $db = $this->tk;
        $query = $db->select('store')
            ->from($table)
            ->where('bioMetricId', $bioM)
            ->where('dateDuty', $dateDuty)
            ->group_by('store')
            ->order_by("ADDTIME(timeIn,timeOut) ASC")
            ->get();


        $codes = [];
        $stores = array_column($query->result_array(), 'store');
        foreach ($stores as $store) {

            $str = array_search($store, $this->tk_bu());
            if ($str === 'FRP' || $str === 'ALP') {
                $str = 'ALP';
            }

            if (!in_array($str, $codes, true)) {
                $codes[] = $str;
            }
        }

        $code = '';
        foreach ($codes as $codeStr) {
            $code .= '<span class="mr-1" style="font-size:smaller;">' . $codeStr . '</span>';
        }

        return $code;
    }

    public function store_entry2($bioM, $allowId, $dateFrom, $entry, $mode)
    {
        $table = '';

        if ($allowId == 'Biometric ID') {
            $table = 'duty_biometric';
        }

        if ($allowId == 'Logbox ID') {
            $table = 'duty_logbox';
        }

        $time = ($mode == 'I') ? 'timeIn' : 'timeOut';

        $cond = "bioMetricId = '$bioM' AND dateDuty = '$dateFrom' AND $time = '$entry' AND mode = '$mode'";
        $row = $this->select_rowArray('tk', $table, $cond);

        if (empty($row)) {
            $store = '';
        } else {
            $store = $row['store'];
        }

        $code = '';
        if ($store == 'alturas') {
            $code = 'A';
        }
        if ($store == 'alturas_tal') {
            $code = 'B';
        }
        if ($store == 'al_tub') {
            $code = 'C';
        }
        if ($store == 'icm') {
            $code = 'D';
        }
        if ($store == 'plaza_marcela') {
            $code = 'E';
        }
        if ($store == 'col_c') {
            $code = 'F';
        }
        if ($store == 'col_m') {
            $code = 'G';
        }
        if ($store == 'alta_cita') {
            $code = 'H';
        }
        if ($store == 'fixrite_panglao' || $store == 'al_panglao') {
            $code = 'I';
        }
        return $code;
    }

    public function no_sil_current($data)
    {

        $dateFrom   = date('Y-m-d', strtotime($data['dateFrom']));
        $dateTo     = date('Y-m-d', strtotime($data['dateTo']));
        $this->tk->query('SET SESSION sql_mode = ""');

        // ONLY_FULL_GROUP_BY
        $this->tk->query('SET SESSION sql_mode =
                              REPLACE(REPLACE(REPLACE(
                              @@sql_mode,
                              "ONLY_FULL_GROUP_BY,", ""),
                              ",ONLY_FULL_GROUP_BY", ""),
                              "ONLY_FULL_GROUP_BY", "")');

        $r_l_absence = "SELECT COUNT(id) as no_rows FROM r_l_absence WHERE 
                            r_l_absence.typeLeave = 'Personal' AND 
                            (if_emer_pers LIKE '%Service Incentive Leave%' OR if_emer_pers LIKE '%SIL%' OR if_emer_pers LIKE '%S.I.L%') AND 
                            r_l_absence.empId = '" . $data['empId'] . "' AND 
                            fromDate = '" . $dateFrom . "' AND toDate = '" . $dateTo . "' AND r_l_absence.supStatus != 'Previous' AND
                            r_l_absence.status = 'Approved'";
        $r_l_absence = $this->tk->query($r_l_absence);
        $r_l_absence_count = $r_l_absence->row();

        $rla_day = "SELECT COUNT(id) as no_rows FROM r_l_absence, rla_day WHERE 
                            r_l_absence.typeLeave = 'Personal' AND 
                            (if_emer_pers LIKE '%Service Incentive Leave%' OR if_emer_pers LIKE '%SIL%' OR if_emer_pers LIKE '%S.I.L%') AND 
                            r_l_absence.empId = '" . $data['empId'] . "' AND 
                            rla_day.empId = '" . $data['empId'] . "' AND 
                            rla_day.date BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "' AND r_l_absence.supStatus != 'Previous' AND
                            r_l_absence.rLAId = rla_day.rLAId AND r_l_absence.empId = rla_day.empId AND r_l_absence.status = 'Approved'";
        $rla_day = $this->tk->query($rla_day);
        $rla_day_count = $rla_day->row();

        if ($r_l_absence_count->no_rows != $rla_day_count->no_rows) {

            $query = "SELECT COUNT(noDays) as sil FROM r_l_absence, rla_day WHERE 
                                      r_l_absence.typeLeave = 'Personal' AND 
                                      (if_emer_pers LIKE '%Service Incentive Leave%' OR if_emer_pers LIKE '%SIL%' OR if_emer_pers LIKE '%S.I.L%') AND 
                                      r_l_absence.empId = '" . $data['empId'] . "' AND 
                                      rla_day.empId = '" . $data['empId'] . "' AND 
                                      rla_day.date BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "' AND r_l_absence.supStatus != 'Previous' AND
                                      r_l_absence.rLAId = rla_day.rLAId AND r_l_absence.empId = rla_day.empId AND r_l_absence.status = 'Approved'";
            $query = $this->tk->query($query);
        } else {

            $query = "SELECT SUM(noDays) as sil FROM r_l_absence, rla_day WHERE 
                                      r_l_absence.typeLeave = 'Personal' AND 
                                      (if_emer_pers LIKE '%Service Incentive Leave%' OR if_emer_pers LIKE '%SIL%' OR if_emer_pers LIKE '%S.I.L%') AND 
                                      r_l_absence.empId = '" . $data['empId'] . "' AND 
                                      rla_day.empId = '" . $data['empId'] . "' AND 
                                      rla_day.date BETWEEN '" . $dateFrom . "' AND '" . $dateTo . "' AND r_l_absence.supStatus != 'Previous' AND
                                      r_l_absence.rLAId = rla_day.rLAId AND r_l_absence.empId = rla_day.empId AND r_l_absence.status = 'Approved'
                                      LIMIT 1";
            $query = $this->tk->query($query);
        }

        return $query->row_array();
    }

    public function no_sil_previous($data)
    {
        $dateFrom   = date('Y-m-d', strtotime($data['dateFrom']));
        $dateTo     = date('Y-m-d', strtotime($data['dateTo']));
        $this->tk->query('SET SESSION sql_mode = ""');

        // ONLY_FULL_GROUP_BY
        $this->tk->query('SET SESSION sql_mode =
                              REPLACE(REPLACE(REPLACE(
                              @@sql_mode,
                              "ONLY_FULL_GROUP_BY,", ""),
                              ",ONLY_FULL_GROUP_BY", ""),
                              "ONLY_FULL_GROUP_BY", "")');

        $query = "SELECT COUNT(r_l_absence.id) as sil FROM r_l_absence, rla_day WHERE 
                                  r_l_absence.typeLeave = 'Personal' AND 
                                  (if_emer_pers LIKE '%Service Incentive Leave%' OR if_emer_pers LIKE '%SIL%' OR if_emer_pers LIKE '%S.I.L%') AND 
                                  r_l_absence.empId = '" . $data['empId'] . "' AND 
                                  rla_day.empId = '" . $data['empId'] . "' AND 
                                  r_l_absence.supDF = '" . $dateFrom . "' AND r_l_absence.supDT = '" . $dateTo . "' AND r_l_absence.supStatus = 'Previous' AND
                                  r_l_absence.rLAId = rla_day.rLAId AND r_l_absence.empId = rla_day.empId AND r_l_absence.status = 'Approved' AND 
                                  rla_day.date BETWEEN r_l_absence.incDate_sF AND r_l_absence.incDate_sT
                                  GROUP BY rla_day.date";
        $query = $this->tk->query($query);

        return $query->row_array();
    }

    public function dtr_legend()
    {
        return $stores = [
            'ASCM'  => 'Alturas Mall',
            'ALTAL' => 'Alturas Talibon',
            'ALTUB' => 'Alturas Tubigon',
            'ICM'   => 'Island City Mall',
            'PM'    => 'Plaza Marcela',
            'ALTA'  => 'Alta Citta',
            'ALP'   => 'Alturas Panglao',
            'FRP'   => 'Alturas Panglao'
        ];
    }

    public function promoCutoffs($bioM, $statCut)
    {
        $db = $this->tk;
        $date = date('Y-m-d', strtotime("-5 years"));

        $query1 = $db->select('dateFrom, dateTo')
            ->from('1stcutoff')
            ->where('statCut', $statCut)
            ->where('statCut !=', '0')
            ->where('statCut !=', '')
            ->where('bioMetricId', $bioM)
            ->where('dateFrom >', $date)
            ->group_by('dateFrom')
            ->order_by('dateFrom', 'desc')
            ->limit(24)
            ->get();

        $query2 = $db->select('dateFrom, dateTo')
            ->from('2ndcutoff')
            ->where('statCut', $statCut)
            ->where('statCut !=', '0')
            ->where('statCut !=', '')
            ->where('bioMetricId', $bioM)
            ->where('dateFrom >', $date)
            ->group_by('dateFrom')
            ->order_by('dateFrom', 'desc')
            ->limit(24)
            ->get();


        $result1 = $query1->result_array();
        $result2 = $query2->result_array();

        foreach ($result1 as &$r1) {
            $r1['cut'] = 1;
        }
        foreach ($result2 as &$r2) {
            $r2['cut'] = 2;
        }

        $merged = array_merge($result1, $result2);

        usort($merged, function ($a, $b) {
            return strtotime($b['dateFrom']) - strtotime($a['dateFrom']);
        });

        return $merged;
    }

    public function promoCutoffs2($bioM, $statCut)
    {
        $db = $this->tk;
        $date = date('Y-m-d', strtotime("-5 years"));

        $query3 = $db->select('dateFrom, dateTo')
            ->from('1stcutoff')
            ->where('statCut !=', $statCut)
            ->where('statCut !=', '0')
            ->where('statCut !=', '')
            ->where('bioMetricId', $bioM)
            ->where('dateFrom >', $date)
            ->group_by('dateFrom')
            ->order_by('dateFrom', 'desc')
            ->limit(24)
            ->get();

        $query4 = $db->select('dateFrom, dateTo')
            ->from('2ndcutoff')
            ->where('statCut !=', $statCut)
            ->where('statCut !=', '0')
            ->where('statCut !=', '')
            ->where('bioMetricId', $bioM)
            ->where('dateFrom >', $date)
            ->group_by('dateFrom')
            ->order_by('dateFrom', 'desc')
            ->limit(24)
            ->get();

        $result3 = $query3->result_array();
        $result4 = $query4->result_array();


        foreach ($result3 as &$r3) {
            $r3['cut'] = 1;
        }
        foreach ($result4 as &$r4) {
            $r4['cut'] = 2;
        }

        $merged = array_merge($result3, $result4);

        usort($merged, function ($a, $b) {
            return strtotime($b['dateFrom']) - strtotime($a['dateFrom']);
        });

        return $merged;
    }

    public function searchPromo($string)
    {
        $query = $this->pis->select('*')
            ->from('employee3 as e')
            ->join('promo_record as p', 'p.emp_id=e.emp_id and p.record_no=e.record_no')
            ->join('timekeeping.promo_sched_emp as s', 's.empId=e.emp_id and s.recordNo=e.record_no')
            ->join('timekeeping.promo_schedule as ps', 'ps.statCut=s.statCut')
            ->join('timekeeping.assign as a', 'a.empId=s.empId')
            ->where('hr_location', $this->hrCode)
            // ->where('current_status !=', 'blacklisted')
            ->where_in('emp_type', ['Promo', 'Promo-NESCO'])
            ->group_start()
            ->like('name', $string, 'both')
            ->or_like('e.emp_id', $string)
            ->group_end()
            ->order_by('name')
            ->limit(10)
            ->get();

        return $query->result_array();
    }

    public function add_searchPromo($string)
    {
        $query = $this->pis->select('*')
            ->from('employee3 as e')
            ->join('promo_record as p', 'p.emp_id=e.emp_id and p.record_no=e.record_no')
            ->join('timekeeping.promo_sched_emp as s', 's.empId=e.emp_id and s.recordNo=e.record_no')
            ->join('timekeeping.promo_schedule as ps', 'ps.statCut=s.statCut')
            ->join('timekeeping.assign as a', 'a.empId=s.empId')
            ->where('hr_location', $this->hrCode)
            ->where('promo_type', 'ROVING')
            ->where('current_status !=', 'blacklisted')
            ->where_in('emp_type', ['Promo', 'Promo-NESCO'])
            ->group_start()
            ->like('name', $string, 'both')
            ->or_like('e.emp_id', $string)
            ->group_end()
            ->order_by('name')
            ->limit(10)
            ->get();

        return $query->result_array();
    }

    public function setup_searchPromo($string)
    {
        $query = $this->pis->select('*')
            ->from('employee3 as e')
            ->join('promo_record as p', 'p.emp_id=e.emp_id and p.record_no=e.record_no')
            ->join('timekeeping.promo_sched_emp as s', 's.empId=e.emp_id and s.recordNo=e.record_no')
            ->join('timekeeping.assign as a', 'a.empId=e.emp_id')
            ->where('hr_location', $this->hrCode)
            ->where('current_status !=', 'blacklisted')
            ->where_in('emp_type', ['Promo', 'Promo-NESCO'])
            ->group_start()
            ->like('name', $string, 'both')
            ->or_like('e.emp_id', $string)
            ->group_end()
            ->order_by('name')
            ->limit(10)
            ->get();

        return $query->result_array();
    }

    public function searchManualInputs($string)
    {
        $query = $this->pis->select('*')
            ->from('employee3 as e')
            ->join('promo_record as p', 'p.emp_id=e.emp_id and p.record_no=e.record_no')
            ->join('timekeeping.promo_sched_emp as s', 's.empId=e.emp_id and s.recordNo=e.record_no')
            ->join('timekeeping.assign as a', 'a.empId=e.emp_id')
            ->where('hr_location', $this->hrCode)
            ->where('current_status !=', 'blacklisted')
            ->where_in('emp_type', ['Promo', 'Promo-NESCO'])
            ->group_start()
            ->like('name', $string, 'both')
            ->or_like('e.emp_id', $string)
            ->group_end()
            ->order_by('name')
            ->limit(10)
            ->get();

        return $query->result_array();
    }

    public function view_searchPromo($string)
    {
        $query = $this->pis->select('*')
            ->from('employee3 as e')
            ->join('promo_record as p', 'p.emp_id=e.emp_id and p.record_no=e.record_no')
            ->join('timekeeping.promo_sched_emp as s', 's.empId=e.emp_id and s.recordNo=e.record_no')
            ->join('timekeeping.assign as a', 'a.empId=e.emp_id')
            ->where('hr_location', $this->hrCode)
            ->where('current_status !=', 'blacklisted')
            ->where_in('emp_type', ['Promo', 'Promo-NESCO'])
            ->group_start()
            ->like('name', $string, 'both')
            ->or_like('e.emp_id', $string)
            ->or_group_start()
            ->where('a.bioMetricId', $string)
            ->or_where('a.barcodeId', $string)
            ->group_end()
            ->group_end()
            ->order_by('name')
            ->limit(10)
            ->get();

        return $query->result_array();
    }

    public function concerns_searchPromo($string)
    {
        $query = $this->pis->select('*')
            ->from('employee3 as e')
            ->join('promo_record as p', 'p.emp_id=e.emp_id and p.record_no=e.record_no')
            ->join('timekeeping.promo_sched_emp as s', 's.empId=e.emp_id and s.recordNo=e.record_no')
            ->join('timekeeping.assign as a', 'a.empId=e.emp_id')
            ->where('hr_location', $this->hrCode)
            ->where('current_status !=', 'blacklisted')
            ->where_in('emp_type', ['Promo', 'Promo-NESCO'])
            ->group_start()
            ->like('name', $string, 'both')
            ->or_like('e.emp_id', $string)
            ->group_end()
            ->order_by('name')
            ->limit(10)
            ->get();

        return $query->result_array();
    }

    public function searchEmployee($string)
    {
        $query = $this->pis->select('*')
            ->from('employee3 as e')
            ->join('timekeeping.assign as a', 'a.empId=e.emp_id')
            ->where('current_status', 'Active')
            ->group_start()
            ->like('name', $string, 'both')
            ->or_like('e.emp_id', $string)
            ->group_end()
            ->order_by('name')
            ->limit(10)
            ->get();

        return $query->result_array();
    }

    public function masterfileSearch($string)
    {
        $query = $this->pis->select('*')
            ->from('employee3 as e')
            ->join('promo_record as p', 'p.emp_id=e.emp_id and p.record_no=e.record_no')
            ->join('timekeeping.promo_sched_emp as s', 's.empId=e.emp_id and s.recordNo=e.record_no')
            ->where('hr_location', $this->hrCode)
            ->where('current_status !=', 'blacklisted')
            ->where_in('emp_type', ['Promo', 'Promo-NESCO'])
            ->group_start()
            ->like('name', $string, 'both')
            ->or_like('e.emp_id', $string)
            ->group_end()
            ->order_by('name')
            ->limit(10)
            ->get();

        return $query->result_array();
    }

    public function promo_hr_start()
    {
        $query = $this->tk->select('*')
            ->from('promo_hr_start as p')
            ->join('pis.employee3 as e', 'p.empId=e.emp_id')
            ->where('current_status', 'Active')
            ->order_by('name')
            ->group_by('p.empId')
            ->get();

        return $query->result_array();
    }

    public function csFirst($df, $dt, $statCut)
    {
        $query = $this->tk->select('*')
            ->from('promo_sched_emp as s')
            ->join('pis.employee3 as e', 's.empId=e.emp_id and s.recordNo=e.record_no')
            ->join('pis.promo_record as p', 'p.emp_id=e.emp_id and p.record_no=e.record_no')
            ->join('assign as ass', 'ass.empId = s.empId')
            ->join('assign_mul_sc as a', 'a.empId = s.empId')
            ->join('assign_mul_sc_exp as b', 'b.empId = a.empId AND b.amsId = a.amsId')
            ->join('request_new_shift as c', 'c.amsId = b.amsId AND c.amsId = a.amsId')
            ->where('statCut', $statCut)
            ->where('a.status', 'active')
            ->where('a.status2', 'Approved')
            ->where('a.remark', '')
            ->where("b.date BETWEEN '$df' AND '$dt'")
            ->where("(current_status='Active' OR (current_status IN ('Ad-Resigned', 'End of Contract', 'V-Resigned') AND sub_status='Uncleared'))")
            ->where_in('emp_type', ['Promo', 'Promo-NESCO'])
            ->order_by('a.empId')
            ->group_by('a.empId,a.amsId')
            ->get();

        return $query->result_array();
    }

    public function odSecond($df, $dt, $statCut)
    {
        $query = $this->tk->select('a.amsId,a.empId')
            ->from('promo_sched_emp as s')
            ->join('pis.employee3 as e', 's.empId=e.emp_id and s.recordNo=e.record_no')
            ->join('pis.promo_record as p', 'p.emp_id=e.emp_id and p.record_no=e.record_no')
            ->join('assign as ass', 'ass.empId = s.empId')
            ->join('assign_mul_sc as a', 'a.empId = s.empId')
            ->join('request_odaf as b', 'a.amsId = b.amsId')
            ->where('statCut', $statCut)
            ->where('a.status', 'active')
            ->where('a.status2', 'Approved')
            ->where("((a.dateFrom BETWEEN '$df' AND '$dt') OR (a.dateTo BETWEEN '$df' AND '$dt') OR ('$df' BETWEEN a.dateFrom AND a.dateTo) OR ('$dt' BETWEEN a.dateFrom AND a.dateTo))")
            ->where("(current_status='Active' OR (current_status IN ('Ad-Resigned', 'End of Contract', 'V-Resigned') AND sub_status='Uncleared'))")
            ->where_in('emp_type', ['Promo', 'Promo-NESCO'])
            ->order_by('a.empId')
            ->group_by('a.amsId')
            ->get();

        return $query->result_array();
    }

    public function tk_stores()
    {
        return [
            'alturas'           => 'Alturas Mall',
            'alturas_tal'       => 'Alturas Talibon',
            'al_tub'            => 'Alturas Tubigon',
            'icm'               => 'Island City Mall',
            'plaza_marcela'     => 'Plaza Marcela',
            'alta_cita'         => 'Alta Citta',
            'fixrite_panglao'   => 'Fixrite Panglao',
            'alturas_panglao'   => 'Alturas Panglao',
            'col_c'             => 'Colonnade Colon',
            'col_m'             => 'Colonnade Mandaue',
            'col_mh'            => 'Colonnade Mohon',
        ];
    }

    public function tk_bu()
    {
        return [
            'ASCM'  => 'alturas',
            'ALTAL' => 'alturas_tal',
            'ALTUB' => 'al_tub',
            'FRTUB' => 'al_tub',
            'ICM'   => 'icm',
            'PMDS'  => 'plaza_marcela',
            'ALTA'  => 'alta_cita',
            'FRP'   => 'fixrite_panglao',
            'ALP'   => 'alturas_panglao',
            'COLC'  => 'col_c',
            'COLM'  => 'col_m',
            'COLMH' => 'col_mh',
            'WH'    => 'warehouse',
        ];
    }

    public function bu_process($server)
    {
        if ($server == 'corporate') {
            return [
                'Alturas Mall'      => 'ASCM',
                'Island City Mall'  => 'ICM',
                'Plaza Marcela'     => 'PMDS',
                'Alta Citta'        => 'ALTA',
                'Alturas Panglao'   => 'ALP'
            ];
        } else if ($server == 'cebo') {
            return [
                'col_c' => 'COLC',
                'col_m' => 'COLM',
                'col_mh' => 'COLMH'
            ];
        } else if ($server == 'talibon') {
            return ['alturas_tal' => 'ALTAL',];
        } else if ($server == 'tubigon') {
            return ['al_tub' => 'ALTUB', 'al_tub' => 'FRTUB'];
        }
    }

    public function duty_temp_user($table)
    {
        $this->tk->query("CREATE TABLE IF NOT EXISTS `$table` (
        `dtId` int(255) NOT NULL AUTO_INCREMENT,
        `bioMetricId` varchar(255) NOT NULL,
        `dateRendered` varchar(50) NOT NULL,
        `in1` varchar(50) NOT NULL,
        `out1` varchar(50) NOT NULL,
        `in2` varchar(50) NOT NULL,
        `out2` varchar(50) NOT NULL,
        `in3` varchar(50) NOT NULL,
        `out3` varchar(50) NOT NULL,
        PRIMARY KEY (`dtId`),
        KEY `bioMetricId` (`bioMetricId`),
        KEY `dateRendered` (`dateRendered`),
        KEY `in1` (`in1`),
        KEY `out1` (`out1`),
        KEY `in2` (`in2`),
        KEY `out2` (`out2`),
        KEY `in3` (`in3`),
        KEY `out3` (`out3`))");
    }

    public function perday_temp_user($table)
    {
        $this->tk->query("CREATE TABLE IF NOT EXISTS `$table` (
        `pdId` int(255) NOT NULL AUTO_INCREMENT,
        `dutyId` varchar(255) NOT NULL,
        `absence` varchar(50) NOT NULL,
        `utIn1` varchar(50) NOT NULL,
        `utIn2` varchar(50) NOT NULL,
        `in1` varchar(50) NOT NULL,
        `out1` varchar(50) NOT NULL,
        PRIMARY KEY (`pdId`),
        KEY `dutyId` (`dutyId`),
        KEY `absence` (`absence`),
        KEY `utIn1` (`utIn1`),
        KEY `utIn2` (`utIn2`),
        KEY `in1` (`in1`),
        KEY `out1` (`out1`))");
    }

    public function dupduty_user($table)
    {
        $sql = "CREATE TABLE IF NOT EXISTS `$table` (
			  `dup2dutyId` int(255) NOT NULL AUTO_INCREMENT,
			  `bioMetricId` varchar(255) NOT NULL,
			  `date` varchar(255) NOT NULL,
			  `dateDuty` varchar(50) NOT NULL,
			  `timeIn` varchar(50) NOT NULL,
			  `timeOut` varchar(50) NOT NULL,
			  `dateTimeIn` varchar(20) NOT NULL,
			  `dateTimeOut` varchar(20) NOT NULL,			  
			  `mode` varchar(50) NOT NULL,
			  PRIMARY KEY (`dup2dutyId`),
			  KEY `bioMetricId` (`bioMetricId`),
			  KEY `date` (`date`),
			  KEY `dateDuty` (`dateDuty`),
			  KEY `timeIn` (`timeIn`),
			  KEY `timeOut` (`timeOut`),
			  KEY `dateTimeIn` (`dateTimeIn`),
			  KEY `dateTimeOut` (`dateTimeOut`),			  
			  KEY `mode` (`mode`)
			)";

        return $this->tk->query($sql);
    }


    public function drop_table($table)
    {
        return $this->tk->query("DROP TABLE $table");
    }

    public function truncate_table($table)
    {
        return $this->tk->query("TRUNCATE TABLE $table");
    }

    public function join_ttjcoA_tk($select, $table1, $table2, $join, $left = null, $condition = null, $order = null, $limit = null)
    {
        $this->tk->select($select)
            ->from($table1 . ' as t1');
        if ($left !== null) {
            $this->tk->join($table2 . ' as t2', $join, $left);
        } else {
            $this->tk->join($table2 . ' as t2', $join);
        }
        if ($condition !== null) {
            $this->tk->where($condition);
        }
        if ($order !== null) {
            list($orderColumn, $orderDirection) = explode('|', $order);
            $this->tk->order_by($orderColumn, $orderDirection);
        }
        if ($limit !== null) {
            $this->tk->limit($limit);
        }
        $query = $this->tk->get();
        return $query->result_array();
    }

    public function setupSupForm($condition)
    {
        $query = $this->pis->select('*')
            ->from('employee3 as e')
            ->join('leveling_subordinates as l', 'e.emp_id = l.ratee')
            ->group_by('l.ratee')
            ->where($condition)
            ->get();
        return $query->result_array();
    }

    public function pendingReq($table, $join)
    {
        $this->tk->select('COUNT(*) as count')
            ->from($table . ' as t1')
            ->join('promo_sched_emp as t2', $join)
            ->join('pis.employee3 as t3', 't3.emp_id=t2.empId AND t3.record_no=t2.recordNo')
            ->join('pis.promo_record as t4', 't4.emp_id=t3.emp_id AND t4.record_no=t3.record_no')
            ->where('current_status !=', 'blacklisted')
            ->where_in('emp_type', ['Promo', 'Promo-NESCO'])
            ->where('status', 'Pending')
            ->where('YEAR(dateReq)', date('Y'));

        $row = $this->tk->get()->row_array();

        return (int) $row['count'];
    }

    public function add_searchotdwa($string)
    {
        $query = $this->pis->select('*')
            ->from('employee3 as e')
            ->join('promo_record as p', 'p.emp_id=e.emp_id and p.record_no=e.record_no')
            ->join('timekeeping.promo_sched_emp as s', 's.empId=e.emp_id and s.recordNo=e.record_no')
            ->join('timekeeping.assign as a', 'a.empId=s.empId')
            ->where('hr_location', $this->hrCode)
            ->where('current_status !=', 'blacklisted')
            ->where_in('emp_type', ['Promo', 'Promo-NESCO'])
            ->group_start()
            ->like('name', $string, 'both')
            ->or_like('e.emp_id', $string)
            ->group_end()
            ->order_by('name')
            ->limit(10)
            ->get();

        return $query->result_array();
    }
}
