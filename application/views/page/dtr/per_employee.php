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
                    <h1>Per Employee</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Promo TK</li>
                        <li class="breadcrumb-item active">DTR</li>
                        <li class="breadcrumb-item active">Per Employee</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-5">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Search Promo Employee</h3>
                    </div>
                    <div class="card-body ">
                        <div class="d-flex">
                            <input type="text" class="form-control" name="searchPromo" onkeyup="searchPromo(this.value)" placeholder="Search employee..." autocomplete="off">
                            <button class="btn btn-warning ml-1 text-white" onclick="promoClear()">Clear</button>
                        </div>
                        <div class="dropdown-list"></div>
                        <div class="promoDetails"> </div>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">List of Cutoff for <b><span class="cutoff">...</span></b></h3>
                    </div>
                    <div class="card-body promoCutoffs"></div>
                </div>
            </div>
        </div>

    </section>
</div>

<div class="modal fade" id="view_dtr">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Daily Time Record</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times; </span>
                </button>
            </div>
            <div class="modal-body view_dtr"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close </button>
                <!-- <button type="button" class="btn btn-primary">Save changes </button> -->
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="time_entries">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Time Entries</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times; </span>
                </button>
            </div>
            <div class="modal-body time_entries"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close </button>
                <!-- <button type="button" class="btn btn-primary">Save changes </button> -->
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="dtr_requests">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Request Details</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times; </span>
                </button>
            </div>
            <div class="modal-body dtr_requests"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close </button>
                <!-- <button type="button" class="btn btn-primary">Save changes </button> -->
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="dtr_process">
    <div class="modal-dialog" style="max-width: 100%; margin:0;height: 100%;">
        <div class="modal-content ">
            <div class="modal-body">
                <div class="row" style="padding: 100px; position:relative;">
                    <div class="col-md-12">
                        <h3 class="modal-title text-white text-center">Promo - Calculating Days Worked For Net DTR</h3>
                        <h5 class="date_process text-center"></h5>
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

<div class="modal fade" id="viewMasterfile">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Masterfile Details</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times; </span>
                </button>
            </div>
            <div class="modal-body viewMasterfile"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close </button>
                <!-- <button type="button" class="btn btn-primary">Save changes </button> -->
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="defSched">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Default Schedule</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times; </span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-hover table-sm" id="defSchedule" style="font-size:smaller;">
                    <thead>
                        <tr>
                            <th width="5%">Day</th>
                            <th width="10%">Shift Code</th>
                            <th width="40%">Time Schedule</th>
                            <th>Store1</th>
                            <th>Store2</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewDO">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Assigned Dayoff/No Dayoff</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times; </span>
                </button>
            </div>
            <div class="modal-body viewDO"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewSched">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Monthly Schedule</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times; </span>
                </button>
            </div>
            <div class="modal-body viewSched"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close </button>
                <!-- <button type="button" class="btn btn-primary">Save changes </button> -->
            </div>
        </div>
    </div>
</div>