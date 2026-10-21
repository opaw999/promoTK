<script type="text/javascript">
    $(document).ready(function() {
        $('#dateFrom,#dateTo').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false
        });

        $('.select2').select2({
            theme: 'bootstrap4',
            // allowClear: true,
        })
        var page = '<?= $file ?>';

        $('#time_entries,#dtr_requests,#dtr_process,#viewMasterfile,#defSched,#viewDO,#viewSched').on('hidden.bs.modal', function() {
            $('body').addClass('modal-open');
        });

        if (page == 'per_cutoff') {
            $('#view_dtr').on('hidden.bs.modal', function() {
                $('body').addClass('modal-open');
            });
        }


        var statCut = $('a.btn.btn-primary.statCut.active').attr('id');
        dtr_cutoffs(statCut)

        $('a.statCut').click(function() {
            $('a.statCut').removeClass('btn-primary active').addClass('btn-default');
            $(this).addClass('btn-primary active');

            var statCut = this.id;
            dtr_cutoffs(statCut)
        });

        $('table#cutoff_table').on('click', 'a.emp_list', function() {
            $('#employee_list').modal({
                backdrop: 'static',
                keyboard: false
            });

            $('#employee_list').modal('show');

            let [statCut, cut, dateFrom, dateTo] = this.id.split('|');
            $("span#cutoff").html(dateFrom + ' - ' + dateTo);
            employee_list(statCut, cut, dateFrom, dateTo);
        });
    });

    function dtr_cutoffs(statCut) {
        $('#cutoff_table').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('cutoff_table'); ?>",
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

    function employee_list(statCut, cut, dateFrom, dateTo) {
        $('#employee_list_table').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?= site_url('employee_list'); ?>",
                type: "POST",
                data: {
                    statCut,
                    cut,
                    dateFrom,
                    dateTo
                },
            },
            "order": [
                [1, "asc"]
            ],
            "columnDefs": [{
                    "targets": [0, 2, 3, 4, 5],
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

    function view_dtr(payload) {
        $('#view_dtr').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#view_dtr').modal('show');

        let [empId, recordNo, dateFrom, dateTo, i, statCut, bioM] = payload.split('|')

        $.ajax({
            type: "POST",
            url: "<?= site_url('dtr_info'); ?>",
            data: {
                empId,
                recordNo,
                dateFrom,
                dateTo,
                i,
                statCut,
                bioM
            },
            beforeSend: function() {
                loader_circle('view_dtr')
            },
            success: function(data) {
                $('div.view_dtr').html(data);
                dtr_details(payload)
                // dtr_net(payload)
            }
        });
    }

    function dtr_net(payload) {
        let [empId, recordNo, dateFrom, dateTo, i, statCut, bioM] = payload.split('|')
        $.ajax({
            type: "POST",
            url: "<?= site_url('dtr_net'); ?>",
            data: {
                empId,
                recordNo,
                dateFrom,
                dateTo,
                i,
                statCut,
                bioM
            },
            success: function(data) {
                $('td.net_data').html(data);
            }
        });
    }

    function dtr_details(payload) {
        dtr_net(payload)
        let [empId, recordNo, dateFrom, dateTo, i, statCut, bioM] = payload.split('|')
        $.ajax({
            type: "POST",
            url: "<?= site_url('dtr_details'); ?>",
            data: {
                empId,
                recordNo,
                dateFrom,
                dateTo,
                i,
                statCut,
                bioM
            },
            beforeSend: function() {
                loader_circle('dtr_details')
            },
            success: function(data) {
                $('div.dtr_details').html(data);
                $('[data-toggle="tooltip"]').tooltip();

            }
        });

    }

    function viewTime(bioM, dateFrom, table, allowId) {
        $('div#time_entries').modal({
            backdrop: 'static',
            keyboard: false,
        });

        $("#time_entries").modal("show");

        $.ajax({
            type: "POST",
            url: "<?= site_url('time_entries'); ?>",
            data: {
                bioM,
                dateFrom,
                table,
                allowId
            },
            beforeSend: function() {
                loader_circle('time_entries')
            },
            success: function(data) {
                $("div.time_entries").html(data);
            }
        });
    }

    function viewRequest(request, empId, date) {
        $('div#dtr_requests').modal({
            backdrop: 'static',
            keyboard: false,
        });
        $("div#dtr_requests").modal("show");

        $.ajax({
            type: "POST",
            url: "<?= site_url('dtr_requests'); ?>",
            data: {
                request,
                empId,
                date
            },
            beforeSend: function(data) {
                loader_circle('dtr_requests')
            },
            success: function(data) {
                $("div.dtr_requests").html(data)
            }
        });
    }

    let ajaxCallCount = 0;
    let ajaxRequest;

    function searchPromo(string) {
        $('div.promoCutoffs,span.cutoff,div.promoDetails').html('');
        if (string.trim() != '') {

            if (ajaxRequest && ajaxRequest.readyState !== 4) {
                ajaxRequest.abort();
            }

            ajaxCallCount++;

            ajaxRequest = $.ajax({
                type: "POST",
                url: "<?= site_url('searchPromo'); ?>",
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

    function searchEarlyCo(string) {
        $('div.promoCutoffs,span.cutoff,div.promoDetails').html('');
        if (string.trim() != '') {

            if (ajaxRequest && ajaxRequest.readyState !== 4) {
                ajaxRequest.abort();
            }

            ajaxCallCount++;

            ajaxRequest = $.ajax({
                type: "POST",
                url: "<?= site_url('searchEarlyCo'); ?>",
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

    function selectEarlyCo(value) {
        if (value.trim() != '') {
            $(".dropdown-list").hide();
            $('#searching').val('');
            let [empId, name] = value.split('|')
            if (empId != '' && name != '') {
                console.log(empId, name)
                $('div#promosTable').show();
                $("select[name='promo']").val(null).trigger('change');
                var ids = [];
                $('input[name="empIds[]"]').each(function() {
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
                        <input class="form-check-input" type="checkbox" name="empIds[]" value="${empId}" onclick="selectEmp()" checked>
                    </td>
                </tr>`);
            }
        }
    }

    function selectEmp() {
        var ids = [];
        $('input[name="empIds[]"]:checked').each(function() {
            ids.push($(this).val());
        });
        if (ids.length == 0) {
            $("button.submit").prop('disabled', true);
        } else {
            $("button.submit").prop('disabled', false);
        }
    }

    function dateFromcs() {
        $("button.submit").prop('disabled', true);
        $("input[name='dateTo']").val('');

        var ids = [];
        $('input[name="empIds[]"]').each(function() {
            ids.push($(this).val());
        });
        // var name = $("select[name='empId']").val();
        // console.log(name)
        if (ids.length == 0) {
            $("input[name='dateFrom']").val('');
            alertRequired('Please select employee!')
            if (ids.length == 0) {
                $("select[name='promo']").next().find('.select2-selection').css('border-color', '#dd4b39');
            }

        } else {
            $("select[name='promo']").css('border-color', '');
        }
        $(".select2-selection").on("focus", function(e) {
            $(this).css('border-color', '');
        });
    }

    function dateTocs(dateTo) {

        var dateFrom = $("input[name='dateFrom']").val();
        // var empId = $("select[name='empId']").val();

        var ids = [];
        $('input[name="empIds[]"]:checked').each(function() {
            ids.push($(this).val());
        });


        if (ids.length != 0 && dateFrom != '') {

            $.ajax({
                type: "POST",
                url: "<?= site_url('calculateDate'); ?>",
                data: {
                    dateFrom,
                    dateTo
                },
                success: function(data) {
                    numDatesCal = data;
                    if (numDatesCal == 'failed') {
                        alertRequired('Date To must be later than Date From!')
                        $("input[name='dateTo']").val('');
                        $("button.submit").prop('disabled', true);
                    } else {
                        $("button.submit").prop('disabled', false);
                    }
                }
            });

        } else {

            $("input[name='dateTo']").val('');
            if (ids.length == '') {
                $("select[name='promo']").next().find('.select2-selection').css('border-color', '#dd4b39');
            }

            if (dateFrom == '') {
                $("input[name='dateFrom']").css('border-color', '#dd4b39');
            }
            $("button.submit").prop('disabled', true);
            alertRequired('Please fill the required fields!')
        }
        $("input[name='dateFrom'],.select2-selection").on("focus", function(e) {
            $(this).css('border-color', '');
        });
    }

    function selectPromo(name, empId, recordNo, statCut, sched) {
        $(".dropdown-list").hide().html('');
        $('input[name="searchPromo"]').val('[' + empId + '] ' + name);
        $('span.cutoff').html('[' + sched + ']');
        promoDetails(empId, recordNo, statCut)
        promoCutoffs(empId, recordNo, statCut)
    }

    function promoDetails(empId, recordNo, statCut) {
        $.ajax({
            type: "POST",
            url: "<?= site_url('promoDetails'); ?>",
            data: {
                empId,
                recordNo,
                statCut
            },
            beforeSend: function() {
                loader_circle('promoDetails')
            },
            success: function(data) {
                $("div.promoDetails").html(data);
            }
        });
    }

    function promoCutoffs(empId, recordNo, statCut) {
        $.ajax({
            type: "POST",
            url: "<?= site_url('promoCutoffs'); ?>",
            data: {
                empId,
                recordNo,
                statCut
            },
            beforeSend: function() {
                loader_circle('promoCutoffs')
            },
            success: function(data) {
                $("div.promoCutoffs").html(data);
                $('#promoCutoffs').DataTable({
                    "destroy": true,
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
                $('#promoCutoffs2').DataTable({
                    "destroy": true,
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
        });
    }

    function promoClear() {
        $(".dropdown-list").hide();
        $('input[name="searchPromo"]').val('')
        $('div.promoCutoffs,span.cutoff,div.promoDetails').html('');
    }

    function dtr_process(payload) {

        let [empId, recordNo, statCut, bioM] = payload.split('|')

        var df = $('input[name="dateFrom"]').val();
        var dt = $('input[name="dateTo"]').val();
        var cut = $('input[name="cut"]').val();
        $('h5.date_process').html(df + ' to ' + dt);
        if (cut == 1) {
            $('h5.cutoff_process').html('First Cutoff');
        } else {
            $('h5.cutoff_process').html('Second Cutoff');
        }

        var empIds = [empId];

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

                                                $('div#view_dtr').modal('hide');
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

    function viewMasterfile(payload) {
        let [empId, recordNo, statCut, bioM] = payload.split('|')

        $('#viewMasterfile').modal({
            backdrop: 'static',
            keyboard: false
        });

        $('#viewMasterfile').modal('show');
        $.ajax({
            type: "POST",
            url: "<?= site_url('viewMasterfile'); ?>",
            data: {
                empId,
                recordNo,
                statCut,
                bioM
            },
            beforeSend: function() {
                loader_circle('viewMasterfile')
            },
            success: function(data) {
                $('div.viewMasterfile').html(data);

                $('#multiSched').DataTable({
                    "destroy": true,

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