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
</style>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Per Cutoff [3rd]</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Promo TK</li>
                        <li class="breadcrumb-item active">Process</li>
                        <li class="breadcrumb-item active">Per Cutoff [3rd]</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="card card-primary card-outline">
            <div class="card-header text-center">
                <?php
                $i = 1;
                foreach ($cutoffs as $row) {

                    $statCut = $row['statCut'];
                    $endFC = ($row['endFC'] == '') ? 'Last' : $row['endFC'];
                    $sched = $row['startFC'] . '-' . $endFC . ' / ' . $row['startSC'] . '-' . $row['endSC'];
                    if ($i == 1) {
                        $class = 'btn btn-primary btn-xs statCut active mb-1 mr-1';
                    } else {
                        $class = 'btn btn-default btn-xs statCut mb-1 mr-1';
                    }
                    echo '<a href="javascript:;" id="' . $statCut . '" class="' . $class . '">' . $sched . '</a>';
                    $i++;
                }
                ?>
            </div>
            <div class="card-body">
                <table id="co_thirtd_table" class="table table-hover table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Date From</th>
                            <th>Date To</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="employee_list">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Employee List (<span id="cutoff"></span>)</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times; </span>
                </button>
            </div>
            <div class="modal-body employee_list">
                <!-- <table id="employee_list_table" class="table table-hover table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>HRMS ID</th>
                            <th>Name</th>
                            <th>Company</th>
                            <th>Department</th>
                            <th>Promo Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table> -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="processCOthird()">Process Final DTR </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close </button>
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
                        <h3 class="modal-title text-white text-center">PROMO - Calculating Days Worked For Net DTR</h3>
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
                        <h5 class="tableName text-white"></h5>
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