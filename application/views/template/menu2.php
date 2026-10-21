<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-light-lightblue elevation-4">
    <!-- Brand Logo -->
    <a href="<?= base_url() ?>" class="brand-link bg-lightblue">
        <img src="<?= base_url('assets/dist/img/tk.png') ?>" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8" />
        <span class="brand-text font-weight-bold">PROMO TIMEKEEPING </span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex align-items-center">
            <div class="user-block">
                <?php
                $link = "http://172.16.161.34:8080/hrms/promo/$user[photo]";
                ?>
                <img class="img-circle img-bordered-sm" src="<?= $link ?>" alt="user image">
                <span class="username">
                    <?= $username . ' | ' . $account['description'] ?>
                </span>
                <span class="description"><i class="fa fa-circle text-success"></i> Online</span>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="<?= base_url('../') ?>" class="nav-link">
                        <i class="nav-icon fas fa-arrow-left"></i>
                        <p>Return </p>
                    </a>
                </li>
                <?php







                $cond = ['empId' => $this->userId, 'menu' => 'add'];
                $roles = $this->promo_model->select_rowArray('tk', 'promo_access_roles', $cond);
                $sm = [];
                if ($roles['submenu']) {
                    $sm = explode('|', $roles['submenu']);
                }
                ?>
                <?php if ($roles) { ?>
                    <li class="nav-item <?= ($menu == 'add' ? 'menu-open' : '') ?>">
                        <a href="#" class="nav-link <?= ($menu == 'add' ? 'active' : '') ?>">
                            <i class="nav-icon fas fa-plus"></i>
                            <p>Add <i class="fas fa-angle-left right"></i></p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (in_array('cutoff', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('add/cutoff') ?>" class="nav-link <?= ($file == 'cutoff' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'cutoff' ? 'text-info' : '') ?>"></i>
                                        <p>Promo Cutoff </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('manual_ot', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('add/manual_ot') ?>" class="nav-link <?= ($file == 'manual_ot' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'manual_ot' ? 'text-info' : '') ?>"></i>
                                        <p>Manual Overtime </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('holiday', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('add/holiday') ?>" class="nav-link <?= ($file == 'holiday' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'holiday' ? 'text-info' : '') ?>"></i>
                                        <p>Holiday </p>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>

                <?php
                $cond = ['empId' => $this->userId, 'menu' => 'setup'];
                $roles = $this->promo_model->select_rowArray('tk', 'promo_access_roles', $cond);
                $sm = [];
                if ($roles['submenu']) {
                    $sm = explode('|', $roles['submenu']);
                }
                ?>
                <?php if ($roles) { ?>
                    <li class="nav-item <?= ($menu == 'setup' ? 'menu-open' : '') ?>">
                        <a href="#" class="nav-link <?= ($menu == 'setup' ? 'active' : '') ?>">
                            <i class="nav-icon fas fa-wrench"></i>
                            <p>
                                Setup
                                <i class="right fas fa-angle-left"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (in_array('app_dis', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('setup/app_dis') ?>" class="nav-link <?= ($file == 'app_dis' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'app_dis' ? 'text-info' : '') ?>"></i>
                                        <p>App/Dis Button </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('cutoff_process', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('setup/cutoff_process') ?>" class="nav-link <?= ($file == 'cutoff_process' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'cutoff_process' ? 'text-info' : '') ?>"></i>
                                        <p>Cutoff to Process </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('enable_addon', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('setup/enable_addon') ?>" class="nav-link <?= ($file == 'enable_addon' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'enable_addon' ? 'text-info' : '') ?>"></i>
                                        <p>Enable Addon </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('manual_inputs', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('setup/manual_inputs') ?>" class="nav-link <?= ($file == 'manual_inputs' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'manual_inputs' ? 'text-info' : '') ?>"></i>
                                        <p>Manual Inputs </p>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
                <?php
                $cond = ['empId' => $this->userId, 'menu' => 'view'];
                $roles = $this->promo_model->select_rowArray('tk', 'promo_access_roles', $cond);
                $sm = [];
                if ($roles['submenu']) {
                    $sm = explode('|', $roles['submenu']);
                }
                ?>
                <?php if ($roles) { ?>
                    <li class="nav-item <?= ($menu == 'view' ? 'menu-open' : '') ?>">
                        <a href="#" class="nav-link <?= ($menu == 'view' ? 'active' : '') ?>">
                            <i class="nav-icon fas fa-list"></i>
                            <p>
                                View
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">

                            <?php if (in_array('masterfile', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('view/masterfile') ?>" class="nav-link <?= ($file == 'masterfile' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'masterfile' ? 'text-info' : '') ?>"></i>
                                        <p>Masterfile </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('date_assigned', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('view/date_assigned') ?>" class="nav-link <?= ($file == 'date_assigned' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'date_assigned' ? 'text-info' : '') ?>"></i>
                                        <p>Date Assigned </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('holidays', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('view/holidays') ?>" class="nav-link <?= ($file == 'holidays' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'holidays' ? 'text-info' : '') ?>"></i>
                                        <p>Holidays </p>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>

                <!-- <li class="nav-item <?= ($menu == 'upload' ? 'menu-open' : '') ?>">
                    <a href="#" class="nav-link <?= ($menu == 'upload' ? 'active' : '') ?>">
                        <i class="nav-icon fas fa-upload"></i>
                        <p>
                            Upload Logs
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>
                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="<?= base_url('upload/logbox') ?>" class="nav-link <?= ($file == 'logbox' ? 'active' : '') ?>">
                                <i class="far fa-circle nav-icon <?= ($file == 'logbox' ? 'text-info' : '') ?>"></i>
                                <p>PROMO - Logbox </p>
                            </a>
                        </li>
                    </ul>
                </li> -->
                <?php
                $cond = ['empId' => $this->userId, 'menu' => 'process'];
                $roles = $this->promo_model->select_rowArray('tk', 'promo_access_roles', $cond);
                $sm = [];
                if ($roles['submenu']) {
                    $sm = explode('|', $roles['submenu']);
                }
                ?>
                <?php if ($roles) { ?>
                    <li class="nav-item <?= ($menu == 'process' ? 'menu-open' : '') ?>">
                        <a href="#" class="nav-link <?= ($menu == 'process' ? 'active' : '') ?>">
                            <i class="nav-icon fas fa-server"></i>
                            <p>
                                Process
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (in_array('single', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('process/single') ?>" class="nav-link <?= ($file == 'single' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'single' ? 'text-info' : '') ?>"></i>
                                        <p>Single Process </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('logbox', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('process/logbox') ?>" class="nav-link <?= ($file == 'logbox' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'logbox' ? 'text-info' : '') ?>"></i>
                                        <p>Logbox </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('biometric', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('process/biometric') ?>" class="nav-link <?= ($file == 'biometric' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'biometric' ? 'text-info' : '') ?>"></i>
                                        <p>Biometric </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('change_shift', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('process/change_shift') ?>" class="nav-link <?= ($file == 'change_shift' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'change_shift' ? 'text-info' : '') ?>"></i>
                                        <p>Change Shift [1st] </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('off_duty', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('process/off_duty') ?>" class="nav-link <?= ($file == 'off_duty' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'off_duty' ? 'text-info' : '') ?>"></i>
                                        <p>Off Duty [2nd] </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('process_per_cutoff', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('process/process_per_cutoff') ?>" class="nav-link <?= ($file == 'process_per_cutoff' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'process_per_cutoff' ? 'text-info' : '') ?>"></i>
                                        <p>Per Cutoff [3rd] </p>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
                <?php
                $cond = ['empId' => $this->userId, 'menu' => 'dtr'];
                $roles = $this->promo_model->select_rowArray('tk', 'promo_access_roles', $cond);
                $sm = [];
                if ($roles['submenu']) {
                    $sm = explode('|', $roles['submenu']);
                }
                ?>
                <?php if ($roles) { ?>
                    <li class="nav-item <?= ($menu == 'dtr' ? 'menu-open' : '') ?>">
                        <a href="#" class="nav-link <?= ($menu == 'dtr' ? 'active' : '') ?>">
                            <i class="nav-icon fas fa-calendar"></i>
                            <p>
                                DTR Monitoring
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (in_array('per_cutoff', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('dtr/per_cutoff') ?>" class="nav-link <?= ($file == 'per_cutoff' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'per_cutoff' ? 'text-info' : '') ?>"></i>
                                        <p>Per Cutoff </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('per_employee', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('dtr/per_employee') ?>" class="nav-link <?= ($file == 'per_employee' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'per_employee' ? 'text-info' : '') ?>"></i>
                                        <p>Per Employee </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('earlyCo', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('dtr/earlyCo') ?>" class="nav-link <?= ($file == 'earlyCo' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'earlyCo' ? 'text-info' : '') ?>"></i>
                                        <p>Early Cutoff </p>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
                <?php
                $cond = ['empId' => $this->userId, 'menu' => 'viewreq'];
                $roles = $this->promo_model->select_rowArray('tk', 'promo_access_roles', $cond);
                $sm = [];
                if ($roles['submenu']) {
                    $sm = explode('|', $roles['submenu']);
                }
                ?>
                <?php if ($roles) { ?>
                    <li class="nav-item <?= ($menu == 'viewreq' ? 'menu-open' : '') ?>">
                        <a href="#" class="nav-link <?= ($menu == 'viewreq' ? 'active' : '') ?>">
                            <i class="nav-icon fas fa-folder"></i>
                            <p>
                                View Request
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (in_array('otAuth', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('viewReq/otAuth') ?>" class="nav-link <?= ($file == 'otAuth' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'otAuth' ? 'text-info' : '') ?>"></i>
                                        <p>Overtime Authorization </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('otPayment', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('viewReq/otPayment') ?>" class="nav-link <?= ($file == 'otPayment' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'otPayment' ? 'text-info' : '') ?>"></i>
                                        <p>Overtime Payment </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('cuAuth', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('viewReq/cuAuth') ?>" class="nav-link <?= ($file == 'cuAuth' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'cuAuth' ? 'text-info' : '') ?>"></i>
                                        <p>Coverup Authorization </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('utAuth', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('viewReq/utAuth') ?>" class="nav-link <?= ($file == 'utAuth' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'utAuth' ? 'text-info' : '') ?>"></i>
                                        <p>Undertime Authorization </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('dwa', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('viewReq/dwa') ?>" class="nav-link <?= ($file == 'dwa' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'dwa' ? 'text-info' : '') ?>"></i>
                                        <p style="font-size: 12px;">Days Worked Adjustment </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('loa', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('viewReq/loa') ?>" class="nav-link <?= ($file == 'loa' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'loa' ? 'text-info' : '') ?>"></i>
                                        <p>Leave of Absence </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('doShort', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('viewReq/doShort') ?>" class="nav-link <?= ($file == 'doShort' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'doShort' ? 'text-info' : '') ?>"></i>
                                        <p style="font-size: 12px;">Change Dayoff (Short Term) </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('doLong', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('viewReq/doLong') ?>" class="nav-link <?= ($file == 'doLong' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'doLong' ? 'text-info' : '') ?>"></i>
                                        <p style="font-size: 12px;">Change Dayoff (Long Term) </p>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
                <?php
                $cond = ['empId' => $this->userId, 'menu' => 'concerns'];
                $roles = $this->promo_model->select_rowArray('tk', 'promo_access_roles', $cond);
                $sm = [];
                if ($roles['submenu']) {
                    $sm = explode('|', $roles['submenu']);
                }
                ?>
                <?php if ($roles) { ?>
                    <li class="nav-item <?= ($menu == 'concerns' ? 'menu-open' : '') ?>">
                        <a href="#" class="nav-link <?= ($menu == 'concerns' ? 'active' : '') ?>">
                            <i class="nav-icon fas fa-toolbox"></i>
                            <p>
                                Concerns
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>
                        <ul class="nav nav-treeview">
                            <?php if (in_array('ot_roving', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('concerns/ot_roving') ?>" class="nav-link <?= ($file == 'ot_roving' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'ot_roving' ? 'text-info' : '') ?>"></i>
                                        <p>Overtime Roving </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('hr_account', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('concerns/hr_account') ?>" class="nav-link <?= ($file == 'hr_account' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'hr_account' ? 'text-info' : '') ?>"></i>
                                        <p>HR Account </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('portal_export', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('concerns/portal_export') ?>" class="nav-link <?= ($file == 'portal_export' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'portal_export' ? 'text-info' : '') ?>"></i>
                                        <p>Portal Export Access </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('promoReqList', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('concerns/promoReqList') ?>" class="nav-link <?= ($file == 'promoReqList' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'promoReqList' ? 'text-info' : '') ?>"></i>
                                        <p style="font-size: 12px;">Promo's Request Access List </p>
                                    </a>
                                </li>
                            <?php } ?>
                            <?php if (in_array('setupList', $sm)) { ?>
                                <li class="nav-item">
                                    <a href="<?= base_url('concerns/setupList') ?>" class="nav-link <?= ($file == 'setupList' ? 'active' : '') ?>">
                                        <i class="far fa-circle nav-icon <?= ($file == 'setupList' ? 'text-info' : '') ?>"></i>
                                        <p>Supervisor List </p>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </li>
                <?php } ?>
                <?php
                $cond = ['empId' => $this->userId, 'menu' => 'user'];
                $roles = $this->promo_model->select_rowArray('tk', 'promo_access_roles', $cond);
                ?>
                <?php if ($roles) { ?>
                    <li class="nav-item">
                        <a href="<?= base_url('user/access') ?>" class="nav-link">
                            <i class="nav-icon fas fa-lock"></i>
                            <p>User Access Roles </p>
                        </a>
                    </li>
                <?php } ?>

                <li class="nav-item">
                    <a href="<?= base_url('assets/userguide.pdf'); ?>" target="_blank" class="nav-link">
                        <i class="nav-icon fas fa-file"></i>
                        <p>User Guide </p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>