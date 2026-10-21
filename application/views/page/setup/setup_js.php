<script type="text/javascript">
    var exportusers;
    var store_code_list;
    $(document).ready(function() {
        $('input.datePicker').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false
        });

        $('input.timePicker').datetimepicker({
            format: 'hh:mm A',
            useCurrent: false,
            allowInputToggle: true
        });

        $('input.timePicker2').datetimepicker({
            format: 'hh:mm',
            useCurrent: false,
            allowInputToggle: true,
            stepping: 30,
            // defaultDate: moment('00:00', 'HH:mm')
        });

        $('.select2').select2({
            theme: 'bootstrap4'
        })

        store_code_list = $('#store_code_list').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('store_code_list'); ?>",
                type: "POST",
            },
            "order": [
                [0, "asc"]
            ],
            "columnDefs": [{
                "targets": [1, 2, 3, 4, 5, 6, 7, 8],
                "orderable": false,

            }, {
                "targets": [3, 4, 5, 6, 7, 8],
                "className": "text-center"
            }]
        });

        var statCut = $('a.btn.btn-primary.statCut.active').attr('id');
        appdis_list(statCut)

        $('a.statCut').click(function() {
            $('a.statCut').removeClass('btn-primary active').addClass('btn-default');
            $(this).addClass('btn-primary active');

            var statCut = this.id;

            appdis_list(statCut)
        });

        $('.status_switch').on('switchChange.bootstrapSwitch', function(event, state) {
            var statCut = $('a.btn.btn-primary.statCut.active').attr('id');
            console.log('Switch changed:' + statCut, state);

            Swal.fire({
                title: 'Confirm!',
                text: 'Update state of all supervisor.',
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
                        text: 'Updating state.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    setTimeout(() => {
                        $.ajax({
                            url: "<?= site_url('appdis_update'); ?>",
                            type: "POST",
                            data: {
                                id: 'all',
                                state,
                                statCut
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
                                            appdis_list(statCut)
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
                    $(this).bootstrapSwitch('toggleState', true, true);
                }
            })

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

        perday_previous_allow = $('#perday_previous_allow').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('perday_previous_allow'); ?>",
                type: "POST",
            },
            "order": [
                [2, "desc"]
            ],
            "columnDefs": [{
                "targets": [0, 1, 3, 4, 5],
                "orderable": false,

            }, {
                "targets": [4, 5],
                "className": "text-center"
            }]
        });

        $.validator.setDefaults({
            submitHandler: function(form) {
                var formId = form.id;
                console.log(formId)
                if (formId == 'add_exportuser') {
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
                } else if (formId == 'addon_form') {
                    var formData = $(form).serialize();

                    Swal.fire({
                        title: 'Confirm!',
                        text: 'Enable previous cutoff processing.',
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
                                text: 'Enabling previous cutoff.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            setTimeout(() => {
                                $.ajax({
                                    url: "<?= site_url('enable_previous'); ?>",
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
                } else if (formId == 'manualLate_form') {
                    var formData = $(form).serialize();

                    Swal.fire({
                        title: 'Confirm!',
                        text: 'Manual late form submission.',
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
                                text: 'Submitting manual late form.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            setTimeout(() => {
                                $.ajax({
                                    url: "<?= site_url('manualLate'); ?>",
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
                } else if (formId == 'manualUndertime_form') {
                    var formData = $(form).serialize();

                    Swal.fire({
                        title: 'Confirm!',
                        text: 'Manual undertime form submission.',
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
                                text: 'Submitting manual undertime form.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            setTimeout(() => {
                                $.ajax({
                                    url: "<?= site_url('manualUndertime'); ?>",
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
                } else if (formId == 'storeCode_form') {
                    var formData = $(form).serialize();

                    Swal.fire({
                        title: 'Confirm!',
                        text: 'Add Promo Store Code.',
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
                                text: 'Adding store code.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            setTimeout(() => {
                                $.ajax({
                                    url: "<?= site_url('addStoreCode'); ?>",
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
                } else if (formId == 'update_storeCode_form') {
                    var formData = $(form).serialize();

                    Swal.fire({
                        title: 'Confirm!',
                        text: 'Update Promo Store Code.',
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
                                text: 'Updating store code.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            setTimeout(() => {
                                $.ajax({
                                    url: "<?= site_url('storeCodes'); ?>",
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
                                                    store_code_list.ajax.reload();
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
                } else {
                    if (typeof formId === 'string' && formId.includes('_')) {
                        let [f, statCut] = formId.split('_')

                        var sched = $('input[name="sched_' + statCut + '"]').val();
                        var formData = $(form).serialize();

                        Swal.fire({
                            title: 'Confirm!',
                            text: 'Setup cutoff for ' + sched + '.',
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
                                        url: "<?= site_url('setup_cutoff'); ?>",
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
            }
        });

        $.validator.addMethod("noSpace", function(value, element) {
            return value.trim().length > 0; // true if not only spaces
        }, "This field cannot be empty or contain only spaces.");

        $('#add_exportuser,#addon_form').validate({
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
                searchPromo: {
                    noSpace: true
                }
            }
        });

        $('#manualLate_form').validate({
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
                searchPromoLate: {
                    noSpace: true
                },
            }
        });

        $('#manualUndertime_form').validate({
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
                searchPromoUndertime: {
                    noSpace: true
                }
            }
        });

        $('#storeCode_form').validate({
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

        $('#update_storeCode_form').validate({
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

        $('form[id^="cutoff_"]').each(function() {
            $(this).validate({
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
                onkeyup: function(element) {
                    $(element).val($(element).val().trim());
                    $(element).valid();
                },
            });
        });

    });

    function appdis_list(statCut) {
        $('.status_switch').bootstrapSwitch({
            size: 'mini',
        });
        $('.status_switch').bootstrapSwitch('state', false, true).bootstrapSwitch('disabled', true);
        $('#appdis_table').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('appdis_list'); ?>",
                type: "POST",
                data: {
                    statCut
                },
                dataSrc: function(json) {
                    if (json.data && json.data.length > 0) {
                        $('.status_switch').bootstrapSwitch('disabled', false).bootstrapSwitch('state', json.checked, true);
                    }
                    return json.data;
                }
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
            }],
            drawCallback: function() {
                $('input.per_status').bootstrapSwitch({
                    size: 'mini'
                })
            }
        });
    }

    let isReverting = false;

    function switch_status(id, statCut) {
        var $switch = $('input.per_status#' + id);
        var state = $switch.bootstrapSwitch('state');

        if (isReverting) return;

        $.ajax({
            url: "<?= site_url('appdis_update'); ?>",
            type: "POST",
            data: {
                id,
                state,
                statCut
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
                            $('.status_switch').bootstrapSwitch('state', response.checked, true)
                        });
                    } else {
                        errorAlert(response.message);
                        isReverting = true;
                        $switch.bootstrapSwitch('toggleState', true, true);
                        setTimeout(() => isReverting = false, 500);
                    }
                } catch (e) {
                    console.error("Error:", data);
                    errorAlert(data);
                    isReverting = true;
                    $switch.bootstrapSwitch('toggleState', true, true);
                    setTimeout(() => isReverting = false, 500);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX Error:", textStatus, errorThrown);
                errorAlert(errorThrown);
                isReverting = true;
                $switch.bootstrapSwitch('toggleState', true, true);
                setTimeout(() => isReverting = false, 500);
            }
        });
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

    let ajaxCallCount = 0;
    let ajaxRequest;

    function searchPromoaddon(string) {
        $('input[name="dateFrom"]').val('');
        $('input[name="dateTo"]').val('');
        if (string.trim() != '') {

            if (ajaxRequest && ajaxRequest.readyState !== 4) {
                ajaxRequest.abort();
            }

            ajaxCallCount++;

            ajaxRequest = $.ajax({
                type: "POST",
                url: "<?= site_url('setup_searchPromo'); ?>",
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
        $(".dropdown-list").hide().html('');
        $('input[name="searchPromo"]').val('[' + empId + '] ' + name);
        $('input[name="empId"]').val(empId);

        getCutoffdate(statCut, bioM)
    }

    function getCutoffdate(statCut, bioM) {
        $.ajax({
            url: "<?= site_url('getCutoffdate'); ?>",
            type: "POST",
            data: {
                statCut,
                bioM
            },
            success: function(data) {
                let response = JSON.parse(data);
                $('select[name="dateFrom"]').html(response.start)
                $('select[name="dateTo"]').html(response.end)
            }
        });
    }

    function update_previous(id, process) {
        if (process == 'activate') {
            var msg1 = 'Activate previous cutoff request.';
            var msg2 = 'Activating previous cutoff request.';
        } else if (process == 'deactivate') {
            var msg1 = 'Deactivate previous cutoff request.';
            var msg2 = 'Deactivating previous cutoff request.';
        } else {
            var msg1 = 'Delete previous cutoff request.';
            var msg2 = 'Deleting previous cutoff request.';
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
                        url: "<?= site_url('update_previous'); ?>",
                        type: "POST",
                        data: {
                            id,
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
                                        perday_previous_allow.ajax.reload();
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

    function searchManualLate(string) {
        $('input[name="lateEmpId"]').val('');
        if (string.trim() != '') {

            if (ajaxRequest && ajaxRequest.readyState !== 4) {
                ajaxRequest.abort();
            }

            ajaxCallCount++;

            ajaxRequest = $.ajax({
                type: "POST",
                url: "<?= site_url('searchManualLate'); ?>",
                data: {
                    string,
                },
                beforeSend: function() {
                    $('.late').html('<ul class="list-group"><span class="list-group-item">' +
                        '<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>' +
                        '</span></ul>');
                },
                success: function(data) {

                    $(".late").show()
                    $(".late").html(data);
                }
            });
        } else {
            $(".late").hide();
        }
    }

    function selectLate(name, empId) {
        $(".late").hide().html('');
        $('input[name="searchPromoLate"]').val('[' + empId + '] ' + name);
        $('input[name="lateEmpId"]').val(empId);
    }

    function searchManualUndertime(string) {
        $('input[name="undertimeEmpId"]').val('');
        if (string.trim() != '') {

            if (ajaxRequest && ajaxRequest.readyState !== 4) {
                ajaxRequest.abort();
            }

            ajaxCallCount++;

            ajaxRequest = $.ajax({
                type: "POST",
                url: "<?= site_url('searchManualUndertime'); ?>",
                data: {
                    string,
                },
                beforeSend: function() {
                    $('.undertime').html('<ul class="list-group"><span class="list-group-item">' +
                        '<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>' +
                        '</span></ul>');
                },
                success: function(data) {

                    $(".undertime").show()
                    $(".undertime").html(data);
                }
            });
        } else {
            $(".undertime").hide();
        }
    }

    function selectUndertime(name, empId) {
        $(".undertime").hide().html('');
        $('input[name="searchPromoUndertime"]').val('[' + empId + '] ' + name);
        $('input[name="undertimeEmpId"]').val(empId);
    }

    function manualType(type) {
        if (type == 'late 1') {
            $('input[name="late1"]').prop('disabled', false);
            $('input[name="late2"]').prop('disabled', true).val('').removeClass('is-invalid');
        }
        if (type == 'late 2') {
            $('input[name="late1"]').prop('disabled', true).val('').removeClass('is-invalid');
            $('input[name="late2"]').prop('disabled', false);
        }
        if (type == 'undertime 1') {
            $('input[name="undertime1"]').prop('disabled', false);
            $('input[name="undertime2"]').prop('disabled', true).val('').removeClass('is-invalid');
        }
        if (type == 'undertime 2') {
            $('input[name="undertime1"]').prop('disabled', true).val('').removeClass('is-invalid');
            $('input[name="undertime2"]').prop('disabled', false);
        }
    }

    function updateStoreCode(id) {
        $('#update_storeCode').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#update_storeCode').modal('show');

        $.ajax({
            url: "<?= site_url('updateStoreCode'); ?>",
            type: "POST",
            data: {
                id
            },
            success: function(data) {
                $('div.update_storeCode').html(data);
                // 🔥 remove old validator instance
                // if ($('#update_storeCode_form').data('validator')) {
                //     $('#update_storeCode_form').validate().destroy();
                // }
                // $('#update_storeCode_form').validate({
                //     ignore: [],
                //     errorElement: 'span',
                //     errorPlacement: function(error, element) {
                //         error.addClass('invalid-feedback');
                //         element.closest('.form-group').append(error);
                //     },
                //     highlight: function(element) {
                //         $(element).addClass('is-invalid');
                //     },
                //     unhighlight: function(element) {
                //         $(element).removeClass('is-invalid');
                //     },
                // });
            }
        });
    }

    function storeCodes(id, process) {
        if (process == 'delete') {
            var msg1 = 'Delete Promo Store Code.';
            var msg2 = 'Deleting Promo Store Code.';
        } else if (process == 'edit') {
            var msg1 = 'Update Promo Store Code.';
            var msg2 = 'Updating Promo Store Code.';
        } else if (process == 'activate') {
            var msg1 = 'Activate Promo Store Code.';
            var msg2 = 'Activating Promo Store Code.';
        } else if (process == 'deactivate') {
            var msg1 = 'Deactivate Promo Store Code.';
            var msg2 = 'Deactivating Promo Store Code.';
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
                        url: "<?= site_url('storeCodes'); ?>",
                        type: "POST",
                        data: {
                            id,
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
                                        store_code_list.ajax.reload();
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

    function getField(select, formId) {
        var option = select.options[select.selectedIndex];

        var field = option.dataset.field; // bunit_field
        var bc = option.dataset.bc; // bc
        var cc = option.dataset.cc; // cc
        var bu = option.dataset.bu; // bu

        $('#' + formId).find('#bunit_field').val(field);
        $('#' + formId).find('#bc').val(bc);
        $('#' + formId).find('#cc').val(cc);
        $('#' + formId).find('#storeCode').val(bu);
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