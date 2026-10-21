<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Promo Supervisor List (Cutoff)</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Promo TK</li>
                        <li class="breadcrumb-item active">Concerns</li>
                        <li class="breadcrumb-item active">Promo Supervisor List (Cutoff)</li>
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
                        <div class="d-flex justify-content-between align-items-center flex-wrap mt-0">
                            <h3 class="card-title text-theme">Supervisor List</h3>
                            <span>
                                <button type="button" class="btn btn-xs btn-primary" onclick="addSupForm()">Add Supervisor</button>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-hover align-middle" id="setupSup_tables">
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Position</th>
                                    <th>Business Unit</th>
                                    <th>Department</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="addSupForm" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Add Supervisor and Setup Cutoff</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times; </span>
                </button>
            </div>
            <div class="modal-body">


                <select class="form-control select2" id="empId" name="empId" data-placeholder="Select Employee" onchange="addcutOff(this.value)">
                    <option value="">Select Supervisor...</option>
                    <?php
                    $cond = "((current_status = 'Active') ";
                    $cond .= "|| (current_status = 'End of Contract' && sub_status = 'Uncleared') ";
                    $cond .= "|| (current_status = 'Ad-Resigned' && sub_status = 'Uncleared') ";
                    $cond .= "|| (current_status = 'V-Resigned' && sub_status = 'Uncleared')) ";
                    $query = $this->promo_model->setupSupForm($cond);
                    foreach ($query as $key => $sub) {

                        $empId  = $sub['emp_id'];
                        $name   = ucwords(strtolower($sub['name']));

                        $cond   = array('empId' => $empId);
                        $chk    = $this->promo_model->select_rowArray('tk', 'promo_sup_start', $cond);
                        if (!$chk) {
                            echo    '<option value="' . $empId . '">' . $empId . ' | ' . $name . '</option>';
                        }
                    }
                    ?>
                </select>
                <div class="cutOff mt-3"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="addSupCutoff()">Submit</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cutOffForm" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Supervisor Cutoff</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times; </span>
                </button>
            </div>
            <div class="modal-body cutOffForm"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="addSupCutoff2()">Update</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Close </button>
            </div>
        </div>
    </div>
</div>