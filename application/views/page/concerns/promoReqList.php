<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Promo's Request Access List</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Promo TK</li>
                        <li class="breadcrumb-item active">Concerns</li>
                        <li class="breadcrumb-item active">Promo's Request Access List</li>
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
                        <h3 class="card-title">Add User</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="empId">Select Employee</label>
                                    <select class="form-control select2" id="empId" name="empId" data-placeholder="Select Employee" required>
                                        <option value="">Select Employee</option>
                                        <?php
                                        $cond   = "p.emp_id=e.emp_id AND current_status ='Active'";
                                        $query  = $this->promo_model->select_resultArray('pis', 'promo_user as p,employee3 as e', $cond);
                                        foreach ($query as $row) {
                                            $name   = ucwords(strtolower($row['name']));
                                            $empId  = $row['emp_id'];
                                            echo '<option value="' . $empId . '">[' . $empId . '] ' . $name . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" onclick="promoReqAccess('', 'add')" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">User List</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-hover align-middle" id="promoReqList_tables">
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Position</th>
                                    <!-- <th>Business Unit</th> -->
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