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
                    <h1>Manual Overtime (Roving)</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Promo TK</li>
                        <li class="breadcrumb-item active">Add</li>
                        <li class="breadcrumb-item active">Manual Overtime (Roving) </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Add Overtime</h3>
                    </div>
                    <form id="add_manualot" novalidate="novalidate">
                        <div class="card-body">

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="searching">Search Employee</label>
                                        <input type="hidden" name="empId">
                                        <input type="text" class="form-control" id="searching" name="searchPromo" onkeyup="searchPromos(this.value)" placeholder="Search employee..." autocomplete="off" required>
                                        <div class="dropdown-list"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="store">Select Store</label>
                                        <select class="form-control" id="store" name="store" required>
                                            <option value="">---</option>
                                            <?php
                                            foreach ($tk_stores as $key => $row) {
                                                echo '<option value="' . $key . '">' . $row . '</option>';
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="ot">Total Overtime</label>
                                        <input type="number" class="form-control" id="ot" name="overtime" min="0" step="0.5" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="dateOt">Date Assigned</label>
                                        <input type="text" class="form-control datetimepicker-input" id="dateOt" name="dateOT" data-toggle="datetimepicker" data-target="#dateOt" placeholder="yyyy-mm-dd" autocomplete="off" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Submit</button>
                            <button type="button" class="btn btn-info" onclick="view_manualot()">View Record(s)</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="manualot_list">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Manual Overtime List</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times; </span>
                </button>
            </div>
            <div class="modal-body table-responsive">
                <table id="manualot_table" class="table table-hover table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>HRMS ID</th>
                            <th>Name</th>
                            <th>Date Overtime</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close </button>
                <!-- <button type="button" class="btn btn-primary">Save changes </button> -->
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="manualot_emp">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Employee Manual Overtime</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times; </span>
                </button>
            </div>
            <div class="modal-body ">
                <table id="manualot_emp_table" class="table table-hover table-bordered table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Store</th>
                            <th>Total Overtime</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close </button>
                <!-- <button type="button" class="btn btn-primary">Save changes </button> -->
            </div>
        </div>
    </div>
</div>