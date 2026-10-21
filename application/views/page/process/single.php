<style>
    #dtr_process .modal-content {
        background-image: url('<?= base_url('assets/dist/img/exportbg.jpg') ?>');
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
        color: white;
        border: none;
        border-radius: 0;
        height: 100%;
    }

    .list-group-item {
        color: black;
        text-decoration: none;
        display: block;
        padding: 10px;
    }

    .list-group-item:hover {
        background-color: #007bff;
        color: white;
    }

    .list-group {
        position: absolute;
        z-index: 999;
        max-height: 200px;
        width: 100%;
        overflow-y: auto;
    }

    .dropdown-list {
        position: relative;
    }
</style>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Single Process</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Promo TK</li>
                        <li class="breadcrumb-item active">Process</li>
                        <li class="breadcrumb-item active">Single Process</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <form id="processSingle" novalidate="novalidate">
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <a href="javascript:;" id="single" class="btn btn-primary process active btn-xs mb-1 mr-1">Single Process</a>
                        </div>
                        <div class="card-body processMenu"></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="startDate">Start Date</label>
                                        <input type="text" class="form-control datetimepicker-input" id="startDate" name="startDate" onchange="chkDateFrom(this.value)" data-toggle="datetimepicker" data-target="#startDate" placeholder="yyyy-mm-dd" autocomplete="off" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="endDate">End Date</label>
                                        <input type="text" class="form-control datetimepicker-input" id="endDate" name="endDate" onchange="chkDateTo(this.value)" data-toggle="datetimepicker" data-target="#endDate" placeholder="yyyy-mm-dd" autocomplete="off" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <hr>
                                    <label>Query Entries </label>
                                    <div class="row">

                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <div class="custom-control custom-radio">
                                                    <input class="custom-control-input" type="radio" id="customRadio1" name="data_log" value="database">
                                                    <label for="customRadio1" class="custom-control-label">Database</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <div class="custom-control custom-radio">
                                                    <input class="custom-control-input" type="radio" id="customRadio2" name="data_log" value="logfile" checked>
                                                    <label for="customRadio2" class="custom-control-label">Logfile</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
</div>

<div class="modal fade" id="dtr_process">
    <div class="modal-dialog" style="max-width: 100%; margin:0;height: 100%;">
        <div class="modal-content ">
            <div class="modal-body">
                <div class="row" style="padding: 100px; position:relative;">
                    <div class="col-md-12">
                        <h3 class="modal-title text-white text-center">Calculating PROMO D.T.R.</h3>
                        <h5 class="date_process text-center">Logbox/Biometric</h5>
                        <h5 class="cutoff_process text-center"></h5>
                    </div>
                    <div class="col-md-12 mt-3">
                        <h1 class="percentage text-white">0%</h1>
                        <div class="progress" style="height:20px;">
                            <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated"
                                id="progress-bar" role="progressbar"
                                aria-valuenow="40"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                style="width: 0%">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 mb-3 mt-3">
                        <div class="d-flex align-items-center mb-1">
                            <h3 class="dateDuty text-white mr-3"></h3>
                            <div class="spinner-border text-white spinnerBar" role="status" style="display: none;">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                        <h4 class="tableName text-white"></h4>
                    </div>
                    <div class="col-md-12">
                        <!-- <button type="button" class="btn btn-light cancelButton" style="display: none;">Cancel Process</button> -->
                        <button type="button" class="btn btn-default closeButton" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>