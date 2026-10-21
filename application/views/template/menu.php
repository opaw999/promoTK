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


                $cond   = ['status' => 'active'];
                $query  = $this->promo_model->select_resultArray('tk', 'promo_menu', $cond);
                foreach ($query as $key => $row) {

                    $menuId     = $row['menu_id'];
                    $mm         = $row['menu'];
                    $menuName   = $row['menu_name'];
                    $menuIcon   = $row['menu_icon'];

                    $cond = ['empId' => $this->userId, 'menu' => $mm];
                    $chk1 = $this->promo_model->select_rowArray('tk', 'promo_access_roles', $cond);
                    if ($chk1) {

                        $sms = [];
                        if ($chk1['submenu']) {
                            $sms = explode('|', $chk1['submenu']);
                        }

                        if ($menuId != '8' && $menuId != '9') {
                            $open = ($menu == $mm) ? 'menu-open' : '';
                            $active = ($menu == $mm) ? 'active' : '';
                            $menuLink = '#';
                            $icon = '<i class="right fas fa-angle-left"></i>';
                        } else {
                            $open = '';
                            $active = ($menu == $mm) ? 'active' : '';
                            $icon = '';
                            if ($menuId == '8') {
                                $menuLink = base_url('user/access');
                            } else if ($menuId == '9') {
                                $menuLink = base_url('duty/insert_duty');
                            } else {
                                $menuLink = '#';
                            }
                        }

                        echo '<li class="nav-item ' . $open . '">
                                <a href="' . $menuLink . '" class="nav-link ' . $active . '">
                                    <i class="nav-icon ' . $menuIcon . '"></i>
                                    <p>' . $menuName . ' ' . $icon . '</p>
                                </a>';
                        if ($chk1['submenu']) {

                            echo '<ul class="nav nav-treeview">';

                            // $cond   = ['menu_id' => $menuId, 'status' => 'active'];
                            $cond   = "menu_id='$menuId' AND status='active' ORDER BY submenu_order ASC";
                            $query  = $this->promo_model->select_resultArray('tk', 'promo_submenu', $cond);
                            foreach ($query as $key => $sub) {

                                $sm         = $sub['submenu'];
                                $link       = base_url("$mm/$sm");
                                $subName    = $sub['submenu_name'];

                                if (in_array($sm, $sms)) {
                                    echo '<li class="nav-item">
                                            <a href="' . $link . '" class="nav-link ' . ($file == $sm ? 'active' : '') . '">
                                                <i class="far fa-circle nav-icon ' . ($file == $sm ? 'text-info' : '') . '"></i>
                                                <p>' . $subName . ' </p>
                                            </a>';
                                    echo '</li>';
                                }
                            }
                            echo '</ul>';
                        }
                        echo '</li>';
                    }
                }

                ?>



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