<script type="text/javascript">
    var exportusers;
    $(document).ready(function() {
        $('input.datePicker').datetimepicker({
            format: 'YYYY-MM-DD',
            useCurrent: false
        });




        $.validator.setDefaults({
            submitHandler: function(form) {
                var formId = form.id;
                if (formId == 'dutyInsert') {
                    var formData = $(form).serialize();

                    Swal.fire({
                        title: 'Confirm!',
                        text: 'Insert Duty to table.',
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
                                text: 'Insering Duty to table.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            setTimeout(() => {
                                $.ajax({
                                    url: "<?= site_url('dutyInsert'); ?>",
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
                }
            }
        });

        $.validator.addMethod("noSpace", function(value, element) {
            return value.trim().length > 0; // true if not only spaces
        }, "This field cannot be empty or contain only spaces.");

        $('#dutyInsert').validate({
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
                searchPromoDuty: {
                    noSpace: true
                }
            }
        });










    });

    let ajaxCallCount = 0;
    let ajaxRequest;

    function searchPromoduty(string) {
        $('input[name="empId"]').val('');
        if (string.trim() != '') {

            if (ajaxRequest && ajaxRequest.readyState !== 4) {
                ajaxRequest.abort();
                $('.dropdown-list').html('<ul class="list-group"><span class="list-group-item">' +
                    '<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>' +
                    '</span></ul>');
            }

            ajaxCallCount++;

            ajaxRequest = $.ajax({
                type: "POST",
                url: "<?= site_url('searchPromoDuty'); ?>",
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

    function selectDuty(name, empId) {
        $(".dropdown-list").hide().html('');
        $('input[name="searchPromoDuty"]').val('[' + empId + '] ' + name);
        $('input[name="empId"]').val(empId);
    }

    function setDateto(df) {
        $('input[name="dateTo"]').val('').datetimepicker('minDate', moment(df));
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