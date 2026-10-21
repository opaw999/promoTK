<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Original URL: https://adminlte.io/themes/v3/index.html
    Date Downloaded: 10/17/2025 8:47:39 AM !-->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Promo | Timekeeping </title>
    <!--favicon-->
    <link rel="icon" href="http://172.16.161.34:8080/hrms/promoV2/assets/promo_assets/images/cart2.png" type="image/png" />
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="<?= base_url('assets/fonts.googleapis.com/css_df518168.css') ?>" />
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/fontawesome-free/css/all.min.css') ?>" />
    <!-- Ionicons -->
    <link rel="stylesheet" href="<?= base_url('assets/code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css') ?>" />
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') ?>" />
    <!-- iCheck -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/icheck-bootstrap/icheck-bootstrap.min.css') ?>" />
    <!-- JQVMap -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/jqvmap/jqvmap.min.css') ?>" />
    <!-- Theme style -->
    <link rel="stylesheet" href="<?= base_url('assets/dist/css/adminlte.min_cdea7bfa.css') ?>" />
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') ?>" />
    <!-- Daterange picker -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/daterangepicker/daterangepicker.css') ?>" />
    <!-- summernote -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/summernote/summernote-bs4.min.css') ?>" />

    <link href="<?= base_url('assets/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker.css') ?>" rel="stylesheet" />
    <link href="<?= base_url('assets/plugins/sweetalert2/sweetalert2.min.css') ?>" rel="stylesheet" />
    <link href="<?= base_url('assets/plugins/bootstrap-icons/font/bootstrap-icons.css') ?>" rel="stylesheet" />
    <link href="<?= base_url('assets/plugins/select2/css/select2.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') ?>" rel="stylesheet">
</head>

<body class="layout-fixed layout-navbar-fixed text-sm">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light bg-lightblue">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link text-white" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <!-- Notifications Dropdown Menu -->
                <li class="nav-item dropdown">
                    <a class="nav-link text-white" data-toggle="dropdown" href="#">
                        <i class="far fa-bell text-warning"></i>
                        <!-- <span class="badge badge-danger navbar-badge">0 </span> -->
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                        <span class="dropdown-item dropdown-header">Pending Requests (<?= date('Y') ?>) </span>
                        <div class="dropdown-divider"></div>
                        <a href="<?= base_url('viewreq/dwa') ?>" class="dropdown-item">
                            <i class="fas fa-clock mr-2"></i> DWA
                            <span class="float-right text-muted text-sm"><i>(<span class="text-danger"><?= $dwa ?></span>) Adjustment(s)</i> </span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?= base_url('viewreq/loa') ?>" class="dropdown-item">
                            <i class="fas fa-clock mr-2"></i> LOA
                            <span class="float-right text-muted text-sm"><i>(<span class="text-danger"><?= $rla ?></span>) Absence(s)</i> </span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?= base_url('viewreq/otAuth') ?>" class="dropdown-item">
                            <i class="fas fa-clock mr-2"></i> EOA
                            <span class="float-right text-muted text-sm"><i>(<span class="text-danger"><?= $eoa ?></span>) Overtime(s)</i> </span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="<?= base_url('viewreq/cuAuth') ?>" class="dropdown-item">
                            <i class="fas fa-clock mr-2"></i> ECA
                            <span class="float-right text-muted text-sm"><i>(<span class="text-danger"><?= $eca ?></span>) Coverup(s)</i> </span>
                        </a>
                        <div class="dropdown-divider mb-3"></div>
                    </div>
                </li>
                <li class="nav-item dropdown user-menu">
                    <a href="#" class="nav-link text-white dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                        <span class="d-none d-md-inline"><?= $user['name'] ?></span> <i class="right fas fa-angle-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="left: inherit; right: 0px;">

                        <?php
                        $link = "http://172.16.161.34:8080/hrms/promo/$user[photo]";
                        ?>
                        <li class="user-header bg-lightblue">
                            <img src="<?= $link ?>" class="img-circle elevation-2" alt="User Image">
                            <p><?= $user['name'] ?></p>
                            <small><?= $user['position'] ?></small>
                        </li>

                        <li class="user-footer">
                            <a href="http://172.16.161.34/timekeeping/?p=changeA" class="btn btn-default btn-flat">Profile</a>
                            <a href="<?= base_url('logout') ?>" class="btn btn-default btn-flat float-right">Sign out</a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>