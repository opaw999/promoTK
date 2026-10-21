<?php
defined('BASEPATH') or exit('No direct script access allowed');

//  -------------------------------------  Promo TK --------------------------------------------------------

// for Page
$route['logout']                    = 'page/logout';
$route['(:any)/(:any)']             = 'page/menu/$1/$2';
// End Page

// for Dtr
$route['searchPromo']               = 'dtr/searchPromo';
$route['searchEarlyCo']             = 'dtr/searchEarlyCo';
$route['promoDetails']              = 'dtr/promoDetails';
$route['promoCutoffs']              = 'dtr/promoCutoffs';
$route['cutoff_table']              = 'dtr/cutoff_table';
$route['employee_list']             = 'dtr/employee_list';
$route['dtr_info']                  = 'dtr/dtr_info';
$route['dtr_details']               = 'dtr/dtr_details';
$route['dtr_net']                   = 'dtr/dtr_net';
$route['time_entries']              = 'dtr/time_entries';
$route['dtr_requests']              = 'dtr/dtr_requests';
$route['dtr_process']               = 'dtr/dtr_process';
$route['calculateDate']             = 'dtr/calculateDate';
$route['printEarlyCO']              = 'dtr/printEarlyCO';
$route['viewMasterfile']            = 'dtr/viewMasterfile';

// End Dtr

// for Add
$route['add_promoCutoff']           = 'add/add_promoCutoff';
$route['add_searchPromo']           = 'add/add_searchPromo';
$route['add_getStores']             = 'add/add_getStores';
$route['add_manualot']              = 'add/add_manualot';
$route['manualot_list']             = 'add/manualot_list';
$route['manualot_emp']              = 'add/manualot_emp';
$route['delete_ot']                 = 'add/delete_ot';
$route['add_holiday']               = 'add/add_holiday';
$route['holidays_promo']            = 'add/holidays_promo';
$route['masterfileSearch']          = 'add/masterfileSearch';
$route['addMasterfileForm']         = 'add/addMasterfileForm';
$route['addMasterfile']             = 'add/addMasterfile';
$route['defaultSchedForm']          = 'add/defaultSchedForm';
$route['defaultSched']              = 'add/defaultSched';
$route['add_searchotdwa']           = 'add/add_searchotdwa';
$route['getShiftdwa']               = 'add/getShiftdwa';
$route['add_otdwa']                 = 'add/add_otdwa';
$route['getShiftentry']             = 'add/getShiftentry';
$route['add_otentry']               = 'add/add_otentry';

// End Add

// for Setup
$route['appdis_list']               = 'setup/appdis_list';
$route['appdis_update']             = 'setup/appdis_update';
$route['exportuser_list']           = 'setup/exportuser_list';
$route['portalAccess']              = 'setup/portalAccess';
$route['setup_cutoff']              = 'setup/setup_cutoff';
$route['setup_searchPromo']         = 'setup/setup_searchPromo';
$route['getCutoffdate']             = 'setup/getCutoffdate';
$route['perday_previous_allow']     = 'setup/perday_previous_allow';
$route['enable_previous']           = 'setup/enable_previous';
$route['update_previous']           = 'setup/update_previous';
$route['searchManualLate']          = 'setup/searchManualLate';
$route['manualLate']                = 'setup/manualLate';
$route['searchManualUndertime']     = 'setup/searchManualUndertime';
$route['manualUndertime']           = 'setup/manualUndertime';
$route['store_code_list']           = 'setup/store_code_list';
$route['addStoreCode']              = 'setup/addStoreCode';
$route['updateStoreCode']           = 'setup/updateStoreCode';
$route['storeCodes']                = 'setup/storeCodes';

// End Setup

// for View
$route['view_searchPromo']          = 'view/view_searchPromo';
$route['masterfileDetails']         = 'view/masterfileDetails';
$route['multiSched']                = 'view/multiSched';
$route['defSched']                  = 'view/defSched';
$route['viewDO']                    = 'view/viewDO';
$route['schedule_pager']            = 'view/schedule_pager';
$route['viewSched']                 = 'view/viewSched';
$route['assign_list']               = 'view/assign_list';
$route['holiday_list']              = 'view/holiday_list';
$route['holiday_form']              = 'view/holiday_form';
$route['holiday_update']            = 'view/holiday_update';
$route['delpromoId']                = 'view/delpromoId';
$route['addpromoId']                = 'view/addpromoId';
$route['submitpromoId']             = 'view/submitpromoId';

// End View

// for Concerns
$route['concerns_searchPromo']      = 'concerns/concerns_searchPromo';
$route['ot_list']                   = 'concerns/ot_list';
$route['change_store']              = 'concerns/change_store';
$route['hrAccount_list']            = 'concerns/hrAccount_list';
$route['hrAccount']                 = 'concerns/hrAccount';
$route['promoReqList']              = 'concerns/promoReqList';
$route['promoReqAccess']            = 'concerns/promoReqAccess';
$route['setupSupList']              = 'concerns/setupSupList';
$route['cutOffSelection']           = 'concerns/cutOffSelection';
$route['setupUpdates']              = 'concerns/setupUpdates';

// End Concerns

// for Process
$route['processMenu']               = 'process/processMenu';
$route['processFilter']             = 'process/processFilter';
$route['process_searchPromo']       = 'process/process_searchPromo';
$route['processLogbox']             = 'process/processLogbox';
$route['bioPromoList']              = 'process/bioPromoList';
$route['processBiometric']          = 'process/processBiometric';
$route['cs_first']                  = 'process/cs_first';
$route['processCSfirst']            = 'process/processCSfirst';
$route['od_second']                 = 'process/od_second';
$route['processODsecond']           = 'process/processODsecond';
$route['co_third']                  = 'process/co_third';
$route['co_list']                   = 'process/co_list';
// $route['processCOthird']            = 'process/processCOthird';

$route['processSingle']             = 'process/processSingle';
// End Process

// for Finalize
$route['finalizeDtr']               = 'finalize/finalizeDtr';

// End Finalize

// for ViewRequest
$route['viewReqPerEmp']             = 'viewRequest/viewReqPerEmp';
$route['otAuth']                    = 'viewRequest/otAuth';
$route['otPayment']                 = 'viewRequest/otPayment';
$route['cuAuth']                    = 'viewRequest/cuAuth';
$route['utAuth']                    = 'viewRequest/utAuth';
$route['dwa']                       = 'viewRequest/dwa';
$route['loa']                       = 'viewRequest/loa';
$route['doShort']                   = 'viewRequest/doShort';
$route['doLong']                    = 'viewRequest/doLong';
$route['viewEmpEOA']                = 'viewRequest/viewEmpEOA';
$route['viewEmpOTP']                = 'viewRequest/viewEmpOTP';
$route['viewEmpCUA']                = 'viewRequest/viewEmpCUA';
$route['viewEmpUTA']                = 'viewRequest/viewEmpUTA';
$route['viewEmpDWA']                = 'viewRequest/viewEmpDWA';
$route['viewEmpLOA']                = 'viewRequest/viewEmpLOA';
$route['viewEmpDOS']                = 'viewRequest/viewEmpDOS';
$route['viewEmpDOL']                = 'viewRequest/viewEmpDOL';
$route['updateReason']              = 'viewRequest/updateReason';

// End ViewRequest

// for User
$route['accessroles_table']         = 'user/accessroles_table';
$route['accessrolesForm']           = 'user/accessrolesForm';
$route['mainMenu']                  = 'user/mainMenu';
$route['subMenu']                   = 'user/subMenu';

// End User

// for Duty
$route['searchPromoDuty']           = 'duty/searchPromoDuty';
$route['dutyInsert']                = 'duty/dutyInsert';

// End Duty


//  -------------------------------------  END Promo TK ----------------------------------------------------


$route['default_controller']        = 'page/menu';
$route['404_override']              = 'pagenotfound';
$route['translate_uri_dashes']      = FALSE;
