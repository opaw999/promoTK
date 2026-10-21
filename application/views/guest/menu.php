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
                    <?= $username . ' | guest' ?>
                </span>
                <span class="description"><i class="fa fa-circle text-success"></i> Online</span>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item">
                    <a href="http://172.16.161.34/timekeeping/client/" class="nav-link">
                        <i class="nav-icon fas fa-arrow-left"></i>
                        <p>Return </p>
                    </a>
                </li>
                <li class="nav-item menu-is-opening menu-open">
                    <a href="#" class="nav-link active">
                        <i class="nav-icon fas fa-calendar"></i>
                        <p>DTR Monitoring <i class="right fas fa-angle-left"></i></p>
                    </a>
                    <ul class="nav nav-treeview" style="display: block;">
                        <li class="nav-item">
                            <a href="http://172.16.161.34/timekeeping/promoTK/dtr/per_cutoff" class="nav-link active">
                                <i class="far fa-circle nav-icon text-info"></i>
                                <p>Per Cutoff </p>
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>