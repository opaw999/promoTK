<footer class="main-footer">
    <strong>Copyright &copy; 2025 Timekeeping. </strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
        <b>Version </b> 2.0
    </div>
</footer>

<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="<?= base_url('assets/plugins/jquery/jquery.min.js') ?>"></script>
<!-- jQuery UI 1.11.4 -->
<script src="<?= base_url('assets/plugins/jquery-ui/jquery-ui.min.js') ?>"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<!-- <script>
    $.widget.bridge('uibutton', $.ui.button)
</script> -->
<!-- Bootstrap 4 -->
<script src="<?= base_url('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') ?>"></script>
<!-- ChartJS -->
<script src="<?= base_url('assets/plugins/chart.js/Chart.min.js') ?>"></script>
<!-- Sparkline -->
<!-- <script src="<?= base_url('assets/plugins/sparklines/sparkline.js') ?>"></script> -->
<!-- JQVMap -->
<script src="<?= base_url('assets/plugins/jqvmap/jquery.vmap.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/jqvmap/maps/jquery.vmap.usa.js') ?>"></script>
<!-- jQuery Knob Chart -->
<script src="<?= base_url('assets/plugins/jquery-knob/jquery.knob.min.js') ?>"></script>
<!-- daterangepicker -->
<script src="<?= base_url('assets/plugins/moment/moment.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/daterangepicker/daterangepicker.js') ?>"></script>
<!-- Tempusdominus Bootstrap 4 -->
<script src="<?= base_url('assets/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') ?>"></script>
<!-- Summernote -->
<script src="<?= base_url('assets/plugins/summernote/summernote-bs4.min.js') ?>"></script>
<!-- overlayScrollbars -->
<script src="<?= base_url('assets/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') ?>"></script>
<!-- AdminLTE App -->
<script src="<?= base_url('assets/dist/js/adminlte_cdea7bfa.js') ?>"></script>
<!-- AdminLTE for demo purposes -->
<!-- <script src="<?= base_url('assets/dist/js/demo.js') ?>"></script> -->
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<!-- <script src="<?= base_url('assets/dist/js/pages/dashboard.js') ?>"></script> -->

<script src="<?= base_url('assets/plugins/select2/js/select2.full.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/bootstrap-datepicker/dist/js/bootstrap-datepicker.js') ?>"></script>
<script src="<?= base_url('assets/plugins/sweetalert2/sweetalert2.all.min.js') ?>"></script>

<link href="<?= base_url('assets/plugins/datatables.net-bs5/css/dataTables.bootstrap5.min.css') ?>" rel="stylesheet" />
<link href="<?= base_url('assets/plugins/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') ?>" rel="stylesheet" />
<script src="<?= base_url('assets/plugins/datatables.net/js/dataTables.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables.net-bs5/js/dataTables.bootstrap5.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/parsleyjs/dist/parsley.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/jquery-validation/jquery.validate.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/jquery-validation/additional-methods.min.js') ?>"></script>
<script src="<?= base_url('assets/plugins/bootstrap-switch/js/bootstrap-switch.min.js') ?>"></script>


<script>
    $(document).ready(function() {
        $('.nav-item > a').on('click', function(e) {
            var $this = $(this);
            var $parent = $this.parent();
            var $submenu = $this.next('.nav-treeview');
            $('.nav-item.menu-open').not($parent).children('a').trigger('click');
        });
    });
</script>
</body>

</html>