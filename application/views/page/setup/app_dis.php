<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Approve/Disapprove Button</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Promo TK</li>
                        <li class="breadcrumb-item active">Setup</li>
                        <li class="breadcrumb-item active">App/Dis Button</li>
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
                <table id="appdis_table" class="table table-hover table-bordered table-sm align-middle">
                    <thead>
                        <tr>
                            <th>HRMS ID</th>
                            <th>Approving Officer</th>
                            <th>Business Unit</th>
                            <th>Department</th>
                            <th><input type="checkbox" name="status_switch" class="status_switch" data-off-color="danger" data-on-color="success"></th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>
</div>