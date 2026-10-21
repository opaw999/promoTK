<?php

class ReportHelper {
    public static function getDuration($table, $empid)
	{
		$i = 0; //counter
		$mo = 0; //months counter
		$yr = 0; //year counter
		$dy = 0; //day counter

		$eoc = array();
        $CI = &get_instance(); // Get the CI_Controller instance

		$sel = $CI->db->query("SELECT emp_id, startdate, eocdate from $table where emp_id = '$empid' ");
		foreach ($sel->result_array() as $r) {
			$eoc[] = $r['eocdate'];
			$date1 = $r['startdate'];
			$date2 = $r['eocdate'];

			$findme1 = date('Y-m-d') + 1;
			$new_ed = date('Y') . '-12-31';

			$pos = strpos($date2, "$findme1");

			if ($pos === false) {
				$diff = abs(strtotime($date2) - strtotime($date1));
			} else {
				$diff = abs(strtotime($new_ed) - strtotime($date1));
			}

			//$diff = abs(strtotime($date2) - strtotime($date1));

			$years = floor($diff / (365 * 60 * 60 * 24));
			$months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
			$days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

			$mo = $mo + $months;
			$yr = $yr + $years;
			$dy = $dy + $days;

			if ($i > 0) {
				$d1 = $eoc[$i - 1];
				$dif = abs(strtotime($date1) - strtotime($d1));

				$year = floor($dif / (365 * 60 * 60 * 24));
				$month = floor(($dif - $year * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
				$day = floor(($dif - $year * 365 * 60 * 60 * 24 - $month * 30 * 60 * 60 * 24) / (60 * 60 * 24));
			}
			//echo $i." ".$date1." ".$date2." - ".$years." years ".$months." months ".$days." days --- per row <br> ";
			$i++;
		}

		$fin = $mo . " month(s) " . $dy . " days";
		return $fin;
	}

	public static function getDur($table, $rec)
	{
		$i  = 0; //counter
		$mo = 0; //months counter		
		$yr = 0; //year counter
		$dy = 0; //day counter
		$flag = 0;
		$eoc = array();
        $CI = &get_instance(); // Get the CI_Controller instance
		$sel = $CI->db->query("SELECT emp_id, startdate, eocdate from $table where record_no = '$rec' "); //and eocdate like '2015-%'
		foreach ($sel->result_array() as $r) {
			$eoc[] = $r['eocdate'];
			$date1 = $r['startdate'];
			$date2 = $r['eocdate'];

			$findme = date('Y-m-d') - 1;
			$new_sd = date('Y') . '-01-00';
			$pos 	= strpos($date1, "$findme");

			if ($pos === false) {
				$diff = abs(strtotime($date2) - strtotime($date1));
			} else {
				$diff = abs(strtotime($date2) - strtotime($new_sd));
			}

			$years = floor($diff / (365 * 60 * 60 * 24));
			$months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
			$days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

			$mo = $mo + $months;
			$yr = $yr + $years;
			$dy = $dy + $days;

			if ($i > 0) {
				$d1 = $eoc[$i - 1];
				$dif = abs(strtotime($date1) - strtotime($d1));

				$year = floor($dif / (365 * 60 * 60 * 24));
				$month = floor(($dif - $year * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
				$day = floor(($dif - $year * 365 * 60 * 60 * 24 - $month * 30 * 60 * 60 * 24) / (60 * 60 * 24));
			}
			$i++;
		}

		/*
		if($yr > 1){	$y = " years ";		} else { $y = " year ";	}
		if($mo > 1){	$m = " months ";	} else { $m = " month ";	}
		if($dy > 1){	$d = " days ";		} else { $d = " day ";		}
		*/
		$fin = $mo . " mo(s)." . $dy . " days";
		return $fin;
	}


	public static function get2Dur($empid)
	{
		$dy1 = 0; // Add this line to initialize $dy1
		$i = 0; //counter
		$mo = 0; //months counter
		$mo1 = 0; //months counter
		$yr = 0; //year counter
		$dy = 0; //day counter


		$eoc = array();
        $CI = &get_instance(); // Get the CI_Controller instance
		$sel = $CI->db->query("SELECT emp_id, startdate, eocdate, emp_type from employee3 where emp_id = '$empid' ");
		$sel1 = $CI->db->query("SELECT emp_id, startdate, eocdate from employmentrecord_ where emp_id = '$empid' "); //and eocdate like '2015-%'
		foreach ($sel->result_array() as $r) {
			$eoc[] = $r['eocdate'];
			$date1 = $r['startdate'];
			$date2 = $r['eocdate'];

			//$findme1= date('Y-m-d')+1;
			//$new_ed	= date('Y').'-12-31';	
			$datetod = date('Y-m-d');

			//if($r['emp_type'] == "Regular"){
			$diff = abs(strtotime($datetod) - strtotime($date1));
			$fl   = "fl";
			/*}else{
				$pos1 	= strpos($date2,"$findme1");			
				
			 	if($pos1 === false) {
					$diff = abs(strtotime($date2) - strtotime($date1));	
					$flags = "false";
				}else {
					$diff = abs(strtotime($new_ed) - strtotime($date1));				
					$flags= "true";
				} 
			}*/

			$years = floor($diff / (365 * 60 * 60 * 24));
			$months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
			$days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

			$mo = $mo + $months;
			$dy = $dy + $days;

			if ($i > 0) {
				$d1 = $eoc[$i - 1];
				$dif = abs(strtotime($date1) - strtotime($d1));

				$year = floor($dif / (365 * 60 * 60 * 24));
				$months = floor(($dif - $year * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
				$day = floor(($dif - $year * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
			}
			$i++;
		}

		foreach ($sel1->result_array() as $r1) {
			$eoc[] = $r1['eocdate'];
			$date1 = $r1['startdate'];
			$date2 = $r1['eocdate'];

			$findme = date('Y-m-d') - 1;
			$new_sd = date('Y') . '-01-00';

			$pos 	= strpos($date1, "$findme");

			if ($pos === false) {
				$diff = abs(strtotime($date2) - strtotime($date1));
			} else {
				$diff = abs(strtotime($date2) - strtotime($new_sd));
			}

			$years = floor($diff / (365 * 60 * 60 * 24));
			$month = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
			$days = floor(($diff - $years * 365 * 60 * 60 * 24 - $month * 30 * 60 * 60 * 24) / (60 * 60 * 24));

			

			$mo1 = $mo1 + $month;
			$dy1 = $dy1 + $days;

			if ($i > 0) {
				$d1 = $eoc[$i - 1];
				$dif = abs(strtotime($date1) - strtotime($d1));

				$year = floor($dif / (365 * 60 * 60 * 24));
				$month = floor(($dif - $year * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
				$day = floor(($dif - $year * 365 * 60 * 60 * 24 - $month * 30 * 60 * 60 * 24) / (60 * 60 * 24));
			}
			$i++;
		}

		$days = $dy + $dy1;
		$month = $mo + $mo1;

		//days
		if ($days > 209) {
			$month = $month + 7;
			$days = $days - 200;
		} else if ($days > 179) {
			$month = $month + 6;
			$days = $days - 180;
		} else if ($days > 149) {
			$month = $month + 5;
			$days = $days - 150;
		} else if ($days > 119) {
			$month = $month + 4;
			$days = $days - 120;
		} else if ($days > 89) {
			$month = $month + 3;
			$days = $days - 90;
		} else if ($days > 59) {
			$month = $month + 2;
			$days = $days - 60;
		} else if ($days > 29) {
			$month = $month + 1;
			$days = $days - 30;
		}

		//months
		if ($month > 71) {
			$yr 	= $yr + 6;
			$month 	= $month - 72;
		} else if ($month > 59) {
			$yr 	= $yr + 5;
			$month 	= $month - 60;
		} else if ($month > 47) {
			$yr 	= $yr + 4;
			$month 	= $month - 48;
		} else if ($month > 35) {
			$yr 	= $yr + 3;
			$month 	= $month - 36;
		} else if ($mo > 23) {
			$yr 	= $yr + 2;
			$month 	= $month - 24;
		} else if ($month > 11) {
			$yr 	= $yr + 1;
			$month 	= $month - 12;
		}


		//$fin = $month." & ".$days." mo=".$mo." mo1=".$mo1." dy=".$dy." dy1=".$dy1;
		$fin = $yr . " year(s) &" . $month . " month(s) & " . $days . " days"; //.$fl;// mo=".$mo." mo1=".$mo1." dy=".$dy." dy1=".$dy1." findme1=".$findme1." flags=".$flags;
		return $fin;
		
	}

    public static function getYears($date1, $startdate)
	{
		if ($date1 == '0000-00-00' || $date1 == '' || $date1 == NULL) {
			$date1 = $startdate;
		} else {
			$date1 = $date1;
		}

		$date2 = date('Y-m-d');
		$dif = abs(strtotime($date2) - strtotime($date1));
		 ($date2) - strtotime($date1);

		$year = floor($dif / (365 * 60 * 60 * 24));
		$months = floor(($dif - $year * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
		$day = floor(($dif - $year * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
		if ($year == 0) {
			return $months . " month(s) & " . $day . " day(s)";
		} else {
			return $year . "yrs & " . $months . "mos & " . $day . "days ";
		}
	}

	

	// function getYear($date1, $startdate)
	// {
	// 	if ($date1 == '0000-00-00' || $date1 == '' || $date1 == NULL) {
	// 		$date1 = $startdate;
	// 	} else {
	// 		$date1 = $date1;
	// 	}

	// 	$date2 = date('Y-m-d');
	// 	$dif = abs(strtotime($date2) - strtotime($date1));

	// 	$year = floor($dif / (365 * 60 * 60 * 24));
	// 	$months = floor(($dif - $year * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
	// 	$day = floor(($dif - $year * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

	// 	return $year . "/" . $months . "/" . $day;
	// }

	// function getYrs($date1, $startdate)
	// {
	// 	if ($date1 == '0000-00-00' || $date1 == '' || $date1 == NULL) {
	// 		$date1 = $startdate;
	// 	} else {
	// 		$date1 = $date1;
	// 	}

	// 	$date2 = date('Y-m-d');
	// 	$dif = abs(strtotime($date2) - strtotime($date1));

	// 	$year = floor($dif / (365 * 60 * 60 * 24));
	// 	$months = floor(($dif - $year * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
	// 	$day = floor(($dif - $year * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

	// 	return $year . "/" . $months;
	// }

	// /**9/11/17**/
	// function getYrMo($date1, $startdate)
	// {
	// 	if ($date1 == '0000-00-00' || $date1 == '' || $date1 == NULL) {
	// 		$date1 = $startdate;
	// 	} else {
	// 		$date1 = $date1;
	// 	}

	// 	$date2 = date('Y-m-d');
	// 	$dif = abs(strtotime($date2) - strtotime($date1));

	// 	$year = floor($dif / (365 * 60 * 60 * 24));
	// 	$months = floor(($dif - $year * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
	// 	$day = floor(($dif - $year * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

	// 	if ($year > 1) {
	// 		$yy = $year . " yrs & ";
	// 	} else if ($year == "0") {
	// 		$yy == "";
	// 	} else {
	// 		$yy = $year . " yr & ";
	// 	}
	// 	if ($months > 1) {
	// 		$mot = $months . " months";
	// 	} else {
	// 		$mot = $months . " month";
	// 	}
	// 	return $yy . $mot;
	// }
	// /**/

	// /** updated 8-5-2016 **/
	public static function getNYears($date1, $startdate)
	{
		if ($date1 == '0000-00-00' || $date1 == '' || $date1 == NULL) {
			$date1 = $startdate;
		} else {
			$date1 = $date1;
		}

		$date2 = date('Y-m-d');
		$dif = abs(strtotime($date2) - strtotime($date1));

		$year = floor($dif / (365 * 60 * 60 * 24));
		$months = floor(($dif - $year * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
		$day = floor(($dif - $year * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
		if ($year == 0) {
			return "0";
		} else {
			return $year . " years";
		}
	}
	

	// function getYearsWithPosition($pos, $cc, $bc, $dc)
	// {
	// 	$qer = mysql_query("Select date_hired,startdate from application_details right join employee3 on application_details.app_id = employee3.emp_id 
	// 					where position = '$pos' and employee3.company_code = '$cc' and employee3.bunit_code = '$bc' and employee3.dept_code = '$dc' and current_status = 'Active' ") or die(mysql_error());

	// 	$x = 0; // 5 months below
	// 	$y = 0; // 6 months to 1 year
	// 	$z = 0; // 2 years and above

	// 	while ($i = mysql_fetch_array($qer)) {
	// 		$date1 = $i['date_hired'];
	// 		$startdate = $i['startdate'];

	// 		if ($date1 == '0000-00-00' || $date1 == '' || $date1 == NULL) {
	// 			$date1 = $startdate;
	// 		} else {
	// 			$date1 = $date1;
	// 		}

	// 		$date2 = date('Y-m-d');
	// 		$dif = abs(strtotime($date2) - strtotime($date1));

	// 		$year = floor($dif / (365 * 60 * 60 * 24));
	// 		$months = floor(($dif - $year * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
	// 		$day = floor(($dif - $year * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));


	// 		if ($year >= 2) {
	// 			$z++;
	// 		} else if ($year < 2 && $year >= 1) {
	// 			$y++;
	// 		} else if ($year < 1) {
	// 			if ($months >= 6) {
	// 				$y++;
	// 			} else if ($months < 6) {
	// 				$x++;
	// 			} else {
	// 				$x++;
	// 			}
	// 		}
	// 	}
	// 	return $x . "/" . $y . "/" . $z;
	// }

	// function getYearsWithPosition1($type, $pos, $cc, $bc, $dc, $sc)
	// {
	// 	if ($type == '1') {
	// 		$etypeequal = "and (emp_type !='NESCO-PTA' and emp_type !='NESCO-PTP' and emp_type !='PTA' and emp_type !='PTP') ";
	// 	} else if ($type == '2') {
	// 		$etypeequal = "and (emp_type ='NESCO-PTA' or emp_type ='NESCO-PTP' or emp_type ='PTA' or emp_type ='PTP') ";
	// 	}
	// 	$qer = mysql_query("Select date_hired,startdate from application_details right join employee3 on application_details.app_id = employee3.emp_id 
	// 					where position = '$pos' $etypeequal and employee3.company_code = '$cc' and employee3.bunit_code = '$bc' and employee3.dept_code = '$dc' and employee3.section_code ='$sc' and current_status = 'Active' ") or die(mysql_error());

	// 	$x = 0; // 5 months below
	// 	$y = 0; // 6 months to 1 year
	// 	$z = 0; // 2 years and above

	// 	while ($i = mysql_fetch_array($qer)) {
	// 		$date1 = $i['date_hired'];
	// 		$startdate = $i['startdate'];

	// 		if ($date1 == '0000-00-00' || $date1 == '' || $date1 == NULL) {
	// 			$date1 = $startdate;
	// 		} else {
	// 			$date1 = $date1;
	// 		}

	// 		$date2 = date('Y-m-d');
	// 		$dif = abs(strtotime($date2) - strtotime($date1));

	// 		$year = floor($dif / (365 * 60 * 60 * 24));
	// 		$months = floor(($dif - $year * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
	// 		$day = floor(($dif - $year * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));


	// 		if ($year >= 2) {
	// 			$z++;
	// 		} else if ($year < 2 && $year >= 1) {
	// 			$y++;
	// 		} else if ($year < 1) {
	// 			if ($months >= 6) {
	// 				$y++;
	// 			} else if ($months < 6) {
	// 				$x++;
	// 			} else {
	// 				$x++;
	// 			}
	// 		}
	// 	}

	// 	if ($type == '2') {
	// 		$x = $x / 2;
	// 		$y = $y / 2;
	// 		$z = $z / 2;
	// 	}
	// 	return $x . "/" . $y . "/" . $z;
	// }
	public static function getYearsInAdvance($datehired, $dateminus)
	{
		// if($datehired == '0000-00-00' || $datehired == '' || $datehired == NULL){
		// 	$datehired = $startdate;
		// }else{
		// 	$date1 = $date1;
		// }

		//$date2 = date('Y-m-d');
		$dif = abs(strtotime($dateminus) - strtotime($datehired));

		$year = floor($dif / (365 * 60 * 60 * 24));
		$months = floor(($dif - $year * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
		$day = floor(($dif - $year * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
		if ($year == 0) {
			return $months . " month(s) & " . $day . " day(s)";
		} else {
			return $year . "yrs & " . $months . "mos & " . $day . "days ";
		}
	}

	public static function get_year($date1, $startdate)
		{
			if ($date1 == '0000-00-00' || $date1 == '' || $date1 == NULL) {
				$date1 = $startdate;
			} else {
				$date1 = $date1;
			}

			$date2 = date('Y-m-d');
			$dif = abs(strtotime($date2) - strtotime($date1));

			$year = floor($dif / (365 * 60 * 60 * 24));
			$months = floor(($dif - $year * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
			$day = floor(($dif - $year * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));
			if ($year == 0) {
				return "0";
			} else {
				return $year;
			}
		}

		public static function formalname($appid)
		{
			$CI = &get_instance(); // Get the CI_Controller instance
			$query = $CI->db->query("SELECT lastname, firstname, MID(middlename,1,1) as mi, suffix from applicant where app_id = '$appid'");
			$result = $query->row_array(); // Use row_array() instead of makeQuery() and fetchArray()

			if (!empty($result)) {
				if (!empty($result['suffix'])) {
					$suffix = ", " . $result['suffix'];
				} else {
					$suffix = "";
				}

				// Concatenate first name, middle initial, last name, and suffix
				$name = $result['firstname'] . " " . $result['mi'] . ". " . $result['lastname'];
				return $name;
			} else {
				return ""; // Return empty string if no result is found
			}
		}
    }

?>