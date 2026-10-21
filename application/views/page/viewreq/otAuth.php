<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Overtime Authorization</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Promo TK</li>
                        <li class="breadcrumb-item active">View Request</li>
                        <li class="breadcrumb-item active">Overtime Authorization</li>
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

                    if ($row['endFC'] == '') {
                        $endFC = 'Last';
                    } else {
                        $endFC = $row['endFC'];
                    }

                    $statCut = $row['statCut'];
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
                <table id="viewReq_table" class="table table-hover table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Date From</th>
                            <th>Date To</th>
                            <th>Pending</th>
                            <th>Approved</th>
                            <th>Disapproved</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="viewReqPerEmp">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title viewReqPerEmp"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times; </span>
                </button>
            </div>
            <div class="modal-body viewReqPerEmp"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewEmpEOA">
    <div class="modal-dialog modal-dialog-scrollable modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Employee Overtime Authorization</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times; </span>
                </button>
            </div>
            <div class="modal-body viewEmpEOA"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>