<script>
    $(document).ready(function() {

        var statCut = $('a.btn.btn-primary.statCut.active').attr('id');

        statCuts(statCut, '<?= $file; ?>')

        $('a.statCut').click(function() {
            $('a.statCut').removeClass('btn-primary active').addClass('btn-default');
            $(this).addClass('btn-primary active');

            var statCut = this.id;
            statCuts(statCut, '<?= $file; ?>')
        });

        $('#viewReqPerEmp,#viewEmpOTP,#viewEmpEOA,#viewEmpCUA,#viewEmpLOA,#viewEmpDWA,#viewEmpLOA,#viewEmpDOS,#viewEmpDOL').on('hidden.bs.modal', function() {
            $('body').addClass('modal-open');
        });

    });

    function statCuts(statCut, func) {
        // console.log(statCut, func)

        $('#viewReq_table').DataTable({
            "destroy": true,
            "ajax": {
                url: "<?php echo site_url(''); ?>" + func,
                type: "POST",
                data: {
                    statCut
                },
            },
            "order": [
                [0, "desc"]
            ],
            "columnDefs": [{
                "targets": [1, 2, 3, 4, 5],
                "orderable": false,
            }, {
                "targets": [2, 3, 4, 5],
                "className": "text-center",
            }],
        });
    }

    function viewReqPerEmp(promoCut, d1, d2) {
        console.log(promoCut, d1, d2)
        var pageName = '<?= $file; ?>'
        $('div#viewReqPerEmp').modal({
            backdrop: 'static',
            keyboard: false,
        });
        $("div#viewReqPerEmp").modal("show");

        const pageTitles = {
            'otAuth': 'Overtime Authorization',
            'otPayment': 'Overtime Payment',
            'cuAuth': 'Coverup Authorization',
            'utAuth': 'Undertime Authorization',
            'dwa': 'Days Worked Adjustment',
            'loa': 'Leave of Absence',
            'doShort': 'Change Dayoff (Short Term)',
            'doLong': 'Change Dayoff (Long Term)'
        };

        if (pageTitles[pageName]) {
            $("h5.viewReqPerEmp").html(`${pageTitles[pageName]} [<b class="text-danger">${d1}</b> - <b class="text-danger">${d2}</b>]`);
        }

        $.ajax({
            type: "POST",
            url: "<?php echo site_url('viewReqPerEmp'); ?>",
            data: {
                d1,
                d2,
                promoCut,
                pageName
            },
            beforeSend: function(data) {
                loader_circle('viewReqPerEmp')
            },
            success: function(data) {

                $("div.viewReqPerEmp").html(data);
                $('table#viewReqPerEmp').dataTable({
                    "destroy": true,
                    "autoWidth": false,
                    "order": [],
                    "columnDefs": [{
                        "targets": [0, 1, 2, 3, 4, 5, 6],
                        "orderable": false,
                    }, {
                        "targets": [5, 6],
                        "className": "text-center",
                    }],
                });
            }
        });
    }

    function viewEmpEOA(eOAId) {
        console.log(eOAId)
        $('div#viewEmpEOA').modal({
            backdrop: 'static',
            keyboard: false,
        });
        $("div#viewEmpEOA").modal("show");


        $.ajax({
            type: "POST",
            url: "<?php echo site_url('viewEmpEOA'); ?>",
            data: {
                eOAId
            },
            beforeSend: function(data) {
                loader_circle('viewEmpEOA')
            },
            success: function(data) {

                $("div.viewEmpEOA").html(data);
            }
        });
    }

    function viewEmpOTP(pay_id) {
        console.log(pay_id)
        $('div#viewEmpOTP').modal({
            backdrop: 'static',
            keyboard: false,
        });
        $("div#viewEmpOTP").modal("show");


        $.ajax({
            type: "POST",
            url: "<?php echo site_url('viewEmpOTP'); ?>",
            data: {
                pay_id
            },
            beforeSend: function(data) {
                loader_circle('viewEmpOTP')
            },
            success: function(data) {

                $("div.viewEmpOTP").html(data);
            }
        });
    }

    function viewEmpCUA(eCAId) {
        console.log(eCAId)
        $('div#viewEmpCUA').modal({
            backdrop: 'static',
            keyboard: false,
        });
        $("div#viewEmpCUA").modal("show");


        $.ajax({
            type: "POST",
            url: "<?php echo site_url('viewEmpCUA'); ?>",
            data: {
                eCAId
            },
            beforeSend: function(data) {
                loader_circle('viewEmpCUA')
            },
            success: function(data) {

                $("div.viewEmpCUA").html(data);
            }
        });
    }

    function viewEmpUTA(eUAId) {
        console.log(eUAId)
        $('div#viewEmpUTA').modal({
            backdrop: 'static',
            keyboard: false,
        });
        $("div#viewEmpUTA").modal("show");


        $.ajax({
            type: "POST",
            url: "<?php echo site_url('viewEmpUTA'); ?>",
            data: {
                eUAId
            },
            beforeSend: function(data) {
                loader_circle('viewEmpUTA')
            },
            success: function(data) {

                $("div.viewEmpUTA").html(data);
            }
        });
    }

    function viewEmpDWA(dWAId) {
        console.log(dWAId)
        $('div#viewEmpDWA').modal({
            backdrop: 'static',
            keyboard: false,
        });
        $("div#viewEmpDWA").modal("show");


        $.ajax({
            type: "POST",
            url: "<?php echo site_url('viewEmpDWA'); ?>",
            data: {
                dWAId
            },
            beforeSend: function(data) {
                loader_circle('viewEmpDWA')
            },
            success: function(data) {

                $("div.viewEmpDWA").html(data);
            }
        });
    }

    function viewEmpLOA(rLAId) {
        console.log(rLAId)
        $('div#viewEmpLOA').modal({
            backdrop: 'static',
            keyboard: false,
        });
        $("div#viewEmpLOA").modal("show");


        $.ajax({
            type: "POST",
            url: "<?php echo site_url('viewEmpLOA'); ?>",
            data: {
                rLAId
            },
            beforeSend: function(data) {
                loader_circle('viewEmpLOA')
            },
            success: function(data) {

                $("div.viewEmpLOA").html(data);
            }
        });
    }

    function viewEmpDOS(acdId) {
        console.log(acdId)
        $('div#viewEmpDOS').modal({
            backdrop: 'static',
            keyboard: false,
        });
        $("div#viewEmpDOS").modal("show");


        $.ajax({
            type: "POST",
            url: "<?php echo site_url('viewEmpDOS'); ?>",
            data: {
                acdId
            },
            beforeSend: function(data) {
                loader_circle('viewEmpDOS')
            },
            success: function(data) {

                $("div.viewEmpDOS").html(data);
            }
        });
    }

    function viewEmpDOL(adlid) {
        console.log(adlid)
        $('div#viewEmpDOL').modal({
            backdrop: 'static',
            keyboard: false,
        });
        $("div#viewEmpDOL").modal("show");


        $.ajax({
            type: "POST",
            url: "<?php echo site_url('viewEmpDOL'); ?>",
            data: {
                adlid
            },
            beforeSend: function(data) {
                loader_circle('viewEmpDOL')
            },
            success: function(data) {

                $("div.viewEmpDOL").html(data);
            }
        });
    }

    function updateReason(rLAId) {
        var reason = $('input[name="reason"]').val();
        if (reason.trim() != '') {
            Swal.fire({
                title: 'Confirm!',
                text: 'Update leave of absence reason.',
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
                        text: 'Updating reason...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    setTimeout(() => {
                        $.ajax({
                            type: "POST",
                            url: "<?php echo site_url('updateReason'); ?>",
                            data: {
                                rLAId,
                                reason
                            },
                            success: function(data) {
                                try {
                                    let response = JSON.parse(data);
                                    if (response.message == 'success') {
                                        Swal.fire({
                                            title: 'Success!',
                                            text: response.feedback,
                                            icon: 'success',
                                            iconColor: '#15ca20',
                                            confirmButtonColor: '#3085d6',
                                            confirmButtonText: 'Ok',
                                            allowOutsideClick: false,
                                        }).then((result) => {
                                            if (result.isConfirmed) {
                                                viewEmpLOA(rLAId)
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
    }

    function loader_circle(page) {
        $('div.' + page).html(
            '<div style="text-align: center;">' +
            '<img src="<?= base_url('assets/dist/img/loader_seq.gif') ?>">' +
            '</div>'
        );
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
</script>