<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Holiday</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item active">Promo TK</li>
                        <li class="breadcrumb-item active">Add</li>
                        <li class="breadcrumb-item active">Holiday </li>
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
                        <h3 class="card-title">Add Holiday</h3>
                    </div>
                    <form id="add_holiday" novalidate="novalidate">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="holiday">Holiday Name</label>
                                        <input type="text" class="form-control" id="holiday" name="holiday" placeholder="Type holiday name..." autocomplete="off" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="date">Holiday Date</label>
                                        <input type="text" class="form-control datetimepicker-input" id="date" name="date" data-toggle="datetimepicker" data-target="#date" placeholder="yyyy-mm-dd" autocomplete="off" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="type">Holiday Type</label>
                                        <select class="form-control" id="type" name="type" onchange="getCategory(this.value)" required>
                                            <option value="">---</option>
                                            <option value="Legal">Legal</option>
                                            <option value="Special">Special</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="category">Holiday Category</label>
                                        <select class="form-control" id="category" name="category" required>
                                            <option value="">---</option>
                                            <option value="pay_100">+ 100% Pay (With or Without Duty)</option>
                                            <option value="pay30_W_WO">+ 30% Pay(With or Without Duty)</option>
                                            <option value="pay30_W">+ 30% Pay(With Duty)</option>
                                            <option value="pay_no">No Pay</option>
                                        </select>
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
            <!-- <div class="col-md-7">
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">Holiday List</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-hover align-middle" id="holidays_promo" style="font-size: smaller;">
                            <thead>
                                <tr>
                                    <th>Holiday name</th>
                                    <th>Date</th>
                                    <th>Move To</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div> -->
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