<style>
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
                    <h1>Masterfile</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Promo TK</li>
                        <li class="breadcrumb-item active">View</li>
                        <li class="breadcrumb-item active">Masterfile</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Search Promo Employee</h3>
                    </div>
                    <div class="card-body ">
                        <div class="d-flex">
                            <input type="text" class="form-control" name="searchPromo" onkeyup="searchPromo(this.value)" placeholder="Search employee..." autocomplete="off">
                        </div>
                        <div class="dropdown-list"></div>
                        <div class="masterfileDetails"></div>
                        <div class="row multiSchedules" style="display: none;">
                            <div class="col-md-12">
                                <hr>
                                <h5 class="text-center"><b>MULTIPLE SHIFT SCHEDULE</b></h5>
                                <table class="table table-hover table-sm align-middle" id="multiSched">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Shift Code</th>
                                            <th>Time Schedule</th>
                                            <th>Status</th>
                                            <th>Request</th>
                                            <th>Request Date</th>
                                            <th>Dayoff</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
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

<div class="modal fade" id="addpromoId">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Setup Biometric</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times; </span>
                </button>
            </div>
            <div class="modal-body addpromoId"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>