<script type="text/javascript">
    var holiday;
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            // allowClear: true,
        })

        $('#dateAssigned').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false
        });

        holiday = $('#holiday_list').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('holiday_list'); ?>",
                type: "POST",
            },
            "order": [
                [1, "desc"]
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
                if (formId == 'holiday_form') {
                    var formData = $(form).serialize();
                    Swal.fire({
                        title: 'Confirm!',
                        text: 'Update holiday.',
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
                                text: 'Updating holiday.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            setTimeout(() => {
                                $.ajax({
                                    url: "<?= site_url('holiday_update'); ?>",
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
                                                    holiday.ajax.reload();
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

        $.validator.addMethod("noSpace", function(value, element) {
            return value.trim().length > 0; // true if not only spaces
        }, "This field cannot be empty or contain only spaces.");

        $('#holiday_form').validate({
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
            rules: {
                holiday: {
                    noSpace: true
                }
            }
        });

    });

    let ajaxCallCount = 0;
    let ajaxRequest;

    function searchPromo(string) {
        $('div.masterfileDetails').html('');
        $('div.multiSchedules').hide()
        if (string.trim() != '') {

            if (ajaxRequest && ajaxRequest.readyState !== 4) {
                ajaxRequest.abort();
            }

            ajaxCallCount++;

            ajaxRequest = $.ajax({
                type: "POST",
                url: "<?= site_url('view_searchPromo'); ?>",
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

    function selectPromo(name, empId, recordNo, statCut, bioM) {
        $('div.multiSchedules').show()
        $(".dropdown-list").hide().html('');
        $('input[name="searchPromo"]').val('[' + empId + '] ' + name);
        masterfileDetails(empId, recordNo, statCut, bioM)
        multiSched(empId)
    }

    function masterfileDetails(empId, recordNo, statCut, bioM) {
        $.ajax({
            type: "POST",
            url: "<?= site_url('masterfileDetails'); ?>",
            data: {
                empId,
                recordNo,
                statCut,
                bioM
            },
            beforeSend: function() {
                loader_circle('masterfileDetails')
            },
            success: function(data) {
                $("div.masterfileDetails").html(data);
            }
        });
    }

    function multiSched(empId) {
        $('#multiSched').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('multiSched'); ?>",
                type: "POST",
                data: {
                    empId
                }
            },
            "order": [
                [0, "desc"]
            ],
            "columnDefs": [{
                "targets": [1, 2, 3, 4, 5, 6],
                "orderable": false,

            }, {
                "targets": [1, 3, 4, 6],
                "className": "text-center"
            }]
        });
    }

    function defSched(empId) {
        $('div#defSched').modal({
            backdrop: 'static',
            keyboard: false,
        });
        $("div#defSched").modal("show");

        $('#defSchedule').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('defSched'); ?>",
                type: "POST",
                data: {
                    empId
                }
            },
            "order": [
                // [0, "desc"]
            ],
            "columnDefs": [{
                "targets": [0, 1, 2, 3, 4],
                "orderable": false,

            }, {
                "targets": [1],
                "className": "text-center"
            }]
        });
    }

    function viewDO(empId, amsId, df, dt) {
        $('div#viewDO').modal({
            backdrop: 'static',
            keyboard: false,
        });
        $('div#viewDO').modal('show');

        $.ajax({
            type: "POST",
            url: "<?= site_url('viewDO'); ?>",
            data: {
                empId,
                amsId,
                df,
                dt
            },
            beforeSend: function() {
                loader_circle('viewDO')
            },
            success: function(data) {
                $('div.viewDO').html(data);
            }
        });
    }

    function schedule_pager(id) {
        $('div#viewSched').modal({
            backdrop: 'static',
            keyboard: false,
        });
        $('div#viewSched').modal('show');

        let [empId, recordNo, promoType, month, year] = id.split('|');
        $.ajax({
            type: "POST",
            url: "<?= site_url('schedule_pager'); ?>",
            data: {
                empId,
                recordNo,
                promoType,
                month,
                year
            },
            beforeSend: function() {
                loader_circle('viewSched')
            },
            success: function(data) {
                $('div.viewSched').html(data);
                view_sched(empId, recordNo, promoType, month, year);
            }
        });
    }

    function view_sched(empId, recordNo, promoType, month, year) {
        $.ajax({
            type: "POST",
            url: "<?= site_url('viewSched'); ?>",
            data: {
                empId,
                recordNo,
                promoType,
                month,
                year
            },
            beforeSend: function() {
                loader_circle('viewSchedPromo')
            },
            success: function(data) {
                $('div.viewSchedPromo').html(data);
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    }

    function month_n(datas) {

        var m_ = $("select[name = 'month_']").val();
        var y_ = $("select[name = 'year_']").val();
        view_sched2(m_, y_, datas);
    }

    function year_n(datas) {

        var m_ = $("select[name = 'month_']").val();
        var y_ = $("select[name = 'year_']").val();
        view_sched2(m_, y_, datas);
    }

    function view_sched2(month, year, data) {
        let [empId, recordNo, promoType] = data.split('|');
        $.ajax({
            type: "POST",
            url: "<?= site_url('schedule_pager'); ?>",
            data: {
                empId,
                recordNo,
                promoType,
                month,
                year
            },
            beforeSend: function() {
                loader_circle('viewSched')
            },
            success: function(data) {
                $('div.viewSched').html(data);
                view_sched(empId, recordNo, promoType, month, year);
            }
        });
    }

    function getList(date) {
        console.log(date)
        $.ajax({
            type: "POST",
            url: "<?= site_url('assign_list'); ?>",
            data: {
                date
            },
            beforeSend: function() {
                loader_circle('assign_list')
            },
            success: function(data) {
                $('div.assign_list').html(data);
            }
        });
    }

    function holiday_update(id, action) {
        if (action == 'update') {
            $('div#holiday').modal({
                backdrop: 'static',
                keyboard: false,
            });
            $('div#holiday').modal('show');

            $.ajax({
                type: "POST",
                url: "<?= site_url('holiday_form'); ?>",
                data: {
                    id,
                    action
                },
                beforeSend: function() {
                    loader_circle('holiday_form')
                },
                success: function(data) {
                    $('div.holiday_form').html(data);

                    $('#date,#moveDate').datetimepicker({
                        format: 'YYYY-MM-DD',
                        useCurrent: false
                    });
                }
            });
        } else {
            var msg1 = 'Delete holiday.';
            var msg2 = 'Deleting holiday.';
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
                            url: "<?= site_url('holiday_update'); ?>",
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
                                            holiday.ajax.reload();
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

    function getCategory(type) {
        if (type == "Legal") {
            $('select[name="category"]').html('');
            $('select[name="category"]').html('<option value="">---</option><option value="pay_100">+ 100% Pay (With or Without Duty)</option>');
        }
        if (type == "Special") {
            $('select[name="category"]').html('');
            $('select[name="category"]').html('<option value= "">---</option><option value="pay30_W_WO">+ 30% Pay(With or Without Duty)</option><option value="pay30_W">+ 30% Pay(With Duty)</option><option value="pay_no">No Pay</option>');
        }
    }

    function delpromoId(pId, baseId) {

        var store = '[' + baseId + ']';
        Swal.fire({
            title: 'Confirm!',
            text: 'Delete Biometric ID. ' + store,
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
                    text: 'Deleting Biometric ID.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                setTimeout(() => {
                    $.ajax({
                        url: "<?= site_url('delpromoId'); ?>",
                        type: "POST",
                        data: {
                            pId
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
                                        location.reload();
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

    function addpromoId(empId) {
        $('div#addpromoId').modal({
            backdrop: 'static',
            keyboard: false,
        });
        $('div#addpromoId').modal('show');

        $.ajax({
            type: "POST",
            url: "<?= site_url('addpromoId'); ?>",
            data: {
                empId
            },
            beforeSend: function() {
                loader_circle('addpromoId')
            },
            success: function(data) {
                $('div.addpromoId').html(data);
            }
        });
    }

    function submitpromoId(empId) {
        var store = $('select[name="store"]').val();
        var id = $('input[name="biometricId"]').val();
        if (store == '') {
            alertRequired('Store is required.')
            return;
        }
        if (id == '') {
            alertRequired('Biometric ID is required.')
            return;
        }
        Swal.fire({
            title: 'Confirm!',
            text: 'Setup Biometric ID.',
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
                    text: 'Setting up Biometric ID.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                setTimeout(() => {
                    $.ajax({
                        url: "<?= site_url('submitpromoId'); ?>",
                        type: "POST",
                        data: {
                            empId,
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
                                        location.reload();
                                    });
                                } else if (response.message === "exists") {
                                    errorAlert(response.feedback)

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