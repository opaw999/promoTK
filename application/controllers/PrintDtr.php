<?php
defined('BASEPATH') or exit('No direct script access allowed');

class PrintDtr extends CI_Controller
{
    public $today;
    public $adminUser;
    public $userId;
    public $hrCode;
    public $ppdf;
    function __construct()
    {
        parent::__construct();
        $this->today        = date('Y-m-d');
        $this->adminUser    = array('01186-2023');
        $this->userId   = $_SESSION['emp_id'];
        $this->hrCode   = $_SESSION['hrCode'];
        $this->load->library('ppdf');
        if (!isset($this->userId)) {
            redirect('http://' . $_SERVER['SERVER_ADDR'] . ":80" . $_SERVER['SERVER_PORT'] . '/hrms');
        }
    }

    public function printDtr()
    {
        $fetch = $this->input->get(NULL, TRUE);
        // var_dump($fetch);
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
        $this->ppdf->AddPage();

        // set margins
        $this->ppdf->SetMargins(12, 12, 12);
        $this->ppdf->SetHeaderMargin('PDF_MARGIN_HEADER');
        $this->ppdf->SetFooterMargin('PDF_MARGIN_FOOTER');

        $empId      = $fetch['empId'];
        $bioM       = $fetch['bioM'];
        $dF         = date('Y-m-d', strtotime($fetch['dateFrom']));
        $dT         = date('Y-m-d', strtotime($fetch['dateTo']));
        $statCut    = $fetch['cutOff'];

        $table1 = 'employee3';
        $table2 = 'promo_record';
        $cond   = "e.emp_id = '$empId'";
        $emp    = $this->promo_HRaccount_model->promoDetails_wttR($cond, $table1, $table2);
        $name   = ucwords(strtolower($emp['name']));
        $comp   = $emp['promo_company'];
        $dept   = $emp['promo_department'];
        $ptype  = $emp['promo_type'];

        $cond = "empId = '$empId' ORDER BY assignId DESC LIMIT 1";
        $allowId = $this->promo_HRaccount_model->selectAll_tcR_tk('assign', $cond)['allowId'];

        $dw = 0;
        $ot = 0;
        $lh = 0;
        $sh = 0;
        $tbl = ($fetch['cut'] == 1) ? '1stcutoff' : '2ndcutoff';
        $cond = array('bioMetricId' => $bioM, 'dateFrom' => $dF, 'dateTo' => $dT, 'statCut' => $statCut);
        $co = $this->promo_HRaccount_model->selectAll_tcR_tk($tbl, $cond);
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
        }

        $sil = 0;
        $cu = $this->promo_HRaccount_model->no_sil_current($fetch)['sil'];
        $sil_cu = ($cu) ? $cu : 0;
        $prev = $this->promo_HRaccount_model->no_sil_previous($fetch)['sil'];
        $sil_prev = ($prev) ? $prev : 0;
        $sil = $sil_prev + $sil_cu;

        $this->ppdf->SetFont('times', 'B', 10, 'L');
        $this->ppdf->Cell(180, 5, 'Integrated Daily Time Record for Promo & Merchandiser', 0, 1, 'C');
        $this->ppdf->Cell(180, 5, 'For the Period of ' . date("F d, Y", strtotime($dF)) . ' to ' . date("F d, Y", strtotime($dT)), 0, 1, 'C');
        $this->ppdf->Ln(2);
        $this->ppdf->SetFont('times', 'B', 9, 'L');

        $this->ppdf->Cell(20, 5, 'Name', 0, 0, 'L');
        $this->ppdf->Cell(90, 5, ': ' . $name, 0, 0, 'L');
        $this->ppdf->SetX(125);
        $this->ppdf->Cell(0, 5, "Days Worked");
        $this->ppdf->SetX(150);
        $this->ppdf->Cell(0, 5, ": $dw");
        $this->ppdf->SetX(165);
        $this->ppdf->Cell(0, 5, "Legal Holiday");
        $this->ppdf->SetX(190);
        $this->ppdf->Cell(0, 5, ": $lh");
        $this->ppdf->Ln(5);
        $y1 = $this->ppdf->GetY();
        $this->ppdf->Cell(20, 5, 'Company', 0, 0, 'L');
        $this->ppdf->MultiCell(90, 5, ": $comp", 0, 'L');
        $y2 = $this->ppdf->GetY();
        $this->ppdf->SetY($y1);
        $this->ppdf->SetX(125);
        $this->ppdf->Cell(0, 5, "Overtime");
        $this->ppdf->SetX(150);
        $this->ppdf->Cell(0, 5, ": $ot");
        $this->ppdf->SetX(165);
        $this->ppdf->Cell(0, 5, "SIL");
        $this->ppdf->SetX(190);
        $this->ppdf->Cell(0, 5, ": $sil");
        $this->ppdf->Ln(5);
        $this->ppdf->SetX(125);
        $this->ppdf->Cell(0, 5, "Special Holiday");
        $this->ppdf->SetX(150);
        $this->ppdf->Cell(0, 5, ": $sh");
        $this->ppdf->SetY($y2);
        $this->ppdf->Cell(20, 5, 'Department', 0, 0, 'L');
        $this->ppdf->Cell(90, 5, ": $dept", 0, 0, 'L');

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

        $this->ppdf->Ln(6);
        $this->ppdf->SetFont('times', '', 10, 'L');

        $dateFrom   = $fetch['dateFrom'];
        $dateTo     = $fetch['dateTo'];
        while (strtotime($dateFrom) <= strtotime($dateTo)) {

            $tempDate   = date('Y-m-d', strtotime($dateFrom));
            $date       = date("m/d/Y", strtotime($dateFrom));
            $day        = date("D", strtotime($dateFrom));

            $dutyId = '';
            $in1    = $out1 = $in2 = $out2 = $in3 = $out3 = '';
            $cond   = "bioMetricId = '$bioM' AND date = '$tempDate' ORDER BY dutyId DESC";
            $dtr    = $this->promo_HRaccount_model->selectAll_tcR_tk('duty', $cond);
            $dutyId = $dtr['dutyId'];
            $in1    = $dtr['in1'];
            $out1   = $dtr['out1'];
            $in2    = $dtr['in2'];
            $out2   = $dtr['out2'];
            $in3    = $dtr['in3'];
            $out3   = $dtr['out3'];

            $in1_store  = $out1_store = $in2_store = $out2_store    = $in3_store = $out3_store = '';
            $in1_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $in1, "I");
            $out1_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $out1, "O");
            $in2_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $in2, "I");
            $out2_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $out2, "O");
            $in3_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $in3, "I");
            $out3_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $out3, "O");

            if (empty($out3_store)) {
                $out3_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, date('Y-m-d', strtotime($tempDate . " +1 day")), $out3, "O");
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

            $cond   = "bioMetricId = '$bioM' AND dateDuty = '$tempDate'";
            $dutyl  = $this->promo_HRaccount_model->selectAll_tcR_tk('duty_logbox', $cond);

            $dw = 0;
            $hw = 0;
            $cond = array('dutyId' => $dutyId);
            $perday = $this->promo_HRaccount_model->selectAll_tcR_tk('perday', $cond);
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
            $otApp = $this->promo_HRaccount_model->selectAll_tcR_tk('otforpayment', $cond)['otApp'];
            if ($otApp && $otApp != '' && $otApp != 0) {
                $ot = floatval($otApp) * 1;
            }
            // overtime ********************************************************************

            // holiday	********************************************************************
            $holiday = false;
            $cond = "date = '$tempDate' AND moveDate = '' LIMIT 1";
            $chk1 = $this->promo_HRaccount_model->selectAll_tcR_tk('holidays_promo', $cond);
            if ($chk1) {
                $holiday = true;
                $hol    = $chk1['nameHoliday'] . ' [ ' . $chk1['category'] . ' ]';
            }

            $cond = "moveDate = '$tempDate' LIMIT 1";
            $chk2 = $this->promo_HRaccount_model->selectAll_tcR_tk('holidays_promo', $cond);
            if ($chk2) {
                $holiday = true;
                $hol    = $chk2['nameHoliday'] . ' [ ' . $chk2['category'] . ' ]';
            }
            // holiday ********************************************************************

            // offduty of holiday ********************************************************************
            $hd = '';
            $odaHoliday = false;
            if ($chk1 || $chk2) {

                $cond  = "empId = '$empId' AND '$tempDate' BETWEEN dateFrom AND dateTo AND remark != ''";
                $sql   = $this->promo_HRaccount_model->selectAll_tcA_tk('assign_mul_sc', $cond);
                foreach ($sql as $ams) {
                    $amsId  = $ams['amsId'];
                    $cond   = array('amsId' => $amsId);
                    $sql    = $this->promo_HRaccount_model->selectAll_tcR_tk('request_odaf', $cond);
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
            $sql = $this->promo_HRaccount_model->selectAll_tcA_tk('assign_mul_sc', $cond);
            if ($sql) {
                foreach ($sql as $ams) {

                    $amsId  = $ams['amsId'];
                    $cond   = array('amsId' => $amsId, 'empId' => $empId, 'date' => $tempDate);
                    $amd    = $this->promo_HRaccount_model->selectAll_tcR_tk('ass_mul_do', $cond);

                    $cond   = array('empId' => $empId, 'dateToChange' => $tempDate, 'status' => 'Approved');
                    $acd    = $this->promo_HRaccount_model->selectAll_tcR_tk('ass_change_do', $cond);

                    if ($amd && !$acd) {
                        $show_do = true;
                    }

                    if ($amd && $acd) {
                        $acdId  = $acd['acdId'];
                        $cond   = array('requestId' => $acdId, 'requestType' => 'CDOS');
                        $aprby  = $this->promo_HRaccount_model->selectAll_tcR_tk('approveby', $cond);
                        if ($aprby) {
                            $dateReq    = $aprby['date'];
                            $timeReq    = $aprby['time'];
                            $app_details = date("Y-m-d H:i:s", strtotime("$dateReq $timeReq"));

                            $cond   = array('amsId' => $amsId);
                            $rns    = $this->promo_HRaccount_model->selectAll_tcR_tk('request_new_shift', $cond);
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
                    $acd    = $this->promo_HRaccount_model->selectAll_tcR_tk('ass_change_do', $cond);
                    if ($acd) {
                        $acdId  = $acd['acdId'];
                        $cond   = array('requestId' => $acdId, 'requestType' => 'CDOS');
                        $aprby  = $this->promo_HRaccount_model->selectAll_tcR_tk('approveby', $cond);
                        if ($aprby) {
                            $dateReq    = $aprby['date'];
                            $timeReq    = $aprby['time'];
                            $app_details = date("Y-m-d H:i:s", strtotime("$dateReq $timeReq"));

                            $cond   = array('amsId' => $amsId);
                            $rns    = $this->promo_HRaccount_model->selectAll_tcR_tk('request_new_shift', $cond);
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
                $acd    = $this->promo_HRaccount_model->selectAll_tcR_tk('ass_change_do', $cond);
                if ($acd) {
                    $show_do = true;
                }

                $cond   = array('empId' => $empId, 'dateToChange' => $tempDate, 'status' => 'Approved');
                $acd    = $this->promo_HRaccount_model->selectAll_tcR_tk('ass_change_do', $cond);
                if ($acd) {
                    $old_do = true;
                }
            }

            // default day-off if $show_do is false
            if (!$sql &&  !$show_do &&  !$old_do) {
                $days = date('l', strtotime($tempDate));
                $cond = array('empId' => $empId, 'day' => date('l', strtotime($tempDate)));
                $sql = $this->promo_HRaccount_model->selectAll_tcR_tk('assign_day_off', $cond);
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
                $sql   = $this->promo_HRaccount_model->selectAll_tcA_tk('assign_mul_sc', $cond);
                foreach ($sql as $ams) {
                    $amsId  = $ams['amsId'];
                    $cond   = array('amsId' => $amsId);
                    $sql    = $this->promo_HRaccount_model->selectAll_tcR_tk('request_odaf', $cond);
                    if ($sql) {
                        $do = $amsId;
                        $odaDayoff = true;
                    }
                }
            }
            // offduty ********************************************************************   

            // no schedule ********************************************************************
            $no_schedule = false;
            if ($ptype == 'ROVING') {
                $nStore = '';
                $cond   = "empId = '$empId' AND dateRendered = '$tempDate' ORDER BY pId DESC";
                $pms    = $this->promo_HRaccount_model->selectAll_tcR_tk('promo_mul_sc', $cond);
                if ($pms) {
                    $nStore = $pms['noStore'];
                }

                if ($nStore == '') {
                    $cond   = "empId = '$empId' AND day_sched = '$day' ORDER BY pdId DESC";
                    $pds    = $this->promo_HRaccount_model->selectAll_tcR_tk('promo_def_sched', $cond);
                    if ($pds) {
                        $nStore = $pds['noStore'];
                    }
                    if ($nStore != '') {
                        $no_schedule = true;
                    }
                } else {
                    if ($nStore == 0) {
                        $no_schedule = true;
                    }
                }
            } else {

                $cond = "empId = '$empId' AND status = 'active' AND status2 = 'Approved' AND '$tempDate' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
                $amsId = $this->promo_HRaccount_model->selectAll_tcR_tk('assign_mul_sc', $cond)['amsId'];
                if ($amsId) {
                    $cond  = array('empId' => $empId, 'amsId' => $amsId, 'date' => $tempDate);
                    $pns   = $this->promo_HRaccount_model->selectAll_tcR_tk('promo_nosched', $cond);
                    if ($pns) {
                        $no_schedule = true;
                    }
                }
            }
            // no schedule ********************************************************************

            $loa = false;
            $cond = "r_l_absence.empId = '$empId' AND r_l_absence.empId = rla_day.empId AND rla_day.date = '$tempDate' 
            AND '$tempDate' BETWEEN r_l_absence.incDate_sF AND r_l_absence.incDate_sT AND (status = 'Approved' OR status = 'Pending')";
            $sqlloa = $this->promo_HRaccount_model->selectAll_tcR_tk('r_l_absence, rla_day', $cond);
            if (count($sqlloa) != 0) {
                $loa = true;
            }

            $sil = false;
            if ($loa) {
                $cpnd = "r_l_absence.empId = '$empId' AND r_l_absence.empId = rla_day.empId AND rla_day.date = '$tempDate' 
                AND '$tempDate' BETWEEN r_l_absence.incDate_sF AND r_l_absence.incDate_sT AND (status = 'Approved' OR status = 'Pending') 
                AND (if_emer_pers LIKE '%Service Incentive Leave%' OR if_emer_pers LIKE '%SIL%' OR if_emer_pers LIKE '%S.I.L%')";
                $sqlsil = $this->promo_HRaccount_model->selectAll_tcR_tk('r_l_absence, rla_day', $cpnd);
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

        // Previous Cut Off
        $dF     = date('Y-m-d', strtotime($fetch['dateFrom']));
        $dT     = date('Y-m-d', strtotime($fetch['dateTo']));
        $cond   = "empId = '$empId' AND coDF1 = '$dF' AND coDF2 = '$dT'";
        $pdp    = $this->promo_HRaccount_model->selectAll_tcA_tk('perday_previous', $cond);
        $cond   = array('empId' => $empId, 'dateFrom' => $dF, 'dateTo' => $dT);
        $allow  = $this->promo_HRaccount_model->selectAll_tcA_tk('perday_previous_allow', $cond);
        if ($pdp && $allow) {
            $this->ppdf->SetFont('times', 'B', 9, 'L');
            $this->ppdf->Cell(20, 5, 'Previous Days Worked', 0, 0, 'L');
            $this->ppdf->SetFont('times', '', 9, 'L');
            $this->ppdf->Ln(2);
            $this->ppdf->Cell(20, 5, '____________________________________________________________________________________________________________________', 0, 0, '');
            $this->ppdf->Ln(6);

            foreach ($pdp as $nf) {

                $dateDuty   = $nf['dateDuty'];
                $hw_new     = floatval($nf['hw_new']) * 1;
                $hw_con     = $nf['hw_con'] + 0;
                $request    = $nf['request'];
                $dayS       = date("D", strtotime($dateDuty));
                $dateD      = date("m/d/Y", strtotime($dateDuty));

                $cond   = "bioMetricId = '$bioM' AND date = '$dateDuty'";
                $dtr    = $this->promo_HRaccount_model->selectAll_tcR_tk('duty', $cond);
                $dutyId = $dtr['dutyId'];
                $in1    = $dtr['in1'];
                $out1   = $dtr['out1'];
                $in2    = $dtr['in2'];
                $out2   = $dtr['out2'];
                $in3    = $dtr['in3'];
                $out3   = $dtr['out3'];

                // store entry
                // $in1_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $in1, "I");
                // $out1_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $out1, "O");
                // $in2_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $in2, "I");
                // $out2_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $out2, "O");
                // $in3_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $in3, "I");
                // $out3_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $out3, "O");

                if (empty($out3_store)) {
                    $out3_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, date('Y-m-d', strtotime($dateDuty . " +1 day")), $out3, "O");
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

                $inC1 = $in1;
                $inC2 = $in2;
                $inC3 = $in3;
                $outC1 = $out1;
                $outC2 = $out2;
                $outC3 = $out3;
                $cond = array('empId' => $empId, 'date' => $dateDuty);
                $od = $this->promo_HRaccount_model->selectAll_tcR_tk('off_date', $cond);
                if ($od) {

                    $inC1 = '';
                    $inC2 = '';
                    $inC3 = '';
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

                    $outC1 = '';
                    $outC2 = '';
                    $outC3 = '';
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

                $this->ppdf->Cell(22, 3, $dateD, 0, 0);
                $this->ppdf->Cell(11, 3, $dayS, 0, 0);
                $this->ppdf->Cell(20, 3, $inC1, 0, 0);
                $this->ppdf->Cell(20, 3, $outC1, 0, 0);
                $this->ppdf->Cell(20, 3, $inC2, 0, 0);
                $this->ppdf->Cell(20, 3, $outC2, 0, 0);
                $this->ppdf->Cell(20, 3, $inC3, 0, 0);
                $this->ppdf->Cell(20, 3, $outC3, 0, 0);
                $this->ppdf->Cell(12, 3, $hw_new, 0, 0);
                $this->ppdf->Cell(12, 3, $hw_con, 0, 0);
                $this->ppdf->Cell(12, 3, "0", 0, 0);
                $this->ppdf->Ln('4');
            }
        }

        // Previous OT
        $dF     = date('Y-m-d', strtotime($fetch['dateFrom']));
        $dT     = date('Y-m-d', strtotime($fetch['dateTo']));
        $cond   = "emp_id = '$empId' AND supDF = '$dF' AND supDT = '$dT' AND otDate < '$dF'";
        $otp    = $this->promo_HRaccount_model->selectAll_tcA_tk('otforpayment', $cond); // for update pani siya 6/4/2025
        if ($otp) {
            $this->ppdf->SetFont('times', 'B', 9, 'L');
            $this->ppdf->Cell(20, 5, 'Previous OT', 0, 0, 'L');
            $this->ppdf->SetFont('times', '', 9, 'L');
            $this->ppdf->Ln(2);
            $this->ppdf->Cell(20, 5, '__________________________________________________________________________________________________________________', 0, 0, '');
            $this->ppdf->Ln(6);

            foreach ($otp as $ot) {

                $dateDuty   = $ot['otDate'];
                $otApproved = floatval($ot['otApp']) * 1;
                $dayS       = date("D", strtotime($dateDuty));
                $dateD      = date("m/d/Y", strtotime($dateDuty));

                $cond   = "bioMetricId = '$bioM' AND date = '$dateDuty'";
                $dtr    = $this->promo_HRaccount_model->selectAll_tcR_tk('duty', $cond);
                $dutyId = $dtr['dutyId'];
                $in1    = $dtr['in1'];
                $out1   = $dtr['out1'];
                $in2    = $dtr['in2'];
                $out2   = $dtr['out2'];
                $in3    = $dtr['in3'];
                $out3   = $dtr['out3'];

                // store entry
                $in1_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $in1, "I");
                $out1_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $out1, "O");
                $in2_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $in2, "I");
                $out2_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $out2, "O");
                $in3_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $in3, "I");
                $out3_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $out3, "O");

                if (empty($out3_store)) {
                    $out3_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, date('Y-m-d', strtotime($dateDuty . " +1 day")), $out3, "O");
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

                $this->ppdf->Cell(22, 3, $dateD, 0, 0);
                $this->ppdf->Cell(11, 3, $dayS, 0, 0);
                $this->ppdf->Cell(20, 3, $in1_store . '' . $in1, 0, 0);
                $this->ppdf->Cell(20, 3, $out1_store . '' . $out1, 0, 0);
                $this->ppdf->Cell(20, 3, $in2_store . '' . $in2, 0, 0);
                $this->ppdf->Cell(20, 3, $out2_store . '' . $out2, 0, 0);
                $this->ppdf->Cell(20, 3, $in3_store . '' . $in3, 0, 0);
                $this->ppdf->Cell(20, 3, $out3_store . '' . $out3, 0, 0);
                $this->ppdf->Cell(12, 3, '0', 0, 0);
                $this->ppdf->Cell(12, 3, '0', 0, 0);
                $this->ppdf->Cell(12, 3, $otApproved, 0, 0);
                $this->ppdf->Ln('4');
            }
        }

        $this->ppdf->Cell(20, 5, '____________________________________________________________________________________________________________________', 0, 0, '');
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

        if ($pdp && $otp) {
            $this->ppdf->Ln(24);
        } else if ($pdp || $otp) {
            $this->ppdf->Ln(26);
        } else {
            $this->ppdf->Ln(28);
        }

        ob_clean();
        $this->ppdf->Output('promo_dtr.pdf', 'I');
    }

    public function printDtrAll()
    {
        $fetch = $this->input->get(NULL, TRUE);
        // var_dump($fetch);
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
        $this->ppdf->AddPage();

        // set margins
        $this->ppdf->SetMargins(12, 12, 12);
        $this->ppdf->SetHeaderMargin('PDF_MARGIN_HEADER');
        $this->ppdf->SetFooterMargin('PDF_MARGIN_FOOTER');
        $i          = 0;
        $dF         = date('Y-m-d', strtotime($fetch['dateFrom']));
        $dT         = date('Y-m-d', strtotime($fetch['dateTo']));
        $cutoff     = ($fetch['cutoff'] == 1) ? '1stcutoff' : '2ndcutoff';
        $statCut    = $fetch['statCut'];
        $empIds     = $fetch['empIds'];
        foreach ($empIds as $empId) {
            $i++;
            $table1 = 'employee3';
            $table2 = 'promo_record';
            $cond   = "e.emp_id = '$empId'";
            $emp    = $this->promo_HRaccount_model->promoDetails_wttR($cond, $table1, $table2);
            $name   = ucwords(strtolower($emp['name']));
            $comp   = $emp['promo_company'];
            $dept   = $emp['promo_department'];
            $ptype  = $emp['promo_type'];

            $cond   = "empId = '$empId' ORDER BY assignId DESC LIMIT 1";
            $assign = $this->promo_HRaccount_model->selectAll_tcR_tk('assign', $cond);
            $allowId = $assign['allowId'];
            if ($allowId == "Biometric ID") {
                $bioM = $assign['bioMetricId'];
            }

            if ($allowId == "Logbox ID") {
                $bioM = $assign['barcodeId'];
            }

            $dw = 0;
            $ot = 0;
            $lh = 0;
            $sh = 0;
            $cond = array('bioMetricId' => $bioM, 'dateFrom' => $dF, 'dateTo' => $dT, 'statCut' => $statCut);
            $co = $this->promo_HRaccount_model->selectAll_tcR_tk($cutoff, $cond);
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
            }

            $data = array('empId' => $empId, 'dateFrom' => $dF, 'dateTo' => $dT);
            $sil = 0;
            $cu = $this->promo_HRaccount_model->no_sil_current($data)['sil'];
            $sil_cu = ($cu) ? $cu : 0;
            $prev = $this->promo_HRaccount_model->no_sil_previous($data)['sil'];
            $sil_prev = ($prev) ? $prev : 0;
            $sil = $sil_prev + $sil_cu;

            $this->ppdf->SetFont('times', 'B', 10, 'L');
            $this->ppdf->Cell(180, 5, 'Integrated Daily Time Record for Promo & Merchandiser', 0, 1, 'C');
            $this->ppdf->Cell(180, 5, 'For the Period of ' . date("F d, Y", strtotime($dF)) . ' to ' . date("F d, Y", strtotime($dT)), 0, 1, 'C');
            $this->ppdf->Ln(2);
            $this->ppdf->SetFont('times', 'B', 9, 'L');

            $this->ppdf->Cell(20, 5, 'Name', 0, 0, 'L');
            $this->ppdf->Cell(90, 5, ': ' . $name, 0, 0, 'L');
            $this->ppdf->SetX(125);
            $this->ppdf->Cell(0, 5, "Days Worked");
            $this->ppdf->SetX(150);
            $this->ppdf->Cell(0, 5, ": $dw");
            $this->ppdf->SetX(165);
            $this->ppdf->Cell(0, 5, "Legal Holiday");
            $this->ppdf->SetX(190);
            $this->ppdf->Cell(0, 5, ": $lh");
            $this->ppdf->Ln(5);
            $y1 = $this->ppdf->GetY();
            $this->ppdf->Cell(20, 5, 'Company', 0, 0, 'L');
            $this->ppdf->MultiCell(90, 5, ": $comp", 0, 'L');
            $y2 = $this->ppdf->GetY();
            $this->ppdf->SetY($y1);
            $this->ppdf->SetX(125);
            $this->ppdf->Cell(0, 5, "Overtime");
            $this->ppdf->SetX(150);
            $this->ppdf->Cell(0, 5, ": $ot");
            $this->ppdf->SetX(165);
            $this->ppdf->Cell(0, 5, "SIL");
            $this->ppdf->SetX(190);
            $this->ppdf->Cell(0, 5, ": $sil");
            $this->ppdf->Ln(5);
            $this->ppdf->SetX(125);
            $this->ppdf->Cell(0, 5, "Special Holiday");
            $this->ppdf->SetX(150);
            $this->ppdf->Cell(0, 5, ": $sh");
            $this->ppdf->SetY($y2);
            $this->ppdf->Cell(20, 5, 'Department', 0, 0, 'L');
            $this->ppdf->Cell(90, 5, ": $dept", 0, 0, 'L');

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

            $this->ppdf->Ln(6);
            $this->ppdf->SetFont('times', '', 10, 'L');

            $dateFrom   = $fetch['dateFrom'];
            $dateTo     = $fetch['dateTo'];
            while (strtotime($dateFrom) <= strtotime($dateTo)) {

                $tempDate   = date('Y-m-d', strtotime($dateFrom));
                $date       = date("m/d/Y", strtotime($dateFrom));
                $day        = date("D", strtotime($dateFrom));

                $dutyId = '';
                $in1    = $out1 = $in2 = $out2 = $in3 = $out3 = '';
                $cond   = "bioMetricId = '$bioM' AND date = '$tempDate' ORDER BY dutyId DESC";
                $dtr    = $this->promo_HRaccount_model->selectAll_tcR_tk('duty', $cond);
                $dutyId = $dtr['dutyId'];
                $in1    = $dtr['in1'];
                $out1   = $dtr['out1'];
                $in2    = $dtr['in2'];
                $out2   = $dtr['out2'];
                $in3    = $dtr['in3'];
                $out3   = $dtr['out3'];

                $in1_store  = $out1_store = $in2_store = $out2_store    = $in3_store = $out3_store = '';
                $in1_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $in1, "I");
                $out1_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $out1, "O");
                $in2_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $in2, "I");
                $out2_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $out2, "O");
                $in3_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $in3, "I");
                $out3_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $out3, "O");

                if (empty($out3_store)) {
                    $out3_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, date('Y-m-d', strtotime($tempDate . " +1 day")), $out3, "O");
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

                $cond   = "bioMetricId = '$bioM' AND dateDuty = '$tempDate'";
                $dutyl  = $this->promo_HRaccount_model->selectAll_tcR_tk('duty_logbox', $cond);

                $dw = 0;
                $hw = 0;
                $cond = array('dutyId' => $dutyId);
                $perday = $this->promo_HRaccount_model->selectAll_tcR_tk('perday', $cond);
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
                $otApp = $this->promo_HRaccount_model->selectAll_tcR_tk('otforpayment', $cond)['otApp'];
                if ($otApp && $otApp != '' && $otApp != 0) {
                    $ot = floatval($otApp) * 1;
                }
                // overtime ********************************************************************

                // holiday	********************************************************************
                $holiday = false;
                $cond = "date = '$tempDate' AND moveDate = '' LIMIT 1";
                $chk1 = $this->promo_HRaccount_model->selectAll_tcR_tk('holidays_promo', $cond);
                if ($chk1) {
                    $holiday = true;
                    $hol    = $chk1['nameHoliday'] . ' [ ' . $chk1['category'] . ' ]';
                }

                $cond = "moveDate = '$tempDate' LIMIT 1";
                $chk2 = $this->promo_HRaccount_model->selectAll_tcR_tk('holidays_promo', $cond);
                if ($chk2) {
                    $holiday = true;
                    $hol    = $chk2['nameHoliday'] . ' [ ' . $chk2['category'] . ' ]';
                }
                // holiday ********************************************************************

                // offduty of holiday ********************************************************************
                $hd = '';
                $odaHoliday = false;
                if ($chk1 || $chk2) {

                    $cond  = "empId = '$empId' AND '$tempDate' BETWEEN dateFrom AND dateTo AND remark != ''";
                    $sql   = $this->promo_HRaccount_model->selectAll_tcA_tk('assign_mul_sc', $cond);
                    foreach ($sql as $ams) {
                        $amsId  = $ams['amsId'];
                        $cond   = array('amsId' => $amsId);
                        $sql    = $this->promo_HRaccount_model->selectAll_tcR_tk('request_odaf', $cond);
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
                $sql = $this->promo_HRaccount_model->selectAll_tcA_tk('assign_mul_sc', $cond);
                if ($sql) {
                    foreach ($sql as $ams) {

                        $amsId  = $ams['amsId'];
                        $cond   = array('amsId' => $amsId, 'empId' => $empId, 'date' => $tempDate);
                        $amd    = $this->promo_HRaccount_model->selectAll_tcR_tk('ass_mul_do', $cond);

                        $cond   = array('empId' => $empId, 'dateToChange' => $tempDate, 'status' => 'Approved');
                        $acd    = $this->promo_HRaccount_model->selectAll_tcR_tk('ass_change_do', $cond);

                        if ($amd && !$acd) {
                            $show_do = true;
                        }

                        $cond   = array('empId' => $empId, 'dateNew' => $tempDate, 'status' => 'Approved');
                        $acd    = $this->promo_HRaccount_model->selectAll_tcR_tk('ass_change_do', $cond);
                        if ($acd) {
                            $acdId  = $acd['acdId'];
                            $cond   = array('requestId' => $acdId, 'requestType' => 'CDOS');
                            $aprby  = $this->promo_HRaccount_model->selectAll_tcR_tk('approveby', $cond);
                            if ($aprby) {
                                $dateReq    = $aprby['date'];
                                $timeReq    = $aprby['time'];
                                $app_details = date("Y-m-d H:i:s", strtotime("$dateReq $timeReq"));

                                $cond   = array('amsId' => $amsId);
                                $rns    = $this->promo_HRaccount_model->selectAll_tcR_tk('request_new_shift', $cond);
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
                    $acd    = $this->promo_HRaccount_model->selectAll_tcR_tk('ass_change_do', $cond);
                    if ($acd) {
                        $show_do = true;
                    }

                    $cond   = array('empId' => $empId, 'dateToChange' => $tempDate, 'status' => 'Approved');
                    $acd    = $this->promo_HRaccount_model->selectAll_tcR_tk('ass_change_do', $cond);
                    if ($acd) {
                        $old_do = true;
                    }
                }

                // default day-off if $show_do is false
                if (!$sql &&  !$show_do &&  !$old_do) {
                    $days = date('l', strtotime($tempDate));
                    $cond = array('empId' => $empId, 'day' => date('l', strtotime($tempDate)));
                    $sql = $this->promo_HRaccount_model->selectAll_tcR_tk('assign_day_off', $cond);
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
                    $sql   = $this->promo_HRaccount_model->selectAll_tcA_tk('assign_mul_sc', $cond);
                    foreach ($sql as $ams) {
                        $amsId  = $ams['amsId'];
                        $cond   = array('amsId' => $amsId);
                        $sql    = $this->promo_HRaccount_model->selectAll_tcR_tk('request_odaf', $cond);
                        if ($sql) {
                            $do = $amsId;
                            $odaDayoff = true;
                        }
                    }
                }
                // offduty ********************************************************************   

                // no schedule ********************************************************************
                $no_schedule = false;
                if ($ptype == 'ROVING') {
                    $nStore = '';
                    $cond   = "empId = '$empId' AND dateRendered = '$tempDate' ORDER BY pId DESC";
                    $pms    = $this->promo_HRaccount_model->selectAll_tcR_tk('promo_mul_sc', $cond);
                    if ($pms) {
                        $nStore = $pms['noStore'];
                    }

                    if ($nStore == '') {
                        $cond   = "empId = '$empId' AND day_sched = '$day' ORDER BY pdId DESC";
                        $pds    = $this->promo_HRaccount_model->selectAll_tcR_tk('promo_def_sched', $cond);
                        if ($pds) {
                            $nStore = $pds['noStore'];
                        }
                        if ($nStore != '') {
                            $no_schedule = true;
                        }
                    } else {
                        if ($nStore == 0) {
                            $no_schedule = true;
                        }
                    }
                } else {

                    $cond = "empId = '$empId' AND status = 'active' AND status2 = 'Approved' AND '$tempDate' BETWEEN dateFrom AND dateTo ORDER BY id DESC";
                    $amsId = $this->promo_HRaccount_model->selectAll_tcR_tk('assign_mul_sc', $cond)['amsId'];
                    if ($amsId) {
                        $cond  = array('empId' => $empId, 'amsId' => $amsId, 'date' => $tempDate);
                        $pns   = $this->promo_HRaccount_model->selectAll_tcR_tk('promo_nosched', $cond);
                        if ($pns) {
                            $no_schedule = true;
                        }
                    }
                }
                // no schedule ********************************************************************

                $loa = false;
                $cond = "r_l_absence.empId = '$empId' AND r_l_absence.empId = rla_day.empId AND rla_day.date = '$tempDate' 
                AND '$tempDate' BETWEEN r_l_absence.incDate_sF AND r_l_absence.incDate_sT AND (status = 'Approved' OR status = 'Pending')";
                $sqlloa = $this->promo_HRaccount_model->selectAll_tcR_tk('r_l_absence, rla_day', $cond);
                if (count($sqlloa) != 0) {
                    $loa = true;
                }

                $sil = false;
                if ($loa) {
                    $cpnd = "r_l_absence.empId = '$empId' AND r_l_absence.empId = rla_day.empId AND rla_day.date = '$tempDate' 
                    AND '$tempDate' BETWEEN r_l_absence.incDate_sF AND r_l_absence.incDate_sT AND (status = 'Approved' OR status = 'Pending') 
                    AND (if_emer_pers LIKE '%Service Incentive Leave%' OR if_emer_pers LIKE '%SIL%' OR if_emer_pers LIKE '%S.I.L%')";
                    $sqlsil = $this->promo_HRaccount_model->selectAll_tcR_tk('r_l_absence, rla_day', $cpnd);
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
                        }

                        if ($in3 == '' && $out3 == '' &&  $check == 2) {
                            $this->ppdf->Cell(40, 3, $leave, 0, 0, 'C');
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

            // Previous Cut Off
            $dF     = date('Y-m-d', strtotime($fetch['dateFrom']));
            $dT     = date('Y-m-d', strtotime($fetch['dateTo']));
            $cond   = "empId = '$empId' AND coDF1 = '$dF' AND coDF2 = '$dT'";
            $pdp    = $this->promo_HRaccount_model->selectAll_tcA_tk('perday_previous', $cond);
            $cond   = array('empId' => $empId, 'dateFrom' => $dF, 'dateTo' => $dT);
            $allow  = $this->promo_HRaccount_model->selectAll_tcA_tk('perday_previous_allow', $cond);
            if ($pdp && $allow) {
                $this->ppdf->SetFont('times', 'B', 9, 'L');
                $this->ppdf->Cell(20, 5, 'Previous Days Worked', 0, 0, 'L');
                $this->ppdf->SetFont('times', '', 9, 'L');
                $this->ppdf->Ln(2);
                $this->ppdf->Cell(20, 5, '____________________________________________________________________________________________________________________', 0, 0, '');
                $this->ppdf->Ln(6);

                foreach ($pdp as $nf) {

                    $dateDuty   = $nf['dateDuty'];
                    $hw_new     = floatval($nf['hw_new']) * 1;
                    $hw_con     = $nf['hw_con'] + 0;
                    $request    = $nf['request'];
                    $dayS       = date("D", strtotime($dateDuty));
                    $dateD      = date("m/d/Y", strtotime($dateDuty));

                    $cond   = "bioMetricId = '$bioM' AND date = '$dateDuty'";
                    $dtr    = $this->promo_HRaccount_model->selectAll_tcR_tk('duty', $cond);
                    $dutyId = $dtr['dutyId'];
                    $in1    = $dtr['in1'];
                    $out1   = $dtr['out1'];
                    $in2    = $dtr['in2'];
                    $out2   = $dtr['out2'];
                    $in3    = $dtr['in3'];
                    $out3   = $dtr['out3'];

                    // store entry
                    // $in1_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $in1, "I");
                    // $out1_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $out1, "O");
                    // $in2_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $in2, "I");
                    // $out2_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $out2, "O");
                    // $in3_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $in3, "I");
                    // $out3_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $out3, "O");

                    if (empty($out3_store)) {
                        $out3_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, date('Y-m-d', strtotime($dateDuty . " +1 day")), $out3, "O");
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

                    $inC1 = $in1;
                    $inC2 = $in2;
                    $inC3 = $in3;
                    $outC1 = $out1;
                    $outC2 = $out2;
                    $outC3 = $out3;
                    $cond = array('empId' => $empId, 'date' => $dateDuty);
                    $od = $this->promo_HRaccount_model->selectAll_tcR_tk('off_date', $cond);
                    if ($od) {

                        $inC1 = '';
                        $inC2 = '';
                        $inC3 = '';
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

                        $outC1 = '';
                        $outC2 = '';
                        $outC3 = '';
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

                    $this->ppdf->Cell(22, 3, $dateD, 0, 0);
                    $this->ppdf->Cell(11, 3, $dayS, 0, 0);
                    $this->ppdf->Cell(20, 3, $inC1, 0, 0);
                    $this->ppdf->Cell(20, 3, $outC1, 0, 0);
                    $this->ppdf->Cell(20, 3, $inC2, 0, 0);
                    $this->ppdf->Cell(20, 3, $outC2, 0, 0);
                    $this->ppdf->Cell(20, 3, $inC3, 0, 0);
                    $this->ppdf->Cell(20, 3, $outC3, 0, 0);
                    $this->ppdf->Cell(12, 3, $hw_new, 0, 0);
                    $this->ppdf->Cell(12, 3, $hw_con, 0, 0);
                    $this->ppdf->Cell(12, 3, "0", 0, 0);
                    $this->ppdf->Ln('4');
                }
            }

            // Previous OT
            $dF     = date('Y-m-d', strtotime($fetch['dateFrom']));
            $dT     = date('Y-m-d', strtotime($fetch['dateTo']));
            $cond   = "emp_id = '$empId' AND supDF = '$dF' AND supDT = '$dT' AND otDate < '$dF'";
            $otp    = $this->promo_HRaccount_model->selectAll_tcA_tk('otforpayment', $cond); // for update pani siya 6/4/2025
            if ($otp) {
                $this->ppdf->SetFont('times', 'B', 9, 'L');
                $this->ppdf->Cell(20, 5, 'Previous OT', 0, 0, 'L');
                $this->ppdf->SetFont('times', '', 9, 'L');
                $this->ppdf->Ln(2);
                $this->ppdf->Cell(20, 5, '__________________________________________________________________________________________________________________', 0, 0, '');
                $this->ppdf->Ln(6);

                foreach ($otp as $ot) {

                    $dateDuty   = $ot['otDate'];
                    $otApproved = floatval($ot['otApp']) * 1;
                    $dayS       = date("D", strtotime($dateDuty));
                    $dateD      = date("m/d/Y", strtotime($dateDuty));

                    $cond   = "bioMetricId = '$bioM' AND date = '$dateDuty'";
                    $dtr    = $this->promo_HRaccount_model->selectAll_tcR_tk('duty', $cond);
                    $dutyId = $dtr['dutyId'];
                    $in1    = $dtr['in1'];
                    $out1   = $dtr['out1'];
                    $in2    = $dtr['in2'];
                    $out2   = $dtr['out2'];
                    $in3    = $dtr['in3'];
                    $out3   = $dtr['out3'];

                    // store entry
                    $in1_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $in1, "I");
                    $out1_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $out1, "O");
                    $in2_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $in2, "I");
                    $out2_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $out2, "O");
                    $in3_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $in3, "I");
                    $out3_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $dateDuty, $out3, "O");

                    if (empty($out3_store)) {
                        $out3_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, date('Y-m-d', strtotime($dateDuty . " +1 day")), $out3, "O");
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

                    $this->ppdf->Cell(22, 3, $dateD, 0, 0);
                    $this->ppdf->Cell(11, 3, $dayS, 0, 0);
                    $this->ppdf->Cell(20, 3, $in1_store . '' . $in1, 0, 0);
                    $this->ppdf->Cell(20, 3, $out1_store . '' . $out1, 0, 0);
                    $this->ppdf->Cell(20, 3, $in2_store . '' . $in2, 0, 0);
                    $this->ppdf->Cell(20, 3, $out2_store . '' . $out2, 0, 0);
                    $this->ppdf->Cell(20, 3, $in3_store . '' . $in3, 0, 0);
                    $this->ppdf->Cell(20, 3, $out3_store . '' . $out3, 0, 0);
                    $this->ppdf->Cell(12, 3, '0', 0, 0);
                    $this->ppdf->Cell(12, 3, '0', 0, 0);
                    $this->ppdf->Cell(12, 3, $otApproved, 0, 0);
                    $this->ppdf->Ln('4');
                }
            }

            $this->ppdf->Cell(20, 5, '____________________________________________________________________________________________________________________', 0, 0, '');
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

            if ($pdp && $otp) {
                $this->ppdf->Ln(24);
            } else if ($pdp || $otp) {
                $this->ppdf->Ln(26);
            } else {
                $this->ppdf->Ln(28);
            }
        }


        ob_clean();
        $this->ppdf->Output('promo_dtr.pdf', 'I');
    }

    public function printEarlyCO2()
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
        $this->ppdf->AddPage();

        // set margins
        $this->ppdf->SetMargins(12, 12, 12);
        $this->ppdf->SetHeaderMargin('PDF_MARGIN_HEADER');
        $this->ppdf->SetFooterMargin('PDF_MARGIN_FOOTER');

        $empId  = $input['empId'];
        $dF     = date('Y-m-d', strtotime($input['dateFrom']));
        $dT     = date('Y-m-d', strtotime($input['dateTo']));
        $dF2    = date('Y-m-d', strtotime($input['dateFrom']));
        $dT2    = date('Y-m-d', strtotime($input['dateTo']));
        $newDF  = date('F d, Y', strtotime($dF));
        $newDT  = date('F d, Y', strtotime($dT));

        $cond   = array('e.emp_id' => $empId);
        $promo  = $this->promo_HRaccount_model->promoDetails_wttR($cond, 'employee3', 'promo_record');
        $name       = ucwords(strtolower($promo['name']));
        $company    = $promo['promo_company'];
        $dept       = $promo['promo_department'];
        $promo_type = $promo['promo_type'];

        $bioM = '';
        $cond = "empId = '$empId' ORDER BY assignId DESC LIMIT 1";
        $s = $this->promo_HRaccount_model->selectAll_tcR_tk('assign', $cond);
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
        $cond1  = $cond . " AND t1.supStatus != 'Previous'";
        $cond2  = $cond . " AND t1.supStatus = 'Previous'";
        $sil1   = count($this->promo_HRaccount_model->join_ttjcoA_tk($select, $t1, $t2, $join, null, $cond1, null, null));
        $sil2   = count($this->promo_HRaccount_model->join_ttjcoA_tk($select, $t1, $t2, $join, null, $cond2, null, null));
        $sil    = $sil1 + $sil2;
        // Service Incentive Leave

        $cond   = "type = 'Legal' AND date BETWEEN '$dF' AND '$dT'";
        $sql    = $this->promo_HRaccount_model->selectAll_tcA_tk('holidays_promo', $cond);
        $lh     = count($sql);


        $sh     = 0;
        $sumDW  = 0;
        $sumOT  = 0;
        while (strtotime($dF2) <= strtotime($dT2)) {

            $cond   = array('bioMetricId' => $bioM, 'date' => $dF2);
            $dutyId = $this->promo_HRaccount_model->selectAll_tcR_tk('duty', $cond)['dutyId'];

            $hw = 0;
            $cond = array('dutyId' => $dutyId);
            $perday = $this->promo_HRaccount_model->selectAll_tcR_tk('perday', $cond)['hoursWork'];
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

            $cond   = "type = 'Special' AND date = '$dF2'";
            $shChk  = $this->promo_HRaccount_model->selectAll_tcR_tk('holidays_promo', $cond);
            if ($shChk) {
                $sh += $hw;
            }

            $ot = 0;
            $cond = "emp_id = '$empId' AND otDate = '$dF2' ORDER BY pay_id DESC";
            $otApp = $this->promo_HRaccount_model->selectAll_tcR_tk('otforpayment', $cond)['otApp'];
            if ($otApp && $otApp != '' && $otApp != 0) {
                $ot = floatval($otApp) * 1;
            }

            $sumOT += $ot;

            $dF2 = date("Y-m-d", strtotime($dF2 . "+1 day"));
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
            $dtr    = $this->promo_HRaccount_model->selectAll_tcR_tk('duty', $cond);
            $dutyId = $dtr['dutyId'];
            $in1    = $dtr['in1'];
            $out1   = $dtr['out1'];
            $in2    = $dtr['in2'];
            $out2   = $dtr['out2'];
            $in3    = $dtr['in3'];
            $out3   = $dtr['out3'];

            $in1_store  = $out1_store = $in2_store = $out2_store    = $in3_store = $out3_store = '';
            $in1_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $in1, "I");
            $out1_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $out1, "O");
            $in2_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $in2, "I");
            $out2_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $out2, "O");
            $in3_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $in3, "I");
            $out3_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $out3, "O");

            if (empty($out3_store)) {
                $out3_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, date('Y-m-d', strtotime($tempDate . " +1 day")), $out3, "O");
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

            $cond   = "bioMetricId = '$bioM' AND dateDuty = '$tempDate'";
            $dutyl  = $this->promo_HRaccount_model->selectAll_tcR_tk('duty_logbox', $cond);

            $dw = 0;
            $hw = 0;
            $cond = array('dutyId' => $dutyId);
            $perday = $this->promo_HRaccount_model->selectAll_tcR_tk('perday', $cond);
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
            $otApp = $this->promo_HRaccount_model->selectAll_tcR_tk('otforpayment', $cond)['otApp'];
            if ($otApp && $otApp != '' && $otApp != 0) {
                $ot = floatval($otApp) * 1;
            }
            // overtime ********************************************************************

            // holiday	********************************************************************
            $holiday = false;
            $cond = "date = '$tempDate' AND moveDate = '' LIMIT 1";
            $chk1 = $this->promo_HRaccount_model->selectAll_tcR_tk('holidays_promo', $cond);
            if ($chk1) {
                $holiday = true;
                $hol    = $chk1['nameHoliday'] . ' [ ' . $chk1['category'] . ' ]';
            }

            $cond = "moveDate = '$tempDate' LIMIT 1";
            $chk2 = $this->promo_HRaccount_model->selectAll_tcR_tk('holidays_promo', $cond);
            if ($chk2) {
                $holiday = true;
                $hol    = $chk2['nameHoliday'] . ' [ ' . $chk2['category'] . ' ]';
            }
            // holiday ********************************************************************

            // offduty of holiday ********************************************************************
            $hd = '';
            $odaHoliday = false;
            if ($chk1 || $chk2) {

                $cond  = "empId = '$empId' AND '$tempDate' BETWEEN dateFrom AND dateTo AND remark != ''";
                $sql   = $this->promo_HRaccount_model->selectAll_tcA_tk('assign_mul_sc', $cond);
                foreach ($sql as $ams) {
                    $amsId  = $ams['amsId'];
                    $cond   = array('amsId' => $amsId);
                    $sql    = $this->promo_HRaccount_model->selectAll_tcR_tk('request_odaf', $cond);
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
            $sql = $this->promo_HRaccount_model->selectAll_tcA_tk('assign_mul_sc', $cond);
            if ($sql) {
                foreach ($sql as $ams) {

                    $amsId  = $ams['amsId'];
                    $cond   = array('amsId' => $amsId, 'empId' => $empId, 'date' => $tempDate);
                    $amd    = $this->promo_HRaccount_model->selectAll_tcR_tk('ass_mul_do', $cond);

                    $cond   = array('empId' => $empId, 'dateToChange' => $tempDate, 'status' => 'Approved');
                    $acd    = $this->promo_HRaccount_model->selectAll_tcR_tk('ass_change_do', $cond);

                    if ($amd && !$acd) {
                        $show_do = true;
                    }

                    $cond   = array('empId' => $empId, 'dateNew' => $tempDate, 'status' => 'Approved');
                    $acd    = $this->promo_HRaccount_model->selectAll_tcR_tk('ass_change_do', $cond);
                    if ($acd) {
                        $acdId  = $acd['acdId'];
                        $cond   = array('requestId' => $acdId, 'requestType' => 'CDOS');
                        $aprby  = $this->promo_HRaccount_model->selectAll_tcR_tk('approveby', $cond);
                        if ($aprby) {
                            $dateReq    = $aprby['date'];
                            $timeReq    = $aprby['time'];
                            $app_details = date("Y-m-d H:i:s", strtotime("$dateReq $timeReq"));

                            $cond   = array('amsId' => $amsId);
                            $rns    = $this->promo_HRaccount_model->selectAll_tcR_tk('request_new_shift', $cond);
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
                $acd    = $this->promo_HRaccount_model->selectAll_tcR_tk('ass_change_do', $cond);
                if ($acd) {
                    $show_do = true;
                }

                $cond   = array('empId' => $empId, 'dateToChange' => $tempDate, 'status' => 'Approved');
                $acd    = $this->promo_HRaccount_model->selectAll_tcR_tk('ass_change_do', $cond);
                if ($acd) {
                    $old_do = true;
                }
            }

            // default day-off if $show_do is false
            if (!$sql &&  !$show_do &&  !$old_do) {
                $days = date('l', strtotime($tempDate));
                $cond = array('empId' => $empId, 'day' => date('l', strtotime($tempDate)));
                $sql = $this->promo_HRaccount_model->selectAll_tcR_tk('assign_day_off', $cond);
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
                $sql   = $this->promo_HRaccount_model->selectAll_tcA_tk('assign_mul_sc', $cond);
                foreach ($sql as $ams) {
                    $amsId  = $ams['amsId'];
                    $cond   = array('amsId' => $amsId);
                    $sql    = $this->promo_HRaccount_model->selectAll_tcR_tk('request_odaf', $cond);
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
                $pms    = $this->promo_HRaccount_model->selectAll_tcR_tk('promo_mul_sc', $cond);
                if ($pms) {
                    $nStore = $pms['noStore'];
                }

                if ($nStore == '') {
                    $cond   = "empId = '$empId' AND day_sched = '$day' ORDER BY pdId DESC";
                    $pds    = $this->promo_HRaccount_model->selectAll_tcR_tk('promo_def_sched', $cond);
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
                $amsId = $this->promo_HRaccount_model->selectAll_tcR_tk('assign_mul_sc', $cond)['amsId'];
                if ($amsId) {
                    $cond  = array('empId' => $empId, 'amsId' => $amsId, 'date' => $tempDate);
                    $pns   = $this->promo_HRaccount_model->selectAll_tcR_tk('promo_nosched', $cond);
                    if ($pns) {
                        $no_schedule = true;
                    }
                }
            }
            // no schedule ********************************************************************

            $loa = false;
            $cond = "r_l_absence.empId = '$empId' AND r_l_absence.empId = rla_day.empId AND rla_day.date = '$tempDate' 
            AND '$tempDate' BETWEEN r_l_absence.incDate_sF AND r_l_absence.incDate_sT AND (status = 'Approved' OR status = 'Pending')";
            $sqlloa = $this->promo_HRaccount_model->selectAll_tcR_tk('r_l_absence, rla_day', $cond);
            if (count($sqlloa) != 0) {
                $loa = true;
            }

            $sil = false;
            if ($loa) {
                $cpnd = "r_l_absence.empId = '$empId' AND r_l_absence.empId = rla_day.empId AND rla_day.date = '$tempDate' 
                AND '$tempDate' BETWEEN r_l_absence.incDate_sF AND r_l_absence.incDate_sT AND (status = 'Approved' OR status = 'Pending') 
                AND (if_emer_pers LIKE '%Service Incentive Leave%' OR if_emer_pers LIKE '%SIL%' OR if_emer_pers LIKE '%S.I.L%')";
                $sqlsil = $this->promo_HRaccount_model->selectAll_tcR_tk('r_l_absence, rla_day', $cpnd);
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

        //Close and output PDF document
        ob_clean();
        $this->ppdf->Output('promo_dtr.pdf', 'I');

        // $this->ppdf->Output();
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
            $cond   = array('e.emp_id' => $empId);
            $promo  = $this->promo_HRaccount_model->promoDetails_wttR($cond, 'employee3', 'promo_record');
            $name       = ucwords(strtolower($promo['name']));
            $company    = $promo['promo_company'];
            $dept       = $promo['promo_department'];
            $promo_type = $promo['promo_type'];

            $bioM = '';
            $cond = "empId = '$empId' ORDER BY assignId DESC LIMIT 1";
            $s = $this->promo_HRaccount_model->selectAll_tcR_tk('assign', $cond);
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
            $cond1  = $cond . " AND t1.supStatus != 'Previous'";
            $cond2  = $cond . " AND t1.supStatus = 'Previous'";
            $sil1   = count($this->promo_HRaccount_model->join_ttjcoA_tk($select, $t1, $t2, $join, null, $cond1, null, null));
            $sil2   = count($this->promo_HRaccount_model->join_ttjcoA_tk($select, $t1, $t2, $join, null, $cond2, null, null));
            $sil    = $sil1 + $sil2;
            // Service Incentive Leave

            $cond   = "type = 'Legal' AND date BETWEEN '$dF' AND '$dT'";
            $sql    = $this->promo_HRaccount_model->selectAll_tcA_tk('holidays_promo', $cond);
            $lh     = count($sql);


            $sh     = 0;
            $sumDW  = 0;
            $sumOT  = 0;
            while (strtotime($dF2) <= strtotime($dT2)) {

                $cond   = array('bioMetricId' => $bioM, 'date' => $dF2);
                $dutyId = $this->promo_HRaccount_model->selectAll_tcR_tk('duty', $cond)['dutyId'];

                $hw = 0;
                $cond = array('dutyId' => $dutyId);
                $perday = $this->promo_HRaccount_model->selectAll_tcR_tk('perday', $cond)['hoursWork'];
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

                $cond   = "type = 'Special' AND date = '$dF2'";
                $shChk  = $this->promo_HRaccount_model->selectAll_tcR_tk('holidays_promo', $cond);
                if ($shChk) {
                    $sh += $hw;
                }

                $ot = 0;
                $cond = "emp_id = '$empId' AND otDate = '$dF2' ORDER BY pay_id DESC";
                $otApp = $this->promo_HRaccount_model->selectAll_tcR_tk('otforpayment', $cond)['otApp'];
                if ($otApp && $otApp != '' && $otApp != 0) {
                    $ot = floatval($otApp) * 1;
                }

                $sumOT += $ot;

                $dF2 = date("Y-m-d", strtotime($dF2 . "+1 day"));
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
                $dtr    = $this->promo_HRaccount_model->selectAll_tcR_tk('duty', $cond);
                $dutyId = $dtr['dutyId'];
                $in1    = $dtr['in1'];
                $out1   = $dtr['out1'];
                $in2    = $dtr['in2'];
                $out2   = $dtr['out2'];
                $in3    = $dtr['in3'];
                $out3   = $dtr['out3'];

                $in1_store  = $out1_store = $in2_store = $out2_store    = $in3_store = $out3_store = '';
                $in1_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $in1, "I");
                $out1_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $out1, "O");
                $in2_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $in2, "I");
                $out2_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $out2, "O");
                $in3_store  = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $in3, "I");
                $out3_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, $tempDate, $out3, "O");

                if (empty($out3_store)) {
                    $out3_store = $this->promo_HRaccount_model->store_entry($bioM, $allowId, date('Y-m-d', strtotime($tempDate . " +1 day")), $out3, "O");
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

                $cond   = "bioMetricId = '$bioM' AND dateDuty = '$tempDate'";
                $dutyl  = $this->promo_HRaccount_model->selectAll_tcR_tk('duty_logbox', $cond);

                $dw = 0;
                $hw = 0;
                $cond = array('dutyId' => $dutyId);
                $perday = $this->promo_HRaccount_model->selectAll_tcR_tk('perday', $cond);
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
                $otApp = $this->promo_HRaccount_model->selectAll_tcR_tk('otforpayment', $cond)['otApp'];
                if ($otApp && $otApp != '' && $otApp != 0) {
                    $ot = floatval($otApp) * 1;
                }
                // overtime ********************************************************************

                // holiday	********************************************************************
                $holiday = false;
                $cond = "date = '$tempDate' AND moveDate = '' LIMIT 1";
                $chk1 = $this->promo_HRaccount_model->selectAll_tcR_tk('holidays_promo', $cond);
                if ($chk1) {
                    $holiday = true;
                    $hol    = $chk1['nameHoliday'] . ' [ ' . $chk1['category'] . ' ]';
                }

                $cond = "moveDate = '$tempDate' LIMIT 1";
                $chk2 = $this->promo_HRaccount_model->selectAll_tcR_tk('holidays_promo', $cond);
                if ($chk2) {
                    $holiday = true;
                    $hol    = $chk2['nameHoliday'] . ' [ ' . $chk2['category'] . ' ]';
                }
                // holiday ********************************************************************

                // offduty of holiday ********************************************************************
                $hd = '';
                $odaHoliday = false;
                if ($chk1 || $chk2) {

                    $cond  = "empId = '$empId' AND '$tempDate' BETWEEN dateFrom AND dateTo AND remark != ''";
                    $sql   = $this->promo_HRaccount_model->selectAll_tcA_tk('assign_mul_sc', $cond);
                    foreach ($sql as $ams) {
                        $amsId  = $ams['amsId'];
                        $cond   = array('amsId' => $amsId);
                        $sql    = $this->promo_HRaccount_model->selectAll_tcR_tk('request_odaf', $cond);
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
                $sql = $this->promo_HRaccount_model->selectAll_tcA_tk('assign_mul_sc', $cond);
                if ($sql) {
                    foreach ($sql as $ams) {

                        $amsId  = $ams['amsId'];
                        $cond   = array('amsId' => $amsId, 'empId' => $empId, 'date' => $tempDate);
                        $amd    = $this->promo_HRaccount_model->selectAll_tcR_tk('ass_mul_do', $cond);

                        $cond   = array('empId' => $empId, 'dateToChange' => $tempDate, 'status' => 'Approved');
                        $acd    = $this->promo_HRaccount_model->selectAll_tcR_tk('ass_change_do', $cond);

                        if ($amd && !$acd) {
                            $show_do = true;
                        }

                        $cond   = array('empId' => $empId, 'dateNew' => $tempDate, 'status' => 'Approved');
                        $acd    = $this->promo_HRaccount_model->selectAll_tcR_tk('ass_change_do', $cond);
                        if ($acd) {
                            $acdId  = $acd['acdId'];
                            $cond   = array('requestId' => $acdId, 'requestType' => 'CDOS');
                            $aprby  = $this->promo_HRaccount_model->selectAll_tcR_tk('approveby', $cond);
                            if ($aprby) {
                                $dateReq    = $aprby['date'];
                                $timeReq    = $aprby['time'];
                                $app_details = date("Y-m-d H:i:s", strtotime("$dateReq $timeReq"));

                                $cond   = array('amsId' => $amsId);
                                $rns    = $this->promo_HRaccount_model->selectAll_tcR_tk('request_new_shift', $cond);
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
                    $acd    = $this->promo_HRaccount_model->selectAll_tcR_tk('ass_change_do', $cond);
                    if ($acd) {
                        $show_do = true;
                    }

                    $cond   = array('empId' => $empId, 'dateToChange' => $tempDate, 'status' => 'Approved');
                    $acd    = $this->promo_HRaccount_model->selectAll_tcR_tk('ass_change_do', $cond);
                    if ($acd) {
                        $old_do = true;
                    }
                }

                // default day-off if $show_do is false
                if (!$sql &&  !$show_do &&  !$old_do) {
                    $days = date('l', strtotime($tempDate));
                    $cond = array('empId' => $empId, 'day' => date('l', strtotime($tempDate)));
                    $sql = $this->promo_HRaccount_model->selectAll_tcR_tk('assign_day_off', $cond);
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
                    $sql   = $this->promo_HRaccount_model->selectAll_tcA_tk('assign_mul_sc', $cond);
                    foreach ($sql as $ams) {
                        $amsId  = $ams['amsId'];
                        $cond   = array('amsId' => $amsId);
                        $sql    = $this->promo_HRaccount_model->selectAll_tcR_tk('request_odaf', $cond);
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
                    $pms    = $this->promo_HRaccount_model->selectAll_tcR_tk('promo_mul_sc', $cond);
                    if ($pms) {
                        $nStore = $pms['noStore'];
                    }

                    if ($nStore == '') {
                        $cond   = "empId = '$empId' AND day_sched = '$day' ORDER BY pdId DESC";
                        $pds    = $this->promo_HRaccount_model->selectAll_tcR_tk('promo_def_sched', $cond);
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
                    $amsId = $this->promo_HRaccount_model->selectAll_tcR_tk('assign_mul_sc', $cond)['amsId'];
                    if ($amsId) {
                        $cond  = array('empId' => $empId, 'amsId' => $amsId, 'date' => $tempDate);
                        $pns   = $this->promo_HRaccount_model->selectAll_tcR_tk('promo_nosched', $cond);
                        if ($pns) {
                            $no_schedule = true;
                        }
                    }
                }
                // no schedule ********************************************************************

                $loa = false;
                $cond = "r_l_absence.empId = '$empId' AND r_l_absence.empId = rla_day.empId AND rla_day.date = '$tempDate' 
            AND '$tempDate' BETWEEN r_l_absence.incDate_sF AND r_l_absence.incDate_sT AND (status = 'Approved' OR status = 'Pending')";
                $sqlloa = $this->promo_HRaccount_model->selectAll_tcR_tk('r_l_absence, rla_day', $cond);
                if (count($sqlloa) != 0) {
                    $loa = true;
                }

                $sil = false;
                if ($loa) {
                    $cpnd = "r_l_absence.empId = '$empId' AND r_l_absence.empId = rla_day.empId AND rla_day.date = '$tempDate' 
                AND '$tempDate' BETWEEN r_l_absence.incDate_sF AND r_l_absence.incDate_sT AND (status = 'Approved' OR status = 'Pending') 
                AND (if_emer_pers LIKE '%Service Incentive Leave%' OR if_emer_pers LIKE '%SIL%' OR if_emer_pers LIKE '%S.I.L%')";
                    $sqlsil = $this->promo_HRaccount_model->selectAll_tcR_tk('r_l_absence, rla_day', $cpnd);
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
}
