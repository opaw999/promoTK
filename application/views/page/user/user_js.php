<script type="text/javascript">
    var accessroles_table;

    $(document).ready(function() {
        accessroles_table = $('#accessroles_table').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('accessroles_table'); ?>",
                type: "POST",
            },
            "order": [
                [1, "asc"]
            ],
            "columnDefs": [{
                "targets": [0, 2, 3, 4],
                "orderable": false,

            }, {
                "targets": [3, 4],
                "className": "text-center"
            }]
        });

    });

    function accessrolesForm(empId) {
        $('div#accessrolesForm').modal({
            backdrop: 'static',
            keyboard: false,
        });
        $("#accessrolesForm").modal("show");

        $.ajax({
            type: "POST",
            url: "<?= site_url('accessrolesForm'); ?>",
            data: {
                empId
            },
            beforeSend: function() {
                loader_circle('accessrolesForm')
            },
            success: function(data) {
                $("div.accessrolesForm").html(data);
            }
        });

    }

    function mainMenu(empId, mm) {

        if ($('input[name="' + mm + '"]').is(':checked')) {
            var process = 'activate';
            $('input.' + mm).prop('checked', true);
        } else {
            var process = 'deactivate';
            $('input.' + mm).prop('checked', false);
        }
        $.ajax({
            url: "<?= site_url('mainMenu'); ?>",
            type: "POST",
            data: {
                empId,
                mm,
                process
            },
            success: function(data) {
                try {
                    let response = JSON.parse(data);
                    if (response.message === "success") {
                        // Swal.fire({
                        //     title: 'Success!',
                        //     text: response.feedback,
                        //     icon: 'success',
                        //     iconColor: '#15ca20',
                        //     confirmButtonText: 'Ok',
                        //     confirmButtonColor: '#0d6efd',
                        //     allowOutsideClick: false,
                        // }).then(() => {
                        // });
                    } else {
                        // errorAlert(response.message)
                    }
                } catch (e) {
                    // console.error("Error:", data);
                    // errorAlert(data)
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                // console.error("AJAX Error:", textStatus, errorThrown);
                // errorAlert(errorThrown)
            }
        });
    }

    function subMenu(empId, mm, sm) {

        if ($('input[name="' + sm + '"]').is(':checked')) {
            var process = 'activate';
            $('input[name="' + mm + '"]').prop('checked', true);
        } else {
            var process = 'deactivate';
        }
        $.ajax({
            url: "<?= site_url('subMenu'); ?>",
            type: "POST",
            data: {
                empId,
                mm,
                sm,
                process
            },
            success: function(data) {
                try {
                    let response = JSON.parse(data);
                    if (response.message === "success") {
                        Swal.fire({
                            title: 'Success!',
                            text: response.feedback,
                            icon: 'success',
                            iconColor: '#15ca20',
                            confirmButtonText: 'Ok',
                            confirmButtonColor: '#0d6efd',
                            allowOutsideClick: false,
                        }).then(() => {
                            // hraccount.ajax.reload();
                        });
                    } else {
                        // errorAlert(response.message)
                    }
                } catch (e) {
                    console.error("Error:", data);
                    // errorAlert(data)
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                // errorAlert(errorThrown)
            }
        });
    }




    function alertRequired(message) {
        Swal.fire({
            title: 'Required!',
            text: message,
            icon: 'warning',
            iconColor: '#ffc107',
            confirmButtonText: 'Ok',
            confirmButtonColor: '#0d6efd',
            allowOutsideClick: false,
        });
    }

    function errorAlert(message) {
        Swal.fire({
            title: 'Error!',
            text: message,
            icon: 'error',
            iconColor: '#dc3545',
            confirmButtonText: 'Ok',
            confirmButtonColor: '#dc3545',
            allowOutsideClick: false,
        })
    }

    function alertInfo(message) {
        Swal.fire({
            title: 'Note!',
            text: message,
            icon: 'info',
            confirmButtonText: 'Ok',
            confirmButtonColor: '#0d6efd',
            allowOutsideClick: false,
        });
    }

    function loader_circle(page) {
        $('div.' + page).html(
            '<div style="text-align: center;">' +
            '<img src="<?= base_url('assets/dist/img/loader_seq.gif') ?>">' +
            '</div>'
        );
    }
</script>