<script type="text/javascript">
    $(document).ready(function() {
        $('#startDate,#endDate').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false
        });

        var page = '<?= $file ?>';

        if (page == 'biometric') {
            promoIdList()
        }

        var process;
        if (page == 'logbox' || page == 'single') {
            process = $('a.btn.btn-primary.process.active').attr('id');
            processMenu(process)
        }

        $('a.process').click(function() {
            $('a.process').removeClass('btn-primary active').addClass('btn-default');
            $(this).addClass('btn-primary active');

            var process = this.id;
            processMenu(process)
        });

        if (page == 'change_shift') {
            var statCut = $('a.btn.btn-primary.statCut.active').attr('id');
            cs_first(statCut)

            $('a.statCut').click(function() {
                $('a.statCut').removeClass('btn-primary active').addClass('btn-default');
                $(this).addClass('btn-primary active');

                var statCut = this.id;
                cs_first(statCut)
            });
        }

        if (page == 'off_duty') {
            var statCut = $('a.btn.btn-primary.statCut.active').attr('id');
            od_second(statCut)

            $('a.statCut').click(function() {
                $('a.statCut').removeClass('btn-primary active').addClass('btn-default');
                $(this).addClass('btn-primary active');

                var statCut = this.id;
                od_second(statCut)
            });
        }

        if (page == 'process_per_cutoff') {
            var statCut = $('a.btn.btn-primary.statCut.active').attr('id');
            co_third(statCut)

            $('a.statCut').click(function() {
                $('a.statCut').removeClass('btn-primary active').addClass('btn-default');
                $(this).addClass('btn-primary active');

                var statCut = this.id;
                co_third(statCut)
            });
        }

        $.validator.setDefaults({
            submitHandler: function(form) {
                // alert("Form successfully submitted! Form ID: " + form.id);
                var formId = form.id;

                var process = $('a.btn.btn-primary.process.active').attr('id');
                if (process == 'single') {
                    formId = 'processSingle';
                }
                console.log(process, formId)

                if (formId == 'processLogbox') {

                    var formData = new FormData(form);
                    var empIds = [];
                    if ($.fn.DataTable.isDataTable('#processtable')) {
                        var table = $('#processtable').DataTable();
                        // if (table.length > 0) {
                        table.rows().nodes().to$().find('input[name="empId[]"]:checked').each(function() {
                            empIds.push($(this).val());
                            formData.append('empId[]', $(this).val());
                        });
                    } else {

                        $('input.processtable[name="empId[]"]:checked').each(function() {
                            empIds.push($(this).val());
                        });

                        // console.log('test')
                    }
                    // console.log(table.length)
                    console.log(empIds)
                    var process = $('a.btn.btn-primary.process.active').attr('id');
                    console.log(process);

                    if (empIds.length == 0) {
                        alertInfo('Please select an employee.');
                        return;
                    }

                    Swal.fire({
                        title: 'Confirm!',
                        text: 'Process DTR Logbox.',
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
                                text: 'Processing DTR.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            setTimeout(() => {
                                Swal.close();
                                $('div#dtr_process').modal({
                                    backdrop: 'static',
                                    keyboard: false,
                                });
                                $("div#dtr_process").modal("show");
                                $(".store_code").text('');
                                if (process != 'single') {
                                    $(".store_code").text('(' + process.toUpperCase() + ')');
                                }
                                // $("h5.date_process").text('Logbox');

                                $('.closeButton').hide();
                                $("#progress-bar").css("width", '0');
                                $(".percentage").text('0%');
                                $(".tableName").text('Loading...');

                                $.ajax({
                                    url: "<?= site_url('processLogbox'); ?>",
                                    type: "POST",
                                    data: formData,
                                    contentType: false,
                                    processData: false,
                                    success: function(data) {
                                        // try {
                                        //     let response = JSON.parse(data);
                                        //     if (response.message === "success") {
                                        //         Swal.fire({
                                        //             title: 'Success!',
                                        //             text: response.feedback,
                                        //             icon: 'success',
                                        //             iconColor: '#15ca20',
                                        //             confirmButtonText: 'Ok',
                                        //             confirmButtonColor: '#0d6efd',
                                        //             allowOutsideClick: false,
                                        //         }).then(() => {
                                        //             location.reload()
                                        //         });
                                        //     } else {
                                        //         errorAlert(response.message)
                                        //     }
                                        // } catch (e) {
                                        //     console.error("Error:", data);
                                        //     errorAlert(data)
                                        // }
                                    },
                                    beforeSend: function(jqXHR, settings) {

                                        var xhr = settings.xhr;
                                        settings.xhr = function() {
                                            var output = xhr();
                                            output.previous_text = '';

                                            output.onreadystatechange = function() {
                                                try {
                                                    if (output.readyState > 0) {

                                                        var result = JSON.parse(output.responseText.match(/{[^}]+}$/gi));
                                                        if (result.result == 'error') {
                                                            //test
                                                        } else if (result.result == 'initial') {
                                                            //test
                                                        } else if (result.result == 'loading') {
                                                            // console.log(process);


                                                            $("#progress-bar").css("width", result.msg);
                                                            $(".percentage").text(result.msg1);
                                                            $(".dateDuty").text(result.date);
                                                            if (result.exported == 'initialize') {
                                                                $('.spinnerBar').show();
                                                                $(".exported").removeClass('badge bg-secondary').text('');
                                                            } else if (result.exported == 'table_update') {
                                                                $('.spinnerBar').show();
                                                                $(".tableName").text(result.table);
                                                                $(".exported").removeClass('badge bg-success').addClass('badge bg-danger').text(result.exported);
                                                            } else if (result.exported == 'NO DATA') {
                                                                $('.spinnerBar').hide();
                                                                $(".exported").removeClass('badge bg-success').addClass('badge bg-danger').text(result.exported);
                                                            } else {
                                                                $('.spinnerBar').hide();
                                                                $(".exported").removeClass('badge bg-danger').addClass('badge bg-success').text(result.exported);
                                                            }
                                                            $('.cancelButton').show();
                                                        } else if (result.result == 'success') {
                                                            $('.spinnerBar').hide();
                                                            $("#progress-bar").css("width", result.bar);
                                                            $(".percentage").text(result.bar);
                                                            $(".tableName").text('');
                                                            $(".dateDuty").text(result.date);


                                                            setTimeout(function() {
                                                                $('.closeButton').show();
                                                            }, 2000);
                                                        }

                                                        output.previous_text = output.responseText;
                                                    }
                                                } catch (e) {
                                                    // console.log("[XHR STATECHANGE] Exception: " + e + ", Message: " + output.responseText);
                                                }
                                            };
                                            return output;
                                        };

                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        console.error("AJAX Error:", textStatus, errorThrown);
                                        errorAlert(errorThrown)
                                    }
                                });
                            }, 2000);
                        }
                    })
                } else if (formId == 'processBiometric') {

                    var formData = new FormData(form);

                    var empIds = [];
                    var table = $('#bioPromoList').DataTable();

                    table.rows().nodes().to$().find('input[name="empId[]"]:checked').each(function() {
                        empIds.push($(this).val());
                        formData.append('empId[]', $(this).val());
                    });

                    if (empIds.length == 0) {
                        alertInfo('Please select an employee.');
                        return;
                    }

                    console.log(empIds)

                    Swal.fire({
                        title: 'Confirm!',
                        text: 'Process DTR Biometric.',
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
                                text: 'Processing DTR.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            setTimeout(() => {
                                Swal.close();
                                $('div#dtr_process').modal({
                                    backdrop: 'static',
                                    keyboard: false,
                                });
                                $("div#dtr_process").modal("show");

                                $('.closeButton').hide();
                                $("#progress-bar").css("width", '0');
                                $(".percentage").text('0%');
                                $(".tableName").text('Loading...');

                                $.ajax({
                                    url: "<?= site_url('processBiometric'); ?>",
                                    type: "POST",
                                    data: formData,
                                    contentType: false,
                                    processData: false,
                                    success: function(data) {

                                    },
                                    beforeSend: function(jqXHR, settings) {

                                        var xhr = settings.xhr;
                                        settings.xhr = function() {
                                            var output = xhr();
                                            output.previous_text = '';

                                            output.onreadystatechange = function() {
                                                try {
                                                    if (output.readyState > 0) {

                                                        var result = JSON.parse(output.responseText.match(/{[^}]+}$/gi));
                                                        if (result.result == 'error') {
                                                            //test
                                                        } else if (result.result == 'initial') {
                                                            //test
                                                        } else if (result.result == 'loading') {
                                                            // console.log(result.tbl);
                                                            $("#progress-bar").css("width", result.msg);
                                                            $(".percentage").text(result.msg1);
                                                            $(".dateDuty").text(result.date);
                                                            if (result.exported == 'initialize') {
                                                                $('.spinnerBar').show();
                                                                $(".exported").removeClass('badge bg-secondary').text('');
                                                            } else if (result.exported == 'table_update') {
                                                                $('.spinnerBar').show();
                                                                $(".tableName").text(result.table);
                                                                $(".exported").removeClass('badge bg-success').addClass('badge bg-danger').text(result.exported);
                                                            } else if (result.exported == 'NO DATA') {
                                                                $('.spinnerBar').hide();
                                                                $(".exported").removeClass('badge bg-success').addClass('badge bg-danger').text(result.exported);
                                                            } else {
                                                                $('.spinnerBar').hide();
                                                                $(".exported").removeClass('badge bg-danger').addClass('badge bg-success').text(result.exported);
                                                            }
                                                            $('.cancelButton').show();
                                                        } else if (result.result == 'success') {
                                                            $('.spinnerBar').hide();
                                                            $("#progress-bar").css("width", result.bar);
                                                            $(".percentage").text(result.bar);
                                                            $(".tableName").text('');
                                                            $(".dateDuty").text(result.date);


                                                            setTimeout(function() {
                                                                $('.closeButton').show();
                                                            }, 2000);
                                                        }

                                                        output.previous_text = output.responseText;
                                                    }
                                                } catch (e) {
                                                    // console.log("[XHR STATECHANGE] Exception: " + e + ", Message: " + output.responseText);
                                                }
                                            };
                                            return output;
                                        };

                                    },
                                    error: function(jqXHR, textStatus, errorThrown) {
                                        console.error("AJAX Error:", textStatus, errorThrown);
                                        errorAlert(errorThrown)
                                    }
                                });
                            }, 2000);
                        }
                    })

                } else if (formId == 'processSingle') {
                    var formData = new FormData(form);

                    var empIds = [];
                    if ($.fn.DataTable.isDataTable('#processtable')) {
                        var table = $('#processtable').DataTable();
                        // if (table.length > 0) {
                        table.rows().nodes().to$().find('input[name="empId[]"]:checked').each(function() {
                            empIds.push($(this).val());
                            formData.append('empId[]', $(this).val());
                        });
                    } else {

                        $('input.processtable[name="empId[]"]:checked').each(function() {
                            empIds.push($(this).val());
                        });

                        // console.log('test')
                    }
                    // console.log(table.length)
                    console.log(empIds)
                    var process = $('a.btn.btn-primary.process.active').attr('id');
                    console.log(process);

                    if (empIds.length == 0) {
                        alertInfo('Please select an employee.');
                        return;
                    }

                    Swal.fire({
                        title: 'Confirm!',
                        text: 'Process DTR Logbox/Biometric.',
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
                                text: 'Processing DTR.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            setTimeout(() => {
                                Swal.close();
                                $('div#dtr_process').modal({
                                    backdrop: 'static',
                                    keyboard: false,
                                });
                                $("div#dtr_process").modal("show");
                                $(".store_code").text('');
                                if (process != 'single') {
                                    $(".store_code").text('(' + process.toUpperCase() + ')');
                                }
                                $("h5.date_process").text('Logbox/Biometric');

                                $('.closeButton').hide();
                                $("#progress-bar").css("width", '0');
                                $(".percentage").text('0%');
                                $(".tableName").text('Loading...');

                                $.ajax({
                                    url: "<?= site_url('processSingle'); ?>",
                                    type: "POST",
                                    data: formData,
                                    contentType: false,
                                    processData: false,
                                    success: function(data) {
                                        // try {
                                        //     let response = JSON.parse(data);
                                        //     if (response.message === "success") {
                                        //         Swal.fire({
                                        //             title: 'Success!',
                                        //             text: response.feedback,
                                        //             icon: 'success',
                                        //             iconColor: '#15ca20',
                                        //             confirmButtonText: 'Ok',
                                        //             confirmButtonColor: '#0d6efd',
                                        //             allowOutsideClick: false,
                                        //         }).then(() => {
                                        //             location.reload()
                                        //         });
                                        //     } else {
                                        //         errorAlert(response.message)
                                        //     }
                                        // } catch (e) {
                                        //     console.error("Error:", data);
                                        //     errorAlert(data)
                                        // }
                                    },
                                    beforeSend: function(jqXHR, settings) {

                                        var xhr = settings.xhr;
                                        settings.xhr = function() {
                                            var output = xhr();
                                            output.previous_text = '';

                                            output.onreadystatechange = function() {
                                                try {
                                                    if (output.readyState > 0) {

                                                        var result = JSON.parse(output.responseText.match(/{[^}]+}$/gi));
                                                        if (result.result == 'error') {
                                                            //test
                                                        } else if (result.result == 'initial') {
                                                            //test
                                                        } else if (result.result == 'loading') {
                                                            // console.log(process);


                                                            $("#progress-bar").css("width", result.msg);
                                                            $(".percentage").text(result.msg1);
                                                            $(".dateDuty").text(result.date);
                                                            if (result.exported == 'initialize') {
                                                                $('.spinnerBar').show();
                                                                $(".exported").removeClass('badge bg-secondary').text('');
                                                            } else if (result.exported == 'table_update') {
                                                                $('.spinnerBar').show();
                                                                $(".tableName").text(result.table);
                                                                $(".exported").removeClass('badge bg-success').addClass('badge bg-danger').text(result.exported);
                                                            } else if (result.exported == 'NO DATA') {
                                                                $('.spinnerBar').hide();
                                                                $(".exported").removeClass('badge bg-success').addClass('badge bg-danger').text(result.exported);
                                                            } else {
                                                                $('.spinnerBar').hide();
                                                                $(".exported").removeClass('badge bg-danger').addClass('badge bg-success').text(result.exported);
                                                            }
                                                            $('.cancelButton').show();
                                                        } else if (result.result == 'success') {
                                                            $('.spinnerBar').hide();
                                                            $("#progress-bar").css("width", result.bar);
                                                            $(".percentage").text(result.bar);
                                                            $(".tableName").text('');
                                                            $(".dateDuty").text(result.date);


                                                            setTimeout(function() {
                                                                $('.closeButton').show();
                                                            }, 2000);
                                                        }

                                                        output.previous_text = output.responseText;
                                                    }
                                                } catch (e) {
                                                    // console.log("[XHR STATECHANGE] Exception: " + e + ", Message: " + output.responseText);
                                                }
                                            };
                                            return output;
                                        };

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

        $('#processLogbox,#processBiometric,#processSingle').validate({
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

    function cs_first(statCut) {
        $('#cs_first_table').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('cs_first'); ?>",
                type: "POST",
                data: {
                    statCut
                },
            },
            "order": [
                [0, "desc"]
            ],
            "columnDefs": [{
                "targets": [1, 2],
                "orderable": false,

            }, {
                "targets": [2],
                "className": "text-center"
            }]
        });
    }

    function processCSfirst(statCut, i, df, dt) {
        Swal.fire({
            title: 'Confirm!',
            text: 'Process Change Shift [1st].',
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
                    text: 'Processing DTR.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                setTimeout(() => {
                    Swal.close();
                    $('div#dtr_process').modal({
                        backdrop: 'static',
                        keyboard: false,
                    });
                    $("div#dtr_process").modal("show");

                    $('.date_process').text(df + ' to ' + dt)

                    $('.closeButton').hide();
                    $("#progress-bar").css("width", '0');
                    $(".percentage").text('0%');
                    $(".tableName").text('Loading...');

                    $.ajax({
                        url: "<?= site_url('processCSfirst'); ?>",
                        type: "POST",
                        data: {
                            statCut,
                            i,
                            df,
                            dt
                        },
                        success: function(data) {
                            // try {
                            //     let response = JSON.parse(data);
                            //     if (response.message === "success") {
                            //         Swal.fire({
                            //             title: 'Success!',
                            //             text: response.feedback,
                            //             icon: 'success',
                            //             iconColor: '#15ca20',
                            //             confirmButtonText: 'Ok',
                            //             confirmButtonColor: '#0d6efd',
                            //             allowOutsideClick: false,
                            //         }).then(() => {
                            //             location.reload()
                            //         });
                            //     } else {
                            //         errorAlert(response.message)
                            //     }
                            // } catch (e) {
                            //     console.error("Error:", data);
                            //     errorAlert(data)
                            // }
                        },
                        beforeSend: function(jqXHR, settings) {

                            var xhr = settings.xhr;
                            settings.xhr = function() {
                                var output = xhr();
                                output.previous_text = '';

                                output.onreadystatechange = function() {
                                    try {
                                        if (output.readyState > 0) {

                                            var result = JSON.parse(output.responseText.match(/{[^}]+}$/gi));
                                            if (result.result == 'error') {
                                                //test
                                            } else if (result.result == 'initial') {
                                                //test
                                            } else if (result.result == 'loading') {
                                                // console.log(result.tbl);
                                                $("#progress-bar").css("width", result.msg);
                                                $(".percentage").text(result.msg1);
                                                $(".dateDuty").text(result.date);
                                                if (result.exported == 'initialize') {
                                                    $('.spinnerBar').show();
                                                    $(".exported").removeClass('badge bg-secondary').text('');
                                                } else if (result.exported == 'table_update') {
                                                    $('.spinnerBar').show();
                                                    $(".tableName").text(result.table);
                                                    $(".exported").removeClass('badge bg-success').addClass('badge bg-danger').text(result.exported);
                                                } else if (result.exported == 'NO DATA') {
                                                    $('.spinnerBar').hide();
                                                    $(".exported").removeClass('badge bg-success').addClass('badge bg-danger').text(result.exported);
                                                } else {
                                                    $('.spinnerBar').hide();
                                                    $(".exported").removeClass('badge bg-danger').addClass('badge bg-success').text(result.exported);
                                                }
                                                $('.cancelButton').show();
                                            } else if (result.result == 'success') {
                                                $('.spinnerBar').hide();
                                                $("#progress-bar").css("width", result.bar);
                                                $(".percentage").text(result.bar);
                                                $(".tableName").text('');
                                                $(".dateDuty").text(result.date);


                                                setTimeout(function() {
                                                    $('.closeButton').show();
                                                }, 2000);
                                            }

                                            output.previous_text = output.responseText;
                                        }
                                    } catch (e) {
                                        // console.log("[XHR STATECHANGE] Exception: " + e + ", Message: " + output.responseText);
                                    }
                                };
                                return output;
                            };

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

    function od_second(statCut) {
        $('#od_second_table').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('od_second'); ?>",
                type: "POST",
                data: {
                    statCut
                },
            },
            "order": [
                [0, "desc"]
            ],
            "columnDefs": [{
                "targets": [1, 2, 3, 4],
                "orderable": false,

            }, {
                "targets": [2, 3, 4],
                "className": "text-center"
            }]
        });
    }

    function processODsecond(statCut, df, dt) {
        Swal.fire({
            title: 'Confirm!',
            text: 'Process Off Duty [2nd].',
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
                    text: 'Processing DTR.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                setTimeout(() => {
                    Swal.close();
                    $('div#dtr_process').modal({
                        backdrop: 'static',
                        keyboard: false,
                    });
                    $("div#dtr_process").modal("show");

                    $('.date_process').text(df + ' to ' + dt)

                    $('.closeButton').hide();
                    $("#progress-bar").css("width", '0');
                    $(".percentage").text('0%');
                    $(".tableName").text('Loading...');

                    $.ajax({
                        url: "<?= site_url('processODsecond'); ?>",
                        type: "POST",
                        data: {
                            statCut,
                            df,
                            dt
                        },
                        success: function(data) {
                            // try {
                            //     let response = JSON.parse(data);
                            //     if (response.message === "success") {
                            //         Swal.fire({
                            //             title: 'Success!',
                            //             text: response.feedback,
                            //             icon: 'success',
                            //             iconColor: '#15ca20',
                            //             confirmButtonText: 'Ok',
                            //             confirmButtonColor: '#0d6efd',
                            //             allowOutsideClick: false,
                            //         }).then(() => {
                            //             location.reload()
                            //         });
                            //     } else {
                            //         errorAlert(response.message)
                            //     }
                            // } catch (e) {
                            //     console.error("Error:", data);
                            //     errorAlert(data)
                            // }
                        },
                        beforeSend: function(jqXHR, settings) {

                            var xhr = settings.xhr;
                            settings.xhr = function() {
                                var output = xhr();
                                output.previous_text = '';
                                var odSecondCalled = false; // flag

                                output.onreadystatechange = function() {
                                    try {
                                        if (output.readyState > 0) {

                                            var result = JSON.parse(output.responseText.match(/{[^}]+}$/gi));
                                            if (result.result == 'error') {
                                                //test
                                            } else if (result.result == 'initial') {
                                                //test
                                            } else if (result.result == 'loading') {
                                                // console.log(result.tbl);
                                                $("#progress-bar").css("width", result.msg);
                                                $(".percentage").text(result.msg1);
                                                $(".dateDuty").text(result.date);
                                                if (result.exported == 'initialize') {
                                                    $('.spinnerBar').show();
                                                    $(".exported").removeClass('badge bg-secondary').text('');
                                                } else if (result.exported == 'table_update') {
                                                    $('.spinnerBar').show();
                                                    $(".tableName").text(result.table);
                                                    $(".exported").removeClass('badge bg-success').addClass('badge bg-danger').text(result.exported);
                                                } else if (result.exported == 'NO DATA') {
                                                    $('.spinnerBar').hide();
                                                    $(".exported").removeClass('badge bg-success').addClass('badge bg-danger').text(result.exported);
                                                } else {
                                                    $('.spinnerBar').hide();
                                                    $(".exported").removeClass('badge bg-danger').addClass('badge bg-success').text(result.exported);
                                                }
                                                $('.cancelButton').show();
                                            } else if (result.result == 'success') {
                                                $('.spinnerBar').hide();
                                                $("#progress-bar").css("width", result.bar);
                                                $(".percentage").text(result.bar);
                                                $(".tableName").text('');
                                                $(".dateDuty").text(result.date);

                                                if (!odSecondCalled) { // only run once
                                                    odSecondCalled = true;
                                                    od_second(statCut);
                                                }

                                                setTimeout(function() {
                                                    $('.closeButton').show();
                                                }, 2000);
                                            }

                                            output.previous_text = output.responseText;
                                        }
                                    } catch (e) {
                                        // console.log("[XHR STATECHANGE] Exception: " + e + ", Message: " + output.responseText);
                                    }
                                };
                                return output;
                            };

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

    function co_third(statCut) {
        $('#co_thirtd_table').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('co_third'); ?>",
                type: "POST",
                data: {
                    statCut
                },
            },
            "order": [
                [0, "desc"]
            ],
            "columnDefs": [{
                "targets": [1, 2],
                "orderable": false,

            }, {
                "targets": [2],
                "className": "text-center"
            }]
        });
    }

    function coList(statCut, cut, dateFrom, dateTo) {
        $('div#employee_list').modal({
            backdrop: 'static',
            keyboard: false,
        });
        $("div#employee_list").modal("show");

        $("span#cutoff").html(dateFrom + ' - ' + dateTo);
        $.ajax({
            type: "POST",
            url: "<?= site_url('co_list'); ?>",
            data: {
                statCut,
                cut,
                dateFrom,
                dateTo
            },
            beforeSend: function() {
                loader_circle('employee_list')
            },
            success: function(data) {
                $('div.employee_list').html(data);
                $('#employee_list_table').DataTable({
                    "destroy": true,
                    "order": [
                        [1, "asc"]
                    ],
                    "columnDefs": [{
                            "targets": [0, 2, 3, 5, 6],
                            "orderable": false,
                        },
                        {
                            "targets": [2],
                            "width": "35%"
                        },
                        {
                            "targets": [4, 5],
                            "className": "text-center"
                        }
                    ],
                });
            }
        });
    }

    function processCOthird() {

        var statCut = $('input[name="statCut"]').val();
        var cut = $('input[name="cut"]').val();
        var df = $('input[name="dateFrom"]').val();
        var dt = $('input[name="dateTo"]').val();

        var empIds = [];
        var table = $('#employee_list_table').DataTable();

        table.rows().nodes().to$().find('input[name="empId[]"]:checked').each(function() {
            empIds.push($(this).val());
        });

        // console.log(empIds)

        if (empIds.length == 0) {
            alertInfo('Please select an employee.');
            return;
        }

        Swal.fire({
            title: 'Confirm!',
            text: 'Process and finalize DTR.',
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
                    text: 'Processing DTR.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                setTimeout(() => {
                    Swal.close();
                    $('div#dtr_process').modal({
                        backdrop: 'static',
                        keyboard: false,
                    });
                    $("div#dtr_process").modal("show");

                    $('.date_process').text(df + ' to ' + dt)

                    if (cut == 1) {
                        $('h5.cutoff_process').html('First Cutoff');
                    } else {
                        $('h5.cutoff_process').html('Second Cutoff');
                    }

                    $('.closeButton').hide();
                    $("#progress-bar").css("width", '0');
                    $(".percentage").text('0%');
                    $(".tableName").text('Loading...');

                    $.ajax({
                        url: "<?= site_url('finalizeDtr'); ?>",
                        type: "POST",
                        data: {
                            statCut,
                            df,
                            dt,
                            cut,
                            empIds
                        },
                        success: function(data) {
                            // try {
                            //     let response = JSON.parse(data);
                            //     if (response.message === "success") {
                            //         Swal.fire({
                            //             title: 'Success!',
                            //             text: response.feedback,
                            //             icon: 'success',
                            //             iconColor: '#15ca20',
                            //             confirmButtonText: 'Ok',
                            //             confirmButtonColor: '#0d6efd',
                            //             allowOutsideClick: false,
                            //         }).then(() => {
                            //             location.reload()
                            //         });
                            //     } else {
                            //         errorAlert(response.message)
                            //     }
                            // } catch (e) {
                            //     console.error("Error:", data);
                            //     errorAlert(data)
                            // }
                        },
                        beforeSend: function(jqXHR, settings) {

                            var xhr = settings.xhr;
                            settings.xhr = function() {
                                var output = xhr();
                                output.previous_text = '';

                                output.onreadystatechange = function() {
                                    try {
                                        if (output.readyState > 0) {

                                            var result = JSON.parse(output.responseText.match(/{[^}]+}$/gi));
                                            if (result.result == 'error') {
                                                //test
                                            } else if (result.result == 'initial') {
                                                //test
                                            } else if (result.result == 'loading') {
                                                // console.log(result.tbl);
                                                $("#progress-bar").css("width", result.msg);
                                                $(".percentage").text(result.msg1);
                                                $(".dateDuty").text(result.date);
                                                if (result.exported == 'initialize') {
                                                    $('.spinnerBar').show();
                                                    $(".exported").removeClass('badge bg-secondary').text('');
                                                } else if (result.exported == 'table_update') {
                                                    $('.spinnerBar').show();
                                                    $(".tableName").text(result.table);
                                                    $(".exported").removeClass('badge bg-success').addClass('badge bg-danger').text(result.exported);
                                                } else if (result.exported == 'NO DATA') {
                                                    $('.spinnerBar').hide();
                                                    $(".exported").removeClass('badge bg-success').addClass('badge bg-danger').text(result.exported);
                                                } else {
                                                    $('.spinnerBar').hide();
                                                    $(".exported").removeClass('badge bg-danger').addClass('badge bg-success').text(result.exported);
                                                }
                                                $('.cancelButton').show();
                                            } else if (result.result == 'success') {
                                                $('.spinnerBar').hide();
                                                $("#progress-bar").css("width", result.bar);
                                                $(".percentage").text(result.bar);
                                                $(".tableName").text('');
                                                $(".dateDuty").text(result.date);

                                                setTimeout(function() {
                                                    $('.closeButton').show();
                                                }, 2000);
                                            }

                                            output.previous_text = output.responseText;
                                        }
                                    } catch (e) {
                                        // console.log("[XHR STATECHANGE] Exception: " + e + ", Message: " + output.responseText);
                                    }
                                };
                                return output;
                            };

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

    function processMenu(process) {
        $.ajax({
            type: "POST",
            url: "<?= site_url('processMenu'); ?>",
            data: {
                process
            },
            beforeSend: function() {
                loader_circle('processMenu')
            },
            success: function(data) {
                $('div.processMenu').html(data);
                $('#processtable').DataTable({
                    "destroy": true,
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
            }
        });
    }

    function chkDateFrom(df) {

        var dt = $('input[name="endDate"]').val();
        if (dt) {

            var dfDate = new Date(df);
            var dtDate = new Date(dt);

            if (dfDate > dtDate) {
                alertRequired('Start Date should be less than End Date.')
                $('input[name="endDate"]').val('');
            }
        }
    }

    function chkDateTo(dt) {

        var df = $('input[name="startDate"]').val();
        if (df) {
            var dfDate = new Date(df);
            var dtDate = new Date(dt);

            if (dfDate > dtDate) {
                alertRequired('Start Date should be less than End Date.')
                $('input[name="endDate"]').val('');
            }
        }
    }

    let ajaxCallCount = 0;
    let ajaxRequest;

    function searchPromos(string) {
        $('div.promoCutoffs,span.cutoff,div.promoDetails').html('');
        if (string.trim() != '') {

            if (ajaxRequest && ajaxRequest.readyState !== 4) {
                ajaxRequest.abort();
            }

            ajaxCallCount++;

            ajaxRequest = $.ajax({
                type: "POST",
                url: "<?= site_url('process_searchPromo'); ?>",
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

    function selectPromo(name, empId) {
        console.log(name, empId)
        $(".dropdown-list").html('').hide();
        $("input[name='searchPromo']").val('')
        $('div#promosTable').show();

        var ids = [];
        $('input[name="empId[]"]').each(function() {
            ids.push($(this).val());
        });
        if (ids.includes(empId)) {
            return;
        }
        $('.promos').append(`
        <tr>
            <td>${empId}</td>
            <td>${name}</td>
            <td class="text-center">
                <input type="checkbox" class="processtable" name="empId[]" value="${empId}" checked>
            </td>
        </tr>`);
    }

    function processFilter() {
        var store = $('select[name="bu"]').val();
        var department = $('select[name="department"]').val();
        var promoType = $('select[name="promo_type"]').val();
        console.log($('select[name="promo_type"]').val())
        if (store != '') {
            $.ajax({
                type: "POST",
                url: "<?= site_url('processFilter'); ?>",
                data: {
                    store,
                    department,
                    promoType
                },
                beforeSend: function() {
                    loader_circle('filteredPromos')
                },
                success: function(data) {
                    $('div.filteredPromos').html(data);
                    $('#processtable').DataTable({
                        "destroy": true,
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
                }
            });
        } else {
            alertRequired('Please select a Store/BU.');
        }
    }

    function chkAllbox(id) {

        var selectAllChecked = $('input[name="chkAll"]').prop('checked');
        // var table = $('#bioPromoList').DataTable();
        var table = $('#' + id).DataTable();

        $('input[name="batch"]').prop('checked', false);

        table.rows().nodes().to$().find('.' + id).prop('checked', selectAllChecked);

        if (!selectAllChecked) {
            table.rows().nodes().to$().find('.' + id).prop('checked', false);
        }
    }

    function chkAllbatch(batchClass) {

        var table = $('#employee_list_table').DataTable();
        $('input[name="chkAll"]').prop('checked', false);
        table.rows().nodes().to$().find('input.employee_list_table').prop('checked', false);
        table.rows().nodes().to$().find('input.' + batchClass).prop('checked', true);
    }


    function promoIdList() {
        $.ajax({
            type: "POST",
            url: "<?= site_url('bioPromoList'); ?>",
            beforeSend: function() {
                loader_circle('promoIdList')
            },
            success: function(data) {
                $('div.promoIdList').html(data);
                $('#bioPromoList').DataTable({
                    "destroy": true,
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