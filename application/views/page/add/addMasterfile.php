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
                    <h1>Masterfile (Add)</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Promo TK</li>
                        <li class="breadcrumb-item active">Add</li>
                        <li class="breadcrumb-item active">Masterfile (Add) </li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-5 d-flex">
                <div class="card card-primary card-outline w-100">
                    <div class="card-header">
                        <h3 class="card-title">Employee Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="searching">Search Employee</label>
                                    <input type="hidden" name="empId">
                                    <input type="text" class="form-control" id="searching" name="searchPromo" onkeyup="masterfileSearch(this.value)" placeholder="Search employee..." autocomplete="off" required>
                                    <div class="dropdown-list"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 promoDetails"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-7 d-flex">
                <div class="card card-primary card-outline w-100 form1" style="display: none;">
                    <div class="card-header">
                        <h3 class="card-title">Assigning of Masterfile Data</h3>
                    </div>
                    <form id="addMasterfileForm" novalidate="novalidate" autocomplete="off">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 addMasterfileForm"></div>
                            </div>
                        </div>
                        <div class="card-footer addmasterfile" style="display: none;">
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card card-primary card-outline form2" style="display: none;">
                    <div class="card-header">
                        <h3 class="card-title">Default Weekly Schedule</h3>
                    </div>
                    <form id="defaultSchedForm" novalidate="novalidate">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 defaultSchedForm"></div>
                            </div>
                        </div>
                        <div class="card-footer defaultsched" style="display: none;">
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