<script type="text/javascript">
    $(document).ready(function() {
        $('#dateOt,#date').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false
        });

        $('#manualot_emp').on('shown.bs.modal', function() {
            var table2 = $('#manualot_emp_table').DataTable();
            table2.columns.adjust().draw();
        });

        $('input.timePicker').attr('readonly', true).on('keydown paste', function(e) {
            e.preventDefault();
        }).datetimepicker({
            // format: 'hh:mm A',
            format: 'YYYY-MM-DD hh:mm A',
            useCurrent: false,
            allowInputToggle: true,
            viewMode: 'times',
            stepping: 30,
            icons: {
                time: 'far fa-clock'
            }
        });

        // $('.select2').select2({
        //     theme: 'bootstrap4',
        //     allowClear: true
        // })

        $.validator.setDefaults({
            submitHandler: function(form) {
                // alert("Form successfully submitted! Form ID: " + form.id);
                var formId = form.id;

                if (formId == 'add_cutoff') {
                    var formData = $(form).serialize();

                    Swal.fire({
                        title: 'Confirm!',
                        text: 'Add Promo cutoff.',
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
                                text: 'Adding promo cutoff.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            setTimeout(() => {
                                $.ajax({
                                    url: "<?= site_url('add_promoCutoff'); ?>",
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
                        }
                    })
                } else if (formId == 'add_manualot') {
                    var formData = $(form).serialize();
                    Swal.fire({
                        title: 'Confirm!',
                        text: 'Add overtime.',
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
                                text: 'Adding overtime.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            setTimeout(() => {
                                $.ajax({
                                    url: "<?= site_url('add_manualot'); ?>",
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
                        }
                    })
                } else if (formId == 'add_holiday') {
                    var formData = $(form).serialize();
                    Swal.fire({
                        title: 'Confirm!',
                        text: 'Add holiday.',
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
                                text: 'Adding holiday.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            setTimeout(() => {
                                $.ajax({
                                    url: "<?= site_url('add_holiday'); ?>",
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
                        }
                    })
                } else if (formId == 'addMasterfileForm') {
                    var formData = $(form).serialize();
                    Swal.fire({
                        title: 'Confirm!',
                        text: 'Assign masterfile data.',
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
                                text: 'Assigning masterfile data.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            setTimeout(() => {
                                $.ajax({
                                    url: "<?= site_url('addMasterfile'); ?>",
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
                        }
                    })
                } else if (formId == 'defaultSchedForm') {
                    var formData = $(form).serialize();
                    Swal.fire({
                        title: 'Confirm!',
                        text: 'Set default weekly schedule.',
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
                                text: 'Setting weekly schedule.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            setTimeout(() => {
                                $.ajax({
                                    url: "<?= site_url('defaultSched'); ?>",
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
                        }
                    })
                } else if (formId == 'add_otdwa') {
                    var formData = $(form).serialize();

                    if (!$('input[name="d1"], input[name="d2"], input[name="d3"]').is(':checked')) {
                        alertRequired('Select Overtime for DWA.');
                        return;
                    }

                    Swal.fire({
                        title: 'Confirm!',
                        text: 'Add Overtime of DWA.',
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
                                text: 'Adding overtime for DWA.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            setTimeout(() => {
                                $.ajax({
                                    url: "<?= site_url('add_otdwa'); ?>",
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
                        }
                    })
                } else if (formId == 'add_otentry') {
                    var formData = $(form).serialize();

                    if (!$('input[name="d1"], input[name="d2"], input[name="d3"]').is(':checked')) {
                        alertRequired('Select Overtime for Entry.');
                        return;
                    }

                    Swal.fire({
                        title: 'Confirm!',
                        text: 'Add Overtime of Entry.',
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
                                text: 'Adding overtime for Entry.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            setTimeout(() => {
                                $.ajax({
                                    url: "<?= site_url('add_otentry'); ?>",
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
                        }
                    })
                }
            }
        });

        $.validator.addMethod("noSpace", function(value, element) {
            return value.trim().length > 0; // true if not only spaces
        }, "This field cannot be empty or contain only spaces.");

        $('#add_manualot,#add_cutoff,#add_holiday,#addMasterfileForm,#add_otdwa,#add_otentry').validate({
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
                $(element).next().find(".select2-selection").removeClass('is-invalid');
            },
            rules: {
                searchPromo: {
                    noSpace: true
                },
                holiday: {
                    noSpace: true
                }
            }
        });

        $('#defaultSchedForm').validate({
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
                $(element).next('.select2-container').find(".select2-selection").removeClass('is-invalid');
            },
        });
    });

    function firstDate(startFC) {

        var subC = Number(startFC) - 1;
        if (subC < 10) {
            subC = '0' + subC;
        }

        $('input[name="endSC"]').val(subC).valid();
        if (startFC == 1) {
            $('input[name="endSC"]').val('31').valid();

        }
    }

    function secondDate(endFC) {
        var startFC = $('select[name="startFC"]').val();
        console.log(endFC, startFC)
        if (startFC == '') {

            $('select[name="startFC"]').focus();
            $('select[name="endFC"]').val('');
            alertRequired('First Cutoff (Start) is required!')
            return;
        }

        if (startFC == endFC) {
            $('input[name="startSC"]').val('');
            $('select[name="endFC"]').val('');
            alertRequired('First Cutoff (Start and End) should not be the same!')
            return;
        }

        var addC = Number(endFC) + 1;
        if (addC < 10) {
            addC = '0' + addC;
        }

        $('input[name="startSC"]').val(addC).valid();
        if (endFC == 31) {
            $('input[name="startSC"]').val('1').valid();
        }
    }

    let ajaxCallCount = 0;
    let ajaxRequest;

    function searchPromos(string) {
        $('select[name="store"]').val('');
        $('input[name="overtime"]').val('');
        $('input[name="dateOT"]').val('');
        $(".dropdown-list").show()
        if (string.trim() != '') {

            if (ajaxRequest && ajaxRequest.readyState !== 4) {
                ajaxRequest.abort();
            }

            ajaxCallCount++;

            ajaxRequest = $.ajax({
                type: "POST",
                url: "<?= site_url('add_searchPromo'); ?>",
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
        $('input[name="empId"]').val(empId);
        get_stores(empId, recordNo);
    }

    function get_stores(empId, recordNo) {

        $.ajax({
            type: "POST",
            url: "<?= site_url('add_getStores'); ?>",
            data: {
                empId,
                recordNo
            },
            success: function(data) {
                $('select[name="store"]').html(data)
            }
        });

    }

    function view_manualot() {
        $('#manualot_list').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#manualot_list').modal('show');


        $('#manualot_table').dataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('manualot_list'); ?>",
                type: "POST",
            },
            "order": [
                [1]
            ],
            "columnDefs": [{
                    "targets": [0, 2, 3],
                    "orderable": false,

                }, {
                    "targets": [3],
                    "className": "text-center"
                },
                {
                    "targets": [2],
                    "className": "text-left"
                }
            ]
        });

    }

    function view_ot(empId, sCode) {
        $('#manualot_emp').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#manualot_emp').modal('show');

        $('#manualot_emp_table').dataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('manualot_emp'); ?>",
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
                "targets": [2, 3],
                "className": "text-center"
            }]
        });
    }

    function delete_ot(empId, id, eoaId) {
        Swal.fire({
            title: 'Confirm!',
            text: 'Delete manual overtime.',
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
                    text: 'Deleting overtime.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                setTimeout(() => {
                    $.ajax({
                        url: "<?= site_url('delete_ot'); ?>",
                        type: "POST",
                        data: {
                            empId,
                            id,
                            eoaId
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
            }
        })
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

    function masterfileSearch(string) {
        $('input[name="empId"]').val('');
        $('div.addmasterfile').hide()
        $('div.addMasterfileForm,div.promoDetails,div.defaultSchedForm').html('');
        $('div.form1').hide()
        $('div.form2').hide()
        if (string.trim() != '') {

            if (ajaxRequest && ajaxRequest.readyState !== 4) {
                ajaxRequest.abort();
            }

            ajaxCallCount++;

            ajaxRequest = $.ajax({
                type: "POST",
                url: "<?= site_url('masterfileSearch'); ?>",
                data: {
                    string,
                },
                beforeSend: function() {
                    $(".dropdown-list").show()
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

    function selectMasterfile(name, empId, recordNo, type) {
        $(".dropdown-list").hide().html('');
        $('input[name="searchPromo"]').val('[' + empId + '] ' + name);
        $('input[name="empId"]').val(empId);
        $('div.form1').show()

        $.ajax({
            type: "POST",
            url: "<?= site_url('promoDetails'); ?>",
            data: {
                empId,
                recordNo
            },
            beforeSend: function() {
                loader_circle('promoDetails');
            },
            success: function(data) {
                $('div.promoDetails').html(data)
            }
        });

        $.ajax({
            type: "POST",
            url: "<?= site_url('addMasterfileForm'); ?>",
            data: {
                empId,
                recordNo
            },
            beforeSend: function() {
                loader_circle('addMasterfileForm');
            },
            success: function(data) {
                $('div.addMasterfileForm').html(data)
                $('div.addmasterfile').show()
                $('.select2').select2({
                    theme: 'bootstrap4',
                    allowClear: true
                })

                $('.select2').on('select2:open', () => {
                    document.querySelector('.select2-search__field').focus();
                });

            }
        });

        if (type == 'ROVING') {
            $('div.form2').show()
            $.ajax({
                type: "POST",
                url: "<?= site_url('defaultSchedForm'); ?>",
                data: {
                    empId,
                    recordNo
                },
                beforeSend: function() {
                    loader_circle('defaultSchedForm');
                },
                success: function(data) {
                    $('div.defaultSchedForm').html(data)
                    $('div.defaultsched').show()
                    $('.select2').select2({
                        theme: 'bootstrap4',
                        width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                        allowClear: true
                    })
                    $('.select2').on('select2:open', () => {
                        document.querySelector('.select2-search__field').focus();
                    });
                }
            });
        }
    }

    function showStore(no, day) {
        console.log(no, day)
        if (no == 1) {
            $('select[name="store1_' + day + '"]').prop('disabled', false).show()
            $('select[name="store2_' + day + '"]').prop('disabled', true).hide()
            $('select[name="shiftCode_' + day + '"]').attr('required', true)
        } else if (no == 2) {
            $('select[name="store1_' + day + '"]').prop('disabled', false).show()
            $('select[name="store2_' + day + '"]').prop('disabled', false).show()
            $('select[name="shiftCode_' + day + '"]').attr('required', true)
        } else {
            $('select[name="store1_' + day + '"]').prop('disabled', true).hide()
            $('select[name="store2_' + day + '"]').prop('disabled', true).hide()
            $('select[name="shiftCode_' + day + '"]').attr('required', false)
        }
    }

    function setDayoff(day) {
        if ($('input[name="dayoff_' + day + '"]').is(':checked')) {
            $('input[name="day_' + day + '"]').prop('disabled', true)
            $('select[name="shiftCode_' + day + '"]').prop('disabled', true).removeClass('is-invalid');
            $('select[name="storeNo_' + day + '"]').prop('disabled', true).removeClass('is-invalid');
            $('select[name="store1_' + day + '"]').prop('disabled', true).removeClass('is-invalid');
            $('select[name="store2_' + day + '"]').prop('disabled', true).removeClass('is-invalid');
        } else {
            $('input[name="day_' + day + '"]').prop('disabled', false)
            $('select[name="shiftCode_' + day + '"]').prop('disabled', false)
            $('select[name="storeNo_' + day + '"]').prop('disabled', false)
            $('select[name="store1_' + day + '"]').prop('disabled', false)
            $('select[name="store2_' + day + '"]').prop('disabled', false)
        }
    }

    var isClearing = false;

    function searchotdwa(string) {
        isClearing = true;
        $('input[name="date"]').val('').datetimepicker('clear');
        isClearing = false;
        $('select[name="dwa"]').html('<option value="">---</option>').val('');
        $('input[name="empId"],input[name="bioM"]').val('');
        $('input[name="purpose"]').val('');
        $('span.shift,span.in1e,span.out1e,span.in2e,span.out2e,span.in3e,span.out3e').html('');
        $('select[name="type"]').val('');
        $('input#in1,input#out1,input#in2,input#out2,input#in3,input#out3').prop('disabled', true).val('');
        $('input[name="d1"],input[name="d2"],input[name="d3"]').prop('checked', false);
        $(".dropdown-list").show()
        if (string.trim() != '') {

            if (ajaxRequest && ajaxRequest.readyState !== 4) {
                ajaxRequest.abort();
            }

            ajaxCallCount++;

            ajaxRequest = $.ajax({
                type: "POST",
                url: "<?= site_url('add_searchotdwa'); ?>",
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

    function selectotdwa(name, empId, bioM) {
        $(".dropdown-list").hide().html('');
        $('input[name="searchPromo"]').val('[' + empId + '] ' + name);
        $('input[name="empId"]').val(empId);
        $('input[name="bioM"]').val(bioM);
    }

    function getShiftdwa(date) {
        if (isClearing) return;
        console.log(date)
        var empId = $('input[name="empId"]').val();
        var bioM = $('input[name="bioM"]').val();
        $('span.shift').html('');
        $('select[name="dwa"]').html('<option value="">---</option>').val('');
        $('input#in1,input#out1,input#in2,input#out2,input#in3,input#out3').prop('disabled', true).val('');
        $('input[name="d1"],input[name="d2"],input[name="d3"]').prop('checked', false);
        if (empId) {
            Swal.fire({
                icon: 'info',
                title: 'Please wait...',
                text: 'Checking Shiftcode and DWA request.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            setTimeout(() => {
                $.ajax({
                    type: "POST",
                    url: "<?= site_url('getShiftdwa'); ?>",
                    data: {
                        empId,
                        date,
                        bioM
                    },
                    beforeSend: function() {

                    },
                    success: function(data) {
                        try {
                            let sc = JSON.parse(data);
                            if (sc.message === "success") {
                                // let sc = JSON.parse(data);
                                // console.log(sc)
                                Swal.close();
                                var opt = sc.opt;
                                $('select[name="dwa"]').html(opt);
                                var in1 = sc.in1;
                                var out1 = sc.out1;
                                var in2 = sc.in2;
                                var out2 = sc.out2;

                                var out2y = sc.out2y;
                                var in1n = sc.in1n;
                                var sched = '[' + in1 + ' - ' + out1 + ']'
                                if (in2 && out2) {
                                    var sched = '[' + in1 + ' - ' + out1 + ' | ' + in2 + ' - ' + out2 + ']'
                                }

                                $('span.shift').html(sched);
                                $('input#in1,input#out1,input#in2,input#out2,input#in3,input#out3').off('change.datetimepicker');

                                // setup in1
                                $('input#in1').on('change.datetimepicker', function(e) {
                                    if (!e.date) return;
                                    $('input#out1').val('')
                                    var selected = e.date;
                                    var min = moment(date + ' ' + out2y, 'YYYY-MM-DD hh:mm A').subtract(1, 'day');
                                    if (selected.isBefore(min)) {
                                        $(this).val(min.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                    var max = moment(date + ' ' + in1, 'YYYY-MM-DD hh:mm A');
                                    if (selected.isAfter(max)) {
                                        $(this).val(max.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                });

                                // setup out1
                                $('input#out1').on('change.datetimepicker', function(e) {
                                    if (!e.date) return;
                                    var ti1 = $('input#in1').val();
                                    if (!ti1) return;
                                    var selected = e.date;
                                    var min = moment(ti1, 'YYYY-MM-DD hh:mm A');
                                    if (selected.isBefore(min)) {
                                        $(this).val(min.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                    var max = moment(date + ' ' + in1, 'YYYY-MM-DD hh:mm A');
                                    if (selected.isAfter(max)) {
                                        $(this).val(max.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                });

                                // setup in2
                                $('input#in2').on('change.datetimepicker', function(e) {
                                    if (!e.date) return;
                                    $('input#out2').val('')
                                    var selected = e.date;
                                    var min = moment(date + ' ' + out2, 'YYYY-MM-DD hh:mm A');
                                    if (selected.isBefore(min)) {
                                        $(this).val(min.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                    var max = moment(date + ' ' + out2, 'YYYY-MM-DD hh:mm A');
                                    if (selected.isAfter(max)) {
                                        $(this).val(max.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                });

                                // setup out2
                                $('input#out2').on('change.datetimepicker', function(e) {
                                    if (!e.date) return;
                                    var selected = e.date;
                                    var min = moment(date + ' ' + out2, 'YYYY-MM-DD hh:mm A');
                                    if (selected.isBefore(min)) {
                                        $(this).val(min.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                    var max = moment(date + ' ' + in1n, 'YYYY-MM-DD hh:mm A').add(1, 'day');
                                    if (selected.isAfter(max)) {
                                        $(this).val(max.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                });

                                // setup in3
                                $('input#in3').on('change.datetimepicker', function(e) {
                                    if (!e.date) return;
                                    $('input#out3').val('')

                                    var min = moment(date + ' ' + out2, 'YYYY-MM-DD hh:mm A');
                                    var to2 = $('input#out2').val();
                                    if (to2) {
                                        var min = moment(to2, 'YYYY-MM-DD hh:mm A');
                                    }
                                    var selected = e.date;
                                    // var min = moment(date + ' ' + out2, 'YYYY-MM-DD hh:mm A');
                                    if (selected.isBefore(min)) {
                                        $(this).val(min.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                    var max = moment(date + ' ' + in1n, 'YYYY-MM-DD hh:mm A').add(1, 'day');
                                    if (selected.isAfter(max)) {
                                        $(this).val(max.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                });

                                // setup out3
                                $('input#out3').on('change.datetimepicker', function(e) {
                                    if (!e.date) return;
                                    var ti3 = $('input#in3').val();
                                    if (!ti3) return;
                                    var selected = e.date;
                                    var min = moment(ti3, 'YYYY-MM-DD hh:mm A');
                                    if (selected.isBefore(min)) {
                                        $(this).val(min.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                    var max = moment(date + ' ' + in1n, 'YYYY-MM-DD hh:mm A').add(1, 'day');
                                    if (selected.isAfter(max)) {
                                        $(this).val(max.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                });
                            } else {
                                errorAlert(sc.message)
                                // $('input[name="date"]').val('');
                                isClearing = true;
                                $('input[name="date"]').val('').datetimepicker('clear');
                                isClearing = false;
                                $('select[name="dwa"]').val('');
                            }
                        } catch (e) {
                            console.error("Error:", data);
                            errorAlert(data)
                        }
                    }
                });
            }, 2000);
        } else {
            $('input[name="date"]').val('');
            alertRequired('Employee is required.')
        }
    }

    function getShiftentry(date) {
        if (isClearing) return;
        console.log(date)
        var empId = $('input[name="empId"]').val();
        var bioM = $('input[name="bioM"]').val();
        $('span.shift,span.in1e,span.out1e,span.in2e,span.out2e,span.in3e,span.out3e').html('');
        $('input[name="purpose"]').val('');
        $('select[name="type"]').val('');
        $('input#in1,input#out1,input#in2,input#out2,input#in3,input#out3').prop('disabled', true).val('');
        $('input[name="d1"],input[name="d2"],input[name="d3"]').prop('checked', false);
        if (empId) {
            Swal.fire({
                icon: 'info',
                title: 'Please wait...',
                text: 'Checking Shiftcode.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            setTimeout(() => {
                $.ajax({
                    type: "POST",
                    url: "<?= site_url('getShiftentry'); ?>",
                    data: {
                        empId,
                        date,
                        bioM
                    },
                    beforeSend: function() {},
                    success: function(data) {
                        try {
                            let sc = JSON.parse(data);
                            if (sc.message === "success") {
                                Swal.close();
                                var in1 = sc.in1;
                                var out1 = sc.out1;
                                var in2 = sc.in2;
                                var out2 = sc.out2;

                                var in1e = sc.in1e;
                                if (in1e) {
                                    $('span.in1e').html(in1e);
                                }
                                var out1e = sc.out1e;
                                if (out1e) {
                                    $('span.out1e').html(out1e);
                                }
                                var in2e = sc.in2e;
                                if (in2e) {
                                    $('span.in2e').html(in2e);
                                }
                                var out2e = sc.out2e;
                                if (out2e) {
                                    $('span.out2e').html(out2e);
                                }
                                var in3e = sc.in3e;
                                if (in3e) {
                                    $('span.in3e').html(in3e);
                                }
                                var out3e = sc.out3e;
                                if (out3e) {
                                    $('span.out3e').html(out3e);
                                }

                                var out2y = sc.out2y;
                                var in1n = sc.in1n;
                                var sched = '[' + in1 + ' - ' + out1 + ']'
                                if (in2 && out2) {
                                    var sched = '[' + in1 + ' - ' + out1 + ' | ' + in2 + ' - ' + out2 + ']'
                                }

                                $('span.shift').html(sched);
                                $('input#in1,input#out1,input#in2,input#out2,input#in3,input#out3').off('change.datetimepicker');

                                // setup in1
                                $('input#in1').on('change.datetimepicker', function(e) {
                                    if (!e.date) return;
                                    $('input#out1').val('')
                                    var selected = e.date;
                                    var min = moment(date + ' ' + out2y, 'YYYY-MM-DD hh:mm A').subtract(1, 'day');
                                    if (selected.isBefore(min)) {
                                        $(this).val(min.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                    var max = moment(date + ' ' + in1, 'YYYY-MM-DD hh:mm A');
                                    if (selected.isAfter(max)) {
                                        $(this).val(max.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                });

                                // setup out1
                                $('input#out1').on('change.datetimepicker', function(e) {
                                    if (!e.date) return;
                                    var ti1 = $('input#in1').val();
                                    if (!ti1) return;
                                    var selected = e.date;
                                    var min = moment(ti1, 'YYYY-MM-DD hh:mm A');
                                    if (selected.isBefore(min)) {
                                        $(this).val(min.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                    var max = moment(date + ' ' + in1, 'YYYY-MM-DD hh:mm A');
                                    if (selected.isAfter(max)) {
                                        $(this).val(max.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                });

                                // setup in2
                                $('input#in2').on('change.datetimepicker', function(e) {
                                    if (!e.date) return;
                                    $('input#out2').val('')
                                    var selected = e.date;
                                    var min = moment(date + ' ' + out2, 'YYYY-MM-DD hh:mm A');
                                    if (selected.isBefore(min)) {
                                        $(this).val(min.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                    var max = moment(date + ' ' + out2, 'YYYY-MM-DD hh:mm A');
                                    if (selected.isAfter(max)) {
                                        $(this).val(max.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                });

                                // setup out2
                                $('input#out2').on('change.datetimepicker', function(e) {
                                    if (!e.date) return;
                                    var selected = e.date;
                                    var min = moment(date + ' ' + out2, 'YYYY-MM-DD hh:mm A');
                                    if (selected.isBefore(min)) {
                                        $(this).val(min.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                    var max = moment(date + ' ' + in1n, 'YYYY-MM-DD hh:mm A').add(1, 'day');
                                    if (in3e) {
                                        var max = moment(date + ' ' + in3e, 'YYYY-MM-DD hh:mm A');
                                        if (max.format('A') === 'AM') {
                                            var max = moment(date + ' ' + in3e, 'YYYY-MM-DD hh:mm A').add(1, 'day');
                                        }
                                    }
                                    if (selected.isAfter(max)) {
                                        $(this).val(max.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                });

                                // setup in3
                                $('input#in3').on('change.datetimepicker', function(e) {
                                    if (!e.date) return;
                                    $('input#out3').val('')

                                    var min = moment(date + ' ' + out2, 'YYYY-MM-DD hh:mm A');
                                    var to2 = $('input#out2').val();
                                    if (out2e) {
                                        var min = moment(date + ' ' + out2e, 'YYYY-MM-DD hh:mm A');
                                        if (min.format('A') === 'AM') {
                                            var min = moment(date + ' ' + out2e, 'YYYY-MM-DD hh:mm A').add(1, 'day');
                                        }
                                    }
                                    if (to2) {
                                        var min = moment(to2, 'YYYY-MM-DD hh:mm A');
                                    }
                                    var selected = e.date;
                                    // var min = moment(date + ' ' + out2, 'YYYY-MM-DD hh:mm A');
                                    if (selected.isBefore(min)) {
                                        $(this).val(min.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                    var max = moment(date + ' ' + in1n, 'YYYY-MM-DD hh:mm A').add(1, 'day');
                                    if (selected.isAfter(max)) {
                                        $(this).val(max.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                });

                                // setup out3
                                $('input#out3').on('change.datetimepicker', function(e) {
                                    if (!e.date) return;
                                    var ti3 = $('input#in3').val();
                                    if (!ti3) return;
                                    var selected = e.date;
                                    var min = moment(ti3, 'YYYY-MM-DD hh:mm A');
                                    if (selected.isBefore(min)) {
                                        $(this).val(min.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                    var max = moment(date + ' ' + in1n, 'YYYY-MM-DD hh:mm A').add(1, 'day');
                                    if (selected.isAfter(max)) {
                                        $(this).val(max.format('YYYY-MM-DD hh:mm A'));
                                        return;
                                    }
                                });
                            } else {
                                errorAlert(sc.message)
                                // $('input[name="date"]').val('');
                                isClearing = true;
                                $('input[name="date"]').val('').datetimepicker('clear');
                                isClearing = false;
                                $('input[name="purpose"]').val('');
                            }
                        } catch (e) {
                            console.error("Error:", data);
                            errorAlert(data)
                        }
                    }
                });
            }, 2000);
        } else {
            $('input[name="date"]').val('');
            alertRequired('Employee is required.')
        }
    }

    function activateTime(id) {
        console.log(id)
        if ($('input[name="d' + id + '"]').is(':checked')) {
            $('input#in' + id).prop('disabled', false).val('');
            $('input#out' + id).prop('disabled', false).val('');

        } else {
            $('input#in' + id).prop('disabled', true).val('');
            $('input#out' + id).prop('disabled', true).val('');
        }
        if (id == '2') {
            $('input#in3').val('');
        }
    }

    function changeTime(name, time) {
        if (name == 'out2') {
            $('input#in3').val('')
            $('input#out3').val('')
        }
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