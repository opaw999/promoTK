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
                    <h1>Overtime DWA</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Promo TK</li>
                        <li class="breadcrumb-item active">Add</li>
                        <li class="breadcrumb-item active">Overtime DWA </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-8">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Add Overtime DWA</h3>
                    </div>
                    <form id="add_otdwa" novalidate="novalidate">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="searching">Search Employee</label>
                                        <input type="hidden" name="empId">
                                        <input type="hidden" name="bioM">
                                        <input type="text" class="form-control" id="searching" name="searchPromo" onkeyup="searchotdwa(this.value)" placeholder="Search employee..." autocomplete="off" required>
                                        <div class="dropdown-list"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="date">Date Overtime</label>
                                        <input type="text" class="form-control datetimepicker-input" id="date" name="date" onchange="getShiftdwa(this.value)" data-toggle="datetimepicker" data-target="#date" placeholder="yyyy-mm-dd" autocomplete="off" required>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="dwa">DWA Requested</label>
                                        <select class="form-control" id="dwa" name="dwa" required>
                                            <option value="">---</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <h6>Shift Schedule: <span class="text-danger shift"></span></h6>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="d1" value="1" onclick="activateTime(this.value)">
                                        <label class="form-check-label"><label>Overtime 1 [ <i class="text-danger">Before Duty</i> ]</label></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="in1">Start Time [ <span class="text-danger">IN</span> ]</label>
                                        <input type="text" class="form-control form-control-sm datetimepicker-input timePicker" id="in1" name="in1" data-toggle="datetimepicker" data-target="#in1" placeholder="HH:MM" autocomplete="off" required disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="out1">End Time [ <span class="text-danger">OUT</span> ]</label>
                                        <input type="text" class="form-control form-control-sm datetimepicker-input timePicker" id="out1" name="out1" data-toggle="datetimepicker" data-target="#out1" placeholder="HH:MM" autocomplete="off" required disabled>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="d2" value="2" onclick="activateTime(this.value)">
                                        <label class="form-check-label"><label>Overtime 2 [ <i class="text-danger">After Duty</i> ]</label></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="in2">Start Time [ <span class="text-danger">IN</span> ]</label>
                                        <input type="text" class="form-control form-control-sm datetimepicker-input timePicker" id="in2" name="in2" data-toggle="datetimepicker" data-target="#in2" placeholder="HH:MM" autocomplete="off" required disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="out2">End Time [ <span class="text-danger">OUT</span> ]</label>
                                        <input type="text" class="form-control form-control-sm datetimepicker-input timePicker" id="out2" name="out2" onchange="changeTime(this.name,this.value)" data-toggle="datetimepicker" data-target="#out2" placeholder="HH:MM" autocomplete="off" required disabled>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="d3" value="3" onclick="activateTime(this.value)">
                                        <label class="form-check-label"><label>Overtime 3 [ <i class="text-danger">After Duty</i> ]</label></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="in3">Start Time [ <span class="text-danger">IN</span> ]</label>
                                        <input type="text" class="form-control form-control-sm datetimepicker-input timePicker" id="in3" name="in3" data-toggle="datetimepicker" data-target="#in3" placeholder="HH:MM" autocomplete="off" required disabled>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="out3">End Time [ <span class="text-danger">OUT</span> ]</label>
                                        <input type="text" class="form-control form-control-sm datetimepicker-input timePicker" id="out3" name="out3" data-toggle="datetimepicker" data-target="#out3" placeholder="HH:MM" autocomplete="off" required disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Submit</button>
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
                            <th>Date Requested</th>
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