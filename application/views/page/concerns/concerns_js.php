<script type="text/javascript">
    var hraccount;
    var exportusers;
    $(document).ready(function() {
        $('#dateOt,#date').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false
        });

        $('.select2').select2({
            theme: 'bootstrap4',
            allowClear: true
        })

        hraccount = $('#hraccount_table').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('hrAccount_list'); ?>",
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

        exportusers = $('#exportuser_table').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('exportuser_list'); ?>",
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

        promoReqList = $('#promoReqList_tables').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('promoReqList'); ?>",
                type: "POST",
            },
            "order": [
                [1, "asc"]
            ],
            "columnDefs": [{
                "targets": [0, 2, 3, 4],
                "orderable": false,

            }, {
                "targets": [4],
                "className": "text-center"
            }]
        });

        setupSupList = $('#setupSup_tables').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('setupSupList'); ?>",
                type: "POST",
            },
            "order": [
                [1, "asc"]
            ],
            "columnDefs": [{
                "targets": [0, 2, 3, 4, 5],
                "orderable": false,

            }, {
                "targets": [5],
                "className": "text-center"
            }]
        });

        $.validator.setDefaults({
            submitHandler: function(form) {
                var formId = form.id;

                if (formId == 'add_hraccount') {
                    var formData = $(form).serialize();

                    Swal.fire({
                        title: 'Confirm!',
                        text: 'Add Promo HR account.',
                        icon: 'question',
                        iconColor: '#ffc107',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes',
                    }).then((result) => {
                        if (result.value) {
                            Swal.fire({
                                icon: 'info',
                                title: 'Please wait...',
                                text: 'Adding account.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            setTimeout(() => {
                                $.ajax({
                                    url: "<?= site_url('hrAccount'); ?>",
                                    type: "POST",
                                    data: formData,
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
                                                    hraccount.ajax.reload();
                                                });
                                            } else {
                                                errorAlert(response.message)
                                            }
                                        } catch (e) {
                                            console.error("Error:", data);
                                            errorAlert(data)
                                        }
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        console.error("AJAX Error:", textStatus, errorThrown);
                                        errorAlert(errorThrown)
                                    }
                                });
                            }, 2000);
                        }
                    })
                } else if (formId == 'add_portalaccount') {
                    var formData = $(form).serialize();

                    Swal.fire({
                        title: 'Confirm!',
                        text: 'Add portal export user.',
                        icon: 'question',
                        iconColor: '#ffc107',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes',
                    }).then((result) => {
                        if (result.value) {
                            Swal.fire({
                                icon: 'info',
                                title: 'Please wait...',
                                text: 'Adding user.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            setTimeout(() => {
                                $.ajax({
                                    url: "<?= site_url('portalAccess'); ?>",
                                    type: "POST",
                                    data: formData,
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
                                                    $('select[name="empId"]').val(null).trigger('change');
                                                    exportusers.ajax.reload();
                                                });
                                            } else {
                                                errorAlert(response.message)
                                            }
                                        } catch (e) {
                                            console.error("Error:", data);
                                            errorAlert(data)
                                        }
                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        console.error("AJAX Error:", textStatus, errorThrown);
                                        errorAlert(errorThrown)
                                    }
                                });
                            }, 2000);
                        }
                    })
                }
            }
        });

        $('#add_hraccount,#add_portalaccount').validate({
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function(element) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element) {
                $(element).removeClass('is-invalid');
            },
        });
    });

    let ajaxCallCount = 0;
    let ajaxRequest;

    function searchPromos(string) {
        $('select[name="store"]').val('');
        $('input[name="overtime"]').val('');
        $('input[name="dateOT"]').val('');
        $('.promoDetails').html('');
        if (string.trim() != '') {

            if (ajaxRequest && ajaxRequest.readyState !== 4) {
                ajaxRequest.abort();
            }

            ajaxCallCount++;

            ajaxRequest = $.ajax({
                type: "POST",
                url: "<?= site_url('concerns_searchPromo'); ?>",
                data: {
                    string,
                },
                beforeSend: function() {
                    $('.dropdown-list').html('<ul class="list-group"><span class="list-group-item">' +
                        '<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>' +
                        '</span></ul>');
                },
                success: function(data) {

                    $(".dropdown-list").show()
                    $(".dropdown-list").html(data);
                }
            });
        } else {
            $(".dropdown-list").hide();
        }
    }

    function selectPromo(name, empId, recordNo) {
        $(".dropdown-list").hide().html('');
        $('input[name="searchPromo"]').val('[' + empId + '] ' + name);
        ot_list(empId)

        promoDetails(empId, recordNo)
    }

    function promoDetails(empId, recordNo) {
        $.ajax({
            type: "POST",
            url: "<?= site_url('promoDetails'); ?>",
            data: {
                empId,
                recordNo
            },
            beforeSend: function() {
                loader_circle('promoDetails')
            },
            success: function(data) {
                $('.promoDetails').html(data);
            }
        });
    }

    function ot_list(empId) {
        $('#ot_list').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('ot_list'); ?>",
                type: "POST",
                data: {
                    empId
                }
            },
            "order": [
                [0, "desc"]
            ],
            "columnDefs": [{
                "targets": [1, 2, 3],
                "orderable": false,

            }, {
                "targets": [1],
                "className": "text-center"
            }]
        });
    }

    function changeStore(store, id, str) {
        console.log(store, id, str)
        Swal.fire({
            title: 'Confirm!',
            text: 'Change store.',
            icon: 'question',
            iconColor: '#ffc107',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
        }).then((result) => {
            if (result.value) {
                Swal.fire({
                    icon: 'info',
                    title: 'Please wait...',
                    text: 'Changing store.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                setTimeout(() => {
                    $.ajax({
                        url: "<?= site_url('change_store'); ?>",
                        type: "POST",
                        data: {
                            store,
                            id
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
                                        location.reload()
                                    });
                                } else {
                                    errorAlert(response.message)
                                }
                            } catch (e) {
                                console.error("Error:", data);
                                errorAlert(data)
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error("AJAX Error:", textStatus, errorThrown);
                            errorAlert(errorThrown)
                        }
                    });
                }, 2000);
            } else {
                $('select#' + id).val(str);

            }
        })
    }

    function hrAccount(id, action) {

        if (action == 'delete') {
            var msg1 = 'Delete Promo HR account.';
            var msg2 = 'Deleting account.';
        } else {
            if (action == 'activate') {
                var msg1 = 'Activate Promo HR account.';
                var msg2 = 'Activating account.';
            } else {
                var msg1 = 'Deactivate Promo HR account.';
                var msg2 = 'Deactivating account.';
            }
        }

        Swal.fire({
            title: 'Confirm!',
            text: msg1,
            icon: 'question',
            iconColor: '#ffc107',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
        }).then((result) => {
            if (result.value) {
                Swal.fire({
                    icon: 'info',
                    title: 'Please wait...',
                    text: msg2,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                setTimeout(() => {
                    $.ajax({
                        url: "<?= site_url('hrAccount'); ?>",
                        type: "POST",
                        data: {
                            id,
                            action
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
                                        hraccount.ajax.reload();
                                    });
                                } else {
                                    errorAlert(response.message)
                                }
                            } catch (e) {
                                console.error("Error:", data);
                                errorAlert(data)
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error("AJAX Error:", textStatus, errorThrown);
                            errorAlert(errorThrown)
                        }
                    });
                }, 2000);
            }
        })
    }

    function portalAccess(empId, action) {
        Swal.fire({
            title: 'Confirm!',
            text: 'Delete portal export user.',
            icon: 'question',
            iconColor: '#ffc107',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
        }).then((result) => {
            if (result.value) {
                Swal.fire({
                    icon: 'info',
                    title: 'Please wait...',
                    text: 'Deleting user.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                setTimeout(() => {
                    $.ajax({
                        url: "<?= site_url('portalAccess'); ?>",
                        type: "POST",
                        data: {
                            empId,
                            action
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
                                        exportusers.ajax.reload();
                                    });
                                } else {
                                    errorAlert(response.message)
                                }
                            } catch (e) {
                                console.error("Error:", data);
                                errorAlert(data)
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            console.error("AJAX Error:", textStatus, errorThrown);
                            errorAlert(errorThrown)
                        }
                    });
                }, 2000);
            }
        })
    }

    function promoReqAccess(id, func) {
        if (func == 'add') {
            var id = $('select[name="empId"]').val()
            var msg1 = 'Add promo request access.';
            var msg2 = 'Adding promo request access...';
            var msg3 = 'Promo request access has been added.';
        } else if (func == 'delete') {
            var msg1 = 'Delete promo request access.';
            var msg2 = 'Deleting promo request access...';
            var msg3 = 'Promo request access has been deleted.';
        } else if (func == 'update') {
            var msg1 = 'Update promo request access.';
            var msg2 = 'Updating promo request access...';
            var msg3 = 'Promo request access has been updated.';
        }
        console.log(id, func)
        if (id != '') {
            Swal.fire({
                title: 'Confirm!',
                text: msg1,
                icon: 'question',
                iconColor: '#ffc107',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Please wait!',
                        text: msg2,
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    setTimeout(() => {
                        $.ajax({
                            type: "POST",
                            url: "<?= site_url('promoReqAccess'); ?>",
                            data: {
                                id,
                                func,
                            },
                            success: function(data) {
                                try {
                                    let response = JSON.parse(data);
                                    if (response.message == 'success') {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: msg3,
                                            icon: 'success',
                                            iconColor: '#15ca20',
                                            confirmButtonColor: '#3085d6',
                                            confirmButtonText: 'Ok',
                                            allowOutsideClick: false
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                promoReqList.ajax.reload();
                                            }
                                        });
                                    } else if (response.message == 'exist') {
                                        alertInfo('Employee ID already have an access.')
                                    } else if (response.message == 'cleared') {
                                        alertInfo('Employee is inactive.')
                                    } else if (response.message == 'mismatch') {
                                        errorAlert('Employee ID does not exist.')
                                    } else {
                                        Swal.close();
                                        errorAlert(response.message)
                                    }
                                } catch (e) {
                                    Swal.close();
                                    console.error("Error:", data);
                                    errorAlert(data)
                                }
                            },
                            error: function(jqXHR, textStatus, errorThrown) {
                                Swal.close();
                                console.error("AJAX Error:", textStatus, errorThrown);
                                errorAlert(errorThrown)
                            }
                        });
                    }, 2000);
                }
            })
        } else {
            alertRequired('Please select employee.');
            $("select[name='empId']").next().find(".select2-selection").css("border-color", "#dd4b39");

            $("select[name='empId']").css("border-color", "#dd4b39");
            $(".select2-selection").on("focus", function() {
                $(this).css("border-color", "");
            });
        }
    }

    function setupListAction(id, action) {
        console.log(id, action)
        if (action == 'delete') {
            var msg1 = 'Delete supervisor account.';
            var msg2 = 'Deleting supervisor account...';
            var msg3 = 'Supervisor account has been deleted.';
        } else if (action == 'update') {
            var msg1 = 'Update supervisor account status.';
            var msg2 = 'Updating supervisor account status...';
            var msg3 = 'Supervisor account status has been updated.';
        } else if (action == 'cutoff') {
            var msg1 = 'Update supervisor account cutoff.';
            var msg2 = 'Updating supervisor account cutoff...';
            var msg3 = 'Supervisor account cutoff has been updated.';
        } else if (action == 'add') {
            var msg1 = 'Add supervisor account.';
            var msg2 = 'Adding supervisor account...';
            var msg3 = 'Supervisor account has been added.';
        } else {
            var msg1 = 'Update cutoff to supervisor account.';
            var msg2 = 'Updating cutoff to supervisor account...';
            var msg3 = 'Supervisor account cutoff has been updated.';
        }

        Swal.fire({
            title: 'Confirm!',
            text: msg1,
            icon: 'question',
            iconColor: '#ffc107',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            allowOutsideClick: false,
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'info',
                    title: 'Please wait!',
                    text: msg2,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                setTimeout(() => {
                    $.ajax({
                        type: "POST",
                        url: "<?php echo site_url('setupUpdates'); ?>",
                        data: {
                            id,
                            action
                        },
                        success: function(data) {
                            try {
                                let response = JSON.parse(data);
                                if (response.message == 'success') {
                                    $('#addSupForm').modal('hide');
                                    $('#cutOffForm').modal('hide');
                                    Swal.fire({
                                        title: 'Success!',
                                        text: msg3,
                                        icon: 'success',
                                        iconColor: '#15ca20',
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'Ok',
                                        allowOutsideClick: false,
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            window.location = "<?= base_url('concerns/setupList') ?>";
                                        }
                                    });
                                } else {
                                    Swal.close();
                                    errorAlert(response.message)
                                }
                            } catch (e) {
                                Swal.close();
                                console.error("Error:", data);
                                errorAlert(data)
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            Swal.close();
                            console.error("AJAX Error:", textStatus, errorThrown);
                            errorAlert(errorThrown)
                        }
                    });
                }, 2000);
            }
        })

    }

    function addSupForm() {
        $('div#addSupForm').modal({
            backdrop: 'static',
            keyboard: false,
        });
        $("#addSupForm").modal("show");
    }

    function addcutOff(id) {
        if (id != '') {
            $.ajax({
                type: "POST",
                url: "<?= site_url('cutOffSelection'); ?>",
                data: {
                    id
                },
                beforeSend: function() {
                    loader_circle('cutOff')
                },
                success: function(data) {
                    $("div.cutOff").html(data);
                    $('.form-check-input:not(:checked)').css('border-color', '#aaa');
                    $('.form-check-input').change(function() {
                        $('.form-check-input:not(:checked)').css('border-color', '#aaa');
                        if (!$(this).is(':checked')) {
                            $(this).css('border-color', '#aaa');
                        } else {
                            $(this).css('border-color', '');
                        }
                    });
                }
            });
        } else {
            $("div.cutOff").html('');
        }
    }

    function addSupCutoff() {
        var empId = $('select[name="empId"]').val();
        var id = [];
        $('.form-check-input.selCutoff:checked').each(function() {
            id.push($(this).val());
        });

        if (empId == '') {
            alertRequired('Please select employee.');
        } else if (id.length == 0) {
            alertRequired('Please select cutoff(s).');
        } else {
            id.unshift(empId);

            setupListAction(id, 'add')
        }
    }

    function addSupCutoff2() {
        var id = [];
        $('.form-check-input.selCutoff:checked').each(function() {
            id.push($(this).val());
        });

        if (id.length != 0) {

            setupListAction(id, 'addCutoff')
        } else {
            alertRequired('Please select cutoff(s).');
        }
    }

    function selectAll(id) {
        if ($('#' + id).is(':checked')) {
            $('input.selCutoff').prop('checked', true).css('border-color', '');
        } else {
            $('input.selCutoff').prop('checked', false).css('border-color', '#aaa');
        }
    }

    function selectAll2(id, emp_id) {
        if ($('#' + id).is(':checked')) {
            $('input.selCutoff').prop('checked', true).css('border-color', '');
        } else {
            $.ajax({
                type: "POST",
                url: "<?= site_url('cutOffSelection'); ?>",
                data: {
                    id: emp_id
                },
                beforeSend: function() {
                    loader_circle('cutOffForm')
                },
                success: function(data) {
                    $("div.cutOffForm").html(data);
                    $('.form-check-input:not(:checked)').css('border-color', '#aaa');
                    $('.form-check-input').change(function() {
                        $('.form-check-input:not(:checked)').css('border-color', '#aaa');
                        if (!$(this).is(':checked')) {
                            $(this).css('border-color', '#aaa');
                        } else {
                            $(this).css('border-color', '');
                        }
                    });
                }
            });
        }
    }

    function cutOffSelection(id) {
        $('div#cutOffForm').modal({
            backdrop: 'static',
            keyboard: false,
        });
        $("#cutOffForm").modal("show");

        $.ajax({
            type: "POST",
            url: "<?= site_url('cutOffSelection'); ?>",
            data: {
                id
            },
            beforeSend: function() {
                loader_circle('cutOffForm')
            },
            success: function(data) {
                $("div.cutOffForm").html(data);
                $('.form-check-input:not(:checked)').css('border-color', '#aaa');
                $('.form-check-input').change(function() {
                    $('.form-check-input:not(:checked)').css('border-color', '#aaa');
                    if (!$(this).is(':checked')) {
                        $(this).css('border-color', '#aaa');
                    } else {
                        $(this).css('border-color', '');
                    }
                });
            }
        });

    }

    function checkboxColor(id) {
        var isChecked = $('input[id="' + id + '"]').prop('checked');
        $('span.' + id).toggleClass('bg-primary', isChecked).toggleClass('bg-secondary', !isChecked);
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